<?php
use App\Core\Database;
use App\Repositories\UserRepository;
use App\Repositories\TaskRepository;
use App\Services\AuthService;
use App\Services\TaskService;
use App\Controllers\AuthController;
use App\Controllers\TaskController;
use App\Middlewares\AuthMiddleware;

return new class {
    private $instances = [];

    public function get(string $className)
    {
        if (!isset($this->instances[$className])) {
            $this->instances[$className] = $this->createInstance($className);
        }
        return $this->instances[$className];
    }

    private function createInstance(string $className)
    {
        switch ($className) {
            case Database::class:
                return new Database();
            case UserRepository::class:
                return new UserRepository($this->get(Database::class));
            case TaskRepository::class:
                return new TaskRepository($this->get(Database::class));
            case AuthService::class:
                return new AuthService(
                    $this->get(UserRepository::class),
                    $this->get(Database::class)
                );
            case TaskService::class:
                return new TaskService($this->get(TaskRepository::class));
            case AuthController::class:
                return new AuthController($this->get(AuthService::class));
            case TaskController::class:
                return new TaskController($this->get(TaskService::class));
            case AuthMiddleware::class:
                return new AuthMiddleware($this->get(AuthService::class));
            default:
                throw new \RuntimeException("Unknown class: $className");
        }
    }
};