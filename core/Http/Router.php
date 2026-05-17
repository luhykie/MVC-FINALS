<?php

declare(strict_types=1);

namespace Core\Http;

use Core\Container\Container;
use Core\View\Engine;

class Router
{
    private array $routes = [];

    public function __construct(
        private readonly ?Container $container = null
    ) {}

    public function get(string $uri, array $handler): void
    {
        $this->add('GET', $uri, $handler);
    }

    public function post(string $uri, array $handler): void
    {
        $this->add('POST', $uri, $handler);
    }

    private function add(string $method, string $uri, array $handler): void
    {
        $uri = trim($uri, '/') ?: '/';

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
        $uri = trim($uri, '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                [$class, $action] = $route['handler'];

                $controller = $this->resolveController($class);

                echo $controller->$action(...array_values($params));
                return;
            }
        }

        http_response_code(404);
        echo Engine::render('/errors/404', ['title' => 'Page not found']);
    }

    private function resolveController(string $class): object
    {
        if ($this->container !== null && $this->container->has($class)) {
            return $this->container->make($class);
        }

        return new $class();
    }
}