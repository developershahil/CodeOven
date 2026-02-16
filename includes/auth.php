<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/workspace.php';

app_session_start();

function current_user(): ?string {
    return $_SESSION['username'] ?? null;
}

function current_user_id(): ?int {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function is_authenticated(): bool
{
    return current_user_id() !== null;
}

function require_login_page(string $redirectTo = 'login.php'): void
{
    if (!is_authenticated()) {
        header('Location: ' . $redirectTo);
        exit();
    }
}

function require_login_api(): void
{
    if (!is_authenticated()) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
}

function get_user_by_username(string $username): array|false {
    global $pdo;
    $stmt = $pdo->prepare('SELECT user_id, username, email, password_hash, is_active FROM tbl_users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    return $stmt->fetch();
}

function register_user(string $username, string $email, string $password): array {
    global $pdo;

    $username = trim($username);
    $email = trim($email);

    if (trim($username) === '' || trim($email) === '' || trim($password) === '') {
        return ['success' => false, 'message' => 'All fields are required.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email address.'];
    }

    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
    }

    $stmt = $pdo->prepare('SELECT user_id FROM tbl_users WHERE username = ? OR email = ? LIMIT 1');
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Username or email already exists.'];
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO tbl_users (username, email, password_hash, is_active, password_updated_at) VALUES (?, ?, ?, 1, NOW())');
    $stmt->execute([$username, $email, $hash]);
    $id = (int)$pdo->lastInsertId();

    $stmt2 = $pdo->prepare('INSERT INTO tbl_preferences (user_id) VALUES (?)');
    $stmt2->execute([$id]);

    $workspacePath = ensure_user_workspace($id);
    $stmt3 = $pdo->prepare('INSERT INTO tbl_user_workspaces (user_id, workspace_path) VALUES (?, ?) ON DUPLICATE KEY UPDATE workspace_path = VALUES(workspace_path), updated_at = NOW()');
    $stmt3->execute([$id, $workspacePath]);

    return ['success' => true, 'user_id' => $id, 'workspace_path' => $workspacePath];
}

function login_user(string $username, string $password): bool {
    global $pdo;

    $stmt = $pdo->prepare('SELECT user_id, username, password_hash, is_active FROM tbl_users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || (int)$user['is_active'] !== 1) {
        return false;
    }

    if (!password_verify($password, $user['password_hash'])) {
        $pdo->prepare('UPDATE tbl_users SET failed_login_attempts = failed_login_attempts + 1 WHERE user_id = ?')->execute([(int)$user['user_id']]);
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['user_id'];
    $_SESSION['username'] = (string)$user['username'];

    $pdo->prepare('UPDATE tbl_users SET failed_login_attempts = 0, last_login_at = NOW() WHERE user_id = ?')->execute([(int)$user['user_id']]);

    $workspacePath = ensure_user_workspace((int)$user['user_id']);
    $pdo->prepare('INSERT INTO tbl_user_workspaces (user_id, workspace_path) VALUES (?, ?) ON DUPLICATE KEY UPDATE workspace_path = VALUES(workspace_path), updated_at = NOW()')
        ->execute([(int)$user['user_id'], $workspacePath]);

    return true;
}

function logout_user(): void {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, [
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => (bool)$params['secure'],
            'httponly' => (bool)$params['httponly'],
            'samesite' => 'Lax',
        ]);
    }

    session_destroy();
}
