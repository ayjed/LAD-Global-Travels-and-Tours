<?php
/**
 * Administrator Login View
 * Sleek, Executive Anti-AI Portal Design
 */
use App\Core\Response;
use App\Core\Security;

$flashes = Response::getFlashes();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Executive Sign In — LAD Global Travels</title>
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
  <link rel="icon" type="image/png" sizes="64x64" href="/favicon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-login-body">

  <div class="admin-login-card">
    <div class="admin-login-header">
      <a href="/" title="Return to LAD Global Travels Public Site">
        <img src="/assets/images/lad-logo-header.png" alt="LAD Global Travels and Tours" class="admin-login-logo">
      </a>
      <h1 class="admin-login-title">Executive Sign In</h1>
      <p class="admin-login-subtitle">LAD Global Travels &amp; Tours Operations Console</p>
    </div>

    <!-- Flash Alerts -->
    <?php if (!empty($flashes)): ?>
      <div style="margin-bottom: 20px;">
        <?php foreach ($flashes as $type => $messages): ?>
          <?php foreach ($messages as $msg): ?>
            <div class="admin-badge admin-badge-<?= $type === 'success' ? 'success' : 'danger' ?>" style="display: flex; width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: var(--admin-radius-md); font-size: 12.5px; margin-bottom: 8px;" role="alert">
              <?= Security::e($msg) ?>
            </div>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form action="/admin/login" method="POST" novalidate>
      <?= Security::csrfField() ?>

      <div class="admin-form-group">
        <label for="username" class="admin-form-label required">Username or Work Email</label>
        <input type="text" id="username" name="username" class="admin-form-control" placeholder="admin or name@ladglobaltravels.com" required autofocus autocomplete="username">
      </div>

      <div class="admin-form-group">
        <label for="password" class="admin-form-label required">Password</label>
        <input type="password" id="password" name="password" class="admin-form-control" placeholder="••••••••••••" required autocomplete="current-password">
      </div>

      <button type="submit" class="admin-btn admin-btn-primary" style="width: 100%; padding: 12px; font-size: 14px; font-weight: 700; margin-top: 8px;">
        Sign In to Portal
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
      </button>
    </form>

    <div class="admin-login-security-notice">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
      <span>Encrypted TLS connection &bull; Multi-factor &amp; audit logging active</span>
    </div>

    <div style="text-align: center; margin-top: 18px;">
      <a href="/" class="admin-back-link" style="font-size: 12px; margin-bottom: 0;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Return to Public Website
      </a>
    </div>
  </div>

</body>
</html>
