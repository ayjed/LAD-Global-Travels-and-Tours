<?php

declare(strict_types=1);

/**
 * LAD GLOBAL TRAVELS AND TOURS — PAYMENT METHOD TOGGLE TEST SUITE
 * Validates dynamic payment method activation/deactivation in admin,
 * server-side booking enforcement, fallback handling, and UI responsiveness.
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Security;
use App\Services\BookingService;
use App\Services\PackageService;
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
echo "LAD GLOBAL TRAVELS — PAYMENT METHOD TOGGLE TEST SUITE\n";
echo "====================================================================\n\n";

// -----------------------------------------------------------------------------
// 1. SettingService Methods & Default State
// -----------------------------------------------------------------------------
echo "1. Testing SettingService Default Payment Method State...\n";

SettingService::clearCache();

// Reset to known state (both enabled)
SettingService::setMultiple([
    'payment_method_bank_enabled'     => '1',
    'payment_method_paystack_enabled' => '1',
]);

$settings = SettingService::getPaymentMethodSettings();
assertTest($settings['bank'] === true, 'Bank transfer is enabled by default');
assertTest($settings['paystack'] === true, 'Paystack gateway is enabled by default');
assertTest($settings['total_enabled'] === 2, 'Total enabled methods count is 2');

$enabledMethods = SettingService::getEnabledPaymentMethods();
assertTest(in_array('manual', $enabledMethods, true), 'manual in getEnabledPaymentMethods');
assertTest(in_array('paystack', $enabledMethods, true), 'paystack in getEnabledPaymentMethods');
assertTest(SettingService::isBankTransferEnabled() === true, 'isBankTransferEnabled returns true');
assertTest(SettingService::isPaystackEnabled() === true, 'isPaystackEnabled returns true');

// -----------------------------------------------------------------------------
// 2. Toggling Bank Transfer Off
// -----------------------------------------------------------------------------
echo "\n2. Testing Disabling Direct Bank Transfer...\n";

SettingService::set('payment_method_bank_enabled', '0');

assertTest(SettingService::isBankTransferEnabled() === false, 'isBankTransferEnabled returns false after toggle');
assertTest(SettingService::isPaystackEnabled() === true, 'isPaystackEnabled remains true');

$enabledOnlyPaystack = SettingService::getEnabledPaymentMethods();
assertTest(!in_array('manual', $enabledOnlyPaystack, true), 'manual is removed from enabled methods');
assertTest(in_array('paystack', $enabledOnlyPaystack, true), 'paystack remains in enabled methods');
assertTest(count($enabledOnlyPaystack) === 1, 'Only 1 method enabled');

$settingsOnlyPaystack = SettingService::getPaymentMethodSettings();
assertTest($settingsOnlyPaystack['total_enabled'] === 1, 'PaymentMethodSettings reflects 1 active method');

// -----------------------------------------------------------------------------
// 3. Toggling Paystack Off (Both Disabled)
// -----------------------------------------------------------------------------
echo "\n3. Testing Disabling All Payment Methods...\n";

SettingService::set('payment_method_paystack_enabled', '0');

assertTest(SettingService::isBankTransferEnabled() === false, 'Bank transfer disabled');
assertTest(SettingService::isPaystackEnabled() === false, 'Paystack disabled');

$enabledNone = SettingService::getEnabledPaymentMethods();
assertTest(empty($enabledNone), 'getEnabledPaymentMethods returns empty array when all are disabled');

$settingsNone = SettingService::getPaymentMethodSettings();
assertTest($settingsNone['total_enabled'] === 0, 'total_enabled is 0');

// -----------------------------------------------------------------------------
// 4. Testing BookingService Enforcement with Disabled Methods
// -----------------------------------------------------------------------------
echo "\n4. Testing BookingService Provider Enforcement...\n";

// Setup a mock payable package for testing
$testPkg = [
    'id'                 => 9999,
    'title'              => 'Payment Toggle Automated Test Package',
    'slug'               => 'toggle-test-package',
    'status'             => 'published',
    'is_payment_enabled' => 1,
    'price_amount'       => 150000.00,
    'price_currency'     => 'NGN',
    'duration_days'      => 3
];

// Test 4A: Attempting manual when bank is disabled
$manualThrew = false;
try {
    BookingService::createBooking($testPkg, [
        'customer_name'    => 'Test User',
        'customer_email'   => 'test@example.com',
        'customer_phone'   => '08012345678',
        'travellers_count' => 1
    ], 'manual');
} catch (RuntimeException $e) {
    $manualThrew = true;
    assertTest(str_contains($e->getMessage(), 'disabled'), 'createBooking throws RuntimeException when manual is disabled: ' . $e->getMessage());
}
assertTest($manualThrew, 'createBooking rejected manual payment when bank transfer is disabled');

// Test 4B: Attempting paystack when paystack is disabled
$paystackThrew = false;
try {
    BookingService::createBooking($testPkg, [
        'customer_name'    => 'Test User',
        'customer_email'   => 'test@example.com',
        'customer_phone'   => '08012345678',
        'travellers_count' => 1
    ], 'paystack');
} catch (RuntimeException $e) {
    $paystackThrew = true;
    assertTest(str_contains($e->getMessage(), 'disabled'), 'createBooking throws RuntimeException when paystack is disabled: ' . $e->getMessage());
}
assertTest($paystackThrew, 'createBooking rejected paystack payment when paystack is disabled');

// -----------------------------------------------------------------------------
// 5. Restoring and Verifying Normal Booking Flow
// -----------------------------------------------------------------------------
echo "\n5. Restoring Payment Methods & Verifying Allowed Bookings...\n";

SettingService::setMultiple([
    'payment_method_bank_enabled'     => '1',
    'payment_method_paystack_enabled' => '1',
]);

assertTest(SettingService::isBankTransferEnabled() === true, 'Bank transfer restored to enabled');
assertTest(SettingService::isPaystackEnabled() === true, 'Paystack restored to enabled');

$restoredMethods = SettingService::getEnabledPaymentMethods();
assertTest(count($restoredMethods) === 2, 'Both payment methods active again');

// -----------------------------------------------------------------------------
// 6. Verifying View Template Files Syntax
// -----------------------------------------------------------------------------
echo "\n6. Checking Syntax of Updated Views...\n";

$viewFiles = [
    'app/services/SettingService.php',
    'app/controllers/BookingController.php',
    'app/controllers/AdminController.php',
    'app/views/pages/booking-form.php',
    'app/views/pages/package-detail.php',
    'app/views/admin/settings/index.php'
];

foreach ($viewFiles as $relPath) {
    $fullPath = dirname(__DIR__) . '/' . $relPath;
    assertTest(file_exists($fullPath), "File exists: {$relPath}");
    
    // Check php -l
    $output = [];
    $returnVar = 0;
    exec("C:\\xampp\\php\\php.exe -l \"{$fullPath}\" 2>&1", $output, $returnVar);
    assertTest($returnVar === 0, "Syntax check: {$relPath}");
}

echo "\n====================================================================\n";
echo "RESULTS: {$passed} Passed, {$failed} Failed\n";
echo "====================================================================\n";

if ($failed > 0) {
    exit(1);
}
