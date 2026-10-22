# LAD GLOBAL TRAVELS AND TOURS — PROJECT SOURCE OF TRUTH

> **Document ID:** LG-SOT-001  
> **Version:** 1.0  
> **Created:** 2026-10-20  
> **Status:** Planning / awaiting final approvals  
> **Authority:** This document governs scope, implementation, acceptance and changes throughout the project lifecycle.

## 0. Document governance

- `PROJECT_SOURCE_OF_TRUTH.md` defines **what** must be built and the boundaries of the project.
- `PROJECT_PHASE_TRACKER.md` records **what has actually been completed**, with evidence and approvals.
- `PHASE_EXECUTION_PROMPTS.md` defines **how to instruct a developer or coding agent** to execute each approved phase.
- In a conflict, the latest client-approved change logged in §15 and then incorporated into this document prevails. Prompts and tracker never silently expand scope.
- Update the document version, date, change log and affected acceptance criteria whenever a requirement is formally changed. Never present an assumption as client approval.

## 1. Project identity

| Field | Value |
|---|---|
| Client brand | LAD Global Travels and Tours |
| Industry | Travel, tours, visa assistance and global mobility |
| Website domain preferences | `ladglobal.com` (preferred option 1); `ladglobaltravel.com` (option 2) — **availability/ownership not verified** |
| Phone and WhatsApp | `07017597650` / `https://wa.me/2347017597650` |
| Office address, as supplied | SF41, Ikorodu Complex, It's Elewa, adjacent to High Court, Ikorodu, Lagos — **verify spelling and formatting before publication** |
| Primary audience | Travellers, tour groups, students and people enquiring about international mobility services |
| Core promise | Professional, personalized travel planning and assistance |
| Brand/tagline references | “Explore • Discover • Experience”; “Your Journey. Our Commitment. Your World of Possibilities.” — choose approved public tagline |
| Visual references | Client-supplied LAD and LGI logos, tour flyers; one Travo screenshot used **only as WhatsApp/contact UI reference**, not as a product specification |

### 1.1 Logo ambiguity requiring approval

The supplied references show different identities: **LAD Global Travels and Tours**, **LAD Global Integrated Service**, and **LGI / LAD Global Integrated Service**. Do not mix these. Get the client's approved **trading name, legal entity name and primary logo** before production UI. For now use “LAD Global Travels and Tours” as the public working name; do not infer legal status from branding.

## 2. Product vision and success criteria

Build a responsive, polished **public-facing travel agency website** that presents services and tour packages, captures enquiries, accepts payments for approved/bookable packages, and provides a **single minimal administrator interface** to manage packages, enquiries and payment status.

A successful launch means:

1. Visitors can understand services, browse destination/package details and reach the business on WhatsApp or by enquiry form.
2. The admin can securely sign in and create, view, edit, publish/unpublish and archive tour packages.
3. A visitor can request a package booking or make a general service enquiry without creating an account.
4. For packages explicitly enabled for payment, visitors can choose either **manual bank transfer** or **Paystack**.
5. Paystack payments are verified **server-side**; manual bank transfers remain **pending** until the admin confirms bank receipt.
6. The admin can view, search and update enquiries using basic statuses and notes, and see payment records.
7. Business contact, domain email and social links work once their actual accounts and settings are supplied.
8. The site works on mobile and desktop, is accessible at a reasonable baseline, and is deployable to appropriate PHP hosting.

## 3. Non-negotiable architecture and scope

### 3.1 Required technology

- **Pure/native PHP 8.2+**; procedural or lightweight object-oriented PHP, with clear modular organization.
- **No PHP framework**: no Laravel, Symfony, CodeIgniter, Slim or similar; no WordPress.
- Frontend: server-rendered PHP templates, semantic HTML5, CSS3 and vanilla JavaScript. Do not require React/Vue or a frontend build pipeline.
- Database: **MySQL 8+ or compatible MariaDB**, accessed via **PDO prepared statements**.
- Web server: Apache/PHP via cPanel or equivalent, HTTPS enforced in production.
- Dependencies: prefer built-in PHP extensions (`pdo_mysql`, `openssl`, `curl`, `mbstring`, `fileinfo`). A small, audited standalone library can only be introduced with explicit approval; never introduce a framework implicitly.
- Source code stored in Git, `.env`-style configuration **outside public webroot** or server-provided environment variables. Never commit secrets.

### 3.2 Explicit exclusions

- **NO live flight search, flight ticketing API or flight booking engine.** Flight assistance is enquiry-only.
- **NO hotel inventory, live rates or hotel booking engine.** Hotel assistance is enquiry-only.
- **NO client portal or customer login.**
- **NO staff accounts, roles or staff dashboard.** One admin account/interface at MVP; design with upgrade path but do not build multi-user RBAC.
- **NO advanced CRM**, automated calling, telephony, staff assignment, drip sequences, marketing automation, or complex pipeline.
- **NO visa/immigration application processing portal** or file/document upload for sensitive applicant records.
- **NO travel provider API integrations** unless separately scoped.
- **NO assumption that customers can instantly book every service**; only explicitly priced and enabled packages accept direct payment.

### 3.3 Scope boundary

In MVP, the **admin manages packages, enquiries, bookings/payment records and limited settings**. Other static pages (About, Services, FAQ, privacy/terms) are maintained in PHP content/templates by developer, not in a full CMS. This is a proposed lightweight implementation assumption, pending client approval.

## 4. Information architecture

**Navigation:** Home | About Us | Services | Tour Packages | FAQs | Contact Us; persistent CTA “Plan Your Trip”, with floating WhatsApp.

| Route (suggested) | Content / action |
|---|---|
| `/` | Brand hero, quick CTA, selected services, destinations and featured packages, value proposition, how it works, FAQ preview, footer |
| `/about` | Supplied company background, mission, vision, commitment |
| `/services` | Services grid and enquiry CTAs |
| `/services/{slug}` (optional) | Detail pages where content adds value; otherwise anchor sections within `/services` |
| `/packages` | Published packages, filters by destination/category if useful |
| `/packages/{slug}` | Gallery, destination, dates/availability note, price display, inclusions/exclusions, itinerary, enquiry and permitted payment action |
| `/enquire` (or embedded form) | General service enquiry; no account required |
| `/booking/{reference}` | **Avoid a public account-like portal**. Show a post-submission acknowledgment only; any payment continuation must use a short-lived unpredictable token if necessary |
| `/faq` | The client's 23 supplied FAQs, edited only for consistency/verified claims |
| `/contact` | Form, phone, WhatsApp, address and map link if location verified |
| `/privacy`, `/terms`, `/refund-policy` | Client-approved policies before launch |
| `/admin/login` | Sole admin authentication |
| `/admin` | Summary counts, recent enquiries and pending transfers |
| `/admin/packages`, `/admin/enquiries`, `/admin/payments`, `/admin/settings` | Minimal admin management |

Clean routes may be implemented using Apache rewrite rules and a small custom PHP router, or conventional `.php` pages. Avoid architectural over-engineering.

## 5. Services and destinations

### 5.1 Services to display (not booking engines)

1. Visa assistance.
2. Local and international tour packages, including group tours.
3. Domestic and international flight **booking assistance / enquiries**.
4. Hotel reservation **assistance / enquiries**.
5. Study abroad / study visa assistance.
6. Canada Permanent Residency (PR) **enquiry and guidance**.
7. Citizenship by Investment (CBI) **enquiry and guidance**.
8. Residency by Investment (RBI) **enquiry and guidance**.

**Legal/compliance gate:** Immigration/CBI/RBI and Canadian PR descriptions must be verified for service authorization and jurisdiction; never suggest guaranteed approvals, provide legal advice, or advertise unsupported programs. In particular validate claims about Dominican Republic CBI before promoting any specific program. The FAQ must maintain the no-guarantee statement.

### 5.2 Destinations mentioned by client

Kenya, Rwanda, Gambia, Ghana, China, South Korea, Morocco, Dubai/UAE; promotional references also show Singapore, Doha/Qatar, Tanzania, Egypt, France, Germany, Italy, Netherlands and Switzerland. **Destinations in images are references, not approved active/available packages.** Publish only actual approved packages with verified price, dates, inclusions and availability.

## 6. Tour package management

### 6.1 Required package fields

- `id`, `title`, `slug` (unique), `destination_country`, optional `destination_city`, `summary`, `description`.
- `cover_image`, optional gallery images; meaningful alt text.
- `duration_days` (optional), `travel_start_date` and `travel_end_date` (optional; not invented).
- `price_amount` decimal and `price_currency` (e.g. NGN) if priced; otherwise “Request a quote”.
- `inclusions`, `exclusions`, optional `itinerary`.
- `capacity` optional, `booking_deadline` optional; do not promise inventory controls unless specified.
- `is_payment_enabled` boolean, `status` (`draft`, `published`, `archived`), `is_featured`, timestamps.

### 6.2 Admin package actions

- Add, edit, preview, publish/unpublish and archive a package.
- Upload/replace images with strict validation.
- Feature/unfeature package on homepage.
- Protect against deleting a package tied to existing booking/payment records (archive instead).
- Validate price > 0 and currency supported before enabling direct payments.

## 7. Customer enquiries and minimal CRM

### 7.1 Form fields

- Full name, email, phone/WhatsApp, service type, destination (optional), preferred dates (optional), number of travellers (optional), message, consent/notice checkbox as applicable.
- Package enquiry should prefill/associate package ID; do not trust package price from form POST.
- Spam prevention: server-side validation, CSRF tokens, honeypot and throttling; CAPTCHA only if needed.

### 7.2 Minimal CRM workflow

- Statuses: `new`, `contacted`, `quoted`, `closed` (optional `cancelled` for invalid enquiries).
- Admin can list, search/filter, open, add internal notes, update status and optional `follow_up_date`.
- Basic dashboard: enquiry counts, latest enquiries, published packages, pending manual transfers.
- Email alert on new enquiry to configured company inbox; customer acknowledgment if mail is configured.
- No automatic voice calling or sales sequence. A manual call button (`tel:`) and WhatsApp link are sufficient.

## 8. Booking and payments

### 8.1 Eligible flows

- **Enquiry-only:** Visa, flight/hotel assistance, study, PR, CBI/RBI and packages without approved price.
- **Payable package:** Admin-enabled package with amount/currency set; customer submits booking details, receives a unique reference, chooses transfer or Paystack. Payment is **not proof of travel confirmation**; fulfillment/availability handled by business.
- Decide before implementation whether pricing is **per person** and how traveller quantity changes the amount. Default to one customer-paid line item; never assume pricing formula.

### 8.2 Manual bank transfer

1. Server creates booking and payment attempt as `pending_manual` using an immutable expected amount and currency.
2. Customer receives company bank details **from admin-managed verified configuration**, unique reference, amount and instructions.
3. Customer may provide transaction reference/proof text (avoid mandatory files at MVP).
4. Admin verifies receipt in actual bank account **outside the website** and marks `paid` or `rejected`; record `verified_at` and audit detail.
5. Never auto-confirm transfer based solely on a screenshot or the visitor clicking “I paid”.

### 8.3 Paystack

1. Backend creates payment attempt with unique internal reference and amount using Paystack secret key server-side.
2. Paystack initialization uses amount in subunits and verified approved currency; present the hosted/secure payment checkout.
3. On callback/webhook, independently **verify transaction server-to-server**; check gateway status, exact amount, currency, reference and related booking, then update transaction atomically.
4. Verify webhook signature; make processing idempotent to prevent duplicate successful payment events.
5. Treat callbacks/browser redirects as **untrusted** until verified. Preserve failed/abandoned attempts without changing successful payments.
6. Never expose secret keys or card data in frontend, source control or logs. Use Paystack test mode until acceptance.

### 8.4 Customer confirmation

- Show booking/enquiry reference on success and email a receipt/acknowledgment if configured.
- Distinguish `payment received` from `travel booking confirmed`.
- Refunds are **manual administrative/business process** at MVP, governed by approved policies; no automated refunds.

## 9. Admin and data model

Recommended minimal tables (exact schema to be finalized at DB phase):

- `admins` (single authorized admin; password hash; last login).
- `packages`, `package_images`.
- `enquiries`, `enquiry_notes`.
- `bookings` (reference, package id, snapshot name/contact, amount/currency, booking status).
- `payments` (booking id, provider `manual|paystack`, attempt reference, amount/currency, status, provider transaction id, verified_at, timestamps).
- `settings` (whatsapp, social URLs, company contact, verified bank details, admin notifications; **never store payment secrets in plain DB fields**).
- `payment_events` or `audit_logs` for webhook deduplication and manual-payment decisions.

Database principles: foreign keys, unique slug/reference constraints, decimal currency values, indexed status/date fields, sensible deletion policy, UTC timestamps stored consistently.

## 10. Branding and design rules

- Design direction: refined travel agency visual style; spacious, responsive, strong travel photography and readable type; avoid recreating dense flyer design as webpage.
- Preliminary palette: navy `#092A60`, royal blue `#135EB0`, muted gold `#E6AE34`, warm white `#F7F6F2`. **Not final until client approves logo and color identity.**
- Approved logo must exist in web-suitable transparent/high-resolution format with mobile/header variation if possible.
- Mobile-first layout; accessible contrast, semantic landmarks, visible keyboard focus, labeled forms and descriptive images.
- No fake testimonials, invented reviews, package prices, dates, certifications or guarantees.
- Floating WhatsApp contact link on every public page; unobtrusive and never covers primary form controls.

## 11. Business email and social accounts

Requested mailboxes, adapted to purchased domain:

- `info@<domain>`
- `sales@<domain>`
- `visa@<domain>`
- `admin@<domain>`
- `lanre@<domain>`

Configure through chosen email provider; decide whether these are **five paid mailboxes** or aliases/shared addresses; set SPF, DKIM, DMARC. SMTP credentials are configured in environment and tested. Facebook/Instagram accounts will be set up separately and their real URLs entered into admin settings once available. Website links are not dependent on social platform APIs.

## 12. Security, privacy, accessibility and quality

- HTTPS, secure cookies, session regeneration, logout, inactivity timeout and rate-limited admin login.
- `password_hash()` / `password_verify()`, CSRF protection, output escaping (`htmlspecialchars`), PDO placeholders.
- Validate *all* input server-side and whitelist expected enum/status values; strict admin authorization on each admin route.
- Media: file type verified with `finfo`, upload size restrictions, randomized filenames, no PHP execution in upload directory; generate resized images if practical.
- Proper error pages, safe user-facing errors, structured private logs; don't leak API credentials/stack traces.
- Backups for database and uploads; restoration instructions; least-privilege DB account.
- Add privacy notice and retention plan for enquiry data; collect only what's required, support deletion requests in accordance with applicable law.
- Test at mobile widths, desktop browsers, keyboard navigation, common form edge cases and payment failures.
- Performance: responsive images, caching headers, minimized assets, limited external scripts.

## 13. Proposed project structure

```text
lad-global/
├── app/
│   ├── config/
│   ├── controllers/
│   ├── core/          # PDO, router, CSRF, validation, auth, response helpers
│   ├── models/
│   ├── services/      # Paystack, email, upload, business rules
│   └── views/         # PHP layouts, public pages, admin pages
├── database/
│   ├── migrations/    # numbered, plain SQL migrations
│   └── seeds/         # sample data; never real customer data
├── public/
│   ├── index.php      # web entry point
│   ├── assets/
│   ├── uploads/       # denied PHP/script execution
│   └── .htaccess
├── storage/           # private logs, temp, backup metadata
├── tests/             # simple PHP tests/manual test scripts
├── .env.example       # placeholders only
├── .gitignore
├── README.md
├── PROJECT_SOURCE_OF_TRUTH.md
├── PROJECT_PHASE_TRACKER.md
└── PHASE_EXECUTION_PROMPTS.md
```

**Hosting caveat:** configure the document root to `public/`. Where cPanel forces `public_html`, place private app/config/storage above it and deploy **only the public entry point/assets** into the public folder, with corrected paths.

## 14. Delivery lifecycle and phase acceptance

| ID | Phase | Exit/acceptance gate |
|---|---|---|
| P00 | Discovery & approval | Brand name/logo, domain, bank/payment ownership, contact details, MVP scope and policy owner confirmed |
| P01 | Information architecture & UI/UX | Approved sitemap and responsive designs for home, packages/detail, services, enquiry and admin; content mapped |
| P02 | Environment & pure-PHP foundation | Runnable PHP project, git baseline, environment config, router, reusable layouts, DB connection, security helpers |
| P03 | Database & admin authentication | SQL schema/migrations, secure single-admin login/logout, guarded admin shell |
| P04 | Public site & content | Responsive pages, service descriptions, FAQ, navigation, WhatsApp and SEO metadata; verified content only |
| P05 | Package management | Admin package CRUD, safe media upload, public package listing/detail, publish/archive logic |
| P06 | Enquiries & basic CRM | Public forms stored safely, admin searchable enquiry list, statuses/notes, email notifications where configured |
| P07 | Booking & payments | Manual pending/verified flow, Paystack test flow and webhook validation, receipts/references, failed-path tests |
| P08 | Business integrations & configuration | Working domain mail routing/SMTP, bank/company settings, WhatsApp/social links |
| P09 | QA, hardening & client UAT | Security checks, payment reconciliation tests, responsive tests, reviewed real content, client sign-off |
| P10 | Deployment, handover & launch | Live SSL, production config, backups, admin credentials transferred securely, user guide, rollback plan |
| P11 | Warranty / post-launch | Agreed bug-fix window, issue log, monitoring/checklist and handover closure (duration TBD) |

**Gate rule:** A phase is complete only after its acceptance criteria pass and evidence/approval is recorded in `PROJECT_PHASE_TRACKER.md`.

## 15. Change control log

| Date | Change | Status |
|---|---|---|
| 2026-10-20 | Clarified: no flight/hotel booking engine; enquiry-only | Accepted user direction |
| 2026-10-20 | No customer portal and no staff dashboard | Accepted user direction |
| 2026-10-20 | Payment methods limited to bank transfer and Paystack | Accepted user direction |
| 2026-10-20 | Basic CRM only; single minimal admin; admin manages packages | Accepted user direction |
| 2026-10-20 | Pure/native PHP required; no Laravel or other frameworks | Accepted user direction |
| 2026-10-20 | Static-page editing by developer; admin editing limited to packages, enquiries and payments | Proposed assumption; pending confirmation |

## 16. Open decisions / client inputs

| ID | Decision/input required | Owner | State |
|---|---|---|---|
| D01 | Confirm approved legal/trading name and **one** final logo | Client | OPEN |
| D02 | Domain selection, availability and registration ownership | Client/developer | OPEN |
| D03 | Verify business address spelling, company phone and official tagline | Client | OPEN |
| D04 | Approved company bank name, account name/number; never hardcode unverified values | Client | OPEN |
| D05 | Paystack verified business account, supported currency and test/live credentials | Client | OPEN |
| D06 | Package pricing basis (per person vs group), tax/fees and change/cancellation rules | Client | OPEN |
| D07 | First 3–6 launch packages with approved descriptions, dates/prices and images | Client | OPEN |
| D08 | Privacy policy, terms, refund policy, service disclaimers and legal review | Client | OPEN |
| D09 | Email provider and whether 5 inboxes vs aliases; Facebook/Instagram profile URLs | Client/developer | OPEN |
| D10 | Confirm public static pages are developer-edited (not full CMS) | Client | OPEN |
| D11 | Whether booking payments require prior manual availability approval | Client | OPEN |
| D12 | Hosting access/cPanel details, PHP version and database availability | Developer/client | OPEN |
| D13 | Who may legally provide immigration/Canada PR/CBI/RBI advice; verify public claims | Client | OPEN |
| D14 | Warranty period, delivery budget and deadline | Client/developer | OPEN |

## 17. Definition of done — entire project

- [ ] Scope in this document matches signed-off requirements and no excluded features are included.
- [ ] Site launches with approved identity, content, policies and active offers.
- [ ] Pure PHP + PDO/MySQL only; maintainable, reasonably documented code.
- [ ] Admin login, package management and minimal CRM operate correctly.
- [ ] Transfer status cannot be falsely auto-confirmed; Paystack verification and webhook are secure and idempotent.
- [ ] No live ticketing engine, client/staff dashboards or advanced CRM.
- [ ] Mobile accessibility/responsiveness and basic security checks passed.
- [ ] Domain email, WhatsApp, contacts and social links verified.
- [ ] Backups, deployment guide, credentials handover and client acceptance recorded.
