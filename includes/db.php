<?php
// includes/db.php
// Update these settings to match your local MySQL environment
$DB_HOST = '127.0.0.1';
$DB_NAME = 'editor_db';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO("mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // Fail loudly in development so the developer can fix configuration.
    // In production you may want to log and show a generic message instead.
    header('Content-Type: text/plain; charset=utf-8');
    die("Database connection failed: " . $e->getMessage());
}
$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
    die("MySQLi connection failed: " . mysqli_connect_error());
}