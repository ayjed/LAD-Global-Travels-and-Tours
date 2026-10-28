<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Env;
use App\Core\Response;
use App\Core\Security;
use App\Core\Validator;
use App\Core\View;
use App\Services\BookingService;
use App\Services\EnquiryService;
use App\Services\PackageService;
use App\Services\PaystackService;
use App\Services\SettingService;
use App\Services\UploadService;
use PDO;
use RuntimeException;
use Throwable;

/**
 * Controller managing single-admin authentication, dashboard, and package operations.
 */
class AdminController
{
    /**
     * Render the admin login screen.
     */
    public function loginForm(): void
    {
        if (Auth::check()) {
            Response::redirect('/admin');
        }

        View::render('admin/login', [
            'pageTitle' => 'Administrator Sign In'
        ], null); // Render without public layout
    }

    /**
     * Process admin login submission with CSRF and rate limiting.
     */
    public function loginSubmit(): void
    {
        // 1. Verify CSRF Token
        $token = $_POST['_csrf_token'] ?? null;
        if (!Security::verifyCsrf($token)) {
            Response::setFlash('danger', 'Security token expired or invalid. Please try signing in again.');
            Response::redirect('/admin/login');
        }

        // 2. Validate Input
        $validator = Validator::make($_POST, [
            'username' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            Response::setFlash('danger', 'Please enter both your username and password.');
            Response::redirect('/admin/login');
        }

        $username = trim((string)$_POST['username']);
        $password = (string)$_POST['password'];

        // 3. Attempt Authentication
        [$success, $message] = Auth::attempt($username, $password);

        if (!$success) {
            Response::setFlash('danger', $message);
            Response::redirect('/admin/login');
        }

        Response::setFlash('success', "Welcome back, " . Security::e(Auth::username()) . ".");
        Response::redirect('/admin');
    }

    /**
     * Terminate the admin session (POST-only with CSRF).
     */
    public function logout(): void
    {
        $token = $_POST['_csrf_token'] ?? null;
        if (Security::verifyCsrf($token)) {
            Auth::logout();
            Response::setFlash('info', 'You have been securely signed out.');
        }

        Response::redirect('/admin/login');
    }

    /**
     * Emergency Admin Recovery View.
     */
    public function recoveryForm(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, username, email, failed_login_attempts, locked_until FROM admins");
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        View::render('admin/recovery', [
            'pageTitle' => 'Emergency Admin Recovery',
            'admins'    => $admins
        ], null);
    }

    /**
     * Process Emergency Admin Password Reset.
     */
    public function recoverySubmit(): void
    {
        $username = trim((string)($_POST['username'] ?? 'admin'));
        $newPassword = (string)($_POST['new_password'] ?? '');

        if (strlen($newPassword) < 8) {
            Response::setFlash('danger', 'Password must be at least 8 characters long.');
            Response::redirect('/reset-admin');
        }

        try {
            $hash = Security::hashPassword($newPassword);
            $pdo = Database::getConnection();

            // Check if any admin exists
            $stmt = $pdo->query("SELECT id FROM admins LIMIT 1");
            $firstAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($firstAdmin) {
                // Update the existing admin record
                $update = $pdo->prepare("UPDATE admins SET username = :u, password_hash = :hash, failed_login_attempts = 0, locked_until = NULL WHERE id = :id");
                $update->execute([
                    ':u'    => $username,
                    ':hash' => $hash,
                    ':id'   => $firstAdmin['id']
                ]);
            } else {
                // Insert fresh admin record with ID 1
                $insert = $pdo->prepare("INSERT INTO admins (id, username, email, password_hash, failed_login_attempts, locked_until, created_at) VALUES (1, :u, :e, :h, 0, NULL, NOW())");
                $insert->execute([
                    ':u' => $username,
                    ':e' => $username . '@ladglobaltravels.com',
                    ':h' => $hash
                ]);
            }

            // Clear rate limiter files safely
            try {
                $tempDir = defined('STORAGE_PATH') ? STORAGE_PATH . '/temp' : ROOT_PATH . '/storage/temp';
                if (is_dir($tempDir)) {
                    $files = @glob($tempDir . '/rate_limit_*.json');
                    if (is_array($files)) {
                        foreach ($files as $f) {
                            @unlink($f);
                        }
                    }
                }
                $sysTemp = sys_get_temp_dir();
                if (!empty($sysTemp) && is_dir($sysTemp)) {
                    $files = @glob($sysTemp . '/rate_limit_*.json');
                    if (is_array($files)) {
                        foreach ($files as $f) {
                            @unlink($f);
                        }
                    }
                }
            } catch (Throwable $ignore) {
            }

            Response::setFlash('success', "SUCCESS! Admin password for '{$username}' updated to: {$newPassword}. Lockout cleared! Click the button below to sign in.");
            Response::redirect('/reset-admin');
        } catch (Throwable $e) {
            Response::setFlash('danger', "Error saving password: " . $e->getMessage());
            Response::redirect('/reset-admin');
        }
    }

    /**
     * Protected Admin Dashboard overview.
     */
    public function dashboard(): void
    {
        Auth::guard();

        $pdo = Database::getConnection();

        // 1. Fetch Real Counts
        // Total & Today's Enquiries
        $enqStmt = $pdo->query("SELECT 
            COUNT(*) as total_enquiries,
            SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_enquiries
            FROM enquiries");
        $enqCounts = $enqStmt->fetch(PDO::FETCH_ASSOC) ?: ['total_enquiries' => 0, 'today_enquiries' => 0];

        // Active Tour Packages
        $pkgStmt = $pdo->query("SELECT COUNT(*) FROM packages WHERE status = 'published'");
        $activePackages = (int)$pkgStmt->fetchColumn();

        // Pending Manual Bank Transfers (Reconciliation Queue)
        $trfStmt = $pdo->query("SELECT COUNT(*) FROM payments WHERE provider = 'manual' AND status = 'pending'");
        $pendingTransfersCount = (int)$trfStmt->fetchColumn();

        // 2. Fetch Recent Enquiries (Latest 5)
        $recentEnquiriesStmt = $pdo->query("SELECT id, reference, full_name, service_type, phone, status, created_at 
                                            FROM enquiries 
                                            ORDER BY created_at DESC 
                                            LIMIT 5");
        $recentEnquiries = $recentEnquiriesStmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Fetch Pending Transfers (Latest 5)
        $pendingTransfersStmt = $pdo->query("SELECT p.id, p.payment_reference, p.amount, p.currency, p.created_at,
                                                    b.reference as booking_ref, b.customer_name, b.customer_phone,
                                                    pkg.title as package_title
                                             FROM payments p
                                             JOIN bookings b ON p.booking_id = b.id
                                             JOIN packages pkg ON b.package_id = pkg.id
                                             WHERE p.provider = 'manual' AND p.status = 'pending'
                                             ORDER BY p.created_at ASC
                                             LIMIT 5");
        $pendingTransfers = $pendingTransfersStmt->fetchAll(PDO::FETCH_ASSOC);

        View::render('admin/dashboard', [
            'pageTitle' => 'Admin Dashboard',
            'adminUsername' => Auth::username(),
            'totalEnquiries' => (int)$enqCounts['total_enquiries'],
            'todayEnquiries' => (int)$enqCounts['today_enquiries'],
            'activePackages' => $activePackages,
            'pendingTransfersCount' => $pendingTransfersCount,
            'recentEnquiries' => $recentEnquiries,
            'pendingTransfers' => $pendingTransfers
        ], 'admin');
    }

    /**
     * List all tour packages with filters.
     */
    public function packages(): void
    {
        Auth::guard();

        $status = $_GET['status'] ?? 'all';
        $search = $_GET['search'] ?? '';

        $packages = PackageService::getAll([
            'status' => $status,
            'search' => $search
        ]);

        View::render('admin/packages/index', [
            'pageTitle' => 'Tour Packages Management',
            'packages' => $packages,
            'currentStatus' => $status,
            'searchQuery' => $search
        ], 'admin');
    }

    /**
     * Show package creation form.
     */
    public function createPackageForm(): void
    {
        Auth::guard();

        View::render('admin/packages/form', [
            'pageTitle' => 'Create New Package',
            'package' => null,
            'errors' => null,
            'formAction' => '/admin/packages/create'
        ], 'admin');
    }

    /**
     * Process package creation submission.
     */
    public function createPackageSubmit(): void
    {
        Auth::guard();

        $token = $_POST['_csrf_token'] ?? null;
        if (!Security::verifyCsrf($token)) {
            Response::setFlash('danger', 'Security token expired or invalid.');
            Response::redirect('/admin/packages/create');
            return;
        }

        $validator = Validator::make($_POST, [
            'title' => 'required|min:3|max:255',
            'destination_country' => 'required|min:2|max:100',
            'summary' => 'required|min:10',
            'description' => 'required|min:20'
        ]);

        if ($validator->fails()) {
            Response::setFlash('danger', 'Please correct the validation errors.');
            View::render('admin/packages/form', [
                'pageTitle' => 'Create New Package',
                'package' => $_POST,
                'errors' => $validator->errors(),
                'formAction' => '/admin/packages/create'
            ], 'admin');
            return;
        }

        // Upload cover image securely if provided
        $coverImagePath = null;
        if (!empty($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                $coverImagePath = UploadService::uploadImage($_FILES['cover_image'], 'packages');
            } catch (RuntimeException $e) {
                Response::setFlash('danger', 'Image upload error: ' . $e->getMessage());
                View::render('admin/packages/form', [
                    'pageTitle' => 'Create New Package',
                    'package' => $_POST,
                    'errors' => ['cover_image' => [$e->getMessage()]],
                    'formAction' => '/admin/packages/create'
                ], 'admin');
                return;
            }
        }

        $data = $_POST;
        if ($coverImagePath !== null) {
            $data['cover_image'] = $coverImagePath;
        }

        try {
            $packageId = PackageService::create($data);

            // Upload secondary gallery images if provided
            if (!empty($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
                $galleryPaths = UploadService::uploadMultipleImages($_FILES['gallery_images'], 'packages');
                foreach ($galleryPaths as $idx => $path) {
                    PackageService::addGalleryImage($packageId, $path, $data['title'], $idx + 1);
                }
            }

            Response::setFlash('success', "Package '" . Security::e($data['title']) . "' created successfully.");
            Response::redirect('/admin/packages');
        } catch (RuntimeException $e) {
            Response::setFlash('danger', $e->getMessage());
            View::render('admin/packages/form', [
                'pageTitle' => 'Create New Package',
                'package' => $_POST,
                'errors' => ['general' => [$e->getMessage()]],
                'formAction' => '/admin/packages/create'
            ], 'admin');
        }
    }

    /**
     * Show package edit form.
     */
    public function editPackageForm(array $params): void
    {
        Auth::guard();

        $id = (int)($params['id'] ?? 0);
        $package = PackageService::getById($id);

        if (!$package) {
            Response::setFlash('danger', "Package with ID {$id} not found.");
            Response::redirect('/admin/packages');
            return;
        }

        $galleryImages = PackageService::getGalleryImages($id);

        View::render('admin/packages/form', [
            'pageTitle' => 'Edit Package: ' . $package['title'],
            'package' => $package,
            'galleryImages' => $galleryImages,
            'errors' => null,
            'formAction' => "/admin/packages/{$id}/edit"
        ], 'admin');
    }

    /**
     * Process package edit submission.
     */
    public function editPackageSubmit(array $params): void
    {
        Auth::guard();

        $id = (int)($params['id'] ?? 0);
        $existing = PackageService::getById($id);

        if (!$existing) {
            Response::setFlash('danger', "Package with ID {$id} not found.");
            Response::redirect('/admin/packages');
            return;
        }

        $token = $_POST['_csrf_token'] ?? null;
        if (!Security::verifyCsrf($token)) {
            Response::setFlash('danger', 'Security token expired or invalid.');
            Response::redirect("/admin/packages/{$id}/edit");
            return;
        }

        $validator = Validator::make($_POST, [
            'title' => 'required|min:3|max:255',
            'destination_country' => 'required|min:2|max:100',
            'summary' => 'required|min:10',
            'description' => 'required|min:20'
        ]);

        if ($validator->fails()) {
            Response::setFlash('danger', 'Please correct the validation errors.');
            View::render('admin/packages/form', [
                'pageTitle' => 'Edit Package: ' . $existing['title'],
                'package' => array_merge($existing, $_POST),
                'errors' => $validator->errors(),
                'formAction' => "/admin/packages/{$id}/edit"
            ], 'admin');
            return;
        }

        // Upload optional replacement cover image
        $data = $_POST;
        if (!empty($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                $newCover = UploadService::uploadImage($_FILES['cover_image'], 'packages');
                if ($newCover !== null) {
                    $data['cover_image'] = $newCover;
                }
            } catch (RuntimeException $e) {
                Response::setFlash('danger', 'Image upload error: ' . $e->getMessage());
                View::render('admin/packages/form', [
                    'pageTitle' => 'Edit Package: ' . $existing['title'],
                    'package' => array_merge($existing, $_POST),
                    'errors' => ['cover_image' => [$e->getMessage()]],
                    'formAction' => "/admin/packages/{$id}/edit"
                ], 'admin');
                return;
            }
        }

        try {
            PackageService::update($id, $data);

            // Upload additional gallery images if provided
            if (!empty($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
                $galleryPaths = UploadService::uploadMultipleImages($_FILES['gallery_images'], 'packages');
                $existingCount = count(PackageService::getGalleryImages($id));
                foreach ($galleryPaths as $idx => $path) {
                    PackageService::addGalleryImage($id, $path, $data['title'], $existingCount + $idx + 1);
                }
            }

            Response::setFlash('success', "Package '" . Security::e($data['title']) . "' updated successfully.");
            Response::redirect('/admin/packages');
        } catch (RuntimeException $e) {
            Response::setFlash('danger', $e->getMessage());
            $galleryImages = PackageService::getGalleryImages($id);
            View::render('admin/packages/form', [
                'pageTitle' => 'Edit Package: ' . $existing['title'],
                'package' => array_merge($existing, $_POST),
                'galleryImages' => $galleryImages,
                'errors' => ['general' => [$e->getMessage()]],
                'formAction' => "/admin/packages/{$id}/edit"
            ], 'admin');
        }
    }

    /**
     * Delete an individual gallery image from a package (POST).
     */
    public function deletePackageImage(array $params): void
    {
        Auth::guard();
        $packageId = (int)($params['id'] ?? 0);
        $imageId = (int)($params['imageId'] ?? 0);

        $token = $_POST['_csrf_token'] ?? null;
        if (!Security::verifyCsrf($token)) {
            Response::setFlash('danger', 'Security token expired or invalid.');
            Response::redirect("/admin/packages/{$packageId}/edit");
            return;
        }

        PackageService::deleteGalleryImage($imageId);
        Response::setFlash('success', 'Gallery image removed successfully.');
        Response::redirect("/admin/packages/{$packageId}/edit");
    }

    /**
     * Quick status update action (POST).
     */
    public function updatePackageStatus(array $params): void
    {
        Auth::guard();

        $token = $_POST['_csrf_token'] ?? null;
        if (!Security::verifyCsrf($token)) {
            Response::setFlash('danger', 'Security token invalid.');
            Response::redirect('/admin/packages');
            return;
        }

        $id = (int)($params['id'] ?? 0);
        $status = $_POST['status'] ?? 'draft';

        try {
            PackageService::setStatus($id, $status);
            Response::setFlash('success', "Package status updated to {$status}.");
        } catch (RuntimeException $e) {
            Response::setFlash('danger', $e->getMessage());
        }

        Response::redirect('/admin/packages');
    }

    /**
     * Delete or archive package action (POST).
     * Enforces protection of historical bookings.
     */
    public function deletePackage(array $params): void
    {
        Auth::guard();

        $token = $_POST['_csrf_token'] ?? null;
        if (!Security::verifyCsrf($token)) {
            Response::setFlash('danger', 'Security token invalid.');
            Response::redirect('/admin/packages');
            return;
        }

        $id = (int)($params['id'] ?? 0);
        $action = $_POST['action'] ?? 'delete';

        if ($action === 'archive') {
            PackageService::setStatus($id, 'archived');
            Response::setFlash('info', 'Package archived successfully (protected from deletion).');
            Response::redirect('/admin/packages');
            return;
        }

        try {
            PackageService::delete($id);
            Response::setFlash('success', 'Package deleted successfully.');
        } catch (RuntimeException $e) {
            Response::setFlash('warning', $e->getMessage());
        }

        Response::redirect('/admin/packages');
    }

    /**
     * List all customer enquiries with status tabs and search filters.
     */
    public function enquiries(): void
    {
        Auth::guard();

        $status = $_GET['status'] ?? 'all';
        $service = $_GET['service'] ?? 'all';
        $search = $_GET['search'] ?? '';

        $enquiries = EnquiryService::getAll([
            'status' => $status,
            'service_type' => $service,
            'search' => $search
        ]);
        $counts = EnquiryService::getCounts();

        View::render('admin/enquiries/index', [
            'pageTitle' => 'Customer Enquiries & Leads',
            'enquiries' => $enquiries,
            'counts' => $counts,
            'currentStatus' => $status,
            'currentService' => $service,
            'searchQuery' => $search
        ], 'admin');
    }

    /**
     * Single enquiry detail view with CRM status, customer info, and consultation notes.
     */
    public function enquiryDetail(array $params): void
    {
        Auth::guard();

        $id = (int)($params['id'] ?? 0);
        $enquiry = EnquiryService::getById($id);

        if (!$enquiry) {
            Response::setFlash('danger', "Enquiry with ID #{$id} not found.");
            Response::redirect('/admin/enquiries');
            return;
        }

        $notes = EnquiryService::getNotes($id);

        View::render('admin/enquiries/detail', [
            'pageTitle' => 'Enquiry ' . $enquiry['reference'],
            'enquiry' => $enquiry,
            'notes' => $notes
        ], 'admin');
    }

    /**
     * Process status and follow-up date update for an enquiry.
     */
    public function updateEnquiryStatus(array $params): void
    {
        Auth::guard();

        $id = (int)($params['id'] ?? 0);
        $token = $_POST['_csrf_token'] ?? null;
        if (!Security::verifyCsrf($token)) {
            Response::setFlash('danger', 'Security validation failed.');
            Response::redirect("/admin/enquiries/{$id}");
            return;
        }

        $status = $_POST['status'] ?? 'new';
        $followUp = !empty($_POST['follow_up_date']) ? $_POST['follow_up_date'] : null;

        try {
            EnquiryService::updateStatus($id, $status, $followUp);
            Response::setFlash('success', "Enquiry status updated to '{$status}'.");
        } catch (RuntimeException $e) {
            Response::setFlash('danger', $e->getMessage());
        }

        Response::redirect("/admin/enquiries/{$id}");
    }

    /**
     * Add an internal consultation note to an enquiry.
     */
    public function addEnquiryNote(array $params): void
    {
        Auth::guard();

        $id = (int)($params['id'] ?? 0);
        $token = $_POST['_csrf_token'] ?? null;
        if (!Security::verifyCsrf($token)) {
            Response::setFlash('danger', 'Security validation failed.');
            Response::redirect("/admin/enquiries/{$id}");
            return;
        }

        $note = trim((string)($_POST['note'] ?? ''));
        $adminId = Auth::id() ?? 1;

        try {
            EnquiryService::addNote($id, $adminId, $note);
            Response::setFlash('success', "Consultation note added successfully.");
        } catch (RuntimeException $e) {
            Response::setFlash('danger', $e->getMessage());
        }

        Response::redirect("/admin/enquiries/{$id}");
    }

    /**
     * Admin Bookings directory.
     */
    public function bookings(): void
    {
        Auth::guard();

        $filters = [
            'status' => $_GET['status'] ?? '',
            'search' => $_GET['search'] ?? '',
            'limit'  => 100
        ];

        $bookings = BookingService::listBookings($filters);
        $counts = BookingService::getCounts();

        View::render('admin/bookings/index', [
            'pageTitle' => 'Package Bookings',
            'bookings'  => $bookings,
            'counts'    => $counts,
            'filters'   => $filters
        ], 'admin');
    }

    /**
     * Admin Single Booking detail.
     */
    public function bookingDetail(array $params): void
    {
        Auth::guard();

        $id = (int)($params['id'] ?? 0);
        $booking = BookingService::getBookingById($id);

        if (!$booking) {
            Response::setFlash('danger', 'Booking record not found.');
            Response::redirect('/admin/bookings');
            return;
        }

        View::render('admin/bookings/detail', [
            'pageTitle' => 'Booking Details: ' . Security::e($booking['reference']),
            'booking'   => $booking,
            'payments'  => $booking['payments'] ?? []
        ], 'admin');
    }

    /**
     * Update booking travel status (pending / confirmed / cancelled).
     */
    public function updateBookingStatus(array $params): void
    {
        Auth::guard();

        $id = (int)($params['id'] ?? 0);
        $token = $_POST['_csrf_token'] ?? null;
        if (!Security::verifyCsrf($token)) {
            Response::setFlash('danger', 'Security validation failed.');
            Response::redirect("/admin/bookings/{$id}");
            return;
        }

        $newStatus = trim((string)($_POST['booking_status'] ?? 'pending'));
        $notes = trim((string)($_POST['admin_notes'] ?? ''));
        $adminId = Auth::id() ?? 1;

        try {
            BookingService::adminUpdateBookingStatus($id, $adminId, $newStatus, $notes);
            Response::setFlash('success', "Booking travel status updated to '{$newStatus}'.");
        } catch (RuntimeException $e) {
            Response::setFlash('danger', $e->getMessage());
        }

        Response::redirect("/admin/bookings/{$id}");
    }

    /**
     * Admin Payments reconciliation directory.
     */
    public function payments(): void
    {
        Auth::guard();

        $filters = [
            'provider' => $_GET['provider'] ?? '',
            'status'   => $_GET['status'] ?? '',
            'search'   => $_GET['search'] ?? '',
            'limit'    => 100
        ];

        $payments = BookingService::listPayments($filters);
        $counts = BookingService::getCounts();

        View::render('admin/payments/index', [
            'pageTitle' => 'Payments & Reconciliation',
            'payments'  => $payments,
            'counts'    => $counts,
            'filters'   => $filters
        ], 'admin');
    }

    /**
     * Admin verifies a manual bank transfer after external bank statement check.
     */
    public function verifyManualPayment(array $params): void
    {
        Auth::guard();

        $id = (int)($params['id'] ?? 0);
        $token = $_POST['_csrf_token'] ?? null;
        if (!Security::verifyCsrf($token)) {
            Response::setFlash('danger', 'Security validation failed.');
            Response::redirect('/admin/payments');
            return;
        }

        $auditNotes = trim((string)($_POST['audit_notes'] ?? ''));
        $adminId = Auth::id() ?? 1;

        try {
            BookingService::adminVerifyManualPayment($id, $adminId, $auditNotes);
            Response::setFlash('success', "Manual payment #{$id} has been verified and marked as PAID. Official receipt issued.");
        } catch (RuntimeException $e) {
            Response::setFlash('danger', $e->getMessage());
        }

        $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : '/admin/payments';
        Response::redirect($redirect);
    }

    /**
     * Admin rejects an invalid manual bank transfer.
     */
    public function rejectManualPayment(array $params): void
    {
        Auth::guard();

        $id = (int)($params['id'] ?? 0);
        $token = $_POST['_csrf_token'] ?? null;
        if (!Security::verifyCsrf($token)) {
            Response::setFlash('danger', 'Security validation failed.');
            Response::redirect('/admin/payments');
            return;
        }

        $reason = trim((string)($_POST['reject_reason'] ?? ''));
        $adminId = Auth::id() ?? 1;

        try {
            BookingService::adminRejectManualPayment($id, $adminId, $reason);
            Response::setFlash('warning', "Manual payment #{$id} has been marked as REJECTED.");
        } catch (RuntimeException $e) {
            Response::setFlash('danger', $e->getMessage());
        }

        $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : '/admin/payments';
        Response::redirect($redirect);
    }

    /**
     * Admin Settings management view (Phase P08).
     */
    public function settings(): void
    {
        Auth::guard();

        $settings = SettingService::getAll();
        $company = SettingService::getCompanyDetails();
        $social = SettingService::getSocialLinks();
        $emailRouting = SettingService::getEmailRouting();
        $bank = SettingService::getBankDetails();
        $paystack = [
            'public_key'   => PaystackService::getPublicKey(),
            'secret_key'   => PaystackService::getSecretKey(),
            'currency'     => (string)Env::get('PAYSTACK_CURRENCY', 'NGN'),
            'is_live'      => PaystackService::isLiveMode(),
            'is_test'      => PaystackService::isTestMode(),
            'is_sandbox'   => PaystackService::isPlaceholderKey(),
            'webhook_url'  => rtrim((string)Env::get('APP_URL', 'http://localhost:8000'), '/') . '/paystack/webhook',
            'callback_url' => rtrim((string)Env::get('APP_URL', 'http://localhost:8000'), '/') . '/paystack/callback',
        ];

        View::render('admin/settings/index', [
            'pageTitle'      => 'Business & Integration Settings',
            'settings'       => $settings,
            'company'        => $company,
            'social'         => $social,
            'emailRouting'   => $emailRouting,
            'bank'           => $bank,
            'paystack'       => $paystack,
            'paymentMethods' => SettingService::getPaymentMethodSettings(),
        ], 'admin');
    }

    /**
     * Update Business Settings (Phase P08).
     */
    public function updateSettings(): void
    {
        Auth::guard();

        $token = $_POST['_csrf_token'] ?? null;
        if (!Security::verifyCsrf($token)) {
            Response::setFlash('danger', 'Security validation failed.');
            Response::redirect('/admin/settings');
            return;
        }

        $section = trim((string)($_POST['section'] ?? 'all'));
        $adminId = Auth::id() ?? 1;

        try {
            if ($section === 'company' || $section === 'all') {
                $companyData = [
                    'company_name'     => trim((string)($_POST['company_name'] ?? '')),
                    'company_tagline'  => trim((string)($_POST['company_tagline'] ?? '')),
                    'contact_phone'    => trim((string)($_POST['contact_phone'] ?? '')),
                    'contact_whatsapp' => trim((string)($_POST['contact_whatsapp'] ?? '')),
                    'office_address'   => trim((string)($_POST['office_address'] ?? '')),
                    'office_hours'     => trim((string)($_POST['office_hours'] ?? '')),
                ];

                if ($companyData['company_name'] === '') {
                    throw new RuntimeException("Company name cannot be empty.");
                }
                if ($companyData['contact_phone'] === '') {
                    throw new RuntimeException("Contact phone cannot be empty.");
                }

                SettingService::setMultiple($companyData);
            }

            if ($section === 'social' || $section === 'all') {
                $insta = trim((string)($_POST['social_instagram'] ?? ''));
                $fb = trim((string)($_POST['social_facebook'] ?? ''));
                $li = trim((string)($_POST['social_linkedin'] ?? ''));

                if ($insta !== '' && !filter_var($insta, FILTER_VALIDATE_URL)) {
                    throw new RuntimeException("Instagram URL must be a valid web address (e.g. https://instagram.com/yourhandle).");
                }
                if ($fb !== '' && !filter_var($fb, FILTER_VALIDATE_URL)) {
                    throw new RuntimeException("Facebook URL must be a valid web address (e.g. https://facebook.com/yourhandle).");
                }
                if ($li !== '' && !filter_var($li, FILTER_VALIDATE_URL)) {
                    throw new RuntimeException("LinkedIn URL must be a valid web address.");
                }

                SettingService::setMultiple([
                    'social_instagram' => $insta,
                    'social_facebook'  => $fb,
                    'social_linkedin'  => $li,
                ]);
            }

            if ($section === 'email' || $section === 'all') {
                $fromAddress = trim((string)($_POST['mail_from_address'] ?? ''));
                $fromName    = trim((string)($_POST['mail_from_name'] ?? ''));
                $enqEmail    = trim((string)($_POST['notification_email_enquiries'] ?? ''));
                $visaEmail   = trim((string)($_POST['notification_email_visa'] ?? ''));
                $payEmail    = trim((string)($_POST['notification_email_payments'] ?? ''));
                $mgmtEmail   = trim((string)($_POST['notification_email_management'] ?? ''));

                if ($fromAddress !== '' && !filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
                    throw new RuntimeException("Default sender email must be a valid address.");
                }
                if ($enqEmail !== '' && !filter_var($enqEmail, FILTER_VALIDATE_EMAIL)) {
                    throw new RuntimeException("Enquiry notification email must be a valid address.");
                }
                if ($visaEmail !== '' && !filter_var($visaEmail, FILTER_VALIDATE_EMAIL)) {
                    throw new RuntimeException("Visa notification email must be a valid address.");
                }
                if ($payEmail !== '' && !filter_var($payEmail, FILTER_VALIDATE_EMAIL)) {
                    throw new RuntimeException("Payment notification email must be a valid address.");
                }
                if ($mgmtEmail !== '' && !filter_var($mgmtEmail, FILTER_VALIDATE_EMAIL)) {
                    throw new RuntimeException("Management notification email must be a valid address.");
                }

                SettingService::setMultiple([
                    'mail_from_address'             => $fromAddress,
                    'mail_from_name'                => $fromName,
                    'notification_email_enquiries'  => $enqEmail,
                    'notification_email_visa'       => $visaEmail,
                    'notification_email_payments'   => $payEmail,
                    'notification_email_management' => $mgmtEmail,
                ]);
            }

            if ($section === 'payment_methods' || $section === 'all') {
                $bankEnabled = !empty($_POST['payment_method_bank_enabled']) ? '1' : '0';
                $paystackEnabled = !empty($_POST['payment_method_paystack_enabled']) ? '1' : '0';

                SettingService::setMultiple([
                    'payment_method_bank_enabled'     => $bankEnabled,
                    'payment_method_paystack_enabled' => $paystackEnabled,
                ]);
            }

            if ($section === 'bank') {
                $bankData = [
                    'bank_name'      => trim((string)($_POST['bank_name'] ?? '')),
                    'account_name'   => trim((string)($_POST['account_name'] ?? '')),
                    'account_number' => trim((string)($_POST['account_number'] ?? '')),
                    'sort_code'      => trim((string)($_POST['sort_code'] ?? '')),
                    'instructions'   => trim((string)($_POST['instructions'] ?? '')),
                ];

                $confirmed = !empty($_POST['client_confirmed']);
                SettingService::updateBankDetails($bankData, $adminId, $confirmed);

                if (isset($_POST['payment_method_bank_toggle_submitted'])) {
                    SettingService::set('payment_method_bank_enabled', !empty($_POST['payment_method_bank_enabled']) ? '1' : '0');
                }
            }

            if ($section === 'paystack') {
                $pubKey = trim((string)($_POST['paystack_public_key'] ?? ''));
                $secKey = trim((string)($_POST['paystack_secret_key'] ?? ''));
                $currency = strtoupper(trim((string)($_POST['paystack_currency'] ?? 'NGN')));

                if (!empty($pubKey) && !str_starts_with($pubKey, 'pk_')) {
                    throw new RuntimeException("Paystack public key must begin with 'pk_test_' or 'pk_live_'.");
                }
                if (!empty($secKey) && !str_starts_with($secKey, 'sk_')) {
                    throw new RuntimeException("Paystack secret key must begin with 'sk_test_' or 'sk_live_'.");
                }

                $updates = [];
                if ($pubKey !== '') {
                    $updates['PAYSTACK_PUBLIC_KEY'] = $pubKey;
                }
                if ($secKey !== '') {
                    $updates['PAYSTACK_SECRET_KEY'] = $secKey;
                }
                if ($currency !== '') {
                    $updates['PAYSTACK_CURRENCY'] = $currency;
                }

                if (!empty($updates)) {
                    \App\Core\Env::writeToEnvFile($updates);
                }

                if (isset($_POST['payment_method_paystack_toggle_submitted'])) {
                    SettingService::set('payment_method_paystack_enabled', !empty($_POST['payment_method_paystack_enabled']) ? '1' : '0');
                }
            }

            Response::setFlash('success', 'Settings updated successfully.');
        } catch (Throwable $e) {
            Response::setFlash('danger', $e->getMessage());
        }

        Response::redirect('/admin/settings' . ($section !== 'all' ? '#' . $section : ''));
    }
}

