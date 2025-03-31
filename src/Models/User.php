<?php
namespace App\Models;

class User
{
    public ?int $id = null;
    public string $email;
    public string $password;
    public string $created_at;
}