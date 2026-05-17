<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class User
{
    public function findByEmail(string $email): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user ?: null;
    }
}
