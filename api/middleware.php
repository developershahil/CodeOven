<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

function api_json(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function api_require_auth(): int
{
    $userId = current_user_id();
    if (!$userId) {
        api_json(['success' => false, 'message' => 'Unauthorized'], 401);
    }
    return (int)$userId;
}

function api_require_csrf_for_write(): void
{
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        return;
    }

    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf_token'] ?? null);
    if (!csrf_validate(is_string($token) ? $token : null)) {
        api_json(['success' => false, 'message' => 'Invalid CSRF token'], 419);
    }
}
