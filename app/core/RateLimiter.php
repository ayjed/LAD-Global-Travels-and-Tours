<?php

declare(strict_types=1);

namespace App\Core;

/**
 * File-based Rate Limiter for login throttling.
 * Tracks failed attempts per client IP or key without requiring external cache daemons.
 */
class RateLimiter
{
    private static string $storageDir;

    public static function init(?string $storageDir = null): void
    {
        self::$storageDir = $storageDir ?? (defined('STORAGE_PATH') ? STORAGE_PATH . '/temp' : sys_get_temp_dir());
        if (!is_dir(self::$storageDir)) {
            @mkdir(self::$storageDir, 0755, true);
        }
    }

    private static function getFilePath(string $key): string
    {
        if (empty(self::$storageDir)) {
            self::init();
        }
        $safeKey = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $key);
        return self::$storageDir . '/rate_limit_' . md5($safeKey) . '.json';
    }

    /**
     * Check if a given key has exceeded maximum allowed attempts.
     */
    public static function tooManyAttempts(string $key, int $maxAttempts = 5, int $decaySeconds = 900): bool
    {
        $file = self::getFilePath($key);
        if (!file_exists($file)) {
            return false;
        }

        $content = @file_get_contents($file);
        if ($content === false) {
            return false;
        }

        $data = json_decode($content, true);
        if (!$data || !isset($data['attempts'], $data['expires_at'])) {
            return false;
        }

        // If decayed/expired, reset
        if (time() > $data['expires_at']) {
            @unlink($file);
            return false;
        }

        return $data['attempts'] >= $maxAttempts;
    }

    /**
     * Increment the attempt count for a key.
     */
    public static function hit(string $key, int $decaySeconds = 900): int
    {
        $file = self::getFilePath($key);
        $now = time();
        $attempts = 1;

        if (file_exists($file)) {
            $content = @file_get_contents($file);
            $data = json_decode((string)$content, true);
            if ($data && isset($data['expires_at']) && $now <= $data['expires_at']) {
                $attempts = ($data['attempts'] ?? 0) + 1;
                $expiresAt = $data['expires_at'];
            } else {
                $expiresAt = $now + $decaySeconds;
            }
        } else {
            $expiresAt = $now + $decaySeconds;
        }

        $payload = json_encode(['attempts' => $attempts, 'expires_at' => $expiresAt]);
        @file_put_contents($file, $payload, LOCK_EX);

        return $attempts;
    }

    /**
     * Clear all recorded attempts for a key.
     */
    public static function clear(string $key): void
    {
        $file = self::getFilePath($key);
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    /**
     * Calculate seconds remaining until rate limit decays.
     */
    public static function availableIn(string $key): int
    {
        $file = self::getFilePath($key);
        if (!file_exists($file)) {
            return 0;
        }

        $content = @file_get_contents($file);
        $data = json_decode((string)$content, true);
        if (!$data || !isset($data['expires_at'])) {
            return 0;
        }

        $remaining = $data['expires_at'] - time();
        return max(0, $remaining);
    }
}
