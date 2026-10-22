# LAD GLOBAL TRAVELS AND TOURS — INFORMATION ARCHITECTURE & UI/UX SPECIFICATION (P01)

> **Document ID:** LG-P01-SPEC-001  
> **Date:** 2026-10-20  
> **Phase:** P01 — Information Architecture, UX and Visual System  
> **Status:** IN REVIEW / PENDING DESIGN APPROVAL  
> **Source of Truth Reference:** `PROJECT_SOURCE_OF_TRUTH.md` (v1.0), `PROJECT_PHASE_TRACKER.md` (v1.0)

---

## 1. Executive Summary & Design Principles

This document establishes the structural information architecture (IA), component breakdown, visual token definitions, accessibility standards, and state handling rules for the LAD Global Travels and Tours web platform.

### Core UX Principles
1. **Clarity Over Complexity:** Every visitor immediately understands what LAD Global offers (personalized travel, visa assistance, and curated packages) without false expectations of an automated airline or hotel booking engine.
2. **Distinct Conversion Paths:**
   - **Enquiry Flow (Quote/Consultation):** Used for Visas, Flights, Hotels, Study Abroad, Canada PR, and unpriced packages. Focuses on minimal friction, clear questions, and fast WhatsApp/callback response.
   - **Payable Package Flow:** Used *only* for published, admin-priced, payment-enabled packages. Transparent pricing in NGN, clear per-person calculation, and explicit two-option payment (Paystack or Manual Bank Transfer).
3. **No False Promises / Clean Compliance:** Zero fabricated reviews, no countdown timers, no "only 1 ticket left" dark patterns, and prominent "No Visa Guarantee" disclaimers.
4. **Mobile-First & Ultra-Accessible:** Optimized for mobile browsing across African and global networks; high contrast text, minimum 44×44px tap targets, visible focus outlines, and zero JavaScript-dependent critical rendering.

---

## 2. Complete Sitemap & Route Inventory

```
LAD Global Travels & Tours
│
├── Public Routes (Frontend)
│   ├── /                         [Home]
│   ├── /about                    [About Us: Story, Mission, Values]
│   ├── /services                 [Services Overview (8 Services)]
│   ├── /packages                 [Tour Packages Listing & Filters]
│   ├── /packages/{slug}          [Package Detail & Booking / Enquiry Modal]
│   ├── /enquire                  [General & Service Enquiry Form]
│   ├── /booking/{reference}      [Booking Status & Payment Instructions]
│   ├── /faq                      [23 Supplied FAQs Accordion]
│   ├── /contact                  [Office, Phone, Map, Direct Message]
│   ├── /privacy                  [Privacy Policy Placeholder]
│   ├── /terms                    [Terms of Service Placeholder]
│   └── /refund-policy            [Refund & Cancellation Policy Placeholder]
│
└── Admin Routes (Protected — Single Admin Session)
    ├── /admin/login              [Secure Administrator Authentication]
    ├── /admin/logout             [POST Session Termination + Token Destruction]
    ├── /admin                    [Dashboard: Metrics, Recent Leads, Pending Transfers]
    ├── /admin/packages           [Package List: Drafts, Published, Archived]
    ├── /admin/packages/create    [New Package Form + Secure Media Upload]
    ├── /admin/packages/{id}/edit [Edit Package, Itinerary, Pricing & Media]
    ├── /admin/enquiries          [Enquiry CRM: Filter by Status, Search, View]
    ├── /admin/enquiries/{id}     [Enquiry Detail + Internal Notes & Follow-up]
    ├── /admin/payments           [Payment Log: Paystack & Manual Transfer Audits]
    └── /admin/settings           [Business Contact, Bank Details, Social Links]
```

---

## 3. Key User Journeys

### Journey 1: Service Enquiry (Flight / Hotel / Visa / Study / PR / CBI)
```
[Visitor lands on /services or /] 
       ↓
[Clicks "Request Flight Assistance" or "Enquire About Visa"]
       ↓
[Navigates to /enquire (or opens prefilled modal) with service pre-selected]
       ↓
[Enters Name, Email, Phone/WhatsApp, Destination, Dates, Message]
       ↓
[Submits form → Server validates input, verifies CSRF, checks honeypot]
       ↓
[Database records enquiry as 'new' → Best-effort admin email alert triggered]
       ↓
[Visitor receives clear confirmation with Reference Code & direct WhatsApp link]
```

### Journey 2: Payable Package Booking & Payment
```
[Visitor browses /packages → Selects a featured package (e.g. Kenya Safari)]
       ↓
[Reviews Package Detail: Inclusions, Exclusions, Dates, Day-by-Day Itinerary]
       ↓
[Clicks "Book This Tour" (Enabled only if admin set price > 0 and payment=true)]
       ↓
[Enters Contact Info + Number of Travellers → Server computes total: amount × travellers]
       ↓
[Server creates Booking Record + Payment Attempt in 'pending' status]
       ↓
┌─────────────────────────────────┴─────────────────────────────────┐
│ Option A: Paystack (Online)       │ Option B: Manual Bank Transfer │
│                                   │                                │
│ 1. Server calls Paystack API      │ 1. Booking marked              │
│    with server-computed amount.   │    'pending_manual'.           │
│ 2. Customer redirected to         │ 2. Screen displays official    │
│    secure Paystack checkout.      │    verified company bank info, │
│ 3. On return, server verifies     │    exact amount, & unique ref. │
│    transaction server-to-server.  │ 3. Customer pays outside app.  │
│ 4. Webhook confirms transaction   │ 4. Admin checks real bank      │
│    idempotently with HMAC check.  │    statement and marks 'paid'. │
└─────────────────────────────────┬─────────────────────────────────┘
       ↓
[Booking Status Screen (/booking/{ref}) shows acknowledgment]
[Note: "Payment Received — Travel confirmation subject to availability verification"]
```

### Journey 3: Quick Direct Contact
```
[Visitor on any public page] 
       ↓
[Taps persistent Floating WhatsApp Button (bottom right)]
       ↓
[Opens https://wa.me/2347017597650 with prefilled greeting]
       ↓
[Direct chat with LAD Global consultant begins]
```

---

## 4. Design System Tokens (Provisional Palette)

The provisional tokens honor the brand identity provided in source materials while providing modern contrast, accessible typography, and scalable elevation.

```css
:root {
  /* Brand Palette */
  --color-primary: #092A60;         /* Deep Royal Navy - Authority & Trust */
  --color-primary-light: #135EB0;   /* Vibrant Azure - Interactive elements */
  --color-primary-dark: #051A3B;    /* Deep Midnight - Text & dark surfaces */
  --color-accent: #E6AE34;          /* Warm Heritage Gold - Badges, highlights */
  --color-accent-hover: #C99525;    /* Dark Gold */
  
  /* Neutral Canvas & Surfaces */
  --color-bg: #F8F9FA;              /* Soft Warm Off-White */
  --color-surface: #FFFFFF;         /* Pure Card White */
  --color-surface-subtle: #F1F4F8;  /* Subtle tinted grey-blue */
  --color-border: #E2E8F0;          /* Crisp neutral border */
  --color-border-focus: #135EB0;    /* High-contrast focus state */
  
  /* Typography Colors */
  --color-text-main: #1A202C;       /* WCAG AAA compliant text on white */
  --color-text-muted: #4A5568;      /* Secondary descriptive text */
  --color-text-light: #718096;      /* Captions, timestamps */
  --color-text-inverse: #FFFFFF;    /* Text on navy / dark buttons */

  /* Semantic Feedback Colors */
  --color-success: #0E7C3A;         /* Emerald Green - Paid / Confirmed */
  --color-success-bg: #E6F4EA;
  --color-warning: #B25E02;         /* Amber - Pending Transfer / Action */
  --color-warning-bg: #FEF3D6;
  --color-danger: #C5221F;          /* Crimson - Error / Rejected */
  --color-danger-bg: #FCE8E6;
  --color-info: #1A73E8;            /* Blue - Notice / Information */
  --color-info-bg: #E8F0FE;

  /* Typography Scale */
  --font-family-sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  --font-family-heading: "Segoe UI", -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
  --font-size-xs: 0.75rem;    /* 12px */
  --font-size-sm: 0.875rem;   /* 14px */
  --font-size-base: 1rem;     /* 16px */
  --font-size-lg: 1.125rem;   /* 18px */
  --font-size-xl: 1.25rem;    /* 20px */
  --font-size-2xl: 1.5rem;    /* 24px */
  --font-size-3xl: 2rem;      /* 32px */
  --font-size-4xl: 2.5rem;    /* 40px */
  
  /* Spacing Scale */
  --space-1: 0.25rem;  /* 4px */
  --space-2: 0.5rem;   /* 8px */
  --space-3: 0.75rem;  /* 12px */
  --space-4: 1rem;     /* 16px */
  --space-6: 1.5rem;   /* 24px */
  --space-8: 2rem;     /* 32px */
  --space-12: 3rem;    /* 48px */
  --space-16: 4rem;    /* 64px */

  /* Elevation & Shadows */
  --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px -1px rgba(9, 42, 96, 0.08), 0 2px 4px -1px rgba(9, 42, 96, 0.04);
  --shadow-lg: 0 10px 15px -3px rgba(9, 42, 96, 0.1), 0 4px 6px -2px rgba(9, 42, 96, 0.05);
  --shadow-xl: 0 20px 25px -5px rgba(9, 42, 96, 0.12), 0 10px 10px -5px rgba(9, 42, 96, 0.04);

  /* Radius */
  --radius-sm: 4px;
  --radius-md: 8px;
  --radius-lg: 12px;
  --radius-xl: 16px;
  --radius-pill: 9999px;
}
```

---

## 5. Page-by-Page Component & Content Specifications

### 5.1 Public Home (`/`)
* **Header / Navigation Bar:**
  - Logo (`LAD Global Travels and Tours`), primary nav links (`Home`, `About`, `Services`, `Tour Packages`, `FAQs`, `Contact`).
  - Mobile hamburger toggle with accessible off-canvas drawer.
  - Primary persistent CTA button: `"Plan Your Trip"` (anchors to `/enquire`).
* **Hero Banner Section:**
  - High-impact headline: *"Your Trusted Partner for Global Travel, Visas & Curated Tours"*.
  - Subheading: Professional, personalized travel assistance from Lagos to destinations worldwide.
  - Dual CTAs: `"Explore Packages"` (Primary) and `"Request Free Enquiry"` (Secondary Outline).
  - Trust Badges: *Personalized Consultations*, *Verified Bank / Paystack Payments*, *Dedicated Travel Advisors*.
* **Services Grid Preview:**
  - 8 Cards representing the core services with clear badges (`Enquiry Only` for Flight & Hotel assistance).
* **Featured Packages Section:**
  - 3–4 package cards showing destination, duration, price or "Quote On Request", cover image with alt tag, and `"View Details"` CTA.
* **Why Choose LAD Global Section:**
  - Key value propositions: Transparent processing, hands-on visa guidance, verified accommodations, responsive WhatsApp assistance.
* **FAQ Preview Section:**
  - 4 high-frequency FAQs in progressive disclosure accordion with `"View All 23 FAQs"` link.
* **Footer:**
  - Company address: `SF41, Ikorodu Complex, Ita Elewa, adjacent to High Court, Ikorodu, Lagos`.
  - Phone: `07017597650` | Direct WhatsApp link | Email placeholders.
  - Copyright, legal links (`Privacy Policy`, `Terms of Service`, `Refund Policy`).
  - Persistent Floating WhatsApp Button anchored at bottom right with accessible tooltip.

### 5.2 Services Page (`/services`)
* Hero explaining LAD Global's comprehensive travel facilitation.
* Detailed section for each of the 8 services:
  1. **Visa Assistance:** Guidance on checklist, documentation review, appointment booking. Explicit disclaimer: *Approvals are at the sole discretion of the respective embassy/consulate; no agent can guarantee a visa.*
  2. **Tour Packages:** Group, solo, and corporate bespoke itineraries.
  3. **Flight Booking Assistance (Enquiry-Only):** Domestic & international routing, itinerary planning, date adjustments. Badge: `Assistance & Enquiry Only — Not an Automated Booking Engine`.
  4. **Hotel Reservation Assistance (Enquiry-Only):** Curated lodging recommendations, group arrangements. Badge: `Assistance & Enquiry Only`.
  5. **Study Abroad Advisory:** University selection, admission advisory, student visa document guidance.
  6. **Canada Permanent Residency Guidance:** Express Entry and Provincial Nominee consultation, documentation roadmap.
  7. **Citizenship by Investment (CBI):** Legitimate global citizenship program advisory.
  8. **Residency by Investment (RBI):** European and international residency advisory.

### 5.3 Tour Packages Directory (`/packages`) & Detail (`/packages/{slug}`)
* **Directory (`/packages`):**
  - Destination filter tabs (e.g. *All*, *Africa*, *Asia*, *Middle East*, *Europe*).
  - Package Card: Cover image with alt tag, destination tag, duration pill, pricing tag (`₦XX,XXX per person` or `Request a Quote`), summary description, and action button.
  - Draft or archived packages are strictly excluded from public display.
* **Package Detail (`/packages/{slug}`):**
  - Header: Package Title, Destination Country & City, Duration, Status badge.
  - Image Gallery: Accessible lightbox or grid with descriptive alt texts.
  - Quick Info Bar: Price per person (or "Custom Quote"), Travel dates/validity, Group capacity note.
  - Tabbed or Linear Content:
    - *Overview & Highlights*
    - *Day-by-Day Itinerary*
    - *Inclusions (What's Covered)*
    - *Exclusions (What's Not Covered)*
    - *Booking & Payment Terms*
  - Conversion Sidebar:
    - If `is_payment_enabled == 1` & `price_amount > 0`: Direct Booking Form (Number of travellers, dynamic total preview, Name, Email, Phone, Payment method selector: Paystack vs Bank Transfer).
    - If `is_payment_enabled == 0`: Prominent `"Request a Custom Quote for this Package"` form.

### 5.4 Enquiry Form (`/enquire`)
* Single-page, friction-free form.
* Fields:
  - Full Name (required, sanitized)
  - Email Address (required, valid email pattern)
  - Phone / WhatsApp Number (required, phone format)
  - Service of Interest (Dropdown: Visa, Tours, Flight, Hotel, Study, Canada PR, CBI/RBI, General)
  - Destination (Optional text)
  - Estimated Travel / Start Date (Optional date picker)
  - Number of Travellers (Optional number)
  - Message / Requirements (Required textarea)
  - Honeypot hidden field (`website_url_check` for anti-bot)
  - Privacy Notice acknowledgment checkbox.
* Accessible validation: Inline red border and explanatory error text on invalid fields; green check on valid fields.

### 5.5 Minimal Admin Views
1. **Admin Login (`/admin/login`):**
   - Clean, centered login card with security notice.
   - Username/Email, Password, CSRF token, rate-limited feedback.
2. **Dashboard Overview (`/admin`):**
   - KPI metric tiles: Total Enquiries, New Enquiries (Today), Published Packages, Pending Bank Transfers awaiting review.
   - Quick Action: `"New Tour Package"`.
   - Table of Recent Enquiries with inline status changer (`new`, `contacted`, `quoted`, `closed`).
   - Table of Pending Manual Bank Transfers with direct link to audit/verify.
3. **Package Manager (`/admin/packages`):**
   - Table showing Title, Destination, Price, Status (`Draft`, `Published`, `Archived`), Featured toggle, Action buttons (`Edit`, `Unpublish`, `Archive`).
   - Deletion prevention: Packages linked to historical payments cannot be deleted; only archived.
4. **Enquiry Manager (`/admin/enquiries`):**
   - Search by name, phone, reference; filter by service type or status.
   - Detail view with internal notes log and timestamped audit history.
5. **Payment Audit Log (`/admin/payments`):**
   - Reference, Booking ID, Customer Name, Amount, Gateway (`Paystack` | `Manual Transfer`), Status (`Pending`, `Paid`, `Failed`), Verification Date.
   - Action for manual transfers: `"Mark as Verified Paid"` (requires external bank statement check, records admin user and timestamp) or `"Reject Transfer"`.

---

## 6. Accessibility & State Design Standards

### 6.1 Form Input States
* **Default:** Clean `#E2E8F0` border, `#FFFFFF` background, clear floating or stacked label.
* **Focus:** High-contrast 2px solid `#135EB0` outline with 2px offset (`:focus-visible`).
* **Valid:** `#0E7C3A` subtle border with accessible check icon.
* **Invalid:** `#C5221F` border, 2px focus ring, and descriptive error message below the input with `aria-describedby` and `role="alert"`.
* **Disabled:** `#EDF2F7` background, `#A0AEC0` text, non-interactive cursor.

### 6.2 Empty & Loading States
* **Empty Package List:** Friendly travel illustration with text: *"No packages currently published in this category. Contact us on WhatsApp for a custom itinerary!"*
* **Empty Enquiries / Search:** *"No enquiries match your search query."*
* **Submission Loading:** Buttons indicate processing state (`"Submitting Enquiry..."` or `"Connecting to Secure Payment..."`) with disabled click to prevent duplicate submissions.

### 6.3 Contrast & Readability
* All body copy meets **WCAG AA standard** (minimum 4.5:1 contrast ratio against background).
* Large headlines meet minimum 3:1 contrast ratio.
* Touch targets are sized at **48×48px** minimum for all primary navigation, buttons, and form inputs.
