# LAD GLOBAL TRAVELS AND TOURS — DISCOVERY & APPROVAL RECORD (P00)

> **Document ID:** LG-P00-REC-001  
> **Date:** 2026-10-20  
> **Phase:** P00 — Discovery & Approval  
> **Status:** IN REVIEW / AWAITING CLIENT APPROVAL  
> **Reference Documents:** `PROJECT_SOURCE_OF_TRUTH.md` (v1.0), `PROJECT_PHASE_TRACKER.md` (v1.0)

---

## 1. Executive MVP Scope Summary

This project delivers a responsive, modern, high-performance website for **LAD Global Travels and Tours** alongside a minimal single-administrator management console.

### 1.1 Strict Guardrails & Non-Negotiables
- **Pure Native PHP 8.2+**: Zero PHP frameworks (no Laravel, Symfony, Slim, CodeIgniter, WordPress).
- **Vanilla Frontend**: Server-rendered HTML5, modern semantic CSS3, vanilla JavaScript. Zero Node/npm build dependencies or SPA frameworks.
- **Database**: MySQL 8+ / MariaDB accessed strictly through PDO with prepared statements.
- **Strict Scope Exclusions**:
  - **NO Live Flight/Hotel Booking Engine**: Flight and hotel assistance pages are enquiry-only forms.
  - **NO Customer Portal / Customer Accounts**: Visitors book or enquire without user accounts.
  - **NO Multi-User Staff Roles or Advanced CRM**: Single admin account managing packages, enquiries, payments, and notes. No automated drip sequences, telephony, or complex pipelines.
  - **NO Visa/Immigration Application Processing Portal**: Sensitive immigration documents are not collected or processed through the web application.
  - **Payment Verification Integrity**: Payments accepted solely via **Manual Bank Transfer** (admin marks paid after verifying external bank statement) or **Paystack** (server-to-server verification + HMAC-SHA512 webhook). No browser redirects or customer screenshots are trusted as proof of payment.
  - **Content Authenticity**: No fabricated package prices, invented dates, fake reviews/testimonials, or false immigration guarantees.

---

## 2. Confirmed Requirements vs. Unapproved Assumptions

| Category | Requirement / Feature | Status | Notes / Source of Truth Reference |
|---|---|---|---|
| **Confirmed** | Pure PHP 8.2+ & PDO MySQL stack | CONFIRMED | SOT §3.1 |
| **Confirmed** | Server-rendered HTML5 / CSS3 / Vanilla JS | CONFIRMED | SOT §3.1 |
| **Confirmed** | Enquiry-only for flights & hotels | CONFIRMED | SOT §3.2, §5.1 |
| **Confirmed** | No client accounts or staff RBAC | CONFIRMED | SOT §3.2 |
| **Confirmed** | Single minimal administrator dashboard | CONFIRMED | SOT §3.2, §7.2 |
| **Confirmed** | Editable Tour Packages (admin CRUD) | CONFIRMED | SOT §6 |
| **Confirmed** | Bank Transfer + Paystack payment methods | CONFIRMED | SOT §8 |
| **Confirmed** | WhatsApp direct integration (`https://wa.me/2347017597650`) | CONFIRMED | Phone `07017597650` verified |
| **Confirmed** | 8 Core Services catalogue display | CONFIRMED | SOT §5.1 |
| **Confirmed** | 23 Client-provided FAQs published | CONFIRMED | SOT §4, FAQ list supplied |
| **Confirmed** | Trading Name & Master Logo selection | **CONFIRMED** | Approved master 3D logo provided by client (D01); deployed to `/assets/images/logo.png` & `/assets/images/logo-160.png` |
| **Confirmed** | Official Company Tagline | **CONFIRMED** | Verified from approved master logo: "Explore • Discover • Experience" (D03) |
| **Assumption** | Domain selection (`ladglobal.com` vs `ladglobaltravel.com`) | **UNAPPROVED** | Availability & ownership not verified (D02) |
| **Assumption** | Office Address spelling ("It's Elewa" vs "Ita Elewa") | **UNAPPROVED** | Needs local spelling verification (D03) |
| **Assumption** | Developer-managed static pages (About, Services, FAQ, Policies) | **UNAPPROVED** | Admin manages packages/enquiries/payments only (D10) |
| **Assumption** | Package pricing basis (Per-person vs flat package price) | **UNAPPROVED** | Needs formula confirmation (D06) |
| **Assumption** | Booking availability gate prior to payment | **UNAPPROVED** | Direct payment vs quote-then-pay (D11) |
| **Assumption** | Mailbox setup (5 dedicated inboxes vs aliases) | **UNAPPROVED** | Email hosting decision (D09) |

---

## 3. Client Approval Checklist & Open Decisions (D01 – D14)

### Priority 1: Blocking Next Phase (P01/P02/P03)
- [x] **D01 — Brand Identity & Trading Name:**
  - *Context:* Confirmed primary public trading name: **LAD Global Travels and Tours**. Approved master 3D logo received from client and integrated across public website, admin console, receipts, metadata, and favicons.
- [x] **D03 (Part A) — Official Brand Tagline:**
  - *Context:* Confirmed from approved master logo: **“Explore • Discover • Experience”**.
- [ ] **D03 (Part B) — Physical Address Spelling:**
  - *Context:* Supplied address reads: `SF41, Ikorodu Complex, It's Elewa, adjacent to High Court, Ikorodu, Lagos`. Confirm whether "Ita Elewa" is the official local municipal spelling.
- [ ] **D06 — Package Pricing Model:**
  - *Context:* Tour packages can be priced per traveler or as a fixed package price.
  - *Client Action:* Confirm pricing formula. Recommended default: *Price quoted per person; booking form multiplies amount by number of travelers on the server.*
- [ ] **D10 — Scope of Admin Editing:**
  - *Context:* To keep code pure and avoid an over-engineered CMS, administrative management covers Tour Packages, Enquiries, Bookings, Payments, and Settings. Static pages (About Us, Services, FAQ, Policies) are maintained in code templates.
  - *Client Action:* Approve this boundary.

### Priority 2: Required Before Payment Implementation (P07)
- [ ] **D04 — Official Company Bank Details:**
  - *Context:* Required for manual bank transfer instructions.
  - *Client Action:* Provide official corporate bank name, account name, and account number.
- [ ] **D05 — Paystack Account & Credentials:**
  - *Context:* Paystack requires an active registered business account.
  - *Client Action:* Confirm registered Paystack account; provide Test Public & Secret Keys for development.
- [ ] **D11 — Booking Availability Gate:**
  - *Context:* Can a customer immediately pay for a package on the website, or must the agency first confirm flight/hotel/tour slot availability before receiving funds?
  - *Client Action:* Decide whether direct instant payment is permitted for enabled packages, or if every booking starts as a confirmed quote.

### Priority 3: Required Before Launch & Deployment (P04, P08 – P10)
- [ ] **D02 — Domain & DNS Ownership:**
  - *Client Action:* Confirm registered domain (e.g. `ladglobal.com` or `ladglobaltravel.com`) and provide registrar/DNS access.
- [ ] **D07 — Launch Packages (3 to 6):**
  - *Client Action:* Provide real itineraries, valid travel dates, verified pricing in NGN, inclusions, exclusions, and authentic photos for launch packages.
- [ ] **D08 — Legal Policies & Disclaimers:**
  - *Client Action:* Review and sign off on Privacy Policy, Terms & Conditions, and Cancellation/Refund Policy.
- [ ] **D09 — Email Inboxes & Social Media Profiles:**
  - *Client Action:* Confirm email provider (cPanel webmail, Google Workspace, or Microsoft 365) and whether `info@`, `sales@`, `visa@`, `admin@`, and `lanre@` are separate mailboxes or aliases. Provide official Instagram and Facebook profile URLs.
- [ ] **D12 — Production Hosting:**
  - *Client Action:* Provide cPanel or hosting environment credentials with PHP 8.2+ and MySQL/MariaDB.
- [ ] **D13 — Regulatory / Immigration Claims Verification:**
  - *Client Action:* Confirm legal compliance wording for Canada PR, Study Visas, and CBI/RBI assistance (ensuring explicit "no guarantee" disclaimers are approved).
- [ ] **D14 — Delivery Timeline, Budget & Warranty Terms:**
  - *Client Action:* Formalize project milestones, warranty window, and acceptance criteria.

---

## 4. Missing Production Assets & Information Inventory

The following physical assets are required from the client:

1. **Brand Assets**:
   - Master vector logo (.SVG, .AI, or high-res transparent .PNG).
   - Horizontal banner logo variation for mobile header.
   - Favicon asset.
2. **Launch Content**:
   - High-resolution photography for destinations (Kenya, Dubai, Rwanda, Gambia, Ghana, etc.).
   - Exact itineraries and pricing breakdowns for initial 3–6 packages.
3. **Financial Credentials**:
   - Corporate bank account name, bank name, account number.
   - Paystack API test keys (`pk_test_...`, `sk_test_...`).
4. **Hosting & Connectivity**:
   - Domain registrar login or DNS zone control.
   - Production hosting cPanel / SSH / FTP credentials.
   - SMTP host, port, username, and password for outgoing email notifications.

---

## 5. Draft Client Sign-Off Record

```text
================================================================================
LAD GLOBAL TRAVELS AND TOURS — PHASE P00 APPROVAL & SCOPE CONFIRMATION
================================================================================

PROJECT: LAD Global Travels and Tours Website & Admin Management Portal
DOCUMENT REFERENCE: LG-SOT-001 (Version 1.0) / LG-P00-REC-001

Confirmed Scope Summary:
- Pure PHP 8.2+ / MySQL PDO / Vanilla HTML5, CSS3, JS.
- Enquiry-only services (Flight assistance, Hotel assistance, Visa, Study Abroad, CBI/RBI, Canada PR).
- Single admin panel for Tour Packages, Enquiries, Bookings, and Payment tracking.
- Bank Transfer + Paystack payment integration with strict server verification.
- No client accounts, no live flight/hotel ticketing engine, no staff roles.

Client Decisions Confirmed:
- Approved Public Trading Name: [ __________________________________ ]
- Approved Brand Tagline:      [ __________________________________ ]
- Address Spelling Confirmed:  [ __________________________________ ]
- Package Pricing Basis:       [ Per Person / Flat Package ]
- Static Pages Policy:         [ Approved: Developer-managed code templates ]

Signatures:

Client Representative: _______________________   Date: _______________

Lead Developer:       _______________________   Date: 2026-10-20
================================================================================
```
