<?php
namespace App\Middlewares;

use App\Services\AuthService;

class AuthMiddleware
{
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function handle()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Authorization token is required']);
            exit;
        }

        try {
            $token = $matches[1];
            $decoded = $this->authService->validateToken($token);
            $_SERVER['USER_ID'] = $decoded['sub'];
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }
}