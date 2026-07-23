<?php
/**
 * Auth — Session-based authentication helpers with persistent "Remember Me" support
 */
class Auth
{
    /** Cache: role slug derived from the current URL path */
    private static ?string $currentRoleSlug = null;

    // ── Session bootstrap ──────────────────────────────────────
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $path = self::getCleanPath();

            $sessionName = SESSION_NAME;
            if (str_starts_with($path, 'admin')) {
                $sessionName .= '_ADMIN';
                self::$currentRoleSlug = 'admin';
            } elseif (str_starts_with($path, 'manager')) {
                $sessionName .= '_MANAGER';
                self::$currentRoleSlug = 'manager';
            } elseif (str_starts_with($path, 'sr')) {
                $sessionName .= '_SR';
                self::$currentRoleSlug = 'sr';
            } elseif (str_starts_with($path, 'dsr')) {
                $sessionName .= '_DSR';
                self::$currentRoleSlug = 'dsr';
            }

            session_name($sessionName);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => false,
                'httponly'  => true,
                'samesite'  => 'Lax',
            ]);
            session_start();

            // ── Auto-login from "Remember Me" cookie ──────────
            if (!self::check() && self::$currentRoleSlug) {
                self::tryRememberLogin(self::$currentRoleSlug);
            }
        }
    }

    // ── Login ─────────────────────────────────────────────────
    /**
     * @param array  $user     User row from DB (must include id, name, email, role_slug, role_name, warehouse_id)
     * @param bool   $remember Whether to set a persistent 30-day cookie
     */
    public static function login(array $user, bool $remember = false): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']      = $user['id'];
        $_SESSION['user_name']    = $user['name'];
        $_SESSION['user_email']   = $user['email'];
        $_SESSION['role']         = $user['role_slug'];
        $_SESSION['role_name']    = $user['role_name'];
        $_SESSION['warehouse_id'] = $user['warehouse_id'];
        $_SESSION['logged_in']    = true;

        // ── Create a session row in DB ────────────────────────
        $token = bin2hex(random_bytes(32));
        $db    = Database::getInstance();
        $expiresAt = date('Y-m-d H:i:s', time() + REMEMBER_LIFETIME);

        $stmt = $db->prepare("
            INSERT INTO user_sessions (user_id, token, ip_address, user_agent, role_slug, expires_at)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user['id'],
            $token,
            $_SERVER['REMOTE_ADDR'] ?? '',
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            $user['role_slug'],
            $expiresAt,
        ]);

        $_SESSION['session_token'] = $token;

        // ── Set "Remember Me" cookie if requested ─────────────
        if ($remember) {
            $cookieName = REMEMBER_COOKIE_NAME . '_' . $user['role_slug'];
            setcookie($cookieName, $token, [
                'expires'  => time() + REMEMBER_LIFETIME,
                'path'     => '/',
                'secure'   => false,
                'httponly'  => true,
                'samesite'  => 'Lax',
            ]);
        }
    }

    // ── Auto-login from remember cookie ──────────────────────
    private static function tryRememberLogin(string $roleSlug): void
    {
        $cookieName = REMEMBER_COOKIE_NAME . '_' . $roleSlug;
        $token = $_COOKIE[$cookieName] ?? null;

        if (!$token || strlen($token) !== 64) {
            return;
        }

        $db   = Database::getInstance();
        $stmt = $db->prepare("
            SELECT us.*, u.name, u.email, u.status AS user_status,
                   r.slug AS role_slug, r.name AS role_name, u.warehouse_id
            FROM user_sessions us
            JOIN users u ON u.id = us.user_id
            JOIN roles r ON r.id = u.role_id
            WHERE us.token = ?
              AND us.is_active = 1
              AND us.expires_at > NOW()
              AND us.role_slug = ?
            LIMIT 1
        ");
        $stmt->execute([$token, $roleSlug]);
        $session = $stmt->fetch();

        if (!$session || $session['user_status'] != 1) {
            // Invalid or expired — clear the cookie
            setcookie($cookieName, '', ['expires' => time() - 3600, 'path' => '/']);
            return;
        }

        // Restore session without creating a new DB row
        session_regenerate_id(true);
        $_SESSION['user_id']       = $session['user_id'];
        $_SESSION['user_name']     = $session['name'];
        $_SESSION['user_email']    = $session['email'];
        $_SESSION['role']          = $session['role_slug'];
        $_SESSION['role_name']     = $session['role_name'];
        $_SESSION['warehouse_id']  = $session['warehouse_id'];
        $_SESSION['logged_in']     = true;
        $_SESSION['session_token'] = $token;

        // Update last_active_at
        $db->prepare("UPDATE user_sessions SET last_active_at = NOW() WHERE token = ?")->execute([$token]);
    }

    // ── Logout ────────────────────────────────────────────────
    public static function logout(): void
    {
        // Deactivate the session row in DB
        $token = $_SESSION['session_token'] ?? null;
        if ($token) {
            $db = Database::getInstance();
            $db->prepare("UPDATE user_sessions SET is_active = 0 WHERE token = ?")->execute([$token]);
        }

        // Clear the remember cookie for the user's role
        $role = self::role();
        if ($role) {
            $cookieName = REMEMBER_COOKIE_NAME . '_' . $role;
            setcookie($cookieName, '', ['expires' => time() - 3600, 'path' => '/']);
        }

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

    public static function sessionToken(): string
    {
        return $_SESSION['session_token'] ?? '';
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

    // ══════════════════════════════════════════════════════════
    //  Session Management Methods
    // ══════════════════════════════════════════════════════════

    /**
     * Update last_active_at for the current session.
     * Throttled to once per 5 minutes to reduce DB writes.
     */
    public static function updateActivity(): void
    {
        $token = $_SESSION['session_token'] ?? null;
        if (!$token) return;

        // Throttle: only update once per 5 minutes
        $lastUpdate = $_SESSION['_last_activity_update'] ?? 0;
        if (time() - $lastUpdate < 300) return;

        $db = Database::getInstance();
        $db->prepare("UPDATE user_sessions SET last_active_at = NOW() WHERE token = ? AND is_active = 1")
           ->execute([$token]);

        $_SESSION['_last_activity_update'] = time();
    }

    /**
     * Check if the current session is still active in the DB.
     * Used by AuthMiddleware to enforce admin force-logout.
     * Returns false if session was force-logged-out.
     */
    public static function isSessionValid(): bool
    {
        $token = $_SESSION['session_token'] ?? null;
        if (!$token) return true; // Legacy sessions without token — allow

        // Throttle check to once per 60 seconds
        $lastCheck = $_SESSION['_last_session_check'] ?? 0;
        if (time() - $lastCheck < 60) return true;

        $db   = Database::getInstance();
        $stmt = $db->prepare("SELECT is_active FROM user_sessions WHERE token = ? LIMIT 1");
        $stmt->execute([$token]);
        $row = $stmt->fetch();

        $_SESSION['_last_session_check'] = time();

        if (!$row || !$row['is_active']) {
            return false;
        }
        return true;
    }

    /**
     * Force-logout a session by its DB id (admin action).
     */
    public static function forceLogout(int $sessionId): bool
    {
        $db   = Database::getInstance();
        $stmt = $db->prepare("UPDATE user_sessions SET is_active = 0 WHERE id = ? AND is_active = 1");
        $stmt->execute([$sessionId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get all active sessions (for admin panel).
     * Joins with users table for name/role info.
     */
    public static function getActiveSessions(?string $filterRole = null): array
    {
        $db  = Database::getInstance();
        $sql = "
            SELECT us.*, u.name AS user_name, u.email AS user_email, u.phone AS user_phone,
                   r.name AS role_name
            FROM user_sessions us
            JOIN users u ON u.id = us.user_id
            JOIN roles r ON r.slug = us.role_slug
            WHERE us.is_active = 1
              AND us.expires_at > NOW()
        ";
        $params = [];

        if ($filterRole) {
            $sql .= " AND us.role_slug = ?";
            $params[] = $filterRole;
        }

        $sql .= " ORDER BY us.last_active_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Count active sessions grouped by role.
     */
    public static function getSessionCounts(): array
    {
        $db = Database::getInstance();
        $rows = $db->query("
            SELECT role_slug, COUNT(*) AS cnt
            FROM user_sessions
            WHERE is_active = 1 AND expires_at > NOW()
            GROUP BY role_slug
        ")->fetchAll();

        $counts = ['admin' => 0, 'manager' => 0, 'sr' => 0, 'dsr' => 0, 'total' => 0];
        foreach ($rows as $row) {
            $counts[$row['role_slug']] = (int)$row['cnt'];
            $counts['total'] += (int)$row['cnt'];
        }
        return $counts;
    }

    // ── Helper: get clean URL path ───────────────────────────
    private static function getCleanPath(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
        $path = str_replace($basePath, '', $uri);
        $path = trim(parse_url($path, PHP_URL_PATH), '/');

        // Strip "public" if it's at the start of the path
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        } elseif ($path === 'public') {
            $path = '';
        }

        // Strip "index.php" if it's at the start of the path
        if (str_starts_with($path, 'index.php/')) {
            $path = substr($path, 10);
        } elseif ($path === 'index.php') {
            $path = '';
        }

        return trim($path, '/');
    }
}
