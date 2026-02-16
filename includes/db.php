<?php
// includes/db.php
// Update these settings to match your local MySQL environment
$DB_HOST = '127.0.0.1';
$DB_NAME = 'editor_db';
$DB_USER = 'root';
$DB_PASS = '';

function fail_database_connection(string $publicMessage): void {
    $isApiRequest = isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false;

    if ($isApiRequest) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode([
            'success' => false,
            'message' => $publicMessage,
        ]);
        exit();
    }

    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    exit($publicMessage);
}

try {
    $pdo = new PDO("mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    fail_database_connection('Database connection failed. Please check your database configuration.');
}

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
    fail_database_connection('MySQLi connection failed. Please check your database configuration.');
}
