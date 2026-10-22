<?php
/**
 * Public Contact & Travel Enquiry View
 * @var array|null $errors Form validation errors (if any)
 * @var array|null $formData Old form input data (if any)
 */
use App\Core\Security;

$packageId = $associatedPackage['id'] ?? ($_GET['package_id'] ?? ($formData['package_id'] ?? null));
$packageTitle = $associatedPackage['title'] ?? null;
$prefilledService = $associatedPackage ? 'tours' : ($_GET['service'] ?? ($formData['service_type'] ?? ''));
$prefilledDest = $associatedPackage ? ($associatedPackage['destination_city'] ?? $associatedPackage['destination_country']) : ($_GET['package'] ?? ($formData['destination'] ?? ''));
?>

<!-- Page Hero -->
<section class="page-hero page-hero-contact">
  <div class="container">
    <span class="eyebrow-pill">GET IN TOUCH</span>
    <h1 class="page-hero-title"><?= $packageTitle ? 'Tour Package Consultation' : 'Contact & Travel Consultation' ?></h1>
    <p class="page-hero-desc">
      Have a question or planning your next international journey? Visit our office in Ikorodu, Lagos, message us on WhatsApp, or send a detailed enquiry below.
    </p>
  </div>
</section>

<!-- Contact & Form Section -->
<section class="section">
  <div class="container">
    <div class="contact-grid">

      <!-- Left Column: Contact Information & Office Details -->
      <div>
        <h2 style="font-size: 1.5rem; margin-bottom: var(--space-4); color: var(--color-primary); font-weight: 800;">Connect with Our Team</h2>
        <p class="text-muted" style="margin-bottom: var(--space-6); line-height: 1.6;">
          Our travel consultants are available to assist with all your visa guidance, flight consultation, and package inquiries.
        </p>

<?php
$company = \App\Services\SettingService::getCompanyDetails();
$emailRouting = \App\Services\SettingService::getEmailRouting();
$waUrl = \App\Services\SettingService::formatWhatsAppUrl($company['whatsapp'], 'Hello LAD Global Travels, I would like to make an enquiry.');
$phoneLink = \App\Services\SettingService::formatPhoneLink($company['phone']);
?>
        <!-- Address Card -->
        <div class="contact-info-card">
          <div class="contact-icon" style="background-color: #FEE2E2; color: #DC2626; border-radius: 12px;" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
          </div>
          <div>
            <strong style="display: block; margin-bottom: 4px; color: var(--color-primary);">Physical Office Address</strong>
            <p style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.5; margin: 0;">
              <?= nl2br(\App\Core\Security::e($company['address'])) ?>
            </p>
          </div>
        </div>

        <!-- Phone & WhatsApp Card -->
        <div class="contact-info-card">
          <div class="contact-icon" style="background-color: #DCFCE7; color: #16A34A; border-radius: 12px;" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          </div>
          <div>
            <strong style="display: block; margin-bottom: 4px; color: var(--color-primary);">Phone & WhatsApp</strong>
            <p style="font-size: var(--font-size-sm); color: var(--color-text-muted); margin-bottom: 8px;">
              Call: <a href="<?= $phoneLink ?>" style="color: var(--color-primary-light); font-weight: 600;"><?= \App\Core\Security::e($company['phone']) ?></a>
            </p>
            <a href="<?= $waUrl ?>" class="btn btn-accent btn-sm" target="_blank" rel="noopener noreferrer" style="font-weight: 700;">
              Chat on WhatsApp Directly
            </a>
          </div>
        </div>

        <!-- Email Card -->
        <div class="contact-info-card">
          <div class="contact-icon" style="background-color: #DBEAFE; color: #2563EB; border-radius: 12px;" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
          </div>
          <div>
            <strong style="display: block; margin-bottom: 4px; color: var(--color-primary);">Official Correspondence</strong>
            <p style="font-size: var(--font-size-sm); color: var(--color-text-muted); margin-bottom: 4px;">
              General: <a href="mailto:<?= \App\Core\Security::e($emailRouting['from_address']) ?>" style="color: var(--color-primary-light); font-weight: 500;"><?= \App\Core\Security::e($emailRouting['from_address']) ?></a>
            </p>
            <p style="font-size: var(--font-size-sm); color: var(--color-text-muted); margin: 0;">
              Tours & Sales: <a href="mailto:<?= \App\Core\Security::e($emailRouting['enquiries']) ?>" style="color: var(--color-primary-light); font-weight: 500;"><?= \App\Core\Security::e($emailRouting['enquiries']) ?></a>
            </p>
          </div>
        </div>

        <!-- Hours Card -->
        <div class="contact-info-card">
          <div class="contact-icon" style="background-color: #E0E7FF; color: #4F46E5; border-radius: 12px;" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
          <div>
            <strong style="display: block; margin-bottom: 4px; color: var(--color-primary);">Working Hours</strong>
            <p style="font-size: var(--font-size-sm); color: var(--color-text-muted); line-height: 1.5; margin: 0;">
              <?= \App\Core\Security::e($company['hours']) ?>
            </p>
          </div>
        </div>
      </div>

      <!-- Right Column: Interactive Consultation & Enquiry Form -->
      <div>
        <div style="background-color: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-8); box-shadow: var(--shadow-sm);">
          <h2 style="font-size: 1.5rem; margin-bottom: var(--space-2); color: var(--color-primary);">
            <?= $packageTitle ? 'Tour Package Inquiry' : 'Send an Online Enquiry' ?>
          </h2>
          <p class="text-muted" style="font-size: var(--font-size-sm); margin-bottom: var(--space-6);">
            <?= $packageTitle ? 'Submit your travel dates and passenger count below for custom pricing and itinerary options.' : 'Fill out the form below. A dedicated consultant will review your details and respond promptly.' ?>
          </p>

          <?php if ($packageTitle): ?>
            <div style="background-color: var(--color-surface-subtle); border: 1px solid var(--color-border); border-left: 4px solid var(--color-accent); border-radius: var(--radius-md); padding: var(--space-4); margin-bottom: var(--space-6);">
              <span style="font-size: var(--font-size-xs); color: var(--color-text-light); text-transform: uppercase; font-weight: bold; display: block;">Tour Package Selected:</span>
              <strong style="font-size: 1.05rem; color: var(--color-primary);"><?= Security::e($packageTitle) ?></strong>
            </div>
          <?php endif; ?>

          <?php if (!empty($errors)): ?>
            <div style="background-color: var(--color-danger-bg); border: 1px solid rgba(197, 34, 31, 0.3); border-radius: var(--radius-md); padding: var(--space-4); margin-bottom: var(--space-6);" role="alert">
              <strong style="color: var(--color-danger); font-size: var(--font-size-sm); display: block; margin-bottom: 4px;">Please review the following errors:</strong>
              <ul style="color: var(--color-danger); font-size: var(--font-size-xs); padding-left: var(--space-4); margin: 0;">
                <?php foreach ($errors as $field => $errs): ?>
                  <?php foreach ((array)$errs as $err): ?>
                    <li><?= Security::e($err) ?></li>
                  <?php endforeach; ?>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form action="/contact" method="POST" novalidate>
            <?= Security::csrfField() ?>

            <!-- Package association hidden fields -->
            <?php if (!empty($packageId)): ?>
              <input type="hidden" name="package_id" value="<?= (int)$packageId ?>">
            <?php endif; ?>
            <?php if (!empty($associatedPackage['slug'])): ?>
              <input type="hidden" name="package_slug" value="<?= Security::e($associatedPackage['slug']) ?>">
            <?php endif; ?>

            <!-- Anti-Spam Honeypot Field -->
            <div style="display: none;" aria-hidden="true">
              <label for="website_url_check">Do not fill this field</label>
              <input type="text" name="website_url_check" id="website_url_check" tabindex="-1" autocomplete="off">
            </div>

            <!-- Full Name -->
            <div class="form-group">
              <label for="full_name" class="form-label form-label-required">Full Name</label>
              <input type="text" name="full_name" id="full_name" class="form-control <?= !empty($errors['full_name']) ? 'is-invalid' : '' ?>" placeholder="e.g. Olawale Johnson" value="<?= Security::e($formData['full_name'] ?? '') ?>" required>
              <?php if (!empty($errors['full_name'])): ?>
                <span class="form-error-msg"><?= Security::e($errors['full_name'][0]) ?></span>
              <?php endif; ?>
            </div>

            <!-- Email & Phone (Grid) -->
            <div class="grid-2" style="gap: var(--space-4);">
              <div class="form-group">
                <label for="email" class="form-label form-label-required">Email Address</label>
                <input type="email" name="email" id="email" class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>" placeholder="e.g. olawale@example.com" value="<?= Security::e($formData['email'] ?? '') ?>" required>
                <?php if (!empty($errors['email'])): ?>
                  <span class="form-error-msg"><?= Security::e($errors['email'][0]) ?></span>
                <?php endif; ?>
              </div>

              <div class="form-group">
                <label for="phone" class="form-label form-label-required">Phone / WhatsApp Number</label>
                <input type="tel" name="phone" id="phone" class="form-control <?= !empty($errors['phone']) ? 'is-invalid' : '' ?>" placeholder="e.g. 08012345678" value="<?= Security::e($formData['phone'] ?? '') ?>" required>
                <?php if (!empty($errors['phone'])): ?>
                  <span class="form-error-msg"><?= Security::e($errors['phone'][0]) ?></span>
                <?php endif; ?>
              </div>
            </div>

            <!-- Service Selection -->
            <div class="form-group">
              <label for="service_type" class="form-label form-label-required">Service of Interest</label>
              <select name="service_type" id="service_type" class="form-control <?= !empty($errors['service_type']) ? 'is-invalid' : '' ?>" required>
                <option value="">-- Please Select a Service --</option>
                <option value="visa" <?= $prefilledService === 'visa' ? 'selected' : '' ?>>Visa Assistance & Advisory</option>
                <option value="tours" <?= ($prefilledService === 'tours' || !empty($prefilledDest) || !empty($packageTitle)) ? 'selected' : '' ?>>International Tour Packages</option>
                <option value="flights" <?= $prefilledService === 'flights' ? 'selected' : '' ?>>Flight Booking Assistance (Enquiry-Only)</option>
                <option value="hotels" <?= $prefilledService === 'hotels' ? 'selected' : '' ?>>Hotel Reservations (Enquiry-Only)</option>
                <option value="study" <?= $prefilledService === 'study' ? 'selected' : '' ?>>Study Abroad Advisory</option>
                <option value="canada_pr" <?= $prefilledService === 'canada_pr' ? 'selected' : '' ?>>Canada Permanent Residency (PR)</option>
                <option value="cbi" <?= $prefilledService === 'cbi' ? 'selected' : '' ?>>Citizenship by Investment (CBI)</option>
                <option value="rbi" <?= $prefilledService === 'rbi' ? 'selected' : '' ?>>Residency by Investment (RBI)</option>
                <option value="other" <?= $prefilledService === 'other' ? 'selected' : '' ?>>Other General Travel Inquiry</option>
              </select>
            </div>

            <!-- Destination & Travellers (Grid) -->
            <div class="grid-2" style="gap: var(--space-4);">
              <div class="form-group">
                <label for="destination" class="form-label">Destination (Optional)</label>
                <input type="text" name="destination" id="destination" class="form-control" placeholder="e.g. Kenya, Dubai, UK" value="<?= Security::e($formData['destination'] ?? $prefilledDest) ?>">
              </div>

              <div class="form-group">
                <label for="travellers_count" class="form-label">Number of Travellers</label>
                <input type="number" min="1" max="100" name="travellers_count" id="travellers_count" class="form-control" placeholder="e.g. 2" value="<?= Security::e((string)($formData['travellers_count'] ?? '1')) ?>">
              </div>
            </div>

            <!-- Travel Date -->
            <div class="form-group">
              <label for="preferred_date" class="form-label">Estimated Travel Date (Optional)</label>
              <input type="date" name="preferred_date" id="preferred_date" class="form-control" value="<?= Security::e($formData['preferred_date'] ?? '') ?>">
            </div>

            <!-- Message Details -->
            <div class="form-group">
              <label for="message" class="form-label form-label-required">Message / Specific Requirements</label>
              <textarea name="message" id="message" rows="4" class="form-control <?= !empty($errors['message']) ? 'is-invalid' : '' ?>" placeholder="Tell us about your trip: number of travellers, special requests, or visa questions..." required><?= Security::e($formData['message'] ?? '') ?></textarea>
              <?php if (!empty($errors['message'])): ?>
                <span class="form-error-msg"><?= Security::e($errors['message'][0]) ?></span>
              <?php endif; ?>
            </div>

            <!-- Privacy Notice & Consent -->
            <div class="form-group">
              <label class="form-checkbox-label">
                <input type="checkbox" name="privacy_consent" value="1" required checked>
                <span>I understand that LAD Global Travels and Tours will use my contact details solely to assist with this travel inquiry in accordance with our <a href="/privacy" target="_blank" style="color: var(--color-primary-light);">Privacy Notice</a>.</span>
              </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: var(--space-4);">
              Submit Consultation Enquiry
            </button>
          </form>
        </div>
      </div>

    </div>
  </div>
</section>
