<?php
class RateLimiter
{
    private static string $storagePath = '';

    private static function path(string $key): string
    {
        $dir = ROOT_PATH . '/storage/rate_limits';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        return $dir . '/' . md5($key) . '.json';
    }

    public static function tooManyAttempts(string $key, int $max = 5, int $decay = 300): bool
    {
        $file = self::path($key);
        if (!file_exists($file)) return false;
        $data = json_decode(file_get_contents($file), true) ?? [];
        if (($data['locked_until'] ?? 0) > time()) return true;
        if ((time() - ($data['first_attempt'] ?? 0)) > $decay) return false;
        return ($data['attempts'] ?? 0) >= $max;
    }

    public static function hit(string $key, int $max = 5, int $decay = 300): void
    {
        $file = self::path($key);
        $data = file_exists($file) ? json_decode(file_get_contents($file), true) ?? [] : ['first_attempt' => time()];
        $data['attempts'] = ($data['attempts'] ?? 0) + 1;
        if ($data['attempts'] >= $max) $data['locked_until'] = time() + $decay;
        file_put_contents($file, json_encode($data), LOCK_EX);
    }

    public static function clear(string $key): void
    {
        $file = self::path($key);
        if (file_exists($file)) @unlink($file);
    }

    public static function retriesLeft(string $key, int $max = 5): int
    {
        $file = self::path($key);
        if (!file_exists($file)) return $max;
        $data = json_decode(file_get_contents($file), true) ?? [];
        return max(0, $max - ($data['attempts'] ?? 0));
    }
}
