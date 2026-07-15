<?php
/**
 * RoleMiddleware — ensure user has required role
 */
class RoleMiddleware
{
    /**
     * Allow access only if user has one of the given roles.
     * @param string|array $roles  Single role slug or array of role slugs
     */
    public static function check(string|array $roles): void
    {
        AuthMiddleware::handle();   // must be logged in first

        $roles      = (array) $roles;
        $userRole   = Auth::role();

        if (!in_array($userRole, $roles, true)) {
            Auth::setFlash('error', 'You do not have permission to access this page.');
            header('Location: ' . BASE_URL . Auth::defaultRedirect());
            exit;
        }
    }
}
