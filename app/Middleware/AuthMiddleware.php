<?php
/**
 * AuthMiddleware — ensure user is logged in.
 * Also validates session against DB (for force-logout support)
 * and updates last_active_at timestamp.
 */
class AuthMiddleware
{
    public static function handle(): void
    {
        if (!Auth::check()) {
            $trueUri = $_SERVER['HTTP_X_REQUEST_URI'] ?? $_SERVER['REQUEST_URI'] ?? '/';
            $urlPath = parse_url($trueUri, PHP_URL_PATH) ?? '/';

            $basePath = parse_url(BASE_URL, PHP_URL_PATH);
            if (!empty($basePath) && $basePath !== '/' && strpos($urlPath, $basePath) === 0) {
                $urlPath = substr($urlPath, strlen($basePath));
            }

            $url = '/' . ltrim($urlPath, '/');
            if (strpos($url, '/public/') === 0) {
                $url = substr($url, 7);
            }
            if ($url === '/public') {
                $url = '/';
            }
            if (strpos($url, '/index.php/') === 0) {
                $url = substr($url, 10);
            }
            if ($url === '/index.php') {
                $url = '/';
            }
            $path = trim($url, '/');

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

        // ── Check if session was force-logged-out by admin ────
        if (!Auth::isSessionValid()) {
            $role = Auth::role();
            Auth::logout();
            $loginUrl = $role && $role !== 'admin' ? "/{$role}/login" : '/admin/login';
            Auth::start(); // Restart session so flash message works
            Auth::setFlash('error', 'Your session was ended by an administrator.');
            header('Location: ' . BASE_URL . $loginUrl);
            exit;
        }

        // ── Update last activity timestamp (throttled) ────────
        Auth::updateActivity();
    }
}
