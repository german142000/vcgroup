<?php
use App\Controllers\AuthController;
use App\Controllers\TaskController;
use App\Middlewares\AuthMiddleware;

$container = require __DIR__ . '/../config/container.php';

$router->add('POST', '/api/register', [AuthController::class, 'register']);
$router->add('POST', '/api/login', [AuthController::class, 'login']);
$router->add('POST', '/api/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);

// Задачи
$router->add('GET', '/api/tasks', [TaskController::class, 'index'], [AuthMiddleware::class]);
$router->add('POST', '/api/tasks', [TaskController::class, 'store'], [AuthMiddleware::class]);
$router->add('GET', '/api/tasks/{id}', [TaskController::class, 'show'], [AuthMiddleware::class]);
$router->add('PUT', '/api/tasks/{id}', [TaskController::class, 'update'], [AuthMiddleware::class]);
$router->add('DELETE', '/api/tasks/{id}', [TaskController::class, 'destroy'], [AuthMiddleware::class]);