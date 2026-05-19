<?php

declare(strict_types=1);

namespace Core\Http;

class Response
{
    public static function status(int $code): void
    {
        // Set the HTTP status code for the response.
        http_response_code($code);
    }
}
