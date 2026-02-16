<?php
declare(strict_types=1);

require_once __DIR__ . '/middleware.php';

$userId = api_require_auth();
api_require_csrf_for_write();

$layout = (string)($_POST['layout'] ?? 'default');
$word_wrap = isset($_POST['word_wrap']) ? (int)$_POST['word_wrap'] : 1;
$show_line_numbers = isset($_POST['show_line_numbers']) ? (int)$_POST['show_line_numbers'] : 1;
$auto_save = isset($_POST['auto_save']) ? (int)$_POST['auto_save'] : 1;

$stmt = $pdo->prepare('
    INSERT INTO tbl_preferences (user_id, layout, word_wrap, show_line_numbers, auto_save, updated_at)
    VALUES (?, ?, ?, ?, ?, NOW())
    ON DUPLICATE KEY UPDATE layout = VALUES(layout), word_wrap = VALUES(word_wrap), show_line_numbers = VALUES(show_line_numbers), auto_save = VALUES(auto_save), updated_at = NOW()
');
$stmt->execute([$userId, $layout, $word_wrap, $show_line_numbers, $auto_save]);

api_json(['success' => true]);
