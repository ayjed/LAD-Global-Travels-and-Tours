<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Core security helper class for output escaping, CSRF protection,
 * secure session initialization, and cryptographic operations.
 */
class Security
{
    private const CSRF_SESSION_KEY = '_csrf_token';

    /**
     * Context-aware HTML escaping for preventing Cross-Site Scripting (XSS).
     *
     * @param string|null $value
     * @return string
     */
    public static function e(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Start a hardened PHP session with secure cookie parameters.
     *
     * @param int $lifetime Session cookie lifetime in seconds (default: 7200)
     * @param bool|null $forceSecure If null, auto-detects HTTPS
     * @return bool
     */
    public static function startSecureSession(int $lifetime = 7200, ?bool $forceSecure = null): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return true;
        }

        $isHttps = $forceSecure ?? (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        );

        // Prevent session fixation and script access to cookies
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => '/',
            'domain' => '',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        return session_start();
    }

    /**
     * Retrieve or generate the active CSRF token for the session.
     *
     * @return string
     */
    public static function csrfToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::startSecureSession();
        }

        if (empty($_SESSION[self::CSRF_SESSION_KEY])) {
            $_SESSION[self::CSRF_SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::CSRF_SESSION_KEY];
    }

    /**
     * Alias for csrfToken() for backwards compatibility.
     *
     * @return string
     */
    public static function getCsrfToken(): string
    {
        return self::csrfToken();
    }

    /**
     * Generate an HTML hidden input field containing the current CSRF token.
     *
     * @return string
     */
    public static function csrfField(): string
    {
        $token = self::e(self::csrfToken());
        return '<input type="hidden" name="_csrf_token" value="' . $token . '">';
    }

    /**
     * Verify a submitted CSRF token using timing-safe comparison.
     *
     * @param string|null $submittedToken
     * @return bool
     */
    public static function verifyCsrf(?string $submittedToken): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::startSecureSession();
        }

        $sessionToken = $_SESSION[self::CSRF_SESSION_KEY] ?? '';
        if (empty($sessionToken) || empty($submittedToken)) {
            return false;
        }

        return hash_equals($sessionToken, $submittedToken);
    }

    /**
     * Generate a cryptographically secure random token (e.g. for booking references or reset keys).
     *
     * @param int $length Byte length before hex encoding
     * @return string
     */
    public static function generateRandomToken(int $length = 16): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Securely hash a password using PHP's default algorithm (Bcrypt/Argon2).
     *
     * @param string $password
     * @return string
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify a plaintext password against a stored cryptographic hash.
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Get client IP address safely.
     */
    public static function getClientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
