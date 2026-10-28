<?php

declare(strict_types=1);

/**
 * LAD GLOBAL TRAVELS AND TOURS — PUBLIC WEB FRONT CONTROLLER
 * Single entry point for all web traffic.
 */

// 1. Initialize Application Bootstrap
if (!defined('ROOT_PATH')) {
    if (file_exists(dirname(__DIR__) . '/app/bootstrap.php')) {
        define('ROOT_PATH', dirname(__DIR__));
    } elseif (file_exists(dirname(__DIR__) . '/lad_travels_app/app/bootstrap.php')) {
        define('ROOT_PATH', dirname(__DIR__) . '/lad_travels_app');
    } elseif (file_exists(dirname(__DIR__) . '/lad_travels/app/bootstrap.php')) {
        define('ROOT_PATH', dirname(__DIR__) . '/lad_travels');
    } else {
        define('ROOT_PATH', dirname(__DIR__));
    }
}
require_once ROOT_PATH . '/app/bootstrap.php';

use App\Core\Router;
use App\Core\View;
use App\Core\Response;

// 2. Initialize Router
$router = new Router();

// 3. Register Core Public Routes
$router->get('/', [App\Controllers\PublicController::class, 'home']);
$router->get('/about', [App\Controllers\PublicController::class, 'about']);
$router->get('/services', [App\Controllers\PublicController::class, 'services']);
$router->get('/services/{slug}', [App\Controllers\PublicController::class, 'serviceDetail']);
$router->get('/packages', [App\Controllers\PublicController::class, 'packages']);
$router->get('/packages/{slug}', [App\Controllers\PublicController::class, 'packageDetail']);
$router->get('/faq', [App\Controllers\PublicController::class, 'faq']);
$router->get('/contact', [App\Controllers\PublicController::class, 'contactForm']);
$router->post('/contact', [App\Controllers\PublicController::class, 'contactSubmit']);
$router->get('/enquire', [App\Controllers\PublicController::class, 'contactForm']);
$router->post('/enquire', [App\Controllers\PublicController::class, 'contactSubmit']);

// Legal & Compliance Routes (Awaiting Client Final Legal Sign-off)
$router->get('/privacy', [App\Controllers\PublicController::class, 'privacy']);
$router->get('/terms', [App\Controllers\PublicController::class, 'terms']);
$router->get('/refund-policy', [App\Controllers\PublicController::class, 'refundPolicy']);
$router->get('/sitemap.xml', [App\Controllers\PublicController::class, 'sitemapXml']);

// 4. Public Booking & Payment Routes (Phase P07)
$router->get('/packages/{slug}/book', [App\Controllers\BookingController::class, 'showBookingForm']);
$router->post('/packages/{slug}/book', [App\Controllers\BookingController::class, 'submitBooking']);
$router->get('/booking/{reference}', [App\Controllers\BookingController::class, 'showBookingStatus']);
$router->get('/booking/{reference}/transfer', [App\Controllers\BookingController::class, 'showTransferInstructions']);
$router->post('/booking/{reference}/transfer-memo', [App\Controllers\BookingController::class, 'submitTransferMemo']);
$router->get('/booking/{reference}/receipt', [App\Controllers\BookingController::class, 'showReceipt']);

// Paystack Gateway Routes
$router->get('/paystack/sandbox/{reference}', [App\Controllers\BookingController::class, 'paystackSandbox']);
$router->get('/paystack/callback', [App\Controllers\BookingController::class, 'paystackCallback']);
$router->post('/paystack/webhook', [App\Controllers\BookingController::class, 'paystackWebhook']);

// 5. Admin Authentication & Management Routes
$router->get('/admin/login', [App\Controllers\AdminController::class, 'loginForm']);
$router->post('/admin/login', [App\Controllers\AdminController::class, 'loginSubmit']);
$router->post('/admin/logout', [App\Controllers\AdminController::class, 'logout']);
$router->get('/admin', [App\Controllers\AdminController::class, 'dashboard']);

// Emergency Password Reset Routes (Prevents 404 on recovery)
$router->get('/reset-admin', [App\Controllers\AdminController::class, 'recoveryForm']);
$router->post('/reset-admin', [App\Controllers\AdminController::class, 'recoverySubmit']);
$router->get('/reset-admin.php', [App\Controllers\AdminController::class, 'recoveryForm']);
$router->post('/reset-admin.php', [App\Controllers\AdminController::class, 'recoverySubmit']);

// 6. Admin Packages Routes (Phase P05)
$router->get('/admin/packages', [App\Controllers\AdminController::class, 'packages']);
$router->get('/admin/packages/create', [App\Controllers\AdminController::class, 'createPackageForm']);
$router->post('/admin/packages/create', [App\Controllers\AdminController::class, 'createPackageSubmit']);
$router->get('/admin/packages/{id}/edit', [App\Controllers\AdminController::class, 'editPackageForm']);
$router->post('/admin/packages/{id}/edit', [App\Controllers\AdminController::class, 'editPackageSubmit']);
$router->post('/admin/packages/{id}/status', [App\Controllers\AdminController::class, 'updatePackageStatus']);
$router->post('/admin/packages/{id}/delete', [App\Controllers\AdminController::class, 'deletePackage']);
$router->post('/admin/packages/{id}/images/{imageId}/delete', [App\Controllers\AdminController::class, 'deletePackageImage']);

// 7. Admin Enquiries & CRM Routes (Phase P06)
$router->get('/admin/enquiries', [App\Controllers\AdminController::class, 'enquiries']);
$router->get('/admin/enquiries/{id}', [App\Controllers\AdminController::class, 'enquiryDetail']);
$router->post('/admin/enquiries/{id}/status', [App\Controllers\AdminController::class, 'updateEnquiryStatus']);
$router->post('/admin/enquiries/{id}/notes', [App\Controllers\AdminController::class, 'addEnquiryNote']);

// 8. Admin Bookings & Payments Routes (Phase P07)
$router->get('/admin/bookings', [App\Controllers\AdminController::class, 'bookings']);
$router->get('/admin/bookings/{id}', [App\Controllers\AdminController::class, 'bookingDetail']);
$router->post('/admin/bookings/{id}/status', [App\Controllers\AdminController::class, 'updateBookingStatus']);
$router->get('/admin/payments', [App\Controllers\AdminController::class, 'payments']);
$router->post('/admin/payments/{id}/verify', [App\Controllers\AdminController::class, 'verifyManualPayment']);
$router->post('/admin/payments/{id}/reject', [App\Controllers\AdminController::class, 'rejectManualPayment']);

// 9. Admin Settings & Integration Routes (Phase P08)
$router->get('/admin/settings', [App\Controllers\AdminController::class, 'settings']);
$router->post('/admin/settings', [App\Controllers\AdminController::class, 'updateSettings']);

// 4. Configure 404 Handler
$router->setNotFoundHandler(function () {
    View::render('pages/404', [
        'pageTitle' => '404 Page Not Found'
    ]);
});

// 5. Dispatch Current HTTP Request
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';

$router->dispatch($method, $uri);
