<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Lightweight, zero-dependency environment loader.
 * Reads .env files, strips comments, trims quotes, and populates $_ENV / getenv().
 */
class Env
{
    private static array $cache = [];

    /**
     * Load environment variables from a file.
     *
     * @param string $filePath Absolute path to the .env file.
     * @return bool True if loaded, false if file does not exist.
     */
    public static function load(string $filePath): bool
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return false;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return false;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            // Skip comments and empty lines
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Split on the first equals sign
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $name = trim($parts[0]);
            $value = trim($parts[1]);

            // Remove enclosing quotes if present
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }

            // Resolve basic booleans and nulls
            $lower = strtolower($value);
            if ($lower === 'true') {
                $resolved = true;
            } elseif ($lower === 'false') {
                $resolved = false;
            } elseif ($lower === 'null') {
                $resolved = null;
            } else {
                $resolved = $value;
            }

            self::$cache[$name] = $resolved;
            $_ENV[$name] = $resolved;
            putenv("{$name}={$value}");
        }

        return true;
    }

    /**
     * Get an environment variable with an optional default.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        $val = getenv($key);
        if ($val !== false) {
            return $val;
        }

        return $default;
    }

    /**
     * Set or override an environment variable in runtime cache and superglobals.
     *
     * @param string $key
     * @param mixed $value
     */
    public static function set(string $key, mixed $value): void
    {
        self::$cache[$key] = $value;
        $_ENV[$key] = $value;
        if (is_scalar($value)) {
            putenv("{$key}={$value}");
        }
    }

    /**
     * Safely update one or more key-value pairs in the .env file and runtime cache.
     *
     * @param array<string, scalar> $variables
     * @param string|null $filePath
     * @return bool
     */
    public static function writeToEnvFile(array $variables, ?string $filePath = null): bool
    {
        $filePath = $filePath ?? (defined('ROOT_PATH') ? ROOT_PATH . '/.env' : dirname(__DIR__, 2) . '/.env');
        if (!file_exists($filePath) || !is_writable($filePath)) {
            return false;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }

        foreach ($variables as $key => $val) {
            self::set($key, (string)$val);
            $pattern = "/^{$key}=.*/m";
            $escapedVal = str_contains((string)$val, ' ') ? '"' . $val . '"' : (string)$val;
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$key}={$escapedVal}", $content);
            } else {
                $content .= "\n{$key}={$escapedVal}";
            }
        }

        return file_put_contents($filePath, $content) !== false;
    }
}
