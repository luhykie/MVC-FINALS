<?php

return [
    'app' => [
        'name' => 'Student Records MVC',
        'base_url' => '',
        'debug' => true,
    ],
    'database' => [
        'driver' => 'sqlite',
        'sqlite_path' => dirname(__DIR__) . '/database/app.sqlite',
        'mysql' => [
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'mvc_finals',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
        ],
    ],
];
