<?php
/**
 * Public Tour Package Detail View
 * @var array|null $package Database package record (if found)
 * @var string $packageSlug Requested slug
 * @var array $galleryImages List of package images from database
 */
use App\Core\Security;

// Sample fallback itineraries for prototype and preview slugs
$sampleItineraries = [
    'kenya-safari-experience' => [
        'title' => 'Kenya Safari & Diani Beach Escape',
        'destination_country' => 'Kenya',
        'destination_city' => 'Nairobi & Diani',
        'duration_days' => 7,
        'cover_image' => '/assets/images/pkg-kenya.jpg',
        'summary' => 'Experience game drives in the Maasai Mara savannah followed by pure coastal relaxation on the white sands of Diani Beach.',
        'description' => "Embark on an iconic East African adventure combining world-class wildlife safaris with tropical relaxation. Begin in Nairobi with an introduction to Kenyan heritage, then fly directly into the legendary Maasai Mara National Reserve for morning and evening game drives in search of the Big Five.\n\nAfter exhilarating days in the wild, journey to the pristine shores of Diani Beach on the Indian Ocean. Unwind at a beachfront resort with watersports, seafood dining, and tropical sunsets.",
        'inclusions' => "• 3 Nights luxury safari lodge accommodation in Maasai Mara\n• 3 Nights beachfront resort accommodation in Diani Beach\n• Full board meals during safari; breakfast & dinner in Diani\n• All scheduled game drives in 4x4 safari land cruisers with pop-up roofs\n• English-speaking professional naturalist safari guide\n• Domestic safari flights and airport transfers",
        'exclusions' => "• International flight tickets (consultant booking available upon request)\n• Kenya electronic travel authorization (eTA) processing fee\n• Personal expenses, laundry, and discretionary tips\n• Optional hot air balloon safari experience",
        'is_payment_enabled' => 1,
        'price_amount' => 1850000.00
    ],
    'dubai-luxury-experience' => [
        'title' => 'Dubai Luxury City & Desert Adventure',
        'destination_country' => 'United Arab Emirates',
        'destination_city' => 'Dubai',
        'duration_days' => 6,
        'cover_image' => '/assets/images/pkg-dubai.jpg',
        'summary' => 'Burj Khalifa experience, desert safari dune bashing, luxury marina dhow dinner cruise, and Dubai gold souk tour.',
        'description' => "Discover the dazzling blend of futuristic architecture, world-class shopping, and Arabian heritage in Dubai. Stay at a 4-star city center hotel with easy access to Dubai Mall and Downtown.\n\nHighlights include ascending to the At the Top observatory at Burj Khalifa, a thrill-packed 4x4 desert safari with dune bashing and BBQ dinner under the stars, and a romantic evening dinner cruise through Dubai Marina.",
        'inclusions' => "• 5 Nights 4-star hotel accommodation with daily buffet breakfast\n• Dubai International Airport return private transfers\n• Half-day Dubai city heritage & modern architecture tour\n• Desert Safari with 4x4 dune bashing, camel rides & BBQ dinner\n• Dubai Marina 5-star international buffet dhow dinner cruise\n• Burj Khalifa Level 124/125 standard admission ticket",
        'exclusions' => "• UAE tourist visa (available through our visa assistance desk)\n• International flight tickets\n• UAE Tourism Dirham hotel tax (payable directly at hotel)\n• Meals not explicitly listed in the inclusions",
        'is_payment_enabled' => 1,
        'price_amount' => 1450000.00
    ],
    'rwanda-cultural-tour' => [
        'title' => 'Rwanda Cultural & Volcanoes Discovery',
        'destination_country' => 'Rwanda',
        'destination_city' => 'Kigali & Musanze',
        'duration_days' => 5,
        'cover_image' => '/assets/images/pkg-rwanda.jpg',
        'summary' => 'Explore the spotless streets of Kigali, visit the memorial center, and journey into the scenic mountains of Volcanoes National Park.',
        'description' => "Rwanda is celebrated as one of Africa's cleanest, safest, and most inspiring destinations. This comprehensive 5-day itinerary introduces you to the vibrancy of Kigali—visiting the Kigali Genocide Memorial and local art centers—before traveling to the lush volcanoes of Musanze for cultural village experiences and twin lakes exploration.",
        'inclusions' => "• 4 Nights hotel and lodge accommodation\n• Daily breakfast and scheduled tour lunches\n• Private ground transportation in sanitized tour van\n• Kigali city guided historical tour and entry fees\n• Iby'Iwacu cultural village immersion and traditional dance show\n• English/French speaking certified Rwandan tour guide",
        'exclusions' => "• International flights\n• Rwanda entry visa (or East Africa Tourist Visa)\n• Optional mountain gorilla trekking permit\n• Personal travel insurance and tips",
        'is_payment_enabled' => 1,
        'price_amount' => 1250000.00
    ],
    'ghana-heritage-tour' => [
        'title' => 'Ghana Heritage & Coastal Discovery',
        'destination_country' => 'Ghana',
        'destination_city' => 'Accra & Cape Coast',
        'duration_days' => 5,
        'cover_image' => '/assets/images/pkg-ghana.jpg',
        'summary' => 'Immerse yourself in Ghanaian culture, visit the historic Cape Coast Castle, and experience the Kakum National Park canopy walk.',
        'description' => "A profound journey through West African history and culture. Explore the buzzing neighborhoods of Accra, visit Kwame Nkrumah Memorial Park and the vibrant Makola Market. Travel along the coastline to the historic Cape Coast and Elmina Castles, and walk high above the rainforest on the canopy walkway of Kakum National Park.",
        'inclusions' => "• 4 Nights 3/4-star accommodation in Accra and Cape Coast\n• Daily breakfast and select regional lunches\n• Air-conditioned private tour transportation\n• Guided entry to Cape Coast Castle and Kakum National Park\n• Accra cultural crafts and memorial park tour\n• Dedicated local tour guide",
        'exclusions' => "• Flights or road transport from Nigeria\n• Personal shopping and discretionary tips\n• Meals outside the scheduled itinerary",
        'is_payment_enabled' => 1,
        'price_amount' => 850000.00
    ],
    'japan-discovery' => [
        'title' => 'Japan & Mount Fuji Alpine Discovery',
        'destination_country' => 'Japan',
        'destination_city' => 'Tokyo & Mt. Fuji',
        'duration_days' => 7,
        'cover_image' => '/assets/images/pkg-fuji.jpg',
        'summary' => 'High-speed bullet train journey, Tokyo city highlights, Hakone hot spring resorts, and breathtaking panoramic views of Mount Fuji.',
        'description' => "Immerse yourself in the timeless harmony of ancient tradition and futuristic innovation in Japan. Begin in Tokyo exploring historic Asakusa, the iconic Shibuya crossing, and modern Ginza. Travel by Shinkansen bullet train to the scenic Hakone region, cruise across Lake Ashi with views of Mount Fuji, and relax in traditional onsen hot springs.",
        'inclusions' => "• 6 Nights 4-star hotel and ryokan accommodation\n• Daily breakfast and scheduled Japanese Kaiseki dinners\n• 7-Day Japan Rail (JR) Shinkansen train pass\n• Tokyo city guided tour and Lake Ashi sightseeing cruise\n• Mount Fuji 5th Station excursion\n• English-speaking local tour director",
        'exclusions' => "• International flights from Lagos\n• Japan tourist visa processing\n• Personal expenses and discretionary gratuities\n• Lunches outside scheduled excursions",
        'is_payment_enabled' => 1,
        'price_amount' => 2950000.00
    ]
];

// Support alias slugs
$sampleItineraries['kenya-safari'] = &$sampleItineraries['kenya-safari-experience'];
$sampleItineraries['dubai-experience'] = &$sampleItineraries['dubai-luxury-experience'];
$sampleItineraries['rwanda-cultural'] = &$sampleItineraries['rwanda-cultural-tour'];
$sampleItineraries['ghana-heritage'] = &$sampleItineraries['ghana-heritage-tour'];
$sampleItineraries['japan-alpine-discovery'] = &$sampleItineraries['japan-discovery'];

$data = $package ?? ($sampleItineraries[$packageSlug] ?? null);

// Sample fallback galleries when database records are not present
$sampleGalleries = [
    'kenya-safari-experience' => [
        ['image_path' => '/assets/images/pkg-kenya.jpg', 'alt_text' => 'Maasai Mara Savannah Safari Game Drive'],
        ['image_path' => '/assets/images/pkg-kenya-beach.jpg', 'alt_text' => 'Diani Beach White Sands & Turquoise Ocean'],
        ['image_path' => '/assets/images/pkg-kenya-safari.jpg', 'alt_text' => 'African Wildlife & 4x4 Safari Cruiser'],
        ['image_path' => '/assets/images/pkg-travel-story.jpg', 'alt_text' => 'VIP Airport Concierge & Flight Departure'],
    ],
    'dubai-luxury-experience' => [
        ['image_path' => '/assets/images/pkg-dubai.jpg', 'alt_text' => 'Burj Khalifa & Downtown Dubai Panoramic View'],
        ['image_path' => '/assets/images/pkg-dubai-marina.jpg', 'alt_text' => 'Dubai Marina Skyline & Luxury Dhow Dinner Cruise'],
        ['image_path' => '/assets/images/service-flights.jpg', 'alt_text' => 'First-Class Flight Comfort to Dubai'],
        ['image_path' => '/assets/images/service-hotels.jpg', 'alt_text' => '5-Star Luxury Resort Accommodation'],
    ],
    'rwanda-cultural-tour' => [
        ['image_path' => '/assets/images/pkg-rwanda.jpg', 'alt_text' => 'Kigali City Panorama & Scenic Green Hills'],
        ['image_path' => '/assets/images/pkg-rwanda-gorilla.jpg', 'alt_text' => 'Volcanoes National Park Mountain Gorillas'],
        ['image_path' => '/assets/images/pkg-fuji-lake.jpg', 'alt_text' => 'Rwanda Twin Lakes & Natural Rainforest Excursion'],
        ['image_path' => '/assets/images/pkg-travel-story.jpg', 'alt_text' => 'Iby\'Iwacu Cultural Village Immersion & Traditions'],
    ],
    'ghana-heritage-tour' => [
        ['image_path' => '/assets/images/pkg-ghana.jpg', 'alt_text' => 'Cape Coast Historic Castle & Atlantic Coastline'],
        ['image_path' => '/assets/images/pkg-ghana-coast.jpg', 'alt_text' => 'Elmina Castle Heritage & Golden Shorelines'],
        ['image_path' => '/assets/images/pkg-coastal-escape.jpg', 'alt_text' => 'Kakum National Park Suspended Rainforest Canopy Walk'],
        ['image_path' => '/assets/images/pkg-travel-story.jpg', 'alt_text' => 'Accra Cultural Arts & Memorial Heritage Tour'],
    ],
    'japan-discovery' => [
        ['image_path' => '/assets/images/pkg-fuji.jpg', 'alt_text' => 'Mount Fuji with Spring Cherry Blossoms'],
        ['image_path' => '/assets/images/pkg-fuji-lake.jpg', 'alt_text' => 'Hakone Lake Ashi Sightseeing Cruise & Mt. Fuji Views'],
        ['image_path' => '/assets/images/hero-airplane.jpg', 'alt_text' => 'Shinkansen Bullet Train & Tokyo Air Travel'],
        ['image_path' => '/assets/images/pkg-travel-story.jpg', 'alt_text' => 'Tokyo Modern Cityscape & Traditional Ryokan Stay'],
    ]
];
$sampleGalleries['kenya-safari'] = &$sampleGalleries['kenya-safari-experience'];
$sampleGalleries['dubai-experience'] = &$sampleGalleries['dubai-luxury-experience'];
$sampleGalleries['rwanda-cultural'] = &$sampleGalleries['rwanda-cultural-tour'];
$sampleGalleries['ghana-heritage'] = &$sampleGalleries['ghana-heritage-tour'];
$sampleGalleries['japan-alpine-discovery'] = &$sampleGalleries['japan-discovery'];

$galleryList = !empty($galleryImages) ? $galleryImages : ($sampleGalleries[$packageSlug] ?? []);
if (empty($galleryList) && !empty($data['cover_image'])) {
    $galleryList = [['image_path' => $data['cover_image'], 'alt_text' => $data['title']]];
}

// Check payable status across DB and array keys
$isPayable = (!empty($data['is_payment_enabled']) || !empty($data['payment_enabled'])) && !empty($data['price_amount']) && (float)$data['price_amount'] > 0;

$bankPaymentActive = \App\Services\SettingService::isBankTransferEnabled();
$paystackPaymentActive = \App\Services\SettingService::isPaystackEnabled();
$hasActivePaymentMethods = $bankPaymentActive || $paystackPaymentActive;

$paymentBadgeText = 'Direct Booking Available';
$paymentTrustText = 'Secure Checkout (Bank Transfer or Paystack)';
if ($bankPaymentActive && $paystackPaymentActive) {
    $paymentTrustText = 'Secure Checkout (Bank Transfer or Paystack)';
} elseif ($bankPaymentActive) {
    $paymentTrustText = 'Secure Checkout (Direct Bank Transfer)';
} elseif ($paystackPaymentActive) {
    $paymentTrustText = 'Secure Checkout (Paystack Online Gateway)';
} else {
    $paymentBadgeText = 'Consultation Booking';
    $paymentTrustText = 'Direct Reservation via Travel Advisor';
}
?>

<!-- Page Hero -->
<section class="page-hero page-hero-packages">
  <div class="container">
    <a href="/packages" style="color: var(--color-accent); font-size: var(--font-size-sm); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-bottom: var(--space-2); font-weight: 600;">
      ← Back to All Packages
    </a>
    <h1 class="page-hero-title"><?= $data ? Security::e($data['title']) : 'Tour Package Details' ?></h1>
    <p class="page-hero-desc">
      <?= $data ? Security::e($data['destination_city'] ?? $data['destination_country']) . ' • ' . (int)($data['duration_days'] ?? 0) . ' Days Tour' : 'Custom travel itinerary' ?>
    </p>
  </div>
</section>

<!-- Package Detail Content Section -->
<section class="section">
  <div class="container">
    <?php if ($data): ?>
      <div class="package-detail-grid">
        <!-- Main Content Column -->
        <div>
          <!-- Overview Card -->
          <div style="background-color: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-8); margin-bottom: var(--space-6);">
            
            <!-- Interactive Multi-Image Gallery -->
            <?php if (!empty($galleryList)): ?>
              <div class="package-gallery" style="margin-bottom: var(--space-6);">
                <!-- Main Featured Photo Viewport -->
                <div style="position: relative; border-radius: var(--radius-md); overflow: hidden; height: 380px; border: 1px solid var(--color-border); background: #0F172A; cursor: pointer;"
                     onclick="openGalleryModal()" title="Click to view full photo">
                  <img id="mainGalleryImage" src="<?= Security::e($galleryList[0]['image_path']) ?>" 
                       alt="<?= Security::e($galleryList[0]['alt_text'] ?? $data['title']) ?>" 
                       style="width: 100%; height: 100%; object-fit: cover; transition: opacity 0.3s ease;">
                  
                  <!-- Gallery Count Badge -->
                  <div style="position: absolute; bottom: 12px; right: 12px; background: rgba(9, 42, 96, 0.85); backdrop-filter: blur(4px); color: #fff; padding: 6px 12px; border-radius: 20px; font-size: var(--font-size-xs); font-weight: 600; display: flex; align-items: center; gap: 6px; border: 1px solid rgba(255, 255, 255, 0.2);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                    <span><?= count($galleryList) ?> Photos</span>
                  </div>

                  <!-- Zoom Hint Icon -->
                  <div style="position: absolute; top: 12px; right: 12px; background: rgba(0, 0, 0, 0.5); color: #fff; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/><line x1="11" x2="11" y1="8" y2="14"/><line x1="8" x2="14" y1="11" y2="11"/></svg>
                  </div>
                </div>

                <!-- Active Photo Caption -->
                <p id="mainGalleryCaption" style="font-size: var(--font-size-xs); color: var(--color-text-muted); margin: 8px 0 12px 2px; font-style: italic;">
                  <?= Security::e($galleryList[0]['alt_text'] ?? $data['title']) ?>
                </p>

                <!-- Thumbnails Bar (Multi-Image Switcher) -->
                <?php if (count($galleryList) > 1): ?>
                  <div style="display: flex; gap: 10px; overflow-x: auto; padding-bottom: 4px;" class="gallery-thumbs-row">
                    <?php foreach ($galleryList as $idx => $img): ?>
                      <div class="gallery-thumb <?= $idx === 0 ? 'active' : '' ?>" 
                           onclick="switchGalleryPhoto(<?= (int)$idx ?>, '<?= Security::e($img['image_path']) ?>', '<?= Security::e(addslashes($img['alt_text'] ?? $data['title'])) ?>')"
                           style="flex: 0 0 88px; height: 60px; border-radius: var(--radius-sm); overflow: hidden; cursor: pointer; border: 2px solid <?= $idx === 0 ? 'var(--color-accent)' : 'var(--color-border)' ?>; transition: all 0.2s ease;">
                        <img src="<?= Security::e($img['image_path']) ?>" alt="<?= Security::e($img['alt_text'] ?? 'Photo') ?>" style="width: 100%; height: 100%; object-fit: cover;">
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            <?php elseif (!empty($data['cover_image'])): ?>
              <div style="margin-bottom: var(--space-6); border-radius: var(--radius-md); overflow: hidden; max-height: 420px; border: 1px solid var(--color-border);">
                <img src="<?= Security::e($data['cover_image']) ?>" alt="<?= Security::e($data['title']) ?>" style="width: 100%; height: 360px; object-fit: cover;">
              </div>
            <?php endif; ?>

            <div style="display: flex; gap: var(--space-2); margin-bottom: var(--space-4); flex-wrap: wrap;">
              <span class="badge badge-gold"><?= Security::e($data['destination_country']) ?></span>
              <?php if (!empty($data['duration_days'])): ?>
                <span class="badge badge-info"><?= (int)$data['duration_days'] ?> Days / <?= max(1, (int)$data['duration_days'] - 1) ?> Nights</span>
              <?php endif; ?>
              <?php if ($isPayable): ?>
                <span class="badge" style="background-color: <?= $hasActivePaymentMethods ? '#DCFCE7' : '#FEF3C7' ?>; color: <?= $hasActivePaymentMethods ? '#15803D' : '#92400E' ?>; font-weight: 700;">
                  <?= Security::e($paymentBadgeText) ?>
                </span>
              <?php endif; ?>
            </div>

            <h2 style="font-size: 1.5rem; margin-bottom: var(--space-4);">Itinerary Overview</h2>
            <div style="color: var(--color-text-muted); line-height: 1.8; white-space: pre-line; margin-bottom: var(--space-6);">
              <?= Security::e($data['description'] ?? $data['summary']) ?>
            </div>

            <?php if (!empty($data['itinerary'])): ?>
              <div style="margin-bottom: var(--space-6); padding-top: var(--space-6); border-top: 1px solid var(--color-border);">
                <h3 style="font-size: 1.25rem; color: var(--color-primary); margin-bottom: var(--space-3);">Day-by-Day Detailed Itinerary</h3>
                <div style="color: var(--color-text-muted); line-height: 1.8; white-space: pre-line;">
                  <?= Security::e($data['itinerary']) ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- Inclusions & Exclusions -->
            <div class="grid-2" style="gap: var(--space-6); margin-top: var(--space-6); padding-top: var(--space-6); border-top: 1px solid var(--color-border);">
              <div>
                <h3 style="color: var(--color-success); font-size: 1.15rem; margin-bottom: var(--space-3); display: flex; align-items: center; gap: 6px;">
                  <span>✓</span> What's Included
                </h3>
                <div style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.7; white-space: pre-line;">
                  <?= !empty($data['inclusions']) ? Security::e($data['inclusions']) : "• Accommodation in verified hotels\n• Daily breakfast\n• Airport transfers\n• Guided sightseeing" ?>
                </div>
              </div>

              <div>
                <h3 style="color: var(--color-danger); font-size: 1.15rem; margin-bottom: var(--space-3); display: flex; align-items: center; gap: 6px;">
                  <span>✕</span> What's Excluded
                </h3>
                <div style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.7; white-space: pre-line;">
                  <?= !empty($data['exclusions']) ? Security::e($data['exclusions']) : "• International flights (unless specified)\n• Entry visas & travel insurance\n• Personal expenses & tips" ?>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- Conversion Sidebar Column -->
        <div>
          <div style="background-color: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-6); box-shadow: var(--shadow-sm); position: sticky; top: 100px;">
            <div style="border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-4); margin-bottom: var(--space-4);">
              <span style="font-size: var(--font-size-xs); color: var(--color-text-light); text-transform: uppercase; font-weight: 600;">Pricing per Person</span>
              <div style="font-size: 1.75rem; font-weight: 800; color: var(--color-primary); margin-top: 4px;">
                <?php if (!empty($data['price_amount']) && (float)$data['price_amount'] > 0): ?>
                  ₦<?= number_format((float)$data['price_amount'], 2) ?>
                <?php else: ?>
                  Quote on Request
                <?php endif; ?>
              </div>
              <p style="font-size: var(--font-size-xs); color: var(--color-text-muted); margin-top: 4px;">
                Authoritative price per traveller &bull; Zero hidden surcharges.
              </p>
            </div>

            <div style="margin-bottom: var(--space-5);">
              <h4 style="font-size: var(--font-size-sm); margin-bottom: var(--space-2); color: var(--color-primary);">Reserve Your Experience</h4>
              <p style="font-size: var(--font-size-xs); color: var(--color-text-muted); line-height: 1.5;">
                Reserve your spot online or speak with a travel advisor:
              </p>
            </div>

            <!-- Action Buttons Stack (Single Unified Booking CTA) -->
            <div style="display: flex; flex-direction: column; gap: var(--space-3);">
              <?php if ($isPayable): ?>
                <a href="/packages/<?= urlencode($packageSlug) ?>/book" class="btn btn-primary btn-block" style="font-weight: 700; padding: 13px; text-align: center; gap: 8px; font-size: 1rem;">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  Book Tour Package &rarr;
                </a>
              <?php endif; ?>

              <!-- WhatsApp Consultation -->
              <a href="https://wa.me/2347017597650?text=Hello%20LAD%20Global,%20I%20am%20interested%20in%20the%20<?= urlencode($data['title']) ?>%20package." class="btn btn-accent btn-block" target="_blank" rel="noopener" style="font-weight: 700; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Chat on WhatsApp Now
              </a>

              <!-- Enquiry Form -->
              <a href="/contact?package=<?= urlencode($packageSlug) ?><?= !empty($data['id']) ? '&package_id=' . (int)$data['id'] : '' ?>" class="btn btn-outline btn-block" style="font-weight: 600; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                Send Online Enquiry
              </a>
            </div>

            <!-- Booking Security & Trust Badges -->
            <div style="margin-top: var(--space-6); padding-top: var(--space-4); border-top: 1px solid var(--color-border); font-size: var(--font-size-xs); color: var(--color-text-muted); display: flex; flex-direction: column; gap: 8px;">
              <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--color-success)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span>Instant Confirmation &amp; Official Receipt</span>
              </div>
              <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--color-success)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span><?= Security::e($paymentTrustText) ?></span>
              </div>
              <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--color-success)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span>No hidden booking surcharges</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div style="text-align: center; padding: var(--space-12) 0;">
        <h2>Package Not Found</h2>
        <p class="text-muted" style="margin-bottom: var(--space-6);">The package or itinerary you requested is not currently listed.</p>
        <a href="/packages" class="btn btn-primary">Browse All Available Packages</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Lightbox Modal for Full-Resolution Photo Viewing -->
<div id="galleryModal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.9); z-index: 9999; align-items: center; justify-content: center; padding: 20px;" onclick="closeGalleryModal(event)">
  <button type="button" onclick="closeGalleryModal()" style="position: absolute; top: 20px; right: 24px; background: transparent; border: none; color: #fff; font-size: 32px; font-weight: bold; cursor: pointer; line-height: 1;">&times;</button>
  <img id="modalGalleryImage" src="" alt="Enlarged package photo" style="max-width: 90vw; max-height: 85vh; object-fit: contain; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
</div>

<script>
function switchGalleryPhoto(index, imagePath, caption) {
  var mainImg = document.getElementById('mainGalleryImage');
  var mainCap = document.getElementById('mainGalleryCaption');
  if (mainImg) {
    mainImg.style.opacity = '0.4';
    setTimeout(function () {
      mainImg.src = imagePath;
      mainImg.alt = caption;
      mainImg.style.opacity = '1';
    }, 150);
  }
  if (mainCap) {
    mainCap.textContent = caption;
  }

  // Update active thumbnail borders
  var thumbs = document.querySelectorAll('.gallery-thumb');
  thumbs.forEach(function (thumb, i) {
    if (i === index) {
      thumb.style.borderColor = 'var(--color-accent)';
      thumb.style.transform = 'translateY(-2px)';
    } else {
      thumb.style.borderColor = 'var(--color-border)';
      thumb.style.transform = 'none';
    }
  });
}

function openGalleryModal() {
  var mainImg = document.getElementById('mainGalleryImage');
  var modal = document.getElementById('galleryModal');
  var modalImg = document.getElementById('modalGalleryImage');
  if (mainImg && modal && modalImg) {
    modalImg.src = mainImg.src;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }
}

function closeGalleryModal(event) {
  if (!event || event.target.id === 'galleryModal' || event.target.tagName === 'BUTTON') {
    var modal = document.getElementById('galleryModal');
    if (modal) {
      modal.style.display = 'none';
      document.body.style.overflow = '';
    }
  }
}

document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') {
    closeGalleryModal();
  }
});
</script>
