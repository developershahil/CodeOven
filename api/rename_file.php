<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}
$user_id = current_user_id();
$old = trim($_POST['old_name'] ?? '');
$new = trim($_POST['new_name'] ?? '');
if ($old === '' || $new === '') {
    echo json_encode(['success' => false, 'message' => 'old_name and new_name required']);
    exit();
}
// prevent clobbering existing project name
$stmt = $pdo->prepare('SELECT 1 FROM tbl_files WHERE user_id = ? AND file_name = ? LIMIT 1');
$stmt->execute([$user_id, $new]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'A project with that name already exists.']);
    exit();
}
$stmt = $pdo->prepare('UPDATE tbl_files SET file_name = ? WHERE user_id = ? AND file_name = ?');
$stmt->execute([$new, $user_id, $old]);
echo json_encode(['success' => true]);
