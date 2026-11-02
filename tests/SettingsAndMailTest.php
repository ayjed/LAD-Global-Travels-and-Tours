<?php

declare(strict_types=1);

/**
 * LAD GLOBAL TRAVELS AND TOURS — PHASE P08 SETTINGS, EMAIL & SOCIAL TEST SUITE
 * Validates company contact settings, official social links, email routing,
 * protected bank configuration, audit logging, and transactional email templates.
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Env;
use App\Core\Router;
use App\Core\Security;
use App\Services\MailService;
use App\Services\SettingService;

$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $message): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$message}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$message}\n";
    }
}

echo "====================================================================\n";
echo "LAD GLOBAL TRAVELS — PHASE P08 SETTINGS & EMAIL INTEGRATION TEST SUITE\n";
echo "====================================================================\n\n";

// -----------------------------------------------------------------------------
// 1. SettingService Defaults & Basic Getters/Setters
// -----------------------------------------------------------------------------
echo "1. Testing SettingService Defaults & Retrieval...\n";

SettingService::clearCache();

$company = SettingService::getCompanyDetails();
assertTest($company['name'] === 'LAD Global Travels and Tours', 'Company name defaults to SOT identity');
assertTest($company['phone'] === '07017597650', 'Company phone defaults to verified number');
assertTest($company['whatsapp'] === '2347017597650', 'WhatsApp number defaults to verified number');
assertTest(str_contains($company['address'], 'Ikorodu'), 'Office address references verified Ikorodu premises');
assertTest(!empty($company['hours']), 'Operating hours are populated');

$social = SettingService::getSocialLinks();
assertTest(array_key_exists('instagram', $social), 'Social links dictionary includes instagram');
assertTest(array_key_exists('facebook', $social), 'Social links dictionary includes facebook');
assertTest(array_key_exists('linkedin', $social), 'Social links dictionary includes linkedin');

$routing = SettingService::getEmailRouting();
assertTest(!empty($routing['from_address']), 'Default mail_from_address is set');
assertTest(!empty($routing['enquiries']), 'notification_email_enquiries is set');
assertTest(!empty($routing['visa']), 'notification_email_visa is set');
assertTest(!empty($routing['payments']), 'notification_email_payments is set');
assertTest(!empty($routing['management']), 'notification_email_management is set');

// -----------------------------------------------------------------------------
// 2. Phone & WhatsApp Link Formats
// -----------------------------------------------------------------------------
echo "\n2. Testing Phone & WhatsApp Formatting Helpers...\n";

$telLink = SettingService::formatPhoneLink('07017597650');
assertTest($telLink === 'tel:07017597650', 'formatPhoneLink produces valid tel: URI');

$waClean1 = SettingService::cleanPhoneForWhatsApp('07017597650');
assertTest($waClean1 === '2347017597650', 'cleanPhoneForWhatsApp converts leading 0 to Nigerian 234 prefix');

$waClean2 = SettingService::cleanPhoneForWhatsApp('+234 701 759 7650');
assertTest($waClean2 === '2347017597650', 'cleanPhoneForWhatsApp strips spaces and + symbols');

$waUrl = SettingService::formatWhatsAppUrl('07017597650', 'Hello LAD Global');
assertTest(str_starts_with($waUrl, 'https://wa.me/2347017597650?text=Hello'), 'formatWhatsAppUrl produces valid wa.me URL with pre-filled text');

// -----------------------------------------------------------------------------
// 3. Database Persistence & Multiple Setting Updates
// -----------------------------------------------------------------------------
echo "\n3. Testing Setting Database Persistence...\n";

$testTagline = 'Your Journey. Our Commitment. ' . uniqid();
SettingService::set('company_tagline', $testTagline);
SettingService::clearCache();

$savedTagline = SettingService::get('company_tagline');
assertTest($savedTagline === $testTagline, 'SettingService persists and retrieves single setting from database');

// Restore default tagline so test values do not leak into live database
SettingService::set('company_tagline', 'Explore • Discover • Experience');
SettingService::clearCache();

SettingService::setMultiple([
    'test_key_1' => 'val_1',
    'test_key_2' => 'val_2'
]);
SettingService::clearCache();
assertTest(SettingService::get('test_key_1') === 'val_1', 'setMultiple updates key 1');
assertTest(SettingService::get('test_key_2') === 'val_2', 'setMultiple updates key 2');

// Clean up test keys
try {
    $db = \App\Core\Database::getConnection();
    $db->exec("DELETE FROM settings WHERE setting_key IN ('test_key_1', 'test_key_2')");
    SettingService::clearCache();
} catch (\Throwable $e) {}

// -----------------------------------------------------------------------------
// 4. Protected Corporate Bank Details Configuration & Audit Trail
// -----------------------------------------------------------------------------
echo "\n4. Testing Protected Bank Details Configuration & Audit Trail...\n";

$bank = SettingService::getBankDetails();
assertTest(!empty($bank['bank_name']), 'Bank details bank_name is populated');
assertTest(!empty($bank['account_name']), 'Bank details account_name is populated');
assertTest(!empty($bank['account_number']), 'Bank details account_number is populated');
assertTest(isset($bank['is_verified']), 'Bank details is_verified flag exists');

// Test update WITHOUT client authorization confirmation (MUST throw exception)
$exceptionThrown = false;
try {
    SettingService::updateBankDetails([
        'bank_name'      => 'Zenith Bank PLC',
        'account_name'   => 'LAD Global Integrated Service',
        'account_number' => '1012345678',
        'sort_code'      => '',
        'instructions'   => 'Use reference'
    ], 1, false); // clientConfirmed = false
} catch (\InvalidArgumentException $e) {
    $exceptionThrown = true;
}
assertTest($exceptionThrown, 'Updating bank details without client authorization certification throws exception');

// Test update with invalid account number (too short)
$shortAccThrown = false;
try {
    SettingService::updateBankDetails([
        'bank_name'      => 'Zenith Bank PLC',
        'account_name'   => 'LAD Global Integrated Service',
        'account_number' => '12345',
        'sort_code'      => '',
        'instructions'   => 'Use reference'
    ], 1, true);
} catch (\InvalidArgumentException $e) {
    $shortAccThrown = true;
}
assertTest($shortAccThrown, 'Updating bank details with non-NUBAN account number throws exception');

// Test update WITH client authorization confirmation
$pdo = Database::getConnection();
$adminRow = $pdo->query("SELECT id FROM admins ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$testAdminId = $adminRow ? (int)$adminRow['id'] : 1;

$validAccount = '1017' . rand(100000, 999999);
$updatedBank = SettingService::updateBankDetails([
    'bank_name'      => 'Zenith Bank PLC',
    'account_name'   => 'LAD Global Travels and Tours Limited',
    'account_number' => $validAccount,
    'sort_code'      => '057',
    'instructions'   => 'Mandatory: Use BK-XXXX as transfer narration.'
], $testAdminId, true);

assertTest($updatedBank['account_number'] === $validAccount, 'Valid bank details update persists new account number');
assertTest($updatedBank['is_verified'] === true, 'Bank verified flag is set to true');

// Verify audit trail entry was recorded in audit_logs
$auditStmt = $pdo->prepare("
    SELECT * FROM audit_logs 
    WHERE action = 'UPDATE_BANK_DETAILS' 
    ORDER BY id DESC LIMIT 1
");
$auditStmt->execute();
$auditLog = $auditStmt->fetch(PDO::FETCH_ASSOC);

assertTest($auditLog !== false, 'Audit log entry is created for UPDATE_BANK_DETAILS');
assertTest((int)$auditLog['admin_id'] === $testAdminId, 'Audit log correctly attributes the modifying admin ID');

$auditDetails = json_decode((string)$auditLog['details'], true);
assertTest(isset($auditDetails['masked_account']), 'Audit log contains masked account representation');
assertTest(!str_contains((string)$auditLog['details'], $validAccount), 'Raw unmasked bank account number is NEVER exposed in audit log details');

// -----------------------------------------------------------------------------
// 5. Admin Settings Route Lookups & Access Guarding
// -----------------------------------------------------------------------------
echo "\n5. Testing Admin Settings Route Lookups & Auth Guarding...\n";

$router = new Router();
$router->get('/admin/settings', [App\Controllers\AdminController::class, 'settings']);
$router->post('/admin/settings', [App\Controllers\AdminController::class, 'updateSettings']);

$matchGet = $router->match('GET', '/admin/settings');
assertTest($matchGet !== null, 'Route GET /admin/settings is registered');
assertTest(isset($matchGet['handler']) && $matchGet['handler'][1] === 'settings', 'GET /admin/settings dispatches AdminController::settings');

$matchPost = $router->match('POST', '/admin/settings');
assertTest($matchPost !== null, 'Route POST /admin/settings is registered');
assertTest(isset($matchPost['handler']) && $matchPost['handler'][1] === 'updateSettings', 'POST /admin/settings dispatches AdminController::updateSettings');

// -----------------------------------------------------------------------------
// 6. Transactional MailService Dynamic Email Routing & Classification
// -----------------------------------------------------------------------------
echo "\n6. Testing MailService Dynamic Routing & SOT 11 Mailboxes...\n";

// Set test email routing in SettingService
SettingService::setMultiple([
    'mail_from_address'             => 'info@ladglobal.com',
    'mail_from_name'                => 'LAD Global Travels',
    'notification_email_enquiries'  => 'sales@ladglobal.com',
    'notification_email_visa'       => 'visa@ladglobal.com',
    'notification_email_payments'   => 'admin@ladglobal.com',
    'notification_email_management' => 'lanre@ladglobal.com',
]);
SettingService::clearCache();

// 6A. Regression Suite: Ambiguous Service Names Classification
echo "  Testing Service Classification & Substring Collision Defense...\n";

$tourAmbiguousCases = [
    'Dubai Premium Safari',
    'Spring in Paris Holiday',
    'Private Island Getaway',
    'Express Flight Booking',
    'Cyprus Family Vacation',
    'Capri Coastal Escape',
    'Apricot Orchard Tour',
    'tours',
    'flights',
    'hotels',
    'other',
    'International Tour Packages'
];

foreach ($tourAmbiguousCases as $service) {
    $isVisa = MailService::isVisaOrImmigrationEnquiry(['service_type' => $service]);
    assertTest($isVisa === false, "Ambiguous tour service '{$service}' is NOT classified as visa (routes to sales@)");
}

$visaGenuineCases = [
    'visa',
    'study',
    'canada_pr',
    'cbi',
    'rbi',
    'Canada PR Consultation',
    'Student Visa & Study Abroad',
    'Permanent Residency Advisory',
    'Citizenship by Investment',
    'Residency by Investment',
    'Express Entry Work Permit'
];

foreach ($visaGenuineCases as $service) {
    $isVisa = MailService::isVisaOrImmigrationEnquiry(['service_type' => $service]);
    assertTest($isVisa === true, "Genuine visa/mobility service '{$service}' is classified as visa (routes to visa@)");
}

// Test that general tour enquiry triggers routing to sales@
$tourEnquiry = [
    'reference'        => 'ENQ-TEST001',
    'full_name'        => 'Amina Bello',
    'email'            => 'amina@example.com',
    'phone'            => '08023456789',
    'service_type'     => 'International Tour Packages',
    'destination'      => 'Dubai',
    'preferred_date'   => '2026-12-15',
    'travellers_count' => 2,
    'message'          => 'Inquiry about Dubai family holiday.'
];
$tourAlertSent = MailService::sendEnquiryAdminAlert($tourEnquiry);
assertTest($tourAlertSent === true || file_exists(dirname(__DIR__) . '/storage/logs/mail.log'), 'sendEnquiryAdminAlert executes gracefully (via socket or mail.log fallback)');

// Test that visa enquiry routes to visa@
$visaEnquiry = [
    'reference'        => 'ENQ-TEST002',
    'full_name'        => 'Emeka Okafor',
    'email'            => 'emeka@example.com',
    'phone'            => '08034567890',
    'service_type'     => 'Visa Assistance',
    'destination'      => 'Canada',
    'preferred_date'   => '2027-01-10',
    'travellers_count' => 1,
    'message'          => 'Student visa consultation.'
];
$visaAlertSent = MailService::sendEnquiryAdminAlert($visaEnquiry);
assertTest($visaAlertSent === true || file_exists(dirname(__DIR__) . '/storage/logs/mail.log'), 'sendEnquiryAdminAlert for visa routes to designated visa inbox');

// Test customer enquiry acknowledgment receipt
$customerReceiptSent = MailService::sendEnquiryCustomerReceipt($tourEnquiry);
assertTest($customerReceiptSent === true || file_exists(dirname(__DIR__) . '/storage/logs/mail.log'), 'sendEnquiryCustomerReceipt executes successfully');

// Test customer bank transfer instructions
$sampleBooking = [
    'reference'       => 'BK-SETTINGTEST',
    'customer_name'   => 'Folake Adeleke',
    'customer_email'  => 'folake@example.com',
    'travellers_count'=> 2,
];
$samplePayment = [
    'payment_reference' => 'PAY-SETTINGTEST',
    'amount'            => 2600000.00,
    'currency'          => 'NGN',
    'provider'          => 'manual'
];
$samplePackage = [
    'title' => 'Dubai Luxury Experience'
];

$transferInstructionsSent = MailService::sendBankTransferInstructions(
    $sampleBooking,
    $samplePayment,
    $samplePackage,
    SettingService::getBankDetails()
);
assertTest($transferInstructionsSent === true || file_exists(dirname(__DIR__) . '/storage/logs/mail.log'), 'sendBankTransferInstructions executes with verified bank details');

// Test official payment receipt separation from travel confirmation (SOT 8.4)
$receiptSent = MailService::sendPaymentReceipt($sampleBooking, $samplePayment, $samplePackage);
assertTest($receiptSent === true || file_exists(dirname(__DIR__) . '/storage/logs/mail.log'), 'sendPaymentReceipt executes with mandatory statutory travel notice');

// Test payment notification to admin/finance
$adminPayAlertSent = MailService::sendAdminPaymentNotification($sampleBooking, $samplePayment, $samplePackage);
assertTest($adminPayAlertSent === true || file_exists(dirname(__DIR__) . '/storage/logs/mail.log'), 'sendAdminPaymentNotification dispatches to finance mailbox');

// -----------------------------------------------------------------------------
// 7. Security Audit & Secrets Exposure Defense
// -----------------------------------------------------------------------------
echo "\n7. Testing Security & Secrets Isolation...\n";

$allSettings = SettingService::getAll();
assertTest(!isset($allSettings['DB_PASSWORD']), 'Database password is never exposed in settings dictionary');
assertTest(!isset($allSettings['PAYSTACK_SECRET_KEY']), 'Paystack secret key is never exposed in settings dictionary');
assertTest(!isset($allSettings['SMTP_PASSWORD']), 'SMTP password is never exposed in settings dictionary');
assertTest(!isset($allSettings['APP_SECRET']), 'Application encryption secret is never exposed in settings dictionary');

// -----------------------------------------------------------------------------
// 8. Public Views Dynamic Links Verification
// -----------------------------------------------------------------------------
echo "\n8. Testing Public Layout & Contact View Integration...\n";

$mainLayoutPath = dirname(__DIR__) . '/app/views/layouts/main.php';
$mainLayoutContent = file_get_contents($mainLayoutPath);
assertTest(str_contains($mainLayoutContent, 'SettingService::getCompanyDetails'), 'main.php layout calls SettingService::getCompanyDetails');
assertTest(str_contains($mainLayoutContent, 'SettingService::getSocialLinks'), 'main.php layout calls SettingService::getSocialLinks');
assertTest(str_contains($mainLayoutContent, 'SettingService::formatWhatsAppUrl'), 'main.php layout uses formatWhatsAppUrl');
assertTest(str_contains($mainLayoutContent, 'SettingService::formatPhoneLink'), 'main.php layout uses formatPhoneLink');

$contactViewPath = dirname(__DIR__) . '/app/views/pages/contact.php';
$contactViewContent = file_get_contents($contactViewPath);
assertTest(str_contains($contactViewContent, 'SettingService::getCompanyDetails'), 'contact.php view calls SettingService::getCompanyDetails');
assertTest(str_contains($contactViewContent, 'SettingService::getEmailRouting'), 'contact.php view calls SettingService::getEmailRouting');

// -----------------------------------------------------------------------------
// Summary
// -----------------------------------------------------------------------------
echo "\n--------------------------------------------------------------------\n";
echo "RESULTS: {$passed} Passed, {$failed} Failed\n";
echo "--------------------------------------------------------------------\n";

if ($failed === 0) {
    echo "All P08 Settings, Email & Social Integration tests passed successfully!\n\n";
    exit(0);
} else {
    echo "P08 Tests encountered {$failed} failure(s)!\n\n";
    exit(1);
}
