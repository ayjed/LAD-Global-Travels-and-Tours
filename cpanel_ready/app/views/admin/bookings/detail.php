<?php
/**
 * Admin Single Booking Detail View
 * @var array $booking The booking entity
 * @var array $payments List of associated payment attempts
 */
use App\Core\Security;

$bId = (int)$booking['id'];
$bRef = Security::e($booking['reference']);
$customerName = Security::e($booking['customer_name']);
$customerEmail = Security::e($booking['customer_email']);
$customerPhone = Security::e($booking['customer_phone']);
$packageTitle = Security::e($booking['package_title']);
$destination = Security::e($booking['destination_city'] . ', ' . $booking['destination_country']);
$totalAmount = number_format((float)$booking['total_amount'], 2);
$currency = Security::e($booking['currency']);
$bookingStatus = (string)$booking['booking_status'];

$cleanPhone = preg_replace('/[^0-9]/', '', (string)$booking['customer_phone']);
if (str_starts_with($cleanPhone, '0')) {
    $cleanPhone = '234' . substr($cleanPhone, 1);
}
$waUrl = "https://wa.me/{$cleanPhone}?text=" . rawurlencode("Hello {$booking['customer_name']}, this is LAD Global Travels regarding your tour booking Ref: {$booking['reference']}.");
?>

<!-- Header Toolbar -->
<div style="margin-bottom: 24px;">
  <a href="/admin/bookings" class="admin-back-link">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
    Back to Bookings Directory
  </a>
  <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
    <div>
      <h1 class="admin-topbar-title" style="margin: 4px 0;">
        Booking Reference: <?= $bRef ?>
      </h1>
      <p class="admin-topbar-subtitle">
        Submitted on <?= date('d M Y \a\t H:i', strtotime($booking['created_at'])) ?>
      </p>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
      <a href="<?= $waUrl ?>" target="_blank" class="admin-btn admin-btn-whatsapp admin-btn-sm" title="Message client via WhatsApp">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
        WhatsApp Client
      </a>
      <a href="tel:<?= $customerPhone ?>" class="admin-btn admin-btn-outline admin-btn-sm" title="Call customer phone">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
        Call Phone
      </a>
      <a href="/booking/<?= $bRef ?>" target="_blank" class="admin-btn admin-btn-outline admin-btn-sm" title="Open customer confirmation portal">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
        View Public Page
      </a>
    </div>
  </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;" class="admin-grid-layout">
  <!-- Left Main Column: Package & Payments -->
  <div>
    <!-- Package Details Card -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2 class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
          Selected Tour Package
        </h2>
      </div>
      <div class="admin-card-body">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
          <div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--admin-primary); margin: 0 0 6px;">
              <?= $packageTitle ?>
            </h3>
            <div style="display: flex; gap: 16px; font-size: 13px; color: var(--admin-text-muted);">
              <span><strong>Destination:</strong> <?= $destination ?></span>
              <span>&bull;</span>
              <span><strong>Duration:</strong> <?= (int)$booking['duration_days'] ?> Days</span>
            </div>
          </div>
          <div style="text-align: right;">
            <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--admin-text-muted); display: block;">Unit Rate</span>
            <div style="font-size: 1.2rem; font-weight: 800; color: var(--admin-text-main);">
              ₦<?= number_format((float)$booking['unit_price'], 2) ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Payments Table & Reconciliation Card -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2 class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
          Payment Transactions &amp; Audit Trail
        </h2>
      </div>
      <div class="admin-card-body">
        <?php if (empty($payments)): ?>
          <div class="admin-empty-state" style="padding: 32px 16px;">
            <p class="admin-empty-desc" style="margin-bottom: 0;">No payment attempts logged for this booking yet.</p>
          </div>
        <?php else: ?>
          <?php foreach ($payments as $p): ?>
            <?php 
              $pId = (int)$p['id'];
              $st = strtoupper((string)$p['status']); 
              $prov = strtoupper((string)$p['provider']);
            ?>
            <div style="border: 1px solid var(--admin-border); border-radius: var(--admin-radius-md); padding: 18px; margin-bottom: 16px; background-color: #F8FAFC;">
              <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 8px;">
                <div>
                  <span style="font-size: 11px; color: var(--admin-text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.04em;">Payment Ref: <?= Security::e($p['payment_reference']) ?></span>
                  <div style="font-weight: 800; font-size: 1.2rem; color: #0F172A; margin-top: 2px;">
                    ₦<?= number_format((float)$p['amount'], 2) ?> <?= Security::e($p['currency']) ?>
                    <span style="font-size: 12.5px; font-weight: 600; color: var(--admin-text-muted); margin-left: 8px;">
                      (<?= $prov === 'MANUAL' ? 'Direct Bank Transfer' : 'Paystack Online Gateway' ?>)
                    </span>
                  </div>
                </div>
                <div>
                  <?php if ($st === 'PAID'): ?>
                    <span class="admin-badge admin-badge-success">Paid &amp; Verified</span>
                  <?php elseif ($st === 'REJECTED'): ?>
                    <span class="admin-badge admin-badge-danger">Rejected</span>
                  <?php else: ?>
                    <span class="admin-badge admin-badge-warning">Pending Audit</span>
                  <?php endif; ?>
                </div>
              </div>

              <?php if (!empty($p['admin_audit_notes'])): ?>
                <div style="background-color: #FFFFFF; border: 1px solid #E2E8F0; border-radius: var(--admin-radius-sm); padding: 10px 14px; margin-top: 10px; font-size: 12.5px; color: #475569; white-space: pre-line;">
                  <strong style="color: #0F172A;">Audit Remarks:</strong><br><?= Security::e($p['admin_audit_notes']) ?>
                </div>
              <?php endif; ?>

              <?php if ($st === 'PAID'): ?>
                <div style="margin-top: 12px;">
                  <a href="/booking/<?= $bRef ?>/receipt" target="_blank" class="admin-btn admin-btn-outline admin-btn-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    View Official Payment Receipt
                  </a>
                </div>
              <?php elseif ($prov === 'MANUAL' && $st === 'PENDING'): ?>
                <!-- Manual Bank Transfer Verification Form -->
                <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--admin-border);">
                  <div style="font-size: 13px; font-weight: 700; color: var(--admin-primary); margin-bottom: 8px;">Reconcile Bank Transfer:</div>
                  <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <!-- Verify Form -->
                    <form action="/admin/payments/<?= $pId ?>/verify" method="POST" style="flex: 1; min-width: 280px; margin: 0;">
                      <?= Security::csrfField() ?>
                      <input type="hidden" name="redirect_to" value="/admin/bookings/<?= $bId ?>">
                      <input type="text" name="audit_notes" required placeholder="Mandatory remarks: e.g. Confirmed in Zenith statement on <?= date('Y-m-d') ?>"
                             class="admin-form-control" style="font-size: 12px; margin-bottom: 8px;">
                      <button type="submit" class="admin-btn admin-btn-success admin-btn-sm" style="font-weight: 700;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Verify &amp; Settle Payment
                      </button>
                    </form>

                    <!-- Reject Form -->
                    <form action="/admin/payments/<?= $pId ?>/reject" method="POST" style="margin: 0;">
                      <?= Security::csrfField() ?>
                      <input type="hidden" name="redirect_to" value="/admin/bookings/<?= $bId ?>">
                      <input type="hidden" name="reject_reason" value="No funds received in corporate bank account">
                      <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm" style="font-weight: 600;"
                              onclick="return confirm('Are you sure you want to reject this payment record?');">
                        Reject
                      </button>
                    </form>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Right Sidebar Column: Customer & Travel Fulfillment Status -->
  <div>
    <!-- Customer Details Card -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2 class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
          Guest Information
        </h2>
      </div>
      <div class="admin-card-body">
        <div style="display: flex; flex-direction: column; gap: 14px;">
          <div>
            <span class="admin-kv-label">Primary Guest</span>
            <div class="admin-kv-value" style="font-size: 15px;"><?= $customerName ?></div>
          </div>
          <div>
            <span class="admin-kv-label">Email Address</span>
            <div class="admin-kv-value">
              <a href="mailto:<?= $customerEmail ?>" style="color: var(--admin-primary-light); text-decoration: none;"><?= $customerEmail ?></a>
            </div>
          </div>
          <div>
            <span class="admin-kv-label">Phone / WhatsApp</span>
            <div class="admin-kv-value">
              <a href="tel:<?= $customerPhone ?>" style="color: var(--admin-primary-light); text-decoration: none;"><?= $customerPhone ?></a>
            </div>
          </div>
          <div>
            <span class="admin-kv-label">Group Size</span>
            <div class="admin-kv-value"><?= (int)$booking['travellers_count'] ?> Guest(s)</div>
          </div>
          <div style="border-top: 1px solid var(--admin-border); padding-top: 12px;">
            <span class="admin-kv-label">Total Booking Invoice</span>
            <div style="font-size: 1.3rem; font-weight: 800; color: #0F172A;">
              ₦<?= $totalAmount ?> <span style="font-size: 12px; font-weight: 600; color: var(--admin-text-muted);"><?= $currency ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Travel Fulfillment Status Card -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2 class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
          Travel Fulfillment
        </h2>
      </div>
      <div class="admin-card-body">
        <p style="font-size: 12px; color: var(--admin-text-muted); line-height: 1.5; margin: 0 0 16px;">
          Per operations policy, travel logistical confirmation is <strong>strictly decoupled from payment settlement</strong>. Mark confirmed only after airline booking and hotel vouchers are locked.
        </p>

        <form action="/admin/bookings/<?= $bId ?>/status" method="POST">
          <?= Security::csrfField() ?>
          
          <div class="admin-form-group">
            <label class="admin-form-label">Logistical Status</label>
            <select name="booking_status" class="admin-form-control">
              <option value="pending" <?= $bookingStatus === 'pending' ? 'selected' : '' ?>>Pending Logistical Review</option>
              <option value="confirmed" <?= $bookingStatus === 'confirmed' ? 'selected' : '' ?>>Confirmed Travel &amp; Issued</option>
              <option value="cancelled" <?= $bookingStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled / Void</option>
            </select>
          </div>

          <div class="admin-form-group">
            <label class="admin-form-label">Fulfillment Audit Remarks</label>
            <textarea name="admin_notes" rows="3" placeholder="e.g. Flight reference issued; hotel voucher dispatch verified." class="admin-form-control" style="font-size: 12.5px;"></textarea>
          </div>

          <button type="submit" class="admin-btn admin-btn-primary" style="width: 100%; font-weight: 700;">
            Update Fulfillment Status
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
