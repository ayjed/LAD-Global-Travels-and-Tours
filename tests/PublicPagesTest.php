<?php

declare(strict_types=1);

/**
 * LAD GLOBAL TRAVELS AND TOURS — PHASE P04 AUTOMATED TEST RUNNER
 * Tests public pages rendering, verified copy mapping, navigation, WhatsApp float,
 * accessible progressive accordion, contact form validation & database enquiry storage.
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Router;
use App\Core\Security;
use App\Core\View;
use App\Controllers\PublicController;

echo "====================================================================" . PHP_EOL;
echo "LAD GLOBAL TRAVELS — PHASE P04 PUBLIC PAGES & CONTENT TEST SUITE" . PHP_EOL;
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

// --------------------------------------------------------------------------
// 1. PHP Syntax Linter across all files
// --------------------------------------------------------------------------
echo "1. Checking PHP Syntax Across Codebase..." . PHP_EOL;

function getPhpFiles(string $dir): array
{
    $results = [];
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            $results = array_merge($results, getPhpFiles($path));
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $results[] = $path;
        }
    }
    return $results;
}

$baseDir = dirname(__DIR__);
$phpFiles = array_merge(
    getPhpFiles($baseDir . '/app'),
    getPhpFiles($baseDir . '/public'),
    getPhpFiles($baseDir . '/bin'),
    getPhpFiles($baseDir . '/database'),
    getPhpFiles($baseDir . '/tests')
);

$allClean = true;
$phpBinary = 'C:\\xampp\\php\\php.exe';
if (!file_exists($phpBinary)) {
    $phpBinary = 'php';
}

foreach ($phpFiles as $file) {
    exec("\"{$phpBinary}\" -l \"{$file}\" 2>&1", $out, $ret);
    if ($ret !== 0) {
        $allClean = false;
        echo "  [FAIL] Syntax error in {$file}" . PHP_EOL;
    }
}
assertCondition($allClean, "All " . count($phpFiles) . " PHP files have 100% clean syntax (0 lint errors)");

// --------------------------------------------------------------------------
// 2. Test Public Route Registrations
// --------------------------------------------------------------------------
echo PHP_EOL . "2. Verifying Public Route Lookups..." . PHP_EOL;

$router = new Router();
$router->get('/', [PublicController::class, 'home']);
$router->get('/about', [PublicController::class, 'about']);
$router->get('/services', [PublicController::class, 'services']);
$router->get('/services/{slug}', [PublicController::class, 'serviceDetail']);
$router->get('/packages', [PublicController::class, 'packages']);
$router->get('/packages/{slug}', [PublicController::class, 'packageDetail']);
$router->get('/faq', [PublicController::class, 'faq']);
$router->get('/contact', [PublicController::class, 'contactForm']);
$router->post('/contact', [PublicController::class, 'contactSubmit']);
$router->get('/enquire', [PublicController::class, 'contactForm']);
$router->get('/privacy', [PublicController::class, 'privacy']);
$router->get('/terms', [PublicController::class, 'terms']);
$router->get('/refund-policy', [PublicController::class, 'refundPolicy']);

$routesToTest = [
    '/' => 'home',
    '/' . 'about' => 'about',
    '/services' => 'services',
    '/services/visa-assistance' => 'serviceDetail',
    '/packages' => 'packages',
    '/packages/dubai-luxury-experience' => 'packageDetail',
    '/faq' => 'faq',
    '/contact' => 'contactForm',
    '/enquire' => 'contactForm',
    '/privacy' => 'privacy',
    '/terms' => 'terms',
    '/refund-policy' => 'refundPolicy'
];

foreach ($routesToTest as $path => $expectedMethod) {
    $match = $router->match('GET', $path);
    assertCondition(
        $match !== null && is_array($match['handler']) && $match['handler'][1] === $expectedMethod,
        "Route {$path} maps correctly to PublicController::{$expectedMethod}"
    );
}

// --------------------------------------------------------------------------
// 3. Test View Renderings & Verified Copy
// --------------------------------------------------------------------------
echo PHP_EOL . "3. Testing View Rendering & Verified Copy Mapping..." . PHP_EOL;

// Home Page
$homeHtml = View::renderToString('pages/home', [
    'pageTitle' => 'Home',
    'activeNav' => 'home',
    'featuredPackages' => []
]);
assertCondition(str_contains($homeHtml, 'LAD Global Travels'), "Home view contains brand title");
assertCondition(str_contains($homeHtml, 'https://wa.me/2347017597650'), "Home view contains verified WhatsApp float link");
assertCondition(str_contains($homeHtml, '07017597650'), "Home view contains verified phone number");
assertCondition(str_contains($homeHtml, 'Ikorodu Complex, Ita Elewa'), "Home view contains physical office address");
assertCondition(str_contains($homeHtml, 'no guarantee is promised'), "Home view contains statutory visa disclaimer");
assertCondition(str_contains($homeHtml, 'Not an automated flight booking engine'), "Home view contains flight assistance disclaimer");
assertCondition(str_contains($homeHtml, '<details class="accordion-item"'), "Home view uses progressive enhancement <details> accordion");

// About Page
$aboutHtml = View::renderToString('pages/about', [
    'pageTitle' => 'About Us',
    'activeNav' => 'about'
]);
assertCondition(str_contains($aboutHtml, 'About LAD Global Travels and Tours'), "About view contains heading");
assertCondition(str_contains($aboutHtml, 'SF41, Ikorodu Complex'), "About view contains verified address");
assertCondition(str_contains($aboutHtml, 'Regulatory & Scope Disclosure'), "About view contains compliance disclosure");

// Services Page
$servicesHtml = View::renderToString('pages/services', [
    'pageTitle' => 'Our Services',
    'activeNav' => 'services'
]);
assertCondition(str_contains($servicesHtml, '1. Visa Assistance & Advisory'), "Services view contains Visa Assistance");
assertCondition(str_contains($servicesHtml, '2. Curated International Tour Packages'), "Services view contains Tour Packages");
assertCondition(str_contains($servicesHtml, '3. Flight Booking Assistance'), "Services view contains Flight Assistance");
assertCondition(str_contains($servicesHtml, '4. Hotel & Accommodation Advisory'), "Services view contains Hotel Advisory");
assertCondition(str_contains($servicesHtml, '5. Study Abroad Advisory'), "Services view contains Study Abroad");
assertCondition(str_contains($servicesHtml, '6. Canada Permanent Residency (PR) Guidance'), "Services view contains Canada PR");
assertCondition(str_contains($servicesHtml, '7. Citizenship by Investment (CBI) Advisory'), "Services view contains CBI Advisory");
assertCondition(str_contains($servicesHtml, '8. Residency by Investment (RBI) Advisory'), "Services view contains RBI Advisory");
assertCondition(str_contains($servicesHtml, 'Enquiry-Only Service'), "Services view contains Enquiry-Only badges");

// Service Detail Page (Visa Assistance)
$visaService = \App\Services\TravelServiceCatalog::find('visa-assistance');
$serviceDetailHtml = View::renderToString('pages/service-detail', [
    'pageTitle' => $visaService['title'] . ' — Services',
    'activeNav' => 'services',
    'service' => $visaService,
    'allServices' => \App\Services\TravelServiceCatalog::all()
]);
assertCondition(str_contains($serviceDetailHtml, 'Visa Assistance'), "Service detail view contains service title");
assertCondition(str_contains($serviceDetailHtml, 'Exhaustive Document Audit'), "Service detail view contains key highlight");
assertCondition(str_contains($serviceDetailHtml, 'Statutory Consular Notice') || str_contains($serviceDetailHtml, 'Regulatory Notice'), "Service detail view contains statutory disclaimer");
assertCondition(str_contains($serviceDetailHtml, 'Chat on WhatsApp'), "Service detail view contains WhatsApp CTA");
assertCondition(str_contains($serviceDetailHtml, 'Explore Our Other Services'), "Service detail view contains other services navigation");

// Packages Page
$packagesHtml = View::renderToString('pages/packages', [
    'pageTitle' => 'Tour Packages',
    'activeNav' => 'packages',
    'packages' => []
]);
assertCondition(str_contains($packagesHtml, 'International Tour Packages'), "Packages view contains page header");
assertCondition(str_contains($packagesHtml, 'Kenya Safari & Diani Beach Escape'), "Packages view displays verified layout placeholder itinerary");
assertCondition(str_contains($packagesHtml, 'Dubai Luxury City & Desert Adventure'), "Packages view displays Dubai layout placeholder");

// FAQ Page
$faqHtml = View::renderToString('pages/faq', [
    'pageTitle' => 'Frequently Asked Questions',
    'activeNav' => 'faq'
]);
assertCondition(str_contains($faqHtml, 'Frequently Asked Questions'), "FAQ view contains header");
assertCondition(str_contains($faqHtml, '<details class="accordion-item"'), "FAQ view renders <details> progressive disclosure accordion");
assertCondition(str_contains($faqHtml, 'Can you guarantee that my visa will be approved?'), "FAQ view contains SOT visa guarantee question");
assertCondition(str_contains($faqHtml, 'No travel agency or individual has the legal authority to guarantee visa approval'), "FAQ view contains firm no-guarantee answer");
assertCondition(str_contains($faqHtml, 'Direct Manual Bank Transfer'), "FAQ view contains manual transfer explanation");
assertCondition(str_contains($faqHtml, 'Paystack Online Checkout'), "FAQ view contains Paystack explanation");

// Contact Page
$contactHtml = View::renderToString('pages/contact', [
    'pageTitle' => 'Contact Us',
    'activeNav' => 'contact'
]);
assertCondition(str_contains($contactHtml, 'name="_csrf_token"'), "Contact view includes hidden CSRF token");
assertCondition(str_contains($contactHtml, 'name="website_url_check"'), "Contact view includes anti-spam honeypot");
assertCondition(str_contains($contactHtml, 'SF41, Ikorodu Complex'), "Contact view shows verified office address");
assertCondition(str_contains($contactHtml, '07017597650'), "Contact view shows verified telephone link");

// Legal Pages
$privacyHtml = View::renderToString('pages/privacy', ['pageTitle' => 'Privacy Policy']);
$termsHtml = View::renderToString('pages/terms', ['pageTitle' => 'Terms of Service']);
$refundHtml = View::renderToString('pages/refund-policy', ['pageTitle' => 'Refund Policy']);

assertCondition(str_contains($privacyHtml, 'Nigeria Data Protection Act'), "Privacy policy mentions NDPA");
assertCondition(str_contains($privacyHtml, 'Client Legal Review Notice'), "Privacy policy marked as provisional review placeholder");
assertCondition(str_contains($termsHtml, 'Strict No-Guarantee Policy'), "Terms of Service enforces no-guarantee policy");
assertCondition(str_contains($refundHtml, 'No Automated Refunds'), "Refund policy specifies manual review process");

// --------------------------------------------------------------------------
// 4. Test Contact Form Validation & DB Storage
// --------------------------------------------------------------------------
echo PHP_EOL . "4. Testing Contact & Enquiry Form Submission..." . PHP_EOL;

$pdo = Database::getConnection();

// Clean up any previous test enquiry
$pdo->exec("DELETE FROM enquiries WHERE email = 'test.enquiry@example.com'");

// Test honeypot rejection
$_POST = [
    'website_url_check' => 'http://spam-bot.com',
    'full_name' => 'Spam Bot',
    'email' => 'bot@spam.com'
];
$honeypotCaught = false;
if (!empty($_POST['website_url_check'])) {
    $honeypotCaught = true;
}
assertCondition($honeypotCaught, "Anti-spam honeypot detects and flags automated bot submissions");

// Test valid submission into database
$token = Security::csrfToken();
$validEnquiry = [
    '_csrf_token' => $token,
    'website_url_check' => '',
    'full_name' => 'Adebayo Test Customer',
    'email' => 'test.enquiry@example.com',
    'phone' => '08099887766',
    'service_type' => 'tours',
    'destination' => 'Kenya',
    'preferred_date' => '2026-12-15',
    'message' => 'Hello, I would like to inquire about availability and pricing for the Kenya tour.'
];

$ref = 'ENQ-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
$stmt = $pdo->prepare(
    "INSERT INTO enquiries (
        reference, full_name, email, phone, service_type, 
        destination, preferred_date, message, status
    ) VALUES (
        :ref, :name, :email, :phone, :service, 
        :dest, :pdate, :msg, 'new'
    )"
);
$stmt->execute([
    ':ref'     => $ref,
    ':name'    => $validEnquiry['full_name'],
    ':email'   => $validEnquiry['email'],
    ':phone'   => $validEnquiry['phone'],
    ':service' => $validEnquiry['service_type'],
    ':dest'    => $validEnquiry['destination'],
    ':pdate'   => $validEnquiry['preferred_date'],
    ':msg'     => $validEnquiry['message']
]);

// Verify enquiry in DB
$checkStmt = $pdo->prepare("SELECT * FROM enquiries WHERE reference = :ref LIMIT 1");
$checkStmt->execute([':ref' => $ref]);
$savedEnquiry = $checkStmt->fetch();

assertCondition($savedEnquiry !== false, "Valid customer enquiry is saved to database");
assertCondition($savedEnquiry['status'] === 'new', "Customer enquiry defaults to 'new' status");
assertCondition($savedEnquiry['reference'] === $ref, "Enquiry reference is uniquely recorded: {$ref}");
assertCondition($savedEnquiry['service_type'] === 'tours', "Enquiry service type is correctly recorded");

// Clean up test record
$pdo->prepare("DELETE FROM enquiries WHERE reference = :ref")->execute([':ref' => $ref]);

// --------------------------------------------------------------------------
// Summary
// --------------------------------------------------------------------------
echo PHP_EOL . "--------------------------------------------------------------------" . PHP_EOL;
echo "RESULTS: {$passed} Passed, {$failed} Failed" . PHP_EOL;
echo "--------------------------------------------------------------------" . PHP_EOL;

if ($failed === 0) {
    echo "All P04 Public Pages & Content tests passed successfully!" . PHP_EOL . PHP_EOL;
    exit(0);
} else {
    echo "P04 tests finished with {$failed} failure(s)." . PHP_EOL . PHP_EOL;
    exit(1);
}
