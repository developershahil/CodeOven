<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}
$user_id = current_user_id();
$stmt = $pdo->prepare('SELECT file_name, MAX(updated_at) as updated_at FROM tbl_files WHERE user_id = ? GROUP BY file_name ORDER BY updated_at DESC');
$stmt->execute([$user_id]);
$rows = $stmt->fetchAll();
echo json_encode(['success' => true, 'files' => $rows]);
