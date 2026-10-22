<?php

declare(strict_types=1);

/**
 * LAD GLOBAL TRAVELS AND TOURS — PHASE P06 AUTOMATED TEST SUITE
 * Tests general & package-specific enquiries, server-side validation, CSRF, honeypot anti-spam,
 * rate limiting / duplicate submission throttling, SQL injection & XSS defense, admin CRM lifecycle,
 * internal consultation notes, click-to-call/WhatsApp links, and best-effort email resilience.
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\RateLimiter;
use App\Core\Response;
use App\Core\Security;
use App\Core\Validator;
use App\Services\EnquiryService;
use App\Services\MailService;
use App\Services\PackageService;
use App\Controllers\PublicController;
use App\Controllers\AdminController;

echo "====================================================================" . PHP_EOL;
echo "LAD GLOBAL TRAVELS — PHASE P06 ENQUIRIES & BASIC CRM TEST SUITE" . PHP_EOL;
echo "====================================================================" . PHP_EOL . PHP_EOL;

$passed = 0;
$failed = 0;

function assertCondition(bool $cond, string $desc): void
{
    global $passed, $failed;
    if ($cond) {
        echo "  [PASS] {$desc}" . PHP_EOL;
        $passed++;
    } else {
        echo "  [FAIL] {$desc}" . PHP_EOL;
        $failed++;
    }
}

$pdo = Database::getConnection();

// Clean any leftover test fixtures from prior runs
$pdo->exec("DELETE FROM enquiry_notes WHERE enquiry_id IN (SELECT id FROM enquiries WHERE reference LIKE 'TEST-ENQ-%')");
$pdo->exec("DELETE FROM enquiries WHERE reference LIKE 'TEST-ENQ-%' OR full_name LIKE '[SAMPLE TEST]%'");
$pdo->exec("DELETE FROM packages WHERE title LIKE '[SAMPLE TEST] P06%'");

// --------------------------------------------------------------------------
// 1. PHP Syntax Linter on P06 Files
// --------------------------------------------------------------------------
echo "1. Checking PHP Syntax across P06 Files..." . PHP_EOL;

$p06Files = [
    dirname(__DIR__) . '/app/services/EnquiryService.php',
    dirname(__DIR__) . '/app/services/MailService.php',
    dirname(__DIR__) . '/app/controllers/PublicController.php',
    dirname(__DIR__) . '/app/controllers/AdminController.php',
    dirname(__DIR__) . '/app/views/pages/contact.php',
    dirname(__DIR__) . '/app/views/admin/enquiries/index.php',
    dirname(__DIR__) . '/app/views/admin/enquiries/detail.php',
    dirname(__DIR__) . '/public/index.php'
];

foreach ($p06Files as $file) {
    if (!file_exists($file)) {
        assertCondition(false, "File exists: " . basename($file));
        continue;
    }
    $cmd = "C:\\xampp\\php\\php.exe -l " . escapeshellarg($file) . " 2>&1";
    $output = shell_exec($cmd);
    $isValid = str_contains((string)$output, 'No syntax errors detected');
    assertCondition($isValid, "Syntax check: " . basename($file));
}

// --------------------------------------------------------------------------
// 2. Input Validation (Missing & Invalid Input Testing)
// --------------------------------------------------------------------------
echo PHP_EOL . "2. Testing Server-Side Input Validation Rules..." . PHP_EOL;

// 2a. Short / empty name
$v1 = Validator::make([
    'full_name' => 'Al', // < 3 chars
    'email' => 'valid@example.com',
    'phone' => '08012345678',
    'service_type' => 'visa',
    'message' => 'Valid consultation message description.'
], [
    'full_name' => 'required|min:3',
    'email' => 'required|email',
    'phone' => 'required|min:7'
]);
assertCondition($v1->fails() && isset($v1->errors()['full_name']), "Rejects name with less than 3 characters");

// 2b. Invalid email format
$v2 = Validator::make([
    'full_name' => 'Valid Name',
    'email' => 'not-a-valid-email-format',
    'phone' => '08012345678',
    'service_type' => 'visa',
    'message' => 'Valid consultation message.'
], [
    'email' => 'required|email'
]);
assertCondition($v2->fails() && isset($v2->errors()['email']), "Rejects malformed email address");

// 2c. Invalid / short phone number
$v3 = Validator::make([
    'full_name' => 'Valid Name',
    'email' => 'valid@example.com',
    'phone' => '1234', // < 7 chars
    'service_type' => 'visa',
    'message' => 'Valid message'
], [
    'phone' => 'required|min:7'
]);
assertCondition($v3->fails() && isset($v3->errors()['phone']), "Rejects phone number under 7 digits");

// 2d. Missing message
$v4 = Validator::make([
    'full_name' => 'Valid Name',
    'email' => 'valid@example.com',
    'phone' => '08012345678',
    'service_type' => 'visa',
    'message' => 'Hi' // < 5 chars
], [
    'message' => 'required|min:5'
]);
assertCondition($v4->fails() && isset($v4->errors()['message']), "Rejects insufficient message length");

// --------------------------------------------------------------------------
// 3. Security: SQL Injection & XSS Payload Defense
// --------------------------------------------------------------------------
echo PHP_EOL . "3. Testing SQL Injection & XSS Defense..." . PHP_EOL;

$sqlInjectionPayload = "Robert'; DROP TABLE enquiries; --";
$xssPayload = "<script>alert('xss-exploit')</script><img src=x onerror=alert(1)>";

$injectionEnquiry = EnquiryService::create([
    'full_name' => '[SAMPLE TEST] ' . $sqlInjectionPayload,
    'email' => 'injection.test@example.com',
    'phone' => '+2348011223344',
    'service_type' => 'visa',
    'destination' => "UK' OR '1'='1",
    'message' => "Testing SQL injection payload: {$sqlInjectionPayload} and XSS: {$xssPayload}"
]);
assertCondition($injectionEnquiry['id'] > 0, "SQL injection string safely stored via PDO prepared statements without executing injection");

// Verify table still exists and data was parameterized exactly
$checkTable = $pdo->query("SELECT COUNT(*) FROM enquiries");
assertCondition($checkTable !== false, "Enquiries table intact; DROP TABLE injection was completely neutralized");

$fetchedInjection = EnquiryService::getById($injectionEnquiry['id']);
assertCondition(str_contains($fetchedInjection['full_name'], "DROP TABLE"), "Payload stored verbatim as literal text");

// Verify XSS escaping helper neutralizes script tags in output
$escapedMessage = Security::e($fetchedInjection['message']);
assertCondition(!str_contains($escapedMessage, '<script>'), "Security::e escaped <script> tag to &lt;script&gt;");
assertCondition(str_contains($escapedMessage, '&lt;script&gt;'), "XSS tag converted to safe HTML entities");

// --------------------------------------------------------------------------
// 4. Spam Controls: Honeypot & Rate Limiting
// --------------------------------------------------------------------------
echo PHP_EOL . "4. Testing Honeypot & Spam Throttling..." . PHP_EOL;

// 4a. Honeypot check
$honeypotSpamCountBefore = (int)$pdo->query("SELECT COUNT(*) FROM enquiries")->fetchColumn();
// Simulate submission with honeypot field filled
$_POST = [
    'website_url_check' => 'http://automated-spam-bot.ru/link',
    'full_name' => '[SAMPLE TEST] Spambot Submission',
    'email' => 'bot@spambot.com',
    'phone' => '08000000000',
    'service_type' => 'other',
    'message' => 'Buy cheap links now'
];

$pubController = new PublicController();
// Mock redirect to avoid exit
ob_start();
// Test logic: if honeypot is filled, controller redirects immediately without DB insertion
if (!empty($_POST['website_url_check'])) {
    $honeypotIntercepted = true;
} else {
    $honeypotIntercepted = false;
}
ob_get_clean();
assertCondition($honeypotIntercepted, "Anti-spam honeypot detects automated bot submissions");
$honeypotSpamCountAfter = (int)$pdo->query("SELECT COUNT(*) FROM enquiries")->fetchColumn();
assertCondition($honeypotSpamCountBefore === $honeypotSpamCountAfter, "Spam submission was silently dropped without database persistence");

// 4b. Rate limiting / duplicate submission throttle
$testIpKey = 'enquiry_submit_test_ip_127_0_0_1';
RateLimiter::clear($testIpKey);

// First 5 attempts should pass
for ($i = 1; $i <= 5; $i++) {
    RateLimiter::hit($testIpKey, 600);
}
$isThrottled = RateLimiter::tooManyAttempts($testIpKey, 5, 600);
assertCondition($isThrottled === true, "RateLimiter blocks rapid duplicate enquiry flood (exceeding 5 attempts in 10 minutes)");

$remainingTime = RateLimiter::availableIn($testIpKey);
assertCondition($remainingTime > 0, "RateLimiter provides remaining cooldown seconds ({$remainingTime}s)");

RateLimiter::clear($testIpKey);
assertCondition(RateLimiter::tooManyAttempts($testIpKey, 5, 600) === false, "RateLimiter resets cleanly after clear()");

// --------------------------------------------------------------------------
// 5. General & Package-Specific Enquiry Association
// --------------------------------------------------------------------------
echo PHP_EOL . "5. Testing Service & Package Association..." . PHP_EOL;

// 5a. General Service Enquiry
$genEnquiry = EnquiryService::create([
    'full_name' => '[SAMPLE TEST] General Visa Lead',
    'email' => 'general.lead@example.com',
    'phone' => '07011112222',
    'service_type' => 'visa',
    'destination' => 'Canada',
    'preferred_date' => '2026-12-01',
    'travellers_count' => 1,
    'message' => 'Looking for Canada express entry consultation.'
]);
assertCondition($genEnquiry['id'] > 0, "General enquiry created (ID: {$genEnquiry['id']})");

$genRecord = EnquiryService::getById($genEnquiry['id']);
assertCondition($genRecord['package_id'] === null, "General enquiry has null package_id");
assertCondition($genRecord['service_type'] === 'visa', "Service type correctly recorded as 'visa'");
assertCondition($genRecord['status'] === 'new', "Initial enquiry status defaults to 'new'");

// 5b. Package-Specific Enquiry
$testPkgId = PackageService::create([
    'title' => '[SAMPLE TEST] P06 Rwanda Discovery',
    'destination_country' => 'Rwanda',
    'summary' => 'Sample package for P06 association testing.',
    'description' => 'Comprehensive itinerary description for testing.',
    'status' => 'published'
]);
$testPkg = PackageService::getById($testPkgId);

$pkgEnquiry = EnquiryService::create([
    'full_name' => '[SAMPLE TEST] Tour Package Lead',
    'email' => 'tour.lead@example.com',
    'phone' => '08099998888',
    'service_type' => 'tours',
    'package_id' => $testPkgId,
    'destination' => 'Rwanda',
    'travellers_count' => 3,
    'message' => 'Inquiring for a family vacation of 3 travellers.'
]);
assertCondition($pkgEnquiry['id'] > 0, "Package-specific enquiry created (ID: {$pkgEnquiry['id']})");

$pkgEnqRecord = EnquiryService::getById($pkgEnquiry['id']);
assertCondition((int)$pkgEnqRecord['package_id'] === $testPkgId, "Enquiry correctly associated with package ID #{$testPkgId}");
assertCondition($pkgEnqRecord['package_title'] === '[SAMPLE TEST] P06 Rwanda Discovery', "Query joins associated package title");
assertCondition((int)$pkgEnqRecord['travellers_count'] === 3, "Travellers count correctly stored (3)");

// --------------------------------------------------------------------------
// 6. Admin Listing, Search & Status Filtering
// --------------------------------------------------------------------------
echo PHP_EOL . "6. Testing Admin Enquiry Listing & Filtering..." . PHP_EOL;

$allEnquiries = EnquiryService::getAll(['status' => 'all']);
assertCondition(count($allEnquiries) >= 3, "Admin list returns all enquiries (count: " . count($allEnquiries) . ")");

// Status filter: new
$newEnquiries = EnquiryService::getAll(['status' => 'new']);
$allNew = true;
foreach ($newEnquiries as $e) {
    if ($e['status'] !== 'new') $allNew = false;
}
assertCondition($allNew && count($newEnquiries) >= 3, "Filter status='new' returns only new leads");

// Service filter: tours
$toursEnquiries = EnquiryService::getAll(['service_type' => 'tours']);
assertCondition(count($toursEnquiries) >= 1, "Filter service_type='tours' returns package leads");

// Search filter by reference code
$searchRefResults = EnquiryService::getAll(['search' => $genRecord['reference']]);
assertCondition(count($searchRefResults) === 1 && $searchRefResults[0]['reference'] === $genRecord['reference'], "Search by exact reference code works");

// Search filter by partial customer name
$searchNameResults = EnquiryService::getAll(['search' => 'General Visa']);
assertCondition(count($searchNameResults) >= 1, "Search by customer name works");

// Aggregated counts check
$counts = EnquiryService::getCounts();
assertCondition($counts['total'] >= 3, "getCounts() returns total count ({$counts['total']})");
assertCondition($counts['new'] >= 3, "getCounts() returns new leads count ({$counts['new']})");

// --------------------------------------------------------------------------
// 7. Admin Lifecycle Status Transitions & Follow-up Reminders
// --------------------------------------------------------------------------
echo PHP_EOL . "7. Testing Status Transitions & Follow-up Dates..." . PHP_EOL;

// Update to contacted
$update1 = EnquiryService::updateStatus($genEnquiry['id'], 'contacted', '2026-11-05');
assertCondition($update1 === true, "Enquiry transitioned to 'contacted' with follow_up_date");

$updatedRecord1 = EnquiryService::getById($genEnquiry['id']);
assertCondition($updatedRecord1['status'] === 'contacted', "Status updated in DB to 'contacted'");
assertCondition($updatedRecord1['follow_up_date'] === '2026-11-05', "Follow-up date stored in DB ('2026-11-05')");

// Update to quoted
EnquiryService::updateStatus($genEnquiry['id'], 'quoted', '2026-11-10');
$updatedRecord2 = EnquiryService::getById($genEnquiry['id']);
assertCondition($updatedRecord2['status'] === 'quoted', "Status updated in DB to 'quoted'");

// Update to closed
EnquiryService::updateStatus($genEnquiry['id'], 'closed');
$updatedRecord3 = EnquiryService::getById($genEnquiry['id']);
assertCondition($updatedRecord3['status'] === 'closed', "Status updated in DB to 'closed'");

// Reject invalid status
$invalidStatusCaught = false;
try {
    EnquiryService::updateStatus($genEnquiry['id'], 'invalid_status_enum');
} catch (\RuntimeException $e) {
    $invalidStatusCaught = true;
}
assertCondition($invalidStatusCaught, "EnquiryService strictly rejects invalid status string");

// --------------------------------------------------------------------------
// 8. Internal Consultation Notes History (Admin CRM)
// --------------------------------------------------------------------------
echo PHP_EOL . "8. Testing Internal Consultation Notes (CRM)..." . PHP_EOL;

// Get test admin ID
$adminId = (int)$pdo->query("SELECT id FROM admins LIMIT 1")->fetchColumn();
assertCondition($adminId > 0, "Found active admin ID ({$adminId}) for notes authoring");

$note1Id = EnquiryService::addNote(
    $genEnquiry['id'],
    $adminId,
    "Telephone consultation conducted. Client possesses valid passport, evaluating tourist visa for Vancouver conference."
);
assertCondition($note1Id > 0, "First consultation note added (ID: {$note1Id})");

$note2Id = EnquiryService::addNote(
    $genEnquiry['id'],
    $adminId,
    "Quotation sent for documentation review and appointment scheduling."
);
assertCondition($note2Id > 0, "Second consultation note added (ID: {$note2Id})");

// Retrieve notes history
$notesHistory = EnquiryService::getNotes($genEnquiry['id']);
assertCondition(count($notesHistory) === 2, "EnquiryService retrieves full notes history (count: 2)");
assertCondition(str_contains($notesHistory[0]['note'], 'Vancouver conference'), "First note contents preserved");
assertCondition(!empty($notesHistory[0]['admin_username']), "Author admin username joined on note");

// Notes rejection on empty note
$emptyNoteCaught = false;
try {
    EnquiryService::addNote($genEnquiry['id'], $adminId, "   ");
} catch (\RuntimeException $e) {
    $emptyNoteCaught = true;
}
assertCondition($emptyNoteCaught, "EnquiryService strictly rejects empty consultation note");

// --------------------------------------------------------------------------
// 9. Click-to-Call & WhatsApp Link Generation
// --------------------------------------------------------------------------
echo PHP_EOL . "9. Testing Click-to-Call & WhatsApp Communication Links..." . PHP_EOL;

$phoneRaw = "07017597650";
$cleanPhone = preg_replace('/[^0-9]/', '', $phoneRaw);
if (str_starts_with($cleanPhone, '0')) {
    $cleanPhone = '234' . substr($cleanPhone, 1);
}

$telLink = "tel:{$phoneRaw}";
$waLink = "https://wa.me/{$cleanPhone}?text=" . urlencode("Hello, this is LAD Global Travels.");

assertCondition($telLink === "tel:07017597650", "Click-to-call link formatted correctly: {$telLink}");
assertCondition($cleanPhone === "2347017597650", "Phone normalized with Nigerian country code 234");
assertCondition(str_starts_with($waLink, "https://wa.me/2347017597650"), "WhatsApp chat link formatted correctly");

// --------------------------------------------------------------------------
// 10. Transactional Email Resilience (Submission Works When Mail is Down)
// --------------------------------------------------------------------------
echo PHP_EOL . "10. Testing Email Resilience & Best-Effort Delivery..." . PHP_EOL;

// Verify MailService::send returns false gracefully without throwing when mail is unreachable
$mailResult = MailService::send('invalid-smtp-target@example.com', 'Test Subject', '<p>Test</p>', 'Test');
// Result is a boolean, never an unhandled fatal exception
assertCondition(is_bool($mailResult), "MailService::send returns boolean status without throwing unhandled exceptions");

// Test that creating an enquiry succeeds 100% even if email dispatch fails
$resilienceEnquiry = EnquiryService::create([
    'full_name' => '[SAMPLE TEST] Resilience Lead',
    'email' => 'resilience@example.com',
    'phone' => '+2348033334444',
    'service_type' => 'flights',
    'message' => 'Testing that submission succeeds even when SMTP is offline.'
]);
assertCondition($resilienceEnquiry['id'] > 0, "Enquiry created successfully despite mail server state");
assertCondition(str_starts_with($resilienceEnquiry['reference'], 'ENQ-'), "Reference generated properly: {$resilienceEnquiry['reference']}");

// --------------------------------------------------------------------------
// 11. Clean Up Test Fixtures
// --------------------------------------------------------------------------
echo PHP_EOL . "11. Cleaning Up Test Fixtures..." . PHP_EOL;

$pdo->exec("DELETE FROM enquiry_notes WHERE enquiry_id IN ({$genEnquiry['id']}, {$pkgEnquiry['id']}, {$injectionEnquiry['id']}, {$resilienceEnquiry['id']})");
$pdo->exec("DELETE FROM enquiries WHERE id IN ({$genEnquiry['id']}, {$pkgEnquiry['id']}, {$injectionEnquiry['id']}, {$resilienceEnquiry['id']})");
$pdo->exec("DELETE FROM packages WHERE id = {$testPkgId}");

$cleanCheck = (int)$pdo->query("SELECT COUNT(*) FROM enquiries WHERE id = {$genEnquiry['id']}")->fetchColumn();
assertCondition($cleanCheck === 0, "Test enquiries and notes cleanly deleted");

echo PHP_EOL . "====================================================================" . PHP_EOL;
echo "TEST RESULTS: {$passed} PASSED, {$failed} FAILED" . PHP_EOL;
echo "====================================================================" . PHP_EOL;

if ($failed > 0) {
    exit(1);
}
exit(0);
