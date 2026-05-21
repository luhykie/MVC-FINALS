<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\StudentController;

// Dashboard routes. Ang "/" ug "/dashboard" pareho ra ug page.
$router->get('/', [DashboardController::class, 'index']);
$router->get('/dashboard', [DashboardController::class, 'index']);

// Authentication routes para sa login ug logout.
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

// Student CRUD routes: list, create, read, update, delete, ug deleted history.
$router->get('/students', [StudentController::class, 'index']);
$router->get('/students/history', [StudentController::class, 'history']);
$router->get('/students/create', [StudentController::class, 'create']);
$router->post('/students', [StudentController::class, 'store']);
$router->get('/students/{id}', [StudentController::class, 'show']);
$router->get('/students/{id}/edit', [StudentController::class, 'edit']);
$router->post('/students/{id}/update', [StudentController::class, 'update']);
$router->post('/students/{id}/delete', [StudentController::class, 'destroy']);
