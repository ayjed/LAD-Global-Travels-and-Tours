# ==============================================================================
# LAD GLOBAL TRAVELS AND TOURS — CREDENTIAL HANDOVER & SECURITY PROCEDURE
# Production Secrets Inventory, Secure Transfer Protocol & Post-Handover Runbook
# ==============================================================================

**Client / Recipient:** Mr. Olanrewaju (Managing Director / Project Owner)  
**Handing-Over Engineer:** Lead Developer & System Architect  
**Project:** LAD Global Travels and Tours Official Web Platform  

---

## 1. Principles of Secure Handover

Production credentials control customer financial data, corporate bank communications, and domain integrity. To prevent credential compromise or unauthorized access, this handover strictly follows the following security standards:

1. **Zero Plaintext Transmission:** Passwords, API keys, and database credentials must **NEVER** be sent via plaintext email, SMS, or open WhatsApp chats.
2. **Secrets Isolation:** No credentials or API keys are stored in the git repository or exposed in source code.
3. **Immediate Client Password Rotation:** Following receipt, the client must rotate all master passwords to establish exclusive client ownership.
4. **Principle of Least Privilege:** Developer temporary administrative accounts are permanently deactivated post-launch.

---

## 2. Production Credentials Inventory

The following distinct credential sets constitute the complete operational access suite for LAD Global Travels and Tours:

| Credential Category | Service / System | Primary Purpose | Required Privileges |
| :--- | :--- | :--- | :--- |
| **1. Domain & DNS** | Domain Registrar (Namecheap, Whogohost, GoDaddy) | Manages domain names (`ladglobaltravels.com`), nameservers, and DNS records (MX, SPF, DKIM, DMARC) | Master Account Owner |
| **2. Web Hosting / cPanel** | cPanel / Linux Web Hosting Server | Controls Apache, PHP 8.2 runtime, SSL certificates, cron jobs, file management, and mailboxes | Master Account Owner |
| **3. MySQL Database** | Production Database (`lad_travels`) | Houses package inventory, enquiries, bookings, payment records, settings, and audit logs | DB User with ALL PRIVILEGES |
| **4. Payment Gateway** | Paystack Live Merchant Portal | Processes live customer debit/credit card payments and settles funds directly to corporate bank | Merchant Owner (Live API Keys) |
| **5. Corporate Mailboxes** | Outbound SMTP & IMAP/POP3 | Manages the 5 SOT inboxes: `info@`, `sales@`, `visa@`, `admin@`, `lanre@` | Individual Mailbox Passwords |
| **6. Website Admin Portal** | LAD Global Admin Console (`/admin`) | Manages tour packages, customer enquiries, manual transfer reconciliation, and settings | Super Administrator |

---

## 3. Approved Secure Transmission Protocol

To transfer the credentials listed above to Mr. Olanrewaju:

### Primary Method: Encrypted Password Vault (Recommended)
1. Store all credentials in an encrypted 1Password or Bitwarden Secure Vault.
2. Generate a time-limited, password-protected Secure Share Link (expires in 24 hours or after single view).
3. Transmit the secure share link via private Signal message or direct client SMS.
4. Transmit the passphrase to decrypt the link via a separate out-of-band communication channel (direct telephone call).

### Fallback Method: AES-256 Encrypted Archive
1. Place the master credentials document into a text file: `lad_credentials_master.txt`.
2. Compress using 7-Zip with **AES-256 encryption** and a high-entropy 24-character passphrase.
3. Deliver the encrypted `.7z` file to Mr. Olanrewaju via direct USB drive or private secure transfer.
4. Provide the decryption passphrase verbally during an in-person or direct telephone handover session.

---

## 4. Immediate Post-Handover Rotation Checklist

Immediately upon receiving production access, Mr. Olanrewaju must execute the following rotation steps:

- [ ] **Step 4.1: Rotate cPanel Password:** Log in to cPanel, navigate to *User Manager > Change Password*, and set a new 20+ character master password.
- [ ] **Step 4.2: Enable Two-Factor Authentication (2FA) on Paystack:** Log in to [dashboard.paystack.com](https://dashboard.paystack.com/), go to *Settings > Profile*, and enable Two-Factor Authentication via Google Authenticator or SMS.
- [ ] **Step 4.3: Verify Paystack Settlement Bank Account:** In the Paystack Dashboard under *Settings > Bank Accounts*, verify that the designated payout bank account matches your authorized corporate Zenith Bank account.
- [ ] **Step 4.4: Rotate MySQL Database Password:** In *cPanel > MySQL Databases*, change the database user password and update the `DB_PASSWORD` value in `/home/username/lad_travels/.env`.
- [ ] **Step 4.5: Rotate Corporate Mailbox Passwords:** In *cPanel > Email Accounts*, set new secure passwords for `info@`, `sales@`, `visa@`, `admin@`, and `lanre@`. Update `SMTP_PASSWORD` in `.env` accordingly.
- [ ] **Step 4.6: Rotate Super Administrator Password:** Log in to `/admin`, navigate to your profile settings, and update the master administrator password.
- [ ] **Step 4.7: Revoke Developer Temporary Access:** Verify that all temporary development accounts or SSH keys created during the setup phase are removed or disabled.

---

## 5. Formal Handover Acceptance Declaration

*(To be signed upon completion of credential transfer and verification)*

**Developer Declaration:**  
I hereby certify that all production systems, source code, database schemas, and administrative credentials for LAD Global Travels and Tours have been transferred securely to the client in accordance with this protocol, and that no unencrypted copies of live secrets remain retained.

**Developer Signature:** ______________________________________  
**Date:** 2026-10-20  

**Client Declaration:**  
I, Mr. Olanrewaju, hereby acknowledge receipt of the master credentials inventory for LAD Global Travels and Tours, confirm that I have verified access to the systems, and understand the instructions for immediate password rotation and 2FA activation.

**Client Signature:** _________________________________________  
**Date:** _____ / _____ / 2026  
