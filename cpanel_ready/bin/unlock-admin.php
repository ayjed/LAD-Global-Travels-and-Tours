<?php

declare(strict_types=1);

/**
 * LAD GLOBAL TRAVELS AND TOURS — ADMIN UNLOCK & THROTTLE CLEAR TOOL
 * Clears brute-force rate limits and database account lockout states.
 * Usage:
 *   php bin/unlock-admin.php
 *   php bin/unlock-admin.php --password=NewPassword123!
 */

if (php_sapi_name() !== 'cli') {
    die("Security Error: CLI execution only.\n");
}

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Security;
use App\Core\RateLimiter;

$pdo = Database::getConnection();

echo "====================================================================\n";
echo "LAD GLOBAL TRAVELS — ADMIN UNLOCK & RATE-LIMIT RESET\n";
echo "====================================================================\n\n";

// 1. Reset Database Lockout State
$unlockStmt = $pdo->prepare("UPDATE admins SET failed_login_attempts = 0, locked_until = NULL");
$unlockStmt->execute();
echo "[SUCCESS] Reset failed login attempts to 0 and cleared locked_until timestamp in database.\n";

// 2. Clear File-based Rate Limiter Cache
$tempDir = defined('STORAGE_PATH') ? STORAGE_PATH . '/temp' : ROOT_PATH . '/storage/temp';
$clearedRateLimits = 0;
if (is_dir($tempDir)) {
    foreach (glob($tempDir . '/rate_limit_*.json') as $file) {
        @unlink($file);
        $clearedRateLimits++;
    }
}
$sysTemp = sys_get_temp_dir();
if (is_dir($sysTemp)) {
    foreach (glob($sysTemp . '/rate_limit_*.json') as $file) {
        @unlink($file);
        $clearedRateLimits++;
    }
}
echo "[SUCCESS] Removed {$clearedRateLimits} rate-limiting throttle lock file(s).\n\n";

// 3. Optional Password Reset
$options = getopt('', ['password:', 'username:']);
if (!empty($options['password'])) {
    $newPass = (string)$options['password'];
    if (strlen($newPass) < 10) {
        echo "[ERROR] Password must be at least 10 characters long.\n";
        exit(1);
    }
    $hash = Security::hashPassword($newPass);
    $user = $options['username'] ?? null;
    if ($user) {
        $stmt = $pdo->prepare("UPDATE admins SET password_hash = :hash, failed_login_attempts = 0, locked_until = NULL WHERE username = :user OR email = :user");
        $stmt->execute([':hash' => $hash, ':user' => $user]);
        echo "[SUCCESS] Password for admin '{$user}' updated successfully.\n\n";
    } else {
        $stmt = $pdo->prepare("UPDATE admins SET password_hash = :hash, failed_login_attempts = 0, locked_until = NULL");
        $stmt->execute([':hash' => $hash]);
        echo "[SUCCESS] Password for all admin accounts updated successfully.\n\n";
    }
}

// 4. Display Active Accounts
echo "=== Active Administrator Accounts ===\n";
$stmt = $pdo->query("SELECT id, username, email, failed_login_attempts, locked_until, last_login_at FROM admins");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($admins as $admin) {
    echo sprintf(
        " • ID: %-2d | Username: %-12s | Email: %-28s | Status: %s\n",
        $admin['id'],
        $admin['username'],
        $admin['email'],
        (empty($admin['locked_until']) ? 'ACTIVE (UNLOCKED)' : 'LOCKED until ' . $admin['locked_until'])
    );
}

echo "\nLogin endpoint: http://127.0.0.1:8000/admin/login\n";
echo "====================================================================\n";
