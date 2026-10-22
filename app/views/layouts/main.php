<?php
/**
 * Main Public Layout
 * @var string $content View content rendered in slot
 * @var string|null $pageTitle Optional custom page title
 * @var string|null $pageDescription Optional meta description
 */
use App\Core\Response;
use App\Core\Security;

$flashes = Response::getFlashes();
$title = !empty($pageTitle) ? Security::e($pageTitle) . ' — LAD Global Travels' : 'LAD Global Travels and Tours — Explore • Discover • Experience';
$description = !empty($pageDescription) ? Security::e($pageDescription) : 'Professional travel assistance, visa guidance, and curated international holiday tour packages from Lagos, Nigeria.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?></title>
  <meta name="description" content="<?= $description ?>">
  <meta name="theme-color" content="#092A60">

  <!-- OpenGraph Metadata -->
  <meta property="og:title" content="<?= $title ?>">
  <meta property="og:description" content="<?= $description ?>">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="LAD Global Travels and Tours">
  <meta property="og:image" content="/assets/images/logo.png">

  <!-- Twitter Card Metadata -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= $title ?>">
  <meta name="twitter:description" content="<?= $description ?>">
  <meta name="twitter:image" content="/assets/images/logo.png">

  <!-- Favicon Icons -->
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
  <link rel="icon" type="image/png" sizes="64x64" href="/favicon.png">
  <link rel="apple-touch-icon" href="/assets/images/logo-160.png">

  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
  <!-- Accessible Skip to Main Content Link for Keyboard and Screen Reader Users -->
  <a href="#mainContent" class="skip-to-content">Skip to main content</a>

  <!-- Site Navigation -->
  <header class="site-header">
    <div class="container header-inner">
      <a href="/" class="brand-logo" aria-label="LAD Global Travels and Tours Home">
        <img src="/assets/images/lad-logo-header.png" alt="LAD Global Travels and Tours" class="brand-header-logo" width="182" height="52">
        <span class="sr-only">LAD Global Travels and Tours — Explore • Discover • Experience</span>
      </a>

      <nav aria-label="Primary Navigation">
        <ul class="nav-links">
          <li><a href="/" class="nav-link <?= ($activeNav ?? '') === 'home' ? 'active' : '' ?>">Home</a></li>
          <li><a href="/about" class="nav-link <?= ($activeNav ?? '') === 'about' ? 'active' : '' ?>">About</a></li>
          <li><a href="/services" class="nav-link <?= ($activeNav ?? '') === 'services' ? 'active' : '' ?>">Services</a></li>
          <li><a href="/packages" class="nav-link <?= ($activeNav ?? '') === 'packages' ? 'active' : '' ?>">Tour Packages</a></li>
          <li><a href="/faq" class="nav-link <?= ($activeNav ?? '') === 'faq' ? 'active' : '' ?>">FAQs</a></li>
          <li><a href="/contact" class="nav-link <?= ($activeNav ?? '') === 'contact' ? 'active' : '' ?>">Contact</a></li>
        </ul>
      </nav>

      <div class="header-cta-group">
        <a href="/contact" class="btn btn-accent btn-sm">Plan Your Trip</a>
        <button id="mobileMenuToggle" class="mobile-menu-toggle" aria-label="Toggle navigation menu" aria-expanded="false">
          ☰
        </button>
      </div>
    </div>
  </header>

  <!-- Mobile Off-Canvas Drawer -->
  <div id="mobileNavDrawer" class="mobile-nav-drawer" role="dialog" aria-modal="true" aria-label="Mobile Navigation">
    <ul class="mobile-nav-links">
      <li><a href="/" class="mobile-nav-link <?= ($activeNav ?? '') === 'home' ? 'active' : '' ?>">Home</a></li>
      <li><a href="/about" class="mobile-nav-link <?= ($activeNav ?? '') === 'about' ? 'active' : '' ?>">About Us</a></li>
      <li><a href="/services" class="mobile-nav-link <?= ($activeNav ?? '') === 'services' ? 'active' : '' ?>">Our Services</a></li>
      <li><a href="/packages" class="mobile-nav-link <?= ($activeNav ?? '') === 'packages' ? 'active' : '' ?>">Tour Packages</a></li>
      <li><a href="/faq" class="mobile-nav-link <?= ($activeNav ?? '') === 'faq' ? 'active' : '' ?>">FAQs</a></li>
      <li><a href="/contact" class="mobile-nav-link <?= ($activeNav ?? '') === 'contact' ? 'active' : '' ?>">Contact Us</a></li>
      <li style="margin-top: var(--space-4);"><a href="/contact" class="btn btn-primary btn-block">Plan Your Trip</a></li>
    </ul>
  </div>

  <!-- Global Flash Messages Notification Banner -->
  <?php if (!empty($flashes)): ?>
    <div class="container" style="margin-top: var(--space-4);">
      <?php foreach ($flashes as $type => $messages): ?>
        <?php foreach ($messages as $msg): ?>
          <div class="badge badge-<?= $type === 'success' ? 'success' : ($type === 'danger' ? 'danger' : 'info') ?>" style="display:block; padding:12px 16px; border-radius:var(--radius-md); font-size:var(--font-size-sm); margin-bottom:var(--space-2); text-transform:none;" role="alert">
            <?= Security::e($msg) ?>
          </div>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- Main View Content Slot -->
  <main id="mainContent">
    <?= $content ?>
  </main>

<?php
$company = \App\Services\SettingService::getCompanyDetails();
$social = \App\Services\SettingService::getSocialLinks();
$waUrl = \App\Services\SettingService::formatWhatsAppUrl($company['whatsapp'], 'Hello LAD Global Travels, I would like to make an enquiry.');
$phoneLink = \App\Services\SettingService::formatPhoneLink($company['phone']);
?>
  <!-- Site Footer -->
  <footer class="site-footer">
    <div class="container">
      <div class="grid-4" style="gap: var(--space-8);">
        <div>
          <div class="brand-logo footer-brand-wrap" style="margin-bottom: var(--space-3);">
            <a href="/" aria-label="LAD Global Travels and Tours Home" style="text-decoration: none; display: inline-flex;">
              <img src="/assets/images/lad-logo-footer.png" alt="LAD Global Travels and Tours" class="brand-footer-logo" width="185" height="50">
            </a>
            <span class="sr-only"><?= \App\Core\Security::e($company['name']) ?></span>
          </div>
          <p class="footer-description" style="font-size: var(--font-size-sm); line-height: 1.6; color: #CBD5E1; margin: 0 0 var(--space-4); max-width: 320px;">
            LAD Global Travels and Tours is your trusted travel and global mobility partner, providing expert visa assistance, personalized travel planning, international tour packages, and travel advisory services.
          <div style="display: flex; gap: var(--space-4); align-items: center;">
            <?php if (!empty($social['instagram'])): ?>
              <a href="<?= \App\Core\Security::e($social['instagram']) ?>" target="_blank" rel="noopener noreferrer" style="color: #E2E8F0; font-size: 0.85rem; display: flex; align-items: center; gap: 6px;" aria-label="LAD Global on Instagram">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg> Instagram
              </a>
            <?php endif; ?>
            <?php if (!empty($social['facebook'])): ?>
              <a href="<?= \App\Core\Security::e($social['facebook']) ?>" target="_blank" rel="noopener noreferrer" style="color: #E2E8F0; font-size: 0.85rem; display: flex; align-items: center; gap: 6px;" aria-label="LAD Global on Facebook">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg> Facebook
              </a>
            <?php endif; ?>
            <?php if (!empty($social['linkedin'])): ?>
              <a href="<?= \App\Core\Security::e($social['linkedin']) ?>" target="_blank" rel="noopener noreferrer" style="color: #E2E8F0; font-size: 0.85rem; display: flex; align-items: center; gap: 6px;" aria-label="LAD Global on LinkedIn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/></svg> LinkedIn
              </a>
            <?php endif; ?>
          </div>
        </div>

        <div>
          <h4 style="color: var(--color-accent); margin-bottom: var(--space-3); font-size: 1rem;">Our Services</h4>
          <ul style="list-style: none; display: flex; flex-direction: column; gap: var(--space-2); font-size: var(--font-size-sm);">
            <li><a href="/services/visa-assistance">Visa Assistance</a></li>
            <li><a href="/packages">International Tour Packages</a></li>
            <li><a href="/services/flight-assistance">Flight Booking Assistance</a></li>
            <li><a href="/services/hotel-reservations">Hotel Reservations</a></li>
            <li><a href="/services/study-abroad">Study Abroad Advisory</a></li>
            <li><a href="/services/canada-pr">Canada PR Guidance</a></li>
          </ul>
        </div>

        <div>
          <h4 style="color: var(--color-accent); margin-bottom: var(--space-3); font-size: 1rem;">Quick Links</h4>
          <ul style="list-style: none; display: flex; flex-direction: column; gap: var(--space-2); font-size: var(--font-size-sm);">
            <li><a href="/">Home</a></li>
            <li><a href="/about">About Us</a></li>
            <li><a href="/services">Our Services</a></li>
            <li><a href="/packages">Browse Packages</a></li>
            <li><a href="/faq">FAQs</a></li>
            <li><a href="/contact">Contact Us</a></li>
            <li><a href="/admin/login">Admin Sign In</a></li>
          </ul>
        </div>

        <div>
          <h4 style="color: var(--color-accent); margin-bottom: var(--space-3); font-size: 1rem;">Office & Contact</h4>
          <p style="font-size: var(--font-size-sm); color: #CBD5E1; margin-bottom: var(--space-2); display: flex; gap: 8px; align-items: flex-start;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 3px; color: var(--color-accent);"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            <span><?= \App\Core\Security::e($company['address']) ?></span>
          </p>
          <p style="font-size: var(--font-size-sm); color: #CBD5E1; margin-bottom: var(--space-2); display: flex; gap: 8px; align-items: center;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; color: var(--color-accent);"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <span>Call: <a href="<?= $phoneLink ?>"><?= \App\Core\Security::e($company['phone']) ?></a></span>
          </p>
          <p style="font-size: var(--font-size-sm); color: #CBD5E1; margin-bottom: var(--space-2); display: flex; gap: 8px; align-items: center;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; color: #25D366;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <span>WhatsApp: <a href="<?= $waUrl ?>" target="_blank" rel="noopener noreferrer">Chat with Us</a></span>
          </p>
          <p style="font-size: var(--font-size-xs); color: #94A3B8; display: flex; gap: 8px; align-items: center;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; color: #94A3B8;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span><?= \App\Core\Security::e($company['hours']) ?></span>
          </p>
        </div>
      </div>

      <div class="footer-bottom">
        <div>© 2026 <?= \App\Core\Security::e($company['name']) ?>. All Rights Reserved.</div>
        <div style="display: flex; gap: var(--space-4);">
          <a href="/privacy">Privacy Policy</a>
          <a href="/terms">Terms of Service</a>
          <a href="/refund-policy">Refund Policy</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Persistent Floating WhatsApp Button -->
  <a href="<?= $waUrl ?>" class="whatsapp-floating" target="_blank" rel="noopener noreferrer" aria-label="Chat with LAD Global Travels on WhatsApp">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.456 5.711 1.457h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
    <span>Chat on WhatsApp</span>
  </a>

  <!-- Core Scripts -->
  <script src="/assets/js/app.js"></script>
</body>
</html>
