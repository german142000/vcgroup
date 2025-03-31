<?php
namespace App\Services;

use App\Models\Task;
use App\Repositories\TaskRepository;

class TaskService
{
    private $taskRepository;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function getTask(int $id, int $userId): ?Task
    {
        return $this->taskRepository->findById($id, $userId);
    }

    public function getTasks(int $userId, int $page, int $perPage, array $filters = []): array
    {
        return $this->taskRepository->findAll($userId, $page, $perPage, $filters);
    }

    public function createTask(int $userId, array $data): Task
    {
        // Валидация обязательных полей
        if (empty($data['title'])) {
            throw new \InvalidArgumentException('Task title is required');
        }
    
        $task = new Task();
        $task->user_id = $userId;
        $task->title = $data['title'];
        $task->description = $data['description'] ?? null;
        $task->status = $data['status'] ?? 'в работе';
        $task->deadline = $data['deadline'] ?? null;
    
        return $this->taskRepository->create($task);
    }

    public function updateTask(int $id, int $userId, array $data): ?Task
    {
        $task = $this->taskRepository->findById($id, $userId);

        if (!$task) {
            return null;
        }

        if (isset($data['title'])) {
            $task->title = $data['title'];
        }

        if (isset($data['description'])) {
            $task->description = $data['description'];
        }

        if (isset($data['status'])) {
            $task->status = $data['status'];
        }

        if (isset($data['deadline'])) {
            $task->deadline = $data['deadline'];
        }

        return $this->taskRepository->update($task);
    }

    public function deleteTask(int $id, int $userId): bool
    {
        return $this->taskRepository->delete($id, $userId);
    }
}