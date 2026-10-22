# LAD Global Travels and Tours — Web Application

> **Version:** 1.0 (Phase P02 Foundation)  
> **Technology Stack:** Pure/Native PHP 8.2+, MySQL 8+ / MariaDB (PDO), Vanilla HTML5, CSS3, JavaScript.  
> **Frameworks:** ZERO PHP Frameworks (No Laravel, Symfony, Slim, CodeIgniter, or WordPress). ZERO Node/npm build dependencies.

---

## 1. Architectural Overview & Directory Structure

```text
lad-global/
├── app/
│   ├── bootstrap.php          # Core autoloader, environment loader, error handler
│   ├── config/                # Application & database configuration
│   │   ├── app.php
│   │   └── database.php
│   ├── controllers/           # Application controllers (P03+)
│   ├── core/                  # Pure PHP core helpers
│   │   ├── Database.php       # PDO connection with strict prepared statements
│   │   ├── Env.php            # Safe zero-dependency .env loader
│   │   ├── Response.php       # JSON, redirects, and session flash messages
│   │   ├── Router.php         # Clean HTTP routing engine & 404 handler
│   │   ├── Security.php       # XSS escaping, CSRF protection, secure sessions
│   │   ├── Validator.php      # Server-side validation & sanitization
│   │   └── View.php           # Server-rendered layouts & views engine
│   ├── models/                # Database entities & query models (P03+)
│   ├── services/              # Paystack, email, business rules (P06/P07)
│   └── views/                 # Server-rendered templates
│       ├── layouts/           # Base layouts (main.php, admin.php)
│       └── pages/             # Page views (home.php, 404.php, 500.php)
├── database/
│   ├── migrations/            # Plain SQL migrations (P03)
│   └── seeds/                 # Sample seed data
├── public/                    # PUBLIC WEBROOT (Expose this folder only to Apache/Nginx)
│   ├── index.php              # Single front controller entry point
│   ├── .htaccess              # Clean URL rewriting & security headers
│   ├── assets/                # Static assets
│   │   ├── css/app.css
│   │   ├── js/app.js
│   │   └── img/
│   └── uploads/               # User uploads (denied PHP execution)
│       └── .htaccess
├── storage/                   # PRIVATE STORAGE (Outside document root)
│   ├── logs/                  # Structured error & audit logs
│   └── temp/                  # Ephemeral files
├── tests/                     # Smoke tests & validation test scripts
│   └── SmokeTest.php
├── prototypes/                # High-fidelity UI/UX prototype suite (P01)
├── .env.example               # Configuration template (no secrets)
├── .gitignore
├── PROJECT_SOURCE_OF_TRUTH.md
├── PROJECT_PHASE_TRACKER.md
├── PHASE_EXECUTION_PROMPTS.md
└── README.md
```

---

## 2. Security & Hosting Guidelines

### 2.1 Standard Deployment (Document Root = `public/`)
On modern VPS, Docker, or cPanel configurations where the document root can be customized, set the web root directory to `/path/to/lad-global/public/`. All private code (`app/`, `storage/`, `database/`, `.env`) resides one level above the document root and cannot be browsed directly over HTTP.

### 2.2 Shared Hosting / cPanel Fallback (`public_html`)
Where cPanel restricts the primary domain root strictly to `public_html`:
1. Place the private folders (`app/`, `storage/`, `database/`, `.env`) outside `public_html`, e.g., in `/home/username/lad_private/`.
2. Place the contents of `public/` directly into `/home/username/public_html/`.
3. In `/home/username/public_html/index.php`, adjust the bootstrap path:
   ```php
   require_once '/home/username/lad_private/app/bootstrap.php';
   ```
4. Verify that `public_html/uploads/.htaccess` continues to deny execution of `.php` scripts.

---

## 3. Local Development & Running the Application

### 3.1 Prerequisites
- PHP 8.2+ with CLI and extensions: `pdo_mysql`, `openssl`, `curl`, `mbstring`, `fileinfo`.
- MySQL 8.0+ or MariaDB 10.4+.

### 3.2 Configuration
1. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```
2. Update database credentials in `.env` if testing database connectivity.

### 3.3 Built-in PHP Development Server
Run from the project root:
```bash
php -S localhost:8000 -t public
```
Visit `http://localhost:8000` in your web browser.

---

## 4. Running Verification Smoke Tests

A standalone smoke test script is provided in `tests/SmokeTest.php` to verify:
1. Environment file loading and parsing.
2. Security helpers (XSS escaping, CSRF generation & timing-safe validation).
3. Server-side input validator rules (`required`, `email`, `phone`, `min`, `max`, `in`).
4. Router dispatching (static paths, dynamic parameters `{slug}`, 404 handlers, method rejection).
5. Database connector exception shielding (confirming secrets are never exposed on failure).

Execute the test suite via PHP CLI:
```bash
php tests/SmokeTest.php
```
