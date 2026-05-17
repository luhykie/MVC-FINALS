<?php

declare(strict_types=1);

use Core\Session;

require dirname(__DIR__) . '/vendor/autoload.php';

Session::start();

$router = require dirname(__DIR__) . '/routes/web.php';

$router->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
