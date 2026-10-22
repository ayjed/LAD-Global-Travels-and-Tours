<?php

declare(strict_types=1);

/**
 * LAD GLOBAL TRAVELS AND TOURS — CLI ADMIN PROVISIONING TOOL
 * Securely provisions the single administrator account.
 * NEVER seeds a hardcoded default password.
 */

if (php_sapi_name() !== 'cli') {
    die("Security Error: This script must only be run via CLI.\n");
}

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Security;

class AdminProvisioner
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function run(array $argv): int
    {
        echo "====================================================================\n";
        echo "LAD GLOBAL TRAVELS — SECURE ADMIN PROVISIONING\n";
        echo "====================================================================\n";

        // Parse command line options
        $options = getopt('', ['username:', 'email:', 'password:', 'reset-password']);
        $isReset = isset($options['reset-password']);

        // Check existing admin count
        $stmt = $this->pdo->query("SELECT id, username, email FROM admins LIMIT 1");
        $existingAdmin = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($existingAdmin && !$isReset) {
            echo "\n[INFO] An administrator account already exists:\n";
            echo "       Username: {$existingAdmin['username']}\n";
            echo "       Email:    {$existingAdmin['email']}\n\n";
            echo "Policy Enforced: Only ONE administrator account is permitted at MVP.\n";
            echo "To update the existing admin's password, run:\n";
            echo "  php bin/create-admin.php --reset-password\n\n";
            return 0;
        }

        // Get Username
        $username = $options['username'] ?? null;
        if (!$username) {
            if ($existingAdmin && $isReset) {
                $username = $existingAdmin['username'];
            } else {
                echo "Enter admin username (min 3 chars, alphanumeric): ";
                $username = trim((string)fgets(STDIN));
            }
        }

        if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
            echo "[ERROR] Username must be between 3 and 50 alphanumeric characters or underscores.\n";
            return 1;
        }

        // Get Email
        $email = $options['email'] ?? null;
        if (!$email) {
            if ($existingAdmin && $isReset) {
                $email = $existingAdmin['email'];
            } else {
                echo "Enter admin email address: ";
                $email = trim((string)fgets(STDIN));
            }
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "[ERROR] Invalid email address provided.\n";
            return 1;
        }

        // Get Password
        $password = $options['password'] ?? null;
        if (!$password) {
            echo "Enter secure password (min 10 chars, upper, lower, number): ";
            $password = trim((string)fgets(STDIN));
        }

        // Validate Password Strength
        if (strlen($password) < 10) {
            echo "[ERROR] Password must be at least 10 characters long.\n";
            return 1;
        }
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            echo "[ERROR] Password must include at least one uppercase letter, one lowercase letter, and one number.\n";
            return 1;
        }

        $hash = Security::hashPassword($password);

        if ($existingAdmin && $isReset) {
            $updateStmt = $this->pdo->prepare("UPDATE admins SET password_hash = :hash, failed_login_attempts = 0, locked_until = NULL, updated_at = NOW() WHERE id = :id");
            $updateStmt->execute([
                ':hash' => $hash,
                ':id'   => $existingAdmin['id']
            ]);
            echo "\n[SUCCESS] Password for admin '{$existingAdmin['username']}' updated successfully.\n";
        } else {
            $insertStmt = $this->pdo->prepare("INSERT INTO admins (username, email, password_hash) VALUES (:username, :email, :hash)");
            $insertStmt->execute([
                ':username' => $username,
                ':email'    => $email,
                ':hash'     => $hash
            ]);
            echo "\n[SUCCESS] Single administrator account '{$username}' provisioned successfully.\n";
        }

        echo "Account is ready for secure login at /admin/login.\n";
        return 0;
    }
}

$provisioner = new AdminProvisioner();
exit($provisioner->run($argv));
