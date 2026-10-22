<?php
/**
 * Admin Enquiries & CRM Consultation Management View
 * @var array $enquiries List of enquiries
 * @var array $counts Aggregated counts by status
 * @var string $currentStatus Active status filter
 * @var string $currentService Active service filter
 * @var string $searchQuery Search query string
 */
use App\Core\Security;
?>

<div class="admin-topbar">
  <div>
    <h1 class="admin-topbar-title">Customer Enquiries &amp; Leads</h1>
    <p class="admin-topbar-subtitle">Review, track, and manage customer consultation inquiries from all travel services.</p>
  </div>
</div>

<!-- Status Counts Summary Metrics Grid -->
<div class="admin-metrics-grid">
  <!-- New Uncontacted -->
  <div class="admin-metric-card">
    <div class="admin-metric-header">
      <span class="admin-metric-label">New Leads</span>
      <div class="admin-metric-icon icon-blue">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
      </div>
    </div>
    <div class="admin-metric-value" style="color: var(--admin-info);"><?= (int)($counts['new'] ?? 0) ?></div>
    <div class="admin-metric-subtext">Awaiting initial outreach</div>
  </div>

  <!-- In Contact -->
  <div class="admin-metric-card">
    <div class="admin-metric-header">
      <span class="admin-metric-label">In Contact</span>
      <div class="admin-metric-icon icon-amber">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
      </div>
    </div>
    <div class="admin-metric-value" style="color: var(--admin-warning);"><?= (int)($counts['contacted'] ?? 0) ?></div>
    <div class="admin-metric-subtext">Active consultation in progress</div>
  </div>

  <!-- Quoted -->
  <div class="admin-metric-card">
    <div class="admin-metric-header">
      <span class="admin-metric-label">Quoted / Proposals</span>
      <div class="admin-metric-icon icon-navy">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
      </div>
    </div>
    <div class="admin-metric-value" style="color: var(--admin-primary);"><?= (int)($counts['quoted'] ?? 0) ?></div>
    <div class="admin-metric-subtext">Formal quotes submitted</div>
  </div>

  <!-- Closed / Won -->
  <div class="admin-metric-card">
    <div class="admin-metric-header">
      <span class="admin-metric-label">Closed / Won</span>
      <div class="admin-metric-icon icon-emerald">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
      </div>
    </div>
    <div class="admin-metric-value" style="color: var(--admin-success);"><?= (int)($counts['closed'] ?? 0) ?></div>
    <div class="admin-metric-subtext">Successfully converted clients</div>
  </div>
</div>

<!-- Filters & Search Toolbar -->
<div class="admin-toolbar">
  <!-- Status Filter Tabs -->
  <div class="admin-tabs">
    <a href="/admin/enquiries?status=all<?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?><?= !empty($currentService) && $currentService !== 'all' ? '&service=' . urlencode($currentService) : '' ?>" class="admin-tab <?= $currentStatus === 'all' ? 'active' : '' ?>">All (<?= (int)($counts['total'] ?? count($enquiries)) ?>)</a>
    <a href="/admin/enquiries?status=new<?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?><?= !empty($currentService) && $currentService !== 'all' ? '&service=' . urlencode($currentService) : '' ?>" class="admin-tab <?= $currentStatus === 'new' ? 'active' : '' ?>">New (<?= (int)($counts['new'] ?? 0) ?>)</a>
    <a href="/admin/enquiries?status=contacted<?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?><?= !empty($currentService) && $currentService !== 'all' ? '&service=' . urlencode($currentService) : '' ?>" class="admin-tab <?= $currentStatus === 'contacted' ? 'active' : '' ?>">Contacted</a>
    <a href="/admin/enquiries?status=quoted<?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?><?= !empty($currentService) && $currentService !== 'all' ? '&service=' . urlencode($currentService) : '' ?>" class="admin-tab <?= $currentStatus === 'quoted' ? 'active' : '' ?>">Quoted</a>
    <a href="/admin/enquiries?status=closed<?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?><?= !empty($currentService) && $currentService !== 'all' ? '&service=' . urlencode($currentService) : '' ?>" class="admin-tab <?= $currentStatus === 'closed' ? 'active' : '' ?>">Closed</a>
  </div>

  <!-- Search and Service Filters -->
  <form action="/admin/enquiries" method="GET" class="admin-search-box">
    <?php if ($currentStatus !== 'all'): ?>
      <input type="hidden" name="status" value="<?= Security::e($currentStatus) ?>">
    <?php endif; ?>

    <select name="service" class="admin-search-input" style="width: 150px;" onchange="this.form.submit()">
      <option value="all">All Services</option>
      <option value="visa" <?= $currentService === 'visa' ? 'selected' : '' ?>>Visa Assistance</option>
      <option value="tours" <?= $currentService === 'tours' ? 'selected' : '' ?>>Tour Packages</option>
      <option value="flights" <?= $currentService === 'flights' ? 'selected' : '' ?>>Flight Booking</option>
      <option value="hotels" <?= $currentService === 'hotels' ? 'selected' : '' ?>>Hotel Advisory</option>
      <option value="study" <?= $currentService === 'study' ? 'selected' : '' ?>>Study Abroad</option>
      <option value="canada_pr" <?= $currentService === 'canada_pr' ? 'selected' : '' ?>>Canada PR</option>
      <option value="cbi" <?= $currentService === 'cbi' ? 'selected' : '' ?>>CBI Advisory</option>
      <option value="rbi" <?= $currentService === 'rbi' ? 'selected' : '' ?>>RBI Advisory</option>
    </select>

    <input type="text" name="search" class="admin-search-input" placeholder="Search ref, name, phone..." value="<?= Security::e($searchQuery ?? '') ?>">
    <button type="submit" class="admin-btn admin-btn-outline admin-btn-sm">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
      Search
    </button>
    <?php if (!empty($searchQuery) || (!empty($currentService) && $currentService !== 'all')): ?>
      <a href="/admin/enquiries?status=<?= Security::e($currentStatus) ?>" class="admin-btn admin-btn-outline admin-btn-sm">Clear</a>
    <?php endif; ?>
  </form>
</div>

<!-- Enquiries Table Card -->
<div class="admin-card">
  <?php if (empty($enquiries)): ?>
    <div class="admin-empty-state">
      <div class="admin-empty-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
      </div>
      <h3 class="admin-empty-title">No Enquiries Found</h3>
      <p class="admin-empty-desc">
        <?= !empty($searchQuery) || $currentStatus !== 'all' || $currentService !== 'all' ? 'No consultation enquiries match your current search and filter criteria.' : 'Consultation inquiries from clients will appear here automatically.' ?>
      </p>
    </div>
  <?php else: ?>
    <div class="admin-table-container">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Reference</th>
            <th>Client Customer</th>
            <th>Service / Itinerary</th>
            <th>Status</th>
            <th>Follow-up</th>
            <th>Notes</th>
            <th>Date</th>
            <th style="text-align: right;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($enquiries as $enq): ?>
            <?php
              $cleanPhone = preg_replace('/[^0-9]/', '', (string)$enq['phone']);
              if (str_starts_with($cleanPhone, '0')) {
                  $cleanPhone = '234' . substr($cleanPhone, 1);
              }
            ?>
            <tr>
              <!-- Reference -->
              <td style="font-family: monospace; font-weight: 700;">
                <a href="/admin/enquiries/<?= (int)$enq['id'] ?>" style="color: var(--admin-primary-light); text-decoration: none;">
                  <?= Security::e($enq['reference']) ?>
                </a>
              </td>

              <!-- Customer Details & Quick Contact Links -->
              <td>
                <div style="font-weight: 600; color: var(--admin-text-main);"><?= Security::e($enq['full_name']) ?></div>
                <div style="font-size: 12px; color: var(--admin-text-muted); margin-top: 2px; display: flex; align-items: center; gap: 8px;">
                  <a href="tel:<?= Security::e($enq['phone']) ?>" style="color: var(--admin-text-muted); text-decoration: none;">
                    <?= Security::e($enq['phone']) ?>
                  </a>
                  <span>&bull;</span>
                  <a href="https://wa.me/<?= $cleanPhone ?>?text=Hello%20<?= urlencode($enq['full_name']) ?>,%20this%20is%20LAD%20Global%20Travels%20regarding%20your%20enquiry%20<?= urlencode($enq['reference']) ?>." target="_blank" rel="noopener" style="color: var(--admin-success); text-decoration: none; font-weight: 600;">
                    WhatsApp
                  </a>
                </div>
                <div style="font-size: 11.5px; color: var(--admin-text-light);">
                  <?= Security::e($enq['email']) ?>
                </div>
              </td>

              <!-- Service & Linked Package -->
              <td>
                <span class="admin-badge admin-badge-navy" style="font-size: 10.5px;"><?= Security::e(strtoupper($enq['service_type'])) ?></span>
                <?php if (!empty($enq['package_title'])): ?>
                  <div style="font-size: 12px; color: var(--admin-primary); font-weight: 600; margin-top: 4px;">
                    <?= Security::e($enq['package_title']) ?>
                  </div>
                <?php elseif (!empty($enq['destination'])): ?>
                  <div style="font-size: 12px; color: var(--admin-text-muted); margin-top: 2px;">
                    <?= Security::e($enq['destination']) ?>
                  </div>
                <?php endif; ?>
              </td>

              <!-- Status Badge -->
              <td>
                <?php if ($enq['status'] === 'new'): ?>
                  <span class="admin-badge admin-badge-info">New</span>
                <?php elseif ($enq['status'] === 'contacted'): ?>
                  <span class="admin-badge admin-badge-warning">Contacted</span>
                <?php elseif ($enq['status'] === 'quoted'): ?>
                  <span class="admin-badge admin-badge-navy">Quoted</span>
                <?php elseif ($enq['status'] === 'closed'): ?>
                  <span class="admin-badge admin-badge-success">Closed</span>
                <?php else: ?>
                  <span class="admin-badge admin-badge-danger">Cancelled</span>
                <?php endif; ?>
              </td>

              <!-- Follow-up -->
              <td style="font-size: 12.5px; color: var(--admin-text-muted);">
                <?php if (!empty($enq['follow_up_date'])): ?>
                  <span style="font-weight: 600; color: #334155;"><?= date('d M Y', strtotime($enq['follow_up_date'])) ?></span>
                <?php else: ?>
                  <span style="color: var(--admin-text-light);">&mdash;</span>
                <?php endif; ?>
              </td>

              <!-- Notes Count -->
              <td>
                <span class="admin-badge admin-badge-navy" style="font-size: 11px;">
                  <?= (int)($enq['notes_count'] ?? 0) ?> notes
                </span>
              </td>

              <!-- Date -->
              <td style="font-size: 12.5px; color: var(--admin-text-muted); white-space: nowrap;">
                <?= date('d M Y', strtotime($enq['created_at'])) ?>
              </td>

              <!-- Action -->
              <td style="text-align: right;">
                <a href="/admin/enquiries/<?= (int)$enq['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm">
                  Review &rarr;
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
