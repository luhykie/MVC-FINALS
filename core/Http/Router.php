<?php

declare(strict_types=1);

namespace Core\Http;

use Core\Container\Container;
use Core\View\Engine;

class Router
{
    // Stores every route registered in routes/web.php.
    private array $routes = [];

    public function __construct(
        private readonly ?Container $container = null
    ) {}

    public function get(string $uri, array $handler): void
    {
        // Register a route that responds to GET requests.
        $this->add('GET', $uri, $handler);
    }

    public function post(string $uri, array $handler): void
    {
        // Register a route that responds to POST requests.
        $this->add('POST', $uri, $handler);
    }

    private function add(string $method, string $uri, array $handler): void
    {
        // Normalize routes so "/students/" and "students" are treated consistently.
        $uri = trim($uri, '/') ?: '/';

        // Convert route parameters like {id} into named regex groups.
        $pattern = preg_replace(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
            '(?P<$1>[^/]+)',
            $uri
        );

        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'pattern' => '#^' . $pattern . '$#',
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        // Normalize the requested URL path before matching.
        $uri = trim($uri, '/') ?: '/';

        foreach ($this->routes as $route) {
            // Skip routes that use a different HTTP method.
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                // Keep only named route parameters, for example ['id' => '3'].
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                [$class, $action] = $route['handler'];

                // Create the controller, then call the matched action method.
                $controller = $this->resolveController($class);

                echo $controller->$action(...array_values($params));
                return;
            }
        }

        // No route matched, so render the 404 page.
        http_response_code(404);
        echo Engine::render('/errors/404', ['title' => 'Page not found']);
    }

    private function resolveController(string $class): object
    {
        // Prefer the container so dependencies can be injected automatically.
        if ($this->container !== null && $this->container->has($class)) {
            return $this->container->make($class);
        }

        // Fallback for simple controllers with no dependencies.
        return new $class();
    }
}
