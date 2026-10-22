<?php
/**
 * Package Direct Booking Form View
 * @var array $package Tour package record
 * @var array $bankDetails Verified bank account details
 * @var bool $paystackConfigured Paystack readiness flag
 * @var string $paystackPublicKey
 * @var array|null $errors Validation error messages
 * @var array|null $old Submitted form data
 * @var int $defaultTravellers
 * @var bool|null $bankEnabled
 * @var bool|null $paystackEnabled
 * @var array|null $enabledMethods
 */
use App\Core\Security;

$bankEnabled = isset($bankEnabled) ? (bool)$bankEnabled : true;
$paystackEnabled = isset($paystackEnabled) ? (bool)$paystackEnabled : true;
$hasPaymentMethods = $bankEnabled || $paystackEnabled;

$selectedMethod = $old['payment_method'] ?? '';
if (!in_array($selectedMethod, ['manual', 'paystack'], true)) {
    if ($bankEnabled) {
        $selectedMethod = 'manual';
    } elseif ($paystackEnabled) {
        $selectedMethod = 'paystack';
    }
} else {
    if ($selectedMethod === 'manual' && !$bankEnabled) {
        $selectedMethod = $paystackEnabled ? 'paystack' : '';
    } elseif ($selectedMethod === 'paystack' && !$paystackEnabled) {
        $selectedMethod = $bankEnabled ? 'manual' : '';
    }
}

$unitPrice = (float)($package['price_amount'] ?? $package['price'] ?? 0);
$travellers = (int)($old['travellers_count'] ?? $defaultTravellers ?? 1);
$totalEstimate = $unitPrice * $travellers;
$packageSlug = Security::e($package['slug'] ?? '');
$packageTitle = Security::e($package['title'] ?? '');
$city = $package['destination_city'] ?? $package['city'] ?? '';
$country = $package['destination_country'] ?? $package['country'] ?? '';
$destination = Security::e(trim($city . ($city && $country ? ', ' : '') . $country));
?>

<section class="section" style="padding-top: var(--space-8); padding-bottom: var(--space-12);">
  <div class="container" style="max-width: 900px;">

    <!-- Breadcrumb -->
    <nav class="breadcrumb" aria-label="Breadcrumbs" style="margin-bottom: var(--space-4);">
      <a href="/">Home</a>
      <span class="breadcrumb-separator">/</span>
      <a href="/packages">Tour Packages</a>
      <span class="breadcrumb-separator">/</span>
      <a href="/packages/<?= $packageSlug ?>"><?= $packageTitle ?></a>
      <span class="breadcrumb-separator">/</span>
      <span class="breadcrumb-current">Book Package</span>
    </nav>

    <div style="margin-bottom: var(--space-6);">
      <span class="service-badge" style="background-color: var(--color-surface-alt); color: var(--color-primary); font-weight: 700; margin-bottom: var(--space-2); display: inline-block;">
        Direct Tour Booking
      </span>
      <h1 style="color: var(--color-primary); margin-bottom: var(--space-2); font-size: 2rem;">Secure Package Booking</h1>
      <p style="color: var(--color-text-muted); margin: 0;">
        Complete your booking details below. Pricing is calculated directly per person and locked in upon submission.
      </p>
    </div>

    <!-- Booking Grid -->
    <div style="display: grid; grid-template-columns: 1fr; gap: var(--space-6);">

      <!-- Package Summary Card -->
      <div style="background-color: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-6); box-shadow: var(--shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--space-4); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-4); margin-bottom: var(--space-4);">
          <div>
            <span style="font-size: var(--font-size-xs); font-weight: 700; color: var(--color-text-muted); text-transform: uppercase;">Selected Package</span>
            <h2 style="font-size: 1.25rem; color: var(--color-primary); margin: 4px 0;"><?= $packageTitle ?></h2>
            <p style="color: var(--color-text-muted); font-size: var(--font-size-sm); margin: 0;">📍 <?= $destination ?> &bull; ⏱️ <?= (int)($package['duration_days'] ?? 0) ?> Days</p>
          </div>
          <div style="text-align: right;">
            <span style="font-size: var(--font-size-xs); color: var(--color-text-muted); display: block;">Price per Person</span>
            <span style="font-size: 1.35rem; font-weight: 800; color: var(--color-primary);">₦<?= number_format($unitPrice, 2) ?></span>
          </div>
        </div>

        <!-- Dynamic Live Estimator Notice -->
        <div style="background-color: #F8FAFC; border-radius: var(--radius-md); padding: var(--space-4); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--space-2);">
          <div>
            <span style="font-size: var(--font-size-xs); color: #64748B; font-weight: 600; text-transform: uppercase;">Estimated Total Due</span>
            <div style="font-size: 1.5rem; font-weight: 800; color: #166534;" id="liveTotalDisplay">
              ₦<?= number_format($totalEstimate, 2) ?>
            </div>
          </div>
          <div style="font-size: var(--font-size-xs); color: var(--color-text-muted); max-width: 320px;">
            Calculated server-side based on traveller count. No hidden booking surcharges.
          </div>
        </div>
      </div>

      <!-- Booking Form -->
      <form action="/packages/<?= $packageSlug ?>/book" method="POST" style="background-color: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-6); box-shadow: var(--shadow-sm);">
        <?= Security::csrfField() ?>

        <!-- Anti-Spam Honeypot Trap -->
        <div style="display: none !important;" aria-hidden="true">
          <label for="website_url_hp">Leave this field blank</label>
          <input type="text" id="website_url_hp" name="website_url_hp" tabindex="-1" autocomplete="off">
        </div>

        <h3 style="font-size: 1.15rem; color: var(--color-primary); margin-bottom: var(--space-4); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-2);">
          1. Lead Traveller Information
        </h3>

        <!-- Full Name -->
        <div class="form-group" style="margin-bottom: var(--space-4);">
          <label for="customer_name" class="form-label" style="font-weight: 600;">Full Name (As on Passport) <span style="color: #DC2626;">*</span></label>
          <input type="text" id="customer_name" name="customer_name" class="form-control" required
                 value="<?= Security::e($old['customer_name'] ?? '') ?>"
                 placeholder="e.g. Adebayo Adeleke"
                 style="width: 100%; padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
          <?php if (!empty($errors['customer_name'])): ?>
            <p style="color: #DC2626; font-size: var(--font-size-xs); margin-top: 4px;"><?= Security::e($errors['customer_name'][0]) ?></p>
          <?php endif; ?>
        </div>

        <!-- Email & Phone Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-4);">
          <div class="form-group">
            <label for="customer_email" class="form-label" style="font-weight: 600;">Email Address (For Receipt & Tickets) <span style="color: #DC2626;">*</span></label>
            <input type="email" id="customer_email" name="customer_email" class="form-control" required
                   value="<?= Security::e($old['customer_email'] ?? '') ?>"
                   placeholder="e.g. adebayo@example.com"
                   style="width: 100%; padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
            <?php if (!empty($errors['customer_email'])): ?>
              <p style="color: #DC2626; font-size: var(--font-size-xs); margin-top: 4px;"><?= Security::e($errors['customer_email'][0]) ?></p>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label for="customer_phone" class="form-label" style="font-weight: 600;">Phone / WhatsApp Number <span style="color: #DC2626;">*</span></label>
            <input type="tel" id="customer_phone" name="customer_phone" class="form-control" required
                   value="<?= Security::e($old['customer_phone'] ?? '') ?>"
                   placeholder="e.g. 08012345678"
                   style="width: 100%; padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
            <?php if (!empty($errors['customer_phone'])): ?>
              <p style="color: #DC2626; font-size: var(--font-size-xs); margin-top: 4px;"><?= Security::e($errors['customer_phone'][0]) ?></p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Travellers Count -->
        <div class="form-group" style="margin-bottom: var(--space-6);">
          <label for="travellers_count" class="form-label" style="font-weight: 600;">Number of Travellers <span style="color: #DC2626;">*</span></label>
          <div style="max-width: 200px;">
            <input type="number" id="travellers_count" name="travellers_count" min="1" max="20" class="form-control" required
                   value="<?= $travellers ?>"
                   style="width: 100%; padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--color-border); font-size: 1.1rem; font-weight: 700;">
          </div>
          <span style="font-size: var(--font-size-xs); color: var(--color-text-muted); display: block; margin-top: 4px;">
            Total will automatically update to ₦<?= number_format($unitPrice, 2) ?> &times; travellers.
          </span>
          <?php if (!empty($errors['travellers_count'])): ?>
            <p style="color: #DC2626; font-size: var(--font-size-xs); margin-top: 4px;"><?= Security::e($errors['travellers_count'][0]) ?></p>
          <?php endif; ?>
        </div>

        <h3 style="font-size: 1.15rem; color: var(--color-primary); margin-bottom: var(--space-4); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-2);">
          2. Choose Payment Method
        </h3>

        <?php if ($hasPaymentMethods): ?>
          <!-- Payment Method Cards -->
          <div style="display: grid; grid-template-columns: 1fr; gap: var(--space-3); margin-bottom: var(--space-6);">
            
            <?php if ($bankEnabled): ?>
              <!-- Method 1: Bank Transfer -->
              <label style="border: 2px solid var(--color-border); border-radius: var(--radius-md); padding: var(--space-4); cursor: pointer; display: flex; align-items: flex-start; gap: var(--space-3);" class="payment-option">
                <input type="radio" name="payment_method" value="manual" <?= $selectedMethod === 'manual' ? 'checked' : '' ?> style="margin-top: 4px;">
                <div style="flex: 1;">
                  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <strong style="color: var(--color-primary); font-size: 1rem;">Direct Corporate Bank Transfer</strong>
                    <span class="badge" style="background-color: #E0F2FE; color: #0369A1; font-size: var(--font-size-xs); font-weight: 700; padding: 2px 8px; border-radius: 4px;">Zero Gateway Fees</span>
                  </div>
                  <p style="color: var(--color-text-muted); font-size: var(--font-size-sm); margin: 4px 0 0;">
                    Transfer directly to our verified Zenith Bank account. You will receive an official payment narration code and instruction page immediately upon submission.
                  </p>
                </div>
              </label>
            <?php endif; ?>

            <?php if ($paystackEnabled): ?>
              <!-- Method 2: Paystack -->
              <label style="border: 2px solid var(--color-border); border-radius: var(--radius-md); padding: var(--space-4); cursor: pointer; display: flex; align-items: flex-start; gap: var(--space-3);" class="payment-option">
                <input type="radio" name="payment_method" value="paystack" <?= $selectedMethod === 'paystack' ? 'checked' : '' ?> style="margin-top: 4px;">
                <div style="flex: 1;">
                  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                      <strong style="color: var(--color-primary); font-size: 1rem;">Pay Online via Paystack</strong>
                      <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; color: #0284C7; background: #E0F2FE; padding: 2px 8px; border-radius: 4px;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                        INSTANT VERIFICATION
                      </span>
                    </div>
                    <div style="display: flex; gap: 5px; align-items: center;">
                      <span style="font-size: 10px; font-weight: 700; background: #EFF6FF; color: #1D4ED8; padding: 2px 6px; border-radius: 4px; border: 1px solid #BFDBFE;">VISA</span>
                      <span style="font-size: 10px; font-weight: 700; background: #FEF2F2; color: #B91C1C; padding: 2px 6px; border-radius: 4px; border: 1px solid #FECACA;">MASTERCARD</span>
                      <span style="font-size: 10px; font-weight: 700; background: #F0FDF4; color: #15803D; padding: 2px 6px; border-radius: 4px; border: 1px solid #BBF7D0;">VERVE</span>
                      <span style="font-size: 10px; font-weight: 700; background: #F8FAFC; color: #475569; padding: 2px 6px; border-radius: 4px; border: 1px solid #E2E8F0;">USSD</span>
                    </div>
                  </div>
                  <p style="color: var(--color-text-muted); font-size: var(--font-size-sm); margin: 6px 0 0;">
                    Pay securely with your Nigerian debit card, direct bank transfer, or USSD via Paystack 256-bit encrypted checkout. Verified instantly with automated official receipt.
                  </p>
                </div>
              </label>
            <?php endif; ?>

          </div>
          <?php if (!empty($errors['payment_method'])): ?>
            <p style="color: #DC2626; font-size: var(--font-size-xs); margin-top: -12px; margin-bottom: var(--space-4);"><?= Security::e($errors['payment_method'][0]) ?></p>
          <?php endif; ?>
        <?php else: ?>
          <!-- Advisory When Online Payments Disabled -->
          <div style="background-color: #FEF3C7; border: 1px solid #FCD34D; border-left: 4px solid #D97706; padding: var(--space-4); border-radius: var(--radius-sm); margin-bottom: var(--space-6); font-size: var(--font-size-sm); color: #92400E; line-height: 1.6;">
            <strong style="display: block; margin-bottom: 4px; font-size: 1rem;">Direct Online Checkout Offline:</strong>
            Direct automated online checkout is currently paused for routine system maintenance. You can still reserve your slots for this package immediately by contacting our dedicated travel desk. Our consultants will verify date availability and provide your corporate reservation invoice.
          </div>
        <?php endif; ?>

        <!-- Statutory Travel & Policy Disclaimers (SOT Section 8.4 & D11) -->
        <div style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-left: 4px solid var(--color-primary); padding: var(--space-4); border-radius: var(--radius-sm); margin-bottom: var(--space-6); font-size: var(--font-size-xs); color: #475569; line-height: 1.6;">
          <strong style="color: var(--color-primary); display: block; margin-bottom: 4px;">IMPORTANT STATUTORY TRAVEL NOTICE (D11):</strong>
          Package payment confirmation certifies <strong>receipt of funds only</strong> and is <strong>not an issued airline ticket, hotel reservation voucher, or travel confirmation</strong>. All travel dates and package logistics remain subject to manual availability verification and final scheduling by LAD Global Travels consultants. Refunds are subject to our manual administrative <a href="/refund-policy" target="_blank" style="color: var(--color-primary); font-weight: 600;">Refund Policy</a>.
        </div>

        <?php if ($hasPaymentMethods): ?>
          <!-- Consent Checkbox -->
          <div class="form-group" style="margin-bottom: var(--space-6);">
            <label style="display: flex; align-items: flex-start; gap: 8px; font-size: var(--font-size-sm); color: var(--color-text); cursor: pointer;">
              <input type="checkbox" name="terms_consent" value="1" required <?= !empty($old['terms_consent']) ? 'checked' : '' ?> style="margin-top: 3px;">
              <span>
                I have read, understood, and agree to the <a href="/terms" target="_blank" style="color: var(--color-primary); font-weight: 600;">Terms of Service</a>, <a href="/refund-policy" target="_blank" style="color: var(--color-primary); font-weight: 600;">Refund Policy</a>, and <a href="/privacy" target="_blank" style="color: var(--color-primary); font-weight: 600;">Privacy Notice</a> under the Nigeria Data Protection Act (NDPA). <span style="color: #DC2626;">*</span>
              </span>
            </label>
            <?php if (!empty($errors['terms_consent'])): ?>
              <p style="color: #DC2626; font-size: var(--font-size-xs); margin-top: 4px;"><?= Security::e($errors['terms_consent'][0]) ?></p>
            <?php endif; ?>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1.05rem; font-weight: 700; border-radius: var(--radius-md);">
            🔒 Proceed to Payment (<span id="btnTotalDisplay">₦<?= number_format($totalEstimate, 2) ?></span>)
          </button>
        <?php else: ?>
          <!-- Direct Consultation Reserve CTAs -->
          <div style="display: flex; flex-direction: column; gap: var(--space-3);">
            <a href="https://wa.me/2347017597650?text=Hello%20LAD%20Global,%20I%20would%20like%20to%20reserve%20the%20<?= urlencode($package['title'] ?? '') ?>%20package." target="_blank" rel="noopener" class="btn btn-accent" style="width: 100%; padding: 14px; font-size: 1.05rem; font-weight: 700; text-align: center; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              Reserve via WhatsApp Travel Consultant
            </a>
            <a href="tel:+2347017597650" class="btn btn-outline" style="width: 100%; padding: 12px; font-size: 1rem; font-weight: 600; text-align: center; border-radius: var(--radius-md); text-decoration: none;">
              📞 Call Our Reservation Desk (+234 701 759 7650)
            </a>
          </div>
        <?php endif; ?>

      </form>

    </div>

  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var unitPrice = <?= json_encode($unitPrice) ?>;
  var countInput = document.getElementById('travellers_count');
  var liveTotal = document.getElementById('liveTotalDisplay');
  var btnTotal = document.getElementById('btnTotalDisplay');

  function updateTotals() {
    var count = parseInt(countInput.value, 10);
    if (isNaN(count) || count < 1) count = 1;
    if (count > 20) count = 20;

    var total = unitPrice * count;
    var formatted = '₦' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    if (liveTotal) liveTotal.textContent = formatted;
    if (btnTotal) btnTotal.textContent = formatted;
  }

  if (countInput) {
    countInput.addEventListener('input', updateTotals);
    countInput.addEventListener('change', updateTotals);
  }
});
</script>
