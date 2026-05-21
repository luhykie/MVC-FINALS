<?php

declare(strict_types=1);

namespace Core\Database;

use PDO;

class Connection
{
    // Mo-hold sa single PDO object para same database connection ang tanan model.
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        // Kung naa nay connection, gamiton balik instead mag-reconnect.
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        // I-load ang database settings from config/database.php.
        $database = require dirname(__DIR__, 2) . '/config/database.php';

        if ($database['driver'] === 'mysql') {
            $mysql = $database['mysql'];

            // DSN mo-tell sa PDO unsa nga driver, host, port, database, ug charset gamiton.
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $mysql['host'],
                $mysql['port'],
                $mysql['database'],
                $mysql['charset']
            );

            // I-create ang PDO connection for MySQL gamit username ug password.
            self::$connection = new PDO($dsn, $mysql['username'], $mysql['password']);
        } else {
            $path = $database['sqlite_path'];
            $directory = dirname($path);

            // Kinahanglan sa SQLite nga naa ang database folder before maka-create/open sa file.
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            // I-create ang PDO connection for SQLite gamit ang database file path.
            self::$connection = new PDO('sqlite:' . $path);
        }

        // Mag-throw ug exceptions kung SQL fails para ma-catch sa controllers ang database errors.
        self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // I-fetch ang rows as associative arrays like ['id' => 1, 'name' => '...'].
        self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return self::$connection;
    }
}
