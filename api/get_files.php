<?php
declare(strict_types=1);

require_once __DIR__ . '/middleware.php';

$userId = api_require_auth();

$stmt = $pdo->prepare('SELECT file_name, MAX(updated_at) as updated_at FROM tbl_files WHERE user_id = ? GROUP BY file_name ORDER BY updated_at DESC');
$stmt->execute([$userId]);

api_json([
    'success' => true,
    'files' => $stmt->fetchAll(),
]);
