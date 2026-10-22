<?php
/**
 * Admin Tour Packages Management View
 * @var array $packages
 * @var string $currentStatus
 * @var string $searchQuery
 */
use App\Core\Security;
?>

<div class="admin-topbar">
  <div>
    <h1 class="admin-topbar-title">Tour Packages</h1>
    <p class="admin-topbar-subtitle">Create, edit, publish, and manage holiday packages, pricing, and itineraries.</p>
  </div>
  <div class="admin-topbar-actions">
    <a href="/admin/packages/create" class="admin-btn admin-btn-primary">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
      Create New Package
    </a>
  </div>
</div>

<!-- Filters & Search Toolbar -->
<div class="admin-toolbar">
  <div class="admin-tabs">
    <a href="/admin/packages?status=all<?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?>" class="admin-tab <?= $currentStatus === 'all' ? 'active' : '' ?>">All (<?= count($packages) ?>)</a>
    <a href="/admin/packages?status=published<?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?>" class="admin-tab <?= $currentStatus === 'published' ? 'active' : '' ?>">Published</a>
    <a href="/admin/packages?status=draft<?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?>" class="admin-tab <?= $currentStatus === 'draft' ? 'active' : '' ?>">Drafts</a>
    <a href="/admin/packages?status=archived<?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?>" class="admin-tab <?= $currentStatus === 'archived' ? 'active' : '' ?>">Archived</a>
  </div>

  <form action="/admin/packages" method="GET" class="admin-search-box">
    <?php if ($currentStatus !== 'all'): ?>
      <input type="hidden" name="status" value="<?= Security::e($currentStatus) ?>">
    <?php endif; ?>
    <input type="text" name="search" class="admin-search-input" placeholder="Search destination or title..." value="<?= Security::e($searchQuery ?? '') ?>">
    <button type="submit" class="admin-btn admin-btn-outline admin-btn-sm">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
      Search
    </button>
    <?php if (!empty($searchQuery)): ?>
      <a href="/admin/packages?status=<?= Security::e($currentStatus) ?>" class="admin-btn admin-btn-outline admin-btn-sm" title="Clear Search">Clear</a>
    <?php endif; ?>
  </form>
</div>

<!-- Packages Table Card -->
<div class="admin-card">
  <?php if (empty($packages)): ?>
    <div class="admin-empty-state">
      <div class="admin-empty-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
      </div>
      <h3 class="admin-empty-title">No Tour Packages Found</h3>
      <p class="admin-empty-desc">
        <?= !empty($searchQuery) || $currentStatus !== 'all' ? 'No packages match your current search and filter criteria.' : 'No packages have been added yet. Click below to create your first package listing.' ?>
      </p>
      <a href="/admin/packages/create" class="admin-btn admin-btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        Add New Package
      </a>
    </div>
  <?php else: ?>
    <div class="admin-table-container">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 72px;">Cover</th>
            <th>Package Title &amp; Slug</th>
            <th>Destination</th>
            <th>Pricing &amp; Booking</th>
            <th>Status</th>
            <th>Bookings</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($packages as $pkg): ?>
            <tr>
              <!-- Cover Thumbnail -->
              <td>
                <?php if (!empty($pkg['cover_image'])): ?>
                  <img src="<?= Security::e($pkg['cover_image']) ?>" alt="Cover" style="width: 54px; height: 38px; object-fit: cover; border-radius: var(--admin-radius-sm); border: 1px solid var(--admin-border); display: block;">
                <?php else: ?>
                  <div style="width: 54px; height: 38px; background: #F1F5F9; border-radius: var(--admin-radius-sm); border: 1px solid var(--admin-border); display: flex; align-items: center; justify-content: center; color: #94A3B8;">
                    <svg style="width: 18px; height: 18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                  </div>
                <?php endif; ?>
              </td>

              <!-- Title & Slug -->
              <td>
                <div style="font-weight: 600; color: var(--admin-primary); margin-bottom: 2px;">
                  <a href="/admin/packages/<?= (int)$pkg['id'] ?>/edit" style="color: var(--admin-primary); text-decoration: none;">
                    <?= Security::e($pkg['title']) ?>
                  </a>
                  <?php if (!empty($pkg['is_featured'])): ?>
                    <span class="admin-badge admin-badge-warning" style="font-size: 10px; padding: 2px 7px; margin-left: 6px;">Featured</span>
                  <?php endif; ?>
                </div>
                <div style="font-family: monospace; font-size: 11.5px; color: var(--admin-text-light);">
                  /packages/<?= Security::e($pkg['slug']) ?>
                </div>
              </td>

              <!-- Destination -->
              <td>
                <div style="font-weight: 600; color: var(--admin-text-main);"><?= Security::e($pkg['destination_country']) ?></div>
                <?php if (!empty($pkg['destination_city'])): ?>
                  <div style="color: var(--admin-text-muted); font-size: 12px;"><?= Security::e($pkg['destination_city']) ?></div>
                <?php endif; ?>
              </td>

              <!-- Price & Mode -->
              <td>
                <?php if (!empty($pkg['price_amount']) && $pkg['price_amount'] > 0): ?>
                  <div style="font-weight: 700; color: #0F172A;">₦<?= number_format((float)$pkg['price_amount'], 2) ?></div>
                  <div style="margin-top: 3px;">
                    <span class="admin-badge <?= !empty($pkg['is_payment_enabled']) ? 'admin-badge-success' : 'admin-badge-info' ?>" style="font-size: 10.5px; padding: 2px 7px;">
                      <?= !empty($pkg['is_payment_enabled']) ? 'Direct Booking' : 'Quote Only' ?>
                    </span>
                  </div>
                <?php else: ?>
                  <span class="admin-badge admin-badge-navy" style="font-size: 10.5px; padding: 2px 7px;">Custom Quote</span>
                <?php endif; ?>
              </td>

              <!-- Status -->
              <td>
                <?php if ($pkg['status'] === 'published'): ?>
                  <span class="admin-badge admin-badge-success">Published</span>
                <?php elseif ($pkg['status'] === 'draft'): ?>
                  <span class="admin-badge admin-badge-warning">Draft</span>
                <?php else: ?>
                  <span class="admin-badge admin-badge-navy">Archived</span>
                <?php endif; ?>
              </td>

              <!-- Bookings Count -->
              <td>
                <?php $bCount = (int)($pkg['bookings_count'] ?? 0); ?>
                <span class="admin-badge <?= $bCount > 0 ? 'admin-badge-info' : 'admin-badge-navy' ?>" style="font-weight: 600;">
                  <?= $bCount ?> <?= $bCount === 1 ? 'Booking' : 'Bookings' ?>
                </span>
              </td>

              <!-- Actions -->
              <td style="text-align: right;">
                <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                  <!-- View Live (if published) -->
                  <?php if ($pkg['status'] === 'published'): ?>
                    <a href="/packages/<?= Security::e($pkg['slug']) ?>" target="_blank" class="admin-btn admin-btn-outline admin-btn-sm" title="View live page on website">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                      Preview
                    </a>
                  <?php endif; ?>

                  <!-- Edit -->
                  <a href="/admin/packages/<?= (int)$pkg['id'] ?>/edit" class="admin-btn admin-btn-outline admin-btn-sm" title="Edit Package">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    Edit
                  </a>

                  <!-- Status Change Dropdown / Toggle -->
                  <form action="/admin/packages/<?= (int)$pkg['id'] ?>/status" method="POST" style="display: inline; margin: 0;">
                    <?= Security::csrfField() ?>
                    <?php if ($pkg['status'] === 'published'): ?>
                      <input type="hidden" name="status" value="draft">
                      <button type="submit" class="admin-btn admin-btn-outline admin-btn-sm" title="Switch status to Draft">Unpublish</button>
                    <?php else: ?>
                      <input type="hidden" name="status" value="published">
                      <button type="submit" class="admin-btn admin-btn-outline admin-btn-sm" style="color: var(--admin-success); border-color: var(--admin-success-border);" title="Publish Package Live">Publish</button>
                    <?php endif; ?>
                  </form>

                  <!-- Delete or Archive -->
                  <form action="/admin/packages/<?= (int)$pkg['id'] ?>/delete" method="POST" style="display: inline; margin: 0;" onsubmit="return confirm('<?= $bCount > 0 ? "This package has recorded bookings and cannot be deleted. Archive it instead?" : "Are you sure you want to permanently delete this package?" ?>');">
                    <?= Security::csrfField() ?>
                    <?php if ($bCount > 0): ?>
                      <input type="hidden" name="action" value="archive">
                      <button type="submit" class="admin-btn admin-btn-outline admin-btn-sm" style="color: var(--admin-warning);" title="Package has bookings: Archive instead">Archive</button>
                    <?php else: ?>
                      <input type="hidden" name="action" value="delete">
                      <button type="submit" class="admin-btn admin-btn-outline admin-btn-sm" style="color: var(--admin-danger); border-color: var(--admin-danger-border);" title="Delete Package">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                      </button>
                    <?php endif; ?>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
