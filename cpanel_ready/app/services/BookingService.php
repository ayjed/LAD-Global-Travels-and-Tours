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
 * BookingService
 * 
 * Manages package booking creation, server-authoritative price freezing, high-entropy
 * reference generation, bank transfer reconciliation, admin payment audit trails,
 * and travel confirmation management per LG-SOT-001 Section 8 & 9.
 * 
 * Strict Invariants:
 * - Direct booking requires: published package, payment_enabled=1, verified price > 0.
 * - Final amount determined exclusively on the server (travellers * unit_price); form POST amount ignored.
 * - Payment received is strictly distinguished from travel booking confirmed (D11).
 * - Manual transfers require external bank statement verification by admin with recorded audit note.
 */
class BookingService
{
    public const VALID_BOOKING_STATUSES = ['pending', 'confirmed', 'cancelled'];
    public const VALID_PAYMENT_STATUSES = ['pending', 'paid', 'failed', 'rejected'];

    /**
     * Check if a tour package is eligible for direct online booking and payment.
     */
    public static function isPackagePayable(array $package): bool
    {
        $isPublished = ($package['status'] ?? '') === 'published';
        $isPaymentEnabled = (int)($package['is_payment_enabled'] ?? $package['payment_enabled'] ?? 0) === 1;
        $price = $package['price_amount'] ?? $package['price'] ?? null;
        $hasPrice = !empty($price) && (float)$price > 0;
        $currency = strtoupper((string)($package['price_currency'] ?? $package['currency'] ?? 'NGN'));
        $isNgn = $currency === 'NGN';

        return $isPublished && $isPaymentEnabled && $hasPrice && $isNgn;
    }

    /**
     * Create a new booking and initial payment attempt.
     * Enforces server-authoritative price calculation and freezes amounts into the records.
     *
     * @param array $package The tour package record from database
     * @param array $customerData Customer input (name, email, phone, travellers_count)
     * @param string $provider 'manual' or 'paystack'
     * @return array ['booking' => array, 'payment' => array]
     * @throws RuntimeException If package is not eligible or validation fails
     */
    public static function createBooking(array $package, array $customerData, string $provider = 'manual'): array
    {
        if (!self::isPackagePayable($package)) {
            throw new RuntimeException('This tour package is currently not available for direct payment. Please submit a consultation enquiry instead.');
        }

        $provider = in_array($provider, ['manual', 'paystack'], true) ? $provider : 'manual';

        if ($provider === 'manual' && !SettingService::isBankTransferEnabled()) {
            throw new RuntimeException('Direct bank transfer payment is currently disabled.');
        }

        if ($provider === 'paystack' && !SettingService::isPaystackEnabled()) {
            throw new RuntimeException('Paystack payment gateway is currently disabled.');
        }

        // 1. Server-authoritative calculation (Respect D06: pricing per person)
        $travellers = max(1, min(20, (int)($customerData['travellers_count'] ?? 1)));
        $unitPrice = (float)($package['price_amount'] ?? $package['price'] ?? 0);
        $totalAmount = round($unitPrice * $travellers, 2);
        $currency = strtoupper((string)($package['price_currency'] ?? $package['currency'] ?? 'NGN'));

        // 2. High-entropy unique references
        $bookingReference = self::generateUniqueBookingReference();
        $paymentReference = self::generateUniquePaymentReference();

        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            // 3. Insert Booking Record
            $bkStmt = $pdo->prepare("INSERT INTO bookings (
                reference, package_id, customer_name, customer_email, customer_phone,
                travellers_count, unit_price, total_amount, currency, booking_status
            ) VALUES (
                :reference, :package_id, :customer_name, :customer_email, :customer_phone,
                :travellers_count, :unit_price, :total_amount, :currency, 'pending'
            )");

            $bkStmt->execute([
                ':reference'        => $bookingReference,
                ':package_id'       => (int)$package['id'],
                ':customer_name'    => trim((string)($customerData['customer_name'] ?? '')),
                ':customer_email'   => strtolower(trim((string)($customerData['customer_email'] ?? ''))),
                ':customer_phone'   => trim((string)($customerData['customer_phone'] ?? '')),
                ':travellers_count' => $travellers,
                ':unit_price'       => $unitPrice,
                ':total_amount'     => $totalAmount,
                ':currency'         => $currency
            ]);

            $bookingId = (int)$pdo->lastInsertId();

            // 4. Insert Initial Payment Attempt
            $payStmt = $pdo->prepare("INSERT INTO payments (
                booking_id, payment_reference, provider, amount, currency, status, admin_audit_notes
            ) VALUES (
                :booking_id, :payment_reference, :provider, :amount, :currency, 'pending', :notes
            )");

            $initialNote = $provider === 'manual' 
                ? 'Pending manual corporate bank transfer reconciliation.'
                : 'Initiated online Paystack checkout attempt.';

            $payStmt->execute([
                ':booking_id'         => $bookingId,
                ':payment_reference'  => $paymentReference,
                ':provider'           => $provider,
                ':amount'             => $totalAmount,
                ':currency'           => $currency,
                ':notes'              => $initialNote
            ]);

            $paymentId = (int)$pdo->lastInsertId();

            $pdo->commit();

            // 5. Fetch clean created models
            $booking = self::getBookingById($bookingId);
            $payment = self::getPaymentById($paymentId);

            return [
                'booking' => $booking,
                'payment' => $payment
            ];

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new RuntimeException("Failed to persist booking: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Retrieve a booking by its unique reference code (e.g., BK-XXXXXXXXXXXX).
     */
    public static function getBookingByReference(string $reference): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT b.*, 
                                      p.title as package_title, 
                                      p.slug as package_slug,
                                      p.destination_city, 
                                      p.destination_country,
                                      p.duration_days,
                                      p.cover_image
                               FROM bookings b
                               JOIN packages p ON b.package_id = p.id
                               WHERE b.reference = :ref
                               LIMIT 1");
        $stmt->execute([':ref' => trim($reference)]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            return null;
        }

        // Attach latest payment attempt
        $booking['latest_payment'] = self::getLatestPaymentForBooking((int)$booking['id']);
        $booking['payments'] = self::getPaymentsForBooking((int)$booking['id']);

        return $booking;
    }

    /**
     * Retrieve a booking by its primary key ID.
     */
    public static function getBookingById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT b.*, 
                                      p.title as package_title, 
                                      p.slug as package_slug,
                                      p.destination_city, 
                                      p.destination_country,
                                      p.duration_days,
                                      p.cover_image
                               FROM bookings b
                               JOIN packages p ON b.package_id = p.id
                               WHERE b.id = :id
                               LIMIT 1");
        $stmt->execute([':id' => $id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            return null;
        }

        $booking['latest_payment'] = self::getLatestPaymentForBooking((int)$booking['id']);
        $booking['payments'] = self::getPaymentsForBooking((int)$booking['id']);

        return $booking;
    }

    /**
     * Retrieve a payment attempt by its primary key ID.
     */
    public static function getPaymentById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT p.*, a.username as verified_by_username
                               FROM payments p
                               LEFT JOIN admins a ON p.verified_by_admin_id = a.id
                               WHERE p.id = :id
                               LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Retrieve a payment attempt by its unique reference code (e.g., PAY-XXXXXXXXXXXXXXXX).
     */
    public static function getPaymentByReference(string $reference): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT p.*, a.username as verified_by_username
                               FROM payments p
                               LEFT JOIN admins a ON p.verified_by_admin_id = a.id
                               WHERE p.payment_reference = :ref
                               LIMIT 1");
        $stmt->execute([':ref' => trim($reference)]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get all payment attempts recorded for a given booking.
     */
    public static function getPaymentsForBooking(int $bookingId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT p.*, a.username as verified_by_username
                               FROM payments p
                               LEFT JOIN admins a ON p.verified_by_admin_id = a.id
                               WHERE p.booking_id = :bid
                               ORDER BY p.created_at DESC");
        $stmt->execute([':bid' => $bookingId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the most recent payment attempt for a booking.
     */
    public static function getLatestPaymentForBooking(int $bookingId): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT p.*, a.username as verified_by_username
                               FROM payments p
                               LEFT JOIN admins a ON p.verified_by_admin_id = a.id
                               WHERE p.booking_id = :bid
                               ORDER BY p.created_at DESC
                               LIMIT 1");
        $stmt->execute([':bid' => $bookingId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Record an optional customer bank transfer narration/memo without marking the payment paid.
     * Adheres to SOT Section 8.2: Customer clicking "I paid" or providing notes is NEVER auto-confirmed.
     */
    public static function recordCustomerTransferMemo(int $paymentId, string $customerMemo): bool
    {
        $cleanMemo = trim(strip_tags($customerMemo));
        if ($cleanMemo === '') {
            return false;
        }

        $pdo = Database::getConnection();
        $noteEntry = sprintf("[%s] Customer reported transfer memo: %s", date('Y-m-d H:i:s'), $cleanMemo);

        $stmt = $pdo->prepare("UPDATE payments 
                               SET admin_audit_notes = CONCAT_WS('\n', COALESCE(admin_audit_notes, ''), :memo)
                               WHERE id = :id AND status = 'pending'");
        return $stmt->execute([
            ':memo' => $noteEntry,
            ':id'   => $paymentId
        ]);
    }

    /**
     * Admin manual bank transfer reconciliation.
     * Marks payment as 'paid' ONLY after the administrator confirms external bank check.
     *
     * @param int $paymentId ID of the payment to verify
     * @param int $adminId ID of the verifying administrator
     * @param string $auditNotes Required external bank verification remarks (e.g. statement date/ref)
     * @return bool
     * @throws RuntimeException If audit notes are empty or payment cannot be transitioned
     */
    public static function adminVerifyManualPayment(int $paymentId, int $adminId, string $auditNotes): bool
    {
        $cleanNotes = trim(strip_tags($auditNotes));
        if (strlen($cleanNotes) < 5) {
            throw new RuntimeException('Verification remarks are mandatory and must include external bank statement check details (minimum 5 characters).');
        }

        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            // Lock payment record
            $payStmt = $pdo->prepare("SELECT * FROM payments WHERE id = :id FOR UPDATE");
            $payStmt->execute([':id' => $paymentId]);
            $payment = $payStmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                $pdo->rollBack();
                throw new RuntimeException('Payment record not found.');
            }

            if ($payment['status'] === 'paid') {
                $pdo->rollBack();
                return true; // Already verified
            }

            $auditEntry = sprintf("[%s] Manually verified by Admin #%d: %s", date('Y-m-d H:i:s'), $adminId, $cleanNotes);

            $updateStmt = $pdo->prepare("UPDATE payments 
                                         SET status = 'paid',
                                             verified_at = CURRENT_TIMESTAMP,
                                             verified_by_admin_id = :admin_id,
                                             admin_audit_notes = CONCAT_WS('\n', COALESCE(admin_audit_notes, ''), :notes)
                                         WHERE id = :id");
            $updateStmt->execute([
                ':admin_id' => $adminId,
                ':notes'    => $auditEntry,
                ':id'       => $paymentId
            ]);

            // Fetch linked booking and package for audit logging & receipt
            $bkStmt = $pdo->prepare("SELECT b.*, p.title as package_title, p.slug as package_slug,
                                            p.destination_city, p.destination_country
                                     FROM bookings b
                                     JOIN packages p ON b.package_id = p.id
                                     WHERE b.id = :bid");
            $bkStmt->execute([':bid' => $payment['booking_id']]);
            $booking = $bkStmt->fetch(PDO::FETCH_ASSOC);

            // Record to system audit trail
            $logStmt = $pdo->prepare("INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, details)
                                      VALUES (:aid, 'PAYMENT_MANUAL_VERIFIED', 'payment', :pid, :details)");
            $logStmt->execute([
                ':aid'     => $adminId,
                ':pid'     => $paymentId,
                ':details' => "Manual bank transfer verified for Booking #{$booking['reference']} (Amount: {$payment['amount']} {$payment['currency']}). Remarks: {$cleanNotes}"
            ]);

            $pdo->commit();

            // Send transactional receipt to customer and alert to admin (safely shielded)
            try {
                MailService::sendPaymentReceipt($booking, $payment, [
                    'title'               => $booking['package_title'],
                    'slug'                => $booking['package_slug'],
                    'destination_city'    => $booking['destination_city'],
                    'destination_country' => $booking['destination_country']
                ]);
            } catch (Throwable $mailEx) {
                // Receipt failure does not roll back manual verification
            }

            return true;

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new RuntimeException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Admin rejects an invalid manual transfer attempt.
     */
    public static function adminRejectManualPayment(int $paymentId, int $adminId, string $reason): bool
    {
        $cleanReason = trim(strip_tags($reason));
        if ($cleanReason === '') {
            $cleanReason = 'No matching funds received in corporate bank account.';
        }

        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            $auditEntry = sprintf("[%s] Rejected by Admin #%d: %s", date('Y-m-d H:i:s'), $adminId, $cleanReason);

            $stmt = $pdo->prepare("UPDATE payments 
                                   SET status = 'rejected',
                                       verified_at = CURRENT_TIMESTAMP,
                                       verified_by_admin_id = :admin_id,
                                       admin_audit_notes = CONCAT_WS('\n', COALESCE(admin_audit_notes, ''), :notes)
                                   WHERE id = :id AND status != 'paid'");
            $stmt->execute([
                ':admin_id' => $adminId,
                ':notes'    => $auditEntry,
                ':id'       => $paymentId
            ]);

            $logStmt = $pdo->prepare("INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, details)
                                      VALUES (:aid, 'PAYMENT_MANUAL_REJECTED', 'payment', :pid, :details)");
            $logStmt->execute([
                ':aid'     => $adminId,
                ':pid'     => $paymentId,
                ':details' => "Payment #{$paymentId} rejected by Admin. Reason: {$cleanReason}"
            ]);

            $pdo->commit();
            return true;

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new RuntimeException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Update booking travel status (pending / confirmed / cancelled).
     * Strictly separate from payment settlement (D11).
     */
    public static function adminUpdateBookingStatus(int $bookingId, int $adminId, string $newStatus, string $auditNotes = ''): bool
    {
        if (!in_array($newStatus, self::VALID_BOOKING_STATUSES, true)) {
            throw new RuntimeException("Invalid booking status: {$newStatus}");
        }

        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("UPDATE bookings SET booking_status = :status WHERE id = :id");
        $stmt->execute([
            ':status' => $newStatus,
            ':id'     => $bookingId
        ]);

        $logStmt = $pdo->prepare("INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, details)
                                  VALUES (:aid, 'BOOKING_STATUS_UPDATED', 'booking', :bid, :details)");
        $logStmt->execute([
            ':aid'     => $adminId,
            ':bid'     => $bookingId,
            ':details' => "Booking status updated to '{$newStatus}'. Notes: " . strip_tags($auditNotes)
        ]);

        return true;
    }

    /**
     * List all bookings with optional filtering and search.
     */
    public static function listBookings(array $filters = []): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT b.*, 
                       p.title as package_title, 
                       p.slug as package_slug,
                       (SELECT status FROM payments WHERE booking_id = b.id ORDER BY id DESC LIMIT 1) as payment_status,
                       (SELECT provider FROM payments WHERE booking_id = b.id ORDER BY id DESC LIMIT 1) as payment_provider
                FROM bookings b
                JOIN packages p ON b.package_id = p.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status']) && in_array($filters['status'], self::VALID_BOOKING_STATUSES, true)) {
            $sql .= " AND b.booking_status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $sql .= " AND (b.reference LIKE :s1 OR b.customer_name LIKE :s2 OR b.customer_email LIKE :s3 OR b.customer_phone LIKE :s4)";
            $params[':s1'] = $search;
            $params[':s2'] = $search;
            $params[':s3'] = $search;
            $params[':s4'] = $search;
        }

        $sql .= " ORDER BY b.created_at DESC";

        $limit = isset($filters['limit']) ? (int)$filters['limit'] : 100;
        $sql .= " LIMIT " . max(1, min(200, $limit));

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * List payments with filtering.
     */
    public static function listPayments(array $filters = []): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT p.*, 
                       b.reference as booking_reference, 
                       b.customer_name, 
                       b.customer_phone,
                       pkg.title as package_title,
                       a.username as verified_by_username
                FROM payments p
                JOIN bookings b ON p.booking_id = b.id
                JOIN packages pkg ON b.package_id = pkg.id
                LEFT JOIN admins a ON p.verified_by_admin_id = a.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['provider']) && in_array($filters['provider'], ['manual', 'paystack'], true)) {
            $sql .= " AND p.provider = :provider";
            $params[':provider'] = $filters['provider'];
        }

        if (!empty($filters['status']) && in_array($filters['status'], self::VALID_PAYMENT_STATUSES, true)) {
            $sql .= " AND p.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $sql .= " AND (p.payment_reference LIKE :s1 OR b.reference LIKE :s2 OR b.customer_name LIKE :s3)";
            $params[':s1'] = $search;
            $params[':s2'] = $search;
            $params[':s3'] = $search;
        }

        $sql .= " ORDER BY p.created_at DESC";

        $limit = isset($filters['limit']) ? (int)$filters['limit'] : 100;
        $sql .= " LIMIT " . max(1, min(200, $limit));

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get pending manual bank transfers requiring administrator external bank verification.
     */
    public static function getPendingManualTransfers(int $limit = 50): array
    {
        return self::listPayments([
            'provider' => 'manual',
            'status'   => 'pending',
            'limit'    => $limit
        ]);
    }

    /**
     * Get aggregated counts for admin dashboard and reporting.
     */
    public static function getCounts(): array
    {
        $pdo = Database::getConnection();

        $bkCounts = $pdo->query("SELECT 
            COUNT(*) as total_bookings,
            SUM(CASE WHEN booking_status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
            SUM(CASE WHEN booking_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings
            FROM bookings")->fetch(PDO::FETCH_ASSOC) ?: [];

        $payCounts = $pdo->query("SELECT 
            SUM(CASE WHEN provider = 'manual' AND status = 'pending' THEN 1 ELSE 0 END) as pending_transfers,
            SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_payments,
            COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0) as total_collected
            FROM payments")->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total_bookings'    => (int)($bkCounts['total_bookings'] ?? 0),
            'pending_bookings'  => (int)($bkCounts['pending_bookings'] ?? 0),
            'confirmed_bookings'=> (int)($bkCounts['confirmed_bookings'] ?? 0),
            'pending_transfers' => (int)($payCounts['pending_transfers'] ?? 0),
            'paid_payments'     => (int)($payCounts['paid_payments'] ?? 0),
            'total_collected'   => (float)($payCounts['total_collected'] ?? 0.0)
        ];
    }

    /**
     * Retrieve verified corporate bank transfer details from settings/environment configuration.
     * Adheres to SOT Section 8.2: Details are admin-configured and verified; never hardcoded randomly.
     */
    public static function getBankDetails(): array
    {
        return SettingService::getBankDetails();
    }

    /**
     * Generate high-entropy unique booking reference code (e.g. BK-4A2D81E9C7B0).
     */
    private static function generateUniqueBookingReference(): string
    {
        $pdo = Database::getConnection();
        $attempts = 0;

        do {
            $ref = 'BK-' . strtoupper(bin2hex(random_bytes(6)));
            $stmt = $pdo->prepare("SELECT id FROM bookings WHERE reference = :ref LIMIT 1");
            $stmt->execute([':ref' => $ref]);
            $exists = (bool)$stmt->fetchColumn();
            $attempts++;
        } while ($exists && $attempts < 10);

        if ($exists) {
            $ref = 'BK-' . strtoupper(bin2hex(random_bytes(8)));
        }

        return $ref;
    }

    /**
     * Generate high-entropy unique payment attempt reference code (e.g. PAY-8F3A29B0D1E47C65).
     */
    private static function generateUniquePaymentReference(): string
    {
        $pdo = Database::getConnection();
        $attempts = 0;

        do {
            $ref = 'PAY-' . strtoupper(bin2hex(random_bytes(8)));
            $stmt = $pdo->prepare("SELECT id FROM payments WHERE payment_reference = :ref LIMIT 1");
            $stmt->execute([':ref' => $ref]);
            $exists = (bool)$stmt->fetchColumn();
            $attempts++;
        } while ($exists && $attempts < 10);

        if ($exists) {
            $ref = 'PAY-' . strtoupper(bin2hex(random_bytes(10)));
        }

        return $ref;
    }
}
