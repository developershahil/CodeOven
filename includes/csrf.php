<?php
declare(strict_types=1);

require_once __DIR__ . '/session.php';

function csrf_token(): string
{
    app_session_start();

    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrf_validate(?string $token): bool
{
    app_session_start();

    if (!isset($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token'])) {
        return false;
    }

    if (!is_string($token) || $token === '') {
        return false;
    }

    return hash_equals($_SESSION['_csrf_token'], $token);
}

function csrf_rotate(): void
{
    app_session_start();
    $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
}
