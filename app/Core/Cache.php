<?php
/**
 * Application Cache Engine
 * File-based key-value caching with TTL & auto serialization
 */
class Cache
{
    private static ?string $storagePath = null;

    /**
     * Get storage path for cache files
     */
    private static function getStoragePath(): string
    {
        if (self::$storagePath === null) {
            $baseDir = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2);
            self::$storagePath = $baseDir . '/storage/cache';
        }

        if (!is_dir(self::$storagePath)) {
            @mkdir(self::$storagePath, 0755, true);
        }

        return self::$storagePath;
    }

    /**
     * Generate file path for a cache key
     */
    private static function getFilePath(string $key): string
    {
        $hash = md5($key);
        return self::getStoragePath() . '/' . $hash . '.cache';
    }

    /**
     * Retrieve item from cache
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $file = self::getFilePath($key);

        if (!file_exists($file)) {
            return $default;
        }

        $content = @file_get_contents($file);
        if ($content === false) {
            return $default;
        }

        $payload = @unserialize($content);
        if (!is_array($payload) || !isset($payload['expires_at'])) {
            @unlink($file);
            return $default;
        }

        // Check expiration
        if ($payload['expires_at'] !== 0 && time() > $payload['expires_at']) {
            @unlink($file);
            return $default;
        }

        return $payload['data'];
    }

    /**
     * Store item in cache
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl Time to live in seconds (0 = forever)
     * @return bool
     */
    public static function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        $file = self::getFilePath($key);
        $expiresAt = ($ttl === 0) ? 0 : time() + $ttl;

        $payload = [
            'key'        => $key,
            'expires_at' => $expiresAt,
            'created_at' => time(),
            'data'       => $value
        ];

        $result = @file_put_contents($file, serialize($payload), LOCK_EX);

        // Occasionally run garbage collection (1% chance)
        if (rand(1, 100) === 1) {
            self::gc();
        }

        return $result !== false;
    }

    /**
     * Get an item from cache, or execute callback and store the result
     *
     * @param string $key
     * @param int $ttl
     * @param callable $callback
     * @return mixed
     */
    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = self::get($key, null);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        self::set($key, $value, $ttl);

        return $value;
    }

    /**
     * Remove item from cache
     *
     * @param string $key
     * @return bool
     */
    public static function forget(string $key): bool
    {
        $file = self::getFilePath($key);
        if (file_exists($file)) {
            return @unlink($file);
        }
        return true;
    }

    /**
     * Clear all cached files
     *
     * @return bool
     */
    public static function flush(): bool
    {
        $path = self::getStoragePath();
        $files = glob($path . '/*.cache');
        if ($files === false) {
            return false;
        }

        $success = true;
        foreach ($files as $file) {
            if (!@unlink($file)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Garbage collection — delete expired cache files
     */
    public static function gc(): void
    {
        $path = self::getStoragePath();
        $files = glob($path . '/*.cache');
        if ($files === false) return;

        $now = time();
        foreach ($files as $file) {
            $content = @file_get_contents($file);
            if ($content === false) continue;

            $payload = @unserialize($content);
            if (is_array($payload) && isset($payload['expires_at'])) {
                if ($payload['expires_at'] !== 0 && $now > $payload['expires_at']) {
                    @unlink($file);
                }
            } else {
                @unlink($file);
            }
        }
    }
}
