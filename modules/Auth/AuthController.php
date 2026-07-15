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

    // ── Show login page ───────────────────────────────────────
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect(ltrim(Auth::defaultRedirect(), '/'));
        }
        $this->render('login', ['pageTitle' => 'Login'], 'auth');
    }

    // ── Process login ─────────────────────────────────────────
    public function login(): void
    {
        if (!$this->isPost()) {
            $this->redirect('login');
        }

        $identity = trim($this->post('email', ''));
        $password = $this->post('password', '');

        if (empty($identity) || empty($password)) {
            $this->flash('error', 'Email/Phone and password are required.');
            $this->redirect('login');
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
            $this->flash('error', 'Invalid email/phone or password.');
            $this->redirect('login');
            return;
        }

        Auth::login($user);

        // Log activity
        $db->prepare("INSERT INTO activity_logs (user_id, action, module, description, ip_address) VALUES (?, 'login', 'auth', 'User logged in', ?)")
           ->execute([$user['id'], $_SERVER['REMOTE_ADDR'] ?? '']);

        $this->redirect(ltrim(Auth::defaultRedirect(), '/'));
    }

    // ── Logout ────────────────────────────────────────────────
    public function logout(): void
    {
        Auth::logout();
        $this->redirect('login');
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
