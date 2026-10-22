# ==============================================================================
# LAD GLOBAL TRAVELS AND TOURS — PRODUCTION DEPLOYMENT RUNBOOK
# cPanel / Apache / PHP 8.2+ / MySQL Production Deployment Checklist & Procedures
# ==============================================================================

This runbook specifies the step-by-step procedures for deploying LAD Global Travels and Tours to a production cPanel / Linux hosting environment running Apache, PHP 8.2+, and MySQL 8+ / MariaDB 10.4+.

---

## 1. Pre-Deployment Environment Verification

Before deploying files, verify the server environment satisfies all core requirements:

| Component | Minimum Specification | Recommended Production Setting | Verification Method |
| :--- | :--- | :--- | :--- |
| **Operating System** | Linux (CloudLinux, AlmaLinux, Ubuntu) | CloudLinux / cPanel | cPanel Server Information |
| **Web Server** | Apache 2.4+ | Apache with `mod_rewrite`, `mod_headers` | `httpd -v` / `.htaccess` support |
| **PHP Version** | PHP 8.2.0 or higher | PHP 8.2 / 8.3 native | `php -v` (cPanel Select PHP Version) |
| **PHP Extensions** | `pdo_mysql`, `mbstring`, `fileinfo`, `curl`, `openssl`, `json`, `session` | All enabled | `php -m` / cPanel PHP Extensions |
| **Database Engine** | MySQL 8.0+ or MariaDB 10.4+ | MySQL 8.0+ (InnoDB, `utf8mb4`) | `mysql -V` / phpMyAdmin |
| **SSL / TLS Certificate** | Valid SSL (Let's Encrypt / Sectigo) | AutoSSL active on domain | Check `https://` in browser |

---

## 2. Directory Architecture & Webroot Security

To guarantee that private application source code, configuration files, and secrets are strictly inaccessible to the public internet, deploy using one of the following two structural models:

### Model A: Document Root Points to `public/` (Recommended)
If your cPanel hosting allows custom Document Root settings for the primary domain or add-on domain (e.g., via *cPanel > Domains > Document Root*):
```text
/home/username/lad_travels/
├── app/               (Private: Controllers, Models, Services, Core, Views)
├── bin/               (Private: CLI migration tools)
├── database/          (Private: Migrations & SQL schemas)
├── storage/           (Private: Logs, sessions, backups)
├── tests/             (Private: Automated test suites)
├── .env               (CRITICAL: Environment secrets, chmod 600)
└── public/            <--- Set Document Root HERE (/home/username/public_html or domain root)
    ├── index.php      (Public Front Controller)
    ├── .htaccess      (Public URL rewrite & security headers)
    ├── robots.txt     (Public crawler rules)
    ├── assets/        (CSS, JS, branding)
    └── uploads/       (User media, scripts blocked via .htaccess)
```

### Model B: Standard cPanel Fallback (When Document Root is Fixed to `public_html`)
If your cPanel hosting strictly fixes the primary domain document root to `public_html`:
1. Upload the private application folders to the user home directory: `/home/username/lad_travels_app/`
2. Upload the contents of the `public/` directory into `/home/username/public_html/`
3. Edit `/home/username/public_html/index.php` line 11:
   ```php
   // Update path to private application bootstrap
   require_once '/home/username/lad_travels_app/app/bootstrap.php';
   ```
4. Verify that `.env` is located at `/home/username/lad_travels_app/.env` and is **NEVER** placed inside `public_html/`.

---

## 3. Filesystem Permissions Matrix

Execute the following permissions via cPanel File Manager or SSH terminal:

```bash
# Directories: 755 (rwxr-xr-x)
find /home/username/lad_travels -type d -exec chmod 755 {} \;

# PHP Source & Asset Files: 644 (rw-r--r--)
find /home/username/lad_travels -type f -exec chmod 644 {} \;

# Private Environment Configuration: 600 (rw-------)
chmod 600 /home/username/lad_travels/.env

# Writable Storage & Log Directories: 775 or 755
chmod -R 775 /home/username/lad_travels/storage/logs
chmod -R 775 /home/username/lad_travels/storage/temp
chmod -R 775 /home/username/lad_travels/public/uploads

# Verify Uploads Execution Block (.htaccess): 644
chmod 644 /home/username/lad_travels/public/uploads/.htaccess
```

---

## 4. Environment Configuration (`.env`)

Create the production `.env` file in the application root using [`/.env.example`](file:///c:/Users/DELL/Desktop/MESH%20PROJECT/LAD%20Global%20Travels%20and%20Tours/.env.example) as template:

```ini
# ==============================================================================
# LAD GLOBAL TRAVELS AND TOURS — PRODUCTION CONFIGURATION
# ==============================================================================

# Application Environment
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ladglobaltravels.com
APP_SECRET=generate_a_secure_64_character_hex_key_here

# Database Configuration (cPanel MySQL Database)
DB_HOST=localhost
DB_NAME=cpaneluser_lad_travels
DB_USER=cpaneluser_lad_user
DB_PASSWORD=StrongSecureRandomPassword123!
DB_CHARSET=utf8mb4

# Outbound Transactional Email (SMTP)
SMTP_HOST=mail.ladglobaltravels.com
SMTP_PORT=465
SMTP_USER=info@ladglobaltravels.com
SMTP_PASSWORD=StrongMailboxPassword456!
SMTP_CRYPTO=ssl
MAIL_FROM_ADDRESS=info@ladglobaltravels.com
MAIL_FROM_NAME="LAD Global Travels and Tours"

# SOT Departmental Mailbox Routing
NOTIFICATION_EMAIL_ENQUIRIES=sales@ladglobaltravels.com
NOTIFICATION_EMAIL_VISA=visa@ladglobaltravels.com
NOTIFICATION_EMAIL_PAYMENTS=admin@ladglobaltravels.com
NOTIFICATION_EMAIL_MANAGEMENT=lanre@ladglobaltravels.com

# Paystack Payment Gateway
PAYSTACK_PUBLIC_KEY=pk_live_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
PAYSTACK_SECRET_KEY=sk_live_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
PAYSTACK_TEST_MODE=false
```

---

## 5. Database Provisioning & Migration Execution

### Step 5.1: Create MySQL Database in cPanel
1. Open *cPanel > MySQL Databases*.
2. Create database: `cpaneluser_lad_travels`.
3. Create user: `cpaneluser_lad_user` with a strong password.
4. Add user to database with **ALL PRIVILEGES**.

### Step 5.2: Execute Migrations
Run the database migrations sequentially via CLI migration tool or phpMyAdmin SQL tab:

**Using CLI Runner (Recommended):**
```bash
php database/migrate.php
```

**Manual Import via phpMyAdmin (Execute in order):**
1. `database/migrations/001_create_admins_table.sql` (Creates `admins` table)
2. `database/migrations/002_create_packages_and_images_tables.sql` (Creates `packages` and `package_images` tables)
3. `database/migrations/003_create_enquiries_and_notes_tables.sql` (Creates `enquiries` and `enquiry_notes` CRM tables)
4. `database/migrations/004_create_bookings_and_payments_tables.sql` (Creates `bookings` and `payments` tables)
5. `database/migrations/005_create_settings_and_audit_tables.sql` (Creates `settings`, `audit_logs`, and `payment_events` tables)

### Step 5.3: Provision the Initial Administrator
Run the secure CLI provisioning tool via SSH:
```bash
php bin/create-admin.php admin@ladglobaltravels.com "StrongAdminPassword789!" "Super Administrator"
```
*Note: If SSH is unavailable, execute an authorized one-time provisioning script that hashes the password with `PASSWORD_BCRYPT` (cost 12) and immediately delete the script.*

---

## 6. Paystack Live vs. Test Separation & Gateway URLs

### Live Gateway Registration:
1. Log in to the [Paystack Dashboard](https://dashboard.paystack.com/) with client-authorized credentials.
2. Navigate to *Settings > API Keys & Webhooks*.
3. Copy the **Live Secret Key** (`sk_live_...`) and **Live Public Key** (`pk_live_...`) into `.env`.
4. Configure the official URLs:
   - **Live Webhook URL:** `https://ladglobaltravels.com/paystack/webhook`
   - **Live Callback URL:** `https://ladglobaltravels.com/paystack/callback`
5. Test Webhook: Send a test ping from Paystack Dashboard and verify that `storage/logs/payment.log` records the event.

### Controlled Authorized Live Payment Verification:
- **Mandatory Guardrail:** Do **NOT** perform live financial transactions without explicit client presence and written authorization.
- When authorized, perform a single low-value live test (e.g. ₦100) using a real debit card.
- Verify:
  1. Amount debited is exactly ₦100.
  2. Paystack dashboard reflects the transaction in *Transactions*.
  3. Booking record in admin updates to `paid`.
  4. Customer receipt view displays accurately.
  5. Payment event idempotency records the transaction ID.

---

## 7. Production Post-Deployment Verification Checklist

Follow this checklist immediately after pushing files live:

- [ ] **HTTPS Enforced:** Visiting `http://ladglobaltravels.com` automatically redirects to `https://`.
- [ ] **Security Headers:** Browser inspect shows `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, and CSP.
- [ ] **Webroot Isolation:** Direct URL access to `https://ladglobaltravels.com/.env` returns **403 Forbidden** or **404 Not Found**.
- [ ] **Upload Execution Block:** Direct access to `https://ladglobaltravels.com/uploads/test.php` returns **403 Forbidden**.
- [ ] **Homepage & Public Pages:** Navigate to `/`, `/about`, `/services`, `/packages`, `/faq`, `/contact`.
- [ ] **Robots & Sitemap:** `https://ladglobaltravels.com/robots.txt` and `https://ladglobaltravels.com/sitemap.xml` resolve.
- [ ] **WhatsApp Float:** Bottom right floating button opens `https://wa.me/2347017597650` with pre-filled text.
- [ ] **Enquiry Form Submission:** Submit a live test enquiry; verify success alert and admin CRM persistence.
- [ ] **Admin Authentication:** Access `/admin/login`, log in with initial admin credentials, verify dashboard metrics.
- [ ] **Bank Reconciliation:** Review manual bank transfer settings in `/admin/settings`.
- [ ] **Automated Backups:** Verify daily cPanel backup cron job is scheduled.
