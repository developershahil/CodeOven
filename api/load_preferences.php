<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit(); }
$user_id = current_user_id();
$stmt = $pdo->prepare('SELECT layout, word_wrap, show_line_numbers, auto_save FROM tbl_preferences WHERE user_id = ? LIMIT 1');
$stmt->execute([$user_id]);
$row = $stmt->fetch();
echo json_encode(['success'=>true, 'preferences' => $row]);
