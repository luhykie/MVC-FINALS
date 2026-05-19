<?php

declare(strict_types=1);

namespace Core;

use Core\View\Engine;

class Controller
{
    protected function render(string $view, array $data = [], string $layout = 'layouts/app'): string
    {
        // Controllers call this to load a view inside a layout.
        return Engine::render($view, $data, $layout);
    }

    protected function redirect(string $path): never
    {
        // Send the browser to another URL and stop running the current action.
        header('Location: ' . $path);
        exit;
    }

    protected function requireAuth(): void
    {
        // Protected pages must redirect guests to the login page.
        if (!Auth::check()) {
            Session::flash('error', 'Please log in to continue.');
            $this->redirect('/login');
        }
    }

    protected function validateCsrf(): void
    {
        // CSRF token proves the POST request came from one of this app's forms.
        if (!Session::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Invalid security token. Please go back and try again.');
        }
    }
}
