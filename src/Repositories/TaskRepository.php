<?php
namespace App\Repositories;

use App\Models\Task;
use App\Core\Database;

class TaskRepository
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db->getConnection();
    }

    public function findById(int $id, int $userId): ?Task
    {
        $stmt = $this->db->prepare("
            SELECT * FROM tasks 
            WHERE id = :id AND user_id = :user_id
        ");
        
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->hydrateTask($data);
    }

    public function findAll(int $userId, int $page = 1, int $limit = 10, array $filters = []): array
    {
        $offset = ($page - 1) * $limit;
        $where = ['user_id = :user_id'];
        $params = ['user_id' => $userId];

        if (!empty($filters['status'])) {
            $where[] = 'status = :status';
            $params['status'] = $filters['status'];
        }

        $whereClause = implode(' AND ', $where);

        $stmt = $this->db->prepare("
            SELECT * FROM tasks 
            WHERE $whereClause
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $tasks = [];

        while ($data = $stmt->fetch()) {
            $tasks[] = $this->hydrateTask($data);
        }

        return $tasks;
    }

    public function create(Task $task): Task
    {
        $stmt = $this->db->prepare("
            INSERT INTO tasks (user_id, title, description, status, deadline)
            VALUES (:user_id, :title, :description, :status, :deadline)
        ");


        $stmt->execute([
            'user_id' => $task->user_id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'deadline' => $task->deadline
        ]);

        $task->id = $this->db->lastInsertId();
        return $task;
    }

    public function update(Task $task): Task
    {
        $stmt = $this->db->prepare("
            UPDATE tasks 
            SET title = :title, 
                description = :description, 
                status = :status, 
                deadline = :deadline,
                updated_at = NOW()
            WHERE id = :id AND user_id = :user_id
        ");

        $stmt->execute([
            'id' => $task->id,
            'user_id' => $task->user_id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'deadline' => $task->deadline
        ]);

        return $task;
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM tasks 
            WHERE id = :id AND user_id = :user_id
        ");

        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    private function hydrateTask(array $data): Task
    {
        $task = new Task();
        $task->id = $data['id'];
        $task->user_id = $data['user_id'];
        $task->title = $data['title'];
        $task->description = $data['description'];
        $task->status = $data['status'];
        $task->deadline = $data['deadline'];
        $task->created_at = $data['created_at'];
        $task->updated_at = $data['updated_at'];

        return $task;
    }
}