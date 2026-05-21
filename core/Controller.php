<?php

declare(strict_types=1);

namespace Core;

use Core\View\Engine;

class Controller
{
    protected function render(string $view, array $data = [], string $layout = 'layouts/app'): string
    {
        // Tawagon ni sa controllers para i-load ang view sulod sa layout.
        return Engine::render($view, $data, $layout);
    }

    protected function redirect(string $path): never
    {
        // Ipadala ang browser sa lain URL ug undangon ang current action.
        header('Location: ' . $path);
        exit;
    }

    protected function requireAuth(): void
    {
        // Protected pages dapat mo-redirect sa guests ngadto sa login page.
        if (!Auth::check()) {
            Session::flash('error', 'Please log in to continue.');
            $this->redirect('/login');
        }
    }

    protected function validateCsrf(): void
    {
        // CSRF token mo-prove nga ang POST request gikan sa app form.
        if (!Session::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Invalid security token. Please go back and try again.');
        }
    }
}
