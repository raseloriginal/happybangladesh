<?php
/**
 * AuthMiddleware — ensure user is logged in
 */
class AuthMiddleware
{
    public static function handle(): void
    {
        if (!Auth::check()) {
            Auth::setFlash('error', 'Please log in to continue.');
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
}
