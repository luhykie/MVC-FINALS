<?php

declare(strict_types=1);

namespace Core\View;

class Alert
{
    public static function render(?string $message, string $type = 'info'): string
    {
        if (!$message) {
            return '';
        }

        $currentPath = self::e(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

        return '
            <div role="alert" style="border: 1px solid #000; padding: 5px 8px; margin: 6px 0; display: inline-block;">
                <span>' . self::e($message) . '</span>
                <a href="' . $currentPath . '">OK</a>
            </div>
        ';
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
