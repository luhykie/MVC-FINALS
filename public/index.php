<?php

declare(strict_types=1);

use Core\Application;
use Core\Container\Container;
use Core\Http\Router;
use Core\Session;

// Composer autoload loads all classes from app/, core/, and vendor/.
require dirname(__DIR__) . '/vendor/autoload.php';

// Start the PHP session before auth, flash messages, or CSRF tokens are used.
Session::start();

// The container creates controller objects and can resolve their dependencies.
$container = new Container();

// The router matches the requested URL to the correct controller method.
$router = new Router($container);

// Register all web routes into the router.
require dirname(__DIR__) . '/routes/web.php';

// Application receives the router and starts handling the current request.
$app = new Application($router);

$app->run();
