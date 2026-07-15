<?php
/**
 * Global Helpers — formatting, utilities
 */
class Helpers
{
    // ── Money ─────────────────────────────────────────────────
    public static function money(float $amount, string $symbol = '৳'): string
    {
        return $symbol . ' ' . number_format($amount, 2);
    }

    // ── Date ──────────────────────────────────────────────────
    public static function date(string $date, string $format = 'd M Y'): string
    {
        if (empty($date) || $date === '0000-00-00') return '—';
        return date($format, strtotime($date));
    }

    public static function datetime(string $dt, string $format = 'd M Y, h:i A'): string
    {
        if (empty($dt)) return '—';
        return date($format, strtotime($dt));
    }

    public static function timeAgo(string $datetime): string
    {
        $now  = time();
        $then = strtotime($datetime);
        $diff = $now - $then;
        if ($diff < 60)       return 'just now';
        if ($diff < 3600)     return (int)($diff / 60) . ' min ago';
        if ($diff < 86400)    return (int)($diff / 3600) . ' hr ago';
        if ($diff < 604800)   return (int)($diff / 86400) . ' days ago';
        return date('d M Y', $then);
    }

    // ── Status badges ─────────────────────────────────────────
    public static function statusBadge(string $status): string
    {
        $map = [
            'active'     => 'bg-green-100 text-green-800',
            'inactive'   => 'bg-gray-100 text-gray-600',
            'pending'    => 'bg-yellow-100 text-yellow-800',
            'approved'   => 'bg-green-100 text-green-800',
            'rejected'   => 'bg-red-100 text-red-800',
            'confirmed'  => 'bg-blue-100 text-blue-800',
            'dispatched' => 'bg-indigo-100 text-indigo-800',
            'delivered'  => 'bg-green-100 text-green-800',
            'cancelled'  => 'bg-red-100 text-red-800',
            'in_transit' => 'bg-orange-100 text-orange-800',
            'present'    => 'bg-green-100 text-green-800',
            'absent'     => 'bg-red-100 text-red-800',
            'late'       => 'bg-yellow-100 text-yellow-800',
            'half_day'   => 'bg-orange-100 text-orange-800',
        ];
        $cls   = $map[strtolower($status)] ?? 'bg-gray-100 text-gray-600';
        $label = ucwords(str_replace('_', ' ', $status));
        return "<span class=\"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$cls}\">{$label}</span>";
    }

    // ── Sanitize ──────────────────────────────────────────────
    public static function e(string $str): string
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    // ── URL ───────────────────────────────────────────────────
    public static function url(string $path = ''): string
    {
        return BASE_URL . '/' . ltrim($path, '/');
    }

    // ── Asset ─────────────────────────────────────────────────
    public static function asset(string $path): string
    {
        return BASE_URL . '/assets/' . ltrim($path, '/');
    }

    // ── CSRF ──────────────────────────────────────────────────
    public static function csrfToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public static function csrfField(): string
    {
        $token = self::csrfToken();
        return "<input type=\"hidden\" name=\"_csrf_token\" value=\"{$token}\">";
    }

    public static function verifyCsrf(): bool
    {
        // Accept both field names: _csrf_token (form) and csrf_token (AJAX/FormData)
        $token = $_POST['_csrf_token'] ?? $_POST['csrf_token'] ?? '';
        if (empty($token)) {
            // Fall back to JSON body
            $input = json_decode(file_get_contents('php://input'), true);
            $token = $input['csrf_token'] ?? $input['_csrf_token'] ?? '';
        }
        // Also accept token from X-CSRF-Token header
        if (empty($token)) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        }
        return hash_equals(self::csrfToken(), $token);
    }

    // ── Pagination ────────────────────────────────────────────
    public static function paginate(int $total, int $perPage, int $current, string $baseUrl): string
    {
        $pages = (int) ceil($total / $perPage);
        if ($pages <= 1) return '';
        $html = '<nav class="flex items-center gap-1 mt-4">';
        for ($i = 1; $i <= $pages; $i++) {
            $active = $i === $current ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50';
            $html  .= "<a href=\"{$baseUrl}?page={$i}\" class=\"px-3 py-1 rounded border text-sm {$active}\">{$i}</a>";
        }
        $html .= '</nav>';
        return $html;
    }

    // ── Number ────────────────────────────────────────────────
    public static function number(int|float $n): string
    {
        return number_format($n);
    }

    // ── Avatar initials ───────────────────────────────────────
    public static function initials(string $name): string
    {
        $parts = explode(' ', trim($name));
        $init  = strtoupper(substr($parts[0], 0, 1));
        if (count($parts) > 1) {
            $init .= strtoupper(substr(end($parts), 0, 1));
        }
        return $init;
    }
}

// Short alias functions for views
function e(string $s): string    { return Helpers::e($s); }
function h(string $s): string    { return Helpers::e($s); }
function url(string $p = ''): string { return Helpers::url($p); }
function asset(string $p): string    { return Helpers::asset($p); }
