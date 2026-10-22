<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Database;
use App\Core\Security;
use App\Core\Response;
use App\Core\RateLimiter;
use PDO;

/**
 * Authentication and Session Guard for the single administrator.
 * Strictly forbids customer and staff roles; implements session regeneration,
 * inactivity timeouts, and brute-force protection.
 */
class Auth
{
    private const SESSION_AUTH_KEY = 'admin_authenticated';
    private const SESSION_ID_KEY   = 'admin_id';
    private const SESSION_USER_KEY = 'admin_username';
    private const SESSION_TIME_KEY = 'admin_last_activity';
    private const INACTIVITY_TIMEOUT = 7200; // 2 hours

    /**
     * Attempt admin authentication with rate-limiting and session regeneration.
     *
     * @param string $usernameOrEmail
     * @param string $password
     * @param string|null $clientIp
     * @return array [bool $success, string $message]
     */
    public static function attempt(string $usernameOrEmail, string $password, ?string $clientIp = null): array
    {
        $ip = $clientIp ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $throttleKey = 'admin_login_' . md5($ip . '_' . strtolower($usernameOrEmail));

        // 1. Check Rate Limiter
        if (RateLimiter::tooManyAttempts($throttleKey, 5, 900)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);
            return [false, "Too many failed login attempts. Account access is temporarily locked for {$minutes} minute(s)."];
        }

        $pdo = Database::getConnection();

        // 2. Fetch Single Admin Record by Username or Email
        $stmt = $pdo->prepare("SELECT id, username, email, password_hash, failed_login_attempts, locked_until 
                               FROM admins 
                               WHERE username = :uname OR email = :uemail 
                               LIMIT 1");
        $stmt->execute([':uname' => $usernameOrEmail, ':uemail' => $usernameOrEmail]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3. Constant-Time Password Verification (Defend Against Timing Attacks)
        $dummyHash = '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUU0123456789012';
        $hashToCheck = $admin['password_hash'] ?? $dummyHash;
        $isValidPassword = Security::verifyPassword($password, $hashToCheck);

        // 4. Verify Account Lockout Status
        if ($admin && !empty($admin['locked_until']) && strtotime($admin['locked_until']) > time()) {
            return [false, "This administrative account is locked due to repeated failed attempts. Please try again later."];
        }

        // 5. Handle Authentication Failure
        if (!$admin || !$isValidPassword) {
            RateLimiter::hit($throttleKey, 900);

            if ($admin) {
                $newAttempts = (int)$admin['failed_login_attempts'] + 1;
                $lockedUntil = null;
                if ($newAttempts >= 5) {
                    $lockedUntil = date('Y-m-d H:i:s', time() + 900); // Lock for 15 mins
                }
                $updateStmt = $pdo->prepare("UPDATE admins SET failed_login_attempts = :attempts, locked_until = :locked WHERE id = :id");
                $updateStmt->execute([
                    ':attempts' => $newAttempts,
                    ':locked'   => $lockedUntil,
                    ':id'       => $admin['id']
                ]);
            }

            return [false, "Invalid username, email, or password."];
        }

        // 6. Handle Authentication Success
        RateLimiter::clear($throttleKey);

        // Regenerate session ID to prevent Session Fixation attacks
        if (session_status() === PHP_SESSION_ACTIVE) {
            if (!headers_sent()) {
                session_regenerate_id(true);
            }
        } else {
            Security::startSecureSession();
        }

        $_SESSION[self::SESSION_AUTH_KEY] = true;
        $_SESSION[self::SESSION_ID_KEY]   = (int)$admin['id'];
        $_SESSION[self::SESSION_USER_KEY] = $admin['username'];
        $_SESSION[self::SESSION_TIME_KEY] = time();

        // Reset failed attempts in database and record login timestamp
        $updateStmt = $pdo->prepare("UPDATE admins SET failed_login_attempts = 0, locked_until = NULL, last_login_at = NOW() WHERE id = :id");
        $updateStmt->execute([':id' => $admin['id']]);

        // Record Audit Log
        $auditStmt = $pdo->prepare("INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, details, ip_address) 
                                    VALUES (:admin_id, 'LOGIN', 'admin', :entity_id, 'Successful admin authentication', :ip)");
        $auditStmt->execute([
            ':admin_id'  => $admin['id'],
            ':entity_id' => $admin['id'],
            ':ip'        => $ip
        ]);

        return [true, "Authentication successful."];
    }

    /**
     * Check if an active, non-expired administrator session exists.
     */
    public static function check(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            Security::startSecureSession();
        }

        if (empty($_SESSION[self::SESSION_AUTH_KEY]) || $_SESSION[self::SESSION_AUTH_KEY] !== true) {
            return false;
        }

        // Inactivity timeout check
        $lastActivity = $_SESSION[self::SESSION_TIME_KEY] ?? 0;
        if ((time() - $lastActivity) > self::INACTIVITY_TIMEOUT) {
            self::logout();
            return false;
        }

        // Refresh activity timestamp
        $_SESSION[self::SESSION_TIME_KEY] = time();
        return true;
    }

    /**
     * Retrieve the authenticated admin ID.
     */
    public static function id(): ?int
    {
        return self::check() ? ($_SESSION[self::SESSION_ID_KEY] ?? null) : null;
    }

    /**
     * Retrieve the authenticated admin username.
     */
    public static function username(): ?string
    {
        return self::check() ? ($_SESSION[self::SESSION_USER_KEY] ?? null) : null;
    }

    /**
     * Terminate the admin session completely and clear cookie headers.
     */
    public static function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            Security::startSecureSession();
        }

        $adminId = $_SESSION[self::SESSION_ID_KEY] ?? null;

        if ($adminId) {
            try {
                $pdo = Database::getConnection();
                $auditStmt = $pdo->prepare("INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, details, ip_address) 
                                            VALUES (:admin_id, 'LOGOUT', 'admin', :entity_id, 'Admin session terminated', :ip)");
                $auditStmt->execute([
                    ':admin_id'  => $adminId,
                    ':entity_id' => $adminId,
                    ':ip'        => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                ]);
            } catch (\Throwable $e) {
                // Ignore audit log failure during logout
            }
        }

        unset(
            $_SESSION[self::SESSION_AUTH_KEY],
            $_SESSION[self::SESSION_ID_KEY],
            $_SESSION[self::SESSION_USER_KEY],
            $_SESSION[self::SESSION_TIME_KEY]
        );

        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }
    }

    /**
     * Guard an administrative route.
     * Redirects unauthenticated visitors to /admin/login with flash notice.
     */
    public static function guard(): void
    {
        if (!self::check()) {
            Response::setFlash('danger', 'You must sign in with authorized administrator credentials to access this area.');
            Response::redirect('/admin/login');
        }
    }
}
