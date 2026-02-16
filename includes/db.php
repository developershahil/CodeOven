<?php
declare(strict_types=1);

$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_NAME = getenv('DB_NAME') ?: 'editor_db';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';

function fail_database_connection(string $publicMessage): void {
    $isApiRequest = isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false;

    if ($isApiRequest) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode([
            'success' => false,
            'message' => $publicMessage,
        ]);
        exit;
    }

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
    }
    exit($publicMessage);
}

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    fail_database_connection('Database connection failed. Please check your database configuration.');
}

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
    fail_database_connection('MySQLi connection failed. Please check your database configuration.');
}
mysqli_set_charset($conn, 'utf8mb4');

function ensure_auth_schema(PDO $pdo): void
{
    $pdo->exec("\n        CREATE TABLE IF NOT EXISTS tbl_user_workspaces (\n            workspace_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,\n            user_id INT NOT NULL UNIQUE,\n            workspace_path VARCHAR(255) NOT NULL,\n            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            CONSTRAINT fk_workspace_user FOREIGN KEY (user_id) REFERENCES tbl_users(user_id) ON DELETE CASCADE\n        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4\n    ");
}

ensure_auth_schema($pdo);
