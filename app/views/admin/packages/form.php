<?php
/**
 * Admin Tour Package Create / Edit Form View
 * @var array|null $package Package data when editing (null when creating)
 * @var array|null $errors Validation errors
 * @var string $formAction URL to submit form to
 * @var array $galleryImages List of gallery images when editing
 */
use App\Core\Security;

$isEdit = !empty($package['id']);
$pkg = $package ?? [];
?>

<div style="margin-bottom: 24px;">
  <a href="/admin/packages" class="admin-back-link">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
    Back to Tour Packages
  </a>
  <h1 class="admin-topbar-title" style="margin: 4px 0;">
    <?= $isEdit ? 'Edit Package: ' . Security::e($pkg['title']) : 'Create New Tour Package' ?>
  </h1>
  <p class="admin-topbar-subtitle">
    <?= $isEdit ? 'Update itinerary, duration, pricing schedule, and marketing gallery.' : 'Add a new handcrafted international tour package to your catalogue.' ?>
  </p>
</div>

<?php if (!empty($errors)): ?>
  <div style="background-color: var(--admin-danger-bg); border: 1px solid var(--admin-danger-border); border-radius: var(--admin-radius-md); padding: 16px 20px; margin-bottom: 24px;" role="alert">
    <strong style="color: var(--admin-danger); font-size: 13.5px; display: block; margin-bottom: 6px;">Please review the errors below:</strong>
    <ul style="color: var(--admin-danger); font-size: 13px; padding-left: 20px; margin: 0;">
      <?php foreach ($errors as $field => $errList): ?>
        <?php foreach ((array)$errList as $err): ?>
          <li><?= Security::e($err) ?></li>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form action="<?= Security::e($formAction) ?>" method="POST" enctype="multipart/form-data">
  <?= Security::csrfField() ?>

  <div style="display: grid; grid-template-columns: 2fr 1.1fr; gap: 24px;" class="admin-form-layout">
    <!-- Left Column: Primary Content & Itinerary -->
    <div>
      <!-- Basic Details Card -->
      <div class="admin-card">
        <div class="admin-card-header">
          <h2 class="admin-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            Primary Details
          </h2>
        </div>
        <div class="admin-card-body">
          <!-- Package Title -->
          <div class="admin-form-group">
            <label for="title" class="admin-form-label required">Package Title</label>
            <input type="text" name="title" id="title" class="admin-form-control" placeholder="e.g. Kenya Safari & Diani Beach Escape" value="<?= Security::e($pkg['title'] ?? '') ?>" required>
          </div>

          <!-- Custom Slug -->
          <div class="admin-form-group">
            <label for="slug" class="admin-form-label">URL Slug (Optional)</label>
            <input type="text" name="slug" id="slug" class="admin-form-control" placeholder="e.g. kenya-safari-experience (Leave blank to auto-generate)" value="<?= Security::e($pkg['slug'] ?? '') ?>">
            <span class="admin-form-hint">Unique identifier for the public web address (/packages/slug).</span>
          </div>

          <!-- Destination Country & City -->
          <div class="admin-form-row">
            <div class="admin-form-group">
              <label for="destination_country" class="admin-form-label required">Destination Country</label>
              <input type="text" name="destination_country" id="destination_country" class="admin-form-control" placeholder="e.g. Kenya" value="<?= Security::e($pkg['destination_country'] ?? '') ?>" required>
            </div>
            <div class="admin-form-group">
              <label for="destination_city" class="admin-form-label">Destination City / Region</label>
              <input type="text" name="destination_city" id="destination_city" class="admin-form-control" placeholder="e.g. Nairobi & Diani" value="<?= Security::e($pkg['destination_city'] ?? '') ?>">
            </div>
          </div>

          <!-- Summary -->
          <div class="admin-form-group">
            <label for="summary" class="admin-form-label required">Short Summary</label>
            <textarea name="summary" id="summary" rows="3" class="admin-form-control" placeholder="One or two sentences highlighting the trip for directory cards..." required><?= Security::e($pkg['summary'] ?? '') ?></textarea>
          </div>

          <!-- Full Description -->
          <div class="admin-form-group" style="margin-bottom: 0;">
            <label for="description" class="admin-form-label required">Full Description &amp; Highlights</label>
            <textarea name="description" id="description" rows="6" class="admin-form-control" placeholder="Detailed narrative describing the travel experience..." required><?= Security::e($pkg['description'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <!-- Itinerary & Inclusions Card -->
      <div class="admin-card">
        <div class="admin-card-header">
          <h2 class="admin-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            Itinerary &amp; Inclusions
          </h2>
        </div>
        <div class="admin-card-body">
          <!-- Day by Day Itinerary -->
          <div class="admin-form-group">
            <label for="itinerary" class="admin-form-label">Day-by-Day Itinerary</label>
            <textarea name="itinerary" id="itinerary" rows="6" class="admin-form-control" placeholder="Day 1: Arrival & Hotel Transfer&#10;Day 2: Morning Safari Game Drive..."><?= Security::e($pkg['itinerary'] ?? '') ?></textarea>
          </div>

          <!-- Inclusions & Exclusions -->
          <div class="admin-form-row">
            <div class="admin-form-group" style="margin-bottom: 0;">
              <label for="inclusions" class="admin-form-label">What's Included</label>
              <textarea name="inclusions" id="inclusions" rows="5" class="admin-form-control" placeholder="• 4 Nights Hotel Accommodation&#10;• Daily Breakfast&#10;• Airport Transfers"><?= Security::e($pkg['inclusions'] ?? '') ?></textarea>
            </div>
            <div class="admin-form-group" style="margin-bottom: 0;">
              <label for="exclusions" class="admin-form-label">What's Excluded</label>
              <textarea name="exclusions" id="exclusions" rows="5" class="admin-form-control" placeholder="• International Flights&#10;• Visa Fees&#10;• Personal Expenses"><?= Security::e($pkg['exclusions'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Settings, Pricing, Schedule & Media -->
    <div>
      <!-- Status Card -->
      <div class="admin-card">
        <div class="admin-card-header">
          <h2 class="admin-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
            Publication Status
          </h2>
        </div>
        <div class="admin-card-body">
          <div class="admin-form-group">
            <label for="status" class="admin-form-label required">Visibility</label>
            <select name="status" id="status" class="admin-form-control" required>
              <option value="draft" <?= ($pkg['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft (Internal Only)</option>
              <option value="published" <?= ($pkg['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published (Live on Website)</option>
              <option value="archived" <?= ($pkg['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived (Protected & Hidden)</option>
            </select>
          </div>

          <div class="admin-form-group" style="margin-bottom: 0;">
            <label class="admin-checkbox-label">
              <input type="checkbox" name="is_featured" value="1" <?= !empty($pkg['is_featured']) ? 'checked' : '' ?>>
              <span><strong>Feature on Homepage</strong><br><span style="color: var(--admin-text-muted); font-size: 12px;">Display in high-priority curated section on homepage</span></span>
            </label>
          </div>
        </div>
      </div>

      <!-- Pricing Card -->
      <div class="admin-card">
        <div class="admin-card-header">
          <h2 class="admin-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            Pricing &amp; Bookings
          </h2>
        </div>
        <div class="admin-card-body">
          <div class="admin-form-row">
            <div class="admin-form-group">
              <label for="price_amount" class="admin-form-label">Price per Person (NGN)</label>
              <input type="number" step="0.01" name="price_amount" id="price_amount" class="admin-form-control" placeholder="e.g. 750000" value="<?= Security::e(isset($pkg['price_amount']) ? (string)$pkg['price_amount'] : '') ?>">
              <span class="admin-form-hint">Leave blank for quote-only.</span>
            </div>

            <div class="admin-form-group">
              <label for="price_currency" class="admin-form-label">Currency</label>
              <input type="text" name="price_currency" id="price_currency" class="admin-form-control" value="<?= Security::e($pkg['price_currency'] ?? 'NGN') ?>" readonly style="background-color: #F8FAFC;">
            </div>
          </div>

          <div class="admin-form-group" style="margin-bottom: 0;">
            <label class="admin-checkbox-label">
              <input type="checkbox" name="is_payment_enabled" value="1" <?= !empty($pkg['is_payment_enabled']) ? 'checked' : '' ?>>
              <span><strong>Enable Direct Online Booking</strong><br><span style="color: var(--admin-text-muted); font-size: 12px;">Allows guests to checkout directly via Paystack or Bank Transfer</span></span>
            </label>
          </div>
        </div>
      </div>

      <!-- Schedule & Duration Card -->
      <div class="admin-card">
        <div class="admin-card-header">
          <h2 class="admin-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            Schedule &amp; Capacity
          </h2>
        </div>
        <div class="admin-card-body">
          <div class="admin-form-group">
            <label for="duration_days" class="admin-form-label">Duration in Days</label>
            <input type="number" name="duration_days" id="duration_days" class="admin-form-control" placeholder="e.g. 7" value="<?= Security::e(isset($pkg['duration_days']) ? (string)$pkg['duration_days'] : '') ?>">
          </div>

          <div class="admin-form-row">
            <div class="admin-form-group">
              <label for="travel_start_date" class="admin-form-label">Start Date</label>
              <input type="date" name="travel_start_date" id="travel_start_date" class="admin-form-control" value="<?= Security::e($pkg['travel_start_date'] ?? '') ?>">
            </div>
            <div class="admin-form-group">
              <label for="travel_end_date" class="admin-form-label">End Date</label>
              <input type="date" name="travel_end_date" id="travel_end_date" class="admin-form-control" value="<?= Security::e($pkg['travel_end_date'] ?? '') ?>">
            </div>
          </div>

          <div class="admin-form-row" style="margin-bottom: 0;">
            <div class="admin-form-group" style="margin-bottom: 0;">
              <label for="capacity" class="admin-form-label">Capacity (Max Guests)</label>
              <input type="number" name="capacity" id="capacity" class="admin-form-control" placeholder="e.g. 20" value="<?= Security::e(isset($pkg['capacity']) ? (string)$pkg['capacity'] : '') ?>">
            </div>
            <div class="admin-form-group" style="margin-bottom: 0;">
              <label for="booking_deadline" class="admin-form-label">Booking Deadline</label>
              <input type="date" name="booking_deadline" id="booking_deadline" class="admin-form-control" value="<?= Security::e($pkg['booking_deadline'] ?? '') ?>">
            </div>
          </div>
        </div>
      </div>

      <!-- Media & Gallery Card -->
      <div class="admin-card">
        <div class="admin-card-header">
          <h2 class="admin-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
            Cover &amp; Gallery Photos
          </h2>
        </div>
        <div class="admin-card-body">
          <?php if (!empty($pkg['cover_image'])): ?>
            <div style="margin-bottom: 16px;">
              <span class="admin-form-label">Current Primary Cover:</span>
              <img src="<?= Security::e($pkg['cover_image']) ?>" alt="Current Cover" style="width: 100%; height: 160px; object-fit: cover; border-radius: var(--admin-radius-md); border: 1px solid var(--admin-border); display: block;">
            </div>
          <?php endif; ?>

          <div class="admin-form-group">
            <label for="cover_image" class="admin-form-label"><?= $isEdit ? 'Upload New Cover (Optional)' : 'Primary Cover Photo' ?></label>
            <input type="file" name="cover_image" id="cover_image" class="admin-form-control" accept="image/jpeg,image/png,image/webp">
            <span class="admin-form-hint">Accepted formats: JPG, PNG, WebP (Max 5 MB).</span>
          </div>

          <!-- Multiple Gallery Uploads -->
          <div class="admin-form-group" style="border-top: 1px dashed var(--admin-border); padding-top: 16px; margin-bottom: 0;">
            <label for="gallery_images" class="admin-form-label">
              Additional Gallery Photos (Multiple Allowed)
            </label>
            <input type="file" name="gallery_images[]" id="gallery_images" class="admin-form-control" multiple accept="image/jpeg,image/png,image/webp">
            <span class="admin-form-hint">Select one or multiple photos to display in the package's interactive image gallery.</span>
          </div>

          <!-- Existing Gallery Photos (When Editing) -->
          <?php if ($isEdit && !empty($galleryImages)): ?>
            <div style="margin-top: 18px; border-top: 1px solid var(--admin-border); padding-top: 16px;">
              <span class="admin-form-label" style="margin-bottom: 8px;">
                Active Gallery Photos (<?= count($galleryImages) ?>):
              </span>
              <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 8px;">
                <?php foreach ($galleryImages as $img): ?>
                  <div style="position: relative; border-radius: var(--admin-radius-sm); overflow: hidden; border: 1px solid var(--admin-border); aspect-ratio: 4/3; background: #000;">
                    <img src="<?= Security::e($img['image_path']) ?>" alt="<?= Security::e($img['alt_text'] ?? 'Gallery image') ?>" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                    <button type="button" onclick="deleteGalleryPhoto(<?= (int)$pkg['id'] ?>, <?= (int)$img['id'] ?>)" title="Remove photo"
                            style="position: absolute; top: 4px; right: 4px; background: rgba(220, 38, 38, 0.9); color: #fff; border: none; border-radius: 50%; width: 22px; height: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: bold; line-height: 1;">
                      &times;
                    </button>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Action Buttons -->
      <div style="display: flex; gap: 12px; margin-bottom: 32px;">
        <button type="submit" class="admin-btn admin-btn-primary" style="flex: 1; padding: 12px;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
          <?= $isEdit ? 'Save Changes' : 'Create Package' ?>
        </button>
        <a href="/admin/packages" class="admin-btn admin-btn-outline" style="padding: 12px 20px;">Cancel</a>
      </div>
    </div>
  </div>
</form>

<!-- Standalone form for gallery photo deletions (placed outside main form to prevent invalid nested forms) -->
<?php if ($isEdit): ?>
  <form id="deleteImageForm" method="POST" style="display: none;">
    <?= Security::csrfField() ?>
  </form>
  <script>
  function deleteGalleryPhoto(packageId, imageId) {
    if (confirm('Are you sure you want to remove this gallery photo?')) {
      var form = document.getElementById('deleteImageForm');
      form.action = '/admin/packages/' + packageId + '/images/' + imageId + '/delete';
      form.submit();
    }
  }
  </script>
<?php endif; ?>
