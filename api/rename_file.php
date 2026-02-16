<?php
declare(strict_types=1);

require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../includes/workspace.php';

$userId = api_require_auth();
api_require_csrf_for_write();

$old = trim((string)($_POST['old_name'] ?? ''));
$new = trim((string)($_POST['new_name'] ?? ''));
if ($old === '' || $new === '') {
    api_json(['success' => false, 'message' => 'old_name and new_name required'], 422);
}

$safeOld = sanitize_project_name($old);
$safeNew = sanitize_project_name($new);

$stmt = $pdo->prepare('SELECT 1 FROM tbl_files WHERE user_id = ? AND file_name = ? LIMIT 1');
$stmt->execute([$userId, $safeNew]);
if ($stmt->fetch()) {
    api_json(['success' => false, 'message' => 'A project with that name already exists.'], 409);
}

try {
    rename_project_dir($userId, $safeOld, $safeNew);
} catch (Throwable $e) {
    api_json(['success' => false, 'message' => $e->getMessage()], 400);
}

$upd = $pdo->prepare('UPDATE tbl_files SET file_name = ?, updated_at = NOW() WHERE user_id = ? AND file_name = ?');
$upd->execute([$safeNew, $userId, $safeOld]);
if ($upd->rowCount() === 0) {
    api_json(['success' => false, 'message' => 'Original project not found.'], 404);
}
api_json(['success' => true]);
