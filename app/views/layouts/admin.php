<?php
/**
 * Administrator Console Layout — Modern Enterprise Edition
 * Clean, Bespoke, Anti-AI Executive Portal
 * @var string $content View content slot
 * @var string|null $pageTitle Optional page title
 */
use App\Core\Auth;
use App\Core\Response;
use App\Core\Security;

$flashes = Response::getFlashes();
$title = !empty($pageTitle) ? Security::e($pageTitle) . ' | LAD Global Admin' : 'LAD Global Admin Portal';
$username = Security::e(Auth::username() ?? 'Admin');
$avatarInitial = strtoupper(substr($username, 0, 1) ?: 'A');

$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$isDashboard = ($currentUri === '/admin' || $currentUri === '/admin/');
$isPackages  = str_starts_with($currentUri, '/admin/packages');
$isEnquiries = str_starts_with($currentUri, '/admin/enquiries');
$isBookings  = str_starts_with($currentUri, '/admin/bookings');
$isPayments  = str_starts_with($currentUri, '/admin/payments');
$isSettings  = str_starts_with($currentUri, '/admin/settings');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?></title>
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
  <link rel="icon" type="image/png" sizes="64x64" href="/favicon.png">
  
  <!-- Modern Clean Typography -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/assets/css/app.css">
  <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-body">
  <!-- Accessible Skip Link -->
  <a href="#adminMainContent" class="skip-to-content">Skip to admin workspace</a>

  <div class="admin-shell">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar" aria-label="Administrator Navigation">
      <!-- Sidebar Header / Brand Logo -->
      <div class="admin-sidebar-header">
        <a href="/admin" class="admin-brand">
          <img src="/assets/images/lad-logo-footer.png" alt="LAD Global Travels" class="admin-brand-logo">
        </a>
      </div>

      <!-- Navigation Menu -->
      <div class="admin-nav-section-title">Operations</div>
      <ul class="admin-nav">
        <!-- Dashboard -->
        <li class="admin-nav-item">
          <a href="/admin" class="<?= $isDashboard ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
            <span>Executive Overview</span>
          </a>
        </li>

        <!-- Tour Packages -->
        <li class="admin-nav-item">
          <a href="/admin/packages" class="<?= $isPackages ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
            <span>Tour Packages</span>
          </a>
        </li>

        <!-- Enquiries & Leads -->
        <li class="admin-nav-item">
          <a href="/admin/enquiries" class="<?= $isEnquiries ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <span>Enquiries &amp; Leads</span>
          </a>
        </li>

        <!-- Bookings -->
        <li class="admin-nav-item">
          <a href="/admin/bookings" class="<?= $isBookings ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
            <span>Tour Bookings</span>
          </a>
        </li>

        <!-- Payments & Audit -->
        <li class="admin-nav-item">
          <a href="/admin/payments" class="<?= $isPayments ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
            <span>Payments &amp; Reconciliation</span>
          </a>
        </li>
      </ul>

      <div class="admin-nav-section-title">Configuration</div>
      <ul class="admin-nav">
        <!-- Settings -->
        <li class="admin-nav-item">
          <a href="/admin/settings" class="<?= $isSettings ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
            <span>Agency Settings</span>
          </a>
        </li>

        <!-- View Website -->
        <li class="admin-nav-item">
          <a href="/" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
            <span>View Live Website</span>
          </a>
        </li>
      </ul>

      <!-- Sidebar Footer / Admin Identity -->
      <div class="admin-sidebar-footer">
        <div class="admin-user-profile">
          <div class="admin-avatar" title="<?= $username ?>"><?= $avatarInitial ?></div>
          <div class="admin-user-info">
            <span class="admin-user-name"><?= $username ?></span>
            <span class="admin-user-role"><span class="admin-status-dot"></span> Administrator</span>
          </div>
          <form action="/admin/logout" method="POST" style="margin: 0;">
            <?= Security::csrfField() ?>
            <button type="submit" class="nav-btn" title="Sign Out" style="padding: 6px; width: 32px; height: 32px; border-radius: 6px; color: #F87171;">
              <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
            </button>
          </form>
        </div>
      </div>
    </aside>

    <!-- Main Workspace -->
    <main id="adminMainContent" class="admin-main">
      <!-- Global Utility Bar & Breadcrumbs -->
      <header class="admin-topbar" style="margin-bottom: 24px; padding-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 8px; font-size: 13px;">
          <span style="color: #64748B; font-weight: 500;">Console</span>
          <span style="color: #CBD5E1;">/</span>
          <span style="font-weight: 700; color: var(--admin-primary);"><?= !empty($pageTitle) ? Security::e($pageTitle) : 'Overview' ?></span>
        </div>
        <div class="admin-topbar-actions">
          <span class="admin-badge admin-badge-success" style="font-size: 11px; padding: 3px 9px;">
            Live Production
          </span>
          <a href="/" class="admin-btn admin-btn-outline admin-btn-sm" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
            Public Site
          </a>
        </div>
      </header>

      <!-- Alert Banners -->
      <?php if (!empty($flashes)): ?>
        <div style="margin-bottom: 24px;">
          <?php foreach ($flashes as $type => $messages): ?>
            <?php foreach ($messages as $msg): ?>
              <div class="admin-badge admin-badge-<?= $type === 'success' ? 'success' : ($type === 'danger' ? 'danger' : 'info') ?>" 
                   style="display: flex; padding: 12px 18px; border-radius: var(--admin-radius-md); font-size: 13.5px; margin-bottom: 8px; width: 100%; box-sizing: border-box;" 
                   role="alert">
                <span><?= Security::e($msg) ?></span>
              </div>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- View Content Slot -->
      <?= $content ?>
    </main>
  </div>

  <script src="/assets/js/app.js"></script>
</body>
</html>
