<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database\Model;

class User extends Model
{
    // I-connect ni nga model sa "users" table.
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        // Gamiton during login para pangitaon ang account sa submitted email.
        $user = self::firstWhere('email', $email);

        return $user?->toArray();
    }
}
