<?php

declare(strict_types=1);

namespace Core\View;

class Engine
{
    public static function render(string $view, array $data = [], string $layout = 'layouts/app'): string
    {
        // Convert a view name like "students/index" into its PHP file path.
        $viewPath = dirname(__DIR__, 2) . '/app/Views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View [{$view}] was not found.");
        }

        // Make array keys available as variables in the view, such as $title.
        extract($data, EXTR_SKIP);

        // Capture the view output into $content instead of printing it immediately.
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Some pages can skip a layout by passing an empty layout string.
        if ($layout === '') {
            return $content;
        }

        // Render the layout. The layout can echo $content where the page should appear.
        ob_start();
        require dirname(__DIR__, 2) . '/app/Views/' . $layout . '.php';

        return ob_get_clean();
    }

    public static function e(?string $value): string
    {
        // Escape text before showing it in HTML to prevent XSS.
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
