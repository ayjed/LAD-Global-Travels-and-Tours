<?php
/**
 * Admin Business & Integration Settings View
 * Phase P08: Company Contact, Official Social Media, Email Routing & Protected Bank Details
 */
use App\Core\Security;

$csrfToken = Security::csrfToken();
?>

<div class="admin-topbar">
  <div>
    <h1 class="admin-topbar-title">Business &amp; Integration Settings</h1>
    <p class="admin-topbar-subtitle">Manage public contact information, official social channels, email notifications, and verified corporate bank details.</p>
  </div>
  <div class="admin-topbar-actions">
    <a href="/admin/dashboard" class="admin-btn admin-btn-outline">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
      Back to Dashboard
    </a>
  </div>
</div>

<!-- Tabs Navigation -->
<div class="admin-toolbar" style="margin-bottom: 24px; padding: 6px;">
  <div class="admin-tabs" style="width: 100%; display: flex; flex-wrap: wrap; gap: 4px; background: transparent; padding: 0;">
    <button type="button" class="admin-tab active settings-tab-btn" onclick="switchSettingsTab('contact')" id="tab-btn-contact" style="border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
      <svg style="width: 15px; height: 15px;" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
      Company &amp; Contact
    </button>
    <button type="button" class="admin-tab settings-tab-btn" onclick="switchSettingsTab('social')" id="tab-btn-social" style="border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
      <svg style="width: 15px; height: 15px;" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
      Social Media
    </button>
    <button type="button" class="admin-tab settings-tab-btn" onclick="switchSettingsTab('email')" id="tab-btn-email" style="border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
      <svg style="width: 15px; height: 15px;" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
      Email &amp; Mailboxes
    </button>
    <button type="button" class="admin-tab settings-tab-btn" onclick="switchSettingsTab('bank')" id="tab-btn-bank" style="border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
      <svg style="width: 15px; height: 15px;" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
      Protected Bank Details
    </button>
    <button type="button" class="admin-tab settings-tab-btn" onclick="switchSettingsTab('paystack')" id="tab-btn-paystack" style="border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
      <svg style="width: 15px; height: 15px;" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
      Paystack Gateway
    </button>
    <button type="button" class="admin-tab settings-tab-btn" onclick="switchSettingsTab('payment_methods')" id="tab-btn-payment_methods" style="border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
      <svg style="width: 15px; height: 15px;" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path><line x1="12" y1="6" x2="12" y2="8"></line><line x1="12" y1="16" x2="12" y2="18"></line></svg>
      Payment Methods (<?= (int)($paymentMethods['total_enabled'] ?? 2) ?>/2)
    </button>
  </div>
</div>

<!-- Tab 1: Company & Contact Information -->
<div class="settings-tab-content" id="tab-content-contact">
  <div class="admin-card">
    <div class="admin-card-header">
      <div>
        <h2 class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
          Public Business Contact Details
        </h2>
        <p class="admin-card-subtitle">These details are displayed dynamically across the public website header, footer, contact consultation page, and confirmation receipts.</p>
      </div>
    </div>
    <div class="admin-card-body">
      <form method="POST" action="/admin/settings">
        <?= Security::csrfField() ?>
        <input type="hidden" name="section" value="company">

        <div class="admin-form-row">
          <div class="admin-form-group">
            <label class="admin-form-label required">Company Trading Name</label>
            <input type="text" name="company_name" value="<?= Security::e($company['name']) ?>" required class="admin-form-control">
            <span class="admin-form-hint">Official brand identity approved in SOT.</span>
          </div>

          <div class="admin-form-group">
            <label class="admin-form-label">Brand Tagline</label>
            <input type="text" name="company_tagline" value="<?= Security::e($company['tagline']) ?>" class="admin-form-control">
            <span class="admin-form-hint">e.g. Explore &bull; Discover &bull; Experience</span>
          </div>

          <div class="admin-form-group">
            <label class="admin-form-label required">Customer Service Telephone</label>
            <input type="text" name="contact_phone" value="<?= Security::e($company['phone']) ?>" required class="admin-form-control">
            <span class="admin-form-hint">Click-to-call link active on public mobile devices.</span>
          </div>

          <div class="admin-form-group">
            <label class="admin-form-label required">Official WhatsApp Number</label>
            <input type="text" name="contact_whatsapp" value="<?= Security::e($company['whatsapp']) ?>" required class="admin-form-control">
            <span class="admin-form-hint">Clean destination link for web &amp; mobile WhatsApp buttons.</span>
          </div>
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label required">Physical Head Office Address</label>
          <textarea name="office_address" rows="2" required class="admin-form-control"><?= Security::e($company['address']) ?></textarea>
          <span class="admin-form-hint">Physical premises in Ikorodu, Lagos (as supplied in SOT Section 1.0).</span>
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">Operating Office Hours</label>
          <input type="text" name="office_hours" value="<?= Security::e($company['hours']) ?>" class="admin-form-control">
          <span class="admin-form-hint">Displayed on the public contact consultation page.</span>
        </div>

        <button type="submit" class="admin-btn admin-btn-primary" style="font-weight: 700;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
          Save Contact Settings
        </button>
      </form>
    </div>
  </div>
</div>

<!-- Tab 2: Social Media Profiles -->
<div class="settings-tab-content" id="tab-content-social" style="display: none;">
  <div class="admin-card">
    <div class="admin-card-header">
      <div>
        <h2 class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
          Official Social Media Channels
        </h2>
        <p class="admin-card-subtitle">Connect website visitors directly to verified corporate channels. Fully self-contained URLs without third-party API dependencies.</p>
      </div>
    </div>
    <div class="admin-card-body">
      <form method="POST" action="/admin/settings">
        <?= Security::csrfField() ?>
        <input type="hidden" name="section" value="social">

        <!-- Instagram -->
        <div class="admin-form-group">
          <label class="admin-form-label">Instagram Profile URL</label>
          <div style="display: flex; gap: 8px;">
            <input type="url" name="social_instagram" value="<?= Security::e($social['instagram'] ?? '') ?>" placeholder="https://instagram.com/ladglobaltravels" class="admin-form-control">
            <?php if (!empty($social['instagram'])): ?>
              <a href="<?= Security::e($social['instagram']) ?>" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn-outline admin-btn-sm" style="white-space: nowrap;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                Test Link
              </a>
            <?php endif; ?>
          </div>
          <span class="admin-form-hint">Leave blank if the corporate Instagram account has not yet launched.</span>
        </div>

        <!-- Facebook -->
        <div class="admin-form-group">
          <label class="admin-form-label">Facebook Page URL</label>
          <div style="display: flex; gap: 8px;">
            <input type="url" name="social_facebook" value="<?= Security::e($social['facebook'] ?? '') ?>" placeholder="https://facebook.com/ladglobaltravels" class="admin-form-control">
            <?php if (!empty($social['facebook'])): ?>
              <a href="<?= Security::e($social['facebook']) ?>" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn-outline admin-btn-sm" style="white-space: nowrap;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                Test Link
              </a>
            <?php endif; ?>
          </div>
          <span class="admin-form-hint">Leave blank if the corporate Facebook page has not yet launched.</span>
        </div>

        <!-- LinkedIn -->
        <div class="admin-form-group">
          <label class="admin-form-label">LinkedIn Company URL (Optional)</label>
          <div style="display: flex; gap: 8px;">
            <input type="url" name="social_linkedin" value="<?= Security::e($social['linkedin'] ?? '') ?>" placeholder="https://linkedin.com/company/ladglobaltravels" class="admin-form-control">
            <?php if (!empty($social['linkedin'])): ?>
              <a href="<?= Security::e($social['linkedin']) ?>" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn-outline admin-btn-sm" style="white-space: nowrap;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                Test Link
              </a>
            <?php endif; ?>
          </div>
        </div>

        <button type="submit" class="admin-btn admin-btn-primary" style="font-weight: 700;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
          Save Social Channels
        </button>
      </form>
    </div>
  </div>
</div>

<!-- Tab 3: Email Routing & Mailboxes -->
<div class="settings-tab-content" id="tab-content-email" style="display: none;">
  <div class="admin-card">
    <div class="admin-card-header">
      <div>
        <h2 class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
          Email Routing &amp; Notification Mailboxes
        </h2>
        <p class="admin-card-subtitle">Configure where client consultations, tour booking receipts, and payment alerts are routed.</p>
      </div>
    </div>
    <div class="admin-card-body">
      <!-- SOT Mailbox Architecture Info Card -->
      <div style="background-color: var(--admin-info-bg); border: 1px solid var(--admin-info-border); border-left: 4px solid var(--admin-info); border-radius: var(--admin-radius-md); padding: 16px 20px; margin-bottom: 24px;">
        <h3 style="margin: 0 0 6px; font-size: 13.5px; color: #0369A1; font-weight: 700; display: flex; align-items: center; gap: 6px;">
          <svg style="width: 15px; height: 15px;" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
          SOT Mailbox Destination Architecture (Section 11)
        </h3>
        <p style="margin: 0 0 10px; font-size: 12.5px; color: #0C4A6E; line-height: 1.5;">
          The project specification designates 5 official mailboxes on the registered corporate domain:
        </p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 8px; font-size: 12px;">
          <div style="background: #FFFFFF; padding: 8px 12px; border-radius: var(--admin-radius-sm); border: 1px solid #E0F2FE;"><strong>info@:</strong> General notices &amp; system sender</div>
          <div style="background: #FFFFFF; padding: 8px 12px; border-radius: var(--admin-radius-sm); border: 1px solid #E0F2FE;"><strong>sales@:</strong> Holiday packages &amp; flight requests</div>
          <div style="background: #FFFFFF; padding: 8px 12px; border-radius: var(--admin-radius-sm); border: 1px solid #E0F2FE;"><strong>visa@:</strong> Visa, PR, Study Abroad, &amp; CBI leads</div>
          <div style="background: #FFFFFF; padding: 8px 12px; border-radius: var(--admin-radius-sm); border: 1px solid #E0F2FE;"><strong>admin@:</strong> System alerts &amp; bank reconciliations</div>
          <div style="background: #FFFFFF; padding: 8px 12px; border-radius: var(--admin-radius-sm); border: 1px solid #E0F2FE;"><strong>lanre@:</strong> Executive escalations inbox</div>
        </div>
      </div>

      <form method="POST" action="/admin/settings">
        <?= Security::csrfField() ?>
        <input type="hidden" name="section" value="email">

        <div class="admin-form-row">
          <div class="admin-form-group">
            <label class="admin-form-label required">Default Outgoing Sender Name</label>
            <input type="text" name="mail_from_name" value="<?= Security::e($emailRouting['from_name']) ?>" required class="admin-form-control">
            <span class="admin-form-hint">e.g. LAD Global Travels and Tours</span>
          </div>

          <div class="admin-form-group">
            <label class="admin-form-label required">Default Outgoing Sender Email (info@)</label>
            <input type="email" name="mail_from_address" value="<?= Security::e($emailRouting['from_address']) ?>" required class="admin-form-control">
            <span class="admin-form-hint">Receipts &amp; customer emails are delivered from this address.</span>
          </div>

          <div class="admin-form-group">
            <label class="admin-form-label required">General &amp; Tours Destination (sales@)</label>
            <input type="email" name="notification_email_enquiries" value="<?= Security::e($emailRouting['enquiries']) ?>" required class="admin-form-control">
            <span class="admin-form-hint">Receives new tour package bookings and general consultations.</span>
          </div>

          <div class="admin-form-group">
            <label class="admin-form-label required">Visa &amp; Immigration Destination (visa@)</label>
            <input type="email" name="notification_email_visa" value="<?= Security::e($emailRouting['visa']) ?>" required class="admin-form-control">
            <span class="admin-form-hint">Receives study abroad, visa, Canada PR, and CBI inquiries.</span>
          </div>

          <div class="admin-form-group">
            <label class="admin-form-label required">Payments &amp; Finance Destination (admin@)</label>
            <input type="email" name="notification_email_payments" value="<?= Security::e($emailRouting['payments']) ?>" required class="admin-form-control">
            <span class="admin-form-hint">Receives verified payment alerts and bank reconciliation notices.</span>
          </div>

          <div class="admin-form-group">
            <label class="admin-form-label required">Executive Management Destination (lanre@)</label>
            <input type="email" name="notification_email_management" value="<?= Security::e($emailRouting['management']) ?>" required class="admin-form-control">
            <span class="admin-form-hint">Principal executive escalation mailbox.</span>
          </div>
        </div>

        <button type="submit" class="admin-btn admin-btn-primary" style="font-weight: 700;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
          Save Email Routing
        </button>
      </form>
    </div>
  </div>
</div>

<!-- Tab 4: Protected Bank Details -->
<div class="settings-tab-content" id="tab-content-bank" style="display: none;">
  <div class="admin-card">
    <div class="admin-card-header">
      <div>
        <h2 class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
          Corporate Bank Account Details (Protected)
        </h2>
        <p class="admin-card-subtitle">Official account presented to clients during manual bank transfer checkout.</p>
      </div>
    </div>
    <div class="admin-card-body">
      <!-- Security Warning Banner -->
      <div style="background-color: var(--admin-warning-bg); border: 1px solid var(--admin-warning-border); border-left: 4px solid var(--admin-warning); border-radius: var(--admin-radius-md); padding: 16px 20px; margin-bottom: 24px;">
        <h3 style="margin: 0 0 6px; font-size: 13.5px; color: #92400E; font-weight: 700; display: flex; align-items: center; gap: 6px;">
          <svg style="width: 15px; height: 15px;" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
          Critical Financial Security Notice (Decision D04)
        </h3>
        <p style="margin: 0 0 8px; font-size: 12.5px; color: #78350F; line-height: 1.5;">
          These corporate bank details are displayed directly to clients on the booking checkout page when selecting <strong>Manual Bank Transfer</strong>. 
          Only enter verified accounts owned and authorized by <strong>LAD Global Travels and Tours</strong>. 
          Any modification writes a permanent record to the security audit trail.
        </p>
        <div style="font-size: 12px; color: #B45309;">
          <strong>Current Status:</strong> <?= $bank['is_verified'] ? '<span class="admin-badge admin-badge-success" style="font-size: 10.5px;">Client Verified &amp; Authorized</span>' : '<span class="admin-badge admin-badge-warning" style="font-size: 10.5px;">Pending Verification</span>' ?> | 
          <strong>Last Updated:</strong> <?= Security::e($bank['verified_at']) ?>
        </div>
      </div>

      <form method="POST" action="/admin/settings">
        <?= Security::csrfField() ?>
        <input type="hidden" name="section" value="bank">
        <input type="hidden" name="payment_method_bank_toggle_submitted" value="1">

        <!-- Checkout Availability Toggle -->
        <div style="background-color: #F8FAFC; border: 1.5px solid <?= !empty($paymentMethods['bank']) ? '#BBF7D0' : '#E2E8F0' ?>; border-radius: var(--admin-radius-md); padding: 14px 18px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
          <div>
            <div style="display: flex; align-items: center; gap: 8px;">
              <strong style="color: var(--admin-primary); font-size: 13.5px;">Checkout Availability:</strong>
              <?php if (!empty($paymentMethods['bank'])): ?>
                <span class="admin-badge admin-badge-success" style="font-size: 10.5px;">Active on Checkout</span>
              <?php else: ?>
                <span class="admin-badge admin-badge-warning" style="font-size: 10.5px;">Disabled (Hidden)</span>
              <?php endif; ?>
            </div>
            <p style="margin: 3px 0 0; font-size: 12px; color: #64748B;">When enabled, customers can select Direct Corporate Bank Transfer on tour package booking checkout.</p>
          </div>
          <label class="admin-checkbox-label" style="margin: 0; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" name="payment_method_bank_enabled" value="1" <?= !empty($paymentMethods['bank']) ? 'checked' : '' ?> style="width: 18px; height: 18px; cursor: pointer;">
            <span>Enable Bank Transfer for Bookings</span>
          </label>
        </div>

        <div class="admin-form-row">
          <div class="admin-form-group">
            <label class="admin-form-label required">Bank Name</label>
            <input type="text" name="bank_name" value="<?= Security::e($bank['bank_name']) ?>" required class="admin-form-control">
            <span class="admin-form-hint">e.g. Zenith Bank PLC</span>
          </div>

          <div class="admin-form-group">
            <label class="admin-form-label required">Account Name</label>
            <input type="text" name="account_name" value="<?= Security::e($bank['account_name']) ?>" required class="admin-form-control">
            <span class="admin-form-hint">Must match official company bank registration.</span>
          </div>

          <div class="admin-form-group">
            <label class="admin-form-label required">Account Number (NUBAN)</label>
            <input type="text" name="account_number" value="<?= Security::e($bank['account_number']) ?>" required pattern="[0-9]{10,}" maxlength="10" class="admin-form-control" style="font-family: monospace; font-weight: 700; letter-spacing: 1px; color: var(--admin-primary);">
            <span class="admin-form-hint">10-digit Nigerian NUBAN account number.</span>
          </div>

          <div class="admin-form-group">
            <label class="admin-form-label">Sort Code / Branch (Optional)</label>
            <input type="text" name="sort_code" value="<?= Security::e($bank['sort_code']) ?>" class="admin-form-control">
            <span class="admin-form-hint">e.g. Ikorodu Main Branch</span>
          </div>
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">Customer Transfer Narration Instructions</label>
          <textarea name="instructions" rows="2" class="admin-form-control"><?= Security::e($bank['instructions']) ?></textarea>
          <span class="admin-form-hint">Appears on customer transfer instructions screen below bank details.</span>
        </div>

        <!-- Mandatory Certification Checkbox -->
        <div style="background-color: #F8FAFC; border: 1px solid var(--admin-border); border-radius: var(--admin-radius-md); padding: 16px; margin-bottom: 24px;">
          <label class="admin-checkbox-label">
            <input type="checkbox" name="client_confirmed" value="1" required>
            <span><strong>I certify under audit review</strong> that these corporate bank account details have been formally verified with and authorized by the company owner (Decision D04). I understand that all changes are permanently logged to the security audit trail.</span>
          </label>
        </div>

        <button type="submit" class="admin-btn admin-btn-danger" style="font-weight: 700;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
          Update Verified Bank Account
        </button>
      </form>
    </div>
  </div>
</div>

<!-- Tab 5: Paystack Payment Gateway Configuration -->
<div class="settings-tab-content" id="tab-content-paystack" style="display: none;">
  <div class="admin-card">
    <div class="admin-card-header">
      <div>
        <h2 class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
          Paystack Payment Gateway Integration
        </h2>
        <p class="admin-card-subtitle">Manage online digital card payments, webhook endpoints, and API credentials for Paystack settlement.</p>
      </div>
      <div>
        <?php if (!empty($paystack['is_live'])): ?>
          <span class="admin-badge admin-badge-success" style="font-weight: 700; padding: 4px 10px;">
            <span class="admin-status-dot"></span> Live Production
          </span>
        <?php elseif (!empty($paystack['is_test']) && empty($paystack['is_sandbox'])): ?>
          <span class="admin-badge admin-badge-info" style="font-weight: 700; padding: 4px 10px;">
            <span class="admin-status-dot"></span> Paystack Test Mode
          </span>
        <?php else: ?>
          <span class="admin-badge admin-badge-warning" style="font-weight: 700; padding: 4px 10px;">
            Developer Sandbox Active
          </span>
        <?php endif; ?>
      </div>
    </div>
    <div class="admin-card-body">
      
      <!-- Informational Status Banner -->
      <div style="background-color: #F0FDF4; border: 1px solid #BBF7D0; border-radius: var(--admin-radius-md); padding: 16px; margin-bottom: 24px; display: flex; gap: 12px; align-items: flex-start;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16A34A" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
        <div style="font-size: 13.5px; color: #15803D; line-height: 1.5;">
          <strong>Automatic Test &amp; Live Separation:</strong> When keys start with <code>pk_test_</code> and <code>sk_test_</code>, transactions run in Paystack Sandbox mode with zero real money charged. When switched to <code>pk_live_</code> and <code>sk_live_</code>, the gateway automatically switches to Live Production mode.
        </div>
      </div>

      <form method="POST" action="/admin/settings">
        <?= Security::csrfField() ?>
        <input type="hidden" name="section" value="paystack">
        <input type="hidden" name="payment_method_paystack_toggle_submitted" value="1">

        <!-- Checkout Availability Toggle -->
        <div style="background-color: #F8FAFC; border: 1.5px solid <?= !empty($paymentMethods['paystack']) ? '#BBF7D0' : '#E2E8F0' ?>; border-radius: var(--admin-radius-md); padding: 14px 18px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
          <div>
            <div style="display: flex; align-items: center; gap: 8px;">
              <strong style="color: var(--admin-primary); font-size: 13.5px;">Checkout Availability:</strong>
              <?php if (!empty($paymentMethods['paystack'])): ?>
                <span class="admin-badge admin-badge-success" style="font-size: 10.5px;">Active on Checkout</span>
              <?php else: ?>
                <span class="admin-badge admin-badge-warning" style="font-size: 10.5px;">Disabled (Hidden)</span>
              <?php endif; ?>
            </div>
            <p style="margin: 3px 0 0; font-size: 12px; color: #64748B;">When enabled, customers can select Paystack (Card, USSD, Transfer) on tour package booking checkout.</p>
          </div>
          <label class="admin-checkbox-label" style="margin: 0; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" name="payment_method_paystack_enabled" value="1" <?= !empty($paymentMethods['paystack']) ? 'checked' : '' ?> style="width: 18px; height: 18px; cursor: pointer;">
            <span>Enable Paystack for Bookings</span>
          </label>
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">Paystack Public Key</label>
          <input type="text" name="paystack_public_key" value="<?= Security::e($paystack['public_key'] ?? '') ?>" class="admin-form-control" placeholder="pk_test_... or pk_live_...">
          <span class="admin-form-hint">Used for client-side checkout initialization and frontend verification.</span>
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">Paystack Secret Key</label>
          <input type="password" name="paystack_secret_key" value="<?= Security::e($paystack['secret_key'] ?? '') ?>" class="admin-form-control" placeholder="sk_test_... or sk_live_...">
          <span class="admin-form-hint">Stored strictly in local application environment for server-to-server transaction verification and webhook HMAC authentication.</span>
        </div>

        <div class="admin-form-group" style="max-width: 220px;">
          <label class="admin-form-label">Gateway Currency</label>
          <input type="text" name="paystack_currency" value="<?= Security::e($paystack['currency'] ?? 'NGN') ?>" class="admin-form-control" readonly>
          <span class="admin-form-hint">Default currency subunit is Kobo (1 NGN = 100 kobo).</span>
        </div>

        <!-- Webhook and Callback URLs -->
        <div style="background-color: #F8FAFC; border: 1px solid var(--admin-border); border-radius: var(--admin-radius-md); padding: 18px; margin-bottom: 24px;">
          <h4 style="font-size: 13px; font-weight: 700; color: var(--admin-primary); margin: 0 0 12px; text-transform: uppercase;">Paystack Dashboard Configuration</h4>
          
          <div style="margin-bottom: 12px;">
            <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px;">Webhook URL (Paste into Paystack Dashboard &rarr; Settings &rarr; Preferences)</label>
            <div style="display: flex; gap: 8px;">
              <input type="text" readonly value="<?= Security::e($paystack['webhook_url'] ?? '') ?>" id="inputWebhookUrl" class="admin-form-control" style="background: #FFFFFF; font-family: monospace; font-size: 12px;">
              <button type="button" class="admin-btn admin-btn-outline admin-btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('inputWebhookUrl').value); alert('Webhook URL copied to clipboard!');">Copy</button>
            </div>
          </div>

          <div>
            <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px;">Callback / Return URL</label>
            <div style="display: flex; gap: 8px;">
              <input type="text" readonly value="<?= Security::e($paystack['callback_url'] ?? '') ?>" id="inputCallbackUrl" class="admin-form-control" style="background: #FFFFFF; font-family: monospace; font-size: 12px;">
              <button type="button" class="admin-btn admin-btn-outline admin-btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('inputCallbackUrl').value); alert('Callback URL copied to clipboard!');">Copy</button>
            </div>
          </div>
        </div>

        <button type="submit" class="admin-btn admin-btn-primary" style="font-weight: 700;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
          Save Paystack Settings
        </button>
      </form>

    </div>
  </div>
</div>

<!-- Tab 6: Payment Methods Configuration -->
<div class="settings-tab-content" id="tab-content-payment_methods" style="display: none;">
  <div class="admin-card">
    <div class="admin-card-header">
      <div>
        <h2 class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path><line x1="12" y1="6" x2="12" y2="8"></line><line x1="12" y1="16" x2="12" y2="18"></line></svg>
          Customer Checkout Payment Methods
        </h2>
        <p class="admin-card-subtitle">Enable or disable checkout channels available to customers when booking tour packages.</p>
      </div>
      <div>
        <?php if (!empty($paymentMethods['bank']) && !empty($paymentMethods['paystack'])): ?>
          <span class="admin-badge admin-badge-success" style="font-weight: 700; padding: 4px 10px;">
            <span class="admin-status-dot"></span> All Channels Active (2/2)
          </span>
        <?php elseif (!empty($paymentMethods['bank']) || !empty($paymentMethods['paystack'])): ?>
          <span class="admin-badge admin-badge-info" style="font-weight: 700; padding: 4px 10px;">
            <span class="admin-status-dot"></span> Single Channel Active (1/2)
          </span>
        <?php else: ?>
          <span class="admin-badge admin-badge-danger" style="font-weight: 700; padding: 4px 10px;">
            All Checkout Methods Disabled (0/2)
          </span>
        <?php endif; ?>
      </div>
    </div>
    <div class="admin-card-body">
      
      <!-- Informational Overview -->
      <div style="background-color: #F8FAFC; border: 1px solid var(--admin-border); border-radius: var(--admin-radius-md); padding: 16px; margin-bottom: 24px; font-size: 13px; color: #475569; line-height: 1.6;">
        Use the switches below to control which payment methods are accepted on the public package checkout form. Disabling a method immediately hides it from the customer checkout interface and prevents checkout submission for that method.
      </div>

      <form method="POST" action="/admin/settings">
        <?= Security::csrfField() ?>
        <input type="hidden" name="section" value="payment_methods">

        <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 28px;">
          
          <!-- Card 1: Direct Corporate Bank Transfer -->
          <div style="border: 2px solid <?= !empty($paymentMethods['bank']) ? 'var(--admin-primary)' : 'var(--admin-border)' ?>; background: <?= !empty($paymentMethods['bank']) ? '#F8FAFC' : '#FFFFFF' ?>; border-radius: var(--admin-radius-md); padding: 20px; transition: border-color 0.2s ease;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
              <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 38px; height: 38px; border-radius: 8px; background: #E0F2FE; color: #0284C7; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                </div>
                <div>
                  <h3 style="margin: 0; font-size: 15px; color: var(--admin-primary); font-weight: 700;">Direct Corporate Bank Transfer</h3>
                  <span style="font-size: 12px; color: #64748B;">Zenith Bank PLC &bull; Account: <?= Security::e($bank['account_number']) ?> &bull; <?= Security::e($bank['account_name']) ?></span>
                </div>
              </div>

              <div>
                <?php if (!empty($paymentMethods['bank'])): ?>
                  <span class="admin-badge admin-badge-success" style="font-weight: 700; font-size: 11px;">
                    <span class="admin-status-dot"></span> Enabled on Checkout
                  </span>
                <?php else: ?>
                  <span class="admin-badge admin-badge-warning" style="font-weight: 700; font-size: 11px;">
                    Disabled (Hidden from Customers)
                  </span>
                <?php endif; ?>
              </div>
            </div>

            <p style="margin: 0 0 16px; font-size: 12.5px; color: #475569; line-height: 1.5;">
              Allows customers to book online and transfer directly to your verified Zenith Bank corporate account. An official payment reference (BK-XXXX) and instructions are issued automatically.
            </p>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 14px; border-top: 1px solid var(--admin-border); flex-wrap: wrap; gap: 10px;">
              <label class="admin-checkbox-label" style="margin: 0; font-weight: 700; cursor: pointer; font-size: 13.5px;">
                <input type="checkbox" name="payment_method_bank_enabled" value="1" <?= !empty($paymentMethods['bank']) ? 'checked' : '' ?> style="width: 18px; height: 18px; cursor: pointer;">
                <span>Accept Direct Bank Transfers</span>
              </label>
              <button type="button" class="admin-btn admin-btn-outline admin-btn-sm" onclick="switchSettingsTab('bank')">
                Edit Bank Details &rarr;
              </button>
            </div>
          </div>

          <!-- Card 2: Paystack Online Payment Gateway -->
          <div style="border: 2px solid <?= !empty($paymentMethods['paystack']) ? 'var(--admin-primary)' : 'var(--admin-border)' ?>; background: <?= !empty($paymentMethods['paystack']) ? '#F8FAFC' : '#FFFFFF' ?>; border-radius: var(--admin-radius-md); padding: 20px; transition: border-color 0.2s ease;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
              <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 38px; height: 38px; border-radius: 8px; background: #DCFCE7; color: #16A34A; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                </div>
                <div>
                  <h3 style="margin: 0; font-size: 15px; color: var(--admin-primary); font-weight: 700;">Paystack Online Gateway</h3>
                  <span style="font-size: 12px; color: #64748B;">Card (Visa / Mastercard / Verve), USSD, and Instant Paystack Bank Settlement</span>
                </div>
              </div>

              <div>
                <?php if (!empty($paymentMethods['paystack'])): ?>
                  <span class="admin-badge admin-badge-success" style="font-weight: 700; font-size: 11px;">
                    <span class="admin-status-dot"></span> Enabled on Checkout
                  </span>
                <?php else: ?>
                  <span class="admin-badge admin-badge-warning" style="font-weight: 700; font-size: 11px;">
                    Disabled (Hidden from Customers)
                  </span>
                <?php endif; ?>
              </div>
            </div>

            <p style="margin: 0 0 16px; font-size: 12.5px; color: #475569; line-height: 1.5;">
              Allows customers to pay instantly with bank cards or USSD. Payments are verified automatically and official receipts issued without manual audit requirement.
            </p>

            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 14px; border-top: 1px solid var(--admin-border); flex-wrap: wrap; gap: 10px;">
              <label class="admin-checkbox-label" style="margin: 0; font-weight: 700; cursor: pointer; font-size: 13.5px;">
                <input type="checkbox" name="payment_method_paystack_enabled" value="1" <?= !empty($paymentMethods['paystack']) ? 'checked' : '' ?> style="width: 18px; height: 18px; cursor: pointer;">
                <span>Accept Paystack Online Payments</span>
              </label>
              <button type="button" class="admin-btn admin-btn-outline admin-btn-sm" onclick="switchSettingsTab('paystack')">
                Manage Paystack API Keys &rarr;
              </button>
            </div>
          </div>

        </div>

        <?php if (empty($paymentMethods['bank']) && empty($paymentMethods['paystack'])): ?>
          <div style="background-color: #FEF2F2; border: 1.5px solid #FCA5A5; border-radius: var(--admin-radius-md); padding: 16px; margin-bottom: 24px; color: #991B1B; font-size: 13px; line-height: 1.5;">
            <strong>Notice:</strong> All checkout payment methods are currently disabled. Customers visiting package booking forms will be shown an advisory to contact your travel consultants directly via WhatsApp or phone to reserve their tour package.
          </div>
        <?php endif; ?>

        <button type="submit" class="admin-btn admin-btn-primary" style="font-weight: 700;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
          Save Payment Method Settings
        </button>
      </form>

    </div>
  </div>
</div>

<script>
function switchSettingsTab(tabKey) {
  document.querySelectorAll('.settings-tab-content').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.settings-tab-btn').forEach(btn => {
    btn.classList.remove('active');
  });

  const contentEl = document.getElementById('tab-content-' + tabKey);
  const btnEl = document.getElementById('tab-btn-' + tabKey);

  if (contentEl) contentEl.style.display = 'block';
  if (btnEl) btnEl.classList.add('active');

  window.location.hash = tabKey;
}

// Auto-select tab if specified in URL hash
window.addEventListener('DOMContentLoaded', () => {
  const hash = window.location.hash.replace('#', '');
  if (hash && document.getElementById('tab-content-' + hash)) {
    switchSettingsTab(hash);
  }
});
</script>
