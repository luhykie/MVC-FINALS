<?php

declare(strict_types=1);

namespace Core;

use Core\Http\Router;

final class Application
{
    public function __construct(
        private readonly Router $router
    ) {}

    public function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        $this->router->dispatch($method, $uri);
    }
}