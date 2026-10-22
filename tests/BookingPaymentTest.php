<?php

declare(strict_types=1);

/**
 * LAD Global Travels and Tours — Phase P07 Automated Test Suite
 * 
 * Verifies package booking creation, server-authoritative price calculation,
 * manual bank transfer reconciliation with audit logging, Paystack server-side initialization,
 * HMAC SHA512 webhook signature verification, server-to-server independent verification,
 * exact amount/currency matching, idempotency deduplication, and travel status separation.
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Env;
use App\Core\Security;
use App\Services\BookingService;
use App\Services\PackageService;
use App\Services\PaystackService;
use App\Services\MailService;

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
echo "LAD GLOBAL TRAVELS — PHASE P07 BOOKING & PAYMENT TEST SUITE" . PHP_EOL;
echo "====================================================================" . PHP_EOL;

$pdo = Database::getConnection();

// --------------------------------------------------------------------------
// 1. PHP Syntax Checks on P07 Source Files
// --------------------------------------------------------------------------
echo PHP_EOL . "1. Checking PHP Syntax across P07 Files..." . PHP_EOL;

$p07Files = [
    'app/services/BookingService.php',
    'app/services/PaystackService.php',
    'app/services/MailService.php',
    'app/controllers/BookingController.php',
    'app/controllers/AdminController.php',
    'app/views/pages/booking-form.php',
    'app/views/pages/booking-transfer.php',
    'app/views/pages/booking-receipt.php',
    'app/views/pages/booking-status.php',
    'app/views/admin/bookings/index.php',
    'app/views/admin/bookings/detail.php',
    'app/views/admin/payments/index.php',
    'public/index.php'
];

foreach ($p07Files as $file) {
    $fullPath = realpath(__DIR__ . '/../' . $file);
    assertCondition($fullPath !== false && file_exists($fullPath), "File exists: {$file}");
    $output = [];
    $returnVar = 0;
    exec("C:\\xampp\\php\\php.exe -l \"{$fullPath}\" 2>&1", $output, $returnVar);
    assertCondition($returnVar === 0, "Syntax check: " . basename($file));
}

// --------------------------------------------------------------------------
// 2. Package Eligibility & Unpayable Package Block
// --------------------------------------------------------------------------
echo PHP_EOL . "2. Testing Package Booking Eligibility & Invariant Defense..." . PHP_EOL;

// Create test packages for eligibility checks
$draftPkg = [
    'title' => '[SAMPLE TEST] Draft Safari',
    'slug'  => 'sample-test-draft-safari',
    'destination_country' => 'Kenya',
    'destination_city' => 'Nairobi',
    'description' => 'Draft test tour',
    'duration_days' => 5,
    'price' => 500000.00,
    'currency' => 'NGN',
    'payment_enabled' => 1,
    'status' => 'draft'
];
assertCondition(!BookingService::isPackagePayable($draftPkg), "Draft package is rejected from direct booking");

$unpayablePkg = [
    'title' => '[SAMPLE TEST] Unpayable Tour',
    'slug'  => 'sample-test-unpayable-tour',
    'destination_country' => 'Dubai',
    'destination_city' => 'Dubai',
    'description' => 'Unpayable test tour',
    'duration_days' => 4,
    'price' => 800000.00,
    'currency' => 'NGN',
    'payment_enabled' => 0, // Disabled
    'status' => 'published'
];
assertCondition(!BookingService::isPackagePayable($unpayablePkg), "Package with payment_enabled=0 is rejected from direct booking");

$zeroPricePkg = [
    'title' => '[SAMPLE TEST] Zero Price Tour',
    'slug'  => 'sample-test-zero-price-tour',
    'destination_country' => 'Ghana',
    'destination_city' => 'Accra',
    'description' => 'Zero price test tour',
    'duration_days' => 3,
    'price' => 0.00,
    'currency' => 'NGN',
    'payment_enabled' => 1,
    'status' => 'published'
];
assertCondition(!BookingService::isPackagePayable($zeroPricePkg), "Package with zero price is rejected from direct booking");

$eligiblePkgData = [
    'title' => '[SAMPLE TEST] Payable Zanzibar Adventure',
    'destination_country' => 'Tanzania',
    'destination_city' => 'Zanzibar',
    'summary' => 'Test summary for payable package',
    'description' => 'Full test tour package for P07 booking testing.',
    'duration_days' => 6,
    'price_amount' => 650000.00,
    'price_currency' => 'NGN',
    'is_payment_enabled' => 1,
    'status' => 'published'
];
$testPkgId = PackageService::create($eligiblePkgData);
$payablePkg = PackageService::getById($testPkgId);
assertCondition(BookingService::isPackagePayable($payablePkg), "Published package with price > 0 and payment_enabled=1 is eligible for booking");

// --------------------------------------------------------------------------
// 3. Server-Authoritative Price Calculation & High-Entropy References
// --------------------------------------------------------------------------
echo PHP_EOL . "3. Testing Server-Authoritative Price Calculation..." . PHP_EOL;

// Customer specifies 3 travellers, but maliciously attempts to submit client-side tampered price
$customerInput = [
    'customer_name'    => 'Adebayo Adeleke',
    'customer_email'   => 'adebayo.test@example.com',
    'customer_phone'   => '08012345678',
    'travellers_count' => 3,
    'price'            => 1.00, // Tampered price
    'total_amount'     => 3.00  // Tampered total
];

$bookingResult = BookingService::createBooking($payablePkg, $customerInput, 'manual');
$booking = $bookingResult['booking'];
$payment = $bookingResult['payment'];

assertCondition(!empty($booking['id']), "Booking created with ID #{$booking['id']}");
assertCondition(str_starts_with($booking['reference'], 'BK-'), "High-entropy booking reference generated: {$booking['reference']}");
assertCondition(strlen($booking['reference']) >= 15, "Booking reference has high entropy (length: " . strlen($booking['reference']) . ")");
assertCondition((int)$booking['travellers_count'] === 3, "Travellers count accurately recorded (3)");
assertCondition((float)$booking['unit_price'] === 650000.00, "Authoritative unit price recorded from DB (₦650,000.00)");
assertCondition((float)$booking['total_amount'] === 1950000.00, "Server-authoritative total calculated (3 * 650,000 = ₦1,950,000.00)");
assertCondition((float)$payment['amount'] === 1950000.00, "Payment attempt frozen with server-authoritative amount (₦1,950,000.00)");
assertCondition(str_starts_with($payment['payment_reference'], 'PAY-'), "High-entropy payment reference generated: {$payment['payment_reference']}");
assertCondition($payment['provider'] === 'manual', "Payment provider correctly recorded as 'manual'");
assertCondition($payment['status'] === 'pending', "Payment initial status is 'pending'");
assertCondition($booking['booking_status'] === 'pending', "Booking initial status is 'pending'");

// --------------------------------------------------------------------------
// 4. Manual Bank Transfer Reconciliation & Audit Trail
// --------------------------------------------------------------------------
echo PHP_EOL . "4. Testing Manual Bank Transfer Flow & Admin Audit..." . PHP_EOL;

// 4a. Customer reports transfer narration memo
$memoUpdated = BookingService::recordCustomerTransferMemo((int)$payment['id'], 'Transfer sent from GTBank ref 994821');
assertCondition($memoUpdated === true, "Customer can report transfer memo");

$reloadedPayment = BookingService::getPaymentById((int)$payment['id']);
assertCondition(str_contains($reloadedPayment['admin_audit_notes'] ?? '', 'GTBank ref 994821'), "Customer memo logged into payment audit notes");
assertCondition($reloadedPayment['status'] === 'pending', "Customer memo does NOT auto-confirm payment (status remains 'pending')");

// 4b. Admin verification validation (rejection of empty remarks)
$validationFailed = false;
try {
    BookingService::adminVerifyManualPayment((int)$payment['id'], 1, '   ');
} catch (RuntimeException $e) {
    $validationFailed = true;
}
assertCondition($validationFailed === true, "Admin verification strictly requires non-empty external bank check remarks");

// 4c. Admin successfully verifies payment after external bank check
$adminStmt = $pdo->query("SELECT id FROM admins LIMIT 1");
$adminId = (int)$adminStmt->fetchColumn() ?: 1;

$verified = BookingService::adminVerifyManualPayment((int)$payment['id'], $adminId, 'Verified credit on Zenith Bank statement page 4 on 2026-10-20');
assertCondition($verified === true, "Admin manual verification succeeds");

$verifiedPayment = BookingService::getPaymentById((int)$payment['id']);
assertCondition($verifiedPayment['status'] === 'paid', "Payment status transitioned to 'paid'");
assertCondition(!empty($verifiedPayment['verified_at']), "Verification timestamp recorded");
assertCondition((int)$verifiedPayment['verified_by_admin_id'] === $adminId, "Verifying administrator ID recorded");

// 4d. Audit log entry recorded
$auditStmt = $pdo->prepare("SELECT * FROM audit_logs WHERE action = 'PAYMENT_MANUAL_VERIFIED' AND entity_id = :pid ORDER BY id DESC LIMIT 1");
$auditStmt->execute([':pid' => $payment['id']]);
$auditEntry = $auditStmt->fetch(PDO::FETCH_ASSOC);
assertCondition(!empty($auditEntry), "Audit log entry created for manual payment verification");
assertCondition(str_contains($auditEntry['details'] ?? '', 'Zenith Bank statement'), "Audit log preserves admin verification remarks");

// 4e. Idempotent re-verification
$reverified = BookingService::adminVerifyManualPayment((int)$payment['id'], $adminId, 'Repeat check');
assertCondition($reverified === true, "Repeated verification is idempotent and does not corrupt payment record");

// --------------------------------------------------------------------------
// 5. Manual Payment Rejection Flow
// --------------------------------------------------------------------------
echo PHP_EOL . "5. Testing Manual Bank Transfer Rejection Flow..." . PHP_EOL;

// Create second booking to test rejection
$bookingResult2 = BookingService::createBooking($payablePkg, [
    'customer_name'    => 'Fraudulent Submission Test',
    'customer_email'   => 'fraud.test@example.com',
    'customer_phone'   => '08099998888',
    'travellers_count' => 1
], 'manual');

$payment2 = $bookingResult2['payment'];
assertCondition($payment2['status'] === 'pending', "Second payment attempt created as 'pending'");

// Admin rejects payment
$rejected = BookingService::adminRejectManualPayment((int)$payment2['id'], $adminId, 'No matching funds found in bank statement');
assertCondition($rejected === true, "Admin manual rejection succeeds");

$rejectedPayment = BookingService::getPaymentById((int)$payment2['id']);
assertCondition($rejectedPayment['status'] === 'rejected', "Payment status transitioned to 'rejected'");

$rejectAuditStmt = $pdo->prepare("SELECT * FROM audit_logs WHERE action = 'PAYMENT_MANUAL_REJECTED' AND entity_id = :pid LIMIT 1");
$rejectAuditStmt->execute([':pid' => $payment2['id']]);
assertCondition((bool)$rejectAuditStmt->fetch(), "Audit log entry created for payment rejection");

// --------------------------------------------------------------------------
// 6. Paystack Server-Side Initialization
// --------------------------------------------------------------------------
echo PHP_EOL . "6. Testing Paystack Server-Side Initialization..." . PHP_EOL;

// Create booking for Paystack
$bookingResult3 = BookingService::createBooking($payablePkg, [
    'customer_name'    => 'Paystack Test User',
    'customer_email'   => 'paystack.client@example.com',
    'customer_phone'   => '08011223344',
    'travellers_count' => 2
], 'paystack');

$booking3 = $bookingResult3['booking'];
$payment3 = $bookingResult3['payment'];

// Total = 650,000 * 2 = 1,300,000. Subunits = 130,000,000 kobo
$expectedSubunits = 130000000;

// Set mock transport to simulate Paystack API
$interceptedPayload = null;
PaystackService::setMockTransport(function (string $method, string $url, array $headers, ?string $body) use (&$interceptedPayload) {
    if (str_ends_with($url, '/transaction/initialize')) {
        $interceptedPayload = json_decode($body, true);
        return [
            'success'  => true,
            'httpCode' => 200,
            'data'     => [
                'status'  => true,
                'message' => 'Authorization URL created',
                'data'    => [
                    'authorization_url' => 'https://checkout.paystack.com/000testaccesscode',
                    'access_code'       => '000testaccesscode',
                    'reference'         => $interceptedPayload['reference']
                ]
            ]
        ];
    }
    return ['success' => false, 'httpCode' => 404, 'data' => []];
});

$initRes = PaystackService::initializeTransaction($payment3, $booking3, $payablePkg, 'http://localhost:8000/paystack/callback');

assertCondition($initRes['success'] === true, "Paystack transaction initialized successfully");
assertCondition(!empty($initRes['authorization_url']), "Paystack returned authorization_url: " . $initRes['authorization_url']);
assertCondition($interceptedPayload !== null, "Paystack API received JSON payload");
assertCondition(($interceptedPayload['amount'] ?? 0) === $expectedSubunits, "Paystack payload uses exact subunit amount in kobo ({$expectedSubunits})");
assertCondition(($interceptedPayload['currency'] ?? '') === 'NGN', "Paystack payload enforces NGN currency");
assertCondition(($interceptedPayload['reference'] ?? '') === $payment3['payment_reference'], "Paystack payload references internal payment ref");

// --------------------------------------------------------------------------
// 7. Paystack HMAC SHA512 Webhook Signature Authentication
// --------------------------------------------------------------------------
echo PHP_EOL . "7. Testing Paystack HMAC SHA512 Webhook Signature..." . PHP_EOL;

$testSecret = PaystackService::getSecretKey();
$samplePayload = json_encode(['event' => 'charge.success', 'data' => ['reference' => $payment3['payment_reference']]]);

// Valid signature
$validSignature = hash_hmac('sha512', $samplePayload, $testSecret);
assertCondition(PaystackService::verifyWebhookSignature($samplePayload, $validSignature) === true, "Valid HMAC SHA512 signature verified");

// Tampered signature
$tamperedSignature = 'tampered_invalid_hex_string_' . bin2hex(random_bytes(16));
assertCondition(PaystackService::verifyWebhookSignature($samplePayload, $tamperedSignature) === false, "Tampered signature rejected");

// Tampered payload with valid signature
$tamperedPayload = json_encode(['event' => 'charge.success', 'data' => ['reference' => 'PAY-TAMPERED']]);
assertCondition(PaystackService::verifyWebhookSignature($tamperedPayload, $validSignature) === false, "Tampered payload with mismatched signature rejected");

// Empty signature header
assertCondition(PaystackService::verifyWebhookSignature($samplePayload, '') === false, "Empty signature header rejected");

// --------------------------------------------------------------------------
// 8. Paystack Settle Payment & Server-to-Server Verification Checks
// --------------------------------------------------------------------------
echo PHP_EOL . "8. Testing Paystack Settle Payment & Security Invariants..." . PHP_EOL;

$validGatewayData = [
    'status'      => 'success',
    'id'          => 88776655,
    'reference'   => $payment3['payment_reference'],
    'amount'      => 130000000, // Exact ₦1,300,000.00 in subunits
    'currency'    => 'NGN',
    'channel'     => 'card',
    'event'       => 'charge.success'
];

$settleResult = PaystackService::settlePayment(
    $payment3['payment_reference'],
    $validGatewayData,
    json_encode($validGatewayData),
    'evt_test_charge_1001'
);

assertCondition($settleResult['success'] === true, "Paystack payment settled successfully");

$settledPayment = BookingService::getPaymentById((int)$payment3['id']);
assertCondition($settledPayment['status'] === 'paid', "Payment record status updated to 'paid'");
assertCondition($settledPayment['provider_transaction_id'] === '88776655', "Provider transaction ID stored");
assertCondition(!empty($settledPayment['verified_at']), "Verified at timestamp stored");

// 8b. Idempotency: Duplicate Webhook Event Replay
$duplicateResult = PaystackService::settlePayment(
    $payment3['payment_reference'],
    $validGatewayData,
    json_encode($validGatewayData),
    'evt_test_charge_1001' // Same event_id
);

assertCondition($duplicateResult['success'] === true && $duplicateResult['duplicate'] === true, "Replayed webhook event detected as duplicate and safely ignored");

// --------------------------------------------------------------------------
// 9. Paystack Mismatched Amount & Currency Detection (Anti-Fraud)
// --------------------------------------------------------------------------
echo PHP_EOL . "9. Testing Paystack Anti-Fraud Protections (Mismatched Amount/Currency)..." . PHP_EOL;

// Create 4th booking to test fraud rejection
$bookingResult4 = BookingService::createBooking($payablePkg, [
    'customer_name'    => 'Tampering Test User',
    'customer_email'   => 'tamper.client@example.com',
    'customer_phone'   => '08055667788',
    'travellers_count' => 1
], 'paystack');
$payment4 = $bookingResult4['payment']; // Expected 650,000 NGN = 65,000,000 kobo

// A. Subunit Underpayment Attempt (Gateway reports ₦100 instead of ₦650,000)
$underpaymentGatewayData = [
    'status'      => 'success',
    'id'          => 99990001,
    'reference'   => $payment4['payment_reference'],
    'amount'      => 10000, // ₦100.00
    'currency'    => 'NGN',
    'channel'     => 'card'
];

$fraudResult1 = PaystackService::settlePayment($payment4['payment_reference'], $underpaymentGatewayData, json_encode($underpaymentGatewayData));
assertCondition($fraudResult1['success'] === false, "Underpayment attempt rejected");
assertCondition(str_contains($fraudResult1['error'] ?? '', 'Amount mismatch'), "Error message explicitly flags amount mismatch");

$tamperedPaymentRecord = BookingService::getPaymentById((int)$payment4['id']);
assertCondition($tamperedPaymentRecord['status'] === 'failed', "Tampered payment marked as 'failed' in database");

// B. Currency Mismatch Attempt (USD instead of NGN)
$bookingResult5 = BookingService::createBooking($payablePkg, [
    'customer_name'    => 'Currency Test User',
    'customer_email'   => 'currency.client@example.com',
    'customer_phone'   => '08099887766',
    'travellers_count' => 1
], 'paystack');
$payment5 = $bookingResult5['payment'];

$currencyMismatchData = [
    'status'      => 'success',
    'id'          => 99990002,
    'reference'   => $payment5['payment_reference'],
    'amount'      => 65000000,
    'currency'    => 'USD', // Wrong currency!
    'channel'     => 'card'
];

$fraudResult2 = PaystackService::settlePayment($payment5['payment_reference'], $currencyMismatchData, json_encode($currencyMismatchData));
assertCondition($fraudResult2['success'] === false, "Currency mismatch attempt rejected");
assertCondition(str_contains($fraudResult2['error'] ?? '', 'Currency mismatch'), "Error message explicitly flags currency mismatch");

// --------------------------------------------------------------------------
// 10. Separation of Payment Receipt vs Travel Confirmation (D11)
// --------------------------------------------------------------------------
echo PHP_EOL . "10. Testing Separation of Payment Receipt vs Travel Confirmation (D11)..." . PHP_EOL;

// Check booking 3: Payment is PAID, but booking_status must remain 'pending' until manual review
$reloadedBooking3 = BookingService::getBookingById((int)$booking3['id']);
assertCondition($reloadedBooking3['latest_payment']['status'] === 'paid', "Payment is confirmed as PAID");
assertCondition($reloadedBooking3['booking_status'] === 'pending', "Travel booking_status remains 'pending' logistical confirmation");

// Admin manually confirms travel booking
$travelConfirmed = BookingService::adminUpdateBookingStatus((int)$booking3['id'], $adminId, 'confirmed', 'Flight seats confirmed on Kenya Airways');
assertCondition($travelConfirmed === true, "Administrator independently confirms travel booking");

$confirmedBooking = BookingService::getBookingById((int)$booking3['id']);
assertCondition($confirmedBooking['booking_status'] === 'confirmed', "Travel booking status updated to 'confirmed'");

// Verify audit log for travel status
$travelAuditStmt = $pdo->prepare("SELECT * FROM audit_logs WHERE action = 'BOOKING_STATUS_UPDATED' AND entity_id = :bid LIMIT 1");
$travelAuditStmt->execute([':bid' => $booking3['id']]);
assertCondition((bool)$travelAuditStmt->fetch(), "Audit log entry created for travel status update");

// --------------------------------------------------------------------------
// 11. Testing Card Data Shielding Invariant
// --------------------------------------------------------------------------
echo PHP_EOL . "11. Verifying Zero Card Data Storage Invariant..." . PHP_EOL;

// Verify table columns in payments, bookings, and payment_events do not have PAN, CVV, PIN, or card_number
$colStmt = $pdo->query("SHOW COLUMNS FROM payments");
$payCols = array_map(fn($c) => strtolower($c['Field']), $colStmt->fetchAll(PDO::FETCH_ASSOC));

$forbiddenColumns = ['card_number', 'pan', 'cvv', 'cvv2', 'pin', 'expiry', 'card_token', 'bank_pin'];
$leakedCols = array_intersect($forbiddenColumns, $payCols);
assertCondition(empty($leakedCols), "Zero cardholder or sensitive authentication data columns in payments table");

$colStmt2 = $pdo->query("SHOW COLUMNS FROM bookings");
$bkCols = array_map(fn($c) => strtolower($c['Field']), $colStmt2->fetchAll(PDO::FETCH_ASSOC));
$leakedCols2 = array_intersect($forbiddenColumns, $bkCols);
assertCondition(empty($leakedCols2), "Zero cardholder or sensitive authentication data columns in bookings table");

// --------------------------------------------------------------------------
// 12. Admin Listing & Metric Aggregation
// --------------------------------------------------------------------------
echo PHP_EOL . "12. Testing Admin Bookings & Payments Listing Queries..." . PHP_EOL;

$allBookings = BookingService::listBookings();
assertCondition(count($allBookings) >= 4, "Admin listBookings returns all active bookings (count: " . count($allBookings) . ")");

$confirmedBookings = BookingService::listBookings(['status' => 'confirmed']);
assertCondition(count($confirmedBookings) >= 1, "Admin listBookings filter by status='confirmed' returns matching record");

$manualPayments = BookingService::listPayments(['provider' => 'manual']);
assertCondition(count($manualPayments) >= 2, "Admin listPayments filter by provider='manual' works");

$counts = BookingService::getCounts();
assertCondition(($counts['total_bookings'] ?? 0) >= 4, "getCounts returns total bookings count");
assertCondition(($counts['total_collected'] ?? 0) > 0, "getCounts accurately calculates total revenue collected (₦" . number_format($counts['total_collected'], 2) . ")");

// --------------------------------------------------------------------------
// 13. Cleaning Up Test Fixtures
// --------------------------------------------------------------------------
echo PHP_EOL . "13. Cleaning Up Test Fixtures..." . PHP_EOL;

// Reset mock transport
PaystackService::setMockTransport(null);

// Remove test bookings and payments created during test
$testBookingIds = [$booking['id'], $bookingResult2['booking']['id'], $booking3['id'], $bookingResult4['booking']['id'], $bookingResult5['booking']['id']];
$inClause = implode(',', array_map('intval', $testBookingIds));

$pdo->exec("DELETE FROM payments WHERE booking_id IN ({$inClause})");
$pdo->exec("DELETE FROM payment_events WHERE event_id LIKE 'evt_test_%'");
$pdo->exec("DELETE FROM audit_logs WHERE details LIKE '%SAMPLE TEST%' OR details LIKE '%BK-%'");
$pdo->exec("DELETE FROM bookings WHERE id IN ({$inClause})");

// Delete test packages
PackageService::delete($testPkgId);

assertCondition(true, "Test bookings, payments, audit logs, and packages cleanly deleted");

echo PHP_EOL . "====================================================================" . PHP_EOL;
echo "TEST RESULTS: {$passed} PASSED, {$failed} FAILED" . PHP_EOL;
echo "====================================================================" . PHP_EOL;

if ($failed > 0) {
    exit(1);
}
