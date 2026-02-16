<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/middleware.php';

const DEFAULT_TIMEOUT_SECONDS = 5;
const DEFAULT_MEMORY_LIMIT = '128m';
const DEFAULT_CPU_LIMIT = '0.50';
const DEFAULT_PIDS_LIMIT = '64';
const DEFAULT_MAX_CODE_BYTES = 100000;
const DEFAULT_MAX_STDIN_BYTES = 32768;
const DEFAULT_MAX_OUTPUT_BYTES = 65536;

$allowedLanguages = [
    'python' => 'Main.py',
    'php' => 'Main.php',
    'cpp' => 'Main.cpp',
    'c++' => 'Main.cpp',
];

$requestId = bin2hex(random_bytes(8));
$timeoutSeconds = (int)(getenv('SANDBOX_TIMEOUT_SECONDS') ?: DEFAULT_TIMEOUT_SECONDS);
$memoryLimit = (string)(getenv('SANDBOX_MEMORY_LIMIT') ?: DEFAULT_MEMORY_LIMIT);
$userId = api_require_auth();
api_require_csrf_for_write();
require_once __DIR__ . '/../includes/workspace.php';

$cpuLimit = (string)(getenv('SANDBOX_CPU_LIMIT') ?: DEFAULT_CPU_LIMIT);
$pidsLimit = (string)(getenv('SANDBOX_PIDS_LIMIT') ?: DEFAULT_PIDS_LIMIT);
$maxCodeBytes = (int)(getenv('SANDBOX_MAX_CODE_BYTES') ?: DEFAULT_MAX_CODE_BYTES);
$maxStdinBytes = (int)(getenv('SANDBOX_MAX_STDIN_BYTES') ?: DEFAULT_MAX_STDIN_BYTES);
$maxOutputBytes = (int)(getenv('SANDBOX_MAX_OUTPUT_BYTES') ?: DEFAULT_MAX_OUTPUT_BYTES);
$baseTempDir = (string)(getenv('SANDBOX_BASE_DIR') ?: (sys_get_temp_dir() . '/codeoven-sandbox'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['success' => false, 'error' => 'Method not allowed. Use POST.', 'request_id' => $requestId]);
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
if (!is_array($input)) {
    $input = $_POST;
}

$language = strtolower(trim((string)($input['language'] ?? '')));
$code = (string)($input['code'] ?? '');
$stdin = (string)($input['stdin'] ?? '');

if ($language === 'c++') {
    $language = 'cpp';
}

if (!isset($allowedLanguages[$language])) {
    respond(422, ['success' => false, 'error' => 'Unsupported language. Allowed: python, php, cpp.', 'request_id' => $requestId]);
}

if ($code === '') {
    respond(422, ['success' => false, 'error' => 'Code is required.', 'request_id' => $requestId]);
}

if (strlen($code) > $maxCodeBytes) {
    respond(413, ['success' => false, 'error' => "Code is too large. Max {$maxCodeBytes} bytes.", 'request_id' => $requestId]);
}

if (strlen($stdin) > $maxStdinBytes) {
    respond(413, ['success' => false, 'error' => "stdin is too large. Max {$maxStdinBytes} bytes.", 'request_id' => $requestId]);
}

$runnerScript = realpath(__DIR__ . '/../sandbox/runner/run_in_sandbox.sh');
if ($runnerScript === false || !is_file($runnerScript)) {
    respond(500, ['success' => false, 'error' => 'Sandbox runner script not found.', 'request_id' => $requestId]);
}

$userRunBase = rtrim(ensure_user_workspace($userId), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.runs';
if (!is_dir($userRunBase) && !mkdir($userRunBase, 0700, true)) {
    respond(500, ['success' => false, 'error' => 'Unable to create sandbox base directory.', 'request_id' => $requestId]);
}

$tempDir = $userRunBase . '/' . bin2hex(random_bytes(12));
if (!mkdir($tempDir, 0700, true)) {
    respond(500, ['success' => false, 'error' => 'Unable to create sandbox directory.', 'request_id' => $requestId]);
}

$sourceFile = $tempDir . '/' . $allowedLanguages[$language];
$stdinFile = $tempDir . '/stdin.txt';

try {
    if (file_put_contents($sourceFile, $code) === false || file_put_contents($stdinFile, $stdin) === false) {
        throw new RuntimeException('Failed to write source/stdin files.');
    }
    @chmod($sourceFile, 0600);
    @chmod($stdinFile, 0600);
    @chmod($tempDir, 0700);

    $cmd = sprintf(
        'timeout --signal=KILL %d %s %s %s %s %s %s',
        max(1, $timeoutSeconds + 1),
        escapeshellarg($runnerScript),
        escapeshellarg($language),
        escapeshellarg($tempDir),
        escapeshellarg((string)$timeoutSeconds),
        escapeshellarg($memoryLimit),
        escapeshellarg($cpuLimit),
        escapeshellarg($pidsLimit)
    );

    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $startedAt = microtime(true);
    $process = proc_open($cmd, $descriptors, $pipes, __DIR__ . '/..');
    if (!is_resource($process)) {
        throw new RuntimeException('Failed to launch sandbox process.');
    }

    fclose($pipes[0]);
    $stdout = (string)stream_get_contents($pipes[1]);
    $stderr = (string)stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);
    $durationMs = (int)round((microtime(true) - $startedAt) * 1000);
    $timedOut = ($exitCode === 124 || $exitCode === 137);

    if (strlen($stdout) > $maxOutputBytes) {
        $stdout = substr($stdout, 0, $maxOutputBytes) . "\n[output truncated]";
    }
    if (strlen($stderr) > $maxOutputBytes) {
        $stderr = substr($stderr, 0, $maxOutputBytes) . "\n[error output truncated]";
    }

    $success = ($exitCode === 0);
    respond($success ? 200 : 400, [
        'success' => $success,
        'request_id' => $requestId,
        'language' => $language,
        'stdout' => $stdout,
        'stderr' => $stderr,
        'exit_code' => $exitCode,
        'timed_out' => $timedOut,
        'duration_ms' => $durationMs,
    ]);
} catch (Throwable $e) {
    respond(500, [
        'success' => false,
        'request_id' => $requestId,
        'error' => 'Sandbox execution failed.',
        'stderr' => $e->getMessage(),
    ]);
} finally {
    rrmdir($tempDir);
}

function rrmdir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    $items = scandir($dir);
    if ($items === false) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            rrmdir($path);
        } else {
            @unlink($path);
        }
    }

    @rmdir($dir);
}

function respond(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}
