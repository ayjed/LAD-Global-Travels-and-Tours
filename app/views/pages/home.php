<?php
/**
 * Public Home Page View
 * @var array $featuredPackages Dynamic published packages from database (if any)
 */
use App\Core\Security;
?>

<!-- Hero Section with Real High-Res Photography -->
<section class="hero-home">
  <div class="container">
    <div class="hero-content">
      <h1 class="hero-title" style="font-size: clamp(2.2rem, 4.2vw, 3.4rem); font-weight: 800; line-height: 1.15; margin-bottom: var(--space-4);">
        Your Journey.<br>
        <span style="color: var(--color-accent);">Our Commitment.</span><br>
        A World of Possibilities.
      </h1>
      <p class="hero-description" style="max-width: 580px; font-size: 1.125rem; line-height: 1.6; color: #F1F5F9; margin-bottom: var(--space-8);">
        Expert visa guidance, flight planning, and curated holidays—making travel effortless from Lagos to the world.
      </p>
      <div class="hero-cta-group">
        <a href="/packages" class="btn btn-accent btn-lg" style="font-weight: 700; box-shadow: var(--shadow-md);">Browse Tour Packages →</a>
        <a href="/contact" class="btn btn-outline btn-lg" style="color: #FFFFFF; border-color: rgba(255,255,255,0.7); background: rgba(5,24,56,0.3); font-weight: 600;">Make an Enquiry</a>
      </div>
      <div class="hero-trust-bar">
        <div class="trust-item">
          <span class="trust-icon" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
          </span>
          <span>Expert Visa Checklist Review</span>
        </div>
        <div class="trust-item">
          <span class="trust-icon" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
          </span>
          <span>Verified Bank & Paystack Payments</span>
        </div>
        <div class="trust-item">
          <span class="trust-icon" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          </span>
          <span>Fast WhatsApp Consultation</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Core Services Section -->
<section class="section" id="servicesSection" style="background-color: #FFFFFF; padding: var(--space-16) 0;">
  <div class="container">
    <div class="flex-between" style="margin-bottom: var(--space-8); flex-wrap: wrap; gap: var(--space-4); align-items: flex-end;">
      <div>
        <span class="eyebrow-pill eyebrow-pill-light">What We Do</span>
        <h2 style="font-size: clamp(1.75rem, 3vw, 2.25rem); color: var(--color-primary); margin: 0; font-weight: 800;">Our Core Travel & Mobility Services</h2>
      </div>
      <a href="/services" class="btn btn-outline" style="font-weight: 600;">View All Services →</a>
    </div>

    <div class="services-grid-home">
      <!-- 1: Visa Assistance -->
      <div class="service-card-clean">
        <div class="service-icon-box-clean" style="background: rgba(9, 42, 96, 0.08); color: var(--color-primary);">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
        </div>
        <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: var(--space-2);">
          <a href="/services/visa-assistance" style="color: var(--color-primary); text-decoration: none;">Visa Assistance</a>
        </h3>
        <p style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.6; margin-bottom: var(--space-4);">
          Complete document checklist preparation, application review, embassy appointment scheduling for business, tourist and student visas.
        </p>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: var(--space-2);">
          <a href="/services/visa-assistance" class="service-link-arrow">View Details →</a>
          <a href="/contact?service=visa" style="font-size: var(--font-size-xs); color: var(--color-primary-light); text-decoration: none; font-weight: 600;">Enquire</a>
        </div>
      </div>

      <!-- 2: Tour Packages -->
      <div class="service-card-clean">
        <div class="service-icon-box-clean" style="background: rgba(230, 174, 52, 0.16); color: #B45309;">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
        </div>
        <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: var(--space-2);">
          <a href="/services/tour-packages" style="color: var(--color-primary); text-decoration: none;">Tour Packages</a>
        </h3>
        <p style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.6; margin-bottom: var(--space-4);">
          Tailored holiday itineraries across Kenya, Rwanda, Ghana, Dubai, China, and Europe with transparent per-person pricing.
        </p>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: var(--space-2);">
          <a href="/services/tour-packages" class="service-link-arrow">View Details →</a>
          <a href="/packages" style="font-size: var(--font-size-xs); color: var(--color-primary-light); text-decoration: none; font-weight: 600;">Browse</a>
        </div>
      </div>

      <!-- 3: Flight Assistance -->
      <div class="service-card-clean">
        <div class="service-icon-box-clean" style="background: rgba(19, 94, 176, 0.10); color: var(--color-primary-light);">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/></svg>
        </div>
        <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: var(--space-2);">
          <a href="/services/flight-assistance" style="color: var(--color-primary); text-decoration: none;">Flight Assistance</a>
        </h3>
        <p style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.6; margin-bottom: var(--space-4);">
          Personalized domestic and international flight search, fare comparison across carriers, and routing advice.
        </p>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: var(--space-2);">
          <a href="/services/flight-assistance" class="service-link-arrow">View Details →</a>
          <a href="/contact?service=flights" style="font-size: var(--font-size-xs); color: var(--color-primary-light); text-decoration: none; font-weight: 600;">Quotes</a>
        </div>
      </div>

      <!-- 4: Hotel Reservations -->
      <div class="service-card-clean">
        <div class="service-icon-box-clean" style="background: rgba(99, 102, 241, 0.10); color: #4F46E5;">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v8h4"/><path d="M18 9h2a2 2 0 0 1 2 2v11h-4"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>
        </div>
        <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: var(--space-2);">
          <a href="/services/hotel-reservations" style="color: var(--color-primary); text-decoration: none;">Hotel Reservations</a>
        </h3>
        <p style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.6; margin-bottom: var(--space-4);">
          Handpicked accommodations matching your budget, verified reviews, and safe booking coordination.
        </p>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: var(--space-2);">
          <a href="/services/hotel-reservations" class="service-link-arrow">View Details →</a>
          <a href="/contact?service=hotels" style="font-size: var(--font-size-xs); color: var(--color-primary-light); text-decoration: none; font-weight: 600;">Advisory</a>
        </div>
      </div>

      <!-- 5: Study Abroad Advisory -->
      <div class="service-card-clean">
        <div class="service-icon-box-clean" style="background: rgba(126, 34, 206, 0.10); color: #7E22CE;">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
        </div>
        <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: var(--space-2);">
          <a href="/services/study-abroad" style="color: var(--color-primary); text-decoration: none;">Study Abroad Advisory</a>
        </h3>
        <p style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.6; margin-bottom: var(--space-4);">
          Accredited international university options, admission prerequisites, tuition guidance, and student visa checklists.
        </p>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: var(--space-2);">
          <a href="/services/study-abroad" class="service-link-arrow">View Details →</a>
          <a href="/contact?service=study" style="font-size: var(--font-size-xs); color: var(--color-primary-light); text-decoration: none; font-weight: 600;">Enquire</a>
        </div>
      </div>

      <!-- 6: Canada PR Guidance -->
      <div class="service-card-clean">
        <div class="service-icon-box-clean" style="background: rgba(190, 18, 60, 0.10); color: #BE123C;">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 9.17 4.24-4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="m9.17 14.83-4.24 4.24"/><circle cx="12" cy="12" r="4"/></svg>
        </div>
        <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: var(--space-2);">
          <a href="/services/canada-pr" style="color: var(--color-primary); text-decoration: none;">Canada PR Guidance</a>
        </h3>
        <p style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.6; margin-bottom: var(--space-4);">
          Express Entry (FSW, CEC), Provincial Nominee Programs (PNP), CRS point calculations, and credential assessment advisory.
        </p>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: var(--space-2);">
          <a href="/services/canada-pr" class="service-link-arrow">View Details →</a>
          <a href="/contact?service=canada_pr" style="font-size: var(--font-size-xs); color: var(--color-primary-light); text-decoration: none; font-weight: 600;">Assess</a>
        </div>
      </div>

      <!-- 7: Citizenship by Investment -->
      <div class="service-card-clean">
        <div class="service-icon-box-clean" style="background: rgba(161, 98, 7, 0.12); color: #A16207;">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
        </div>
        <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: var(--space-2);">
          <a href="/services/citizenship-by-investment" style="color: var(--color-primary); text-decoration: none;">Citizenship by Investment</a>
        </h3>
        <p style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.6; margin-bottom: var(--space-4);">
          Advisory on legitimate government-legislated economic citizenship programs offering global visa-free mobility.
        </p>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: var(--space-2);">
          <a href="/services/citizenship-by-investment" class="service-link-arrow">View Details →</a>
          <a href="/contact?service=cbi" style="font-size: var(--font-size-xs); color: var(--color-primary-light); text-decoration: none; font-weight: 600;">Consult</a>
        </div>
      </div>

      <!-- 8: Residency by Investment -->
      <div class="service-card-clean">
        <div class="service-icon-box-clean" style="background: rgba(4, 120, 87, 0.10); color: #047857;">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </div>
        <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: var(--space-2);">
          <a href="/services/residency-by-investment" style="color: var(--color-primary); text-decoration: none;">Residency by Investment</a>
        </h3>
        <p style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.6; margin-bottom: var(--space-4);">
          European and international Golden Visa programs, residency permits, and family relocation options through investment.
        </p>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: var(--space-2);">
          <a href="/services/residency-by-investment" class="service-link-arrow">View Details →</a>
          <a href="/contact?service=rbi" style="font-size: var(--font-size-xs); color: var(--color-primary-light); text-decoration: none; font-weight: 600;">Consult</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Featured Packages Section with Authentic Photography -->
<section class="section section-subtle" id="packagesSection" style="padding: var(--space-16) 0;">
  <div class="container">
    <div class="flex-between" style="margin-bottom: var(--space-8); flex-wrap: wrap; gap: var(--space-4); align-items: flex-end;">
      <div>
        <span class="eyebrow-pill eyebrow-pill-light">Destinations</span>
        <h2 style="font-size: clamp(1.75rem, 3vw, 2.25rem); color: var(--color-primary); margin: 0 0 var(--space-2); font-weight: 800;">Featured Tour Packages</h2>
        <p class="text-muted" style="margin: 0;">Handcrafted international itineraries with verified accommodations and guided tours.</p>
      </div>
      <a href="/packages" class="btn btn-outline" style="font-weight: 600;">Browse All Packages →</a>
    </div>

    <div class="grid-3">
      <?php if (!empty($featuredPackages)): ?>
        <?php foreach (array_slice($featuredPackages, 0, 3) as $pkg): ?>
          <article class="card">
            <div class="card-media" style="height: 220px;">
              <a href="/packages/<?= Security::e($pkg['slug']) ?>" style="display: block; width: 100%; height: 100%;" aria-label="View <?= Security::e($pkg['title']) ?>">
                <?php if (!empty($pkg['cover_image'])): ?>
                  <img src="<?= Security::e($pkg['cover_image']) ?>" alt="<?= Security::e($pkg['title']) ?>" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                  <img src="/assets/images/pkg-kenya.jpg" alt="<?= Security::e($pkg['title']) ?>" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                <?php endif; ?>
              </a>
              <div class="card-badge-top">
                <span class="badge badge-gold" style="font-weight: 700; letter-spacing: 0.5px;"><?= Security::e($pkg['destination_country']) ?></span>
              </div>
            </div>
            <div class="card-body">
              <div class="card-destination" style="color: var(--color-primary-light); font-weight: 700; font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 4px;">
                <?= Security::e($pkg['destination_city'] ?? $pkg['destination_country']) ?> • <?= !empty($pkg['duration_days']) ? Security::e((string)$pkg['duration_days']) . ' DAYS' : 'FLEXIBLE' ?>
              </div>
              <h3 class="card-title" style="font-size: 1.15rem; font-weight: 700; color: var(--color-primary); margin-bottom: var(--space-2);">
                <a href="/packages/<?= Security::e($pkg['slug']) ?>" style="color: inherit; text-decoration: none;">
                  <?= Security::e($pkg['title']) ?>
                </a>
              </h3>
              <p class="card-text" style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.5; margin-bottom: var(--space-4);">
                <?= Security::e($pkg['summary'] ?? substr($pkg['description'] ?? '', 0, 120)) ?>
              </p>
              <div class="card-footer" style="margin-top: auto; padding-top: var(--space-3); border-top: 1px solid #F1F5F9; display: flex; align-items: center; justify-content: space-between;">
                <div class="price-display">
                  <span class="price-label" style="font-size: 0.75rem; color: #64748B; display: block;">Per Person</span>
                  <span class="price-amount" style="font-weight: 700; color: var(--color-primary);">
                    <?= !empty($pkg['price_amount']) && $pkg['price_amount'] > 0 ? '₦' . number_format((float)$pkg['price_amount'], 2) : 'Request Quote' ?>
                  </span>
                </div>
                <a href="/packages/<?= Security::e($pkg['slug']) ?>" class="btn btn-primary btn-sm">View Details</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <!-- 1: Kenya Safari -->
        <article class="card">
          <div class="card-media" style="height: 220px;">
            <a href="/packages/kenya-safari-experience" style="display: block; width: 100%; height: 100%;" aria-label="View Kenya Safari details">
              <img src="/assets/images/pkg-kenya.jpg" alt="Kenya Safari & Diani Beach Escape" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
            </a>
            <div class="card-badge-top">
              <span class="badge badge-gold" style="font-weight: 700; letter-spacing: 0.5px;">KENYA</span>
            </div>
          </div>
          <div class="card-body">
            <div class="card-destination" style="color: var(--color-primary-light); font-weight: 700; font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 4px;">
              NAIROBI & MASAI MARA • 7 DAYS
            </div>
            <h3 class="card-title" style="font-size: 1.15rem; font-weight: 700; color: var(--color-primary); margin-bottom: var(--space-2);">
              <a href="/packages/kenya-safari-experience" style="color: inherit; text-decoration: none;">
                Kenya Safari & Diani Beach Escape
              </a>
            </h3>
            <p class="card-text" style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.5; margin-bottom: var(--space-4);">
              Experience the thrill of the African savannah with game drives in Maasai Mara followed by serene white sands of Diani Beach.
            </p>
            <div class="card-footer" style="margin-top: auto; padding-top: var(--space-3); border-top: 1px solid #F1F5F9; display: flex; align-items: center; justify-content: space-between;">
              <a href="/packages/kenya-safari-experience" class="service-link-arrow" style="padding: 0; font-size: 0.85rem;">Full Itinerary →</a>
              <a href="/packages/kenya-safari-experience" class="btn btn-primary btn-sm">View Details</a>
            </div>
          </div>
        </article>

        <!-- 2: Dubai Luxury -->
        <article class="card">
          <div class="card-media" style="height: 220px;">
            <a href="/packages/dubai-luxury-experience" style="display: block; width: 100%; height: 100%;" aria-label="View Dubai Luxury details">
              <img src="/assets/images/pkg-dubai.jpg" alt="Dubai Luxury City & Desert Adventure" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
            </a>
            <div class="card-badge-top">
              <span class="badge badge-gold" style="font-weight: 700; letter-spacing: 0.5px;">DUBAI, UAE</span>
            </div>
          </div>
          <div class="card-body">
            <div class="card-destination" style="color: var(--color-primary-light); font-weight: 700; font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 4px;">
              DUBAI CITY & DESERT • 6 DAYS
            </div>
            <h3 class="card-title" style="font-size: 1.15rem; font-weight: 700; color: var(--color-primary); margin-bottom: var(--space-2);">
              <a href="/packages/dubai-luxury-experience" style="color: inherit; text-decoration: none;">
                Dubai Luxury City & Desert Adventure
              </a>
            </h3>
            <p class="card-text" style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.5; margin-bottom: var(--space-4);">
              Burj Khalifa experience, desert safari dune bashing, luxury marina dhow dinner cruise, and city heritage tour.
            </p>
            <div class="card-footer" style="margin-top: auto; padding-top: var(--space-3); border-top: 1px solid #F1F5F9; display: flex; align-items: center; justify-content: space-between;">
              <a href="/packages/dubai-luxury-experience" class="service-link-arrow" style="padding: 0; font-size: 0.85rem;">Full Itinerary →</a>
              <a href="/packages/dubai-luxury-experience" class="btn btn-primary btn-sm">View Details</a>
            </div>
          </div>
        </article>

        <!-- 3: Rwanda Cultural -->
        <article class="card">
          <div class="card-media" style="height: 220px;">
            <a href="/packages/rwanda-cultural-tour" style="display: block; width: 100%; height: 100%;" aria-label="View Rwanda Cultural details">
              <img src="/assets/images/pkg-rwanda.jpg" alt="Rwanda Cultural & Gorilla Trekking" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
            </a>
            <div class="card-badge-top">
              <span class="badge badge-gold" style="font-weight: 700; letter-spacing: 0.5px;">RWANDA</span>
            </div>
          </div>
          <div class="card-body">
            <div class="card-destination" style="color: var(--color-primary-light); font-weight: 700; font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 4px;">
              KIGALI & VOLCANOES • 5 DAYS
            </div>
            <h3 class="card-title" style="font-size: 1.15rem; font-weight: 700; color: var(--color-primary); margin-bottom: var(--space-2);">
              <a href="/packages/rwanda-cultural-tour" style="color: inherit; text-decoration: none;">
                Rwanda Cultural & Gorilla Trekking
              </a>
            </h3>
            <p class="card-text" style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.5; margin-bottom: var(--space-4);">
              Explore the clean streets of Kigali, visit the memorial center, and journey into the scenic mountains of Volcanoes National Park.
            </p>
            <div class="card-footer" style="margin-top: auto; padding-top: var(--space-3); border-top: 1px solid #F1F5F9; display: flex; align-items: center; justify-content: space-between;">
              <a href="/packages/rwanda-cultural-tour" class="service-link-arrow" style="padding: 0; font-size: 0.85rem;">Full Itinerary →</a>
              <a href="/packages/rwanda-cultural-tour" class="btn btn-primary btn-sm">View Details</a>
            </div>
          </div>
        </article>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Why Choose Us / Full Transparency Banner -->
<section class="transparency-banner">
  <div class="container">
    <div class="text-center" style="max-width: 720px; margin: 0 auto var(--space-12);">
      <span class="eyebrow-pill" style="border-color: rgba(230,174,52,0.5);">WHY LAD GLOBAL</span>
      <h2 style="color: #FFFFFF; font-size: clamp(1.85rem, 3.2vw, 2.5rem); font-weight: 800; margin-bottom: var(--space-3);">
        Travel with Confidence and Full Transparency
      </h2>
      <p style="color: #E2E8F0; font-size: 1.1rem; line-height: 1.6; margin: 0;">
        We eliminate the guesswork and stress from your international journeys.
      </p>
    </div>

    <div class="grid-3" style="gap: var(--space-6);">
      <!-- Feature 1: Secure Verified Payments -->
      <div class="feature-box-white">
        <div class="feature-icon-badge" style="background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0;">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/><path d="M7 15h.01"/><path d="M11 15h2"/></svg>
        </div>
        <div class="feature-box-body">
          <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--color-primary); margin-bottom: var(--space-2);">Secure Verified Payments</h3>
          <p style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.6; margin: 0;">
            Choose between instant Paystack checkout or manual direct bank transfer to our verified corporate account.
          </p>
        </div>
      </div>

      <!-- Feature 2: Thorough Document Screening -->
      <div class="feature-box-white">
        <div class="feature-icon-badge" style="background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE;">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/></svg>
        </div>
        <div class="feature-box-body">
          <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--color-primary); margin-bottom: var(--space-2);">Thorough Document Screening</h3>
          <p style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.6; margin: 0;">
            Every visa file is checked for completeness and formatting to significantly minimize common embassy rejection reasons.
          </p>
        </div>
      </div>

      <!-- Feature 3: Responsive Support -->
      <div class="feature-box-white">
        <div class="feature-icon-badge" style="background: #FEF3C7; color: #B45309; border: 1px solid #FDE68A;">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
        </div>
        <div class="feature-box-body">
          <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--color-primary); margin-bottom: var(--space-2);">Responsive Support</h3>
          <p style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.6; margin: 0;">
            Direct one-on-one consultation on WhatsApp. Real advisors in Lagos ready to assist you throughout your journey.
          </p>
        </div>
      </div>
    </div>


  </div>
</section>

<!-- FAQ Preview Section (Progressive Accordion) -->
<section class="section section-subtle" id="faqSection" style="padding: var(--space-16) 0;">
  <div class="container">
    <div class="faq-layout-grid">
      <!-- Left Column / Sticky Sidebar -->
      <div class="faq-sidebar">
        <span class="eyebrow-pill eyebrow-pill-light" style="margin-bottom: var(--space-3); display: inline-block;">Got Questions?</span>
        <h2 style="font-size: clamp(1.75rem, 3vw, 2.25rem); color: var(--color-primary); margin: 0 0 var(--space-3); font-weight: 800; line-height: 1.25;">
          Frequently Asked Questions
        </h2>
        <p class="text-muted" style="margin: 0 0 var(--space-6); font-size: 1rem; line-height: 1.6;">
          Answers to common queries regarding our visa guidance, packages, assisted flight reservations, and verified payment procedures.
        </p>

        <!-- Quick Consultation Contact Card -->
        <div class="faq-contact-card">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
            </div>
            <div>
              <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: var(--color-primary);">Still have questions?</h4>
              <p style="margin: 0; font-size: 0.825rem; color: var(--color-text-muted);">Speak directly with a consultant</p>
            </div>
          </div>
          <p style="font-size: 0.85rem; color: var(--color-text-muted); line-height: 1.5; margin-bottom: 16px;">
            Our verified advisory team in Ikorodu, Lagos is ready to evaluate your document checklist or travel itinerary.
          </p>
          <div style="display: flex; flex-direction: column; gap: 8px;">
            <a href="https://wa.me/2347017597650" target="_blank" rel="noopener" class="btn btn-sm" style="background: #25D366; color: #FFFFFF; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px; border-radius: var(--radius-md);">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
              Chat on WhatsApp
            </a>
            <a href="/contact" class="btn btn-sm btn-outline" style="font-weight: 600; display: inline-flex; align-items: center; justify-content: center; border-radius: var(--radius-md);">
              Send Us a Message &rarr;
            </a>
          </div>
        </div>
      </div>

      <!-- Right Column / Separated FAQ Accordions -->
      <div>
        <div class="accordion accordion-separated">
          <!-- FAQ 1 -->
          <details class="accordion-item" open>
            <summary class="accordion-header">
              <span>Can you guarantee that my visa will be approved?</span>
              <span class="accordion-toggle-icon">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
              </span>
            </summary>
            <div class="accordion-body">
              <p style="margin: 0; line-height: 1.65; color: var(--color-text-muted); font-size: 0.925rem;">
                <strong>No.</strong> Under statutory international law and Nigerian consumer protection guidelines, no travel agency, consultant, or third party has the legal authority to guarantee visa approval (no guarantee is promised or implied). Sovereign embassies, high commissions, and consular officers alone make all visa decisions. Our role is strictly to provide professional document audit, checklist compliance, application screening, and advisory to ensure your application meets official consular criteria and avoids common errors.
              </p>
            </div>
          </details>

          <!-- FAQ 2 -->
          <details class="accordion-item">
            <summary class="accordion-header">
              <span>How do I book a tour package and make payment?</span>
              <span class="accordion-toggle-icon">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
              </span>
            </summary>
            <div class="accordion-body">
              <p style="margin: 0; line-height: 1.65; color: var(--color-text-muted); font-size: 0.925rem;">
                You can select any published package on our Tour Packages page and complete the simple booking form. We accept payments via secure online checkout powered by Paystack (Debit/Credit Card, Bank Transfer, USSD) or direct manual bank transfer to our verified Zenith Bank corporate account. Official payment receipts are issued immediately upon confirmed reconciliation.
              </p>
            </div>
          </details>

          <!-- FAQ 3 -->
          <details class="accordion-item">
            <summary class="accordion-header">
              <span>Are flight bookings and hotel reservations automated?</span>
              <span class="accordion-toggle-icon">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
              </span>
            </summary>
            <div class="accordion-body">
              <p style="margin: 0; line-height: 1.65; color: var(--color-text-muted); font-size: 0.925rem;">
                Flight and hotel arrangements are provided on an <strong>assisted consultation basis</strong>. Not an automated flight booking engine. Rather than an impersonal automated booking engine, our experienced travel consultants manually verify airline schedules, fare classes, room availability, and hotel reviews to secure the optimal itinerary for your budget. Submit an enquiry and our team will provide a tailored quote within 24 business hours.
              </p>
            </div>
          </details>

          <!-- FAQ 4 -->
          <details class="accordion-item">
            <summary class="accordion-header">
              <span>What documents do I need to begin a visa application?</span>
              <span class="accordion-toggle-icon">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
              </span>
            </summary>
            <div class="accordion-body">
              <p style="margin: 0; line-height: 1.65; color: var(--color-text-muted); font-size: 0.925rem;">
                Requirements vary depending on the destination and category of visa (tourist, business, conference, student). Standard baseline documents typically include a valid international passport (minimum 6 months validity), passport-sized photographs, proof of employment or CAC business registration, 6 months certified bank statements, proof of accommodation, and flight reservations. We provide a customized checklist tailored to your destination.
              </p>
            </div>
          </details>

          <!-- FAQ 5 -->
          <details class="accordion-item">
            <summary class="accordion-header">
              <span>Where is your physical office located in Nigeria?</span>
              <span class="accordion-toggle-icon">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
              </span>
            </summary>
            <div class="accordion-body">
              <p style="margin: 0; line-height: 1.65; color: var(--color-text-muted); font-size: 0.925rem;">
                LAD Global Travels and Tours operates from our physical office located at <strong>Suite 08, 1st Floor, Betaplac Plaza, Along Ikorodu-Sagamu Road, Eyita Junction, Ikorodu, Lagos State, Nigeria</strong>. Walk-in consultations are welcome during business hours (Monday to Friday, 9:00 AM – 5:00 PM; Saturday by appointment).
              </p>
            </div>
          </details>
        </div>

        <div style="margin-top: var(--space-6); text-align: left;">
          <a href="/faq" class="btn btn-outline" style="font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
            View All 23+ Frequently Asked Questions &rarr;
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
