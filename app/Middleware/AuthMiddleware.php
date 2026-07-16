<?php
/**
 * AuthMiddleware — ensure user is logged in
 */
class AuthMiddleware
{
    public static function handle(): void
    {
        if (!Auth::check()) {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
            $path = str_replace($basePath, '', $uri);
            $path = trim(parse_url($path, PHP_URL_PATH), '/');

            $loginUrl = '/login';
            if (str_starts_with($path, 'admin')) {
                $loginUrl = '/admin/login';
            } elseif (str_starts_with($path, 'manager')) {
                $loginUrl = '/manager/login';
            } elseif (str_starts_with($path, 'sr')) {
                $loginUrl = '/sr/login';
            } elseif (str_starts_with($path, 'dsr')) {
                $loginUrl = '/dsr/login';
            }

            Auth::setFlash('error', 'Please log in to continue.');
            header('Location: ' . BASE_URL . $loginUrl);
            exit;
        }
    }
}
