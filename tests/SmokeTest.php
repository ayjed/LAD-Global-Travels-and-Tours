<?php

declare(strict_types=1);

/**
 * LAD GLOBAL TRAVELS AND TOURS — PHASE P02 SMOKE TEST SUITE
 * Zero external test runners required. Pure PHP standalone test runner.
 */

// Define root path
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/bootstrap.php';

use App\Core\Env;
use App\Core\Security;
use App\Core\Validator;
use App\Core\Router;
use App\Core\Database;
use App\Core\View;

class SmokeTestRunner
{
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];

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
        echo "LAD GLOBAL TRAVELS — PHASE P02 FOUNDATION SMOKE TEST RUNNER\n";
        echo "====================================================================\n\n";

        $this->testEnvLoader();
        $this->testSecurityHelpers();
        $this->testValidator();
        $this->testRouter();
        $this->testDatabaseExceptionShield();
        $this->testViewRendering();

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

        echo "All P02 smoke tests passed successfully!\n";
        return 0;
    }

    private function testEnvLoader(): void
    {
        echo "1. Testing Env Loader...\n";
        // Create temporary env file
        $tempEnv = ROOT_PATH . '/storage/temp/test.env';
        file_put_contents($tempEnv, "TEST_APP_NAME=\"Test Travel Co\"\nTEST_DEBUG=true\nTEST_PORT=3306\n# Comment line\n");

        Env::load($tempEnv);
        $this->assert(Env::get('TEST_APP_NAME') === 'Test Travel Co', 'Env loads quoted strings correctly');
        $this->assert(Env::get('TEST_DEBUG') === true, 'Env parses booleans correctly');
        $this->assert(Env::get('TEST_PORT') === '3306', 'Env loads numeric values');
        $this->assert(Env::get('NON_EXISTENT_KEY', 'default_val') === 'default_val', 'Env returns fallback default');

        if (file_exists($tempEnv)) {
            unlink($tempEnv);
        }
    }

    private function testSecurityHelpers(): void
    {
        echo "\n2. Testing Security & CSRF Helpers...\n";
        
        // HTML Escaping
        $dirty = '<script>alert("XSS")</script>&\'"';
        $escaped = Security::e($dirty);
        $this->assert(!str_contains($escaped, '<script>'), 'XSS script tag escaped');
        $this->assert(str_contains($escaped, '&lt;script&gt;'), 'HTML special characters converted');

        // CSRF Generation and Verification
        $token1 = Security::csrfToken();
        $this->assert(is_string($token1) && strlen($token1) === 64, 'CSRF token generated with 64 hex characters');

        $field = Security::csrfField();
        $this->assert(str_contains($field, 'type="hidden"') && str_contains($field, 'name="_csrf_token"'), 'CSRF field generates hidden input');

        $this->assert(Security::verifyCsrf($token1) === true, 'CSRF token verified successfully with matching token');
        $this->assert(Security::verifyCsrf('invalid_tampered_token') === false, 'CSRF verification rejects tampered token');
        $this->assert(Security::verifyCsrf(null) === false, 'CSRF verification rejects null token');

        // Password Hashing
        $pass = 'SuperSecret2026!';
        $hash = Security::hashPassword($pass);
        $this->assert(Security::verifyPassword($pass, $hash) === true, 'Password verification succeeds with correct password');
        $this->assert(Security::verifyPassword('WrongPassword', $hash) === false, 'Password verification fails with incorrect password');
    }

    private function testValidator(): void
    {
        echo "\n3. Testing Server-Side Input Validator...\n";

        // Valid data
        $validData = [
            'name' => 'Adebayo Adeleke',
            'email' => 'adebayo@example.com',
            'phone' => '+2347017597650',
            'service' => 'visa',
            'travellers' => '2'
        ];
        $rules = [
            'name' => 'required|min:3|max:100',
            'email' => 'required|email',
            'phone' => 'required|phone',
            'service' => 'required|in:visa,tours,flights,hotels',
            'travellers' => 'required|numeric|min:1|max:10'
        ];

        $v1 = Validator::make($validData, $rules);
        $this->assert($v1->passes(), 'Validator passes with clean valid data');

        // Invalid data
        $invalidData = [
            'name' => 'Ad', // too short
            'email' => 'not-an-email',
            'phone' => 'abc', // invalid phone
            'service' => 'unsupported_service',
            'travellers' => '99' // exceeds max 10
        ];
        $v2 = Validator::make($invalidData, $rules);
        $this->assert($v2->fails(), 'Validator fails when input violates rules');
        $this->assert(count($v2->getErrors()) === 5, 'Validator records errors for all 5 invalid fields');
    }

    private function testRouter(): void
    {
        echo "\n4. Testing HTTP Router...\n";

        $router = new Router();
        $dispatchedHome = false;
        $capturedSlug = null;
        $captured404 = false;

        $router->get('/', function () use (&$dispatchedHome) {
            $dispatchedHome = true;
            return 'HOME';
        });

        $router->get('/packages/{slug}', function ($params) use (&$capturedSlug) {
            $capturedSlug = $params['slug'] ?? null;
            return 'PACKAGE_DETAIL';
        });

        $router->setNotFoundHandler(function () use (&$captured404) {
            $captured404 = true;
            return 'NOT_FOUND';
        });

        // Test static route dispatch
        $res1 = $router->dispatch('GET', '/');
        $this->assert($dispatchedHome && $res1 === 'HOME', 'Router dispatches static root path');

        // Test parameterized route dispatch
        $res2 = $router->dispatch('GET', '/packages/nairobi-safari');
        $this->assert($capturedSlug === 'nairobi-safari' && $res2 === 'PACKAGE_DETAIL', 'Router extracts dynamic route parameter {slug}');

        // Test 404 dispatch
        $res3 = $router->dispatch('GET', '/non-existent-page-url');
        $this->assert($captured404 && $res3 === 'NOT_FOUND', 'Router executes 404 handler on missing route');

        // Test method mismatch (POST on GET route)
        ob_start();
        $router->dispatch('POST', '/packages/nairobi-safari');
        $output = ob_get_clean();
        $this->assert(http_response_code() === 405, 'Router responds with 405 Method Not Allowed on method mismatch');
    }

    private function testDatabaseExceptionShield(): void
    {
        echo "\n5. Testing Database Exception Shielding...\n";

        // Attempt connection to non-existent DB host with dummy credentials
        $testConfig = [
            'host' => '127.0.0.1',
            'port' => 65530, // Unlikely to be running MySQL
            'database' => 'fake_db',
            'username' => 'fake_user',
            'password' => 'secret_password_do_not_leak'
        ];

        Database::reset();
        [$success, $message] = Database::testConnection($testConfig);
        $this->assert($success === false, 'Database gracefully returns failure on unreachable host');
        $this->assert(!str_contains($message, 'secret_password_do_not_leak'), 'Database error message never leaks credentials');
        Database::reset();
    }

    private function testViewRendering(): void
    {
        echo "\n6. Testing View Engine...\n";

        ob_start();
        View::render('pages/404', ['pageTitle' => 'Test 404'], 'main');
        $html = ob_get_clean();

        $this->assert(str_contains($html, '<!DOCTYPE html>'), 'View renders complete HTML document');
        $this->assert(str_contains($html, 'Page Not Found'), 'View renders 404 content slot');
        $this->assert(str_contains($html, 'LAD Global Travels'), 'View wraps content in main layout');
    }
}

// Execute test runner
$runner = new SmokeTestRunner();
exit($runner->run());
