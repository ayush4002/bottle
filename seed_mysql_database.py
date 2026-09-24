import os, re, json

print("1. Parsing data.js for full MySQL database seeding...")

with open('data.js', 'r', encoding='utf-8') as f:
    js_content = f.read()

# Load ProductModel or node JSON exporter
def get_node_json(var_name):
    cmd = f"node -e \"const d = require('./data.js'); console.log(JSON.stringify(d.{var_name}));\""
    import subprocess
    res = subprocess.check_output(cmd, shell=True, text=True, encoding='utf-8')
    return json.loads(res)

categories = get_node_json('FRAPAK_CATEGORIES_12')
series = get_node_json('SERIES_CATALOGUE')
skus = get_node_json('SKUS_CATALOGUE')
company = get_node_json('COMPANY_INFO')

print(f"Loaded {len(categories)} categories, {len(series)} series, {len(skus)} SKUs for MySQL import.")

# Prepare SQL script
sql_lines = [
    "-- ============================================================================",
    "-- TRUENORTH GROUP — ENTERPRISE RELATIONAL DATABASE SCHEMA & COMPLETE SEED DUMP",
    "-- Database Name: truenorth_db",
    "-- Compatibility: MySQL 5.7+ / MySQL 8.0+ / MariaDB 10.2+",
    "-- Contains ALL 12 Categories and 468+ Production Packaging Products",
    "-- ============================================================================",
    "",
    "CREATE DATABASE IF NOT EXISTS `truenorth_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
    "USE `truenorth_db`;",
    "",
    "-- ----------------------------------------------------------------------------",
    "-- Table 1: admins",
    "-- ----------------------------------------------------------------------------",
    "DROP TABLE IF EXISTS `admins`;",
    "CREATE TABLE `admins` (",
    "  `id` INT(11) NOT NULL AUTO_INCREMENT,",
    "  `username` VARCHAR(50) NOT NULL UNIQUE,",
    "  `email` VARCHAR(100) NOT NULL UNIQUE,",
    "  `password_hash` VARCHAR(255) NOT NULL,",
    "  `role` ENUM('Super Admin', 'Product Manager', 'Editor') DEFAULT 'Super Admin',",
    "  `status` ENUM('active', 'inactive') DEFAULT 'active',",
    "  `last_login` DATETIME DEFAULT NULL,",
    "  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,",
    "  PRIMARY KEY (`id`)",
    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    "",
    "INSERT INTO `admins` (`username`, `email`, `password_hash`, `role`, `status`) VALUES",
    "('admin', 'info@wetruenorthgroup.com', '$2y$10$QkC.S3.Z4Z/6gq9gA1H5/e/dO8h5rJk3Ff0R.x.X.Y.X.Y.X.Y.X', 'Super Admin', 'active')",
    "ON DUPLICATE KEY UPDATE `username`=`username`;",
    "",
    "-- ----------------------------------------------------------------------------",
    "-- Table 2: categories",
    "-- ----------------------------------------------------------------------------",
    "DROP TABLE IF EXISTS `categories`;",
    "CREATE TABLE `categories` (",
    "  `id` INT(11) NOT NULL AUTO_INCREMENT,",
    "  `parent_id` INT(11) DEFAULT NULL,",
    "  `name` VARCHAR(100) NOT NULL,",
    "  `slug` VARCHAR(100) NOT NULL UNIQUE,",
    "  `image` VARCHAR(255) DEFAULT NULL,",
    "  `cover_image` VARCHAR(255) DEFAULT NULL,",
    "  `description` TEXT DEFAULT NULL,",
    "  `sort_order` INT(11) DEFAULT 0,",
    "  `status` ENUM('published', 'draft') DEFAULT 'published',",
    "  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,",
    "  PRIMARY KEY (`id`),",
    "  KEY `parent_id` (`parent_id`)",
    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    ""
]

def esc(val):
    if val is None:
        return "NULL"
    if isinstance(val, bool):
        return "1" if val else "0"
    if isinstance(val, (int, float)):
        return str(val)
    s = str(val).replace("\\", "\\\\").replace("'", "\\'")
    return f"'{s}'"

cat_values = []
for idx, cat in enumerate(categories, 1):
    cat_values.append(f"({esc(cat['name'])}, {esc(cat['slug'])}, {esc(cat.get('image'))}, {esc(cat.get('coverImage'))}, {esc(cat.get('desc'))}, {idx}, 'published')")

sql_lines.append("INSERT INTO `categories` (`name`, `slug`, `image`, `cover_image`, `description`, `sort_order`, `status`) VALUES\n" + ",\n".join(cat_values) + ";\n")

sql_lines.extend([
    "-- ----------------------------------------------------------------------------",
    "-- Table 3: products",
    "-- ----------------------------------------------------------------------------",
    "DROP TABLE IF EXISTS `products`;",
    "CREATE TABLE `products` (",
    "  `id` INT(11) NOT NULL AUTO_INCREMENT,",
    "  `sku` VARCHAR(100) NOT NULL UNIQUE,",
    "  `name` VARCHAR(255) NOT NULL,",
    "  `slug` VARCHAR(255) NOT NULL UNIQUE,",
    "  `category_slug` VARCHAR(100) NOT NULL,",
    "  `category_name` VARCHAR(100) NOT NULL,",
    "  `subcategory` VARCHAR(100) DEFAULT 'General Series',",
    "  `gender` VARCHAR(50) DEFAULT 'Unisex',",
    "  `short_desc` TEXT DEFAULT NULL,",
    "  `description` LONGTEXT DEFAULT NULL,",
    "  `regular_price` DECIMAL(10,2) DEFAULT 0.00,",
    "  `sale_price` DECIMAL(10,2) DEFAULT NULL,",
    "  `stock_qty` INT(11) DEFAULT 1000,",
    "  `stock_status` ENUM('in_stock', 'out_of_stock', 'backorder') DEFAULT 'in_stock',",
    "  `status` ENUM('published', 'draft', 'archived') DEFAULT 'published',",
    "  `is_featured` TINYINT(1) DEFAULT 0,",
    "  `capacity` VARCHAR(50) DEFAULT 'N/A',",
    "  `neck_finish` VARCHAR(50) DEFAULT 'N/A',",
    "  `material` VARCHAR(100) DEFAULT '100% rPET / Food-Grade PET',",
    "  `weight` VARCHAR(50) DEFAULT 'N/A',",
    "  `moq` VARCHAR(50) DEFAULT '5,000 pcs',",
    "  `seo_title` VARCHAR(255) DEFAULT NULL,",
    "  `seo_description` TEXT DEFAULT NULL,",
    "  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,",
    "  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,",
    "  `deleted_at` DATETIME DEFAULT NULL,",
    "  PRIMARY KEY (`id`),",
    "  KEY `status` (`status`),",
    "  KEY `is_featured` (`is_featured`)",
    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    ""
])

prod_values = []
img_values = []

cat_map = {c['slug']: c['name'] for c in categories}

for p_idx, p in enumerate(skus, 1):
    sku_code = p.get('id') or p.get('articleNo') or f"SKU-{p_idx}"
    p_name = p.get('name') or p.get('fullTitle') or f"Product {p_idx}"
    p_slug = re.sub(r'[^a-z0-9-]+', '-', (sku_code + '-' + p_name).lower()).strip('-')
    cat_slug = p.get('categorySlug') or 'pet-bottles'
    cat_name = cat_map.get(cat_slug, 'PET bottles')
    subcat = p.get('serie') or 'General Series'
    gender = p.get('gender') or 'Unisex'
    short_desc = p.get('shortDescription') or p.get('description') or ''
    desc = p.get('description') or ''
    reg_price = float(p.get('regularPrice') or p.get('price') or 0)
    sale_price = float(p['salePrice']) if p.get('salePrice') is not None else None
    stock_qty = int(p.get('stockQty') or (1000 if p.get('isStock') else 0))
    stock_st = 'in_stock' if stock_qty > 0 else 'out_of_stock'
    status = p.get('status') or 'published'
    is_feat = 1 if p.get('isFeatured') else 0
    cap = p.get('specs', {}).get('capacity') or p.get('volume') or p.get('capacity') or 'N/A'
    neck = p.get('specs', {}).get('neck') or p.get('neckSize') or p.get('neck') or 'N/A'
    mat = p.get('specs', {}).get('material') or p.get('material') or 'PET / Plastic'
    wt = p.get('specs', {}).get('weight') or p.get('weight') or 'N/A'
    moq = p.get('specs', {}).get('moq') or p.get('moq') or '5,000 pcs'

    prod_values.append(
        f"({esc(sku_code)}, {esc(p_name)}, {esc(p_slug)}, {esc(cat_slug)}, {esc(cat_name)}, {esc(subcat)}, {esc(gender)}, "
        f"{esc(short_desc)}, {esc(desc)}, {reg_price}, {esc(sale_price)}, {stock_qty}, {esc(stock_st)}, {esc(status)}, "
        f"{is_feat}, {esc(cap)}, {esc(neck)}, {esc(mat)}, {esc(wt)}, {esc(moq)})"
    )

    images = p.get('images') or (p.get('image') and [p.get('image')]) or ['/logo_svg.svg']
    if not isinstance(images, list): images = [images]
    for i_idx, img_url in enumerate(images[:4], 1):
        is_primary = 1 if i_idx == 1 else 0
        img_values.append(f"({p_idx}, {esc(img_url)}, {is_primary}, {i_idx})")

sql_lines.append("INSERT INTO `products` (`sku`, `name`, `slug`, `category_slug`, `category_name`, `subcategory`, `gender`, `short_desc`, `description`, `regular_price`, `sale_price`, `stock_qty`, `stock_status`, `status`, `is_featured`, `capacity`, `neck_finish`, `material`, `weight`, `moq`) VALUES\n" + ",\n".join(prod_values) + ";\n")

sql_lines.extend([
    "-- ----------------------------------------------------------------------------",
    "-- Table 4: product_images",
    "-- ----------------------------------------------------------------------------",
    "DROP TABLE IF EXISTS `product_images`;",
    "CREATE TABLE `product_images` (",
    "  `id` INT(11) NOT NULL AUTO_INCREMENT,",
    "  `product_id` INT(11) NOT NULL,",
    "  `image_url` VARCHAR(255) NOT NULL,",
    "  `is_primary` TINYINT(1) DEFAULT 0,",
    "  `sort_order` INT(11) DEFAULT 0,",
    "  PRIMARY KEY (`id`),",
    "  KEY `product_id` (`product_id`),",
    "  CONSTRAINT `fk_product_images` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE",
    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    ""
])

sql_lines.append("INSERT INTO `product_images` (`product_id`, `image_url`, `is_primary`, `sort_order`) VALUES\n" + ",\n".join(img_values) + ";\n")

sql_lines.extend([
    "-- ----------------------------------------------------------------------------",
    "-- Table 5: settings",
    "-- ----------------------------------------------------------------------------",
    "DROP TABLE IF EXISTS `settings`;",
    "CREATE TABLE `settings` (",
    "  `setting_key` VARCHAR(100) NOT NULL,",
    "  `setting_value` TEXT DEFAULT NULL,",
    "  PRIMARY KEY (`setting_key`)",
    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    "",
    "INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES",
    "('site_name', 'TrueNorth Group'),",
    "('site_tagline', 'Primary Packaging Specialist — PET & Glass Solutions'),",
    "('contact_email', 'info@wetruenorthgroup.com'),",
    "('contact_phone', '+91 98765 43210'),",
    "('hq_address', 'Global Manufacturing Operations | Audited Partner Network Serving Global Exports');",
    ""
])

full_sql = "\n".join(sql_lines)

with open('truenorth_db.sql', 'w', encoding='utf-8') as f:
    f.write(full_sql)

with open('config/truenorth_db.sql', 'w', encoding='utf-8') as f:
    f.write(full_sql)

print(f"Generated truenorth_db.sql ({len(full_sql)} bytes) with 468 SKUs & 12 Categories!")
