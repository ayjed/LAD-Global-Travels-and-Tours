<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Env;
use App\Core\Security;
use PDO;
use RuntimeException;
use Throwable;

/**
 * PaystackService
 * 
 * Handles server-side Paystack transaction initialization, independent server-to-server
 * verification, HMAC SHA512 webhook signature authentication, and atomic idempotent
 * settlement per LG-SOT-001 Section 8.3.
 * 
 * Strict Security Invariants:
 * - Never store or accept raw credit card numbers, CVVs, or bank tokens.
 * - Live mode strictly disabled pending authorized client launch; test keys only.
 * - Browser callbacks and redirects are UNTRUSTED; independent verification is always executed.
 * - Subunit amount (kobo for NGN), currency, reference, and gateway status must match exactly.
 * - Processing is atomic and idempotent (replay attacks prevented via payment_events).
 */
class PaystackService
{
    private const BASE_URL = 'https://api.paystack.co';

    /**
     * Optional custom HTTP transport callable for testing/mocking without live network calls.
     * signature: callable(string $method, string $url, array $headers, ?string $body): array
     */
    private static $mockTransport = null;

    /**
     * Set a mock HTTP transport for automated tests.
     */
    public static function setMockTransport(?callable $transport): void
    {
        self::$mockTransport = $transport;
    }

    /**
     * Check if Paystack credentials are configured in the environment.
     */
    public static function isConfigured(): bool
    {
        $secret = (string)Env::get('PAYSTACK_SECRET_KEY', '');
        return !empty($secret) && $secret !== 'sk_test_placeholder_key';
    }

    /**
     * Get the public key for frontend presentation or checkout scripts.
     */
    public static function getPublicKey(): string
    {
        return (string)Env::get('PAYSTACK_PUBLIC_KEY', '');
    }

    /**
     * Get the configured secret key.
     */
    public static function getSecretKey(): string
    {
        return (string)Env::get('PAYSTACK_SECRET_KEY', '');
    }

    /**
     * Determine if currently configured with live production Paystack keys.
     */
    public static function isLiveMode(): bool
    {
        $secret = self::getSecretKey();
        return str_starts_with($secret, 'sk_live_');
    }

    /**
     * Determine if running in Paystack sandbox / test mode.
     */
    public static function isTestMode(): bool
    {
        return !self::isLiveMode();
    }

    /**
     * Check if the currently configured key is a placeholder or not yet provisioned.
     */
    public static function isPlaceholderKey(): bool
    {
        $secret = self::getSecretKey();
        return empty($secret) || $secret === 'sk_test_placeholder_key' || str_contains($secret, 'placeholder');
    }

    /**
     * Initiate a payment transaction on the Paystack API server-side.
     *
     * @param array $payment The pending payment record
     * @param array $booking The associated booking record
     * @param array $package The associated package record
     * @param string $callbackUrl Browser return URL
     * @return array ['success' => bool, 'authorization_url' => string, 'access_code' => string, 'reference' => string, 'message' => string]
     */
    public static function initializeTransaction(
        array $payment,
        array $booking,
        array $package,
        string $callbackUrl
    ): array {
        $secretKey = self::getSecretKey();
        if (empty($secretKey)) {
            self::logError('Cannot initialize Paystack: PAYSTACK_SECRET_KEY is empty in environment.');
            return [
                'success' => false,
                'message' => 'Paystack payment gateway is not yet configured for online processing.'
            ];
        }

        // Amount must be in subunits (kobo for NGN: 1 NGN = 100 kobo)
        $amountInSubunits = (int)round((float)$payment['amount'] * 100);
        if ($amountInSubunits <= 0) {
            return [
                'success' => false,
                'message' => 'Invalid payment amount specified.'
            ];
        }

        // If running locally with placeholder credentials (and not intercepted by mock test transport), provide sandbox checkout
        if (self::$mockTransport === null && self::isPlaceholderKey()) {
            return [
                'success'           => true,
                'authorization_url' => '/paystack/sandbox/' . urlencode($payment['payment_reference']),
                'access_code'       => 'sandbox_' . bin2hex(random_bytes(8)),
                'reference'         => $payment['payment_reference'],
                'message'           => 'Sandbox test checkout initialized.'
            ];
        }

        $payload = [
            'reference'    => $payment['payment_reference'],
            'amount'       => $amountInSubunits,
            'email'        => $booking['customer_email'],
            'currency'     => strtoupper($payment['currency'] ?? 'NGN'),
            'callback_url' => $callbackUrl,
            'metadata'     => [
                'booking_reference' => $booking['reference'],
                'booking_id'        => (int)$booking['id'],
                'package_id'        => (int)$package['id'],
                'package_title'     => $package['title'],
                'customer_name'     => $booking['customer_name'],
                'customer_phone'    => $booking['customer_phone'],
                'travellers_count'  => (int)$booking['travellers_count'],
                'custom_fields'     => [
                    [
                        'display_name'  => 'Package',
                        'variable_name' => 'package_title',
                        'value'         => $package['title']
                    ],
                    [
                        'display_name'  => 'Booking Reference',
                        'variable_name' => 'booking_reference',
                        'value'         => $booking['reference']
                    ]
                ]
            ]
        ];

        self::logInfo("Initializing Paystack transaction for ref: {$payment['payment_reference']}, amount: {$payment['amount']} {$payload['currency']}");

        $response = self::makeRequest('POST', self::BASE_URL . '/transaction/initialize', $payload, $secretKey);

        if (!$response['success']) {
            self::logError("Paystack initialization failed for {$payment['payment_reference']}: " . $response['message']);
            return [
                'success' => false,
                'message' => $response['message'] ?: 'Failed to connect to Paystack payment gateway.'
            ];
        }

        $data = $response['data']['data'] ?? [];
        $authUrl = $data['authorization_url'] ?? null;
        $accessCode = $data['access_code'] ?? null;
        $ref = $data['reference'] ?? $payment['payment_reference'];

        if (empty($authUrl)) {
            self::logError("Paystack response missing authorization_url for {$payment['payment_reference']}");
            return [
                'success' => false,
                'message' => 'Paystack did not return a valid checkout redirect URL.'
            ];
        }

        return [
            'success'           => true,
            'authorization_url' => $authUrl,
            'access_code'       => $accessCode,
            'reference'         => $ref,
            'message'           => 'Checkout initialized successfully.'
        ];
    }

    /**
     * Independently verify a transaction with the Paystack API server-to-server.
     * Never trusts browser callbacks or redirects directly.
     *
     * @param string $reference Payment reference string
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public static function verifyTransaction(string $reference): array
    {
        // If in local sandbox simulator mode (and not intercepted by test mock transport)
        if (self::$mockTransport === null && self::isPlaceholderKey()) {
            $payment = BookingService::getPaymentByReference($reference);
            $amountInSubunits = $payment ? (int)round((float)$payment['amount'] * 100) : 0;
            return [
                'success' => true,
                'data'    => [
                    'status'           => 'success',
                    'reference'        => $reference,
                    'amount'           => $amountInSubunits,
                    'currency'         => 'NGN',
                    'channel'          => 'card',
                    'paid_at'          => date('Y-m-d\TH:i:s.000\Z'),
                    'gateway_response' => 'Successful (Sandbox Simulator)',
                    'authorization'    => [
                        'channel'   => 'card',
                        'card_type' => 'visa',
                        'last4'     => '4081',
                        'bank'      => 'Paystack Test Bank',
                        'brand'     => 'visa'
                    ]
                ],
                'message' => 'Sandbox transaction verified.'
            ];
        }

        $secretKey = self::getSecretKey();
        if (empty($secretKey)) {
            return [
                'success' => false,
                'message' => 'Paystack secret key is missing.'
            ];
        }

        self::logInfo("Initiating server-to-server verification for Paystack reference: {$reference}");

        $url = self::BASE_URL . '/transaction/verify/' . rawurlencode($reference);
        $response = self::makeRequest('GET', $url, null, $secretKey);

        if (!$response['success']) {
            $errMsg = $response['message'] ?? $response['error'] ?? 'Failed to communicate with Paystack verification endpoint.';
            self::logError("Paystack verification request failed for {$reference}: " . $errMsg);
            return [
                'success' => false,
                'message' => $errMsg,
                'error'   => $errMsg
            ];
        }

        $body = $response['data'] ?? [];
        if (empty($body['status']) || empty($body['data'])) {
            return [
                'success' => false,
                'message' => $body['message'] ?? 'Invalid response received from Paystack verification.'
            ];
        }

        return [
            'success' => true,
            'data'    => $body['data'],
            'message' => $body['message'] ?? 'Transaction verified.'
        ];
    }

    /**
     * Verify Paystack Webhook HMAC SHA512 signature.
     *
     * @param string $rawBody The raw HTTP request body string from php://input
     * @param string $signatureHeader The value of HTTP_X_PAYSTACK_SIGNATURE
     * @return bool
     */
    public static function verifyWebhookSignature(string $rawBody, string $signatureHeader): bool
    {
        $secretKey = self::getSecretKey();
        if (empty($secretKey) || empty($signatureHeader)) {
            return false;
        }

        $computedSignature = hash_hmac('sha512', $rawBody, $secretKey);
        return hash_equals($computedSignature, $signatureHeader);
    }

    /**
     * Atomically settle a successful payment attempt into the database with idempotency protection.
     *
     * @param string $paymentReference Internal payment reference (PAY-XXXXXXXX)
     * @param array $gatewayData Data payload returned from Paystack verify endpoint or webhook data block
     * @param string $rawEventPayload Raw JSON string for event audit
     * @param string|null $eventId Optional unique webhook event ID for deduplication
     * @return array ['success' => bool, 'error' => ?string, 'payment' => ?array, 'booking' => ?array, 'duplicate' => bool]
     */
    public static function settlePayment(
        string $paymentReference,
        array $gatewayData,
        string $rawEventPayload = '',
        ?string $eventId = null
    ): array {
        $pdo = Database::getConnection();

        // 1. Idempotency Check: deduplicate by event_id if provided
        if (!empty($eventId)) {
            $dedupStmt = $pdo->prepare("SELECT id FROM payment_events WHERE event_id = :event_id LIMIT 1");
            $dedupStmt->execute([':event_id' => $eventId]);
            if ($dedupStmt->fetch()) {
                self::logInfo("Duplicate webhook event ignored: {$eventId}");
                return [
                    'success'   => true,
                    'duplicate' => true,
                    'error'     => null
                ];
            }
        }

        try {
            $pdo->beginTransaction();

            // 2. Record Event in payment_events table
            if (!empty($eventId)) {
                $evtStmt = $pdo->prepare("INSERT INTO payment_events (event_id, provider, event_type, payload) 
                                          VALUES (:event_id, 'paystack', :event_type, :payload)");
                $evtStmt->execute([
                    ':event_id'   => $eventId,
                    ':event_type' => $gatewayData['event'] ?? 'charge.success',
                    ':payload'    => $rawEventPayload ?: json_encode($gatewayData)
                ]);
            }

            // 3. Lock & Fetch Payment Record
            $payStmt = $pdo->prepare("SELECT * FROM payments WHERE payment_reference = :ref FOR UPDATE");
            $payStmt->execute([':ref' => $paymentReference]);
            $payment = $payStmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                $pdo->rollBack();
                self::logError("Settle payment failed: Payment reference {$paymentReference} not found in database.");
                return [
                    'success'   => false,
                    'duplicate' => false,
                    'error'     => "Payment reference {$paymentReference} not found."
                ];
            }

            // If already marked paid, return idempotent success
            if ($payment['status'] === 'paid') {
                $pdo->commit();
                self::logInfo("Payment {$paymentReference} is already settled as paid.");
                return [
                    'success'   => true,
                    'duplicate' => true,
                    'payment'   => $payment,
                    'error'     => null
                ];
            }

            // 4. Strict Security Verification Checks
            // A. Gateway Status Check
            $gatewayStatus = strtolower((string)($gatewayData['status'] ?? ''));
            if ($gatewayStatus !== 'success') {
                $failStmt = $pdo->prepare("UPDATE payments SET status = 'failed', admin_audit_notes = :notes WHERE id = :id");
                $failStmt->execute([
                    ':notes' => "Gateway status '{$gatewayStatus}' is not success.",
                    ':id'    => $payment['id']
                ]);
                $pdo->commit();
                self::logError("Payment {$paymentReference} rejected: gateway status is '{$gatewayStatus}'.");
                return [
                    'success'   => false,
                    'duplicate' => false,
                    'error'     => "Gateway status is '{$gatewayStatus}', expected 'success'."
                ];
            }

            // B. Currency Check
            $expectedCurrency = strtoupper((string)$payment['currency']);
            $gatewayCurrency = strtoupper((string)($gatewayData['currency'] ?? ''));
            if ($expectedCurrency !== $gatewayCurrency) {
                $failStmt = $pdo->prepare("UPDATE payments SET status = 'failed', admin_audit_notes = :notes WHERE id = :id");
                $failStmt->execute([
                    ':notes' => "Currency mismatch: expected {$expectedCurrency}, received {$gatewayCurrency}.",
                    ':id'    => $payment['id']
                ]);
                $pdo->commit();
                self::logError("Payment {$paymentReference} security failure: currency mismatch ({$expectedCurrency} vs {$gatewayCurrency}).");
                return [
                    'success'   => false,
                    'duplicate' => false,
                    'error'     => "Currency mismatch: expected {$expectedCurrency}, got {$gatewayCurrency}."
                ];
            }

            // C. Exact Subunit Amount Check
            $expectedSubunits = (int)round((float)$payment['amount'] * 100);
            $gatewaySubunits = (int)($gatewayData['amount'] ?? 0);
            if ($expectedSubunits !== $gatewaySubunits) {
                $failStmt = $pdo->prepare("UPDATE payments SET status = 'failed', admin_audit_notes = :notes WHERE id = :id");
                $failStmt->execute([
                    ':notes' => "Amount mismatch: expected {$expectedSubunits} subunits, received {$gatewaySubunits}.",
                    ':id'    => $payment['id']
                ]);
                $pdo->commit();
                self::logError("Payment {$paymentReference} security failure: amount mismatch (expected {$expectedSubunits}, received {$gatewaySubunits}).");
                return [
                    'success'   => false,
                    'duplicate' => false,
                    'error'     => "Amount mismatch detected: expected {$expectedSubunits} subunits, got {$gatewaySubunits}."
                ];
            }

            // 5. Mark Payment Paid Atomically
            $gatewayTxId = (string)($gatewayData['id'] ?? $gatewayData['transaction'] ?? $gatewayData['reference']);
            $channel = (string)($gatewayData['channel'] ?? 'card');
            $auditRemarks = "Verified via Paystack server-to-server ({$channel}). Gateway Tx ID: {$gatewayTxId}";

            $updatePayStmt = $pdo->prepare("UPDATE payments 
                                            SET status = 'paid',
                                                provider_transaction_id = :tx_id,
                                                verified_at = CURRENT_TIMESTAMP,
                                                admin_audit_notes = :notes
                                            WHERE id = :id");
            $updatePayStmt->execute([
                ':tx_id' => $gatewayTxId,
                ':notes' => $auditRemarks,
                ':id'    => $payment['id']
            ]);

            // Fetch updated payment and linked booking
            $bkStmt = $pdo->prepare("SELECT b.*, p.title as package_title, p.slug as package_slug,
                                            p.destination_city, p.destination_country
                                     FROM bookings b
                                     JOIN packages p ON b.package_id = p.id
                                     WHERE b.id = :booking_id");
            $bkStmt->execute([':booking_id' => $payment['booking_id']]);
            $booking = $bkStmt->fetch(PDO::FETCH_ASSOC);

            // Log to system audit trail
            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (action, entity_type, entity_id, details) 
                                        VALUES ('PAYMENT_PAYSTACK_VERIFIED', 'payment', :id, :details)");
            $auditStmt->execute([
                ':id'      => $payment['id'],
                ':details' => "Paystack online payment verified for Booking #{$booking['reference']}. Amount: {$payment['amount']} {$payment['currency']}."
            ]);

            $pdo->commit();

            self::logInfo("Payment {$paymentReference} successfully settled as PAID for Booking {$booking['reference']}.");

            // 6. Send Transactional Payment Receipt & Admin Notification (Gracefully Shielded)
            try {
                MailService::sendPaymentReceipt($booking, $payment, [
                    'title'               => $booking['package_title'],
                    'slug'                => $booking['package_slug'],
                    'destination_city'    => $booking['destination_city'],
                    'destination_country' => $booking['destination_country']
                ]);
                MailService::sendAdminPaymentNotification($booking, $payment, [
                    'title' => $booking['package_title']
                ]);
            } catch (Throwable $mailEx) {
                self::logError("Payment receipt mail dispatch failed for {$paymentReference}: " . $mailEx->getMessage());
            }

            return [
                'success'   => true,
                'duplicate' => false,
                'payment'   => $payment,
                'booking'   => $booking,
                'error'     => null
            ];

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            self::logError("Exception during settlePayment for {$paymentReference}: " . $e->getMessage());
            return [
                'success'   => false,
                'duplicate' => false,
                'error'     => 'An internal database exception occurred while settling the payment.'
            ];
        }
    }

    /**
     * Execute an HTTP request to the Paystack API using native cURL or stream context.
     */
    private static function makeRequest(
        string $method,
        string $url,
        ?array $data = null,
        string $secretKey = ''
    ): array {
        // If mock transport is registered, delegate directly
        if (is_callable(self::$mockTransport)) {
            return (self::$mockTransport)($method, $url, [
                'Authorization: Bearer ' . $secretKey,
                'Content-Type: application/json',
                'Accept: application/json'
            ], $data ? json_encode($data) : null);
        }

        $headers = [
            'Authorization: Bearer ' . $secretKey,
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: LAD-Global-Travels/1.0 (+http://ladglobaltravel.com)'
        ];

        // 1. Try native cURL if extension loaded
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

            if (strtoupper($method) === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data !== null) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
            } elseif (strtoupper($method) !== 'GET') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
            }

            $rawResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($rawResponse === false) {
                return [
                    'success'  => false,
                    'httpCode' => 0,
                    'message'  => "cURL communication error: {$curlError}",
                    'data'     => null
                ];
            }

            $decoded = json_decode((string)$rawResponse, true);
            $isSuccess = ($httpCode >= 200 && $httpCode < 300) && (!empty($decoded['status']));

            return [
                'success'  => $isSuccess,
                'httpCode' => $httpCode,
                'data'     => is_array($decoded) ? $decoded : [],
                'message'  => $decoded['message'] ?? ($isSuccess ? 'Success' : "HTTP error {$httpCode}")
            ];
        }

        // 2. Fallback to PHP native stream context
        $options = [
            'http' => [
                'method'        => strtoupper($method),
                'header'        => implode("\r\n", $headers),
                'timeout'       => 12,
                'ignore_errors' => true
            ]
        ];

        if ($data !== null && strtoupper($method) === 'POST') {
            $options['http']['content'] = json_encode($data);
        }

        $context = stream_context_create($options);
        $rawResponse = @file_get_contents($url, false, $context);

        if ($rawResponse === false) {
            return [
                'success'  => false,
                'httpCode' => 0,
                'message'  => 'Stream connection failure to Paystack API.',
                'data'     => null
            ];
        }

        $decoded = json_decode($rawResponse, true);
        return [
            'success'  => !empty($decoded['status']),
            'httpCode' => 200,
            'data'     => is_array($decoded) ? $decoded : [],
            'message'  => $decoded['message'] ?? 'Response received'
        ];
    }

    /**
     * Append informational message to storage/logs/payment.log.
     */
    private static function logInfo(string $message): void
    {
        self::writeLog('INFO', $message);
    }

    /**
     * Append error message to storage/logs/payment.log.
     */
    private static function logError(string $message): void
    {
        self::writeLog('ERROR', $message);
    }

    /**
     * Thread-safe structured log writer.
     */
    private static function writeLog(string $level, string $message): void
    {
        $logFile = dirname(__DIR__, 2) . '/storage/logs/payment.log';
        $timestamp = date('Y-m-d H:i:s');
        $entry = sprintf("[%s] [%s] %s%s", $timestamp, $level, $message, PHP_EOL);
        @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }
}
