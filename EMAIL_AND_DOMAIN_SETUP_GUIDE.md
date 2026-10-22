# LAD GLOBAL TRAVELS AND TOURS — BUSINESS EMAIL, DOMAIN & DNS SETUP GUIDE

> **Document ID:** LG-GUIDE-001  
> **Phase:** P08 — Contact, bank, business email and social settings  
> **Source of Truth Reference:** `PROJECT_SOURCE_OF_TRUTH.md` (Sections 11 & 16: Decisions D02 & D09)  
> **Compliance Notice:** This document outlines the technical specification and operational instructions for configuring business email, domain DNS, and deliverability protocols. **In accordance with project governance, no domain has been purchased and no external mailboxes have been created without explicit client authorization and credentials.**

---

## 1. Domain & Mailbox Architecture Overview

The Source of Truth (§11) specifies five designated business addresses tailored to the client's registered domain:

| Address | Primary Role | Inbound Routing in Application | Recommended Type |
|---|---|---|---|
| `info@<domain>` | Primary company correspondence, general queries, and public sender address for customer receipts | Outgoing default sender (`mail_from_address`) | Primary Mailbox / Shared Inbox |
| `sales@<domain>` | Tour packages, holiday bookings, domestic/international flight assistance, hotel enquiries | Inbound destination for tour enquiries (`notification_email_enquiries`) | Inbox or Alias to Sales Team |
| `visa@<domain>` | Visa assistance, study abroad advisory, Canada PR guidance, CBI/RBI advisory | Inbound destination for visa/mobility enquiries (`notification_email_visa`) | Inbox or Alias to Visa Consultant |
| `admin@<domain>` | System alerts, payment reconciliation notifications, audit log digests | Inbound destination for payment/reconciliation alerts (`notification_email_payments`) | Primary Mailbox / Webmaster |
| `lanre@<domain>` | Principal officer & executive management escalations | High-priority management escalation destination (`notification_email_management`) | Dedicated Executive Mailbox |

---

## 2. Mailbox Deployment Models: Paid vs. Aliases

Before provisioning accounts, the client and project owner must decide between two primary hosting architectures:

### Option A: Dedicated Independent Mailboxes (Google Workspace / Microsoft 365 / Zoho Workplace)
* **Structure:** 5 individual billable accounts (`info`, `sales`, `visa`, `admin`, `lanre`).
* **Pros:** Complete isolation, separate login credentials for staff members, independent Google Drive / OneDrive storage, highest deliverability reputation.
* **Cons:** Monthly license cost per user (e.g., $6/user/mo for Google Workspace = $30/month).
* **Recommendation:** Ideal if LAD Global Travels operates with distinct staff members managing tours vs. visas on separate computers.

### Option B: Unified Core Mailbox + Free Domain Aliases (cPanel Webmail or Zoho Mail Free Tier)
* **Structure:** 1 or 2 primary mailboxes (e.g., `info@<domain>` and `lanre@<domain>`), with `sales@`, `visa@`, and `admin@` configured as **free email forwarders/aliases** pointing to `info@` or the specific manager's address.
* **Pros:** **Zero incremental cost** on standard cPanel/LiteSpeed hosting accounts; simple centralized inbox monitoring; outgoing emails can still use `info@` as the unified brand identity.
* **Cons:** All messages arrive in the shared inbox; requires email filters or labels to separate sales from visa requests.
* **Recommendation:** **Highly recommended for MVP launch** to minimize operational expenses before volume justifies dedicated licenses.

---

## 3. Mandatory DNS Records: MX, SPF, DKIM, and DMARC

To guarantee that outgoing emails (enquiry acknowledgments, bank transfer instructions, and official payment receipts) land directly in customer inboxes rather than spam folders, the following DNS records must be added to the domain's DNS zone editor:

### 3.1 MX (Mail Exchange) Records
Directs incoming mail to the designated email server.

* **For cPanel / Shared Hosting:**
  ```text
  Type:     MX
  Host:     @ (or ladglobal.com)
  PointsTo: mail.ladglobal.com
  Priority: 0
  TTL:      3600
  ```
* **For Google Workspace:**
  ```text
  Priority 1:  ASPMX.L.GOOGLE.COM
  Priority 5:  ALT1.ASPMX.L.GOOGLE.COM
  Priority 5:  ALT2.ASPMX.L.GOOGLE.COM
  Priority 10: ALT3.ASPMX.L.GOOGLE.COM
  Priority 10: ALT4.ASPMX.L.GOOGLE.COM
  ```

---

### 3.2 SPF (Sender Policy Framework) Record
Authorizes specific mail servers to send email on behalf of your domain, preventing spammers from forging your company name.

* **Standard cPanel / VPS Sending:**
  ```text
  Type:  TXT
  Host:  @ (or ladglobal.com)
  Value: v=spf1 +a +mx +ip4:<YOUR_SERVER_IP> ~all
  TTL:   3600
  ```
* **Google Workspace + Web Server Hybrid:**
  ```text
  Type:  TXT
  Host:  @
  Value: v=spf1 include:_spf.google.com +ip4:<YOUR_SERVER_IP> ~all
  TTL:   3600
  ```

---

### 3.3 DKIM (DomainKeys Identified Mail)
Provides a digital signature embedded into outgoing message headers so recipient mailboxes (Gmail, Yahoo, Outlook) can verify the message was not tampered with in transit.

* **Implementation in cPanel:**
  1. Log in to cPanel.
  2. Navigate to **Email Deliverability**.
  3. Locate the primary domain and click **Manage**.
  4. Copy the generated `default._domainkey` TXT record (2048-bit RSA public key).
  5. Ensure the record is active in DNS:
     ```text
     Type:  TXT
     Host:  default._domainkey
     Value: v=DKIM1; k=rsa; p=MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA...
     TTL:   3600
     ```

---

### 3.4 DMARC (Domain-based Message Authentication, Reporting, and Conformance)
Specifies policy enforcement if an email fails SPF or DKIM checks, preventing fraudulent phishing attacks impersonating LAD Global Travels.

* **Recommended Phase 1 (Monitoring Policy):**
  ```text
  Type:  TXT
  Host:  _dmarc
  Value: v=DMARC1; p=none; sp=none; rua=mailto:admin@ladglobal.com; pct=100; adkim=r; aspf=r
  TTL:   3600
  ```
* **Recommended Phase 2 (Hardened Quarantine Policy after 30 days):**
  ```text
  Type:  TXT
  Host:  _dmarc
  Value: v=DMARC1; p=quarantine; sp=quarantine; rua=mailto:admin@ladglobal.com; pct=100
  TTL:   3600
  ```

---

## 4. Application SMTP Configuration (`.env`)

In production, outgoing transactional notifications are sent via pure native PHP socket SMTP (`App\Services\MailService`). Configure the following environment variables in `.env` (kept strictly private outside the public webroot):

```ini
# Outbound Email SMTP Settings (Phase P08)
SMTP_HOST=mail.ladglobal.com
SMTP_PORT=587
SMTP_USERNAME=info@ladglobal.com
SMTP_PASSWORD="YOUR_SECURE_GENERATED_PASSWORD"
SMTP_ENCRYPTION=tls
SMTP_FROM_ADDRESS=info@ladglobal.com
SMTP_FROM_NAME="LAD Global Travels and Tours"

# Notification Recipient Routing
NOTIFICATION_EMAIL_ENQUIRIES=sales@ladglobal.com
NOTIFICATION_EMAIL_VISA=visa@ladglobal.com
NOTIFICATION_EMAIL_PAYMENTS=admin@ladglobal.com
NOTIFICATION_EMAIL_MANAGEMENT=lanre@ladglobal.com
```

### Safety Fallback:
If SMTP is temporarily unavailable or misconfigured, `MailService` automatically:
1. Logs the failure with structured timestamp to `storage/logs/mail.log`.
2. Falls back to native PHP `mail()` if a local MTA is active.
3. **Never aborts or blocks customer enquiry submissions or booking creations.**

---

## 5. Verification & Testing Checklist

Once the domain DNS and mailboxes have been provisioned by the client:

1. [ ] **MX Resolution:** Run `nslookup -type=MX ladglobal.com` to verify mail exchange records.
2. [ ] **SPF Verification:** Check domain on [mxtoolbox.com/spf.aspx](https://mxtoolbox.com/spf.aspx).
3. [ ] **DKIM Alignment:** Send test email to `check-auth@verifier.port25.com` or [mail-tester.com](https://www.mail-tester.com).
4. [ ] **Test Form Dispatch:** Submit an enquiry on `/contact` and verify receipt in `sales@` and confirmation in customer inbox.
5. [ ] **Test Payment Receipt Dispatch:** Trigger a test bank transfer verification in `/admin/bookings/{id}` and confirm receipt lands in customer inbox with SOT Section 8.4 statutory travel separation notice.
