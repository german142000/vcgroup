<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Database;

// Загрузка переменных окружения
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Инициализация базы данных
$db = new Database();

// Создание маршрутизатора
$router = new Router();

// Подключение маршрутов
require_once __DIR__ . '/../routes/api.php';

// Запуск маршрутизатора
$router->dispatch();
