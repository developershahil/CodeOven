<?php
declare(strict_types=1);

require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../includes/workspace.php';

$userId = api_require_auth();
$fileName = trim((string)($_GET['file_name'] ?? ''));

if ($fileName === '') {
    api_json(['success' => false, 'message' => 'file_name required'], 422);
}

$stmt = $pdo->prepare('SELECT file_name FROM tbl_files WHERE user_id = ? AND file_name = ? LIMIT 1');
$stmt->execute([$userId, $fileName]);
if (!$stmt->fetch()) {
    api_json(['success' => false, 'message' => 'Project not found'], 404);
}

api_json([
    'success' => true,
    'file' => read_project_files($userId, $fileName),
]);
