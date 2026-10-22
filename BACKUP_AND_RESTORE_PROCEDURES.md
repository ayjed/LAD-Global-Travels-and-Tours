# ==============================================================================
# LAD GLOBAL TRAVELS AND TOURS — BACKUP AND RESTORATION PROCEDURES
# System Operations, Disaster Recovery & Rehearsal Runbook
# ==============================================================================

This runbook establishes standard operating procedures (SOP) for routine automated backups, emergency disaster recovery, and offline restoration rehearsals for the LAD Global Travels and Tours platform.

---

## 1. System Inventory & Critical Assets

The platform consists of three core persistent components that must be preserved:

| Component | Filesystem / Engine Location | Criticality | Recommended Frequency |
| :--- | :--- | :--- | :--- |
| **MySQL Database** | Database: `lad_travels` (all tables) | CRITICAL | Daily automated snapshot + before any schema migration |
| **Media Uploads** | `public/uploads/packages/` | HIGH | Daily / Sync on upload |
| **Environment Config** | `.env` (private outside webroot) | CRITICAL | Stored securely in password manager / offsite vault; backup on change |

---

## 2. Database Backup Procedures (`mysqldump`)

### A. Windows / PowerShell Automated Backup Script
Run the following script manually or via Windows Task Scheduler:

```powershell
# Backup-Database.ps1
$Date = Get-Date -Format "yyyyMMdd_HHmmss"
$BackupDir = "C:\Users\DELL\Desktop\MESH PROJECT\LAD Global Travels and Tours\storage\backups"
$DbUser = "root"
$DbPass = ""
$DbName = "lad_travels"
$OutputFile = "$BackupDir\db_backup_$Date.sql"

# Ensure backup directory exists
if (-not (Test-Path $BackupDir)) {
    New-Item -ItemType Directory -Path $BackupDir -Force | Out-Null
}

# Execute mysqldump with single-transaction and drop table statements
& "C:\xampp\mysql\bin\mysqldump.exe" --user=$DbUser --password=$DbPass --databases $DbName --single-transaction --quick --routines --triggers --add-drop-table > $OutputFile

Write-Host "Database backup successfully saved to: $OutputFile"
```

### B. Linux / cPanel Cron Job Backup Script
For cPanel / Ubuntu / Rocky Linux production servers:

```bash
#!/usr/bin/env bash
# backup_db.sh
set -euo pipefail

BACKUP_DIR="/home/username/backups/lad_travels"
DATE=$(date +%Y%m%d_%H%M%S)
DB_USER="cpanel_dbuser"
DB_PASS="StrongSecretPassword"
DB_NAME="cpanel_lad_travels"
TARGET="${BACKUP_DIR}/lad_travels_${DATE}.sql.gz"

mkdir -p "${BACKUP_DIR}"

# Stream dump directly into gzip compression
mysqldump --user="${DB_USER}" --password="${DB_PASS}" \
  --single-transaction --quick --routines --triggers \
  --add-drop-table "${DB_NAME}" | gzip > "${TARGET}"

# Retain last 30 daily backups
find "${BACKUP_DIR}" -type f -name "lad_travels_*.sql.gz" -mtime +30 -delete

echo "Database backup complete: ${TARGET}"
```

**cPanel Cron Configuration (Runs Daily at 02:00 AM):**
```cron
0 2 * * * /home/username/scripts/backup_db.sh >/dev/null 2>&1
```

---

## 3. Storage & Uploads Backup

The upload folder `public/uploads/` contains customer package brochures and tour imagery.

### PowerShell File Archive:
```powershell
$Date = Get-Date -Format "yyyyMMdd_HHmmss"
$Source = "public\uploads"
$Destination = "storage\backups\uploads_backup_$Date.zip"

Compress-Archive -Path $Source -DestinationPath $Destination -CompressionLevel Optimal
Write-Host "Uploads archived to $Destination"
```

### Linux Tarball Archive:
```bash
tar -czf /home/username/backups/uploads_$(date +%Y%m%d).tar.gz -C /home/username/public_html uploads/
```

---

## 4. Full Database Restoration Rehearsal Runbook

Follow these exact steps to rehearse or execute a complete disaster recovery restoration:

### Step 1: Pre-Restoration Safety Freeze
1. Temporarily place application in maintenance mode or notify admins to prevent concurrent writes.
2. If the current database is still accessible, generate an immediate pre-restore dump:
   ```bash
   mysqldump -u root -p lad_travels > pre_restore_snapshot.sql
   ```

### Step 2: Database Re-Creation
Create a fresh database (or drop existing test schema):
```sql
DROP DATABASE IF EXISTS lad_travels_rehearsal;
CREATE DATABASE lad_travels_rehearsal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 3: Execute Restoration Import
```powershell
# Windows PowerShell
Get-Content "storage\backups\db_backup_XXXXXXXX.sql" | & "C:\xampp\mysql\bin\mysql.exe" --user=root lad_travels_rehearsal
```

```bash
# Linux
gunzip < lad_travels_20261020_120000.sql.gz | mysql -u cpanel_dbuser -p cpanel_lad_travels
```

### Step 4: Post-Restoration Integrity Verification
Run the following SQL sanity queries to confirm table rows and data integrity:

```sql
-- 1. Verify schema tables exist
SHOW TABLES;

-- 2. Check record counts across primary entities
SELECT 
    (SELECT COUNT(*) FROM admins) AS admin_count,
    (SELECT COUNT(*) FROM packages) AS package_count,
    (SELECT COUNT(*) FROM enquiries) AS enquiry_count,
    (SELECT COUNT(*) FROM bookings) AS booking_count,
    (SELECT COUNT(*) FROM payments) AS payment_count,
    (SELECT COUNT(*) FROM audit_logs) AS audit_log_count,
    (SELECT COUNT(*) FROM settings) AS settings_count;

-- 3. Verify latest admin credentials work
SELECT id, username, email, is_active FROM admins WHERE is_active = 1;

-- 4. Verify payment references and amounts are intact
SELECT id, payment_reference, amount, currency, status FROM payments ORDER BY id DESC LIMIT 5;
```

### Step 5: Test Application Connectivity
Point `.env` temporarily to the restored database:
```env
DB_NAME=lad_travels_rehearsal
```
Run the automated test suite to ensure 100% operational functionality:
```bash
C:\xampp\php\php.exe tests/SmokeTest.php
C:\xampp\php\php.exe tests/BookingPaymentTest.php
```

---

## 5. Emergency Rollback Strategy

If an erroneous deployment or corruption event occurs:
1. **Revert Filesystem:** Restore application files from the last stable release branch.
2. **Revert Database:** Import the pre-deployment SQL snapshot captured in Step 1.
3. **Re-link Uploads:** Restore `public/uploads` from archive if media files were deleted or overwritten.
4. **Purge Cache & Re-run Tests:** Ensure `storage/logs/` is writable and execute `tests/SmokeTest.php`.

---

## 6. Security & Storage Best Practices

1. **Offsite Vaulting:** Never store backups exclusively on the webserver. Transfer encrypted copies to AWS S3, Google Drive, or an external hard drive weekly.
2. **Permission Guarding:** Restrict `storage/backups/` permissions to `0700` (`chmod 700`) on Linux so unauthorized web users or cPanel users cannot read database dumps.
3. **No Plaintext Passwords in Git:** Scripts containing database passwords must be excluded via `.gitignore`.
