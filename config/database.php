<?php

return [
    // Change this to "mysql" if you want the app to use the MySQL settings below.
    'driver' => 'sqlite',

    // SQLite stores the whole database in one file. Connection.php creates the
    // folder automatically if it does not exist yet.
    'sqlite_path' => dirname(__DIR__) . '/database/app.sqlite',

    // These values are only used when the driver above is set to "mysql".
    'mysql' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'mvc_finals',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
];
