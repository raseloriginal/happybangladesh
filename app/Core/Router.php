<?php
/**
 * Router — simple pattern-matching front controller router
 */
class Router
{
    private array $routes = [];

    // ── Register routes ───────────────────────────────────────
    public function get(string $path, callable|array $handler): void
    {
        $this->routes[] = ['GET', $path, $handler];
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->routes[] = ['POST', $path, $handler];
    }

    public function any(string $path, callable|array $handler): void
    {
        $this->routes[] = ['ANY', $path, $handler];
    }

    // ── Dispatch ──────────────────────────────────────────────
    public function dispatch(string $url, string $method): void
    {
        $url    = '/' . trim($url, '/');
        $method = strtoupper($method);

        foreach ($this->routes as [$routeMethod, $pattern, $handler]) {
            if ($routeMethod !== 'ANY' && $routeMethod !== $method) {
                continue;
            }

            $regex = $this->buildRegex($pattern);
            if (preg_match($regex, $url, $matches)) {
                // Extract named params
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->callHandler($handler, $params);
                return;
            }
        }

        // 404
        http_response_code(404);
        echo $this->render404();
    }

    // ── Convert route pattern to regex ────────────────────────
    private function buildRegex(string $pattern): string
    {
        $pattern = preg_replace('/\{([a-z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $pattern . '$#';
    }

    // ── Call the handler ──────────────────────────────────────
    private function callHandler(callable|array $handler, array $params): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        // Array: [ControllerClass, method]
        [$class, $method] = $handler;
        if (!class_exists($class)) {
            http_response_code(500);
            die("Controller class '{$class}' not found.");
        }
        $controller = new $class();
        if (!method_exists($controller, $method)) {
            http_response_code(500);
            die("Method '{$method}' not found in '{$class}'.");
        }
        call_user_func_array([$controller, $method], $params);
    }

    // ── 404 page ──────────────────────────────────────────────
    private function render404(): string
    {
        return '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>404 Not Found</title>
<script src="https://cdn.tailwindcss.com"></script></head>
<body class="flex items-center justify-center min-h-screen bg-gray-50">
  <div class="text-center">
    <div class="text-8xl font-bold text-blue-600 mb-4">404</div>
    <h1 class="text-2xl font-semibold text-gray-700 mb-2">Page Not Found</h1>
    <p class="text-gray-500 mb-6">The page you are looking for does not exist.</p>
    <a href="' . BASE_URL . '" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Go Home</a>
  </div>
</body></html>';
    }
}
