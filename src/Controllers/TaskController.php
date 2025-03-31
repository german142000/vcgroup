<?php
namespace App\Controllers;

use App\Services\TaskService;
use App\Utils\JsonResponse;

class TaskController
{
    private $taskService;
    private $userId;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
        $this->userId = $_SERVER['USER_ID'] ?? 0;
    }

    public function index(array $params = [])
    {
        // Validate pagination parameters
        $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'default' => 1]
        ]);
        
        $perPage = filter_var($_GET['per_page'] ?? 10, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 100, 'default' => 10]
        ]);

        // Validate filters
        $allowedFilters = ['status'];
        $filters = array_filter($_GET, fn($key) => in_array($key, $allowedFilters), ARRAY_FILTER_USE_KEY);
        
        // Validate status if provided
        if (isset($filters['status'])) {
            $validStatuses = ['в работе', 'завершено', 'дедлайн'];
            if (!in_array($filters['status'], $validStatuses)) {
                JsonResponse::error('Invalid status value. Allowed values: ' . implode(', ', $validStatuses), 400);
                return;
            }
        }

        $tasks = $this->taskService->getTasks($this->userId, $page, $perPage, $filters);
        JsonResponse::success([
            'tasks' => $tasks,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => count($tasks)
            ]
        ]);
    }

    public function show(array $params)
    {
        // Validate task ID
        if (!isset($params['id']) || !filter_var($params['id'], FILTER_VALIDATE_INT)) {
            JsonResponse::error('Invalid task ID', 400);
            return;
        }

        $task = $this->taskService->getTask($params['id'], $this->userId);

        if (!$task) {
            JsonResponse::error('Task not found', 404);
            return;
        }

        JsonResponse::success(['task' => $task]);
    }

    public function store()
    {
        $data = $this->getAndValidateJsonInput();
        if ($data === null) return;

        // Validate task data
        $validationErrors = $this->validateTaskData($data, true);
        if (!empty($validationErrors)) {
            JsonResponse::error(['errors' => $validationErrors], 400);
            return;
        }

        try {
            $task = $this->taskService->createTask($this->userId, $data);
            JsonResponse::success(['task' => $task], 201);
        } catch (\InvalidArgumentException $e) {
            JsonResponse::error($e->getMessage(), 400);
        }
    }

    public function update(array $params)
    {
        // Validate task ID
        if (!isset($params['id']) || !filter_var($params['id'], FILTER_VALIDATE_INT)) {
            JsonResponse::error('Invalid task ID', 400);
            return;
        }

        $data = $this->getAndValidateJsonInput();
        if ($data === null) return;

        // Validate task data
        $validationErrors = $this->validateTaskData($data, false);
        if (!empty($validationErrors)) {
            JsonResponse::error(['errors' => $validationErrors], 400);
            return;
        }

        $task = $this->taskService->updateTask($params['id'], $this->userId, $data);

        if (!$task) {
            JsonResponse::error('Task not found', 404);
            return;
        }

        JsonResponse::success(['task' => $task]);
    }

    public function destroy(array $params)
    {
        // Validate task ID
        if (!isset($params['id']) || !filter_var($params['id'], FILTER_VALIDATE_INT)) {
            JsonResponse::error('Invalid task ID', 400);
            return;
        }

        $deleted = $this->taskService->deleteTask($params['id'], $this->userId);

        if (!$deleted) {
            JsonResponse::error('Task not found', 404);
            return;
        }

        JsonResponse::success(['message' => 'Task deleted successfully']);
    }

    /**
     * Get and validate JSON input
     */
    private function getAndValidateJsonInput(): ?array
    {
        $input = file_get_contents('php://input');
        
        // Convert encoding to UTF-8 if needed
        $inputEncoding = mb_detect_encoding($input, mb_detect_order(), true);
        if ($inputEncoding !== 'UTF-8') {
            $input = mb_convert_encoding($input, 'UTF-8', $inputEncoding);
        }

        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            JsonResponse::error('Invalid JSON data: ' . json_last_error_msg(), 400);
            return null;
        }

        return $data;
    }

    /**
     * Validate task data based on database schema
     */
    private function validateTaskData(array $data, bool $isCreate): array
    {
        $errors = [];

        // Title validation (required for create, optional for update)
        if ($isCreate && empty($data['title'])) {
            $errors['title'] = 'Title is required';
        }
        if (isset($data['title']) && strlen($data['title']) > 255) {
            $errors['title'] = 'Title must be 255 characters or less';
        }

        // Description validation
        if (isset($data['description']) && !is_string($data['description'])) {
            $errors['description'] = 'Description must be a string';
        }

        // Status validation
        if (isset($data['status'])) {
            $validStatuses = ['в работе', 'завершено', 'дедлайн'];
            if (!in_array($data['status'], $validStatuses)) {
                $errors['status'] = 'Invalid status. Allowed values: ' . implode(', ', $validStatuses);
            }
        }

        // Deadline validation
        if (isset($data['deadline'])) {
            if (!strtotime($data['deadline'])) {
                $errors['deadline'] = 'Invalid deadline format';
            } elseif (strtotime($data['deadline']) < time()) {
                $errors['deadline'] = 'Deadline cannot be in the past';
            }
        }

        return $errors;
    }
}