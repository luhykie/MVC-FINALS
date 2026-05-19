<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Auth;
use Core\Controller;
use Core\Session;

class AuthController extends Controller
{
    public function showLogin(): string
    {
        // Logged-in users should not see the login form again.
        if (Auth::check()) {
            $this->redirect('/');
        }

        // Login page uses a smaller auth layout instead of the main app layout.
        return $this->render('auth/login', ['title' => 'Login'], 'layouts/auth');
    }

    public function login(): never
    {
        // Protect login POST from fake form submissions.
        $this->validateCsrf();

        // Read submitted credentials from the login form.
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        // Auth::attempt checks the database and stores the user in the session on success.
        if (Auth::attempt($email, $password)) {
            $name = Auth::user()['name'] ?? 'User';
            Session::flash('success', "Welcome back, {$name}!");
            $this->redirect('/dashboard');
        }

        // Invalid credentials go back to login with a one-time error message.
        Session::flash('error', 'Invalid email or password.');
        $this->redirect('/login');
    }

    public function logout(): never
    {
        // Logout is a POST action, so it also checks CSRF.
        $this->validateCsrf();
        Auth::logout();
        Session::flash('success', 'You have been logged out.');
        $this->redirect('/login');
    }
}
