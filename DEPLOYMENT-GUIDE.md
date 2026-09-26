# TrueNorth Group — Production Deployment & Domain Setup Guide

This guide walks you step-by-step through uploading and deploying your website and CMS to your live domain (cPanel, Hostinger, GoDaddy, VPS, or any standard PHP/MySQL hosting provider).

---

## 📋 Pre-Flight Deployment Checklist

- [x] **Database Dump Ready**: `truenorth_db.sql` (Contains all 468+ products, categories, specifications, and CMS settings).
- [x] **Config Auto-Loader**: `config/config.php` automatically loads environment variables from `.env`.
- [x] **Environment Template**: `.env.example` ready for your live domain and database credentials.
- [x] **Apache / LiteSpeed Routing & Security**: `.htaccess` configured with URL rewrites, security headers, browser caching, and access protection.
- [x] **Image Upload Storage**: `public/uploads/products/` prepared for admin product image uploads.

---

## 🚀 Deployment Instructions (cPanel / Shared Hosting / Hostinger)

### Step 1: Create the MySQL Database & User

1. Log in to your hosting **cPanel** (or hosting control panel).
2. Go to **MySQL Databases** (or **Database Wizard**):
   - **Create New Database**: e.g., `youruser_truenorth_db`
   - **Create New User**: e.g., `youruser_dbuser` with a strong password.
   - **Add User to Database**: Check **ALL PRIVILEGES** and click **Make Changes**.
3. Keep your Database Name, Username, and Password handy.

---

### Step 2: Import the Database (`truenorth_db.sql`)

1. In cPanel, click **phpMyAdmin**.
2. Select your newly created database on the left sidebar.
3. Click the **Import** tab in the top navigation bar.
4. Click **Choose File** and select `truenorth_db.sql` from your project folder.
5. Click **Import** (or **Go**) at the bottom.
6. Once completed, you will see a green success message confirming all tables (`products`, `categories`, `product_images`, `admins`, `quotes`, etc.) have imported.

---

### Step 3: Upload Website Files to Your Server

1. **Option A — Upload via File Manager (Recommended)**:
   - Compress your project folder into a `.zip` archive (exclude `.git` and `dist.zip` to keep it lightweight).
   - In cPanel, open **File Manager** and navigate to your domain root:
     - For primary domain: `public_html/`
     - For subdomain/addon domain: `public_html/your-subdomain/`
   - Click **Upload** and upload your `.zip` file.
   - Right-click the uploaded `.zip` file and select **Extract**.
   - Move files so `index.php` is directly in `public_html/` (not inside a subfolder).

2. **Option B — Upload via FTP / SFTP**:
   - Connect using FileZilla, Cyberduck, or WinSCP.
   - Upload all project files into your `public_html/` directory.

---

### Step 4: Configure Database in `config/config.php` (No .env Needed)

1. In your cPanel **File Manager**, navigate to the **`config/`** folder.
2. Right-click **`config.php`** and click **Edit**.
3. Update the 4 database lines near the top with your cPanel database info:

```php
define('DB_HOST', 'localhost');                  // Usually 'localhost' on cPanel
define('DB_NAME', 'yourcpanel_truenorth_db');     // Your cPanel database name
define('DB_USER', 'yourcpanel_dbuser');           // Your cPanel database user
define('DB_PASS', 'YourActualDatabasePassword');  // Your cPanel database password
```

4. *(Optional)* Update your admin password or mail settings directly in this file if desired.
5. Click **Save Changes**. That's it—no `.env` file is required!

---

### Step 5: Check File Permissions

Ensure the following directories have write permissions for image uploads and JSON fallback storage:
- `public/uploads/` -> **755** (or **775**)
- `public/uploads/products/` -> **755** (or **775**)
- `config/` -> **755** (or **775**)

---

### Step 6: Enable SSL (HTTPS)

1. In cPanel, search for **SSL/TLS Status** or **Let's Encrypt SSL**.
2. Click **Run AutoSSL** or issue a free SSL certificate for `yourdomain.com` and `www.yourdomain.com`.
3. To enforce HTTPS across all pages automatically, open `.htaccess` and uncomment lines 45-47:
   ```apache
   RewriteCond %{HTTPS} !=on
   RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

---

## 🔒 Step 7: Verify Everything on Your Live Domain

1. **Visit Live Website**: Open `https://yourdomain.com` in your browser.
   - Verify homepage banners, product categories, and bottle listings display correctly.
2. **Access Admin Panel**: Go to `https://yourdomain.com/admin/login.php`.
   - Sign in with your `ADMIN_USER` and `ADMIN_PASS`.
3. **Test Product Editing**:
   - Go to `/admin/products.php`.
   - Click **Edit** on a product, change a field, and click **Save Changes**.
4. **Test Quote Inquiries**:
   - Submit a test quote request on the frontend and verify it records in the database and sends notification.

---

## 🛠️ Server Environment Requirements

- **PHP Version**: PHP 8.0, 8.1, 8.2, or 8.3
- **PHP Extensions**:
  - `pdo_mysql` (Database connectivity)
  - `gd` or `imagick` (Image processing & uploads)
  - `mbstring` (UTF-8 text handling)
  - `curl` (API & external requests)
- **Web Server**: Apache, LiteSpeed, or Nginx with `mod_rewrite` enabled.
