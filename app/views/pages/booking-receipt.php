<?php
/**
 * Official Payment Receipt View
 * @var array $booking The booking entity
 * @var array $payment The paid payment entity
 */
use App\Core\Security;

$bRef = Security::e($booking['reference']);
$pRef = Security::e($payment['payment_reference']);
$amount = number_format((float)$payment['amount'], 2);
$currency = Security::e($payment['currency']);
$packageTitle = Security::e($booking['package_title']);
$destination = Security::e($booking['destination_city'] . ', ' . $booking['destination_country']);
$customerName = Security::e($booking['customer_name']);
$customerEmail = Security::e($booking['customer_email']);
$customerPhone = Security::e($booking['customer_phone']);
$provider = strtoupper((string)$payment['provider']);
$verifiedAt = !empty($payment['verified_at']) ? date('d M Y, H:i T', strtotime($payment['verified_at'])) : date('d M Y, H:i T');
?>

<style>
@media print {
  /* Strictly hide site header, footer, WhatsApp button, navigation drawer, and buttons */
  header.site-header,
  footer.site-footer,
  .site-header,
  .site-footer,
  .mobile-menu-toggle,
  .mobile-nav-drawer,
  .whatsapp-floating,
  .skip-to-content,
  .no-print,
  .no-print * {
    display: none !important;
    visibility: hidden !important;
    height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  /* Reset document for clean paper printing */
  html,
  body {
    background: #FFFFFF !important;
    background-color: #FFFFFF !important;
    color: #0F172A !important;
    font-size: 11pt !important;
    line-height: 1.4 !important;
    margin: 0 !important;
    padding: 0 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  main#mainContent,
  .section,
  .container {
    padding: 0 !important;
    margin: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
    background: transparent !important;
    box-shadow: none !important;
  }

  .receipt-paper {
    border: 1px solid #94A3B8 !important;
    box-shadow: none !important;
    border-radius: 4px !important;
    padding: 10mm 12mm !important;
    margin: 0 auto !important;
    max-width: 100% !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
    background: #FFFFFF !important;
  }

  @page {
    margin: 10mm 12mm;
    size: auto;
  }
}
</style>

<section class="section" style="padding-top: var(--space-8); padding-bottom: var(--space-12);">
  <div class="container" style="max-width: 750px;">

    <!-- Print Navigation -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4);" class="no-print">
      <a href="/booking/<?= $bRef ?>" class="btn btn-outline" style="padding: 8px 16px; font-size: var(--font-size-sm);">
        ← Back to Booking Status
      </a>
      <button onclick="window.print()" class="btn btn-primary" style="padding: 8px 18px; font-size: var(--font-size-sm); font-weight: 600;">
        🖨️ Print Receipt
      </button>
    </div>

    <!-- Official Receipt Document Paper -->
    <div class="receipt-paper" style="background-color: #FFFFFF; border: 1px solid #CBD5E1; border-radius: var(--radius-lg); padding: var(--space-8); box-shadow: var(--shadow-md); position: relative;">

      <!-- Paid Stamp -->
      <div style="position: absolute; top: 30px; right: 30px; border: 3px solid #16A34A; color: #16A34A; font-size: 1.25rem; font-weight: 900; padding: 6px 16px; border-radius: 8px; text-transform: uppercase; transform: rotate(-5deg); letter-spacing: 2px;">
        ✓ PAID
      </div>

      <!-- Receipt Header -->
      <div style="border-bottom: 2px solid var(--color-primary); padding-bottom: var(--space-4); margin-bottom: var(--space-6);">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
          <img src="/assets/images/logo-160.png" alt="LAD Global Travels and Tours" style="width: 52px; height: 52px; object-fit: contain; border-radius: 6px; border: 1px solid #E2E8F0; padding: 2px;">
          <div>
            <h1 style="font-size: 1.35rem; color: var(--color-primary); margin: 0; line-height: 1.2;">LAD Global Travels and Tours</h1>
            <span style="font-size: var(--font-size-xs); color: #64748B;">Official Transaction Receipt &bull; RC Certified</span>
          </div>
        </div>
        <p style="font-size: var(--font-size-xs); color: #64748B; margin: 4px 0 0;">
          SF41, Ikorodu Complex, Ita Elewa, Adjacent to High Court, Ikorodu, Lagos, Nigeria &bull; Tel: 07017597650
        </p>
      </div>

      <!-- Receipt Metadata Grid -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-6); font-size: var(--font-size-sm);">
        <div>
          <span style="color: #64748B; font-size: var(--font-size-xs); text-transform: uppercase; font-weight: 600; display: block;">Booking Reference</span>
          <strong style="color: var(--color-primary); font-size: 1.1rem;"><?= $bRef ?></strong>
        </div>
        <div>
          <span style="color: #64748B; font-size: var(--font-size-xs); text-transform: uppercase; font-weight: 600; display: block;">Payment Reference</span>
          <strong style="color: #0F172A; font-family: monospace; font-size: 1rem;"><?= $pRef ?></strong>
        </div>
        <div>
          <span style="color: #64748B; font-size: var(--font-size-xs); text-transform: uppercase; font-weight: 600; display: block;">Settlement Date</span>
          <strong style="color: #0F172A;"><?= $verifiedAt ?></strong>
        </div>
        <div>
          <span style="color: #64748B; font-size: var(--font-size-xs); text-transform: uppercase; font-weight: 600; display: block;">Payment Channel</span>
          <strong style="color: #0F172A;"><?= $provider === 'MANUAL' ? 'Corporate Bank Transfer' : 'Paystack Online Gateway' ?></strong>
        </div>
      </div>

      <!-- Customer Details -->
      <div style="background-color: #F8FAFC; border-radius: var(--radius-md); padding: var(--space-4); margin-bottom: var(--space-6); font-size: var(--font-size-sm);">
        <span style="font-size: var(--font-size-xs); color: #64748B; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 6px;">Billed To</span>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: var(--space-2);">
          <div><strong>Name:</strong> <?= $customerName ?></div>
          <div><strong>Email:</strong> <?= $customerEmail ?></div>
          <div><strong>Phone:</strong> <?= $customerPhone ?></div>
        </div>
      </div>

      <!-- Line Items Table -->
      <table style="width: 100%; border-collapse: collapse; font-size: var(--font-size-sm); margin-bottom: var(--space-6);">
        <thead>
          <tr style="background-color: #F1F5F9; border-bottom: 2px solid #CBD5E1;">
            <th style="padding: 10px 12px; text-align: left; color: #475569;">Description</th>
            <th style="padding: 10px 12px; text-align: center; color: #475569;">Travellers</th>
            <th style="padding: 10px 12px; text-align: right; color: #475569;">Unit Price</th>
            <th style="padding: 10px 12px; text-align: right; color: #475569;">Total Paid</th>
          </tr>
        </thead>
        <tbody>
          <tr style="border-bottom: 1px solid #E2E8F0;">
            <td style="padding: 14px 12px;">
              <strong style="color: var(--color-primary); display: block;"><?= $packageTitle ?></strong>
              <span style="font-size: var(--font-size-xs); color: #64748B;">Destination: <?= $destination ?> &bull; <?= (int)$booking['duration_days'] ?> Days Tour</span>
            </td>
            <td style="padding: 14px 12px; text-align: center; font-weight: 600;">
              <?= (int)$booking['travellers_count'] ?>
            </td>
            <td style="padding: 14px 12px; text-align: right; color: #475569;">
              ₦<?= number_format((float)$booking['unit_price'], 2) ?>
            </td>
            <td style="padding: 14px 12px; text-align: right; font-weight: 800; font-size: 1rem; color: #166534;">
              ₦<?= $amount ?>
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3" style="padding: 14px 12px; text-align: right; font-weight: 700; color: #0F172A; font-size: 1rem;">
              Total Amount Settled:
            </td>
            <td style="padding: 14px 12px; text-align: right; font-weight: 900; font-size: 1.25rem; color: #166534;">
              ₦<?= $amount ?> <?= $currency ?>
            </td>
          </tr>
        </tfoot>
      </table>

      <!-- Mandatory SOT Section 8.4 Statutory Travel Notice (D11) -->
      <div style="background-color: #FEF3C7; border: 1px solid #FDE68A; border-left: 4px solid #D97706; border-radius: var(--radius-sm); padding: var(--space-4); font-size: var(--font-size-xs); color: #92400E; line-height: 1.6; margin-bottom: var(--space-6);">
        <strong>STATUTORY TRAVEL NOTICE (D11):</strong><br>
        This receipt certifies that <strong>funds have been received and verified</strong> for Booking Ref <strong><?= $bRef ?></strong>. It is <strong>NOT an issued airline ticket, hotel check-in voucher, or travel confirmation</strong>. Package logistical fulfillment, flight reservations, and scheduled dates are subject to formal availability confirmation and scheduling by LAD Global Travels consultants.
      </div>

      <!-- Footer Sign-off -->
      <div style="border-top: 1px solid #E2E8F0; padding-top: var(--space-4); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--space-2); font-size: var(--font-size-xs); color: #64748B;">
        <span>Generated by LAD Global Travels Automated System</span>
        <span>For enquiries: <a href="https://wa.me/2347017597650" style="color: var(--color-primary); font-weight: 600;">07017597650</a></span>
      </div>

    </div>

  </div>
</section>
