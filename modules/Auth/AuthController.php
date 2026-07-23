<?php
/**
 * AuthController — login, logout, forgot password
 */
class AuthController extends Controller
{
    protected string $viewPath;

    public function __construct()
    {
        $this->viewPath = MOD_PATH . '/Auth/views';
    }

    // ── Portal Selection ──────────────────────────────────────
    public function portal(): void
    {
        if (Auth::check()) {
            // Legacy session found on the main portal page.
            // Since we now use role-specific sessions, this is invalid. Destroy it.
            Auth::logout();
        }
        $this->render('portal', ['pageTitle' => 'Select Portal'], 'auth');
    }

    // ── Admin Login ───────────────────────────────────────────
    public function showLoginAdmin(): void { $this->showRoleLogin('admin'); }
    public function loginAdmin(): void { $this->processRoleLogin('admin'); }

    // ── Manager Login ─────────────────────────────────────────
    public function showLoginManager(): void { $this->showRoleLogin('manager'); }
    public function loginManager(): void { $this->processRoleLogin('manager'); }

    // ── SR Login ──────────────────────────────────────────────
    public function showLoginSR(): void { $this->showRoleLogin('sr'); }
    public function loginSR(): void { $this->processRoleLogin('sr'); }

    // ── DSR Login ─────────────────────────────────────────────
    public function showLoginDSR(): void { $this->showRoleLogin('dsr'); }
    public function loginDSR(): void { $this->processRoleLogin('dsr'); }

    // ── Internal Helpers ──────────────────────────────────────
    private function showRoleLogin(string $role): void
    {
        if (Auth::check()) {
            $this->redirect(ltrim(Auth::defaultRedirect(), '/'));
        }
        $this->render("login_{$role}", ['pageTitle' => ucfirst($role) . ' Login'], 'auth');
    }

    private function processRoleLogin(string $role): void
    {
        $loginUrl = $role === 'admin' ? 'admin/login' : "{$role}/login";
        
        if (!$this->isPost()) {
            $this->redirect($loginUrl);
        }

        $identity = trim($this->post('email', ''));
        $password = $this->post('password', '');

        if (empty($identity) || empty($password)) {
            $this->flash('error', 'Email/Phone and password are required.');
            $this->redirect($loginUrl);
            return;
        }

        $db   = Database::getInstance();
        $stmt = $db->prepare("
            SELECT u.*, r.slug AS role_slug, r.name AS role_name
            FROM users u
            JOIN roles r ON r.id = u.role_id
            WHERE (u.email = ? OR u.phone = ?) AND u.status = 1
            LIMIT 1
        ");
        $stmt->execute([$identity, $identity]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $this->flash('error', 'Invalid credentials.');
            $this->redirect($loginUrl);
            return;
        }

        if ($user['role_slug'] !== $role) {
            $this->flash('error', "This portal is only for {$role}s. Please use the correct portal.");
            $this->redirect($loginUrl);
            return;
        }

        Auth::login($user, !empty($this->post('remember')));

        // Log activity
        $db->prepare("INSERT INTO activity_logs (user_id, action, module, description, ip_address) VALUES (?, 'login', 'auth', 'User logged in', ?)")
           ->execute([$user['id'], $_SERVER['REMOTE_ADDR'] ?? '']);

        $this->redirect(ltrim(Auth::defaultRedirect(), '/'));
    }

    // ── Logout ────────────────────────────────────────────────
    public function logout(): void
    {
        $role = Auth::role();
        Auth::logout();
        
        $loginUrl = $role && $role !== 'admin' ? "{$role}/login" : ($role === 'admin' ? 'admin/login' : 'login');
        $this->redirect($loginUrl);
    }

    // ── Forgot password (UI only) ─────────────────────────────
    public function showForgot(): void
    {
        $this->render('forgot_password', ['pageTitle' => 'Forgot Password'], 'auth');
    }

    public function forgot(): void
    {
        $this->flash('info', 'Password reset is not implemented in this starter version. Please contact admin.');
        $this->redirect('forgot');
    }
}
