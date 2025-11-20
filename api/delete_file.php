<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}
$user_id = current_user_id();
$file_name = $_POST['file_name'] ?? '';
if ($file_name === '') {
    echo json_encode(['success' => false, 'message' => 'file_name required']);
    exit();
}
$stmt = $pdo->prepare('DELETE FROM tbl_files WHERE user_id = ? AND file_name = ?');
$stmt->execute([$user_id, $file_name]);
echo json_encode(['success' => true]);
