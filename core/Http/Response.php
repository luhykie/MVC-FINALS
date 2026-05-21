<?php

declare(strict_types=1);

namespace Core\Http;

class Response
{
    public static function status(int $code): void
    {
        // I-set ang HTTP status code para sa response.
        http_response_code($code);
    }
}
