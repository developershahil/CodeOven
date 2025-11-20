<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}
$user_id = current_user_id();
$file_name = $_GET['file_name'] ?? '';
if ($file_name === '') {
    echo json_encode(['success' => false, 'message' => 'file_name required']);
    exit();
}
$stmt = $pdo->prepare('SELECT file_type, file_content FROM tbl_files WHERE user_id = ? AND file_name = ?');
$stmt->execute([$user_id, $file_name]);
$rows = $stmt->fetchAll();
$data = ['html'=>'','css'=>'','js'=>''];
foreach ($rows as $r) {
    $data[$r['file_type']] = $r['file_content'];
}
echo json_encode(['success'=>true,'file'=>$data]);
