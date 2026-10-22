<?php
/**
 * Public Tour Packages Directory View
 * @var array $packages Dynamic list of published packages
 */
use App\Core\Security;
?>

<!-- Page Hero with Coastal Photography -->
<section class="page-hero page-hero-packages">
  <div class="container">
    <span class="eyebrow-pill">Curated Holidays</span>
    <h1 class="page-hero-title">International Tour Packages</h1>
    <p class="page-hero-desc">
      Explore handpicked vacation experiences designed for families, couples, and groups. Transparent per-person pricing, verified hotels, and stress-free travel.
    </p>
  </div>
</section>

<!-- Packages Listing Section -->
<section class="section" style="padding: var(--space-16) 0; background-color: #FFFFFF;">
  <div class="container">

    <div class="flex-between" style="margin-bottom: var(--space-8); flex-wrap: wrap; gap: var(--space-4); align-items: flex-end;">
      <div>
        <h2 style="font-size: clamp(1.75rem, 3vw, 2.25rem); color: var(--color-primary); font-weight: 800; margin: 0 0 4px;">
          Available & Upcoming Tour Packages
        </h2>
        <p class="text-muted" style="margin: 0;">All tours include hotel accommodation, scheduled excursions, and dedicated tour coordination.</p>
      </div>
      <div style="font-size: var(--font-size-sm); color: var(--color-text-muted); font-weight: 600;">
        All prices quoted in Nigerian Naira (NGN) per person.
      </div>
    </div>

    <div class="grid-3" style="gap: var(--space-6);">
      <?php if (!empty($packages)): ?>
        <?php foreach ($packages as $pkg): ?>
          <article class="card">
            <div class="card-media" style="height: 220px;">
              <a href="/packages/<?= Security::e($pkg['slug']) ?>" style="display: block; width: 100%; height: 100%;" aria-label="View <?= Security::e($pkg['title']) ?>">
                <?php if (!empty($pkg['cover_image'])): ?>
                  <img src="<?= Security::e($pkg['cover_image']) ?>" alt="<?= Security::e($pkg['title']) ?>" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                  <div class="card-media-placeholder">
                    <span class="placeholder-tag"><?= Security::e($pkg['destination_country'] ?? 'LAD GLOBAL') ?></span>
                  </div>
                <?php endif; ?>
              </a>
              <div class="card-badge-top">
                <span class="badge badge-gold" style="font-weight: 700; letter-spacing: 0.5px;"><?= Security::e($pkg['destination_country']) ?></span>
              </div>
            </div>
            <div class="card-body">
              <div class="card-destination" style="color: var(--color-primary-light); font-weight: 700; font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 4px;">
                <?= Security::e($pkg['destination_city'] ?? $pkg['destination_country']) ?>
                <?= !empty($pkg['duration_days']) ? ' • ' . (int)$pkg['duration_days'] . ' DAYS' : '' ?>
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
                <a href="/packages/<?= Security::e($pkg['slug']) ?>" class="btn btn-primary btn-sm">View Package</a>
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
            Experience game drives in the Maasai Mara savannah followed by pure coastal relaxation on the white sands of Diani Beach.
          </p>
          <div class="card-footer" style="margin-top: auto; padding-top: var(--space-3); border-top: 1px solid #F1F5F9; display: flex; align-items: center; justify-content: space-between;">
            <a href="/packages/kenya-safari-experience" class="service-link-arrow" style="padding: 0; font-size: 0.85rem;">Full Itinerary →</a>
            <a href="/packages/kenya-safari-experience" class="btn btn-primary btn-sm">View Package</a>
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
            Burj Khalifa experience, desert safari dune bashing, luxury marina show dinner cruise, and Dubai gold souk tour.
          </p>
          <div class="card-footer" style="margin-top: auto; padding-top: var(--space-3); border-top: 1px solid #F1F5F9; display: flex; align-items: center; justify-content: space-between;">
            <a href="/packages/dubai-luxury-experience" class="service-link-arrow" style="padding: 0; font-size: 0.85rem;">Full Itinerary →</a>
            <a href="/packages/dubai-luxury-experience" class="btn btn-primary btn-sm">View Package</a>
          </div>
        </div>
      </article>

      <!-- 3: Rwanda Cultural -->
      <article class="card">
        <div class="card-media" style="height: 220px;">
          <a href="/packages/rwanda-cultural-tour" style="display: block; width: 100%; height: 100%;" aria-label="View Rwanda Cultural details">
            <img src="/assets/images/pkg-rwanda.jpg" alt="Rwanda Cultural & Volcanoes Discovery" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
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
              Rwanda Cultural & Volcanoes Discovery
            </a>
          </h3>
          <p class="card-text" style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.5; margin-bottom: var(--space-4);">
            Explore the spotless streets of Kigali, visit the memorial center, and journey into the scenic mountains of Volcanoes National Park.
          </p>
          <div class="card-footer" style="margin-top: auto; padding-top: var(--space-3); border-top: 1px solid #F1F5F9; display: flex; align-items: center; justify-content: space-between;">
            <a href="/packages/rwanda-cultural-tour" class="service-link-arrow" style="padding: 0; font-size: 0.85rem;">Full Itinerary →</a>
            <a href="/packages/rwanda-cultural-tour" class="btn btn-primary btn-sm">View Package</a>
          </div>
        </div>
      </article>

      <!-- 4: Ghana Heritage -->
      <article class="card">
        <div class="card-media" style="height: 220px;">
          <a href="/packages/ghana-heritage-tour" style="display: block; width: 100%; height: 100%;" aria-label="View Ghana Heritage details">
            <img src="/assets/images/pkg-ghana.jpg" alt="Ghana Heritage & Coastal Discovery" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
          </a>
          <div class="card-badge-top">
            <span class="badge badge-gold" style="font-weight: 700; letter-spacing: 0.5px;">GHANA</span>
          </div>
        </div>
        <div class="card-body">
          <div class="card-destination" style="color: var(--color-primary-light); font-weight: 700; font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 4px;">
            ACCRA & CAPE COAST • 5 DAYS
          </div>
          <h3 class="card-title" style="font-size: 1.15rem; font-weight: 700; color: var(--color-primary); margin-bottom: var(--space-2);">
            <a href="/packages/ghana-heritage-tour" style="color: inherit; text-decoration: none;">
              Ghana Heritage & Coastal Discovery
            </a>
          </h3>
          <p class="card-text" style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.5; margin-bottom: var(--space-4);">
            Immerse yourself in Ghanaian culture, visit the historic Cape Coast Castle, and experience the Kakum National Park canopy walk.
          </p>
          <div class="card-footer" style="margin-top: auto; padding-top: var(--space-3); border-top: 1px solid #F1F5F9; display: flex; align-items: center; justify-content: space-between;">
            <a href="/packages/ghana-heritage-tour" class="service-link-arrow" style="padding: 0; font-size: 0.85rem;">Full Itinerary →</a>
            <a href="/packages/ghana-heritage-tour" class="btn btn-primary btn-sm">View Package</a>
          </div>
        </div>
      </article>

      <!-- 5: Japan & Mount Fuji -->
      <article class="card">
        <div class="card-media" style="height: 220px;">
          <a href="/packages/japan-discovery" style="display: block; width: 100%; height: 100%;" aria-label="View Japan Mount Fuji details">
            <img src="/assets/images/pkg-fuji.jpg" alt="Japan & Mount Fuji Alpine Discovery" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
          </a>
          <div class="card-badge-top">
            <span class="badge badge-gold" style="font-weight: 700; letter-spacing: 0.5px;">JAPAN</span>
          </div>
        </div>
        <div class="card-body">
          <div class="card-destination" style="color: var(--color-primary-light); font-weight: 700; font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 4px;">
            TOKYO & MT. FUJI • 7 DAYS
          </div>
          <h3 class="card-title" style="font-size: 1.15rem; font-weight: 700; color: var(--color-primary); margin-bottom: var(--space-2);">
            <a href="/packages/japan-discovery" style="color: inherit; text-decoration: none;">
              Japan & Mount Fuji Alpine Discovery
            </a>
          </h3>
          <p class="card-text" style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.5; margin-bottom: var(--space-4);">
            High-speed bullet train journey, Tokyo city highlights, Hakone hot spring resorts, and breathtaking panoramic views of Mount Fuji.
          </p>
          <div class="card-footer" style="margin-top: auto; padding-top: var(--space-3); border-top: 1px solid #F1F5F9; display: flex; align-items: center; justify-content: space-between;">
            <a href="/packages/japan-discovery" class="service-link-arrow" style="padding: 0; font-size: 0.85rem;">Full Itinerary →</a>
            <a href="/packages/japan-discovery" class="btn btn-primary btn-sm">View Package</a>
          </div>
        </div>
      </article>
      <?php endif; ?>

      <!-- Custom Private Itinerary Card -->
      <article class="custom-itinerary-card">
        <div style="display: flex; gap: var(--space-4); align-items: flex-start; margin-bottom: var(--space-4);">
          <div style="width: 44px; height: 44px; border-radius: var(--radius-md); background: #FDE68A; color: #92400E; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
          </div>
          <div>
            <h3 style="font-size: 1.35rem; color: #78350F; font-weight: 800; margin: 0 0 var(--space-2);">
              Looking for a Custom Private Itinerary?
            </h3>
            <p style="color: #92400E; font-size: var(--font-size-sm); line-height: 1.6; margin: 0;">
              We design tailor-made packages for couples, family reunions, and corporate groups to the United Kingdom, Europe, China, and destinations worldwide.
            </p>
          </div>
        </div>
        <div style="display: flex; gap: var(--space-3); flex-wrap: wrap; margin-top: auto;">
          <a href="https://wa.me/2347017597650" target="_blank" rel="noopener" class="btn btn-accent btn-sm" style="font-weight: 700;">Chat with Tour Specialist</a>
          <a href="/contact?service=custom-package" class="btn btn-outline btn-sm" style="border-color: #D97706; color: #92400E; font-weight: 600;">Request Custom Quote</a>
        </div>
      </article>
    </div>
  </div>
</section>
