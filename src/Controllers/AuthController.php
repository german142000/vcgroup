<?php
namespace App\Controllers;

use App\Services\AuthService;
use App\Utils\JsonResponse;

class AuthController
{
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate input
        $validationErrors = $this->validateRegistrationInput($data);
        if (!empty($validationErrors)) {
            JsonResponse::error(['errors' => $validationErrors], 400);
            return;
        }
        
        try {
            $user = $this->authService->register($data['email'], $data['password']);
            JsonResponse::success(['message' => 'User registered successfully']);
        } catch (\InvalidArgumentException $e) {
            JsonResponse::error($e->getMessage(), 400);
        }
    }

    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate input
        $validationErrors = $this->validateLoginInput($data);
        if (!empty($validationErrors)) {
            JsonResponse::error(['errors' => $validationErrors], 400);
            return;
        }
        
        try {
            $token = $this->authService->login($data['email'], $data['password']);
            JsonResponse::success(['token' => $token]);
        } catch (\InvalidArgumentException $e) {
            JsonResponse::error($e->getMessage(), 401);
        }
    }

    public function logout()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $this->authService->revokeToken($matches[1]);
        }
        
        JsonResponse::success(['message' => 'Successfully logged out']);
    }

    /**
     * Validate registration input data
     */
    private function validateRegistrationInput(array $data): array
    {
        $errors = [];
        
        // Email validation
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } elseif (strlen($data['email']) > 255) {
            $errors['email'] = 'Email must be less than 255 characters';
        }
        
        // Password validation
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } else {
            if (strlen($data['password']) < 8) {
                $errors['password'] = 'Password must be at least 8 characters';
            }
            if (!preg_match('/[A-Z]/', $data['password'])) {
                $errors['password'] = 'Password must contain at least one uppercase letter';
            }
            if (!preg_match('/[a-z]/', $data['password'])) {
                $errors['password'] = 'Password must contain at least one lowercase letter';
            }
            if (!preg_match('/[0-9]/', $data['password'])) {
                $errors['password'] = 'Password must contain at least one number';
            }
            if (!preg_match('/[\W]/', $data['password'])) {
                $errors['password'] = 'Password must contain at least one special character';
            }
            if (strlen($data['password']) > 64) {
                $errors['password'] = 'Password must be less than 64 characters';
            }
        }
        
        return $errors;
    }

    /**
     * Validate login input data
     */
    private function validateLoginInput(array $data): array
    {
        $errors = [];
        
        // Email validation
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        // Password validation
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        }
        
        return $errors;
    }
}