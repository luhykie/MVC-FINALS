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
        // Logged-in users dili na dapat makita ang login form again.
        if (Auth::check()) {
            $this->redirect('/');
        }

        // Login page gamit ug smaller auth layout instead sa main app layout.
        return $this->render('auth/login', ['title' => 'Login'], 'layouts/auth');
    }

    public function login(): never
    {
        // Protektahan ang login POST from fake form submissions.
        $this->validateCsrf();

        // Basahon ang submitted credentials gikan sa login form.
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        // Auth::attempt mo-check sa database ug mo-store sa user sa session kung success.
        if (Auth::attempt($email, $password)) {
            $name = Auth::user()['name'] ?? 'User';
            Session::flash('success', "Welcome back, {$name}!");
            $this->redirect('/dashboard');
        }

        // Kung invalid credentials, balik sa login with one-time error message.
        Session::flash('error', 'Invalid email or password.');
        $this->redirect('/login');
    }

    public function logout(): never
    {
        // Logout kay POST action, so mo-check pud ug CSRF.
        $this->validateCsrf();
        Auth::logout();
        Session::flash('success', 'You have been logged out.');
        $this->redirect('/login');
    }
}
