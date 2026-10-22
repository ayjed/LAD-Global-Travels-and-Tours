<?php
/**
 * Booking Status Overview View
 * @var array $booking The booking entity
 * @var array $payments List of payment attempts
 */
use App\Core\Security;

$bRef = Security::e($booking['reference']);
$packageTitle = Security::e($booking['package_title']);
$destination = Security::e($booking['destination_city'] . ', ' . $booking['destination_country']);
$customerName = Security::e($booking['customer_name']);
$totalAmount = number_format((float)$booking['total_amount'], 2);
$currency = Security::e($booking['currency']);
$bookingStatus = strtoupper((string)$booking['booking_status']);

$latestPayment = $booking['latest_payment'] ?? null;
$paymentStatus = strtoupper((string)($latestPayment['status'] ?? 'NONE'));

$waText = rawurlencode("Hello LAD Global Travels, I would like an update on my Tour Booking Ref: {$booking['reference']}.");
$waUrl = "https://wa.me/2347017597650?text={$waText}";
?>

<section class="section" style="padding-top: var(--space-8); padding-bottom: var(--space-12);">
  <div class="container" style="max-width: 800px;">

    <!-- Breadcrumb -->
    <nav class="breadcrumb" aria-label="Breadcrumbs" style="margin-bottom: var(--space-4);">
      <a href="/">Home</a>
      <span class="breadcrumb-separator">/</span>
      <a href="/packages">Tour Packages</a>
      <span class="breadcrumb-separator">/</span>
      <span class="breadcrumb-current">Booking Status</span>
    </nav>

    <!-- Top Summary Card -->
    <div style="background-color: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-6); margin-bottom: var(--space-6); box-shadow: var(--shadow-sm);">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--space-4);">
        <div>
          <span style="font-size: var(--font-size-xs); color: var(--color-text-muted); text-transform: uppercase; font-weight: 700;">Booking Reference</span>
          <h1 style="color: var(--color-primary); font-size: 1.75rem; margin: 4px 0;"><?= $bRef ?></h1>
          <p style="color: var(--color-text-muted); font-size: var(--font-size-sm); margin: 0;">
            Booked for: <strong><?= $packageTitle ?></strong> (<?= $destination ?>)
          </p>
        </div>
        <div style="text-align: right;">
          <div style="margin-bottom: 6px;">
            <span style="font-size: var(--font-size-xs); color: #64748B; display: block; text-transform: uppercase; font-weight: 600;">Payment Status</span>
            <?php if ($paymentStatus === 'PAID'): ?>
              <span class="badge" style="background-color: #DCFCE7; color: #15803D; font-weight: 800; padding: 4px 10px; border-radius: 4px;">PAID / VERIFIED</span>
            <?php elseif ($paymentStatus === 'PENDING'): ?>
              <span class="badge" style="background-color: #FEF3C7; color: #92400E; font-weight: 800; padding: 4px 10px; border-radius: 4px;">PENDING RECONCILIATION</span>
            <?php else: ?>
              <span class="badge" style="background-color: #FEE2E2; color: #B91C1C; font-weight: 800; padding: 4px 10px; border-radius: 4px;"><?= $paymentStatus ?></span>
            <?php endif; ?>
          </div>
          <div>
            <span style="font-size: var(--font-size-xs); color: #64748B; display: block; text-transform: uppercase; font-weight: 600;">Travel Status</span>
            <?php if ($bookingStatus === 'CONFIRMED'): ?>
              <span class="badge" style="background-color: #DBEAFE; color: #1D4ED8; font-weight: 800; padding: 4px 10px; border-radius: 4px;">TRAVEL CONFIRMED</span>
            <?php else: ?>
              <span class="badge" style="background-color: #F1F5F9; color: #475569; font-weight: 800; padding: 4px 10px; border-radius: 4px;">PENDING LOGISTICAL REVIEW</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Booking Overview Details -->
    <div style="background-color: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-6); margin-bottom: var(--space-6); box-shadow: var(--shadow-sm);">
      <h2 style="font-size: 1.15rem; color: var(--color-primary); margin-top: 0; margin-bottom: var(--space-4); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-2);">
        Booking Information
      </h2>

      <table style="width: 100%; border-collapse: collapse; font-size: var(--font-size-sm);">
        <tbody>
          <tr><td style="padding: 8px 0; color: #64748B; width: 35%;">Lead Traveller:</td><td style="padding: 8px 0; font-weight: 600;"><?= $customerName ?></td></tr>
          <tr><td style="padding: 8px 0; color: #64748B;">Contact Email:</td><td style="padding: 8px 0;"><?= Security::e($booking['customer_email']) ?></td></tr>
          <tr><td style="padding: 8px 0; color: #64748B;">Contact Phone:</td><td style="padding: 8px 0;"><?= Security::e($booking['customer_phone']) ?></td></tr>
          <tr><td style="padding: 8px 0; color: #64748B;">Travellers Count:</td><td style="padding: 8px 0; font-weight: 600;"><?= (int)$booking['travellers_count'] ?> person(s)</td></tr>
          <tr><td style="padding: 8px 0; color: #64748B;">Total Amount:</td><td style="padding: 8px 0; font-weight: 800; color: #15803D; font-size: 1.1rem;">₦<?= $totalAmount ?> <?= $currency ?></td></tr>
          <tr><td style="padding: 8px 0; color: #64748B;">Created Date:</td><td style="padding: 8px 0;"><?= date('d M Y, H:i T', strtotime($booking['created_at'])) ?></td></tr>
        </tbody>
      </table>
    </div>

    <!-- Payment Records & Actions -->
    <div style="background-color: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-6); margin-bottom: var(--space-6); box-shadow: var(--shadow-sm);">
      <h2 style="font-size: 1.15rem; color: var(--color-primary); margin-top: 0; margin-bottom: var(--space-4); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-2);">
        Payment History
      </h2>

      <?php if (empty($payments)): ?>
        <p style="color: var(--color-text-muted); font-size: var(--font-size-sm);">No payment attempts recorded.</p>
      <?php else: ?>
        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; font-size: var(--font-size-sm);">
            <thead>
              <tr style="border-bottom: 2px solid var(--color-border); text-align: left; color: #64748B;">
                <th style="padding: 8px;">Reference</th>
                <th style="padding: 8px;">Method</th>
                <th style="padding: 8px;">Amount</th>
                <th style="padding: 8px;">Status</th>
                <th style="padding: 8px; text-align: right;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($payments as $p): ?>
                <?php $st = strtoupper((string)$p['status']); ?>
                <tr style="border-bottom: 1px solid var(--color-border);">
                  <td style="padding: 10px 8px; font-family: monospace; font-weight: 600;"><?= Security::e($p['payment_reference']) ?></td>
                  <td style="padding: 10px 8px;"><?= ucfirst((string)$p['provider']) ?></td>
                  <td style="padding: 10px 8px; font-weight: 600;">₦<?= number_format((float)$p['amount'], 2) ?></td>
                  <td style="padding: 10px 8px;">
                    <?php if ($st === 'PAID'): ?>
                      <span style="color: #15803D; font-weight: 700;">● Paid</span>
                    <?php elseif ($st === 'PENDING'): ?>
                      <span style="color: #D97706; font-weight: 700;">● Pending</span>
                    <?php else: ?>
                      <span style="color: #DC2626; font-weight: 700;">● <?= $st ?></span>
                    <?php endif; ?>
                  </td>
                  <td style="padding: 10px 8px; text-align: right;">
                    <?php if ($st === 'PAID'): ?>
                      <a href="/booking/<?= $bRef ?>/receipt" class="btn btn-outline" style="padding: 4px 10px; font-size: var(--font-size-xs);">View Receipt</a>
                    <?php elseif ($p['provider'] === 'manual'): ?>
                      <a href="/booking/<?= $bRef ?>/transfer" class="btn btn-primary" style="padding: 4px 10px; font-size: var(--font-size-xs);">Transfer Details</a>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- Communication & WhatsApp Help -->
    <div style="display: flex; gap: var(--space-4); justify-content: space-between; align-items: center; flex-wrap: wrap;">
      <a href="/packages" class="btn btn-outline" style="padding: 12px 20px;">
        ← Back to Packages
      </a>
      <a href="<?= $waUrl ?>" target="_blank" class="btn btn-primary" style="background-color: #25D366; border-color: #25D366; color: #FFFFFF; padding: 12px 24px; font-weight: 700;">
        💬 Contact Support on WhatsApp
      </a>
    </div>

  </div>
</section>
