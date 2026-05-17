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
        if (Auth::check()) {
            $this->redirect('/');
        }

        return $this->render('auth/login', ['title' => 'Login'], 'layouts/auth');
    }

    public function login(): never
    {
        $this->validateCsrf();

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if (Auth::attempt($email, $password)) {
            $name = Auth::user()['name'] ?? 'User';
            Session::flash('success', "Welcome back, {$name}!");
            $this->redirect('/dashboard');
        }

        Session::flash('error', 'Invalid email or password.');
        $this->redirect('/login');
    }

    public function logout(): never
    {
        $this->validateCsrf();
        Auth::logout();
        Session::flash('success', 'You have been logged out.');
        $this->redirect('/login');
    }
}
