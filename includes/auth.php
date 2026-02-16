<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/workspace.php';
require_once __DIR__ . '/rate_limit.php';

app_session_start();

const MAX_FAILED_LOGIN_ATTEMPTS = 5;
const ACCOUNT_LOCKOUT_MINUTES = 15;

function auth_set_last_error(string $message): void
{
    $_SESSION['_auth_last_error'] = $message;
}

function auth_last_error(): string
{
    $msg = $_SESSION['_auth_last_error'] ?? '';
    unset($_SESSION['_auth_last_error']);
    return is_string($msg) ? $msg : '';
}

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

function valid_username(string $username): bool
{
    return (bool)preg_match('/^[a-zA-Z0-9_]{3,32}$/', $username);
}

function valid_password_strength(string $password): bool
{
    if (strlen($password) < 10) {
        return false;
    }
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    if (!preg_match('/\d/', $password)) {
        return false;
    }
    return (bool)preg_match('/[^a-zA-Z0-9]/', $password);
}

function get_user_by_username(string $username): array|false {
    global $pdo;
    $stmt = $pdo->prepare('SELECT user_id, username, email, password_hash, is_active, failed_login_attempts, locked_until FROM tbl_users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    return $stmt->fetch();
}

function register_user(string $username, string $email, string $password): array {
    global $pdo;

    $ip = rate_limit_client_ip();
    if (rate_limit_hit('register:' . $ip, 5, 600)) {
        return ['success' => false, 'message' => 'Too many registration attempts. Try again later.'];
    }

    $username = trim($username);
    $email = trim($email);

    if ($username === '' || $email === '' || trim($password) === '') {
        return ['success' => false, 'message' => 'All fields are required.'];
    }

    if (!valid_username($username)) {
        return ['success' => false, 'message' => 'Username must be 3-32 chars and use only letters, numbers, underscore.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email address.'];
    }

    if (!valid_password_strength($password)) {
        return ['success' => false, 'message' => 'Password must be 10+ chars and include upper/lowercase, number and symbol.'];
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

    $ip = rate_limit_client_ip();
    if (rate_limit_hit('login:' . $ip, 10, 300)) {
        auth_set_last_error('Too many login attempts. Please wait a few minutes.');
        return false;
    }

    $username = trim($username);
    if (!valid_username($username)) {
        auth_set_last_error('Invalid username or password.');
        return false;
    }

    $stmt = $pdo->prepare('SELECT user_id, username, password_hash, is_active, failed_login_attempts, locked_until FROM tbl_users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || (int)$user['is_active'] !== 1) {
        auth_set_last_error('Invalid username or password.');
        return false;
    }

    if (!empty($user['locked_until']) && strtotime((string)$user['locked_until']) > time()) {
        auth_set_last_error('Account temporarily locked due to failed login attempts. Try again later.');
        return false;
    }

    if (!password_verify($password, (string)$user['password_hash'])) {
        $failedAttempts = ((int)$user['failed_login_attempts']) + 1;
        if ($failedAttempts >= MAX_FAILED_LOGIN_ATTEMPTS) {
            $pdo->prepare('UPDATE tbl_users SET failed_login_attempts = ?, locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE user_id = ?')
                ->execute([$failedAttempts, ACCOUNT_LOCKOUT_MINUTES, (int)$user['user_id']]);
            auth_set_last_error('Account temporarily locked due to failed login attempts.');
        } else {
            $pdo->prepare('UPDATE tbl_users SET failed_login_attempts = ? WHERE user_id = ?')
                ->execute([$failedAttempts, (int)$user['user_id']]);
            auth_set_last_error('Invalid username or password.');
        }
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['user_id'];
    $_SESSION['username'] = (string)$user['username'];

    $pdo->prepare('UPDATE tbl_users SET failed_login_attempts = 0, locked_until = NULL, last_login_at = NOW() WHERE user_id = ?')->execute([(int)$user['user_id']]);

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
