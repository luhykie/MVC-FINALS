<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\StudentController;
use Core\Http\Router;

$router = new Router();

$router->get('/', [DashboardController::class, 'index']);
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/students', [StudentController::class, 'index']);
$router->get('/students/history', [StudentController::class, 'history']);
$router->get('/students/create', [StudentController::class, 'create']);
$router->post('/students', [StudentController::class, 'store']);
$router->get('/students/{id}', [StudentController::class, 'show']);
$router->get('/students/{id}/edit', [StudentController::class, 'edit']);
$router->post('/students/{id}/update', [StudentController::class, 'update']);
$router->post('/students/{id}/delete', [StudentController::class, 'destroy']);

return $router;
