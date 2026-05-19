<?php

declare(strict_types=1);

namespace Core\Database;

use PDO;

class Connection
{
    // Holds the single PDO object so every model uses the same database connection.
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        // If a connection was already created, reuse it instead of reconnecting.
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        // Load database settings from config/database.php.
        $database = require dirname(__DIR__, 2) . '/config/database.php';

        if ($database['driver'] === 'mysql') {
            $mysql = $database['mysql'];

            // DSN tells PDO what database driver, host, port, database, and charset to use.
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $mysql['host'],
                $mysql['port'],
                $mysql['database'],
                $mysql['charset']
            );

            // Create the PDO connection for MySQL using username and password.
            self::$connection = new PDO($dsn, $mysql['username'], $mysql['password']);
        } else {
            $path = $database['sqlite_path'];
            $directory = dirname($path);

            // SQLite needs the database folder to exist before it can create/open the file.
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            // Create the PDO connection for SQLite using the database file path.
            self::$connection = new PDO('sqlite:' . $path);
        }

        // Throw exceptions when SQL fails, so controllers can catch database errors.
        self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch rows as associative arrays like ['id' => 1, 'name' => '...'].
        self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return self::$connection;
    }
}
