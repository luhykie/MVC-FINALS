<?php

declare(strict_types=1);

namespace Core\Http;

class Request
{
    public function method(): string
    {
        // Mo-return sa current HTTP method, default kay GET.
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public function path(): string
    {
        // Mo-return ra sa path from URL, walay query parameters.
        return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    }
}
