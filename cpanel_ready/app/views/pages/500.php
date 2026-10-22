<?php
$company = \App\Services\SettingService::getCompanyDetails();
$waUrl = \App\Services\SettingService::formatWhatsAppUrl($company['whatsapp'] ?? '2347017597650', 'Hello LAD Global Travels, I encountered an issue on your website.');
?>
<section class="section" style="padding: var(--space-16) 0;">
  <div class="container container-narrow text-center">
    <span class="badge badge-danger" style="margin-bottom: var(--space-3);">Error 500</span>
    <h1 style="font-size: 3rem; margin-bottom: var(--space-4);">System Error</h1>
    <p class="text-muted" style="font-size: var(--font-size-lg); line-height: 1.6; margin-bottom: var(--space-8);">
      Our server encountered an unexpected error while processing your request. Our technical team has been notified.
    </p>
    <div style="display: flex; gap: var(--space-4); justify-content: center; flex-wrap: wrap;">
      <a href="/" class="btn btn-primary btn-lg">Return to Homepage</a>
      <a href="<?= $waUrl ?>" target="_blank" rel="noopener noreferrer" class="btn btn-accent btn-lg">Reach Us on WhatsApp</a>
    </div>
  </div>
</section>
