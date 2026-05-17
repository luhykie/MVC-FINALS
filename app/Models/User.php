<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database\Model;

class User extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        $user = self::firstWhere('email', $email);

        return $user?->toArray();
    }
}
