# ==============================================================================
# LAD GLOBAL TRAVELS AND TOURS — ADMIN OPERATIONS AND USAGE GUIDE
# Comprehensive Administrator Manual & Operating Handbook
# ==============================================================================

**Target Audience:** Authorized Agency Administrators, Operations Staff, and Executive Management (Mr. Olanrewaju).  
**Admin Portal Access:** `https://ladglobaltravels.com/admin/login` (or `http://127.0.0.1:8000/admin/login` on local test environment).

---

## 1. Administrative Security & Authentication

### A. Signing In
1. Open the secure login page at `/admin/login`.
2. Enter your authorized administrator username or email address and password.
3. Click **Sign In to Dashboard**.
4. **Security Notice:**
   - The portal enforces strict rate limiting. After **5 failed login attempts**, access from your IP is temporarily locked out for 15 minutes to prevent brute-force attacks.
   - For security, sessions automatically expire after **2 hours of inactivity**, requiring you to sign in again.

### B. Signing Out
Always click **🔒 Sign Out** at the bottom of the left sidebar when stepping away from your computer. This immediately destroys your authenticated session and clears session cookies.

---

## 2. Admin Dashboard & Real-Time Metrics

Upon signing in, you are greeted with the Executive Overview dashboard (`/admin`):

- **📦 Active Packages:** Total number of tour packages currently published and visible to the public.
- **💬 New Inquiries:** Total number of new customer leads awaiting initial consultation or follow-up.
- **✈️ Total Bookings:** Total number of guest tour bookings created across all payment methods.
- **💳 Total Revenue Collected:** Cumulative verified revenue collected from completed Paystack card payments and verified bank transfers.
- **⚡ Pending Manual Payments Queue:** A prioritized list of direct bank transfers awaiting your verification against corporate bank account statements.

---

## 3. Tour Package Management (`/admin/packages`)

The platform allows administrators to manage tour packages without technical coding knowledge.

### A. Creating a New Package (`/admin/packages/create`)
1. Click **+ New Tour Package** in the top navigation or packages index.
2. Complete the primary details:
   - **Tour Title:** Descriptive, engaging name (e.g., *“6-Day Zanzibar Coastal Safari & Spice Experience”*).
   - **Destination Country & City:** Country and primary destination city.
   - **Summary:** Short 1–2 sentence highlight displayed on package cards.
   - **Duration:** Number of days (e.g., `6`).
   - **Travel Dates (Optional):** Fixed travel window (e.g., *“December 15 – December 21, 2026”*) or leave blank for flexible tours.
   - **Price (NGN) & Currency:** The per-person cost in Nigerian Naira (e.g., `650000`).
   - **Enable Online Payments:** Check this box if customers can pay online or via direct bank transfer. If unchecked, the package will display an **Enquire About This Tour** consultation button instead.
   - **Featured on Homepage:** Check to display this package in the homepage highlights.
3. Provide Detailed Content:
   - **Full Description:** Comprehensive tour overview and highlights.
   - **Inclusions:** Enter items included in the price (e.g., *“4-Star Hotel Accommodation, Daily Breakfast, Airport Transfers, Safari Game Drives”*), separated by newlines.
   - **Exclusions:** Enter items excluded (e.g., *“International Flights, Personal Travel Insurance, Discretionary Tips”*), separated by newlines.
   - **Itinerary:** Day-by-day schedule (e.g., *“Day 1: Arrival & Welcome Dinner\nDay 2: Historic Stone Town Walking Tour”*).
4. Upload Cover Image:
   - Choose a high-quality JPG, PNG, or WebP photo (maximum size: 5 MB).
   - The system automatically validates binary contents, randomizes the file name for security, and stores it in `/uploads/packages/`.
5. Set Status:
   - **Draft:** Saves package internally for review. It will **NOT** appear on the public website.
   - **Published:** Makes package immediately visible and bookable on `/packages`.

### B. Editing and Updating Packages
- Click **Edit** next to any package.
- Modify pricing, itinerary, inclusions, or status and click **Save Changes**.
- **Important:** If you change a package price, all previously completed bookings remain frozen at their historical price to maintain strict accounting integrity.

### C. Deletion Protection vs. Archiving
- Packages that have linked customer bookings or payments **CANNOT** be destructively deleted. The system enforces this safeguard to protect financial records and audit histories.
- To retire an old package, set its status to **Archived**. Archived packages are immediately hidden from public search engines and website visitors while preserving all historical records in your database.

---

## 4. Enquiries & Customer CRM (`/admin/enquiries`)

Manage all customer leads submitted through the website's consultation forms.

### A. Filtering & Searching Inquiries
- Filter by status tabs: **All**, **New Leads**, **Contacted**, **Quoted**, **Closed**.
- Filter by Service Category: Visa Advisory, Tour Packages, Flight Booking Assistance, Hotel Reservations, Study Abroad, Canada PR, Citizenship by Investment (CBI), Residency by Investment (RBI).
- Search by customer name, email, phone number, or unique reference code (e.g., `ENQ-36C72A45`).

### B. Consultation Workflow & Notes
1. Click **View** on an enquiry.
2. Review customer travel preferences, passport country, destination, and message.
3. **One-Click Contact:**
   - Click the phone number (`tel:`) to dial immediately from your phone or softphone.
   - Click **💬 Chat on WhatsApp** to open a direct WhatsApp conversation with a pre-filled professional greeting.
4. **Update Status & Follow-up Date:**
   - Change status to **Contacted** after initial outreach, or **Quoted** after sending a travel proposal.
   - Set a **Follow-up Date** to schedule your next consultation check-in.
5. **Internal Consultation Notes:**
   - Use the **Add Consultation Note** box to record conversation history (e.g., *“Customer requested quote for 3 family members to Dubai. Sent itinerary via email, awaiting passport copies”*).
   - Notes are permanently timestamped and attributed to your administrator account.

---

## 5. Booking Management & Payment Reconciliation (`/admin/bookings`, `/admin/payments`)

### A. Understanding the Two Payment Methods
1. **Paystack Online Card Payment:** Customers pay online with debit/credit cards. The transaction is instantly verified server-to-server via webhook, marks the payment `paid`, and issues an official payment receipt.
2. **Direct Bank Transfer:** Customers receive corporate bank account instructions (`pending_manual` status) and submit a transfer narration memo.

### B. Reconciling Direct Bank Transfers (Critical Operational Procedure)
1. Go to **💳 Payments & Audit** (`/admin/payments`) and click the **Pending Transfer Queue** tab.
2. Inspect the customer's payment reference (e.g., `PAY-67906A6EE62E6441`), amount, and customer transfer memo (e.g., *“Transfer from GTBank Adebayo ref 994821”*).
3. **Check Your Corporate Bank Account:**
   - Log in to your corporate bank portal (e.g. Zenith Bank).
   - Verify that the exact amount has credited your account with the matching reference.
4. **Approve Payment:**
   - Enter your verification notes in the text box (e.g., *“Verified credit on Zenith Bank statement on 2026-10-20, transaction ref #994821”*).
   - Click **Verify Payment**.
   - Payment status immediately transitions to **Paid**, customer receives an automated confirmation receipt, and an irreversible record is written to `audit_logs`.
5. **Reject Payment:**
   - If funds were not received or are fraudulent, enter the reason (e.g., *“No matching credit found on bank statement after 48 hours”*) and click **Reject Payment**.

### C. Independent Travel Booking Confirmation (SOT Section 8.4 & Decision D11)
- **Legal Invariant:** In accordance with Nigerian aviation regulations and travel agency best practice, a **payment receipt does NOT constitute a confirmed travel ticket**.
- When a customer pays, their payment status becomes **PAID**, but their travel booking status remains **PENDING**.
- Once your travel consultants have secured airline reservations, hotel vouchers, and visa approvals:
  1. Open the booking in `/admin/bookings/{id}`.
  2. Change Travel Status to **Confirmed**.
  3. Enter confirmation remarks (e.g., *“Kenya Airways PNR #KL892A and PrideInn Hotel voucher #9948 issued and emailed”*).
  4. Click **Update Travel Status**.

---

## 6. Protected Company Settings & Bank Details (`/admin/settings`)

Manage website contact details, official social links, and corporate bank credentials.

### A. General & Social Settings
- **Company Name, Phone, WhatsApp:** Updates the public telephone links, header contact bar, and floating WhatsApp widget across all pages.
- **Physical Office Address:** Updates the official address in the footer and contact page.
- **Official Social URLs:** Enter full URLs for Instagram, Facebook, and LinkedIn. Format validation ensures invalid links cannot be saved.

### B. Updating Corporate Bank Details (Decision D04 Security Safeguard)
Because bank details determine where customer payments are routed, edits are strictly protected:
1. Navigate to the **Protected Bank Details** tab.
2. Notice the yellow financial security banner and that account numbers are displayed in masked format (`******5678`) to prevent exposing banking details.
3. To update the bank name, account name, or account number:
   - Enter the verified corporate bank details.
   - **Mandatory Step:** Check the **Client Authorization Certification** checkbox certifying: *“I certify that these bank account details have been formally verified with and authorized by the business owner (Mr. Olanrewaju) for live customer payments.”*
   - Click **Save Protected Bank Details**.
4. The system validates the 10-digit NUBAN format, persists the update, and records a permanent entry in `audit_logs` attributing the action to your administrator account while masking the account number in the logs.
