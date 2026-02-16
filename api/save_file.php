<?php
declare(strict_types=1);

require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../includes/workspace.php';

$userId = api_require_auth();
api_require_csrf_for_write();

$fileName = trim((string)($_POST['file_name'] ?? ''));
$html = (string)($_POST['html'] ?? '');
$css = (string)($_POST['css'] ?? '');
$js = (string)($_POST['js'] ?? '');

if ($fileName === '') {
    api_json(['success' => false, 'message' => 'File name required.'], 422);
}

try {
    $fileName = sanitize_project_name($fileName);
} catch (Throwable $e) {
    api_json(['success' => false, 'message' => 'Invalid file name.'], 422);
}

write_project_files($userId, $fileName, $html, $css, $js);

$pdo->beginTransaction();
try {
    $exists = $pdo->prepare('SELECT file_id FROM tbl_files WHERE user_id = ? AND file_name = ? AND file_type = ? LIMIT 1');
    $insert = $pdo->prepare('INSERT INTO tbl_files (user_id, file_name, file_type, file_content, line_numbers, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())');
    $update = $pdo->prepare('UPDATE tbl_files SET updated_at = NOW() WHERE file_id = ?');

    foreach (['html', 'css', 'js'] as $type) {
        $exists->execute([$userId, $fileName, $type]);
        $row = $exists->fetch();
        if ($row) {
            $update->execute([(int)$row['file_id']]);
        } else {
            $insert->execute([$userId, $fileName, $type, '']);
        }
    }

    $pdo->commit();

    api_json([
        'success' => true,
        'file_name' => $fileName,
    ], 201);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    api_json([
        'success' => false,
        'message' => 'Save failed.',
    ], 500);
}
