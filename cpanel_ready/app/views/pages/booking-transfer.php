<?php
/**
 * Manual Bank Transfer Instructions View
 * @var array $booking The booking entity
 * @var array $payment The pending payment entity
 * @var array $bankDetails Verified bank credentials
 */
use App\Core\Security;

$bRef = Security::e($booking['reference']);
$pRef = Security::e($payment['payment_reference']);
$amount = number_format((float)$payment['amount'], 2);
$currency = Security::e($payment['currency']);
$packageTitle = Security::e($booking['package_title']);
$customerName = Security::e($booking['customer_name']);

$waText = rawurlencode("Hello LAD Global Travels, I have initiated a manual bank transfer for Booking Ref: {$booking['reference']} (Payment Ref: {$payment['payment_reference']}).");
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
      <span class="breadcrumb-current">Bank Transfer Instructions</span>
    </nav>

    <!-- Header Box -->
    <div style="background-color: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-6); margin-bottom: var(--space-6); box-shadow: var(--shadow-sm); text-align: center;">
      <div style="display: inline-block; background-color: #FEF3C7; color: #92400E; font-weight: 700; font-size: var(--font-size-xs); padding: 4px 12px; border-radius: 999px; margin-bottom: var(--space-2); text-transform: uppercase;">
        Pending Manual Bank Transfer
      </div>
      <h1 style="color: var(--color-primary); font-size: 1.75rem; margin: 0 0 var(--space-2);">Bank Transfer Instructions</h1>
      <p style="color: var(--color-text-muted); font-size: var(--font-size-sm); margin: 0;">
        Booking Reference: <strong style="color: var(--color-primary);"><?= $bRef ?></strong> &bull; Customer: <?= $customerName ?>
      </p>
    </div>

    <!-- Payment Details Card -->
    <div style="background-color: var(--color-surface); border: 2px solid #E2E8F0; border-radius: var(--radius-lg); padding: var(--space-6); margin-bottom: var(--space-6); box-shadow: var(--shadow-sm);">
      <h2 style="font-size: 1.15rem; color: var(--color-primary); margin-top: 0; margin-bottom: var(--space-4); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-2);">
        Official Corporate Bank Account
      </h2>

      <table style="width: 100%; border-collapse: collapse; font-size: 1rem; margin-bottom: var(--space-4);">
        <tbody>
          <tr>
            <td style="padding: 10px 0; color: #64748B; width: 40%; border-bottom: 1px solid #F1F5F9;">Bank Name:</td>
            <td style="padding: 10px 0; font-weight: 700; color: var(--color-primary); border-bottom: 1px solid #F1F5F9;">
              <?= Security::e($bankDetails['bank_name']) ?>
            </td>
          </tr>
          <tr>
            <td style="padding: 10px 0; color: #64748B; border-bottom: 1px solid #F1F5F9;">Account Name:</td>
            <td style="padding: 10px 0; font-weight: 700; color: var(--color-primary); border-bottom: 1px solid #F1F5F9;">
              <?= Security::e($bankDetails['account_name']) ?>
            </td>
          </tr>
          <tr>
            <td style="padding: 12px 0; color: #64748B; border-bottom: 1px solid #F1F5F9;">Account Number:</td>
            <td style="padding: 12px 0; font-weight: 800; font-size: 1.35rem; color: #135EB0; letter-spacing: 1px; border-bottom: 1px solid #F1F5F9;">
              <?= Security::e($bankDetails['account_number']) ?>
            </td>
          </tr>
          <tr>
            <td style="padding: 10px 0; color: #64748B; border-bottom: 1px solid #F1F5F9;">Exact Amount:</td>
            <td style="padding: 10px 0; font-weight: 800; font-size: 1.25rem; color: #15803D; border-bottom: 1px solid #F1F5F9;">
              ₦<?= $amount ?> <?= $currency ?>
            </td>
          </tr>
          <tr>
            <td style="padding: 12px 0; color: #DC2626; font-weight: 700;">Mandatory Transfer Memo / Narration:</td>
            <td style="padding: 12px 0; font-weight: 800; font-size: 1.25rem; color: #DC2626; letter-spacing: 1px;">
              <?= $pRef ?>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Critical Narration Warning -->
      <div style="background-color: #FEF2F2; border: 1px solid #FCA5A5; border-radius: var(--radius-md); padding: var(--space-4); margin-bottom: var(--space-4); color: #991B1B; font-size: var(--font-size-sm);">
        <strong>⚠️ CRITICAL INSTRUCTION:</strong> You must enter <strong><?= $pRef ?></strong> into your bank app's transfer remarks/narration field. Our finance desk matches incoming bank credits exclusively using this reference.
      </div>

      <!-- SOT Section 8.2 Compliance Disclosure -->
      <div style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: var(--radius-md); padding: var(--space-4); color: #475569; font-size: var(--font-size-xs); line-height: 1.6;">
        <strong>RECONCILIATION POLICY:</strong> Bank transfers are <strong>never automatically verified</strong>. Our administrator manually verifies funds in our corporate bank statement before marking payments as paid and issuing your official payment receipt. Falsified transfer claims are automatically rejected.
      </div>
    </div>

    <!-- Optional Transfer Memo Submission Form -->
    <div style="background-color: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-6); margin-bottom: var(--space-6); box-shadow: var(--shadow-sm);">
      <h3 style="font-size: 1.1rem; color: var(--color-primary); margin-top: 0; margin-bottom: var(--space-2);">
        Report Your Transfer Narration (Optional)
      </h3>
      <p style="color: var(--color-text-muted); font-size: var(--font-size-xs); margin-bottom: var(--space-4);">
        If your bank app generated a specific session ID or sender account name, you may note it here to expedite manual statement reconciliation:
      </p>

      <form action="/booking/<?= $bRef ?>/transfer-memo" method="POST">
        <?= Security::csrfField() ?>
        <div style="display: flex; gap: var(--space-3); flex-wrap: wrap;">
          <input type="text" name="transfer_memo" placeholder="e.g. Sent from GTBank Adebayo Adeleke Ref 10098483" required
                 style="flex: 1; min-width: 260px; padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--color-border); font-size: var(--font-size-sm);">
          <button type="submit" class="btn btn-outline" style="padding: 10px 20px; font-weight: 600; white-space: nowrap;">
            Save Narration Note
          </button>
        </div>
      </form>
    </div>

    <!-- Action Buttons -->
    <div style="display: flex; gap: var(--space-4); justify-content: space-between; flex-wrap: wrap;">
      <a href="/booking/<?= $bRef ?>" class="btn btn-outline" style="padding: 12px 20px;">
        ← View Booking Status
      </a>
      <a href="<?= $waUrl ?>" target="_blank" class="btn btn-primary" style="background-color: #25D366; border-color: #25D366; color: #FFFFFF; padding: 12px 24px; font-weight: 700;">
        💬 Notify Us on WhatsApp
      </a>
    </div>

  </div>
</section>
