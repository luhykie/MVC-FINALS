<?php

declare(strict_types=1);

namespace Core\View;

class Engine
{
    public static function render(string $view, array $data = [], string $layout = 'layouts/app'): string
    {
        $viewPath = dirname(__DIR__, 2) . '/app/Views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View [{$view}] was not found.");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        if ($layout === '') {
            return $content;
        }

        ob_start();
        require dirname(__DIR__, 2) . '/app/Views/' . $layout . '.php';

        return ob_get_clean();
    }

    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
