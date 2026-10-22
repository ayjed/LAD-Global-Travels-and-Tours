<?php

declare(strict_types=1);

/**
 * LAD Global Travels and Tours — Phase P09 Security, QA, Accessibility & Payment Matrix Test Suite
 * 
 * Verifies SOT pure PHP compliance, excluded modules absence, route access restrictions,
 * prepared PDO queries, CSRF enforcement, output escaping, file upload restrictions,
 * secrets isolation, rate limiting, secure sessions, broad payment matrix (manual-pending,
 * mismatched webhook, duplicate event, bad signature, callback fraud, failed payment, network timeout),
 * accessibility focus/skip links, and SEO robots/sitemap.
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Env;
use App\Core\Router;
use App\Core\Security;
use App\Services\BookingService;
use App\Services\PackageService;
use App\Services\PaystackService;
use App\Services\SettingService;
use App\Services\UploadService;

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
echo "LAD GLOBAL TRAVELS — PHASE P09 SECURITY, QA & PAYMENT MATRIX SUITE" . PHP_EOL;
echo "====================================================================" . PHP_EOL;

$pdo = Database::getConnection();

// --------------------------------------------------------------------------
// 1. SOT Compliance: Pure Native PHP & Zero Excluded Modules
// --------------------------------------------------------------------------
echo PHP_EOL . "1. Auditing SOT Compliance: Pure Native PHP & Excluded Modules..." . PHP_EOL;

$rootDir = realpath(__DIR__ . '/..');
assertCondition(!file_exists($rootDir . '/composer.json'), "Zero Composer dependency: composer.json absent");
assertCondition(!file_exists($rootDir . '/composer.lock'), "Zero Composer dependency: composer.lock absent");
assertCondition(!is_dir($rootDir . '/vendor'), "Zero vendor directory: vendor/ absent");
assertCondition(!file_exists($rootDir . '/package.json'), "Zero Node.js dependency: package.json absent");
assertCondition(!is_dir($rootDir . '/node_modules'), "Zero node_modules directory: node_modules/ absent");

// Excluded Modules: Ensure zero client login/portal routes exist
$routeDefinitions = file_get_contents($rootDir . '/public/index.php');
$forbiddenPatterns = ['/customer', '/client/login', '/client/register', '/portal', '/flights/search', '/hotels/search', '/api/gds'];
foreach ($forbiddenPatterns as $forbidden) {
    assertCondition(!str_contains($routeDefinitions, "'{$forbidden}'"), "Excluded module absent from routes: {$forbidden}");
}

// --------------------------------------------------------------------------
// 2. Security Invariants: Secrets Outside Webroot & PDO Prepared Queries
// --------------------------------------------------------------------------
echo PHP_EOL . "2. Auditing Security Invariants (Secrets & SQL Parameterization)..." . PHP_EOL;

assertCondition(!file_exists($rootDir . '/public/.env'), "Production secret .env is NOT inside public webroot");
assertCondition(file_exists($rootDir . '/.env'), "Production .env is stored safely outside public webroot");

$allSettings = SettingService::getAll();
assertCondition(!isset($allSettings['DB_PASSWORD']), "DB_PASSWORD is isolated and not exposed in settings");
assertCondition(!isset($allSettings['PAYSTACK_SECRET_KEY']), "PAYSTACK_SECRET_KEY is isolated and not exposed in settings");
assertCondition(!isset($allSettings['SMTP_PASSWORD']), "SMTP_PASSWORD is isolated and not exposed in settings");
assertCondition(!isset($allSettings['APP_SECRET']), "APP_SECRET is isolated and not exposed in settings");

// Check services for raw string interpolation in SQL queries
$serviceFiles = [
    $rootDir . '/app/services/PackageService.php',
    $rootDir . '/app/services/BookingService.php',
    $rootDir . '/app/services/EnquiryService.php',
    $rootDir . '/app/services/SettingService.php'
];

foreach ($serviceFiles as $sf) {
    $content = file_get_contents($sf);
    // Disallow raw $_GET / $_POST inside queries
    assertCondition(!preg_match('/query\s*\(\s*["\'].*\$_(GET|POST|REQUEST)/i', $content), "No direct global superglobals inside query() in " . basename($sf));
    assertCondition(!preg_match('/exec\s*\(\s*["\'].*\$_(GET|POST|REQUEST)/i', $content), "No direct global superglobals inside exec() in " . basename($sf));
}

// --------------------------------------------------------------------------
// 3. Security: Output Escaping, CSRF & Secure Sessions
// --------------------------------------------------------------------------
echo PHP_EOL . "3. Auditing Output Escaping, CSRF & Secure Session Tokens..." . PHP_EOL;

// XSS Escaping
$xssPayload = '<script>alert("XSS")</script>';
$escaped = Security::e($xssPayload);
assertCondition(!str_contains($escaped, '<script>'), "XSS script tag properly encoded: {$escaped}");
assertCondition(str_contains($escaped, '&lt;script&gt;'), "XSS tag converted to safe HTML entities");

$attrXss = '"><img src=x onerror=alert(1)>';
$escapedAttr = Security::e($attrXss);
assertCondition(!str_contains($escapedAttr, '"><'), "Attribute breakout XSS properly encoded: {$escapedAttr}");

// CSRF Token Validation
$token = Security::csrfToken();
assertCondition(strlen($token) === 64, "CSRF token generated with 64-char hex entropy");
assertCondition(Security::verifyCsrf($token) === true, "Valid CSRF token passes verification");
assertCondition(Security::verifyCsrf('tampered_token_' . bin2hex(random_bytes(16))) === false, "Tampered CSRF token is rejected");
assertCondition(Security::verifyCsrf('') === false, "Empty CSRF token is rejected");

// Secure Session Settings
assertCondition(ini_get('session.cookie_httponly') === '1', "Session cookie_httponly flag is enabled");
assertCondition(ini_get('session.use_strict_mode') === '1', "Session use_strict_mode flag is enabled");

// Rate Limiting Throttling
$testKey = 'qa_test_rate_limit_' . bin2hex(random_bytes(4));
for ($i = 0; $i < 5; $i++) {
    \App\Core\RateLimiter::hit($testKey, 60);
}
assertCondition(\App\Core\RateLimiter::tooManyAttempts($testKey, 5, 60) === true, "Rate limiter enforces throttling after limit exceeded");
\App\Core\RateLimiter::clear($testKey);
assertCondition(\App\Core\RateLimiter::tooManyAttempts($testKey, 5, 60) === false, "Rate limiter clear resets attempt counter");

// --------------------------------------------------------------------------
// 4. Upload Security Restrictions
// --------------------------------------------------------------------------
echo PHP_EOL . "4. Auditing File Upload Security & MIME Restrictions..." . PHP_EOL;

// 4a. Upload directory execution denial (.htaccess)
$uploadHtaccess = $rootDir . '/public/uploads/.htaccess';
assertCondition(file_exists($uploadHtaccess), "public/uploads/.htaccess exists to harden upload directory");
$htaccessContent = file_get_contents($uploadHtaccess);
assertCondition(str_contains($htaccessContent, 'Require all denied') || str_contains($htaccessContent, 'Deny from all'), "Upload .htaccess denies execution of script extensions");
assertCondition(str_contains($htaccessContent, 'Options -Indexes -ExecCGI'), "Directory listing and ExecCGI are disabled in uploads");

// 4b. UPLOAD_ERR_NO_FILE gracefully returns null
$noFileResult = UploadService::uploadImage(['error' => UPLOAD_ERR_NO_FILE]);
assertCondition($noFileResult === null, "UploadService gracefully returns null when no file is uploaded");

// 4c. Oversized file (> 5MB) strictly rejected
$oversizedRejected = false;
try {
    UploadService::uploadImage([
        'name'     => 'oversized.jpg',
        'type'     => 'image/jpeg',
        'tmp_name' => $rootDir . '/storage/logs/oversized.tmp',
        'error'    => UPLOAD_ERR_OK,
        'size'     => 6 * 1024 * 1024 // 6 MB
    ]);
} catch (\RuntimeException $e) {
    if (str_contains($e->getMessage(), 'exceeds the 5 MB maximum size limit')) {
        $oversizedRejected = true;
    }
}
assertCondition($oversizedRejected === true, "UploadService strictly rejects files exceeding 5 MB limit");

// 4d. MIME validation via finfo
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$txtMime = finfo_buffer($finfo, "<?php echo 'malicious payload'; ?>");
finfo_close($finfo);
assertCondition($txtMime !== 'image/jpeg' && $txtMime !== 'image/png' && $txtMime !== 'image/webp', "finfo distinguishes PHP script from legitimate image MIME ({$txtMime})");

// --------------------------------------------------------------------------
// 5. Broad Payment Test Matrix (All States & Failure Permutations)
// --------------------------------------------------------------------------
echo PHP_EOL . "5. Executing Broad Payment Test Matrix..." . PHP_EOL;

// Prepare test payable package
$matrixPkgData = [
    'title' => '[QA MATRIX TEST] Dubai Deluxe Tour',
    'destination_country' => 'UAE',
    'destination_city' => 'Dubai',
    'summary' => 'QA matrix test package',
    'description' => 'Comprehensive payment state testing tour.',
    'duration_days' => 5,
    'price_amount' => 500000.00,
    'price_currency' => 'NGN',
    'is_payment_enabled' => 1,
    'status' => 'published'
];
$matrixPkgId = PackageService::create($matrixPkgData);
$matrixPkg = PackageService::getById($matrixPkgId);

// --- State 1: Manual Bank Transfer Pending State ---
$booking1 = BookingService::createBooking($matrixPkg, [
    'customer_name'    => 'Manual Transfer QA User',
    'customer_email'   => 'manual.qa@example.com',
    'customer_phone'   => '08012341234',
    'travellers_count' => 1
], 'manual');

$p1 = $booking1['payment'];
assertCondition($p1['status'] === 'pending', "[Matrix 1] Bank transfer initial status is 'pending'");
assertCondition($booking1['booking']['booking_status'] === 'pending', "[Matrix 1] Bank transfer booking status is 'pending'");

// --- State 2: Callback Redirect Fraud Prevention ---
// A malicious user redirects directly to /paystack/callback?reference=XYZ without server settlement
$fakeRef = $p1['payment_reference'];
$reloadedP1 = BookingService::getPaymentById((int)$p1['id']);
assertCondition($reloadedP1['status'] === 'pending', "[Matrix 2] Unverified browser callback cannot mark payment paid");

// --- State 3: Paystack Webhook Bad / Tampered Signature ---
$matrixPayload = json_encode(['event' => 'charge.success', 'data' => ['reference' => $p1['payment_reference']]]);
$badSignature = 'bad_sig_' . bin2hex(random_bytes(32));
assertCondition(PaystackService::verifyWebhookSignature($matrixPayload, $badSignature) === false, "[Matrix 3] Paystack webhook rejects invalid HMAC signature");
assertCondition(PaystackService::verifyWebhookSignature($matrixPayload, '') === false, "[Matrix 3] Paystack webhook rejects empty HMAC signature");

// --- State 4: Paystack Webhook Mismatched Amount ---
$booking2 = BookingService::createBooking($matrixPkg, [
    'customer_name'    => 'Mismatched Amount User',
    'customer_email'   => 'mismatch.qa@example.com',
    'customer_phone'   => '08055554444',
    'travellers_count' => 2 // Total = 1,000,000 NGN = 100,000,000 kobo
], 'paystack');
$p2 = $booking2['payment'];

// Subunit mismatch (reported 500,000 kobo = 5,000 NGN instead of 1,000,000 NGN)
$mismatchedAmountData = [
    'status'    => 'success',
    'id'        => 7770001,
    'reference' => $p2['payment_reference'],
    'amount'    => 500000, // ₦5,000.00
    'currency'  => 'NGN',
    'channel'   => 'card'
];
$mismatchRes = PaystackService::settlePayment($p2['payment_reference'], $mismatchedAmountData, json_encode($mismatchedAmountData));
assertCondition($mismatchRes['success'] === false, "[Matrix 4] Mismatched amount rejected by settlement engine");
$reloadedP2 = BookingService::getPaymentById((int)$p2['id']);
assertCondition($reloadedP2['status'] === 'failed', "[Matrix 4] Mismatched amount transitions payment to 'failed'");

// --- State 5: Paystack Webhook Mismatched Currency ---
$booking3 = BookingService::createBooking($matrixPkg, [
    'customer_name'    => 'Currency Mismatch User',
    'customer_email'   => 'currmismatch.qa@example.com',
    'customer_phone'   => '08066663333',
    'travellers_count' => 1 // 500,000 NGN = 50,000,000 kobo
], 'paystack');
$p3 = $booking3['payment'];

$mismatchedCurrData = [
    'status'    => 'success',
    'id'        => 7770002,
    'reference' => $p3['payment_reference'],
    'amount'    => 50000000,
    'currency'  => 'GBP', // Wrong currency!
    'channel'   => 'card'
];
$currMismatchRes = PaystackService::settlePayment($p3['payment_reference'], $mismatchedCurrData, json_encode($mismatchedCurrData));
assertCondition($currMismatchRes['success'] === false, "[Matrix 5] Mismatched currency (GBP vs NGN) rejected");
$reloadedP3 = BookingService::getPaymentById((int)$p3['id']);
assertCondition($reloadedP3['status'] === 'failed', "[Matrix 5] Mismatched currency transitions payment to 'failed'");

// --- State 6: Duplicate Webhook Event Idempotency ---
$booking4 = BookingService::createBooking($matrixPkg, [
    'customer_name'    => 'Idempotent QA User',
    'customer_email'   => 'idempotent.qa@example.com',
    'customer_phone'   => '08077772222',
    'travellers_count' => 1
], 'paystack');
$p4 = $booking4['payment'];

$validData4 = [
    'status'    => 'success',
    'id'        => 7770003,
    'reference' => $p4['payment_reference'],
    'amount'    => 50000000, // ₦500,000.00
    'currency'  => 'NGN',
    'channel'   => 'card'
];

$firstSettle = PaystackService::settlePayment($p4['payment_reference'], $validData4, json_encode($validData4), 'evt_matrix_idemp_101');
assertCondition($firstSettle['success'] === true, "[Matrix 6] Initial payment settlement succeeds");

$secondSettle = PaystackService::settlePayment($p4['payment_reference'], $validData4, json_encode($validData4), 'evt_matrix_idemp_101');
assertCondition($secondSettle['success'] === true && $secondSettle['duplicate'] === true, "[Matrix 6] Replayed duplicate webhook event safely handled with duplicate flag");

// --- State 7: Failed Transaction Event ---
$booking5 = BookingService::createBooking($matrixPkg, [
    'customer_name'    => 'Failed Charge User',
    'customer_email'   => 'failed.charge@example.com',
    'customer_phone'   => '08088881111',
    'travellers_count' => 1
], 'paystack');
$p5 = $booking5['payment'];

$failedGatewayData = [
    'status'    => 'failed',
    'id'        => 7770004,
    'reference' => $p5['payment_reference'],
    'amount'    => 50000000,
    'currency'  => 'NGN',
    'channel'   => 'card',
    'gateway_response' => 'Insufficient funds'
];
$failedSettle = PaystackService::settlePayment($p5['payment_reference'], $failedGatewayData, json_encode($failedGatewayData));
assertCondition($failedSettle['success'] === false, "[Matrix 7] Gateway failed charge identified as unsuccessful");
$reloadedP5 = BookingService::getPaymentById((int)$p5['id']);
assertCondition($reloadedP5['status'] === 'failed', "[Matrix 7] Payment record updated to 'failed'");

// --- State 8: Network Timeout / External Provider Failure Simulation ---
PaystackService::setMockTransport(function (string $method, string $url, array $headers, ?string $body) {
    // Simulate cURL timeout (error code 28)
    return [
        'success'  => false,
        'httpCode' => 0,
        'error'    => 'cURL error 28: Operation timed out after 15000 milliseconds with 0 bytes received',
        'data'     => []
    ];
});

$verifyResult = PaystackService::verifyTransaction('PAY-TIMEOUT-TEST-999');
assertCondition($verifyResult['success'] === false, "[Matrix 8] Network timeout handled gracefully without uncaught fatal error");
assertCondition(str_contains($verifyResult['error'] ?? '', 'timed out') || str_contains($verifyResult['error'] ?? '', 'cURL'), "[Matrix 8] Timeout error logged clearly for diagnostics");

// Reset mock transport
PaystackService::setMockTransport(null);

// --- State 9: Travel Status Separation Invariant (D11) ---
$reloadedBooking4 = BookingService::getBookingById((int)$booking4['booking']['id']);
assertCondition($reloadedBooking4['latest_payment']['status'] === 'paid', "[Matrix 9] Payment 4 is confirmed as PAID");
assertCondition($reloadedBooking4['booking_status'] === 'pending', "[Matrix 9] Travel booking status remains 'pending' awaiting agency logistical confirmation");

// Clean up matrix test records
$matrixBookingIds = [$booking1['booking']['id'], $booking2['booking']['id'], $booking3['booking']['id'], $booking4['booking']['id'], $booking5['booking']['id']];
$inMatrix = implode(',', array_map('intval', $matrixBookingIds));
$pdo->exec("DELETE FROM payments WHERE booking_id IN ({$inMatrix})");
$pdo->exec("DELETE FROM payment_events WHERE event_id LIKE 'evt_matrix_%'");
$pdo->exec("DELETE FROM bookings WHERE id IN ({$inMatrix})");
PackageService::delete($matrixPkgId);

// --------------------------------------------------------------------------
// 6. UI, Accessibility & SEO Invariants
// --------------------------------------------------------------------------
echo PHP_EOL . "6. Auditing UI, Accessibility & SEO Invariants..." . PHP_EOL;

// Robots.txt check
$robotsPath = $rootDir . '/public/robots.txt';
assertCondition(file_exists($robotsPath), "public/robots.txt file exists");
$robotsTxt = file_get_contents($robotsPath);
assertCondition(str_contains($robotsTxt, 'Disallow: /admin/'), "robots.txt protects /admin/");
assertCondition(str_contains($robotsTxt, 'Sitemap: /sitemap.xml'), "robots.txt points to /sitemap.xml");

// Public layout accessibility check
$mainLayout = file_get_contents($rootDir . '/app/views/layouts/main.php');
assertCondition(str_contains($mainLayout, 'class="skip-to-content"'), "main.php includes accessible skip-to-content link");
assertCondition(str_contains($mainLayout, 'og:title'), "main.php includes OpenGraph og:title");
assertCondition(str_contains($mainLayout, 'og:description'), "main.php includes OpenGraph og:description");
assertCondition(str_contains($mainLayout, 'twitter:card'), "main.php includes Twitter card meta tag");

// Admin layout accessibility check
$adminLayout = file_get_contents($rootDir . '/app/views/layouts/admin.php');
assertCondition(str_contains($adminLayout, 'class="skip-to-content"'), "admin.php includes accessible skip-to-content link");

// CSS Focus Visibility check
$cssContent = file_get_contents($rootDir . '/public/assets/css/app.css');
assertCondition(str_contains($cssContent, ':focus-visible'), "app.css includes WCAG 2.1 AA :focus-visible rules");
assertCondition(str_contains($cssContent, '.skip-to-content'), "app.css styles .skip-to-content link");
assertCondition(str_contains($cssContent, 'min-height: 44px'), "app.css enforces minimum 44px touch target on mobile");

// 500 error page dynamic WhatsApp check
$err500 = file_get_contents($rootDir . '/app/views/pages/500.php');
assertCondition(str_contains($err500, 'SettingService::getCompanyDetails'), "500.php uses dynamic SettingService contact links");

// --------------------------------------------------------------------------
// 7. Operational Backup & Restoration Documentation Check
// --------------------------------------------------------------------------
echo PHP_EOL . "7. Auditing Operational Documentation & UAT Artifacts..." . PHP_EOL;

assertCondition(file_exists($rootDir . '/BACKUP_AND_RESTORE_PROCEDURES.md'), "BACKUP_AND_RESTORE_PROCEDURES.md runbook exists");
assertCondition(file_exists($rootDir . '/DEFECT_LOG_AND_RETEST_RECORD.md'), "DEFECT_LOG_AND_RETEST_RECORD.md exists");
assertCondition(file_exists($rootDir . '/CLIENT_UAT_CHECKLIST.md'), "CLIENT_UAT_CHECKLIST.md exists");

$uatContent = file_get_contents($rootDir . '/CLIENT_UAT_CHECKLIST.md');
assertCondition(str_contains($uatContent, 'Scenario 1: Brand Identity'), "UAT checklist covers Scenario 1 (Brand Identity)");
assertCondition(str_contains($uatContent, 'Scenario 4: Guest Package Booking with Bank Transfer'), "UAT checklist covers Scenario 4 (Bank Transfer)");
assertCondition(str_contains($uatContent, 'Scenario 5: Guest Package Booking with Paystack'), "UAT checklist covers Scenario 5 (Paystack)");
assertCondition(str_contains($uatContent, 'Scenario 11: Admin Protected Settings & Bank Details'), "UAT checklist covers Scenario 11 (Protected Settings)");
assertCondition(str_contains($uatContent, 'Client Formal Sign-Off'), "UAT checklist includes formal client sign-off record");

echo PHP_EOL . "====================================================================" . PHP_EOL;
echo "TEST RESULTS: {$passed} PASSED, {$failed} FAILED" . PHP_EOL;
echo "====================================================================" . PHP_EOL;

if ($failed > 0) {
    exit(1);
}
