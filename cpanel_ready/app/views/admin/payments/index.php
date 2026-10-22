<?php
/**
 * Admin Payments Directory & Manual Reconciliation Queue View
 * @var array $payments List of payment entities
 * @var array $counts Aggregated counts
 * @var array $filters Active filters
 */
use App\Core\Security;

$currentProvider = $filters['provider'] ?? '';
$currentStatus = $filters['status'] ?? '';
$searchQuery = $filters['search'] ?? '';
?>

<div class="admin-topbar">
  <div>
    <h1 class="admin-topbar-title">Payments &amp; Reconciliation</h1>
    <p class="admin-topbar-subtitle">
      Manual bank transfer settlement audit, Paystack digital transaction log, and financial records.
    </p>
  </div>
  <div class="admin-topbar-actions">
    <a href="/admin/bookings" class="admin-btn admin-btn-outline">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
      View All Bookings
    </a>
  </div>
</div>

<!-- Metrics Cards Grid -->
<div class="admin-metrics-grid">
  <!-- Pending Manual Transfers -->
  <div class="admin-metric-card">
    <div class="admin-metric-header">
      <span class="admin-metric-label">Pending Transfers</span>
      <div class="admin-metric-icon icon-amber">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
      </div>
    </div>
    <div class="admin-metric-value" style="color: var(--admin-warning);">
      <?= (int)($counts['pending_transfers'] ?? 0) ?>
    </div>
    <div class="admin-metric-subtext">Requires bank statement review</div>
  </div>

  <!-- Paid Payments -->
  <div class="admin-metric-card">
    <div class="admin-metric-header">
      <span class="admin-metric-label">Settled Payments</span>
      <div class="admin-metric-icon icon-emerald">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
      </div>
    </div>
    <div class="admin-metric-value" style="color: var(--admin-success);">
      <?= (int)($counts['paid_payments'] ?? 0) ?>
    </div>
    <div class="admin-metric-subtext">Confirmed &amp; receipts issued</div>
  </div>

  <!-- Total Revenue Collected -->
  <div class="admin-metric-card">
    <div class="admin-metric-header">
      <span class="admin-metric-label">Total Revenue</span>
      <div class="admin-metric-icon icon-blue">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
      </div>
    </div>
    <div class="admin-metric-value" style="color: var(--admin-primary); font-size: 1.6rem;">
      ₦<?= number_format((float)($counts['total_collected'] ?? 0), 2) ?>
    </div>
    <div class="admin-metric-subtext">Verified lifetime settlement</div>
  </div>
</div>

<!-- Filters Toolbar -->
<div class="admin-toolbar">
  <div class="admin-tabs">
    <a href="/admin/payments" 
       class="admin-tab <?= empty($currentProvider) && empty($currentStatus) ? 'active' : '' ?>">
      All Payments
    </a>
    <a href="/admin/payments?provider=manual&status=pending" 
       class="admin-tab <?= $currentProvider === 'manual' && $currentStatus === 'pending' ? 'active' : '' ?>">
      Pending Bank Queue (<?= (int)($counts['pending_transfers'] ?? 0) ?>)
    </a>
    <a href="/admin/payments?status=paid" 
       class="admin-tab <?= $currentStatus === 'paid' ? 'active' : '' ?>">
      Paid
    </a>
    <a href="/admin/payments?provider=paystack" 
       class="admin-tab <?= $currentProvider === 'paystack' ? 'active' : '' ?>">
      Paystack Online
    </a>
    <a href="/admin/payments?status=rejected" 
       class="admin-tab <?= $currentStatus === 'rejected' ? 'active' : '' ?>">
      Rejected
    </a>
  </div>

  <!-- Search Input -->
  <form action="/admin/payments" method="GET" class="admin-search-box">
    <?php if (!empty($currentProvider)): ?>
      <input type="hidden" name="provider" value="<?= Security::e($currentProvider) ?>">
    <?php endif; ?>
    <?php if (!empty($currentStatus)): ?>
      <input type="hidden" name="status" value="<?= Security::e($currentStatus) ?>">
    <?php endif; ?>
    <input type="text" name="search" value="<?= Security::e($searchQuery) ?>" class="admin-search-input" placeholder="Search payment or booking ref...">
    <button type="submit" class="admin-btn admin-btn-outline admin-btn-sm">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
      Search
    </button>
    <?php if (!empty($searchQuery)): ?>
      <a href="/admin/payments" class="admin-btn admin-btn-outline admin-btn-sm">Clear</a>
    <?php endif; ?>
  </form>
</div>

<!-- Payments Table Card -->
<div class="admin-card">
  <?php if (empty($payments)): ?>
    <div class="admin-empty-state">
      <div class="admin-empty-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
      </div>
      <h3 class="admin-empty-title">No Payment Records Found</h3>
      <p class="admin-empty-desc">
        <?= !empty($searchQuery) ? 'No payment records match your search criteria.' : 'Transactions will populate here automatically as clients complete booking payments.' ?>
      </p>
    </div>
  <?php else: ?>
    <div class="admin-table-container">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Payment Reference</th>
            <th>Booking Reference</th>
            <th>Client</th>
            <th>Channel</th>
            <th style="text-align: right;">Amount</th>
            <th style="text-align: center;">Status</th>
            <th>Date</th>
            <th style="text-align: right;">Audit Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payments as $p): ?>
            <?php 
              $pId = (int)$p['id'];
              $st = strtoupper((string)$p['status']);
              $prov = strtoupper((string)$p['provider']);
              $isPendingManual = ($prov === 'MANUAL' && $st === 'PENDING');
            ?>
            <tr style="<?= $isPendingManual ? 'background-color: #FFFDF5;' : '' ?>">
              <!-- Payment Ref -->
              <td style="font-family: monospace; font-weight: 700; color: #0F172A;">
                <?= Security::e($p['payment_reference']) ?>
              </td>

              <!-- Booking Ref -->
              <td style="font-family: monospace;">
                <a href="/admin/bookings/<?= (int)$p['booking_id'] ?>" style="color: var(--admin-primary-light); font-weight: 600; text-decoration: none;">
                  <?= Security::e($p['booking_reference']) ?>
                </a>
              </td>

              <!-- Customer -->
              <td>
                <div style="font-weight: 600; color: var(--admin-text-main);"><?= Security::e($p['customer_name']) ?></div>
                <div style="font-size: 12px; color: var(--admin-text-muted);"><?= Security::e($p['customer_phone']) ?></div>
              </td>

              <!-- Channel -->
              <td>
                <?php if ($prov === 'MANUAL'): ?>
                  <span class="admin-badge admin-badge-info" style="font-size: 11px;">Bank Transfer</span>
                <?php else: ?>
                  <span class="admin-badge admin-badge-navy" style="font-size: 11px;">Paystack Online</span>
                <?php endif; ?>
              </td>

              <!-- Amount -->
              <td style="text-align: right; font-weight: 700; color: #0F172A;">
                ₦<?= number_format((float)$p['amount'], 2) ?>
              </td>

              <!-- Status -->
              <td style="text-align: center;">
                <?php if ($st === 'PAID'): ?>
                  <span class="admin-badge admin-badge-success">Paid</span>
                <?php elseif ($st === 'REJECTED'): ?>
                  <span class="admin-badge admin-badge-danger">Rejected</span>
                <?php else: ?>
                  <span class="admin-badge admin-badge-warning">Pending Audit</span>
                <?php endif; ?>
              </td>

              <!-- Date -->
              <td style="font-size: 12.5px; color: var(--admin-text-muted); white-space: nowrap;">
                <?= date('d M Y, H:i', strtotime($p['created_at'])) ?>
              </td>

              <!-- Action -->
              <td style="text-align: right;">
                <?php if ($isPendingManual): ?>
                  <div style="display: flex; gap: 6px; justify-content: flex-end;">
                    <form action="/admin/payments/<?= $pId ?>/verify" method="POST" style="margin: 0; display: inline;">
                      <?= Security::csrfField() ?>
                      <input type="hidden" name="audit_notes" value="Verified in corporate bank statement on <?= date('Y-m-d') ?>">
                      <button type="submit" class="admin-btn admin-btn-success admin-btn-sm" style="font-weight: 700;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Verify
                      </button>
                    </form>
                    <form action="/admin/payments/<?= $pId ?>/reject" method="POST" style="margin: 0; display: inline;">
                      <?= Security::csrfField() ?>
                      <input type="hidden" name="reject_reason" value="No funds found in statement">
                      <button type="submit" class="admin-btn admin-btn-outline admin-btn-sm" style="color: var(--admin-danger); border-color: var(--admin-danger-border);"
                              onclick="return confirm('Reject this manual payment?');" title="Reject Payment">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                      </button>
                    </form>
                  </div>
                <?php elseif ($st === 'PAID'): ?>
                  <span style="font-size: 12px; font-weight: 600; color: var(--admin-success); display: inline-flex; align-items: center; gap: 4px;">
                    <svg style="width: 13px; height: 13px;" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Reconciled
                  </span>
                <?php else: ?>
                  <span style="font-size: 12px; color: var(--admin-text-light);">&mdash;</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
