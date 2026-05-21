<?php

return [
    // Ilisi ni ug "mysql" kung gusto nimo gamiton ang MySQL settings below.
    'driver' => 'sqlite',

    // SQLite mo-store sa whole database sa usa ka file. Connection.php mo-create
    // sa folder automatically kung wala pa nag-exist.
    'sqlite_path' => dirname(__DIR__) . '/database/app.sqlite',

    // Kini nga values gamiton ra kung ang driver above kay "mysql".
    'mysql' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'mvc_finals',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
];
