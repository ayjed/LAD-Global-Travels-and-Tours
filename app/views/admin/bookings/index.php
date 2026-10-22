<?php
/**
 * Admin Bookings Directory View
 * @var array $bookings List of booking records
 * @var array $counts Aggregated counts
 * @var array $filters Active search/status filters
 */
use App\Core\Security;

$currentStatus = $filters['status'] ?? '';
$searchQuery = $filters['search'] ?? '';
?>

<div class="admin-topbar">
  <div>
    <h1 class="admin-topbar-title">Package Bookings</h1>
    <p class="admin-topbar-subtitle">
      Manage guest tour reservations, track payment settlement, and update travel fulfillment status.
    </p>
  </div>
  <div class="admin-topbar-actions">
    <a href="/admin/payments" class="admin-btn admin-btn-outline">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
      Payments &amp; Reconciliation Queue
    </a>
  </div>
</div>

<!-- Metrics Cards Grid -->
<div class="admin-metrics-grid">
  <!-- Total Bookings -->
  <div class="admin-metric-card">
    <div class="admin-metric-header">
      <span class="admin-metric-label">Total Bookings</span>
      <div class="admin-metric-icon icon-navy">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
      </div>
    </div>
    <div class="admin-metric-value"><?= (int)($counts['total_bookings'] ?? 0) ?></div>
    <div class="admin-metric-subtext">Lifetime client reservations</div>
  </div>

  <!-- Pending Travel Review -->
  <div class="admin-metric-card">
    <div class="admin-metric-header">
      <span class="admin-metric-label">Pending Review</span>
      <div class="admin-metric-icon icon-amber">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
      </div>
    </div>
    <div class="admin-metric-value" style="color: var(--admin-warning);"><?= (int)($counts['pending_bookings'] ?? 0) ?></div>
    <div class="admin-metric-subtext">Awaiting operational confirmation</div>
  </div>

  <!-- Confirmed Travel -->
  <div class="admin-metric-card">
    <div class="admin-metric-header">
      <span class="admin-metric-label">Confirmed Travel</span>
      <div class="admin-metric-icon icon-emerald">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
      </div>
    </div>
    <div class="admin-metric-value" style="color: var(--admin-success);"><?= (int)($counts['confirmed_bookings'] ?? 0) ?></div>
    <div class="admin-metric-subtext">Vouchers &amp; itineraries issued</div>
  </div>

  <!-- Total Paid Revenue -->
  <div class="admin-metric-card">
    <div class="admin-metric-header">
      <span class="admin-metric-label">Settled Revenue</span>
      <div class="admin-metric-icon icon-blue">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
      </div>
    </div>
    <div class="admin-metric-value" style="font-size: 1.6rem; color: var(--admin-info);">
      ₦<?= number_format((float)($counts['total_collected'] ?? 0), 2) ?>
    </div>
    <div class="admin-metric-subtext">Verified customer receipts</div>
  </div>
</div>

<!-- Filters & Search Toolbar -->
<div class="admin-toolbar">
  <div class="admin-tabs">
    <a href="/admin/bookings<?= !empty($searchQuery) ? '?search=' . urlencode($searchQuery) : '' ?>" 
       class="admin-tab <?= $currentStatus === '' ? 'active' : '' ?>">
      All Bookings (<?= (int)($counts['total_bookings'] ?? 0) ?>)
    </a>
    <a href="/admin/bookings?status=pending<?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?>" 
       class="admin-tab <?= $currentStatus === 'pending' ? 'active' : '' ?>">
      Pending Travel (<?= (int)($counts['pending_bookings'] ?? 0) ?>)
    </a>
    <a href="/admin/bookings?status=confirmed<?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?>" 
       class="admin-tab <?= $currentStatus === 'confirmed' ? 'active' : '' ?>">
      Confirmed (<?= (int)($counts['confirmed_bookings'] ?? 0) ?>)
    </a>
    <a href="/admin/bookings?status=cancelled<?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?>" 
       class="admin-tab <?= $currentStatus === 'cancelled' ? 'active' : '' ?>">
      Cancelled
    </a>
  </div>

  <form action="/admin/bookings" method="GET" class="admin-search-box">
    <?php if ($currentStatus !== ''): ?>
      <input type="hidden" name="status" value="<?= Security::e($currentStatus) ?>">
    <?php endif; ?>
    <input type="text" name="search" value="<?= Security::e($searchQuery) ?>" class="admin-search-input" placeholder="Search ref, guest name, phone...">
    <button type="submit" class="admin-btn admin-btn-outline admin-btn-sm">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
      Search
    </button>
    <?php if (!empty($searchQuery)): ?>
      <a href="/admin/bookings<?= !empty($currentStatus) ? '?status=' . Security::e($currentStatus) : '' ?>" class="admin-btn admin-btn-outline admin-btn-sm">Clear</a>
    <?php endif; ?>
  </form>
</div>

<!-- Bookings Table Card -->
<div class="admin-card">
  <?php if (empty($bookings)): ?>
    <div class="admin-empty-state">
      <div class="admin-empty-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
      </div>
      <h3 class="admin-empty-title">No Bookings Found</h3>
      <p class="admin-empty-desc">
        <?= !empty($searchQuery) || !empty($currentStatus) ? 'No bookings match your selected filter criteria.' : 'Tour package reservations will appear here once guests complete checkout.' ?>
      </p>
    </div>
  <?php else: ?>
    <div class="admin-table-container">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Reference</th>
            <th>Guest Customer</th>
            <th>Tour Package</th>
            <th style="text-align: center;">Guests</th>
            <th style="text-align: right;">Total Amount</th>
            <th style="text-align: center;">Payment</th>
            <th style="text-align: center;">Travel Status</th>
            <th>Date</th>
            <th style="text-align: right;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($bookings as $bk): ?>
            <?php 
              $payStatus = strtoupper((string)($bk['payment_status'] ?? 'PENDING'));
              $trvStatus = strtoupper((string)$bk['booking_status']);
            ?>
            <tr>
              <!-- Reference -->
              <td style="font-family: monospace; font-weight: 700;">
                <a href="/admin/bookings/<?= (int)$bk['id'] ?>" style="color: var(--admin-primary-light); text-decoration: none;">
                  <?= Security::e($bk['reference']) ?>
                </a>
              </td>

              <!-- Customer -->
              <td>
                <div style="font-weight: 600; color: var(--admin-text-main);"><?= Security::e($bk['customer_name']) ?></div>
                <div style="font-size: 12px; color: var(--admin-text-muted);"><?= Security::e($bk['customer_phone']) ?></div>
              </td>

              <!-- Package -->
              <td>
                <div style="font-weight: 500; color: #334155;"><?= Security::e($bk['package_title']) ?></div>
              </td>

              <!-- Guests -->
              <td style="text-align: center; font-weight: 600; color: var(--admin-text-main);">
                <?= (int)$bk['travellers_count'] ?>
              </td>

              <!-- Amount -->
              <td style="text-align: right; font-weight: 700; color: #0F172A;">
                ₦<?= number_format((float)$bk['total_amount'], 2) ?>
              </td>

              <!-- Payment Status -->
              <td style="text-align: center;">
                <?php if ($payStatus === 'PAID'): ?>
                  <span class="admin-badge admin-badge-success">Paid</span>
                <?php elseif ($payStatus === 'REJECTED'): ?>
                  <span class="admin-badge admin-badge-danger">Rejected</span>
                <?php else: ?>
                  <span class="admin-badge admin-badge-warning">Pending</span>
                <?php endif; ?>
              </td>

              <!-- Travel Status -->
              <td style="text-align: center;">
                <?php if ($trvStatus === 'CONFIRMED'): ?>
                  <span class="admin-badge admin-badge-info">Confirmed</span>
                <?php elseif ($trvStatus === 'CANCELLED'): ?>
                  <span class="admin-badge admin-badge-navy">Cancelled</span>
                <?php else: ?>
                  <span class="admin-badge admin-badge-warning">Pending Review</span>
                <?php endif; ?>
              </td>

              <!-- Date -->
              <td style="font-size: 12.5px; color: var(--admin-text-muted); white-space: nowrap;">
                <?= date('d M Y', strtotime($bk['created_at'])) ?>
              </td>

              <!-- Action -->
              <td style="text-align: right;">
                <a href="/admin/bookings/<?= (int)$bk['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm">
                  Manage &rarr;
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
