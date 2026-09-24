# TrueNorth Group — CMS Admin User & Handover Manual

Welcome to the **TrueNorth Group Enterprise CMS Dashboard**. This manual provides step-by-step instructions for non-technical administrators to independently manage the website product catalogue, upload imagery, adjust prices, manage inventory, and configure site settings without developer assistance.

---

## Table of Contents
1. [Logging In to the Dashboard](#1-logging-in-to-the-dashboard)
2. [Dashboard Overview & Business Metrics](#2-dashboard-overview--business-metrics)
3. [Adding & Uploading a New Product](#3-adding--uploading-a-new-product)
4. [Managing Existing Products](#4-managing-existing-products)
5. [Inventory & Price Management](#5-inventory--price-management)
6. [Managing Product Categories](#6-managing-product-categories)
7. [Media Library](#7-media-library)
8. [Website Settings & Administration](#8-website-settings--administration)
9. [Security & Logging Out](#9-security--logging-out)

---

## 1. Logging In to the Dashboard

1. Open your web browser and navigate to:
   ```text
   https://yourdomain.com/admin/login.php
   ```
2. Enter your administrator credentials:
   - **Default Username**: `admin`
   - **Default Password**: `admin123`
3. Click **SECURE SIGN IN**.
4. *(Note: For security, change your password immediately under **Change Password** after your first login).*

---

## 2. Dashboard Overview & Business Metrics

Upon logging in, you will see the **CMS Business Dashboard** (`/admin/index.php`) featuring:
- **Total Products**: Total active packaging SKUs.
- **Published Live**: Number of products visible to public website visitors.
- **Drafts**: Products hidden from public view.
- **Out of Stock**: Products with 0 inventory count.
- **Featured Products**: Products displayed in homepage promotional showcases.
- **Quote Inquiries**: Customer quote submissions.

---

## 3. Adding & Uploading a New Product

To add a new packaging product to your catalogue:

1. Click **+ Upload New Product** in the sidebar navigation or header.
2. **Upload Product Image**:
   - Click the image upload box or drag and drop your image file (`PNG`, `JPG`, `WEBP`, `SVG`).
   - A live visual preview of your product image will appear.
3. **Fill in Product Details**:
   - **Product Name**: e.g., `250ml Boston Round PET Bottle`
   - **SKU / Article Number**: e.g., `TN-B250-BR`
   - **Packaging Category**: Select from dropdown (PET Bottles, PET Jars, Caps, Pumps, Sprayers, etc.).
   - **Series / Mould Range**: e.g., `Boston Round Series`
4. **Technical Specifications**:
   - **Volume / Capacity**: e.g., `250ml` or `250cc`
   - **Neck Finish / Thread**: e.g., `24/410 ROPP`
   - **Material Polymer**: e.g., `100% rPET / Food-Grade PET`
   - **Minimum Order Quantity (MOQ)**: e.g., `5,000 pcs`
5. **Product Description**:
   - Enter detailed descriptions, application details (cosmetics, pharma, agro), and compatible closure options.
6. Click **PUBLISH PRODUCT TO LIVE CATALOGUE**. The product will immediately become visible on the live public website.

---

## 4. Managing Existing Products

1. Click **Products List** (`/admin/products.php`) in the sidebar.
2. Use the **Search Bar** or **Category Dropdown** to locate specific products.
3. For custom uploaded items, click **Delete** to remove an item.

---

## 5. Inventory & Price Management

1. Click **Inventory & Prices** (`/admin/inventory.php`).
2. Update **Regular Price (₹)** or **Stock Qty** for any product line.
3. When **Stock Qty** is set to `0`, the product status automatically updates to **Out of Stock** on the public website.
4. Click **Save** next to the item to commit your changes instantly.

---

## 6. Managing Product Categories

1. Click **Categories** (`/admin/categories.php`).
2. To create a new product category, enter the Category Name, URL Slug, and Description, then click **SAVE CATEGORY**.

---

## 7. Media Library

1. Click **Media Library** (`/admin/media.php`).
2. Browse, view, and inspect high-resolution product image assets stored on your server.

---

## 8. Website Settings & Administration

1. Click **Website Settings** (`/admin/settings.php`) to update site title, contact email, phone number, and global currency symbol.
2. Click **Admin Users** (`/admin/users.php`) to add additional administrator accounts and assign roles (`Super Admin`, `Product Manager`, `Editor`).

---

## 9. Security & Logging Out

- Always click **Logout** at the top right of the screen when finishing your administrative session.
- Session timeout is automatically enforced after 30 minutes of inactivity.
