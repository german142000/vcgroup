<?php
namespace App\Core;

class Router
{
    private $routes = [];
    private $middlewares = [];

    public function add(string $method, string $path, array $handler, array $middlewares = [])
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    public function use(array $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    public function dispatch()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            $pattern = $this->buildPattern($route['path']);
            
            if ($route['method'] === $requestMethod && preg_match($pattern, $requestUri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                try {
                    // Apply global middlewares
                    foreach ($this->middlewares as $middleware) {
                        $this->callMiddleware($middleware);
                    }
                    
                    // Apply route-specific middlewares
                    foreach ($route['middlewares'] as $middleware) {
                        $this->callMiddleware($middleware);
                    }
                    
                    // Call the handler
                    $this->callHandler($route['handler'], $params);
                    return;
                } catch (\Exception $e) {
                    $this->handleError($e);
                    return;
                }
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
    }

    private function buildPattern(string $path): string
    {
        return '#^' . preg_replace('/\{([a-z]+)\}/', '(?P<$1>[^/]+)', $path) . '$#i';
    }

    private function callMiddleware($middleware)
    {
        if (is_string($middleware)) {
            $middleware = [$middleware, 'handle'];
        }
        
        if (!is_array($middleware)) {
            throw new \InvalidArgumentException('Middleware must be string or array');
        }
        
        $class = $middleware[0];
        $method = $middleware[1] ?? 'handle';
        
        $container = require __DIR__ . '/../../config/container.php';
        $instance = $container->get($class);
        $instance->$method();
    }

    private function callHandler(array $handler, array $params)
    {
        $className = is_string($handler[0]) ? $handler[0] : get_class($handler[0]);
        $method = $handler[1];
        
        // Получаем экземпляр класса из контейнера
        $container = require __DIR__ . '/../../config/container.php';
        $instance = $container->get($className);
        
        $instance->$method($params);
    }

    private function handleError(\Exception $e)
    {
        http_response_code(500);
        echo json_encode([
            'error' => $e->getMessage(),
            'trace' => $_ENV['APP_ENV'] === 'dev' ? $e->getTrace() : null
        ]);
    }
}