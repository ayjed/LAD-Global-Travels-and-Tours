<?php

declare(strict_types=1);

/**
 * LAD Global Travels and Tours — Phase P10 Deployment and Handover Test Suite
 * 
 * Verifies production deployment runbooks, admin user guide, rollback procedures,
 * credential handover protocols, client acceptance form, .htaccess security headers (CSP, HSTS,
 * Permissions-Policy), Paystack live/test separation invariants, and database migration sequence.
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Env;
use App\Services\PaystackService;

$passed = 0;
$failed = 0;

function assertCondition(bool $condition, string $description): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$description}" . PHP_EOL;
    } else {
        $failed++;
        echo "  [FAIL] {$description}" . PHP_EOL;
    }
}

echo "====================================================================" . PHP_EOL;
echo "LAD GLOBAL TRAVELS — PHASE P10 DEPLOYMENT & HANDOVER TEST SUITE" . PHP_EOL;
echo "====================================================================" . PHP_EOL;

$rootDir = realpath(__DIR__ . '/..');

// --------------------------------------------------------------------------
// 1. Verifying Required P10 Runbooks and Handover Artifacts
// --------------------------------------------------------------------------
echo PHP_EOL . "1. Auditing P10 Runbooks, Guides & Handover Artifacts..." . PHP_EOL;

$requiredDocs = [
    'PRODUCTION_DEPLOYMENT_RUNBOOK.md'          => ['cPanel', 'Document Root', 'chmod 600', 'Paystack Live vs. Test'],
    'ADMIN_OPERATIONS_AND_USAGE_GUIDE.md'       => ['Signing In', 'Active Packages', 'Tour Package Management', 'Reconciling Direct Bank Transfers', 'Client Authorization Certification'],
    'ROLLBACK_AND_DISASTER_RECOVERY_GUIDE.md'   => ['Emergency Maintenance Mode', 'Fast Codebase Rollback', 'Database Rollback', 'Media & Uploads Rollback'],
    'CREDENTIAL_HANDOVER_AND_SECURITY_PROCEDURE.md' => ['Zero Plaintext Transmission', 'Production Credentials Inventory', 'Immediate Post-Handover Rotation', 'Formal Handover Acceptance'],
    'FINAL_CLIENT_LAUNCH_ACCEPTANCE_FORM.md'    => ['Project Delivery & Milestone Summary', 'Functional Acceptance Checklist', 'Formal Acceptance Sign-Off']
];

foreach ($requiredDocs as $filename => $expectedPhrases) {
    $filePath = $rootDir . '/' . $filename;
    assertCondition(file_exists($filePath), "Required artifact exists: {$filename}");
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        foreach ($expectedPhrases as $phrase) {
            assertCondition(str_contains($content, $phrase), "{$filename} contains section: '{$phrase}'");
        }
    }
}

// --------------------------------------------------------------------------
// 2. Auditing Production .htaccess Security Headers & Rewrite Rules
// --------------------------------------------------------------------------
echo PHP_EOL . "2. Auditing Production .htaccess Security & CSP Headers..." . PHP_EOL;

$htaccessPath = $rootDir . '/public/.htaccess';
assertCondition(file_exists($htaccessPath), "public/.htaccess exists");
$htaccess = file_get_contents($htaccessPath);

// HTTPS Rewrite Rule
assertCondition(str_contains($htaccess, 'RewriteCond %{HTTPS} !=on'), ".htaccess enforces HTTPS redirect");
assertCondition(str_contains($htaccess, 'localhost|127\.0\.0\.1'), ".htaccess HTTPS rule safely exempts local development");

// Standard Security Headers
assertCondition(str_contains($htaccess, 'X-Content-Type-Options "nosniff"'), ".htaccess sets X-Content-Type-Options nosniff");
assertCondition(str_contains($htaccess, 'X-Frame-Options "SAMEORIGIN"'), ".htaccess sets X-Frame-Options SAMEORIGIN");
assertCondition(str_contains($htaccess, 'Referrer-Policy "strict-origin-when-cross-origin"'), ".htaccess sets Referrer-Policy");
assertCondition(str_contains($htaccess, 'Permissions-Policy'), ".htaccess sets Permissions-Policy");

// HSTS (Strict-Transport-Security)
assertCondition(str_contains($htaccess, 'Strict-Transport-Security "max-age=31536000'), ".htaccess sets HSTS max-age=31536000");

// Content Security Policy (Paystack-compatible)
assertCondition(str_contains($htaccess, 'Content-Security-Policy'), ".htaccess defines Content-Security-Policy");
assertCondition(str_contains($htaccess, 'https://js.paystack.co'), "CSP allows Paystack script src (https://js.paystack.co)");
assertCondition(str_contains($htaccess, 'https://api.paystack.co'), "CSP allows Paystack connect src (https://api.paystack.co)");
assertCondition(str_contains($htaccess, 'https://checkout.paystack.com'), "CSP allows Paystack frame src (https://checkout.paystack.com)");

// --------------------------------------------------------------------------
// 3. Testing Paystack Live vs. Test Key Separation Invariants
// --------------------------------------------------------------------------
echo PHP_EOL . "3. Testing Paystack Live / Test Mode Separation Invariants..." . PHP_EOL;

// Backup current key
$currentKey = PaystackService::getSecretKey();

// Test mode key
Env::set('PAYSTACK_SECRET_KEY', 'sk_test_mock_secret_key_123456');
assertCondition(PaystackService::isTestMode() === true, "PaystackService recognizes sk_test_ prefix as test mode");
assertCondition(PaystackService::isLiveMode() === false, "PaystackService correctly flags isLiveMode() = false for test key");

// Live mode key
Env::set('PAYSTACK_SECRET_KEY', 'sk_live_mock_secret_key_789012');
assertCondition(PaystackService::isLiveMode() === true, "PaystackService recognizes sk_live_ prefix as live mode");
assertCondition(PaystackService::isTestMode() === false, "PaystackService correctly flags isTestMode() = false for live key");

// Restore current key
Env::set('PAYSTACK_SECRET_KEY', $currentKey);

// --------------------------------------------------------------------------
// 4. Auditing Sequential Database Migration Scripts
// --------------------------------------------------------------------------
echo PHP_EOL . "4. Auditing Database Migration Scripts Sequence..." . PHP_EOL;

$migrations = [
    '001_create_admins_table.sql' => ['CREATE TABLE IF NOT EXISTS admins'],
    '002_create_packages_and_images_tables.sql' => ['CREATE TABLE IF NOT EXISTS packages', 'CREATE TABLE IF NOT EXISTS package_images'],
    '003_create_enquiries_and_notes_tables.sql' => ['CREATE TABLE IF NOT EXISTS enquiries', 'CREATE TABLE IF NOT EXISTS enquiry_notes'],
    '004_create_bookings_and_payments_tables.sql' => ['CREATE TABLE IF NOT EXISTS bookings', 'CREATE TABLE IF NOT EXISTS payments'],
    '005_create_settings_and_audit_tables.sql' => ['CREATE TABLE IF NOT EXISTS settings', 'CREATE TABLE IF NOT EXISTS payment_events', 'CREATE TABLE IF NOT EXISTS audit_logs']
];

foreach ($migrations as $migFile => $expectedClauses) {
    $fullMigPath = $rootDir . '/database/migrations/' . $migFile;
    assertCondition(file_exists($fullMigPath), "Migration script exists: {$migFile}");
    if (file_exists($fullMigPath)) {
        $sql = file_get_contents($fullMigPath);
        foreach ($expectedClauses as $clause) {
            assertCondition(str_contains($sql, $clause), "{$migFile} contains clause: '{$clause}'");
        }
    }
}

// --------------------------------------------------------------------------
// 5. Auditing Webroot Boundary & Private Code Protection
// --------------------------------------------------------------------------
echo PHP_EOL . "5. Auditing Webroot Boundary & Private Code Isolation..." . PHP_EOL;

$privateDirs = ['app', 'storage', 'database', 'bin', 'tests'];
foreach ($privateDirs as $dir) {
    assertCondition(!file_exists($rootDir . '/public/' . $dir), "Private directory '{$dir}' is NOT inside public webroot");
    assertCondition(file_exists($rootDir . '/' . $dir), "Private directory '{$dir}' exists outside public webroot");
}

assertCondition(!file_exists($rootDir . '/public/.env'), "Production .env is NOT inside public webroot");
assertCondition(file_exists($rootDir . '/.env'), "Production .env exists outside public webroot");

// Public directory only contains front controller and public assets
assertCondition(file_exists($rootDir . '/public/index.php'), "public/index.php front controller exists");
assertCondition(file_exists($rootDir . '/public/robots.txt'), "public/robots.txt exists");
assertCondition(file_exists($rootDir . '/public/assets/css/app.css'), "public/assets/css/app.css exists");
assertCondition(file_exists($rootDir . '/public/assets/js/app.js'), "public/assets/js/app.js exists");
assertCondition(file_exists($rootDir . '/public/uploads/.htaccess'), "public/uploads/.htaccess execution denial exists");

echo PHP_EOL . "====================================================================" . PHP_EOL;
echo "TEST RESULTS: {$passed} PASSED, {$failed} FAILED" . PHP_EOL;
echo "====================================================================" . PHP_EOL;

if ($failed > 0) {
    exit(1);
}
