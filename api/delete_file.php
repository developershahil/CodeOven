<?php
declare(strict_types=1);

require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../includes/workspace.php';

$userId = api_require_auth();
api_require_csrf_for_write();

$fileName = trim((string)($_POST['file_name'] ?? ''));
if ($fileName === '') {
    api_json(['success' => false, 'message' => 'file_name required'], 422);
}

try {
    $safeFileName = sanitize_project_name($fileName);
} catch (Throwable $e) {
    api_json(['success' => false, 'message' => 'Invalid file_name'], 422);
}

delete_project_dir($userId, $safeFileName);

$stmt = $pdo->prepare('DELETE FROM tbl_files WHERE user_id = ? AND file_name = ?');
$stmt->execute([$userId, $safeFileName]);
api_json(['success' => true]);
