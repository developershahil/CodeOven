<?php
require_once __DIR__ . '/../includes/auth.php';
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo 'Unauthorized';
    exit();
}
$user_id = current_user_id();
$file_name = $_GET['file_name'] ?? '';
if ($file_name === '') {
    http_response_code(400);
    echo 'file_name required';
    exit();
}

$stmt = $pdo->prepare('SELECT file_type, file_content FROM tbl_files WHERE user_id = ? AND file_name = ?');
$stmt->execute([$user_id, $file_name]);
$rows = $stmt->fetchAll();

if (!$rows) {
    http_response_code(404);
    echo 'Not found';
    exit();
}

// Prepare files
$tmpDir = sys_get_temp_dir() . '/editor_export_' . time() . '_' . rand(1000,9999);
mkdir($tmpDir);
$index = '<!doctype html>\n<html>\n<head>\n<meta charset="utf-8">\n<title>' . htmlspecialchars($file_name) . '</title>\n<link rel="stylesheet" href="style.css">\n</head>\n<body>\n';
$script = '\n</body>\n</html>';

$css = '';
$js = '';
$html = '';

foreach ($rows as $r) {
    if ($r['file_type'] === 'html') $html = $r['file_content'];
    if ($r['file_type'] === 'css') $css = $r['file_content'];
    if ($r['file_type'] === 'js') $js = $r['file_content'];
}

// If the stored HTML doesn't already include html/body tags, wrap it
$finalIndex = $html ?: '<h1>' . htmlspecialchars($file_name) . '</h1>';

file_put_contents($tmpDir . '/index.html', $finalIndex);
file_put_contents($tmpDir . '/style.css', $css);
file_put_contents($tmpDir . '/script.js', $js);

$zipPath = $tmpDir . '.zip';
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
    http_response_code(500);
    echo 'Could not create zip file';
    exit();
}
$zip->addFile($tmpDir . '/index.html', 'index.html');
$zip->addFile($tmpDir . '/style.css', 'style.css');
$zip->addFile($tmpDir . '/script.js', 'script.js');
$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($file_name) . '.zip"');
header('Content-Length: ' . filesize($zipPath));
readfile($zipPath);

// cleanup
@unlink($tmpDir . '/index.html');
@unlink($tmpDir . '/style.css');
@unlink($tmpDir . '/script.js');
@unlink($zipPath);
@rmdir($tmpDir);
