# ==============================================================================
# LAD GLOBAL TRAVELS AND TOURS — ROLLBACK AND DISASTER RECOVERY GUIDE
# Emergency Rollback Procedures, Snapshot Restoration & Recovery Runbook
# ==============================================================================

This runbook establishes rapid emergency recovery procedures for LAD Global Travels and Tours in the event of a critical deployment failure, database corruption, or server outage.

---

## 1. Emergency Severity Classifications

| Severity | Definition | Target Recovery Time (RTO) | Action |
| :--- | :--- | :--- | :--- |
| **SEV-1 (Critical)** | Production site down (500 Error, database unreachable, Paystack webhooks failing). | **< 30 Minutes** | Initiate Immediate Rollback or Maintenance Mode. |
| **SEV-2 (Major)** | Core feature degraded (e.g. package booking form error, file upload failure). | **< 2 Hours** | Apply Hotfix or Revert specific component. |
| **SEV-3 (Minor)** | Cosmetic defect, typo, or non-blocking admin styling issue. | **Next Scheduled Deploy** | Log in defect tracker; no emergency rollback needed. |

---

## 2. Emergency Maintenance Mode

If the site is throwing fatal errors while repairs are underway, enable the clean static maintenance page to protect brand reputation and notify visitors:

### Enable Maintenance Mode:
1. In `public_html/` or `public/`, create a file named `.maintenance`:
   ```bash
   touch /home/username/public_html/.maintenance
   ```
2. In `public/.htaccess`, activate the maintenance redirect rule by adding at the top of `mod_rewrite`:
   ```apache
   # Emergency Maintenance Redirect
   RewriteCond %{DOCUMENT_ROOT}/.maintenance -f
   RewriteCond %{REMOTE_ADDR} !^127\.0\.0\.1$
   RewriteCond %{REQUEST_URI} !^/maintenance\.html$
   RewriteCond %{REQUEST_URI} !^/assets/
   RewriteRule ^ /maintenance.html [R=503,L]
   ```
3. Create `public/maintenance.html` displaying company branding, phone number (`+234 701 759 7650`), and WhatsApp link (`https://wa.me/2347017597650`).

### Disable Maintenance Mode:
Remove the `.maintenance` sentinel file:
```bash
rm -f /home/username/public_html/.maintenance
```

---

## 3. Fast Codebase Rollback Procedure

If a new code deployment introduces breaking changes:

### Option A: Git Release Tag Reversion (Recommended if using Git on server)
```bash
cd /home/username/lad_travels

# 1. Fetch current status and discard uncommitted hotfix edits
git status
git reset --hard HEAD

# 2. Revert to the previous stable release tag or commit SHA
git checkout tags/v1.0.0-stable

# 3. Verify files and permissions
chmod 600 .env
chmod -R 775 storage/logs public/uploads

# 4. Clear opcode cache (restart PHP-FPM or touch index.php)
touch public/index.php
```

### Option B: Archive Extraction Rollback
If deployment was performed via zip archive:
```bash
# 1. Move failed release to quarantine directory
mv /home/username/lad_travels /home/username/lad_travels_failed_$(date +%Y%m%d_%H%M%S)

# 2. Extract pre-deployment backup archive
tar -xzf /home/username/backups/lad_travels_pre_deploy.tar.gz -C /home/username/

# 3. Verify .env permissions
chmod 600 /home/username/lad_travels/.env
chmod -R 775 /home/username/lad_travels/storage/logs
```

---

## 4. Database Rollback Procedure

If a database migration failed or caused data corruption:

### Step 1: Pre-Rollback Quarantine Dump
Always capture a snapshot of the corrupted state for forensic analysis before overwriting:
```bash
mysqldump --user=cpaneluser_lad_user --password=DBPassword123! cpaneluser_lad_travels > /home/username/backups/corrupted_state_$(date +%Y%m%d_%H%M%S).sql
```

### Step 2: Restore Last Known Good Snapshot
Restore the pre-deployment database dump captured prior to the migration:
```bash
# Uncompress and import SQL snapshot
gunzip < /home/username/backups/lad_travels_pre_migration.sql.gz | mysql --user=cpaneluser_lad_user --password=DBPassword123! cpaneluser_lad_travels
```

### Step 3: Run Post-Rollback Integrity Sanity Checks
Verify table counts and recent transactions are intact:
```sql
USE cpaneluser_lad_travels;

-- Verify critical table existence
SHOW TABLES;

-- Check record integrity
SELECT 'admins' AS tbl, COUNT(*) FROM admins
UNION ALL
SELECT 'packages', COUNT(*) FROM packages
UNION ALL
SELECT 'enquiries', COUNT(*) FROM enquiries
UNION ALL
SELECT 'bookings', COUNT(*) FROM bookings
UNION ALL
SELECT 'payments', COUNT(*) FROM payments;

-- Verify latest payments status
SELECT id, payment_reference, amount, status FROM payments ORDER BY id DESC LIMIT 5;
```

---

## 5. Media & Uploads Rollback

If uploaded package images were deleted or corrupted:
```bash
# Restore uploads directory from daily backup tarball
tar -xzf /home/username/backups/uploads_20261020.tar.gz -C /home/username/lad_travels/public/

# Ensure directory execution block is preserved
chmod 644 /home/username/lad_travels/public/uploads/.htaccess
chmod -R 775 /home/username/lad_travels/public/uploads
```

---

## 6. Post-Rollback Health & Sanity Verification

Execute these smoke checks immediately following any rollback before declaring the incident resolved:

1. **HTTP Status:** Run `curl -I https://ladglobaltravels.com` — must return **HTTP 200 OK**.
2. **Error Logs:** Check `/home/username/lad_travels/storage/logs/app.log` — verify no uncaught fatal exceptions are appending.
3. **Admin Sign-in:** Log in to `/admin` — verify dashboard metrics load correctly.
4. **Public Tour Packages:** Browse `/packages` — verify tour images and prices load.
5. **Contact / Enquiry Submission:** Submit a test enquiry to verify form processing and database persistence.
6. **WhatsApp Handshake:** Click the floating WhatsApp button to confirm connection.
