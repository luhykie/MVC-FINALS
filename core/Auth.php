<?php

declare(strict_types=1);

namespace Core;

use App\Models\User;

class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        // Pangitaon ang user row gamit ang submitted email.
        $user = (new User())->findByEmail($email);

        // password_verify mo-compare sa plain password ug hashed password sa database.
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        // I-store ra ang safe user details sa session, dili ang password hash.
        Session::set('user', [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ]);

        return true;
    }

    public static function check(): bool
    {
        // Logged in ang user kung naa iyang user data sa session.
        return Session::get('user') !== null;
    }

    public static function user(): ?array
    {
        // Mo-return sa logged-in user's session data, or null kung not logged in.
        return Session::get('user');
    }

    public static function logout(): void
    {
        // Pag-remove sa user session kay mo-logout sa user.
        Session::remove('user');
    }
}
