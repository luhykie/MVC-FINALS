<?php

declare(strict_types=1);

namespace Core\Database;

class QueryBuilder
{
    public static function table(string $table): Model
    {
        throw new \LogicException('Use model classes for ORM queries in this project.');
    }
}
