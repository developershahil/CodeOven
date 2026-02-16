<?php
declare(strict_types=1);

require_once __DIR__ . '/middleware.php';

$userId = api_require_auth();

$stmt = $pdo->prepare('SELECT layout, word_wrap, show_line_numbers, auto_save FROM tbl_preferences WHERE user_id = ? LIMIT 1');
$stmt->execute([$userId]);
$row = $stmt->fetch();
api_json([
    'success' => true,
    'preferences' => $row ?: ['layout' => 'default', 'word_wrap' => 1, 'show_line_numbers' => 1, 'auto_save' => 1],
]);
