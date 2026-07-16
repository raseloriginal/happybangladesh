<?php
/**
 * Auth — Session-based authentication helpers
 */
class Auth
{
    // ── Session bootstrap ──────────────────────────────────────
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
            $path = str_replace($basePath, '', $uri);
            $path = trim(parse_url($path, PHP_URL_PATH), '/');

            $sessionName = SESSION_NAME;
            if (str_starts_with($path, 'admin')) {
                $sessionName .= '_ADMIN';
            } elseif (str_starts_with($path, 'manager')) {
                $sessionName .= '_MANAGER';
            } elseif (str_starts_with($path, 'sr')) {
                $sessionName .= '_SR';
            } elseif (str_starts_with($path, 'dsr')) {
                $sessionName .= '_DSR';
            }

            session_name($sessionName);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => false,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    // ── Login ─────────────────────────────────────────────────
    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email']= $user['email'];
        $_SESSION['role']      = $user['role_slug'];
        $_SESSION['role_name'] = $user['role_name'];
        $_SESSION['warehouse_id'] = $user['warehouse_id'];
        $_SESSION['logged_in'] = true;
    }

    // ── Logout ────────────────────────────────────────────────
    public static function logout(): void
    {
        session_unset();
        session_destroy();
    }

    // ── Checks ────────────────────────────────────────────────
    public static function check(): bool
    {
        return !empty($_SESSION['logged_in']);
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function name(): string
    {
        return $_SESSION['user_name'] ?? 'Guest';
    }

    public static function email(): string
    {
        return $_SESSION['user_email'] ?? '';
    }

    public static function role(): string
    {
        return $_SESSION['role'] ?? '';
    }

    public static function roleName(): string
    {
        return $_SESSION['role_name'] ?? '';
    }

    public static function warehouseId(): ?int
    {
        return $_SESSION['warehouse_id'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return self::role() === ROLE_ADMIN;
    }

    public static function isManager(): bool
    {
        return self::role() === ROLE_MANAGER;
    }

    public static function isSR(): bool
    {
        return self::role() === ROLE_SR;
    }

    public static function isDSR(): bool
    {
        return self::role() === ROLE_DSR;
    }

    public static function hasRole(string $role): bool
    {
        return self::role() === $role;
    }

    // ── Redirect by role after login ──────────────────────────
    public static function defaultRedirect(): string
    {
        return match (self::role()) {
            ROLE_ADMIN   => '/admin/dashboard',
            ROLE_MANAGER => '/manager/dashboard',
            ROLE_SR      => '/sr/dashboard',
            ROLE_DSR     => '/dsr/dashboard',
            default      => '/login',
        };
    }

    // ── Flash messages ────────────────────────────────────────
    public static function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    public static function getFlash(): ?array
    {
        if (!empty($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}
