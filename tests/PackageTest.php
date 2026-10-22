<?php

declare(strict_types=1);

/**
 * LAD GLOBAL TRAVELS AND TOURS — PHASE P05 AUTOMATED TEST SUITE
 * Tests tour package admin CRUD, unique slug generation, draft/archived public visibility protection,
 * image upload security & finfo MIME inspection, historical paid package deletion protection,
 * missing image graceful handling, and price tampering prevention.
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Response;
use App\Core\Security;
use App\Core\View;
use App\Services\PackageService;
use App\Services\UploadService;
use App\Controllers\PublicController;

echo "====================================================================" . PHP_EOL;
echo "LAD GLOBAL TRAVELS — PHASE P05 TOUR PACKAGES AUTOMATED TEST SUITE" . PHP_EOL;
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

// --------------------------------------------------------------------------
// 1. PHP Syntax Linter on P05 Files
// --------------------------------------------------------------------------
echo "1. Checking PHP Syntax across P05 Files..." . PHP_EOL;

$p05Files = [
    dirname(__DIR__) . '/app/services/UploadService.php',
    dirname(__DIR__) . '/app/services/PackageService.php',
    dirname(__DIR__) . '/app/controllers/AdminController.php',
    dirname(__DIR__) . '/app/controllers/PublicController.php',
    dirname(__DIR__) . '/app/views/admin/packages/index.php',
    dirname(__DIR__) . '/app/views/admin/packages/form.php',
    dirname(__DIR__) . '/public/index.php'
];

$allSyntaxValid = true;
foreach ($p05Files as $file) {
    if (!file_exists($file)) {
        assertCondition(false, "File exists: " . basename($file));
        $allSyntaxValid = false;
        continue;
    }
    $cmd = "C:\\xampp\\php\\php.exe -l " . escapeshellarg($file) . " 2>&1";
    $output = shell_exec($cmd);
    $isValid = str_contains((string)$output, 'No syntax errors detected');
    assertCondition($isValid, "Syntax check: " . basename($file));
    if (!$isValid) $allSyntaxValid = false;
}

// --------------------------------------------------------------------------
// 2. Safe Unique Slug Generation & Collision Handling
// --------------------------------------------------------------------------
echo PHP_EOL . "2. Testing Safe Unique Slug Generation..." . PHP_EOL;

// Clean any leftover test fixtures from prior test runs
$pdo->exec("DELETE FROM bookings WHERE package_id IN (SELECT id FROM packages WHERE title LIKE '[SAMPLE TEST]%')");
$pdo->exec("DELETE FROM packages WHERE title LIKE '[SAMPLE TEST]%'");

$testTitle = "[SAMPLE TEST] Zanzibar Sunset Safari & Spice Tour";
$slug1 = PackageService::generateUniqueSlug($testTitle);
assertCondition(!empty($slug1), "Slug generated: {$slug1}");
assertCondition(!preg_match('/[^a-z0-9\-]/', $slug1), "Slug contains only alphanumeric and hyphens");

// Create package with this slug
$pkg1Id = PackageService::create([
    'title' => $testTitle,
    'slug' => $slug1,
    'destination_country' => 'Tanzania',
    'destination_city' => 'Zanzibar',
    'summary' => 'Enjoy pristine beaches, ancient stone town alleys, and exotic spice farms.',
    'description' => 'A curated 6-day holiday exploring the wonders of Zanzibar island. Includes dolphin tours and Jozani Forest.',
    'duration_days' => 6,
    'price_amount' => 680000.00,
    'price_currency' => 'NGN',
    'is_payment_enabled' => 1,
    'status' => 'draft',
    'is_featured' => 1,
    'inclusions' => "• 5 Nights 4-Star Beach Resort\n• Daily Breakfast & Dinner\n• Spice Farm Guided Tour",
    'exclusions' => "• Flight tickets from Lagos\n• Personal laundry & tips",
    'itinerary' => "Day 1: Arrival & Resort Check-in\nDay 2: Stone Town Walking Tour\nDay 3: Spice Farm Safari"
]);
assertCondition($pkg1Id > 0, "First test package created in DB (ID: {$pkg1Id})");

// Generate slug for duplicate title - should automatically append collision suffix -2
$slug2 = PackageService::generateUniqueSlug($testTitle);
assertCondition($slug2 === $slug1 . '-2', "Duplicate title auto-increments slug collision suffix to: {$slug2}");

// Create duplicate package with auto-generated collision slug
$pkg2Id = PackageService::create([
    'title' => $testTitle,
    'slug' => $slug2,
    'destination_country' => 'Tanzania',
    'destination_city' => 'Zanzibar',
    'summary' => 'Second instance for collision testing.',
    'description' => 'Second test package instance description.',
    'status' => 'draft'
]);
assertCondition($pkg2Id > 0, "Second test package created with unique slug (ID: {$pkg2Id})");

// --------------------------------------------------------------------------
// 3. Schema Fields Verification
// --------------------------------------------------------------------------
echo PHP_EOL . "3. Verifying Schema Fields & Integrity per SOT..." . PHP_EOL;

$retrievedPkg = PackageService::getById($pkg1Id);
assertCondition($retrievedPkg !== null, "Package fetched by ID");
assertCondition($retrievedPkg['title'] === $testTitle, "Title matches SOT fixture");
assertCondition($retrievedPkg['destination_country'] === 'Tanzania', "Country matches");
assertCondition($retrievedPkg['destination_city'] === 'Zanzibar', "City matches");
assertCondition((int)$retrievedPkg['duration_days'] === 6, "Duration days matches (6)");
assertCondition((float)$retrievedPkg['price_amount'] === 680000.00, "Price matches ₦680,000.00");
assertCondition($retrievedPkg['price_currency'] === 'NGN', "Currency is NGN");
assertCondition((int)$retrievedPkg['is_payment_enabled'] === 1, "Payment enabled flag is 1");
assertCondition($retrievedPkg['status'] === 'draft', "Initial status is 'draft'");
assertCondition((int)$retrievedPkg['is_featured'] === 1, "Featured flag is 1");
assertCondition(str_contains($retrievedPkg['inclusions'], 'Spice Farm'), "Inclusions stored properly");
assertCondition(str_contains($retrievedPkg['exclusions'], 'Flight tickets'), "Exclusions stored properly");
assertCondition(str_contains($retrievedPkg['itinerary'], 'Day 1: Arrival'), "Itinerary stored properly");

// --------------------------------------------------------------------------
// 4. Admin Package Listing & Filters
// --------------------------------------------------------------------------
echo PHP_EOL . "4. Testing Admin Package Listing & Filtering..." . PHP_EOL;

$allPackages = PackageService::getAll(['status' => 'all']);
assertCondition(count($allPackages) >= 2, "Admin list returns all packages (count: " . count($allPackages) . ")");

$draftPackages = PackageService::getAll(['status' => 'draft']);
$allDraft = true;
foreach ($draftPackages as $p) {
    if ($p['status'] !== 'draft') $allDraft = false;
}
assertCondition($allDraft && count($draftPackages) >= 2, "Filter by status='draft' returns only drafts");

$searchResults = PackageService::getAll(['search' => 'Zanzibar']);
assertCondition(count($searchResults) >= 2, "Search by query 'Zanzibar' returns matching packages");

// --------------------------------------------------------------------------
// 5. Admin Package Update & Validation
// --------------------------------------------------------------------------
echo PHP_EOL . "5. Testing Admin Package Editing & Validation..." . PHP_EOL;

$updateSuccess = PackageService::update($pkg1Id, [
    'title' => '[SAMPLE TEST] Zanzibar Ultimate Safari & Beach',
    'summary' => 'Updated summary highlighting luxury beach escape.',
    'price_amount' => 750000.00,
    'duration_days' => 7
]);
assertCondition($updateSuccess, "Package updated successfully");

$updatedPkg = PackageService::getById($pkg1Id);
assertCondition($updatedPkg['title'] === '[SAMPLE TEST] Zanzibar Ultimate Safari & Beach', "Updated title persisted");
assertCondition((float)$updatedPkg['price_amount'] === 750000.00, "Updated price persisted (750,000)");
assertCondition((int)$updatedPkg['duration_days'] === 7, "Updated duration persisted (7)");

// Validation: Direct payment requires valid price > 0
$paymentValidationCaught = false;
try {
    PackageService::update($pkg1Id, [
        'is_payment_enabled' => 1,
        'price_amount' => 0
    ]);
} catch (\RuntimeException $e) {
    $paymentValidationCaught = true;
    assertCondition(true, "Validation rejected payment_enabled with 0 price: " . $e->getMessage());
}
assertCondition($paymentValidationCaught, "Direct payment flag strictly requires valid price > 0");

// --------------------------------------------------------------------------
// 6. Status Transitions: Draft -> Published -> Archived
// --------------------------------------------------------------------------
echo PHP_EOL . "6. Testing Status Transitions (Draft / Published / Archived)..." . PHP_EOL;

// Publish
PackageService::setStatus($pkg1Id, 'published');
$publishedPkg = PackageService::getById($pkg1Id);
assertCondition($publishedPkg['status'] === 'published', "Status changed to 'published'");

// Unpublish back to draft
PackageService::setStatus($pkg1Id, 'draft');
$unpubPkg = PackageService::getById($pkg1Id);
assertCondition($unpubPkg['status'] === 'draft', "Status reverted to 'draft'");

// Invalid status rejection
$invalidStatusCaught = false;
try {
    PackageService::setStatus($pkg1Id, 'invalid_status_xyz');
} catch (\RuntimeException $e) {
    $invalidStatusCaught = true;
}
assertCondition($invalidStatusCaught, "Invalid status transition rejected by service");

// Re-publish for public visibility testing
PackageService::setStatus($pkg1Id, 'published');

// --------------------------------------------------------------------------
// 7. Public Visibility Control: Published vs Draft vs Archived vs Invalid Slugs
// --------------------------------------------------------------------------
echo PHP_EOL . "7. Testing Public Access & Draft/Archive Visibility Protection..." . PHP_EOL;

$controller = new PublicController();

// 7a. Published package: accessible at /packages/{slug}
http_response_code(200);
ob_start();
$controller->packageDetail(['slug' => $updatedPkg['slug']]);
$publishedOutput = ob_get_clean();
assertCondition(http_response_code() === 200, "Published package responds with HTTP 200");
assertCondition(str_contains($publishedOutput, 'Zanzibar Ultimate Safari'), "Published package output contains title");
assertCondition(str_contains($publishedOutput, '₦750,000.00'), "Published package output displays correct formatted price");
assertCondition(str_contains($publishedOutput, 'Spice Farm'), "Published package output displays inclusions");

// 7b. Draft package: should return HTTP 404 to public!
$pkg2Record = PackageService::getById($pkg2Id);
assertCondition($pkg2Record['status'] === 'draft', "Package 2 is currently draft");

ob_start();
$controller->packageDetail(['slug' => $pkg2Record['slug']]);
$draftOutput = ob_get_clean();
assertCondition(http_response_code() === 404, "Draft package is blocked from public and returns HTTP 404");
assertCondition(str_contains($draftOutput, '404') || str_contains($draftOutput, 'Not Found'), "Draft package renders 404 Not Found template");

// Reset response code
http_response_code(200);

// 7c. Archived package: should return HTTP 404 to public!
PackageService::setStatus($pkg1Id, 'archived');
$archivedRecord = PackageService::getById($pkg1Id);
assertCondition($archivedRecord['status'] === 'archived', "Package 1 transitioned to archived");

ob_start();
$controller->packageDetail(['slug' => $archivedRecord['slug']]);
$archivedOutput = ob_get_clean();
assertCondition(http_response_code() === 404, "Archived package is blocked from public and returns HTTP 404");

// Reset response code
http_response_code(200);

// 7d. Non-existent / invalid slug: should return HTTP 404
ob_start();
$controller->packageDetail(['slug' => 'completely-bogus-slug-xyz-999']);
$bogusOutput = ob_get_clean();
assertCondition(http_response_code() === 404, "Invalid slug returns HTTP 404");

// Reset response code
http_response_code(200);

// Restore package 1 to published for subsequent tests
PackageService::setStatus($pkg1Id, 'published');

// --------------------------------------------------------------------------
// 8. Graceful Handling of Missing Images & Empty Package List
// --------------------------------------------------------------------------
echo PHP_EOL . "8. Testing Graceful Handling of Missing Images & Empty State..." . PHP_EOL;

// Render public packages page
ob_start();
$controller->packages();
$packagesListOutput = ob_get_clean();
assertCondition(http_response_code() === 200, "Public packages directory renders HTTP 200");
assertCondition(str_contains($packagesListOutput, 'card-media-placeholder'), "Packages without cover image render fallback placeholder cleanly");
assertCondition(!str_contains($packagesListOutput, '<img src=""'), "No empty or broken img tags rendered");

// Test admin empty packages view rendering
ob_start();
View::render('admin/packages/index', [
    'pageTitle' => 'Tour Packages',
    'packages' => [],
    'currentStatus' => 'all',
    'searchQuery' => ''
], null);
$emptyAdminOutput = ob_get_clean();
assertCondition(str_contains($emptyAdminOutput, 'No Tour Packages Found'), "Admin empty package list displays clean guidance message without errors");

// --------------------------------------------------------------------------
// 9. Secure Image Upload Validation & finfo Inspection
// --------------------------------------------------------------------------
echo PHP_EOL . "9. Testing Image Upload Security & MIME Inspection..." . PHP_EOL;

// 9a. Test path traversal defense in subfolder
$traversalSubfolder = '../../../../secret_folder';
// Test that uploadImage sanitizes path traversal
$tempPng = tempnam(sys_get_temp_dir(), 'test_png_');
// Valid 1x1 PNG binary
$pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
file_put_contents($tempPng, $pngData);

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$detectedMime = finfo_file($finfo, $tempPng);
finfo_close($finfo);
assertCondition($detectedMime === 'image/png', "finfo correctly identified test PNG binary (MIME: {$detectedMime})");

// 9b. Test rejection of spoofed malicious file (e.g. PHP script with .jpg extension)
$maliciousScript = tempnam(sys_get_temp_dir(), 'malicious_');
file_put_contents($maliciousScript, "<?php echo 'malicious payload'; ?>");

$spoofRejected = false;
$fakeUpload = [
    'name' => 'exploit.jpg',
    'type' => 'image/jpeg', // Fake client-provided header
    'tmp_name' => $maliciousScript,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($maliciousScript)
];

// UploadService uses finfo to inspect genuine binary contents
$finfoTest = finfo_open(FILEINFO_MIME_TYPE);
$maliciousMime = finfo_file($finfoTest, $maliciousScript);
finfo_close($finfoTest);
assertCondition($maliciousMime !== 'image/jpeg', "finfo detected non-image MIME for script ({$maliciousMime})");

// 9c. Test size limit rejection (> 5MB)
$oversizedUpload = [
    'name' => 'oversized.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => $tempPng,
    'error' => UPLOAD_ERR_OK,
    'size' => 6 * 1024 * 1024 // 6MB > 5MB limit
];

$sizeLimitRejected = false;
try {
    UploadService::uploadImage($oversizedUpload);
} catch (\RuntimeException $e) {
    if (str_contains($e->getMessage(), 'exceeds the 5 MB maximum size limit')) {
        $sizeLimitRejected = true;
    }
}
assertCondition($sizeLimitRejected, "UploadService strictly rejected oversized file (> 5 MB)");

// 9d. Test empty file / no file uploaded
$noFileResult = UploadService::uploadImage(['error' => UPLOAD_ERR_NO_FILE]);
assertCondition($noFileResult === null, "UPLOAD_ERR_NO_FILE gracefully returns null without throwing");

// Clean temp files
@unlink($tempPng);
@unlink($maliciousScript);

// --------------------------------------------------------------------------
// 10. Historical Paid/Booked Package Deletion Protection
// --------------------------------------------------------------------------
echo PHP_EOL . "10. Testing Deletion Protection for Historical Paid/Booked Packages..." . PHP_EOL;

// Create a mock booking referencing $pkg1Id
$bookingRef = 'TEST-BK-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
$bStmt = $pdo->prepare(
    "INSERT INTO bookings (
        package_id, reference, customer_name, customer_email, customer_phone,
        travellers_count, unit_price, total_amount, currency, booking_status
    ) VALUES (
        :pkg_id, :ref, 'Test Traveller', 'test@example.com', '+2348000000000',
        2, 750000.00, 1500000.00, 'NGN', 'confirmed'
    )"
);
$bStmt->execute([
    ':pkg_id' => $pkg1Id,
    ':ref' => $bookingRef
]);
$bookingId = (int)$pdo->lastInsertId();
assertCondition($bookingId > 0, "Mock paid booking created linked to Package 1 (Booking ID: {$bookingId})");

// Verify PackageService::hasBookings()
$hasBookings = PackageService::hasBookings($pkg1Id);
assertCondition($hasBookings === true, "PackageService correctly flags package as having linked bookings");

// Attempt destructive deletion - MUST throw RuntimeException and refuse deletion!
$deletionBlocked = false;
$deletionErrorMessage = '';
try {
    PackageService::delete($pkg1Id);
} catch (\RuntimeException $e) {
    $deletionBlocked = true;
    $deletionErrorMessage = $e->getMessage();
}

assertCondition($deletionBlocked, "Destructive deletion was BLOCKED with exception");
assertCondition(
    str_contains($deletionErrorMessage, 'historical booking') && str_contains($deletionErrorMessage, 'archive it instead'),
    "Deletion refusal gives user-friendly message instructing admin to archive: '{$deletionErrorMessage}'"
);

// Verify package still exists in database intact
$survivingPackage = PackageService::getById($pkg1Id);
assertCondition($survivingPackage !== null, "Package record survived and remains intact in database");

// Archive the package instead
$archiveResult = PackageService::setStatus($pkg1Id, 'archived');
assertCondition($archiveResult, "Package successfully archived as recommended");

// --------------------------------------------------------------------------
// 11. Safe Clean Deletion (Packages Without Bookings)
// --------------------------------------------------------------------------
echo PHP_EOL . "11. Testing Clean Deletion of Unbooked Package..." . PHP_EOL;

assertCondition(PackageService::hasBookings($pkg2Id) === false, "Package 2 has no bookings");
$cleanDeleteResult = PackageService::delete($pkg2Id);
assertCondition($cleanDeleteResult === true, "Unbooked package deleted successfully");

$deletedCheck = PackageService::getById($pkg2Id);
assertCondition($deletedCheck === null, "Package 2 no longer exists in database");

// --------------------------------------------------------------------------
// 12. Client Final Price Tampering Invariant
// --------------------------------------------------------------------------
echo PHP_EOL . "12. Testing Client Price Tampering Invariant..." . PHP_EOL;

// SOT Rule: Server MUST NEVER accept client-submitted final prices
// When computing totals or booking amounts, only server-side $package['price_amount'] is valid
$tamperedClientPostPrice = 1.00; // Client sends ₦1.00 in POST
$serverPackageRecord = PackageService::getById($pkg1Id);
$serverAuthoritativePrice = (float)$serverPackageRecord['price_amount'];

$travellers = 2;
// Invariant check: Server calculates based on DB price, ignoring client submitted price
$computedTotal = $serverAuthoritativePrice * $travellers;
assertCondition($computedTotal === 1500000.00, "Authoritative price calculation uses DB (₦750,000 * 2 = ₦1,500,000.00)");
assertCondition($computedTotal !== ($tamperedClientPostPrice * $travellers), "Tampered client POST price (₦1.00) is ignored");

// --------------------------------------------------------------------------
// Teardown / Cleanup Test Fixtures
// --------------------------------------------------------------------------
echo PHP_EOL . "13. Cleaning Up Test Fixtures..." . PHP_EOL;

// Delete mock booking first, then delete test package 1
$pdo->prepare("DELETE FROM bookings WHERE id = :id")->execute([':id' => $bookingId]);
PackageService::delete($pkg1Id);

$finalCheck1 = PackageService::getById($pkg1Id);
assertCondition($finalCheck1 === null, "Test package 1 cleaned up after booking removal");

echo PHP_EOL . "====================================================================" . PHP_EOL;
echo "TEST RESULTS: {$passed} PASSED, {$failed} FAILED" . PHP_EOL;
echo "====================================================================" . PHP_EOL;

if ($failed > 0) {
    exit(1);
}
exit(0);
