<?php

declare(strict_types=1);

namespace Core;

class Session
{
    public static function start(): void
    {
        // Start a PHP session only if one is not already active.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        // Read a value from the session, or return a default when it is missing.
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        // Save a value into the session.
        $_SESSION[$key] = $value;
    }

    public static function remove(string $key): void
    {
        // Delete one session value.
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, ?string $message = null): ?string
    {
        // When a message is provided, store it for the next page load.
        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return null;
        }

        // When no message is provided, read then remove it so it appears once.
        $value = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);

        return $value;
    }

    public static function csrfToken(): string
    {
        // Create one random CSRF token per session if it does not exist yet.
        if (!isset($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf'];
    }

    public static function verifyCsrf(?string $token): bool
    {
        // hash_equals compares tokens safely.
        return is_string($token) && hash_equals($_SESSION['_csrf'] ?? '', $token);
    }
}
