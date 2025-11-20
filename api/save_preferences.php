<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit(); }
$user_id = current_user_id();
$layout = $_POST['layout'] ?? 'default';
$word_wrap = isset($_POST['word_wrap']) ? (int)$_POST['word_wrap'] : 1;
$show_line_numbers = isset($_POST['show_line_numbers']) ? (int)$_POST['show_line_numbers'] : 1;
$auto_save = isset($_POST['auto_save']) ? (int)$_POST['auto_save'] : 1;

$stmt = $pdo->prepare('UPDATE tbl_preferences SET layout = ?, word_wrap = ?, show_line_numbers = ?, auto_save = ?, updated_at = NOW() WHERE user_id = ?');
$stmt->execute([$layout, $word_wrap, $show_line_numbers, $auto_save, $user_id]);
echo json_encode(['success'=>true]);
