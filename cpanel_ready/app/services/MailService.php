<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use App\Core\Security;
use Throwable;

/**
 * MailService
 * Provides best-effort transactional email notifications for enquiries and bookings
 * using pure native PHP (socket SMTP or native mail()) without external dependencies.
 * Submission workflows remain 100% operational even if SMTP is unconfigured or down.
 */
class MailService
{
    /**
     * Send new enquiry alert to the company/admin inbox.
     *
     * @param array $enquiry The persisted enquiry record
     * @param array|null $package Linked tour package (if applicable)
     * @return bool
     */
    public static function sendEnquiryAdminAlert(array $enquiry, ?array $package = null): bool
    {
        $serviceType = strtolower((string)($enquiry['service_type'] ?? ''));
        $routing = SettingService::getEmailRouting();
        if (str_contains($serviceType, 'visa') || str_contains($serviceType, 'pr') || str_contains($serviceType, 'cbi') || str_contains($serviceType, 'rbi')) {
            $adminEmail = $routing['visa'] ?? $routing['enquiries'];
        } else {
            $adminEmail = $routing['enquiries'] ?? (string)Env::get('ADMIN_NOTIFICATION_EMAIL', 'info@ladglobaltravels.com');
        }
        $ref = Security::e($enquiry['reference'] ?? 'N/A');
        $name = Security::e($enquiry['full_name'] ?? 'N/A');
        $email = Security::e($enquiry['email'] ?? 'N/A');
        $phone = Security::e($enquiry['phone'] ?? 'N/A');
        $service = Security::e($enquiry['service_type'] ?? 'General');
        $destination = !empty($enquiry['destination']) ? Security::e($enquiry['destination']) : 'Not Specified';
        $pDate = !empty($enquiry['preferred_date']) ? Security::e($enquiry['preferred_date']) : 'Flexible';
        $travellers = !empty($enquiry['travellers_count']) ? (int)$enquiry['travellers_count'] : 1;
        $message = nl2br(Security::e($enquiry['message'] ?? ''));
        $packageTitle = $package ? Security::e($package['title']) : ($enquiry['package_title'] ?? null);

        $subject = "[New Enquiry] {$ref} - {$service} from {$name}";

        $htmlBody = <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{$subject}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333333; margin: 0; padding: 20px; background-color: #f7f9fc;">
  <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden;">
    <div style="background-color: #092A60; padding: 20px; color: #ffffff;">
      <h2 style="margin: 0; font-size: 1.25rem;">LAD Global Travels and Tours</h2>
      <p style="margin: 4px 0 0; font-size: 0.9rem; color: #E6AE34;">New Customer Consultation Enquiry</p>
    </div>
    <div style="padding: 24px;">
      <p>A new enquiry has been submitted on the website:</p>
      <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 0.9rem;">
        <tr><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7; font-weight: bold; width: 35%;">Reference:</td><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7; color: #092A60; font-weight: bold;">{$ref}</td></tr>
        <tr><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7; font-weight: bold;">Full Name:</td><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7;">{$name}</td></tr>
        <tr><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7; font-weight: bold;">Email:</td><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7;"><a href="mailto:{$email}">{$email}</a></td></tr>
        <tr><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7; font-weight: bold;">Phone:</td><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7;"><a href="tel:{$phone}">{$phone}</a></td></tr>
        <tr><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7; font-weight: bold;">Service Type:</td><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7;">{$service}</td></tr>
HTML;

        if ($packageTitle) {
            $htmlBody .= "<tr><td style=\"padding: 8px 0; border-bottom: 1px solid #edf2f7; font-weight: bold;\">Associated Package:</td><td style=\"padding: 8px 0; border-bottom: 1px solid #edf2f7; color: #135EB0;\">{$packageTitle}</td></tr>";
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$enquiry['phone']);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '234' . substr($cleanPhone, 1);
        }

        $htmlBody .= <<<HTML
        <tr><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7; font-weight: bold;">Destination:</td><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7;">{$destination}</td></tr>
        <tr><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7; font-weight: bold;">Estimated Date:</td><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7;">{$pDate}</td></tr>
        <tr><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7; font-weight: bold;">Travellers Count:</td><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7;">{$travellers}</td></tr>
      </table>

      <h3 style="font-size: 1rem; color: #092A60; margin-bottom: 8px;">Customer Message / Requirements:</h3>
      <div style="background-color: #f7fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin-bottom: 24px; font-size: 0.9rem;">
        {$message}
      </div>

      <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #e2e8f0; display: flex; gap: 10px;">
        <a href="https://wa.me/{$cleanPhone}?text=Hello%20{$name},%20this%20is%20LAD%20Global%20Travels%20regarding%20your%20enquiry%20{$ref}." style="display: inline-block; background-color: #25D366; color: #ffffff; padding: 10px 16px; border-radius: 4px; text-decoration: none; font-size: 0.9rem; font-weight: bold;">Open WhatsApp Chat</a>
        <a href="tel:{$phone}" style="display: inline-block; background-color: #092A60; color: #ffffff; padding: 10px 16px; border-radius: 4px; text-decoration: none; font-size: 0.9rem;">Call Customer</a>
      </div>
    </div>
  </div>
</body>
</html>
HTML;

        $textBody = "NEW ENQUIRY - LAD Global Travels and Tours\n";
        $textBody .= "Reference: {$ref}\n";
        $textBody .= "Name: {$name}\n";
        $textBody .= "Email: {$email}\n";
        $textBody .= "Phone: {$phone}\n";
        $textBody .= "Service: {$service}\n";
        if ($packageTitle) $textBody .= "Package: {$packageTitle}\n";
        $textBody .= "Destination: {$destination}\n";
        $textBody .= "Date: {$pDate}\n";
        $textBody .= "\nMessage:\n" . ($enquiry['message'] ?? '') . "\n";

        return self::send($adminEmail, $subject, $htmlBody, $textBody);
    }

    /**
     * Send confirmation acknowledgment email to the customer.
     *
     * @param array $enquiry The persisted enquiry record
     * @param array|null $package Linked tour package (if applicable)
     * @return bool
     */
    public static function sendEnquiryCustomerReceipt(array $enquiry, ?array $package = null): bool
    {
        $customerEmail = trim((string)($enquiry['email'] ?? ''));
        if ($customerEmail === '' || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $ref = Security::e($enquiry['reference'] ?? 'N/A');
        $name = Security::e($enquiry['full_name'] ?? 'Valued Client');
        $service = Security::e($enquiry['service_type'] ?? 'Travel Consultation');
        $subject = "Enquiry Received: {$ref} - LAD Global Travels and Tours";

        $htmlBody = <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{$subject}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333333; margin: 0; padding: 20px; background-color: #f7f9fc;">
  <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden;">
    <div style="background-color: #092A60; padding: 24px; color: #ffffff;">
      <h2 style="margin: 0; font-size: 1.25rem;">LAD Global Travels and Tours</h2>
      <p style="margin: 4px 0 0; font-size: 0.9rem; color: #E6AE34;">Consultation Enquiry Acknowledgment</p>
    </div>
    <div style="padding: 24px;">
      <p>Dear <strong>{$name}</strong>,</p>
      <p>Thank you for contacting LAD Global Travels and Tours. Your consultation enquiry regarding <strong>{$service}</strong> has been logged in our system under reference number:</p>
      
      <div style="background-color: #f0fdf4; border: 1px solid #86efac; border-radius: 6px; padding: 14px; text-align: center; margin: 20px 0;">
        <span style="font-size: 0.85rem; color: #166534; display: block; text-transform: uppercase; font-weight: bold;">Your Enquiry Reference</span>
        <span style="font-size: 1.5rem; font-weight: 800; color: #092A60; letter-spacing: 1px;">{$ref}</span>
      </div>

      <p>One of our dedicated travel consultants will review your requirements and reach out via phone or WhatsApp shortly during business hours (Monday – Friday, 9:00 AM – 5:30 PM WAT).</p>

      <p style="font-size: 0.85rem; color: #64748b; margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 16px;">
        <strong>Need immediate assistance?</strong><br>
        WhatsApp: <a href="https://wa.me/2347017597650" style="color: #092A60;">07017597650</a><br>
        Office: SF41, Ikorodu Complex, Ita Elewa, Adjacent to High Court, Ikorodu, Lagos, Nigeria.
      </p>
    </div>
  </div>
</body>
</html>
HTML;

        $textBody = "Dear {$name},\n\nThank you for reaching out to LAD Global Travels and Tours. Your enquiry reference is: {$ref}.\nA travel consultant will contact you shortly.\n\nWhatsApp: 07017597650\nOffice: SF41, Ikorodu Complex, Ita Elewa, Ikorodu, Lagos.";

        return self::send($customerEmail, $subject, $htmlBody, $textBody);
    }

    /**
     * Send bank transfer instructions for manual payment attempts.
     */
    public static function sendBankTransferInstructions(
        array $booking,
        array $payment,
        array $package,
        array $bankDetails
    ): bool {
        $to = trim($booking['customer_email'] ?? '');
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $bRef = Security::e($booking['reference']);
        $pRef = Security::e($payment['payment_reference']);
        $name = Security::e($booking['customer_name']);
        $pkgTitle = Security::e($package['title'] ?? 'Tour Package');
        $amount = number_format((float)$payment['amount'], 2);
        $curr = Security::e($payment['currency']);
        $bankName = Security::e($bankDetails['bank_name']);
        $accName = Security::e($bankDetails['account_name']);
        $accNumber = Security::e($bankDetails['account_number']);

        $subject = "Bank Transfer Instructions: {$bRef} - {$pkgTitle}";

        $htmlBody = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>{$subject}</title></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333333; margin: 0; padding: 20px; background-color: #f7f9fc;">
  <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden;">
    <div style="background-color: #092A60; padding: 24px; color: #ffffff;">
      <h2 style="margin: 0; font-size: 1.25rem;">LAD Global Travels and Tours</h2>
      <p style="margin: 4px 0 0; font-size: 0.9rem; color: #E6AE34;">Manual Bank Transfer Instructions</p>
    </div>
    <div style="padding: 24px;">
      <p>Dear <strong>{$name}</strong>,</p>
      <p>Thank you for initiating your tour booking for <strong>{$pkgTitle}</strong> (Booking Ref: <strong>{$bRef}</strong>).</p>
      <p>Please complete your payment via direct bank transfer using the verified account details below:</p>

      <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 18px; margin: 20px 0;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.95rem;">
          <tr><td style="padding: 6px 0; color: #64748b; width: 40%;">Bank Name:</td><td style="padding: 6px 0; font-weight: bold; color: #092A60;">{$bankName}</td></tr>
          <tr><td style="padding: 6px 0; color: #64748b;">Account Name:</td><td style="padding: 6px 0; font-weight: bold; color: #092A60;">{$accName}</td></tr>
          <tr><td style="padding: 6px 0; color: #64748b;">Account Number:</td><td style="padding: 6px 0; font-weight: 800; font-size: 1.2rem; color: #135EB0; letter-spacing: 1px;">{$accNumber}</td></tr>
          <tr><td style="padding: 6px 0; color: #64748b;">Exact Amount:</td><td style="padding: 6px 0; font-weight: bold; color: #166534; font-size: 1.1rem;">₦{$amount} {$curr}</td></tr>
          <tr><td style="padding: 6px 0; color: #64748b;">Transfer Narration / Memo:</td><td style="padding: 6px 0; font-weight: 800; color: #dc2626;">{$pRef}</td></tr>
        </table>
      </div>

      <div style="background-color: #fffbeb; border: 1px solid #fef3c7; border-left: 4px solid #f59e0b; padding: 12px; border-radius: 4px; font-size: 0.85rem; color: #92400e; margin-bottom: 20px;">
        <strong>IMPORTANT:</strong> You MUST include <strong>{$pRef}</strong> as your payment narration or transfer reference so our finance team can match your payment.
      </div>

      <p style="font-size: 0.9rem; color: #475569;">
        Once your transfer is made, our finance team will verify the funds directly on our bank statement. You will receive an official payment receipt once verified.
      </p>

      <p style="font-size: 0.85rem; color: #64748b; margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 16px;">
        Questions? Contact us on WhatsApp: <a href="https://wa.me/2347017597650">07017597650</a> or email <a href="mailto:info@ladglobaltravel.com">info@ladglobaltravel.com</a>.
      </p>
    </div>
  </div>
</body>
</html>
HTML;

        $textBody = "LAD Global Travels - Bank Transfer Instructions\n\n" .
                    "Dear {$booking['customer_name']},\n" .
                    "Booking Ref: {$booking['reference']}\n" .
                    "Package: {$package['title']}\n" .
                    "Amount Due: ₦{$amount} {$curr}\n\n" .
                    "Bank: {$bankDetails['bank_name']}\n" .
                    "Account Name: {$bankDetails['account_name']}\n" .
                    "Account Number: {$bankDetails['account_number']}\n" .
                    "Narration/Memo (Mandatory): {$payment['payment_reference']}\n\n" .
                    "Our team will reconcile your transfer upon receiving funds.\n" .
                    "WhatsApp: 07017597650";

        return self::send($to, $subject, $htmlBody, $textBody);
    }

    /**
     * Send official payment receipt to the customer upon verified payment.
     * Strictly separates payment receipt from travel confirmation (D11).
     */
    public static function sendPaymentReceipt(
        array $booking,
        array $payment,
        array $package
    ): bool {
        $to = trim($booking['customer_email'] ?? '');
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $bRef = Security::e($booking['reference']);
        $pRef = Security::e($payment['payment_reference']);
        $name = Security::e($booking['customer_name']);
        $pkgTitle = Security::e($package['title'] ?? 'Tour Package');
        $amount = number_format((float)$payment['amount'], 2);
        $curr = Security::e($payment['currency']);
        $provider = strtoupper((string)$payment['provider']);
        $date = date('d M Y, H:i T');

        $subject = "Payment Receipt: {$bRef} - ₦{$amount} Received - LAD Global Travels";

        $htmlBody = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>{$subject}</title></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333333; margin: 0; padding: 20px; background-color: #f7f9fc;">
  <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden;">
    <div style="background-color: #092A60; padding: 24px; color: #ffffff;">
      <h2 style="margin: 0; font-size: 1.25rem;">LAD Global Travels and Tours</h2>
      <p style="margin: 4px 0 0; font-size: 0.9rem; color: #E6AE34;">Official Payment Receipt (Funds Received)</p>
    </div>
    <div style="padding: 24px;">
      <div style="background-color: #f0fdf4; border: 1px solid #86efac; border-radius: 6px; padding: 14px; text-align: center; margin-bottom: 20px;">
        <span style="font-size: 0.85rem; color: #166534; font-weight: bold; text-transform: uppercase;">Payment Status</span>
        <span style="font-size: 1.4rem; font-weight: 800; color: #15803d; display: block;">PAID / VERIFIED</span>
      </div>

      <p>Dear <strong>{$name}</strong>,</p>
      <p>This email confirms that payment has been successfully received and verified for your booking:</p>

      <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 0.9rem;">
        <tr><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7; color: #64748b;">Booking Reference:</td><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7; font-weight: bold; color: #092A60;">{$bRef}</td></tr>
        <tr><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7; color: #64748b;">Payment Reference:</td><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7; font-weight: bold;">{$pRef}</td></tr>
        <tr><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7; color: #64748b;">Tour Package:</td><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7; font-weight: bold;">{$pkgTitle}</td></tr>
        <tr><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7; color: #64748b;">Travellers:</td><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7;">{$booking['travellers_count']} person(s)</td></tr>
        <tr><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7; color: #64748b;">Amount Paid:</td><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7; font-weight: 800; font-size: 1.1rem; color: #15803d;">₦{$amount} {$curr}</td></tr>
        <tr><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7; color: #64748b;">Payment Channel:</td><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7;">{$provider}</td></tr>
        <tr><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7; color: #64748b;">Receipt Date:</td><td style="padding: 8px 0; border-bottom: 1px solid #edf2f7;">{$date}</td></tr>
      </table>

      <!-- Mandatory SOT Boundary Disclosure -->
      <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-left: 4px solid #135EB0; padding: 14px; border-radius: 4px; font-size: 0.85rem; color: #334155; margin-bottom: 20px;">
        <strong>STATUTORY TRAVEL NOTICE:</strong><br>
        This receipt certifies <strong>payment received</strong> only. It is <strong>NOT an issued airline ticket, hotel reservation voucher, or travel confirmation</strong>. Package fulfillment and flight/hotel bookings are subject to final logistical verification by LAD Global Travels consultants. Our operations desk will contact you to conclude your travel arrangements.
      </div>

      <p style="font-size: 0.85rem; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 16px;">
        LAD Global Travels and Tours — SF41, Ikorodu Complex, Ita Elewa, Ikorodu, Lagos.<br>
        WhatsApp: <a href="https://wa.me/2347017597650">07017597650</a>
      </p>
    </div>
  </div>
</body>
</html>
HTML;

        $textBody = "LAD Global Travels - Official Payment Receipt\n\n" .
                    "Payment Status: PAID / VERIFIED\n" .
                    "Booking Ref: {$booking['reference']}\n" .
                    "Payment Ref: {$payment['payment_reference']}\n" .
                    "Package: {$package['title']}\n" .
                    "Amount Paid: ₦{$amount} {$curr}\n" .
                    "Channel: {$provider}\n\n" .
                    "NOTICE: This document certifies payment received only. It is not an issued flight ticket or travel confirmation.\n" .
                    "Our operations desk will contact you shortly.\n";

        return self::send($to, $subject, $htmlBody, $textBody);
    }

    /**
     * Send alert to admin inbox upon successful payment settlement.
     */
    public static function sendAdminPaymentNotification(
        array $booking,
        array $payment,
        array $package
    ): bool {
        $routing = SettingService::getEmailRouting();
        $adminEmail = $routing['payments'] ?? (string)Env::get('ADMIN_NOTIFICATION_EMAIL', 'admin@ladglobal.com');
        $bRef = Security::e($booking['reference']);
        $pRef = Security::e($payment['payment_reference']);
        $name = Security::e($booking['customer_name']);
        $pkgTitle = Security::e($package['title'] ?? 'Tour Package');
        $amount = number_format((float)$payment['amount'], 2);
        $curr = Security::e($payment['currency']);
        $provider = strtoupper((string)$payment['provider']);

        $cPhone = Security::e($booking['customer_phone'] ?? 'N/A');

        $subject = "[Payment Verified] ₦{$amount} {$curr} for Booking {$bRef} ({$provider})";

        $htmlBody = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>{$subject}</title></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333333; margin: 0; padding: 20px; background-color: #f7f9fc;">
  <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden;">
    <div style="background-color: #15803d; padding: 20px; color: #ffffff;">
      <h2 style="margin: 0; font-size: 1.25rem;">LAD Global Travels — Finance Notification</h2>
      <p style="margin: 4px 0 0; font-size: 0.9rem;">Payment Successfully Verified</p>
    </div>
    <div style="padding: 24px;">
      <p>A customer payment has been confirmed and settled into the system:</p>
      <ul style="font-size: 0.95rem; line-height: 1.8;">
        <li><strong>Booking Ref:</strong> {$bRef}</li>
        <li><strong>Payment Ref:</strong> {$pRef}</li>
        <li><strong>Customer:</strong> {$name} (<a href="tel:{$cPhone}">{$cPhone}</a>)</li>
        <li><strong>Package:</strong> {$pkgTitle}</li>
        <li><strong>Travellers:</strong> {$booking['travellers_count']}</li>
        <li><strong>Amount Settled:</strong> ₦{$amount} {$curr}</li>
        <li><strong>Channel:</strong> {$provider}</li>
      </ul>
      <p><a href="/admin/bookings" style="background-color: #092A60; color: #ffffff; padding: 10px 18px; text-decoration: none; border-radius: 4px; display: inline-block; font-weight: bold;">View in Admin Portal</a></p>
    </div>
  </div>
</body>
</html>
HTML;

        $textBody = "LAD Global Travels - Payment Verified Alert\n\n" .
                    "Booking Ref: {$booking['reference']}\n" .
                    "Payment Ref: {$payment['payment_reference']}\n" .
                    "Customer: {$booking['customer_name']} ({$cPhone})\n" .
                    "Amount: ₦{$amount} {$curr}\n" .
                    "Channel: {$provider}\n";

        return self::send($adminEmail, $subject, $htmlBody, $textBody);
    }

    /**
     * Dispatch an email via configured transport (SMTP socket or native mail()).
     * Enforces best-effort delivery: logs failures, never throws exceptions.
     *
     * @param string $to Recipient email address
     * @param string $subject Subject line
     * @param string $htmlBody HTML content
     * @param string $textBody Plain text fallback
     * @return bool True if successfully sent, false otherwise
     */
    public static function send(string $to, string $subject, string $htmlBody, string $textBody): bool
    {
        $to = trim($to);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            self::logError("Invalid recipient email address: '{$to}'");
            return false;
        }

        $fromAddress = (string)SettingService::get('mail_from_address', (string)Env::get('SMTP_FROM_ADDRESS', 'info@ladglobal.com'));
        $fromName = (string)SettingService::get('mail_from_name', (string)Env::get('SMTP_FROM_NAME', 'LAD Global Travels and Tours'));
        $smtpHost = (string)Env::get('SMTP_HOST', '');
        $smtpPort = (int)Env::get('SMTP_PORT', 587);
        $smtpUser = (string)Env::get('SMTP_USERNAME', '');
        $smtpPass = (string)Env::get('SMTP_PASSWORD', '');
        $smtpEnc  = strtolower((string)Env::get('SMTP_ENCRYPTION', 'tls'));

        // If SMTP credentials are configured and host is not placeholder, attempt native socket SMTP
        if ($smtpHost !== '' && !str_contains($smtpHost, 'placeholder') && $smtpUser !== '' && !str_contains($smtpPass, 'placeholder')) {
            try {
                return self::sendViaSocketSmtp($to, $subject, $htmlBody, $textBody, [
                    'host' => $smtpHost,
                    'port' => $smtpPort,
                    'user' => $smtpUser,
                    'pass' => $smtpPass,
                    'enc'  => $smtpEnc,
                    'from' => $fromAddress,
                    'name' => $fromName
                ]);
            } catch (Throwable $e) {
                self::logError("SMTP socket dispatch failed to {$to}: " . $e->getMessage());
                // Fall through to native mail() attempt
            }
        }

        // Fallback: Native PHP mail()
        try {
            $boundary = "==Multipart_Boundary_x" . md5((string)time()) . "x";
            $headers = [
                "From: {$fromName} <{$fromAddress}>",
                "Reply-To: {$fromAddress}",
                "MIME-Version: 1.0",
                "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
                "X-Mailer: LAD-Global-PHP-Native"
            ];

            $message = "--{$boundary}\r\n" .
                       "Content-Type: text/plain; charset=\"UTF-8\"\r\n" .
                       "Content-Transfer-Encoding: 7bit\r\n\r\n" .
                       $textBody . "\r\n\r\n" .
                       "--{$boundary}\r\n" .
                       "Content-Type: text/html; charset=\"UTF-8\"\r\n" .
                       "Content-Transfer-Encoding: 7bit\r\n\r\n" .
                       $htmlBody . "\r\n\r\n" .
                       "--{$boundary}--";

            $sent = @mail($to, $subject, $message, implode("\r\n", $headers));
            if (!$sent) {
                self::logError("Native mail() returned false for {$to} (Check local sendmail/MTA configuration)");
            }
            return (bool)$sent;
        } catch (Throwable $e) {
            self::logError("Native mail() dispatch exception to {$to}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Native lightweight SMTP socket client (zero external packages).
     */
    private static function sendViaSocketSmtp(string $to, string $subject, string $htmlBody, string $textBody, array $cfg): bool
    {
        $host = $cfg['enc'] === 'ssl' ? 'ssl://' . $cfg['host'] : $cfg['host'];
        $timeout = 10;

        $socket = @fsockopen($host, $cfg['port'], $errno, $errstr, $timeout);
        if (!$socket) {
            throw new \RuntimeException("Could not connect to SMTP host {$host}:{$cfg['port']} ({$errstr})");
        }

        stream_set_timeout($socket, $timeout);

        $read = function () use ($socket): string {
            $response = '';
            while ($str = fgets($socket, 515)) {
                $response .= $str;
                if (substr($str, 3, 1) === ' ') break;
            }
            return $response;
        };

        $write = function (string $cmd) use ($socket, $read): string {
            fputs($socket, $cmd . "\r\n");
            return $read();
        };

        $res = $read();
        if (!str_starts_with($res, '220')) {
            fclose($socket);
            throw new \RuntimeException("SMTP Greeting failed: {$res}");
        }

        // EHLO
        $res = $write("EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost'));

        // STARTTLS if requested
        if ($cfg['enc'] === 'tls') {
            $res = $write("STARTTLS");
            if (str_starts_with($res, '220')) {
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT)) {
                    fclose($socket);
                    throw new \RuntimeException("Failed to negotiate TLS crypto on SMTP socket.");
                }
                $write("EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
            }
        }

        // AUTH LOGIN
        $res = $write("AUTH LOGIN");
        if (str_starts_with($res, '334')) {
            $write(base64_encode($cfg['user']));
            $res = $write(base64_encode($cfg['pass']));
            if (!str_starts_with($res, '235')) {
                fclose($socket);
                throw new \RuntimeException("SMTP Authentication failed: {$res}");
            }
        }

        // MAIL FROM & RCPT TO
        $write("MAIL FROM: <{$cfg['from']}>");
        $res = $write("RCPT TO: <{$to}>");
        if (!str_starts_with($res, '250')) {
            fclose($socket);
            throw new \RuntimeException("SMTP RCPT TO rejected: {$res}");
        }

        // DATA
        $write("DATA");
        $boundary = "==Multipart_Boundary_x" . md5((string)time()) . "x";

        $headers = "From: =?UTF-8?B?" . base64_encode($cfg['name']) . "?= <{$cfg['from']}>\r\n" .
                   "To: <{$to}>\r\n" .
                   "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n" .
                   "MIME-Version: 1.0\r\n" .
                   "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n\r\n";

        $content = "--{$boundary}\r\n" .
                   "Content-Type: text/plain; charset=UTF-8\r\n" .
                   "Content-Transfer-Encoding: base64\r\n\r\n" .
                   chunk_split(base64_encode($textBody)) . "\r\n" .
                   "--{$boundary}\r\n" .
                   "Content-Type: text/html; charset=UTF-8\r\n" .
                   "Content-Transfer-Encoding: base64\r\n\r\n" .
                   chunk_split(base64_encode($htmlBody)) . "\r\n" .
                   "--{$boundary}--\r\n.\r\n";

        $res = $write($headers . $content);
        $write("QUIT");
        fclose($socket);

        return str_starts_with($res, '250');
    }

    /**
     * Log structured email warning to error log and storage log.
     */
    private static function logError(string $message): void
    {
        $logEntry = "[" . date('Y-m-d H:i:s') . "] [MailService Warning] " . $message . PHP_EOL;
        error_log($logEntry);

        $logDir = defined('STORAGE_PATH') ? STORAGE_PATH . '/logs' : dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        @file_put_contents($logDir . '/mail.log', $logEntry, FILE_APPEND | LOCK_EX);
    }
}
