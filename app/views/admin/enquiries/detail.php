<?php
/**
 * Admin Single Enquiry & CRM Consultation Detail View
 * @var array $enquiry The enquiry record with package details
 * @var array $notes Consultation notes history
 */
use App\Core\Security;

$cleanPhone = preg_replace('/[^0-9]/', '', (string)$enquiry['phone']);
if (str_starts_with($cleanPhone, '0')) {
    $cleanPhone = '234' . substr($cleanPhone, 1);
}
?>

<div style="margin-bottom: 24px;">
  <a href="/admin/enquiries" class="admin-back-link">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
    Back to All Enquiries
  </a>
  <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
    <div>
      <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
        <h1 class="admin-topbar-title" style="margin: 0;">
          Enquiry: <?= Security::e($enquiry['reference']) ?>
        </h1>
        <?php if ($enquiry['status'] === 'new'): ?>
          <span class="admin-badge admin-badge-info">New Lead</span>
        <?php elseif ($enquiry['status'] === 'contacted'): ?>
          <span class="admin-badge admin-badge-warning">In Contact</span>
        <?php elseif ($enquiry['status'] === 'quoted'): ?>
          <span class="admin-badge admin-badge-navy">Quoted</span>
        <?php elseif ($enquiry['status'] === 'closed'): ?>
          <span class="admin-badge admin-badge-success">Closed / Won</span>
        <?php else: ?>
          <span class="admin-badge admin-badge-danger">Cancelled</span>
        <?php endif; ?>
      </div>
      <p class="admin-topbar-subtitle" style="margin-top: 4px;">
        Received on <?= date('l, d F Y \a\t H:i', strtotime($enquiry['created_at'])) ?>
      </p>
    </div>

    <!-- Quick Communication Action Bar -->
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
      <a href="tel:<?= Security::e($enquiry['phone']) ?>" class="admin-btn admin-btn-outline admin-btn-sm" title="Call customer phone">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
        Call Phone
      </a>
      <a href="https://wa.me/<?= $cleanPhone ?>?text=Hello%20<?= urlencode($enquiry['full_name']) ?>,%20this%20is%20LAD%20Global%20Travels%20regarding%20your%20enquiry%20<?= urlencode($enquiry['reference']) ?>." target="_blank" rel="noopener" class="admin-btn admin-btn-whatsapp admin-btn-sm" title="Start WhatsApp conversation">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
        Open WhatsApp
      </a>
      <a href="mailto:<?= Security::e($enquiry['email']) ?>?subject=LAD%20Global%20Travels%20-%20Enquiry%20<?= urlencode($enquiry['reference']) ?>" class="admin-btn admin-btn-outline admin-btn-sm" title="Compose email">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
        Send Email
      </a>
    </div>
  </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;" class="admin-grid-layout">
  <!-- Left Column: Customer & Enquiry Details -->
  <div>
    <!-- Customer Details Card -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2 class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
          Prospect Information
        </h2>
      </div>
      <div class="admin-card-body">
        <div class="admin-kv-grid">
          <div class="admin-kv-item">
            <span class="admin-kv-label">Full Name</span>
            <div class="admin-kv-value" style="font-size: 15px;"><?= Security::e($enquiry['full_name']) ?></div>
          </div>

          <div class="admin-kv-item">
            <span class="admin-kv-label">Email Address</span>
            <div class="admin-kv-value">
              <a href="mailto:<?= Security::e($enquiry['email']) ?>" style="color: var(--admin-primary-light); text-decoration: none;">
                <?= Security::e($enquiry['email']) ?>
              </a>
            </div>
          </div>

          <div class="admin-kv-item">
            <span class="admin-kv-label">Telephone / WhatsApp</span>
            <div class="admin-kv-value">
              <a href="tel:<?= Security::e($enquiry['phone']) ?>" style="color: var(--admin-primary-light); text-decoration: none;">
                <?= Security::e($enquiry['phone']) ?>
              </a>
            </div>
          </div>

          <div class="admin-kv-item">
            <span class="admin-kv-label">Service Category</span>
            <div>
              <span class="admin-badge admin-badge-navy" style="margin-top: 2px;">
                <?= Security::e(strtoupper($enquiry['service_type'])) ?>
              </span>
            </div>
          </div>

          <div class="admin-kv-item">
            <span class="admin-kv-label">Target Destination</span>
            <div class="admin-kv-value"><?= !empty($enquiry['destination']) ? Security::e($enquiry['destination']) : 'Not Specified' ?></div>
          </div>

          <div class="admin-kv-item">
            <span class="admin-kv-label">Estimated Travel Date</span>
            <div class="admin-kv-value"><?= !empty($enquiry['preferred_date']) ? Security::e($enquiry['preferred_date']) : 'Flexible' ?></div>
          </div>

          <div class="admin-kv-item">
            <span class="admin-kv-label">Number of Travellers</span>
            <div class="admin-kv-value"><?= !empty($enquiry['travellers_count']) ? (int)$enquiry['travellers_count'] : '1' ?> Person(s)</div>
          </div>

          <div class="admin-kv-item">
            <span class="admin-kv-label">Internal Record ID</span>
            <div class="admin-kv-value" style="font-family: monospace; color: var(--admin-text-light);">#<?= (int)$enquiry['id'] ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Package Association Card (if linked) -->
    <?php if (!empty($enquiry['package_title'])): ?>
      <div class="admin-card" style="border-left: 4px solid var(--admin-primary-light);">
        <div class="admin-card-header">
          <h2 class="admin-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
            Associated Tour Package
          </h2>
          <div style="display: flex; gap: 8px;">
            <?php if (!empty($enquiry['package_id'])): ?>
              <a href="/admin/packages/<?= (int)$enquiry['package_id'] ?>/edit" class="admin-btn admin-btn-outline admin-btn-sm">Edit Package</a>
            <?php endif; ?>
            <a href="/packages/<?= Security::e($enquiry['package_slug']) ?>" target="_blank" class="admin-btn admin-btn-outline admin-btn-sm">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
              View Live
            </a>
          </div>
        </div>
        <div class="admin-card-body">
          <div style="background-color: #F8FAFC; padding: 14px 18px; border-radius: var(--admin-radius-md); border: 1px solid var(--admin-border);">
            <div style="font-weight: 700; color: var(--admin-primary); font-size: 1.05rem;">
              <?= Security::e($enquiry['package_title']) ?>
            </div>
            <div style="font-family: monospace; font-size: 12px; color: var(--admin-text-muted); margin-top: 2px;">
              /packages/<?= Security::e($enquiry['package_slug']) ?>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Customer Message Card -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2 class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
          Inquiry Message &amp; Requirements
        </h2>
      </div>
      <div class="admin-card-body">
        <div style="background-color: #F8FAFC; border: 1px solid var(--admin-border); border-radius: var(--admin-radius-md); padding: 18px; font-size: 13.5px; color: var(--admin-text-main); line-height: 1.7; white-space: pre-line;">
          <?= Security::e($enquiry['message']) ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Right Column: Status & Consultation Notes (CRM) -->
  <div>
    <!-- Status & Follow-up Update Form -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2 class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
          Lifecycle &amp; Follow-up
        </h2>
      </div>
      <div class="admin-card-body">
        <form action="/admin/enquiries/<?= (int)$enquiry['id'] ?>/status" method="POST">
          <?= Security::csrfField() ?>

          <div class="admin-form-group">
            <label for="status" class="admin-form-label required">Lifecycle Status</label>
            <select name="status" id="status" class="admin-form-control" required>
              <option value="new" <?= $enquiry['status'] === 'new' ? 'selected' : '' ?>>New (Uncontacted)</option>
              <option value="contacted" <?= $enquiry['status'] === 'contacted' ? 'selected' : '' ?>>Contacted (Phone/WhatsApp Active)</option>
              <option value="quoted" <?= $enquiry['status'] === 'quoted' ? 'selected' : '' ?>>Quoted (Formal Proposal Sent)</option>
              <option value="closed" <?= $enquiry['status'] === 'closed' ? 'selected' : '' ?>>Closed (Completed / Won)</option>
              <option value="cancelled" <?= $enquiry['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled / Lost</option>
            </select>
          </div>

          <div class="admin-form-group">
            <label for="follow_up_date" class="admin-form-label">Next Follow-up Date</label>
            <input type="date" name="follow_up_date" id="follow_up_date" class="admin-form-control" value="<?= Security::e($enquiry['follow_up_date'] ?? '') ?>">
            <span class="admin-form-hint">Set reminder date for callback or proposal check.</span>
          </div>

          <button type="submit" class="admin-btn admin-btn-primary" style="width: 100%; font-weight: 700;">
            Update Status
          </button>
        </form>
      </div>
    </div>

    <!-- Internal Consultation Notes Card -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2 class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
          Consultation Notes
        </h2>
      </div>
      <div class="admin-card-body">
        <p style="font-size: 12px; color: var(--admin-text-muted); margin: 0 0 16px;">
          Private internal log. Notes are strictly confidential to administrators and never visible to the customer.
        </p>

        <!-- Add Note Form -->
        <form action="/admin/enquiries/<?= (int)$enquiry['id'] ?>/notes" method="POST" style="margin-bottom: 20px;">
          <?= Security::csrfField() ?>
          <div class="admin-form-group">
            <label for="note" class="admin-form-label required">Add Note</label>
            <textarea name="note" id="note" rows="3" class="admin-form-control" placeholder="Record telephone discussions, visa document status, quote details, or client preferences..." required style="font-size: 12.5px;"></textarea>
          </div>
          <button type="submit" class="admin-btn admin-btn-outline admin-btn-sm" style="width: 100%; font-weight: 600;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add Consultation Note
          </button>
        </form>

        <!-- Existing Notes Timeline -->
        <?php if (empty($notes)): ?>
          <div style="text-align: center; padding: 18px 0; color: var(--admin-text-muted); font-size: 12px;">
            No consultation notes recorded yet for this enquiry.
          </div>
        <?php else: ?>
          <div class="admin-timeline">
            <?php foreach ($notes as $n): ?>
              <div class="admin-timeline-item">
                <div class="admin-timeline-meta">
                  <span class="admin-timeline-author">
                    <svg style="width: 12px; height: 12px;" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <?= Security::e($n['admin_username'] ?? 'Consultant') ?>
                  </span>
                  <span><?= date('d M Y, H:i', strtotime($n['created_at'])) ?></span>
                </div>
                <div class="admin-timeline-content">
                  <?= Security::e($n['note']) ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
