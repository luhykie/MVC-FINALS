<?php

declare(strict_types=1);

namespace Core\Middleware;

use Core\Auth;
use Core\Session;

class AuthMiddleware
{
    public function handle(): void
    {
        // Middleware version of requireAuth: block guests before protected code runs.
        if (!Auth::check()) {
            Session::flash('error', 'Please log in to continue.');
            header('Location: /login');
            exit;
        }
    }
}
