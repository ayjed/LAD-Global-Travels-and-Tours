# LAD Global Travels and Tours — cPanel Deployment Guide

This guide provides simple, step-by-step instructions to upload and launch **LAD Global Travels and Tours** on your cPanel hosting.

---

## What is in this Package?
You have a ready-to-upload zip file:
- **`lad_travels_cpanel_upload.zip`** (Contains all website source code, high-resolution assets, icons, admin portal, and database schemas).
- **`database/lad_global_production.sql`** (Contains the full database schema, system settings, and 5 published tour packages ready to import).
- **`.env.production`** (Production configuration template).

---

## Step 1: Upload the Zip File to cPanel

1. Log into your **cPanel Account** (usually `https://yourdomain.com:2083` or via your hosting client area).
2. Open **File Manager** (under the *Files* section).
3. Decide on your upload location:
   - **Method A (Easiest — Standard `public_html`):**
     - Navigate into the **`public_html`** folder.
     - Click **Upload** in the top toolbar.
     - Select and upload **`lad_travels_cpanel_upload.zip`**.
     - Once uploaded (progress bar turns green), go back to File Manager, right-click `lad_travels_cpanel_upload.zip`, and choose **Extract**.
     - Extract into `/public_html`.
   - **Method B (Subdomain / Addon Domain / Custom Webroot):**
     - Navigate into your domain's folder (e.g., `/home/username/lad_travels`).
     - Upload and Extract `lad_travels_cpanel_upload.zip`.
     - In cPanel > Domains, ensure the Document Root points to the `public` subfolder (e.g., `/home/username/lad_travels/public`).

---

## Step 2: Create the MySQL Database in cPanel

1. In cPanel, navigate to **MySQL Databases** (under the *Databases* section).
2. **Create New Database:**
   - Enter database name (e.g., `lad_travels`) and click **Create Database**.
   - Note down the full database name (e.g. `cpaneluser_lad_travels`).
3. **Create Database User:**
   - Scroll down to *MySQL Users > Add New User*.
   - Enter username (e.g., `lad_user`) and a secure password.
   - Click **Create User**.
   - Note down the full username and password.
4. **Add User to Database:**
   - In *Add User to Database*, select the user and database you just created.
   - Click **Add**.
   - Check the box **ALL PRIVILEGES** and click **Make Changes**.

---

## Step 3: Import the Database (`lad_global_production.sql`)

1. Go back to cPanel home and open **phpMyAdmin** (under *Databases*).
2. In the left sidebar, click on your newly created database (e.g. `cpaneluser_lad_travels`).
3. Click the **Import** tab at the top.
4. Under *File to import*, click **Choose File** and select **`database/lad_global_production.sql`** (you can download this from your File Manager under the `database/` folder, or from your computer).
5. Scroll down and click **Import** (or **Go**).
6. You will see a success message: all 5 tables, system settings, tour packages, and admin account are now active!

---

## Step 4: Configure Your `.env` File

1. In cPanel **File Manager**, locate **`.env.production`** (in the root of your uploaded project).
2. Right-click **`.env.production`** and choose **Rename** -> rename it to **`.env`** (make sure the leading dot is there).
3. If hidden files are not visible, click **Settings** (top right of File Manager) and check **Show Hidden Files (dotfiles)**, then click Save.
4. Right-click **`.env`** and click **Edit**.
5. Update these lines with your actual cPanel values:
   ```ini
   APP_URL=https://ladglobaltravels.com

   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=your_cpanel_database_name
   DB_USERNAME=your_cpanel_database_user
   DB_PASSWORD=your_cpanel_database_password
   ```
6. (Optional) Outbound Email / SMTP:
   - If using cPanel webmail for enquiry and booking email alerts:
   ```ini
   SMTP_HOST=mail.ladglobaltravels.com
   SMTP_PORT=465
   SMTP_USERNAME=info@ladglobaltravels.com
   SMTP_PASSWORD=your_email_password
   SMTP_ENCRYPTION=ssl
   ```
7. Click **Save Changes**.
8. Right-click `.env`, choose **Change Permissions**, and set it to **`600`** (Read/Write for User only, preventing public reads).

---

## Step 5: Verify Permissions

In cPanel File Manager, ensure the following folders have write permissions:
- `storage/logs/` -> **775** or **755**
- `storage/temp/` -> **775** or **755**
- `public/uploads/` -> **775** or **755**

---

## Step 6: Test Your Live Website!

1. Open your browser and visit: **`https://yourdomain.com`**
   - Check the homepage, services, tour packages, about, and contact pages.
2. Sign in to your Admin Operations Console:
   - URL: **`https://yourdomain.com/admin/login`**
   - Username: **`admin`** (or `admin@ladglobaltravels.com`)
   - Password: Your administrator password.
3. Once logged in, verify your dashboard metrics, packages, and settings!

---

### Need to Reset Admin Password on Server?
If you ever need to reset the admin password directly on the server via cPanel Terminal (SSH):
```bash
php bin/unlock-admin.php --password="YourNewPassword123!"
```
Or run the provisioning tool:
```bash
php bin/create-admin.php --reset-password
```
