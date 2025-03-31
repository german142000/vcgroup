<?php
namespace App\Models;

class Task
{
    public ?int $id = null;
    public int $user_id;
    public string $title;
    public string $description;
    public string $status; // 'в работе', 'завершено', 'дедлайн'
    public ?string $deadline = null;
    public string $created_at;
    public string $updated_at;
}