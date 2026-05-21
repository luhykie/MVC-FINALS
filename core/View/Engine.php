<?php

declare(strict_types=1);

namespace Core\View;

class Engine
{
    public static function render(string $view, array $data = [], string $layout = 'layouts/app'): string
    {
        // I-convert ang view name like "students/index" into PHP file path.
        $viewPath = dirname(__DIR__, 2) . '/app/Views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View [{$view}] was not found.");
        }

        // Himuon nga variables sa view ang array keys, like $title.
        extract($data, EXTR_SKIP);

        // I-capture ang view output sa $content instead nga i-print dayon.
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Some pages pwede mo-skip sa layout by passing empty layout string.
        if ($layout === '') {
            return $content;
        }

        // I-render ang layout. Ang layout mo-echo sa $content kung asa dapat mo-appear ang page.
        ob_start();
        require dirname(__DIR__, 2) . '/app/Views/' . $layout . '.php';

        return ob_get_clean();
    }

    public static function e(?string $value): string
    {
        // I-escape ang text before ipakita sa HTML para prevent XSS.
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
