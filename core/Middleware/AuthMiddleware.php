<?php

declare(strict_types=1);

namespace Core\Middleware;

use Core\Auth;
use Core\Session;

class AuthMiddleware
{
    public function handle(): void
    {
        if (!Auth::check()) {
            Session::flash('error', 'Please log in to continue.');
            header('Location: /login');
            exit;
        }
    }
}
