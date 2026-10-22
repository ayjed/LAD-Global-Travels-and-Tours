<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Env;
use App\Core\RateLimiter;
use App\Core\Response;
use App\Core\Security;
use App\Core\Validator;
use App\Core\View;
use App\Services\BookingService;
use App\Services\MailService;
use App\Services\PackageService;
use App\Services\PaystackService;
use App\Services\SettingService;
use RuntimeException;
use Throwable;

/**
 * BookingController
 * 
 * Handles public guest booking requests, server-side price validation,
 * manual bank transfer instructions, Paystack checkout initialization,
 * independent verification callbacks, and HMAC webhooks.
 */
class BookingController
{
    /**
     * Display the package booking checkout form.
     */
    public function showBookingForm(array $params): void
    {
        $slug = trim((string)($params['slug'] ?? ''));
        $package = PackageService::getBySlug($slug);

        if (!$package) {
            View::render('pages/404', ['pageTitle' => 'Tour Package Not Found']);
            return;
        }

        // Strict Eligibility Invariant: Must be published, payment enabled, and price > 0
        if (!BookingService::isPackagePayable($package)) {
            Response::flash('warning', 'Online direct booking is not enabled for this package. Please use our consultation enquiry form.');
            Response::redirect("/packages/{$slug}");
            return;
        }

        $bankDetails = BookingService::getBankDetails();
        $paystackConfigured = PaystackService::isConfigured();

        $bankEnabled = SettingService::isBankTransferEnabled();
        $paystackEnabled = SettingService::isPaystackEnabled();
        $enabledMethods = SettingService::getEnabledPaymentMethods();

        $requestedMethod = trim((string)($_GET['method'] ?? ''));
        $defaultOld = [];
        if (in_array($requestedMethod, $enabledMethods, true)) {
            $defaultOld['payment_method'] = $requestedMethod;
        } elseif (count($enabledMethods) === 1) {
            $defaultOld['payment_method'] = $enabledMethods[0];
        } elseif (in_array('manual', $enabledMethods, true)) {
            $defaultOld['payment_method'] = 'manual';
        } elseif (!empty($enabledMethods)) {
            $defaultOld['payment_method'] = $enabledMethods[0];
        }

        View::render('pages/booking-form', [
            'pageTitle'          => 'Book Tour: ' . Security::e($package['title']),
            'package'            => $package,
            'bankDetails'        => $bankDetails,
            'paystackConfigured' => $paystackConfigured,
            'paystackPublicKey'  => PaystackService::getPublicKey(),
            'defaultTravellers'  => 1,
            'old'                => $defaultOld,
            'bankEnabled'        => $bankEnabled,
            'paystackEnabled'    => $paystackEnabled,
            'enabledMethods'     => $enabledMethods,
        ]);
    }

    /**
     * Process booking form submission.
     */
    public function submitBooking(array $params): void
    {
        $slug = trim((string)($params['slug'] ?? ''));
        $package = PackageService::getBySlug($slug);

        if (!$package || !BookingService::isPackagePayable($package)) {
            Response::flash('danger', 'This package is not currently available for direct online booking.');
            Response::redirect("/packages/{$slug}");
            return;
        }

        // 1. Verify CSRF Token
        $token = $_POST['_csrf_token'] ?? null;
        if (!Security::verifyCsrf($token)) {
            Response::flash('danger', 'Security validation expired or invalid. Please resubmit the booking form.');
            Response::redirect("/packages/{$slug}/book");
            return;
        }

        // 2. Anti-spam honeypot check
        if (!empty($_POST['website_url_hp'])) {
            // Silently abort automated bot submission
            Response::redirect('/');
            return;
        }

        // 3. Rate limiting (max 5 booking submissions per 10 minutes per IP)
        $clientIp = Security::getClientIp();
        $rateLimitKey = 'booking_submit_' . $clientIp;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5, 600)) {
            $retryMinutes = max(1, (int)ceil(RateLimiter::availableIn($rateLimitKey) / 60));
            Response::flash('danger', "Too many booking attempts. Please wait {$retryMinutes} minute(s) or reach us on WhatsApp.");
            Response::redirect("/packages/{$slug}/book");
            return;
        }

        // 4. Server-Side Input Validation
        $enabledMethods = SettingService::getEnabledPaymentMethods();
        if (empty($enabledMethods)) {
            Response::flash('danger', 'Online checkout is currently disabled. Please contact our reservation desk directly.');
            Response::redirect("/packages/{$slug}/book");
            return;
        }

        $validator = Validator::make($_POST, [
            'customer_name'    => 'required|min:3|max:150',
            'customer_email'   => 'required|email|max:191',
            'customer_phone'   => 'required|min:7|max:50',
            'travellers_count' => 'required|numeric|min:1|max:20',
            'payment_method'   => 'required|in:' . implode(',', $enabledMethods),
            'terms_consent'    => 'required'
        ]);

        if ($validator->fails()) {
            Response::flash('danger', 'Please correct the highlighted form errors to proceed.');
            View::render('pages/booking-form', [
                'pageTitle'          => 'Book Tour: ' . Security::e($package['title']),
                'package'            => $package,
                'bankDetails'        => BookingService::getBankDetails(),
                'paystackConfigured' => PaystackService::isConfigured(),
                'paystackPublicKey'  => PaystackService::getPublicKey(),
                'errors'             => $validator->errors(),
                'old'                => $_POST,
                'defaultTravellers'  => (int)($_POST['travellers_count'] ?? 1),
                'bankEnabled'        => SettingService::isBankTransferEnabled(),
                'paystackEnabled'    => SettingService::isPaystackEnabled(),
                'enabledMethods'     => $enabledMethods,
            ]);
            return;
        }

        RateLimiter::hit($rateLimitKey, 600);

        $paymentMethod = $_POST['payment_method'];

        try {
            // 5. Create Booking & Payment Attempt (Server-Authoritative Calculation)
            $result = BookingService::createBooking($package, [
                'customer_name'    => $_POST['customer_name'],
                'customer_email'   => $_POST['customer_email'],
                'customer_phone'   => $_POST['customer_phone'],
                'travellers_count' => (int)$_POST['travellers_count']
            ], $paymentMethod);

            $booking = $result['booking'];
            $payment = $result['payment'];

            // 6A. Handle Manual Bank Transfer
            if ($paymentMethod === 'manual') {
                $bankDetails = BookingService::getBankDetails();
                try {
                    MailService::sendBankTransferInstructions($booking, $payment, $package, $bankDetails);
                } catch (Throwable $e) {
                    // Gracefully shielded
                }

                Response::flash('success', "Booking initiated successfully! Please follow the bank transfer instructions below.");
                Response::redirect("/booking/{$booking['reference']}/transfer");
                return;
            }

            // 6B. Handle Paystack Online Checkout
            if ($paymentMethod === 'paystack') {
                $appUrl = (string)Env::get('APP_URL', 'http://localhost:8000');
                $callbackUrl = rtrim($appUrl, '/') . '/paystack/callback';

                $initRes = PaystackService::initializeTransaction($payment, $booking, $package, $callbackUrl);

                if ($initRes['success'] && !empty($initRes['authorization_url'])) {
                    // Redirect browser to Paystack hosted checkout
                    Response::redirect($initRes['authorization_url']);
                    return;
                }

                // If Paystack initialization failed (e.g. offline API or test key issue), fall back to manual transfer
                Response::flash('warning', "Online checkout service notice: {$initRes['message']} You may complete your booking using Bank Transfer below.");
                Response::redirect("/booking/{$booking['reference']}/transfer");
                return;
            }

        } catch (RuntimeException $e) {
            Response::flash('danger', $e->getMessage());
            Response::redirect("/packages/{$slug}/book");
        }
    }

    /**
     * Display manual bank transfer instructions for a booking.
     */
    public function showTransferInstructions(array $params): void
    {
        $ref = trim((string)($params['reference'] ?? ''));
        $booking = BookingService::getBookingByReference($ref);

        if (!$booking) {
            View::render('pages/404', ['pageTitle' => 'Booking Not Found']);
            return;
        }

        $bankDetails = BookingService::getBankDetails();
        $payment = $booking['latest_payment'] ?? null;

        View::render('pages/booking-transfer', [
            'pageTitle'   => 'Bank Transfer Instructions: ' . Security::e($booking['reference']),
            'booking'     => $booking,
            'payment'     => $payment,
            'bankDetails' => $bankDetails
        ]);
    }

    /**
     * Customer records their bank transfer narration or memo.
     */
    public function submitTransferMemo(array $params): void
    {
        $ref = trim((string)($params['reference'] ?? ''));
        $booking = BookingService::getBookingByReference($ref);

        if (!$booking) {
            Response::redirect('/');
            return;
        }

        $token = $_POST['_csrf_token'] ?? null;
        if (!Security::verifyCsrf($token)) {
            Response::flash('danger', 'Security token expired.');
            Response::redirect("/booking/{$ref}/transfer");
            return;
        }

        $memo = trim((string)($_POST['transfer_memo'] ?? ''));
        $paymentId = (int)($booking['latest_payment']['id'] ?? 0);

        if ($paymentId > 0 && $memo !== '') {
            BookingService::recordCustomerTransferMemo($paymentId, $memo);
            Response::flash('success', 'Your payment narration has been noted for our finance reconciliation team. Thank you!');
        }

        Response::redirect("/booking/{$ref}/transfer");
    }

    /**
     * Paystack Developer Sandbox Screen (active when placeholder keys are used).
     */
    public function paystackSandbox(array $params): void
    {
        $ref = trim((string)($params['reference'] ?? ''));
        $payment = BookingService::getPaymentByReference($ref);

        if (!$payment) {
            Response::flash('danger', 'Payment record not found.');
            Response::redirect('/packages');
            return;
        }

        $booking = BookingService::getBookingById((int)$payment['booking_id']);
        if (!$booking) {
            Response::redirect('/');
            return;
        }

        $package = PackageService::getById((int)$booking['package_id']) ?? [
            'title' => 'Tour Package',
            'destination_country' => 'Global'
        ];

        View::render('pages/paystack-sandbox', [
            'pageTitle' => 'Paystack Sandbox Checkout',
            'booking'   => $booking,
            'payment'   => $payment,
            'package'   => $package,
        ]);
    }

    /**
     * Paystack Browser Callback Handler.
     * CRITICAL: Never trusts browser redirects; always performs independent server-to-server verification.
     */
    public function paystackCallback(): void
    {
        $paymentRef = trim((string)($_GET['reference'] ?? $_GET['trxref'] ?? ''));

        if ($paymentRef === '') {
            Response::flash('danger', 'Payment reference missing from gateway callback.');
            Response::redirect('/packages');
            return;
        }

        $payment = BookingService::getPaymentByReference($paymentRef);
        if (!$payment) {
            Response::flash('danger', 'Payment attempt record not found.');
            Response::redirect('/packages');
            return;
        }

        $booking = BookingService::getBookingById((int)$payment['booking_id']);
        if (!$booking) {
            Response::redirect('/');
            return;
        }

        // 1. Independent Server-to-Server Verification
        $verifyRes = PaystackService::verifyTransaction($paymentRef);

        if (!$verifyRes['success']) {
            Response::flash('warning', "Payment verification notice: {$verifyRes['message']}. If your account was debited, our finance team will verify the payment shortly.");
            Response::redirect("/booking/{$booking['reference']}");
            return;
        }

        $gatewayData = $verifyRes['data'] ?? [];

        // 2. Atomic Settlement with Idempotency Protection
        $settleRes = PaystackService::settlePayment($paymentRef, $gatewayData, json_encode($gatewayData));

        if ($settleRes['success']) {
            Response::flash('success', 'Payment successfully verified and confirmed! Your official payment receipt is shown below.');
            Response::redirect("/booking/{$booking['reference']}/receipt");
            return;
        }

        Response::flash('danger', 'Payment settlement error: ' . ($settleRes['error'] ?? 'Could not reconcile payment.'));
        Response::redirect("/booking/{$booking['reference']}");
    }

    /**
     * Paystack Webhook Receiver.
     * Verifies HMAC SHA512 signature, deduplicates via payment_events, and settles transaction atomically.
     */
    public function paystackWebhook(): void
    {
        // 1. Read raw body & signature header
        $rawBody = (string)file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';

        // 2. HMAC SHA512 Signature Check
        if (!PaystackService::verifyWebhookSignature($rawBody, $signature)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['status' => false, 'message' => 'Invalid HMAC signature']);
            exit;
        }

        $payload = json_decode($rawBody, true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(['status' => false, 'message' => 'Malformed JSON payload']);
            exit;
        }

        $event = $payload['event'] ?? '';
        $data = $payload['data'] ?? [];
        $paymentRef = (string)($data['reference'] ?? '');
        $eventId = 'evt_' . ($data['id'] ?? $paymentRef ?? bin2hex(random_bytes(6)));

        // 3. Process charge.success event
        if ($event === 'charge.success' && !empty($paymentRef)) {
            $settleRes = PaystackService::settlePayment($paymentRef, $data, $rawBody, $eventId);
        }

        // Always acknowledge Paystack with 200 OK
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['status' => true, 'message' => 'Webhook received']);
        exit;
    }

    /**
     * Display official payment receipt.
     * Strictly separates payment receipt from travel confirmation (D11).
     */
    public function showReceipt(array $params): void
    {
        $ref = trim((string)($params['reference'] ?? ''));
        $booking = BookingService::getBookingByReference($ref);

        if (!$booking) {
            View::render('pages/404', ['pageTitle' => 'Booking Not Found']);
            return;
        }

        $payment = $booking['latest_payment'] ?? null;

        // If not yet marked paid, redirect to status view
        if (!$payment || $payment['status'] !== 'paid') {
            Response::redirect("/booking/{$ref}");
            return;
        }

        View::render('pages/booking-receipt', [
            'pageTitle' => 'Payment Receipt: ' . Security::e($booking['reference']),
            'booking'   => $booking,
            'payment'   => $payment
        ]);
    }

    /**
     * Display general booking status view.
     */
    public function showBookingStatus(array $params): void
    {
        $ref = trim((string)($params['reference'] ?? ''));
        $booking = BookingService::getBookingByReference($ref);

        if (!$booking) {
            View::render('pages/404', ['pageTitle' => 'Booking Not Found']);
            return;
        }

        View::render('pages/booking-status', [
            'pageTitle' => 'Booking Status: ' . Security::e($booking['reference']),
            'booking'   => $booking,
            'payments'  => $booking['payments'] ?? []
        ]);
    }
}
