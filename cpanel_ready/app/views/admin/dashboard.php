<?php
/**
 * Admin Dashboard Overview View — Modern Enterprise Edition
 * @var int $totalEnquiries
 * @var int $todayEnquiries
 * @var int $activePackages
 * @var int $pendingTransfersCount
 * @var array $recentEnquiries
 * @var array $pendingTransfers
 */
use App\Core\Security;
?>

<div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
  <div>
    <h1 class="admin-topbar-title">Executive Dashboard</h1>
    <p class="admin-topbar-subtitle">Operational metrics, manual transfer queues, and client leads.</p>
  </div>
  <div class="admin-topbar-actions">
    <a href="/admin/packages/create" class="admin-btn admin-btn-primary">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
      Create New Package
    </a>
  </div>
</div>

<!-- Key Operational Metrics -->
<section class="admin-metrics-grid">
  <!-- Metric 1: New Enquiries Today -->
  <div class="admin-metric-card">
    <div class="admin-metric-header">
      <span class="admin-metric-label">Enquiries (Today)</span>
      <div class="admin-metric-icon icon-blue">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      </div>
    </div>
    <div class="admin-metric-value"><?= number_format($todayEnquiries) ?></div>
    <div class="admin-metric-subtext">
      <span><?= number_format($totalEnquiries) ?> total customer leads</span>
    </div>
  </div>

  <!-- Metric 2: Pending Bank Transfers -->
  <div class="admin-metric-card">
    <div class="admin-metric-header">
      <span class="admin-metric-label">Pending Transfers</span>
      <div class="admin-metric-icon icon-amber">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      </div>
    </div>
    <div class="admin-metric-value" style="color: <?= $pendingTransfersCount > 0 ? 'var(--admin-warning)' : 'inherit' ?>;">
      <?= number_format($pendingTransfersCount) ?>
    </div>
    <div class="admin-metric-subtext">
      <span>Awaiting corporate bank review</span>
    </div>
  </div>

  <!-- Metric 3: Published Tour Packages -->
  <div class="admin-metric-card">
    <div class="admin-metric-header">
      <span class="admin-metric-label">Active Packages</span>
      <div class="admin-metric-icon icon-emerald">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
      </div>
    </div>
    <div class="admin-metric-value"><?= number_format($activePackages) ?></div>
    <div class="admin-metric-subtext">
      <span>Published live on public catalog</span>
    </div>
  </div>

  <!-- Metric 4: System Status -->
  <div class="admin-metric-card">
    <div class="admin-metric-header">
      <span class="admin-metric-label">System Security</span>
      <div class="admin-metric-icon icon-navy">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
    </div>
    <div class="admin-metric-value" style="color: var(--admin-success); font-size: 1.5rem; margin-top: 4px;">Guarded</div>
    <div class="admin-metric-subtext">
      <span>Rate limited &bull; CSRF active</span>
    </div>
  </div>
</section>

<!-- Section: Pending Bank Transfers (Reconciliation Queue) -->
<section class="admin-card">
  <div class="admin-card-header">
    <div>
      <h2 class="admin-card-title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
        Pending Bank Transfers Queue
      </h2>
      <p class="admin-card-subtitle">
        Strict audit policy: Confirm receipt in company bank statement before approving.
      </p>
    </div>
    <div>
      <span class="admin-badge <?= count($pendingTransfers) > 0 ? 'admin-badge-warning' : 'admin-badge-success' ?>">
        <?= count($pendingTransfers) ?> Awaiting Audit
      </span>
    </div>
  </div>

  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Booking Ref</th>
          <th>Customer</th>
          <th>Tour Package</th>
          <th>Amount Due</th>
          <th>Payment Mode</th>
          <th>Status</th>
          <th>Submitted</th>
          <th style="text-align: right;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($pendingTransfers)): ?>
          <tr>
            <td colspan="8" style="text-align: center; color: var(--admin-text-muted); padding: 36px 20px;">
              No manual bank transfers awaiting reconciliation at this time.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($pendingTransfers as $pt): ?>
            <tr>
              <td><strong style="color: var(--admin-primary);"><?= Security::e($pt['booking_ref']) ?></strong></td>
              <td>
                <div style="font-weight: 600; color: var(--admin-text-main);"><?= Security::e($pt['customer_name']) ?></div>
                <div style="font-size: 12px; color: var(--admin-text-muted);"><?= Security::e($pt['customer_phone']) ?></div>
              </td>
              <td><?= Security::e($pt['package_title']) ?></td>
              <td><strong><?= Security::e($pt['currency']) ?> <?= number_format((float)$pt['amount'], 2) ?></strong></td>
              <td><span class="admin-badge admin-badge-info">Direct Transfer</span></td>
              <td><span class="admin-badge admin-badge-warning">Pending Audit</span></td>
              <td style="font-size: 12px; color: var(--admin-text-muted);"><?= Security::e($pt['created_at']) ?></td>
              <td style="text-align: right;">
                <a href="/admin/payments" class="admin-btn admin-btn-sm admin-btn-primary">
                  Verify &rarr;
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<!-- Section: Recent Customer Inquiries -->
<section class="admin-card">
  <div class="admin-card-header">
    <div>
      <h2 class="admin-card-title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Recent Inquiries &amp; Leads
      </h2>
      <p class="admin-card-subtitle">
        Customer consultation submissions and package interest forms.
      </p>
    </div>
    <div>
      <a href="/admin/enquiries" class="admin-btn admin-btn-outline admin-btn-sm">
        View All Inquiries &rarr;
      </a>
    </div>
  </div>

  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Reference</th>
          <th>Lead Name</th>
          <th>Service Type</th>
          <th>Contact &amp; WhatsApp</th>
          <th>Status</th>
          <th>Received</th>
          <th style="text-align: right;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($recentEnquiries)): ?>
          <tr>
            <td colspan="7" style="text-align: center; color: var(--admin-text-muted); padding: 36px 20px;">
              No customer inquiries recorded yet. Form submissions from the public website will land here.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($recentEnquiries as $enq): ?>
            <tr>
              <td><strong style="color: var(--admin-primary);"><?= Security::e($enq['reference']) ?></strong></td>
              <td><strong style="color: var(--admin-text-main);"><?= Security::e($enq['full_name']) ?></strong></td>
              <td><span class="admin-badge admin-badge-navy"><?= Security::e($enq['service_type']) ?></span></td>
              <td>
                <span style="font-size: 13px;"><?= Security::e($enq['phone']) ?></span>
                <span style="color: #CBD5E1; margin: 0 4px;">&bull;</span>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $enq['phone']) ?>" target="_blank" rel="noopener" style="color: var(--admin-success); font-weight: 600; text-decoration: none;">
                  WhatsApp
                </a>
              </td>
              <td>
                <span class="admin-badge admin-badge-<?= $enq['status'] === 'new' ? 'info' : ($enq['status'] === 'closed' ? 'success' : 'warning') ?>">
                  <?= Security::e(ucfirst($enq['status'])) ?>
                </span>
              </td>
              <td style="font-size: 12px; color: var(--admin-text-muted);"><?= Security::e($enq['created_at']) ?></td>
              <td style="text-align: right;">
                <a href="/admin/enquiries/<?= (int)$enq['id'] ?>" class="admin-btn admin-btn-sm admin-btn-outline">
                  Details
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
