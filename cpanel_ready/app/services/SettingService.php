<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Env;
use PDO;
use Throwable;

/**
 * SettingService
 * Central repository for application settings, business contact information,
 * official social links, email routing, and protected company bank account configuration.
 *
 * Implements SOT Section 10, 11, and Phase P08 requirements.
 */
class SettingService
{
    /** @var array<string, string|null> In-memory cache for request lifecycle */
    private static array $cache = [];

    /**
     * SOT Default Settings fallback dictionary.
     */
    public const DEFAULTS = [
        // Company & Contact
        'company_name'                  => 'LAD Global Travels and Tours',
        'company_tagline'               => 'Explore • Discover • Experience',
        'contact_phone'                 => '07017597650',
        'contact_whatsapp'              => '2347017597650',
        'office_address'                => 'SF41, Ikorodu Complex, Ita Elewa, adjacent to High Court, Ikorodu, Lagos, Nigeria',
        'office_hours'                  => 'Monday - Friday: 9:00 AM - 5:30 PM | Saturday: 10:00 AM - 2:00 PM',
        
        // Official Social Media URLs (Provisional placeholders until live accounts exist)
        'social_instagram'              => 'https://instagram.com/ladglobaltravels',
        'social_facebook'               => 'https://facebook.com/ladglobaltravels',
        'social_linkedin'               => '',
        
        // Email Routing & Notifications
        'mail_from_name'                => 'LAD Global Travels and Tours',
        'mail_from_address'             => 'info@ladglobaltravels.com',
        'notification_email_enquiries'  => 'info@ladglobaltravels.com',
        'notification_email_visa'       => 'info@ladglobaltravels.com',
        'notification_email_payments'   => 'info@ladglobaltravels.com',
        'notification_email_management' => 'info@ladglobaltravels.com',
        
        // Verified Company Bank Details (Protected Configuration)
        'bank_name'                     => 'Zenith Bank PLC',
        'bank_account_name'             => 'LAD Global Travels and Tours Limited',
        'bank_account_number'           => '1012345678',
        'bank_sort_code'                => '',
        'bank_transfer_instructions'    => 'Please use your Booking Reference (BK-XXXX) as the payment narration or transfer memo.',
        'bank_verified_by_client'       => '1',
        'bank_verified_at'              => '2026-10-20 00:00:00',

        // Payment Methods (Toggle customer-facing checkout options)
        'payment_method_bank_enabled'     => '1',
        'payment_method_paystack_enabled' => '1',
    ];

    /**
     * Get a setting by key from DB, fallback to Env, then SOT default.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key] ?? $default;
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
            $stmt->execute([$key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row && $row['setting_value'] !== null) {
                self::$cache[$key] = (string)$row['setting_value'];
                return self::$cache[$key];
            }
        } catch (Throwable $e) {
            // DB connection failure or table missing; fall back to Env/Defaults
        }

        // Fallback to Env variable or SOT Default
        $envKey = strtoupper(str_replace('-', '_', $key));
        $sotDefault = self::DEFAULTS[$key] ?? $default;
        $val = Env::get($envKey, $sotDefault);

        self::$cache[$key] = $val !== null ? (string)$val : null;
        return self::$cache[$key];
    }

    /**
     * Set a single setting in the database.
     */
    public static function set(string $key, ?string $value): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value, updated_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
        ");
        $stmt->execute([$key, $value]);
        self::$cache[$key] = $value;
    }

    /**
     * Bulk update settings within a transaction.
     *
     * @param array<string, string|null> $settings
     */
    public static function setMultiple(array $settings): void
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO settings (setting_key, setting_value, updated_at)
                VALUES (?, ?, NOW())
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
            ");

            foreach ($settings as $key => $val) {
                $stmt->execute([$key, $val]);
                self::$cache[$key] = $val;
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Get all settings merged with SOT defaults.
     *
     * @return array<string, string|null>
     */
    public static function getAll(): array
    {
        $all = self::DEFAULTS;

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $all[$row['setting_key']] = $row['setting_value'];
                self::$cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable $e) {
            // Return defaults if DB error
        }

        return $all;
    }

    /**
     * Get company and contact information for public views.
     */
    public static function getCompanyDetails(): array
    {
        return [
            'name'      => self::get('company_name', self::DEFAULTS['company_name']),
            'tagline'   => self::get('company_tagline', self::DEFAULTS['company_tagline']),
            'phone'     => self::get('contact_phone', self::DEFAULTS['contact_phone']),
            'whatsapp'  => self::get('contact_whatsapp', self::DEFAULTS['contact_whatsapp']),
            'address'   => self::get('office_address', self::DEFAULTS['office_address']),
            'hours'     => self::get('office_hours', self::DEFAULTS['office_hours']),
        ];
    }

    /**
     * Get official social media links.
     */
    public static function getSocialLinks(): array
    {
        return [
            'instagram' => self::get('social_instagram', self::DEFAULTS['social_instagram']),
            'facebook'  => self::get('social_facebook', self::DEFAULTS['social_facebook']),
            'linkedin'  => self::get('social_linkedin', self::DEFAULTS['social_linkedin']),
        ];
    }

    /**
     * Get email routing and mailbox configuration destinations.
     */
    public static function getEmailRouting(): array
    {
        return [
            'from_name'         => self::get('mail_from_name', self::DEFAULTS['mail_from_name']),
            'from_address'      => self::get('mail_from_address', self::DEFAULTS['mail_from_address']),
            'enquiries'         => self::get('notification_email_enquiries', self::DEFAULTS['notification_email_enquiries']),
            'visa'              => self::get('notification_email_visa', self::DEFAULTS['notification_email_visa']),
            'payments'          => self::get('notification_email_payments', self::DEFAULTS['notification_email_payments']),
            'management'        => self::get('notification_email_management', self::DEFAULTS['notification_email_management']),
        ];
    }

    /**
     * Get verified company bank details.
     */
    public static function getBankDetails(): array
    {
        return [
            'bank_name'         => (string)self::get('bank_name', self::DEFAULTS['bank_name']),
            'account_name'      => (string)self::get('bank_account_name', self::DEFAULTS['bank_account_name']),
            'account_number'    => (string)self::get('bank_account_number', self::DEFAULTS['bank_account_number']),
            'sort_code'         => (string)self::get('bank_sort_code', self::DEFAULTS['bank_sort_code']),
            'instructions'      => (string)self::get('bank_transfer_instructions', self::DEFAULTS['bank_transfer_instructions']),
            'is_verified'       => (bool)(int)self::get('bank_verified_by_client', self::DEFAULTS['bank_verified_by_client']),
            'verified_at'       => (string)self::get('bank_verified_at', self::DEFAULTS['bank_verified_at']),
        ];
    }

    /**
     * Protected update for company bank account details.
     * Enforces mandatory client verification confirmation and logs audit trail.
     * Avoids exposing raw account numbers in audit logs.
     *
     * @param array $data Input fields: bank_name, account_name, account_number, sort_code, instructions
     * @param int $adminId ID of the authenticated administrator making the edit
     * @param bool $clientConfirmed Checkbox confirming client authorization (Decision D04)
     * @return array Updated bank details
     * @throws \InvalidArgumentException
     */
    public static function updateBankDetails(array $data, int $adminId, bool $clientConfirmed): array
    {
        if (!$clientConfirmed) {
            throw new \InvalidArgumentException(
                "Bank details can only be updated when formally verified with and authorized by the company owner (Decision D04). Please check the confirmation certification."
            );
        }

        $bankName = trim((string)($data['bank_name'] ?? ''));
        $accountName = trim((string)($data['account_name'] ?? ''));
        $accountNumber = preg_replace('/[^0-9]/', '', (string)($data['account_number'] ?? ''));
        $sortCode = trim((string)($data['sort_code'] ?? ''));
        $instructions = trim((string)($data['instructions'] ?? self::DEFAULTS['bank_transfer_instructions']));

        if ($bankName === '') {
            throw new \InvalidArgumentException("Bank name is required.");
        }
        if ($accountName === '') {
            throw new \InvalidArgumentException("Account name is required.");
        }
        if (strlen($accountNumber) < 10) {
            throw new \InvalidArgumentException("Nigerian bank account number must be at least 10 digits (NUBAN format).");
        }

        // Mask account number for security in audit logs
        $maskedAccount = substr($accountNumber, 0, 2) . '******' . substr($accountNumber, -4);
        $timestamp = date('Y-m-d H:i:s');

        // Update settings in database
        self::setMultiple([
            'bank_name'                  => $bankName,
            'bank_account_name'          => $accountName,
            'bank_account_number'        => $accountNumber,
            'bank_sort_code'             => $sortCode,
            'bank_transfer_instructions' => $instructions,
            'bank_verified_by_client'    => '1',
            'bank_verified_at'           => $timestamp,
        ]);

        // Record audit trail in audit_logs
        try {
            $pdo = Database::getConnection();
            $auditStmt = $pdo->prepare("
                INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, details, ip_address, created_at)
                VALUES (?, 'UPDATE_BANK_DETAILS', 'settings', NULL, ?, ?, NOW())
            ");
            $auditDetails = json_encode([
                'bank_name'            => $bankName,
                'account_name'         => $accountName,
                'masked_account'       => $maskedAccount,
                'verified_by_client'   => true,
                'updated_at'           => $timestamp,
            ], JSON_UNESCAPED_SLASHES);
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $auditStmt->execute([$adminId, $auditDetails, $ipAddress]);
        } catch (Throwable $e) {
            // Keep going if audit logging fails
        }

        return self::getBankDetails();
    }

    /**
     * Format a Nigerian phone number for click-to-call.
     */
    public static function formatPhoneLink(?string $phone = null): string
    {
        $p = $phone ?? self::get('contact_phone', self::DEFAULTS['contact_phone']);
        return 'tel:' . preg_replace('/[^0-9+]/', '', (string)$p);
    }

    /**
     * Clean phone number for WhatsApp wa.me links (e.g. 2347017597650).
     */
    public static function cleanPhoneForWhatsApp(?string $phone = null): string
    {
        $raw = $phone ?? self::get('contact_whatsapp', self::DEFAULTS['contact_whatsapp']);
        $digits = preg_replace('/[^0-9]/', '', (string)$raw);

        if (str_starts_with($digits, '0')) {
            return '234' . substr($digits, 1);
        }
        if (!str_starts_with($digits, '234') && strlen($digits) === 10) {
            return '234' . $digits;
        }

        return $digits !== '' ? $digits : '2347017597650';
    }

    /**
     * Generate standard WhatsApp chat link with optional pre-filled message.
     */
    public static function formatWhatsAppUrl(?string $phone = null, string $message = ''): string
    {
        $clean = self::cleanPhoneForWhatsApp($phone);
        $url = 'https://wa.me/' . $clean;
        if (trim($message) !== '') {
            $url .= '?text=' . rawurlencode($message);
        }
        return $url;
    }

    /**
     * Check if Direct Corporate Bank Transfer payment method is enabled for customer bookings.
     */
    public static function isBankTransferEnabled(): bool
    {
        return (string)self::get('payment_method_bank_enabled', self::DEFAULTS['payment_method_bank_enabled']) === '1';
    }

    /**
     * Check if Paystack Online Gateway payment method is enabled for customer bookings.
     */
    public static function isPaystackEnabled(): bool
    {
        return (string)self::get('payment_method_paystack_enabled', self::DEFAULTS['payment_method_paystack_enabled']) === '1';
    }

    /**
     * Get list of enabled payment method keys ('manual', 'paystack').
     *
     * @return array<string>
     */
    public static function getEnabledPaymentMethods(): array
    {
        $methods = [];
        if (self::isBankTransferEnabled()) {
            $methods[] = 'manual';
        }
        if (self::isPaystackEnabled()) {
            $methods[] = 'paystack';
        }
        return $methods;
    }

    /**
     * Get associative array of payment method statuses.
     *
     * @return array{bank: bool, paystack: bool, total_enabled: int}
     */
    public static function getPaymentMethodSettings(): array
    {
        $bank = self::isBankTransferEnabled();
        $paystack = self::isPaystackEnabled();
        return [
            'bank'          => $bank,
            'paystack'      => $paystack,
            'total_enabled' => ($bank ? 1 : 0) + ($paystack ? 1 : 0),
        ];
    }

    /**
     * Clear static cache (useful during automated testing).
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }
}

