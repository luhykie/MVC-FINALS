<?php

declare(strict_types=1);

use Core\Application;
use Core\Container\Container;
use Core\Http\Router;
use Core\Session;

// Composer autoload mo-load sa classes gikan sa app/, core/, ug vendor/.
require dirname(__DIR__) . '/vendor/autoload.php';

// I-start ang PHP session before gamiton ang auth, flash messages, or CSRF tokens.
Session::start();

// Ang container mo-create sa controller objects ug mo-resolve sa dependencies.
$container = new Container();

// Ang router mo-match sa URL ngadto sa sakto nga controller method.
$router = new Router($container);

// I-register tanan web routes sa router.
require dirname(__DIR__) . '/routes/web.php';

// Ang application modawat sa router ug mo-handle sa current request.
$app = new Application($router);

$app->run();
