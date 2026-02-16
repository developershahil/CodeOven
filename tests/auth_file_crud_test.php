<?php
declare(strict_types=1);

$base = $argv[1] ?? 'http://localhost/CodeOven';
$username = $argv[2] ?? ('testuser_' . random_int(1000, 9999));
$email = $argv[3] ?? ($username . '@example.com');
$password = $argv[4] ?? 'Strong#Pass123';

$cookieFile = tempnam(sys_get_temp_dir(), 'codeoven_cookie_');

function http_request(string $url, array $opts = []): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 20,
    ] + $opts);

    $raw = curl_exec($ch);
    if ($raw === false) {
        throw new RuntimeException('Curl failed: ' . curl_error($ch));
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    return [
        'status' => $status,
        'headers' => substr($raw, 0, $headerSize),
        'body' => substr($raw, $headerSize),
    ];
}

function extract_csrf(string $html): string
{
    if (preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    }
    if (preg_match('/name="csrf-token"\s+content="([^"]+)"/', $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    }
    throw new RuntimeException('Unable to find CSRF token');
}

try {
    $signupPage = http_request($base . '/php/signup.php', [
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
    ]);
    $csrfSignup = extract_csrf($signupPage['body']);

    $signupRes = http_request($base . '/php/signup.php', [
        CURLOPT_POST => true,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_POSTFIELDS => http_build_query([
            '_csrf_token' => $csrfSignup,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'confirm_password' => $password,
            'agree_terms' => 'on',
        ]),
    ]);

    $loginPage = http_request($base . '/php/login.php', [
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
    ]);
    $csrfLogin = extract_csrf($loginPage['body']);

    $loginRes = http_request($base . '/php/login.php', [
        CURLOPT_POST => true,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_POSTFIELDS => http_build_query([
            '_csrf_token' => $csrfLogin,
            'username' => $username,
            'password' => $password,
        ]),
    ]);

    $dash = http_request($base . '/php/dashboard.php', [
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
    ]);
    $csrfApi = extract_csrf($dash['body']);

    $save = http_request($base . '/api/save_file.php', [
        CURLOPT_POST => true,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_POSTFIELDS => http_build_query([
            '_csrf_token' => $csrfApi,
            'file_name' => 'demo_project',
            'html' => '<h1>Hello</h1>',
            'css' => 'h1{color:red;}',
            'js' => 'console.log("ok")',
        ]),
    ]);

    $list = http_request($base . '/api/get_files.php', [
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
    ]);

    $load = http_request($base . '/api/load_file.php?file_name=demo_project', [
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
    ]);

    $delete = http_request($base . '/api/delete_file.php', [
        CURLOPT_POST => true,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_POSTFIELDS => http_build_query([
            '_csrf_token' => $csrfApi,
            'file_name' => 'demo_project',
        ]),
    ]);

    echo "Signup HTTP: {$signupRes['status']}\n";
    echo "Login HTTP: {$loginRes['status']}\n";
    echo "Save response: {$save['body']}\n";
    echo "List response: {$list['body']}\n";
    echo "Load response: {$load['body']}\n";
    echo "Delete response: {$delete['body']}\n";
} finally {
    @unlink($cookieFile);
}
