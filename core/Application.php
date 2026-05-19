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
        // Read the HTTP method, such as GET or POST.
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Read only the path part of the URL, without query strings.
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        // Send the request to the router so it can call the matching controller.
        $this->router->dispatch($method, $uri);
    }
}
