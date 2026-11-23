<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT) !== false;
}

function requireLogin(string $loginPath = 'login.php'): void
{
    if (!isLoggedIn()) {
        header('Location: ' . $loginPath);
        exit;
    }
}

function currentUserId(): int
{
    return isLoggedIn() ? (int) $_SESSION['user_id'] : 0;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && is_string($_POST['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function requestCode(array $allowed): string
{
    foreach (['success', 'registered', 'error'] as $key) {
        $value = $_GET[$key] ?? null;
        if (is_string($value) && in_array($value, $allowed, true)) {
            return $value;
        }
    }
    return '';
}
