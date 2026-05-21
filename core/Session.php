<?php

declare(strict_types=1);

namespace Core;

class Session
{
    public static function start(): void
    {
        // I-start ang PHP session kung wala pay active session.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        // Basahon ang value from session, or default kung missing.
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        // I-save ang value sa session.
        $_SESSION[$key] = $value;
    }

    public static function remove(string $key): void
    {
        // I-delete ang usa ka session value.
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, ?string $message = null): ?string
    {
        // Kung naay message, i-store para sa next page load.
        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return null;
        }

        // Kung walay message, basahon then tangtangon para once ra mo-show.
        $value = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);

        return $value;
    }

    public static function csrfToken(): string
    {
        // Mag-create ug random CSRF token per session kung wala pa.
        if (!isset($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf'];
    }

    public static function verifyCsrf(?string $token): bool
    {
        // hash_equals mo-compare sa tokens safely.
        return is_string($token) && hash_equals($_SESSION['_csrf'] ?? '', $token);
    }
}
