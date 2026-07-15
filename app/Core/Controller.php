<?php
/**
 * Base Controller — view rendering, redirects, JSON responses
 */
abstract class Controller
{
    // Sub-classes set this to their module's views folder path
    protected string $viewPath = '';

    // ── Render view inside a layout ───────────────────────────
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data, EXTR_SKIP);

        // Buffer view content
        ob_start();
        $viewFile = $this->viewPath . '/' . ltrim($view, '/') . '.php';
        if (!file_exists($viewFile)) {
            ob_end_clean();
            $this->abort(404, "View not found: {$viewFile}");
            return;
        }
        include $viewFile;
        $content = ob_get_clean();

        // Render inside layout
        $layoutFile = APP_PATH . '/Views/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            echo $content;
            return;
        }
        include $layoutFile;
    }

    // ── Render without layout ─────────────────────────────────
    protected function renderPartial(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = $this->viewPath . '/' . ltrim($view, '/') . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        }
    }

    // ── Redirect ──────────────────────────────────────────────
    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
        exit;
    }

    protected function redirectBack(): void
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/';
        header('Location: ' . $ref);
        exit;
    }

    // ── JSON ──────────────────────────────────────────────────
    protected function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ── Abort ─────────────────────────────────────────────────
    protected function abort(int $code = 404, string $message = 'Not Found'): void
    {
        http_response_code($code);
        $wantsJson = (
            str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') ||
            str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') ||
            str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') ||
            !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        );
        if ($wantsJson) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => $message]);
        } else {
            echo "<h1>{$code} — {$message}</h1>";
        }
        exit;
    }

    // ── Input helpers ─────────────────────────────────────────
    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    protected function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    // ── Flash helpers ─────────────────────────────────────────
    protected function flash(string $type, string $msg): void
    {
        Auth::setFlash($type, $msg);
    }

    // ── CSRF verify ───────────────────────────────────────────
    protected function verifyCsrf(): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->abort(403, 'Invalid CSRF token');
        }
    }
}
