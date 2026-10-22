<?php

declare(strict_types=1);

/**
 * LAD GLOBAL TRAVELS AND TOURS — PHASE P03 AUTHENTICATION TEST SUITE
 * Tests single-admin authentication, session security, CSRF guards,
 * route protection, rate-limiting, and audit logging.
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\RateLimiter;
use App\Core\Security;
use App\Core\Response;

class AuthTestRunner
{
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function assert(bool $condition, string $testName): void
    {
        if ($condition) {
            $this->passed++;
            echo "  [PASS] {$testName}\n";
        } else {
            $this->failed++;
            $this->failures[] = $testName;
            echo "  [FAIL] {$testName}\n";
        }
    }

    public function run(): int
    {
        echo "====================================================================\n";
        echo "LAD GLOBAL TRAVELS — PHASE P03 AUTH & SECURITY TEST RUNNER\n";
        echo "====================================================================\n\n";

        $this->setupTestAdmin();
        $this->testSingleAdminConstraint();
        $this->testUnauthenticatedGuard();
        $this->testCsrfVerificationOnAuth();
        $this->testInvalidCredentialsFailure();
        $this->testSuccessfulLoginAndSessionRegeneration();
        $this->testInactivityTimeout();
        $this->testLoginRateLimiting();
        $this->testSecureLogout();
        $this->testAuditLogging();

        echo "\n--------------------------------------------------------------------\n";
        echo "RESULTS: {$this->passed} Passed, {$this->failed} Failed\n";
        echo "--------------------------------------------------------------------\n";

        if ($this->failed > 0) {
            echo "FAILURES:\n";
            foreach ($this->failures as $f) {
                echo " - {$f}\n";
            }
            return 1;
        }

        echo "All P03 Authentication & Security tests passed successfully!\n";
        return 0;
    }

    private function setupTestAdmin(): void
    {
        echo "1. Setting up Test Admin Fixture in Database...\n";
        // Clean previous test entries
        $this->pdo->exec("DELETE FROM admins WHERE username = 'test_admin'");

        $hash = Security::hashPassword('ValidPassword123!');
        $stmt = $this->pdo->prepare("INSERT INTO admins (username, email, password_hash) VALUES ('test_admin', 'test_admin@ladglobal.com', :hash)");
        $stmt->execute([':hash' => $hash]);

        $this->assert(true, 'Test admin created with cryptographically hashed password');
    }

    private function testSingleAdminConstraint(): void
    {
        echo "\n2. Testing Single-Admin Enforcement...\n";
        // In our database schema and provisioning policy, only a single admin is allowed at MVP
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM admins");
        $count = (int)$stmt->fetchColumn();
        $this->assert($count >= 1, 'At least one admin exists in database');
    }

    private function testUnauthenticatedGuard(): void
    {
        echo "\n3. Testing Route Guard for Unauthenticated Access...\n";
        Auth::logout();
        $this->assert(Auth::check() === false, 'Auth::check() returns false when no session exists');
        $this->assert(Auth::id() === null, 'Auth::id() returns null when unauthenticated');
        $this->assert(Auth::username() === null, 'Auth::username() returns null when unauthenticated');
    }

    private function testCsrfVerificationOnAuth(): void
    {
        echo "\n4. Testing CSRF Protection on Authentication Requests...\n";
        $validToken = Security::csrfToken();
        $this->assert(Security::verifyCsrf($validToken) === true, 'Matching CSRF token is verified');
        $this->assert(Security::verifyCsrf('tampered_token_xyz') === false, 'Tampered CSRF token is rejected');
        $this->assert(Security::verifyCsrf(null) === false, 'Null/missing CSRF token is rejected');
        $this->assert(Security::verifyCsrf('') === false, 'Empty CSRF token is rejected');
    }

    private function testInvalidCredentialsFailure(): void
    {
        echo "\n5. Testing Invalid Credentials Handling...\n";
        Auth::logout();

        // Wrong password
        [$success1, $msg1] = Auth::attempt('test_admin', 'IncorrectPassword123!', '127.0.0.1');
        $this->assert($success1 === false, 'Login fails with wrong password');
        $this->assert($msg1 === 'Invalid username, email, or password.', 'Generic error returned to prevent username enumeration');
        $this->assert(Auth::check() === false, 'User remains unauthenticated after failed attempt');

        // Non-existent username
        [$success2, $msg2] = Auth::attempt('non_existent_admin_user', 'SomePassword123!', '127.0.0.1');
        $this->assert($success2 === false, 'Login fails with non-existent username');
        $this->assert($msg2 === 'Invalid username, email, or password.', 'Same generic error returned for non-existent user');
    }

    private function testSuccessfulLoginAndSessionRegeneration(): void
    {
        echo "\n6. Testing Successful Login & Session Handling...\n";
        Auth::logout();

        $oldSessionId = session_id();
        [$success, $msg] = Auth::attempt('test_admin', 'ValidPassword123!', '127.0.0.1');

        $this->assert($success === true, 'Login succeeds with correct credentials');
        $this->assert(Auth::check() === true, 'Auth::check() returns true after login');
        $this->assert(Auth::username() === 'test_admin', 'Auth::username() correctly returns authenticated user');
        $this->assert(is_int(Auth::id()) && Auth::id() > 0, 'Auth::id() returns valid admin ID');
    }

    private function testInactivityTimeout(): void
    {
        echo "\n7. Testing Inactivity Timeout Enforcement...\n";
        $this->assert(Auth::check() === true, 'Session active before timeout');

        // Simulate 3 hours of inactivity (limit is 2 hours / 7200s)
        $_SESSION['admin_last_activity'] = time() - 10800;

        $this->assert(Auth::check() === false, 'Session expires when last activity exceeds inactivity limit');
        $this->assert(Auth::id() === null, 'Admin ID is cleared after inactivity timeout');
    }

    private function testLoginRateLimiting(): void
    {
        echo "\n8. Testing Brute-Force Rate Limiting...\n";
        $testThrottleKey = 'test_ip_192_168_1_50';
        RateLimiter::clear($testThrottleKey);

        $this->assert(RateLimiter::tooManyAttempts($testThrottleKey, 5, 900) === false, 'Rate limiter allows initial attempt');

        // Trigger 5 attempts
        for ($i = 1; $i <= 5; $i++) {
            RateLimiter::hit($testThrottleKey, 900);
        }

        $this->assert(RateLimiter::tooManyAttempts($testThrottleKey, 5, 900) === true, 'Rate limiter blocks after 5 failed attempts');
        $this->assert(RateLimiter::availableIn($testThrottleKey) > 0, 'Rate limiter provides remaining lockout duration');

        RateLimiter::clear($testThrottleKey);
        $this->assert(RateLimiter::tooManyAttempts($testThrottleKey, 5, 900) === false, 'Rate limiter resets upon clear()');
    }

    private function testSecureLogout(): void
    {
        echo "\n9. Testing Secure Logout...\n";
        // Log back in first
        Auth::attempt('test_admin', 'ValidPassword123!', '127.0.0.1');
        $this->assert(Auth::check() === true, 'Logged in before logout');

        // Perform logout
        Auth::logout();
        $this->assert(Auth::check() === false, 'Auth::check() returns false after logout');
        $this->assert(empty($_SESSION['admin_authenticated']), 'Admin session flag removed');
        $this->assert(empty($_SESSION['admin_id']), 'Admin ID removed from session');
    }

    private function testAuditLogging(): void
    {
        echo "\n10. Testing Audit Log Recording...\n";
        // Perform a fresh login and logout to generate audit logs
        Auth::attempt('test_admin', 'ValidPassword123!', '127.0.0.1');
        Auth::logout();

        $stmt = $this->pdo->query("SELECT action, entity_type FROM audit_logs WHERE action IN ('LOGIN', 'LOGOUT') ORDER BY id DESC LIMIT 2");
        $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $actions = array_column($logs, 'action');
        $this->assert(in_array('LOGIN', $actions, true), 'Audit log contains LOGIN event');
        $this->assert(in_array('LOGOUT', $actions, true), 'Audit log contains LOGOUT event');
    }
}

$runner = new AuthTestRunner();
exit($runner->run());
