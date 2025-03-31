<?php
namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Core\Database;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService
{
    private $userRepository;
    private $secretKey;
    private $db;

    public function __construct(UserRepository $userRepository, Database $database)
    {
        $this->userRepository = $userRepository;
        $this->secretKey = $_ENV['JWT_SECRET'];
        $this->db = $database->getConnection();
    }

    public function register(string $email, string $password): User
    {
        if ($this->userRepository->findByEmail($email)) {
            throw new \InvalidArgumentException("User with this email already exists");
        }

        $user = new User();
        $user->email = $email;
        $user->password = $password;

        return $this->userRepository->create($user);
    }

    public function login(string $email, string $password): string
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !password_verify($password, $user->password)) {
            throw new \InvalidArgumentException("Invalid email or password");
        }

        $payload = [
            'iss' => $_ENV['APP_URL'],
            'aud' => $_ENV['APP_URL'],
            'iat' => time(),
            'exp' => time() + 3600, // 1 hour
            'sub' => $user->id,
            'email' => $user->email
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    public function revokeToken(string $token): void
    {
        $decoded = $this->validateToken($token);
        $expiresAt = date('Y-m-d H:i:s', $decoded['exp']);

        $stmt = $this->db->prepare(
            "INSERT INTO revoked_tokens (token, expires_at) VALUES (:token, :expires_at)"
        );
        $stmt->execute([
            'token' => $token,
            'expires_at' => $expiresAt
        ]);
    }

    public function isTokenRevoked(string $token): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM revoked_tokens WHERE token = :token LIMIT 1"
        );
        $stmt->execute(['token' => $token]);

        return (bool) $stmt->fetch();
    }

    public function validateToken(string $token): array
    {
        if ($this->isTokenRevoked($token)) {
            throw new \RuntimeException("Token has been revoked");
        }
        
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            throw new \RuntimeException("Invalid token: " . $e->getMessage());
        }
    }
}