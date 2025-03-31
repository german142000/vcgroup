<?php
namespace App\Repositories;

use App\Models\User;
use App\Core\Database;

class UserRepository
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db->getConnection();
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM public.users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $user = new User();
        $user->id = $data['id'];
        $user->email = $data['email'];
        $user->password = $data['password'];
        $user->created_at = $data['created_at'];

        return $user;
    }

    public function create(User $user): User
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (email, password) 
            VALUES (:email, :password)
        ");

        $stmt->execute([
            'email' => $user->email,
            'password' => password_hash($user->password, PASSWORD_BCRYPT)
        ]);

        $user->id = $this->db->lastInsertId();
        return $user;
    }
}