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
        // Basahon ang HTTP method, like GET or POST.
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Basahon ra ang path part sa URL, walay query strings.
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        // Ipadala ang request sa router para matawag ang matching controller.
        $this->router->dispatch($method, $uri);
    }
}
