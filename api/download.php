<?php
declare(strict_types=1);

require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../includes/workspace.php';

$userId = api_require_auth();
$fileName = trim((string)($_GET['file_name'] ?? ''));
if ($fileName === '') {
    api_json(['success' => false, 'message' => 'file_name required'], 422);
}

$safeFileName = sanitize_project_name($fileName);
$files = read_project_files($userId, $safeFileName);
if ($files['html'] === '' && $files['css'] === '' && $files['js'] === '') {
    api_json(['success' => false, 'message' => 'Project not found'], 404);
}

$zipPath = tempnam(sys_get_temp_dir(), 'codeoven_zip_');
$zip = new ZipArchive();
$zip->open($zipPath, ZipArchive::OVERWRITE);
$zip->addFromString('index.html', $files['html']);
$zip->addFromString('style.css', $files['css']);
$zip->addFromString('script.js', $files['js']);
$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $safeFileName . '.zip"');
header('Content-Length: ' . (string)filesize($zipPath));
readfile($zipPath);
@unlink($zipPath);
