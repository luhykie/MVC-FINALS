<?php

declare(strict_types=1);

use Core\Application;
use Core\Container\Container;
use Core\Http\Router;
use Core\Session;

require dirname(__DIR__) . '/vendor/autoload.php';

Session::start();

$container = new Container();

$router = new Router($container);

require dirname(__DIR__) . '/routes/web.php';

$app = new Application($router);

$app->run();