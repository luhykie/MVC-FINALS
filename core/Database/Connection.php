<?php

declare(strict_types=1);

namespace Core\Database;

use PDO;

class Connection
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $database = require dirname(__DIR__, 2) . '/config/database.php';

        if ($database['driver'] === 'mysql') {
            $mysql = $database['mysql'];
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $mysql['host'],
                $mysql['port'],
                $mysql['database'],
                $mysql['charset']
            );
            self::$connection = new PDO($dsn, $mysql['username'], $mysql['password']);
        } else {
            $path = $database['sqlite_path'];
            $directory = dirname($path);

            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            self::$connection = new PDO('sqlite:' . $path);
        }

        self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return self::$connection;
    }
}
