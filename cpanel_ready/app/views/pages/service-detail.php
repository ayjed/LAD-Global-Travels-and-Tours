<?php
/**
 * Public Service Detail View
 * Comprehensive description page for each of LAD Global's 8 travel and mobility services.
 * 
 * @var array $service Current service definition from TravelServiceCatalog
 * @var array $allServices All 8 services for sidebar navigation
 */
use App\Core\Security;
use App\Services\SettingService;

$company = SettingService::getCompanyDetails();
$waMsg = $service['whatsapp_msg'] ?? 'Hello LAD Global Travels, I would like to make an enquiry about ' . $service['short_title'] . '.';
$waUrl = SettingService::formatWhatsAppUrl($company['whatsapp'] ?? '2347017597650', $waMsg);
$phoneLink = SettingService::formatPhoneLink($company['phone'] ?? '07017597650');
?>

<!-- Page Hero Section -->
<section class="page-hero page-hero-packages">
  <div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="Breadcrumb" style="margin-bottom: var(--space-3);">
      <ol style="display: flex; gap: 8px; align-items: center; list-style: none; padding: 0; margin: 0; font-size: var(--font-size-sm); flex-wrap: wrap;">
        <li><a href="/" style="color: #CBD5E1; text-decoration: none;">Home</a></li>
        <li style="color: #64748B;">/</li>
        <li><a href="/services" style="color: #CBD5E1; text-decoration: none;">Our Services</a></li>
        <li style="color: #64748B;">/</li>
        <li style="color: var(--color-accent); font-weight: 600;" aria-current="page"><?= Security::e($service['short_title']) ?></li>
      </ol>
    </nav>

    <div style="display: flex; gap: var(--space-2); margin-bottom: var(--space-3);">
      <span class="eyebrow-pill"><?= Security::e($service['eyebrow'] ?? 'Travel & Mobility Service') ?></span>
      <span class="badge <?= Security::e($service['badge_class'] ?? 'badge-info') ?>" style="align-self: center;"><?= Security::e($service['badge']) ?></span>
    </div>

    <h1 class="page-hero-title"><?= Security::e($service['title']) ?></h1>
    <p class="page-hero-desc">
      <?= Security::e($service['tagline']) ?>
    </p>

    <!-- Hero CTA Buttons -->
    <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6); flex-wrap: wrap;">
      <a href="<?= Security::e($service['cta_url']) ?>" class="btn btn-accent btn-lg" style="font-weight: 700;">
        <?= Security::e($service['cta_text']) ?> →
      </a>
      <a href="<?= Security::e($waUrl) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg" style="display: inline-flex; align-items: center; gap: 8px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
        Chat on WhatsApp
      </a>
    </div>
  </div>
</section>

<!-- Main Content Grid -->
<section class="section" style="padding: var(--space-16) 0; background-color: #F8FAFC;">
  <div class="container">
    <div class="package-detail-grid">

      <!-- Left Column: Main Service Narrative -->
      <div>
        
        <!-- Hero Visual Banner -->
        <?php if (!empty($service['cover_image'])): ?>
          <div style="border-radius: var(--radius-lg); overflow: hidden; margin-bottom: var(--space-8); box-shadow: 0 4px 16px rgba(9, 42, 96, 0.08); border: 1px solid var(--color-border); max-height: 420px; position: relative;">
            <img src="<?= Security::e($service['cover_image']) ?>" 
                 alt="<?= Security::e($service['title']) ?>" 
                 style="width: 100%; height: 380px; object-fit: cover; display: block;">
            
            <div style="position: absolute; bottom: 16px; left: 16px; background: rgba(9, 42, 96, 0.88); backdrop-filter: blur(6px); color: #fff; padding: 8px 16px; border-radius: var(--radius-pill); font-size: var(--font-size-sm); font-weight: 600; display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(255, 255, 255, 0.2);">
              <span style="color: var(--color-accent);"><?= $service['icon_svg'] ?? '' ?></span>
              <span><?= Security::e($service['short_title']) ?></span>
            </div>
          </div>
        <?php endif; ?>

        <!-- Detailed Service Overview -->
        <div style="background-color: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-8); margin-bottom: var(--space-8); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: var(--space-4);">
            <div style="width: 44px; height: 44px; border-radius: var(--radius-md); background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <?= $service['icon_svg'] ?? '' ?>
            </div>
            <div>
              <span class="badge <?= Security::e($service['badge_class'] ?? 'badge-info') ?>"><?= Security::e($service['badge']) ?></span>
              <h2 style="font-size: 1.5rem; color: var(--color-primary); margin: 4px 0 0; font-weight: 800;">Service Overview</h2>
            </div>
          </div>

          <div style="color: var(--color-text-muted); line-height: 1.8; font-size: 1.025rem;">
            <?php foreach (($service['overview'] ?? []) as $paragraph): ?>
              <p style="margin-bottom: var(--space-4);"><?= Security::e($paragraph) ?></p>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Key Highlights & Pillars -->
        <?php if (!empty($service['highlights'])): ?>
          <div style="background-color: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-8); margin-bottom: var(--space-8); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <h2 style="font-size: 1.35rem; color: var(--color-primary); margin: 0 0 var(--space-6); font-weight: 800;">
              Key Scope & Client Deliverables
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: var(--space-6);">
              <?php foreach ($service['highlights'] as $hl): ?>
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: var(--radius-md); padding: var(--space-5);">
                  <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <div style="width: 24px; height: 24px; border-radius: 50%; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-primary); margin: 0;"><?= Security::e($hl['title']) ?></h3>
                  </div>
                  <p style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.6; margin: 0;">
                    <?= Security::e($hl['desc']) ?>
                  </p>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Step-by-Step Advisory Process -->
        <?php if (!empty($service['process_steps'])): ?>
          <div style="background-color: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-8); margin-bottom: var(--space-8); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="margin-bottom: var(--space-6);">
              <span class="eyebrow-pill eyebrow-pill-light" style="margin-bottom: 6px; display: inline-block;">Our Proven Workflow</span>
              <h2 style="font-size: 1.35rem; color: var(--color-primary); margin: 0; font-weight: 800;">
                How We Deliver This Service
              </h2>
            </div>

            <div style="display: flex; flex-direction: column; gap: var(--space-5);">
              <?php foreach ($service['process_steps'] as $step): ?>
                <div style="display: flex; gap: var(--space-4); align-items: flex-start;">
                  <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--color-primary); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1rem; flex-shrink: 0; box-shadow: 0 2px 6px rgba(9, 42, 96, 0.2);">
                    <?= Security::e($step['step']) ?>
                  </div>
                  <div style="flex: 1; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: var(--radius-md); padding: var(--space-4) var(--space-5);">
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-primary); margin: 0 0 6px;">
                      <?= Security::e($step['title']) ?>
                    </h3>
                    <p style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.6; margin: 0;">
                      <?= Security::e($step['desc']) ?>
                    </p>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Required Checklist / Deliverables -->
        <?php if (!empty($service['checklist'])): ?>
          <div style="background-color: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-8); margin-bottom: var(--space-8); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <h2 style="font-size: 1.35rem; color: var(--color-primary); margin: 0 0 var(--space-4); font-weight: 800;">
              Typical Checklist & Requirements
            </h2>
            <p style="color: var(--color-text-muted); font-size: var(--font-size-sm); margin-bottom: var(--space-4);">
              Below are the standard baseline items required when initiating your application or consultation:
            </p>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px;">
              <?php foreach ($service['checklist'] as $item): ?>
                <li style="display: flex; align-items: flex-start; gap: 10px; font-size: var(--font-size-sm); color: var(--color-text); line-height: 1.5;">
                  <span style="color: #16A34A; flex-shrink: 0; margin-top: 2px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                  </span>
                  <span><?= Security::e($item) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <!-- Statutory Consular & Legal Notice -->
        <?php if (!empty($service['disclaimer'])): ?>
          <div style="background-color: #FEF3C7; border: 1.5px solid #F59E0B; border-radius: var(--radius-lg); padding: var(--space-6); margin-bottom: var(--space-8);">
            <div style="display: flex; gap: 12px; align-items: flex-start;">
              <span style="color: #B45309; flex-shrink: 0; margin-top: 2px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              </span>
              <div>
                <h4 style="margin: 0 0 6px; color: #92400E; font-size: 1rem; font-weight: 700;">Regulatory Notice & Consumer Protection</h4>
                <p style="margin: 0; color: #78350F; font-size: var(--font-size-sm); line-height: 1.6;">
                  <?= Security::e($service['disclaimer']) ?>
                </p>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <!-- Service-Specific FAQs -->
        <?php if (!empty($service['faqs'])): ?>
          <div style="background-color: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-8); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <h2 style="font-size: 1.35rem; color: var(--color-primary); margin: 0 0 var(--space-6); font-weight: 800;">
              Frequently Asked Questions About <?= Security::e($service['short_title']) ?>
            </h2>

            <div class="accordion accordion-separated">
              <?php foreach ($service['faqs'] as $idx => $faq): ?>
                <details class="accordion-item" <?= $idx === 0 ? 'open' : '' ?>>
                  <summary class="accordion-header">
                    <span><?= Security::e($faq['q']) ?></span>
                    <span class="accordion-toggle-icon">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </span>
                  </summary>
                  <div class="accordion-body">
                    <p style="margin: 0; line-height: 1.65; color: var(--color-text-muted); font-size: 0.925rem;">
                      <?= Security::e($faq['a']) ?>
                    </p>
                  </div>
                </details>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

      </div>

      <!-- Right Column: Sticky Consultation Sidebar -->
      <div>
        <div style="position: sticky; top: 96px; display: flex; flex-direction: column; gap: var(--space-6);">

          <!-- Consultation Card -->
          <div style="background-color: var(--color-surface); border: 1.5px solid var(--color-primary-light); border-radius: var(--radius-lg); padding: var(--space-6); box-shadow: 0 4px 16px rgba(9, 42, 96, 0.08);">
            <span class="eyebrow-pill eyebrow-pill-light" style="margin-bottom: 8px; display: inline-block;">Ready to Begin?</span>
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--color-primary); margin: 0 0 var(--space-2);">
              Speak With an Advisor
            </h3>
            <p style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.6; margin-bottom: var(--space-5);">
              Have questions about requirements, timelines, or pricing for <?= Security::e($service['short_title']) ?>? Our verified team in Ikorodu is available now.
            </p>

            <div style="display: flex; flex-direction: column; gap: 10px;">
              <a href="<?= Security::e($service['cta_url']) ?>" class="btn btn-primary btn-block" style="font-weight: 700; text-align: center; justify-content: center;">
                <?= Security::e($service['cta_text']) ?> →
              </a>

              <a href="<?= Security::e($waUrl) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp btn-block" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                WhatsApp Consultation
              </a>

              <a href="tel:<?= Security::e($phoneLink) ?>" class="btn btn-outline btn-block" style="font-weight: 600; text-align: center; justify-content: center; display: inline-flex; align-items: center; gap: 8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                Call: 07017597650
              </a>
            </div>

            <div style="margin-top: var(--space-4); padding-top: var(--space-4); border-top: 1px solid var(--color-border); font-size: var(--font-size-xs); color: var(--color-text-muted);">
              <strong>Office Hours:</strong> Mon – Fri: 9:00 AM – 5:30 PM<br>
              <strong>Location:</strong> Suite 08, 1st Floor, Betaplac Plaza, Along Ikorodu-Sagamu Road, Eyita Junction, Ikorodu, Lagos.
            </div>
          </div>

          <!-- Explore Other Services Navigation Card -->
          <div style="background-color: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-6); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <h4 style="font-size: 1rem; font-weight: 700; color: var(--color-primary); margin: 0 0 var(--space-4);">
              Explore Our Other Services
            </h4>

            <nav aria-label="Other Services Navigation">
              <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                <?php foreach (($allServices ?? []) as $otherSlug => $otherSvc): ?>
                  <?php if ($otherSlug !== $service['slug']): ?>
                    <li>
                      <a href="/services/<?= Security::e($otherSlug) ?>" 
                         style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; border-radius: var(--radius-md); text-decoration: none; color: var(--color-primary); font-size: var(--font-size-sm); font-weight: 600; background: #F8FAFC; border: 1px solid #E2E8F0; transition: all 0.2s ease;">
                        <span style="display: flex; align-items: center; gap: 8px;">
                          <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; color: var(--color-primary-light);">
                            <?= $otherSvc['icon_svg'] ?? '' ?>
                          </span>
                          <span><?= Security::e($otherSvc['short_title']) ?></span>
                        </span>
                        <span style="color: var(--color-text-muted); font-size: 0.8rem;">→</span>
                      </a>
                    </li>
                  <?php endif; ?>
                <?php endforeach; ?>
              </ul>
            </nav>
          </div>

          <!-- Trust & Compliance Card -->
          <div style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-6);">
            <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 10px;">
              <div style="width: 32px; height: 32px; border-radius: 50%; background: #DCFCE7; color: #16A34A; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              </div>
              <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--color-primary);">100% Verified Agency</h4>
            </div>
            <p style="font-size: var(--font-size-xs); color: var(--color-text-muted); line-height: 1.5; margin: 0;">
              CAC Registered Entity • Official Corporate Zenith Bank Account • Zero False Guarantees • Transparent Invoicing.
            </p>
          </div>

        </div>
      </div>

    </div>
  </div>
</section>

<!-- Bottom Conversion Section -->
<section class="section section-subtle text-center" style="padding: var(--space-16) 0; border-top: 1px solid var(--color-border);">
  <div class="container container-narrow">
    <span class="eyebrow-pill eyebrow-pill-light" style="margin-bottom: 8px; display: inline-block;">Personalized Guidance</span>
    <h2 style="font-size: clamp(1.75rem, 3vw, 2.25rem); color: var(--color-primary); margin: 0 0 var(--space-3); font-weight: 800;">
      Plan Your Travel With LAD Global
    </h2>
    <p class="text-muted" style="margin-bottom: var(--space-6); font-size: 1.05rem;">
      Whether you need visa application assistance, international tour arrangements, or global mobility consultation, our team is ready to guide you.
    </p>
    <div style="display: flex; gap: var(--space-4); justify-content: center; flex-wrap: wrap;">
      <a href="<?= Security::e($service['cta_url']) ?>" class="btn btn-accent btn-lg" style="font-weight: 700;">
        <?= Security::e($service['cta_text']) ?>
      </a>
      <a href="<?= Security::e($waUrl) ?>" class="btn btn-whatsapp btn-lg" target="_blank" rel="noopener">
        Chat with a Consultant on WhatsApp
      </a>
    </div>
  </div>
</section>
