<?php

declare(strict_types=1);

namespace Core\Http;

use Core\Container\Container;
use Core\View\Engine;

class Router
{
    // Mo-store sa tanan route nga registered sa routes/web.php.
    private array $routes = [];

    public function __construct(
        private readonly ?Container $container = null
    ) {}

    public function get(string $uri, array $handler): void
    {
        // I-register ang route nga mo-respond sa GET requests.
        $this->add('GET', $uri, $handler);
    }

    public function post(string $uri, array $handler): void
    {
        // I-register ang route nga mo-respond sa POST requests.
        $this->add('POST', $uri, $handler);
    }

    private function add(string $method, string $uri, array $handler): void
    {
        // I-normalize ang routes para "/students/" ug "students" same ra treatment.
        $uri = trim($uri, '/') ?: '/';

        // I-convert ang route parameters like {id} into named regex groups.
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
        // I-normalize ang requested URL path before matching.
        $uri = trim($uri, '/') ?: '/';

        foreach ($this->routes as $route) {
            // I-skip ang routes nga lahi ug HTTP method.
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                // I-keep ra ang named route parameters, example ['id' => '3'].
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                [$class, $action] = $route['handler'];

                // I-create ang controller, then tawagon ang matched action method.
                $controller = $this->resolveController($class);

                echo $controller->$action(...array_values($params));
                return;
            }
        }

        // Kung walay route nga ni-match, i-render ang 404 page.
        http_response_code(404);
        echo Engine::render('/errors/404', ['title' => 'Page not found']);
    }

    private function resolveController(string $class): object
    {
        // Prefer ang container para ma-inject automatically ang dependencies.
        if ($this->container !== null && $this->container->has($class)) {
            return $this->container->make($class);
        }

        // Fallback para sa simple controllers nga walay dependencies.
        return new $class();
    }
}
