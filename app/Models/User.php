<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database\Model;

class User extends Model
{
    // Connects this model to the "users" table.
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        // Used during login to find the account that owns the submitted email.
        $user = self::firstWhere('email', $email);

        return $user?->toArray();
    }
}
