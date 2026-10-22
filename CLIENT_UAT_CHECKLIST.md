# ==============================================================================
# LAD GLOBAL TRAVELS AND TOURS — CLIENT USER ACCEPTANCE TESTING (UAT) CHECKLIST
# Phase P09 Functional & Operational Acceptance Test Script
# ==============================================================================

**Client / Reviewer:** Mr. Olanrewaju / LAD Global Travels & Tours Management  
**Application URL:** `http://127.0.0.1:8000` (Local Test Environment) / Production Domain  
**Target Milestone:** Phase P09 / Pre-Deployment Readiness

---

## 1. Instructions for the Client Tester

This checklist guides you through testing every aspect of your travel website before public launch. No technical knowledge is required. Follow the steps in each scenario on both **desktop** and **mobile (smartphone)** and mark whether the system behaves as expected.

---

## 2. Acceptance Testing Scenarios

### Scenario 1: Brand Identity & Public Homepage Browsing
- [ ] **Step 1.1:** Open the homepage (`/`). Verify the LAD Global Travels logo, company name, and tagline (*Explore • Discover • Experience*) display prominently.
- [ ] **Step 1.2:** Verify the hero section highlights international travel planning, visa assistance, and curated tour packages.
- [ ] **Step 1.3:** Verify the 8 core services preview cards are clearly visible.
- [ ] **Step 1.4:** Verify the featured tour packages grid appears and links to package detail pages.
- [ ] **Step 1.5:** Verify the FAQ accordion opens and closes smoothly when clicking questions.
- [ ] **Step 1.6:** Verify the footer displays the registered Ikorodu office address, official phone number (`+234 701 759 7650`), operating hours, and social media links.
- *Tester Notes:* ____________________________________________________

---

### Scenario 2: Service Inquiries & WhatsApp Handshake
- [ ] **Step 2.1:** Click the persistent green **Chat on WhatsApp** floating button on the bottom right. Verify WhatsApp opens with a pre-filled enquiry message addressed to your official number.
- [ ] **Step 2.2:** Navigate to the **Contact Us** page (`/contact`). Verify all phone, address, and email links are clickable (`tel:` and `mailto:`).
- [ ] **Step 2.3:** Submit a test enquiry form with Name, Email, Phone, and Travel Message.
- [ ] **Step 2.4:** Verify a success confirmation banner appears on screen.
- [ ] **Step 2.5:** Submit the form again with an invalid email address (e.g., `test@`). Verify the form rejects the submission and displays a clear red validation warning.
- *Tester Notes:* ____________________________________________________

---

### Scenario 3: Tour Packages Exploration & Itinerary
- [ ] **Step 3.1:** Navigate to **Tour Packages** (`/packages`). Verify published tour packages are displayed with destination, duration, and price.
- [ ] **Step 3.2:** Click on any package card. Verify the detail page (`/packages/{slug}`) displays full tour overview, itinerary, inclusions, exclusions, and travel dates.
- [ ] **Step 3.3:** If a package has payments enabled, verify the prominent **Book This Tour** button appears.
- [ ] **Step 3.4:** If a package is inquiry-only, verify the button says **Enquire About This Tour** and links to the consultation form.
- *Tester Notes:* ____________________________________________________

---

### Scenario 4: Guest Package Booking with Bank Transfer (Manual Reconcile)
- [ ] **Step 4.1:** On an eligible package, click **Book This Tour**.
- [ ] **Step 4.2:** Enter Lead Traveller Name, Email, Phone, and select 2 travellers. Notice the total price calculates automatically on the server (2 × Package Price).
- [ ] **Step 4.3:** Select payment method: **Direct Bank Transfer**.
- [ ] **Step 4.4:** Click **Proceed to Payment**. Verify you are redirected to the Transfer Instructions page (`/booking/{ref}/transfer`).
- [ ] **Step 4.5:** Verify company bank name, account name, and account number are clearly shown with payment reference as narration instructions.
- [ ] **Step 4.6:** Enter a mock transfer memo (e.g., *“Sent from GTBank ref 12345”*) and click **Submit Confirmation Memo**. Verify success message displays.
- *Tester Notes:* ____________________________________________________

---

### Scenario 5: Guest Package Booking with Paystack Checkout (Test Mode)
- [ ] **Step 5.1:** Start a new package booking and select payment method: **Debit / Credit Card (Paystack)**.
- [ ] **Step 5.2:** Click **Proceed to Payment**. Verify you are redirected to the secure Paystack checkout modal/page.
- [ ] **Step 5.3:** Complete the transaction using a Paystack test card (e.g., `4084 0840 ...`).
- [ ] **Step 5.4:** Verify you are returned to the official Booking Status page (`/booking/{ref}`) showing payment status **PAID**.
- [ ] **Step 5.5:** Verify the on-screen notice clearly states: *“Payment receipt issued. Your travel booking is currently pending logistical confirmation by our travel consultants.”* (Adherence to SOT 8.4 & D11).
- *Tester Notes:* ____________________________________________________

---

### Scenario 6: Customer Booking Status Lookup & Receipt Printing
- [ ] **Step 6.1:** Access the Booking Status page using your booking reference (`/booking/{ref}`).
- [ ] **Step 6.2:** Click **View Official Receipt** (`/booking/{ref}/receipt`).
- [ ] **Step 6.3:** Verify the printable receipt displays customer name, package title, reference number, amount paid, and statutory agency disclaimer.
- [ ] **Step 6.4:** Click the **Print Receipt** button and verify the browser print dialog opens cleanly without navigation bars.
- *Tester Notes:* ____________________________________________________

---

### Scenario 7: Administrator Login, Session Security & Logout
- [ ] **Step 7.1:** Navigate to the admin portal (`/admin/login`).
- [ ] **Step 7.2:** Attempt login with incorrect password. Verify access is denied with a generic error message and throttled after multiple failed attempts.
- [ ] **Step 7.3:** Sign in with authorized admin credentials. Verify you are redirected to the Admin Dashboard (`/admin`).
- [ ] **Step 7.4:** Click **Sign Out**. Verify you are securely logged out and subsequent access to `/admin` redirects back to login.
- *Tester Notes:* ____________________________________________________

---

### Scenario 8: Admin Tour Package Management
- [ ] **Step 8.1:** Go to **Tour Packages** in admin (`/admin/packages`). Click **+ New Tour Package**.
- [ ] **Step 8.2:** Fill in title, destination, pricing, description, inclusions, and upload a cover photo. Save as **Draft**.
- [ ] **Step 8.3:** Verify the draft package appears in the admin list with a grey Draft badge and does **NOT** appear on the public website.
- [ ] **Step 8.4:** Edit the package, change status to **Published**, and verify it immediately displays on the public `/packages` page.
- [ ] **Step 8.5:** Click **Archive**. Verify it is safely removed from public display.
- *Tester Notes:* ____________________________________________________

---

### Scenario 9: Admin Enquiry CRM & Notes
- [ ] **Step 9.1:** Go to **Enquiries & Leads** (`/admin/enquiries`). Verify the test enquiry submitted in Scenario 2 appears in the list.
- [ ] **Step 9.2:** Click on the enquiry to view details.
- [ ] **Step 9.3:** Update status to **Quoted** and set a follow-up date.
- [ ] **Step 9.4:** Add an internal note (e.g., *“Customer interested in Canada student visa, scheduled call for Friday”*). Verify the note is saved with timestamp.
- *Tester Notes:* ____________________________________________________

---

### Scenario 10: Admin Payment Verification & Travel Confirmation
- [ ] **Step 10.1:** Go to **Payments & Audit** (`/admin/payments`). Find the pending manual bank transfer from Scenario 4.
- [ ] **Step 10.2:** Review customer transfer memo and verify customer payment. Enter bank statement reference remarks and click **Verify Payment**.
- [ ] **Step 10.3:** Verify payment status updates to **Paid** and audit log records the action.
- [ ] **Step 10.4:** Go to **Bookings** (`/admin/bookings`) and click on the associated booking.
- [ ] **Step 10.5:** Update travel status from Pending to **Confirmed** with notes (e.g., *“Flight tickets and hotel voucher issued”*).
- *Tester Notes:* ____________________________________________________

---

### Scenario 11: Admin Protected Settings & Bank Details (Decision D04)
- [ ] **Step 11.1:** Go to **Settings** (`/admin/settings`). Review Company Details, Social Links, and Email Routing tabs.
- [ ] **Step 11.2:** Switch to **Protected Bank Details** tab. Notice the warning banner and masked account number representation.
- [ ] **Step 11.3:** Attempt to change the bank account number without checking the **Client Authorization Certification** checkbox. Verify the update is blocked.
- [ ] **Step 11.4:** Check the authorization box, enter the verified corporate bank details, and submit. Verify success banner displays and audit log records the change with masked account number (`******5678`).
- *Tester Notes:* ____________________________________________________

---

### Scenario 12: Mobile Experience & Responsiveness
- [ ] **Step 12.1:** Open the website on an iPhone / Android smartphone (or resize desktop browser to 375px width).
- [ ] **Step 12.2:** Verify the mobile hamburger menu opens and closes smoothly.
- [ ] **Step 12.3:** Verify all buttons and touch targets are easy to tap with one thumb.
- [ ] **Step 12.4:** Verify package cards and booking forms fit on screen without awkward horizontal scrolling.
- *Tester Notes:* ____________________________________________________

---

## 3. Client Formal Sign-Off & Defect Deferral Record

*(To be signed upon client completion of the above test scenarios)*

### Acceptance Status:
- [ ] **ACCEPTED:** The system meets all agreed requirements and is ready for Phase P10 Production Handover.
- [ ] **ACCEPTED WITH DEFERRED ITEMS:** Minor cosmetic or non-critical items noted below to be addressed post-launch.
- [ ] **REJECTED / ACTION REQUIRED:** Critical defects identified that must be resolved prior to launch.

### Deferred Items / Client Feedback:
```text
1. __________________________________________________________________
2. __________________________________________________________________
3. __________________________________________________________________
```

### Signatures:

**Client / Project Owner Name:** __________________________________  
**Client Signature:** _____________________________________________  
**Sign-off Date:** _____ / _____ / 2026  

**Lead Developer Signature:** ______________________________________  
**Date:** 2026-10-20  
