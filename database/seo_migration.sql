-- ==============================================================================
-- TRUENORTH GROUP — ENTERPRISE TECHNICAL SEO SCHEMA & MIGRATION SCRIPT
-- Tables: seo_settings, seo_pages, seo_redirects, seo_404_log,
--         seo_faqs, seo_meta_templates, seo_audit_log
-- Column Updates: product_images (alt_text, title_text), seo_pages (WordPress SEO fields)
-- ==============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Table: seo_settings (Global Defaults & Third-Party Integration Keys)
CREATE TABLE IF NOT EXISTS `seo_settings` (
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` MEDIUMTEXT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Table: seo_pages (Per-Page Overrides for Static Pages, Categories, Products, Blog)
CREATE TABLE IF NOT EXISTS `seo_pages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `page_type` VARCHAR(50) NOT NULL,
  `page_ref_id` VARCHAR(100) NULL,
  `url_slug` VARCHAR(255) NOT NULL UNIQUE,
  `meta_title` VARCHAR(255) NULL,
  `meta_description` TEXT NULL,
  `meta_keywords` VARCHAR(255) NULL,
  `focus_keyword` VARCHAR(150) NULL,
  `canonical_url` VARCHAR(255) NULL,
  `robots_index` TINYINT(1) NOT NULL DEFAULT 1,
  `robots_follow` TINYINT(1) NOT NULL DEFAULT 1,
  `og_title` VARCHAR(255) NULL,
  `og_description` TEXT NULL,
  `og_image` VARCHAR(255) NULL,
  `schema_type` VARCHAR(60) NOT NULL DEFAULT 'WebPage',
  `custom_schema_json` MEDIUMTEXT NULL,
  `h1_override` VARCHAR(255) NULL,
  `cornerstone_content` TINYINT(1) NOT NULL DEFAULT 0,
  `breadcrumbs_title` VARCHAR(255) NULL,
  `twitter_title` VARCHAR(255) NULL,
  `twitter_description` TEXT NULL,
  `twitter_image` VARCHAR(255) NULL,
  `twitter_card` VARCHAR(50) NULL DEFAULT 'summary_large_image',
  `advanced_robots` VARCHAR(255) NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_seo_pages_type` (`page_type`),
  INDEX `idx_seo_pages_ref` (`page_ref_id`),
  INDEX `idx_seo_pages_slug` (`url_slug`),
  INDEX `idx_seo_pages_robots` (`robots_index`, `robots_follow`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Table: seo_redirects (301/302/410 URL Redirect Manager)
CREATE TABLE IF NOT EXISTS `seo_redirects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `old_url` VARCHAR(255) NOT NULL UNIQUE,
  `new_url` VARCHAR(255) NOT NULL,
  `status_code` INT NOT NULL DEFAULT 301,
  `hits` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_redirect_old` (`old_url`),
  INDEX `idx_redirect_status` (`status_code`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Table: seo_404_log (Real-Time 404 Error Monitor & Conversion to 301)
CREATE TABLE IF NOT EXISTS `seo_404_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `url` VARCHAR(255) NOT NULL UNIQUE,
  `referrer` VARCHAR(255) NULL,
  `hits` INT NOT NULL DEFAULT 1,
  `last_seen` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_404_hits` (`hits`),
  INDEX `idx_404_last_seen` (`last_seen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Table: seo_faqs (FAQ Schema Repeater per Page / Product)
CREATE TABLE IF NOT EXISTS `seo_faqs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `page_type` VARCHAR(50) NOT NULL,
  `page_ref_id` VARCHAR(100) NOT NULL,
  `question` VARCHAR(255) NOT NULL,
  `answer` TEXT NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  INDEX `idx_seo_faq_ref` (`page_type`, `page_ref_id`),
  INDEX `idx_seo_faq_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Table: seo_meta_templates (Dynamic Meta Patterns with Variable Replacement)
CREATE TABLE IF NOT EXISTS `seo_meta_templates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `page_type` VARCHAR(50) NOT NULL UNIQUE,
  `title_template` VARCHAR(255) NOT NULL,
  `description_template` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Table: seo_audit_log (Admin Action & Modification Audit Trail)
CREATE TABLE IF NOT EXISTS `seo_audit_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `admin_id` VARCHAR(50) NOT NULL DEFAULT 'admin',
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Alter Tables: product_images & seo_pages (WordPress SEO Extensions)
ALTER TABLE `product_images` ADD COLUMN IF NOT EXISTS `alt_text` VARCHAR(255) NULL AFTER `image_url`;
ALTER TABLE `product_images` ADD COLUMN IF NOT EXISTS `title_text` VARCHAR(255) NULL AFTER `alt_text`;
ALTER TABLE `seo_pages` ADD COLUMN IF NOT EXISTS `cornerstone_content` TINYINT(1) NOT NULL DEFAULT 0 AFTER `h1_override`;
ALTER TABLE `seo_pages` ADD COLUMN IF NOT EXISTS `breadcrumbs_title` VARCHAR(255) NULL AFTER `cornerstone_content`;
ALTER TABLE `seo_pages` ADD COLUMN IF NOT EXISTS `twitter_title` VARCHAR(255) NULL AFTER `breadcrumbs_title`;
ALTER TABLE `seo_pages` ADD COLUMN IF NOT EXISTS `twitter_description` TEXT NULL AFTER `twitter_title`;
ALTER TABLE `seo_pages` ADD COLUMN IF NOT EXISTS `twitter_image` VARCHAR(255) NULL AFTER `twitter_description`;
ALTER TABLE `seo_pages` ADD COLUMN IF NOT EXISTS `twitter_card` VARCHAR(50) NULL DEFAULT 'summary_large_image' AFTER `twitter_image`;
ALTER TABLE `seo_pages` ADD COLUMN IF NOT EXISTS `advanced_robots` VARCHAR(255) NULL AFTER `twitter_card`;

-- 9. Seed Default Global Settings
INSERT INTO `seo_settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'TrueNorth Group'),
('title_suffix', ' | TrueNorth Group'),
('default_meta_description', 'TrueNorth Group is an ISO 9001 certified primary packaging manufacturer and partner producing high-precision PET bottles, cosmetic jars, caps, and dispensing pumps.'),
('default_og_image', '/logo.webp'),
('favicon', '/logo_svg.svg'),
('twitter_handle', '@TrueNorthPack'),
('ga4_id', 'G-TN77884411'),
('gtm_id', 'GTM-TRUENORTH'),
('facebook_pixel_id', ''),
('google_verification', 'googled4a94827b587a8f1'),
('bing_verification', 'BINGE08A6F4E26A911C8'),
('preferred_domain', 'non-www'),
('header_scripts', ''),
('footer_scripts', ''),
('robots_txt_content', 'User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /admin\nDisallow: /api/\nDisallow: /config/\nDisallow: /*?q=*\nDisallow: /*?sort=*\nDisallow: /*?filter=*\n\nSitemap: https://wetruenorthgroup.com/sitemap.xml'),
('schema_org_enabled', '1'),
('schema_website_enabled', '1'),
('schema_breadcrumbs_enabled', '1'),
('schema_product_enabled', '1'),
('schema_article_enabled', '1'),
('schema_faq_enabled', '1'),
('schema_local_business_enabled', '1'),
('org_name', 'TrueNorth Group'),
('org_legal_name', 'TrueNorth Group Primary Packaging Limited'),
('org_logo', '/logo.webp'),
('org_phone', '+91 9810220761'),
('org_email', 'info@wetruenorthgroup.com'),
('org_address_street', '#201, Vardhaman Trade Towers, Rohit Kunj, LSC Pitampura'),
('org_address_locality', 'Delhi'),
('org_address_region', 'DL'),
('org_address_postal', '110034'),
('org_address_country', 'IN'),
('org_social_linkedin', 'https://www.linkedin.com/company/truenorth-group'),
('org_social_facebook', ''),
('org_social_twitter', ''),
('org_social_instagram', ''),
('currency', 'INR'),
('language', 'en')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

-- 10. Seed Default Meta Templates
INSERT INTO `seo_meta_templates` (`page_type`, `title_template`, `description_template`) VALUES
('product', '{product_name} - Wholesale High-Tolerance Packaging | {site_name}', 'Wholesale {product_name} manufactured in ISO certified cleanrooms. Material: {material}, Neck Finish: {neck_finish}. Get instant B2B quotation & free samples.'),
('category', '{category_name} Packaging & Containers Directory | {site_name}', 'Browse comprehensive range of {category_name} engineered for pharmaceutical, cosmetic, and personal-care applications. Direct manufacturer volume pricing.'),
('blog', '{article_title} | Technical Packaging Guide | {site_name}', '{article_excerpt}'),
('static', '{page_name} | {site_name}', '{site_description}')
ON DUPLICATE KEY UPDATE `title_template` = VALUES(`title_template`), `description_template` = VALUES(`description_template`);

-- 11. Seed Core Static Pages into seo_pages
INSERT INTO `seo_pages` (`page_type`, `page_ref_id`, `url_slug`, `meta_title`, `meta_description`, `meta_keywords`, `focus_keyword`, `canonical_url`, `robots_index`, `robots_follow`, `og_title`, `og_description`, `og_image`, `schema_type`, `h1_override`) VALUES
('home', 'home', '/', 'TrueNorth Group — Primary Packaging Specialist | PET Bottles & Jars', 'TrueNorth Group is an ISO 9001 certified hybrid primary packaging specialist manufacturing and distributing high-grade PET bottles, HDPE containers, cosmetic jars, pumps, and custom closures.', 'PET bottles, cosmetic packaging, HDPE bottles, plastic jars, lotion pumps, mist sprays, wholesale packaging manufacturer, cleanroom molding', 'primary packaging manufacturer', 'https://wetruenorthgroup.com/', 1, 1, 'TrueNorth Group — Primary Packaging Specialist | PET Bottles & Jars', 'Primary packaging manufacturer and partner producing and distributing high-grade PET bottles, jars, caps, and pumps worldwide.', '/logo.webp', 'Organization', 'Primary Packaging Specialists & Manufacturers'),
('page', 'products', '/products', 'Packaging Catalog & Containers Directory | TrueNorth Group', 'Browse 295+ PET bottles, agro containers, cosmetic jars, lotion pumps, mist sprayers, and precision closures across 8 specialized categories. ISO certified factory direct.', 'PET bottles catalogue, wholesale packaging containers, cosmetic bottles bulk, pharma packaging direct', 'packaging catalog', 'https://wetruenorthgroup.com/products', 1, 1, 'Packaging Catalog & Containers Directory | TrueNorth Group', 'Explore complete primary packaging solutions engineered with high tolerances.', '/vikaas_inputs/thumbnails/THUMBNAIL_PET_BOTTLES.webp', 'CollectionPage', 'Choose from Our Catalogue of Primary Packaging Products'),
('page', 'about', '/about', 'About TrueNorth Group — Hybrid Primary Packaging Specialist & Manufacturer', 'Learn about TrueNorth Group, our 25+ years heritage, Class 10,000 cleanroom manufacturing hubs, TÜV NORD ISO 9001 certification, and global supply network.', 'about TrueNorth, primary packaging manufacturer, ISO 9001 cleanroom packaging, PET bottle supplier', 'primary packaging specialist', 'https://wetruenorthgroup.com/about', 1, 1, 'About TrueNorth Group — 25+ Years Primary Packaging Heritage', 'Global manufacturing hubs and audited partner network serving pharmaceutical and personal-care brands.', '/factory.webp', 'AboutPage', 'Engineering Excellence with a Global Footprint'),
('page', 'contact', '/contact', 'Request a Wholesale Quote & Contact Engineering | TrueNorth Group', 'Get custom volume pricing, technical CAD drawings, and complimentary sample kits within 24 hours. Contact TrueNorth Group primary packaging specialists.', 'contact packaging manufacturer, request bottle sample, wholesale packaging quote, packaging inquiry', 'request wholesale quote', 'https://wetruenorthgroup.com/contact', 1, 1, 'Request a Wholesale Quote & Contact Engineering | TrueNorth Group', 'Get custom volume pricing, technical CAD drawings, and complimentary sample kits.', '/photo_cleanroom.webp', 'ContactPage', 'Request a Consultation & Custom Quote'),
('page', 'custom', '/custom', 'Custom Mould Design & Tailored Packaging Engineering | TrueNorth Group', 'Bespoke container tooling, 3D rapid prototyping, custom neck finishes, and private labelling solutions engineered to your exact product specifications.', 'custom bottle mould, bespoke packaging engineering, 3D bottle prototype, custom neck finish', 'custom packaging engineering', 'https://wetruenorthgroup.com/custom', 1, 1, 'Custom Mould Design & Tailored Packaging Engineering | TrueNorth Group', 'Bespoke container tooling, 3D rapid prototyping, and private labelling solutions.', '/photo_injection_molding.webp', 'ItemPage', 'Tailored Packaging Solutions & Custom Mould Engineering'),
('page', 'sustainability', '/sustainability', 'Sustainable Packaging Solutions — PCR-PET & Circular Polymers | TrueNorth', 'Eco-conscious packaging innovation. 100% Post-Consumer Recycled (PCR) PET bottles, lightweighting engineering, and closed-loop circular packaging solutions.', 'rPET bottles, PCR packaging, sustainable cosmetic containers, circular plastics, eco-friendly primary packaging', 'sustainable packaging solutions', 'https://wetruenorthgroup.com/sustainability', 1, 1, 'Sustainable Packaging Solutions — PCR-PET & Circular Polymers | TrueNorth', 'Eco-conscious packaging innovation with 100% Post-Consumer Recycled (PCR) PET bottles.', '/benefit_supply.webp', 'ItemPage', 'Sustainable Primary Packaging Solutions'),
('page', 'blog', '/blog', 'Packaging Industry Insights & Technical Guides | TrueNorth Group', 'Explore expert engineering insights, sustainable rPET packaging developments, cleanroom compliance guides, and dispensing closure specifications from TrueNorth Group.', 'PET bottles blog, sustainable packaging articles, cleanroom bottle molding, cosmetic packaging guide, rPET whitepaper', 'packaging technical guides', 'https://wetruenorthgroup.com/blog', 1, 1, 'Packaging Industry Insights & Technical Guides | TrueNorth Group', 'Explore expert engineering insights, sustainable rPET packaging developments, and cleanroom compliance guides.', '/vikaas_inputs/thumbnails/thumbnail_pet_bottles.webp', 'Blog', 'Packaging Insights, Technical Guides & Case Studies'),
('page', 'terms', '/terms', 'General Terms & Conditions of Sale | TrueNorth Group', 'B2B commercial sales terms, quality standards, order fulfillment, and warranty specifications of TrueNorth Group.', 'commercial terms of sale, packaging supply agreement, TrueNorth terms', 'terms and conditions', 'https://wetruenorthgroup.com/terms', 1, 1, 'General Terms & Conditions of Sale | TrueNorth Group', 'B2B commercial sales terms, quality standards, and warranty specifications.', '/logo.webp', 'WebPage', 'General Terms & Conditions of Sale'),
('page', 'privacy', '/privacy', 'Privacy Statement & Data Protection Policy | TrueNorth Group', 'Information regarding personal and commercial data processing, international trade inquiries, and GDPR/regulatory privacy protections.', 'privacy policy, data protection, TrueNorth privacy', 'privacy policy', 'https://wetruenorthgroup.com/privacy', 1, 1, 'Privacy Statement & Data Protection Policy | TrueNorth Group', 'Information regarding personal and commercial data processing and GDPR protections.', '/logo.webp', 'WebPage', 'Privacy Statement & Data Protection Policy')
ON DUPLICATE KEY UPDATE `url_slug` = `url_slug`;

SET FOREIGN_KEY_CHECKS = 1;
