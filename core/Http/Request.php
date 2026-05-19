<?php

declare(strict_types=1);

namespace Core\Http;

class Request
{
    public function method(): string
    {
        // Returns the current HTTP method, defaulting to GET.
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public function path(): string
    {
        // Returns only the path from the URL, without query parameters.
        return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    }
}
