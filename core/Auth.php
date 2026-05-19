<?php

declare(strict_types=1);

namespace Core;

use App\Models\User;

class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        // Look for a user row with the submitted email.
        $user = (new User())->findByEmail($email);

        // password_verify compares the plain password with the hashed password in the database.
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        // Store only safe user details in the session, never the password hash.
        Session::set('user', [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ]);

        return true;
    }

    public static function check(): bool
    {
        // A user is logged in when their user data exists in the session.
        return Session::get('user') !== null;
    }

    public static function user(): ?array
    {
        // Returns the logged-in user's session data, or null if not logged in.
        return Session::get('user');
    }

    public static function logout(): void
    {
        // Removing the user session logs the user out.
        Session::remove('user');
    }
}
