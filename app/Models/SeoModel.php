<?php
// ==============================================================================
// TRUENORTH GROUP — CENTRAL ENTERPRISE TECHNICAL SEO DATA MODEL
// Handles Global SEO Settings, Per-Page Meta Overrides, Meta Templates,
// 301/302/410 Redirects, 404 Logging, FAQs, Audit Logs, Image Alt Text,
// Sitemap Generation, Robots.txt, and Real-Time SEO Auditing.
// Dual Storage: MySQL PDO when connected + JSON fallback in config/.
// ==============================================================================

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';

class SeoModel {

    private static $cacheDir = null;

    public static function getCacheDir() {
        if (self::$cacheDir === null) {
            $base = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2);
            self::$cacheDir = $base . '/config/cache';
            if (!is_dir(self::$cacheDir)) {
                @mkdir(self::$cacheDir, 0755, true);
            }
        }
        return self::$cacheDir;
    }

    public static function getJsonFile($name) {
        $base = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2);
        return $base . '/config/' . $name . '.json';
    }

    private static function getPdo() {
        if (class_exists('Database')) {
            $db = Database::getInstance();
            if ($db && $db->isConnected()) {
                return $db->getConnection();
            }
        }
        return null;
    }

    /**
     * Initialize SEO Storage (creates MySQL tables or JSON defaults)
     */
    public static function init() {
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                // Run schema creation statements safely
                $migrationFile = dirname(__DIR__, 2) . '/database/seo_migration.sql';
                if (file_exists($migrationFile)) {
                    $sql = file_get_contents($migrationFile);
                    // Check if table seo_settings exists
                    $stmt = $pdo->query("SHOW TABLES LIKE 'seo_settings'");
                    if ($stmt->rowCount() === 0) {
                        $pdo->exec($sql);
                    }
                }
            } catch (Exception $e) {
                error_log("SEO Table Migration Error: " . $e->getMessage());
            }
        }

        // Initialize JSON fallbacks if missing
        self::initJsonFallbacks();
    }

    private static function initJsonFallbacks() {
        $files = [
            'seo_settings' => [
                'site_name' => 'TrueNorth Group',
                'title_suffix' => ' | TrueNorth Group',
                'default_meta_description' => 'TrueNorth Group is an ISO 9001 certified primary packaging manufacturer and partner producing high-precision PET bottles, cosmetic jars, caps, and dispensing pumps.',
                'default_og_image' => '/logo.webp',
                'favicon' => '/logo_svg.svg',
                'twitter_handle' => '@TrueNorthPack',
                'ga4_id' => 'G-TN77884411',
                'gtm_id' => 'GTM-TRUENORTH',
                'facebook_pixel_id' => '',
                'google_verification' => 'googled4a94827b587a8f1',
                'bing_verification' => 'BINGE08A6F4E26A911C8',
                'preferred_domain' => 'non-www',
                'header_scripts' => '',
                'footer_scripts' => '',
                'robots_txt_content' => "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /admin\nDisallow: /api/\nDisallow: /config/\nDisallow: /*?q=*\nDisallow: /*?sort=*\nDisallow: /*?filter=*\n\nSitemap: https://wetruenorthgroup.com/sitemap.xml",
                'schema_org_enabled' => '1',
                'schema_website_enabled' => '1',
                'schema_breadcrumbs_enabled' => '1',
                'schema_product_enabled' => '1',
                'schema_article_enabled' => '1',
                'schema_faq_enabled' => '1',
                'schema_local_business_enabled' => '1',
                'org_name' => 'TrueNorth Group',
                'org_legal_name' => 'TrueNorth Group Primary Packaging Limited',
                'org_logo' => '/logo.webp',
                'org_phone' => '+91 9810220761',
                'org_email' => 'info@wetruenorthgroup.com',
                'org_address_street' => '#201, Vardhaman Trade Towers, Rohit Kunj, LSC Pitampura',
                'org_address_locality' => 'Delhi',
                'org_address_region' => 'DL',
                'org_address_postal' => '110034',
                'org_address_country' => 'IN',
                'org_social_linkedin' => 'https://www.linkedin.com/company/truenorth-group',
                'org_social_facebook' => '',
                'org_social_twitter' => '',
                'org_social_instagram' => '',
                'currency' => 'INR',
                'language' => 'en'
            ],
            'seo_meta_templates' => [
                'product' => [
                    'page_type' => 'product',
                    'title_template' => '{product_name} - Wholesale High-Tolerance Packaging | {site_name}',
                    'description_template' => 'Wholesale {product_name} manufactured in ISO certified cleanrooms. Material: {material}, Neck Finish: {neck_finish}. Get instant B2B quotation & free samples.'
                ],
                'category' => [
                    'page_type' => 'category',
                    'title_template' => '{category_name} Packaging & Containers Directory | {site_name}',
                    'description_template' => 'Browse comprehensive range of {category_name} engineered for pharmaceutical, cosmetic, and personal-care applications. Direct manufacturer volume pricing.'
                ],
                'blog' => [
                    'page_type' => 'blog',
                    'title_template' => '{article_title} | Technical Packaging Guide | {site_name}',
                    'description_template' => '{article_excerpt}'
                ],
                'static' => [
                    'page_type' => 'static',
                    'title_template' => '{page_name} | {site_name}',
                    'description_template' => '{site_description}'
                ]
            ],
            'seo_redirects' => [],
            'seo_404_log' => [],
            'seo_faqs' => [],
            'seo_audit_log' => [],
            'seo_pages' => []
        ];

        foreach ($files as $name => $defaultData) {
            $f = self::getJsonFile($name);
            if (!file_exists($f)) {
                @file_put_contents($f, json_encode($defaultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
            }
        }
    }

    // =========================================================================
    // 1. GLOBAL SEO SETTINGS
    // =========================================================================

    public static function getSettings() {
        $cacheKey = 'seo_settings_cache';
        $cached = self::getCache($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $settings = [];
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->query("SELECT setting_key, setting_value FROM seo_settings");
                while ($row = $stmt->fetch()) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }
            } catch (Exception $e) {
                // fall through to JSON
            }
        }

        if (empty($settings)) {
            $f = self::getJsonFile('seo_settings');
            if (file_exists($f)) {
                $settings = json_decode(file_get_contents($f), true) ?: [];
            }
        }

        self::setCache($cacheKey, $settings);
        return $settings;
    }

    public static function getSetting($key, $default = null) {
        $all = self::getSettings();
        return $all[$key] ?? $default;
    }

    public static function saveSettings($data, $adminId = 'admin') {
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO seo_settings (setting_key, setting_value) VALUES (:k, :v) ON DUPLICATE KEY UPDATE setting_value = :v2");
                foreach ($data as $k => $v) {
                    $stmt->execute([':k' => $k, ':v' => $v, ':v2' => $v]);
                }
            } catch (Exception $e) {
                error_log("Save Settings DB Error: " . $e->getMessage());
            }
        }

        // Always sync JSON fallback
        $f = self::getJsonFile('seo_settings');
        $current = [];
        if (file_exists($f)) {
            $current = json_decode(file_get_contents($f), true) ?: [];
        }
        foreach ($data as $k => $v) {
            $current[$k] = $v;
        }
        @file_put_contents($f, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);

        self::flushCache();
        self::logAudit('update_seo_settings', 'Updated global SEO settings: ' . implode(', ', array_keys($data)), $adminId);
        return true;
    }

    // =========================================================================
    // 2. PAGE SEO OVERRIDES (seo_pages)
    // =========================================================================

    public static function getPageSeo($slugOrUrl, $type = null, $refId = null) {
        $slug = '/' . ltrim($slugOrUrl, '/');
        // Strip query string for route lookup
        $cleanSlug = strtok($slug, '?');

        $pdo = self::getPdo();
        if ($pdo) {
            try {
                if ($type && $refId) {
                    $stmt = $pdo->prepare("SELECT * FROM seo_pages WHERE (url_slug = :slug OR (page_type = :type AND page_ref_id = :ref)) LIMIT 1");
                    $stmt->execute([':slug' => $cleanSlug, ':type' => $type, ':ref' => $refId]);
                } else {
                    $stmt = $pdo->prepare("SELECT * FROM seo_pages WHERE url_slug = :slug LIMIT 1");
                    $stmt->execute([':slug' => $cleanSlug]);
                }
                $res = $stmt->fetch();
                if ($res) return $res;
            } catch (Exception $e) {
                // fall through to JSON
            }
        }

        // JSON Fallback
        $f = self::getJsonFile('seo_pages');
        if (file_exists($f)) {
            $pages = json_decode(file_get_contents($f), true) ?: [];
            foreach ($pages as $p) {
                if ($p['url_slug'] === $cleanSlug || ($type && $refId && ($p['page_type'] ?? '') === $type && ($p['page_ref_id'] ?? '') === $refId)) {
                    return $p;
                }
            }
        }
        return null;
    }

    public static function getAllPagesList($search = '', $type = 'all', $page = 1, $perPage = 15) {
        $items = [];
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $where = [];
                $params = [];
                if ($type !== 'all' && !empty($type)) {
                    $where[] = "page_type = :type";
                    $params[':type'] = $type;
                }
                if (!empty($search)) {
                    $where[] = "(url_slug LIKE :s1 OR meta_title LIKE :s2 OR meta_description LIKE :s3 OR focus_keyword LIKE :s4)";
                    foreach ([':s1', ':s2', ':s3', ':s4'] as $ph) {
                        $params[$ph] = '%' . $search . '%';
                    }
                }
                $whereSql = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";

                $countStmt = $pdo->prepare("SELECT COUNT(*) FROM seo_pages {$whereSql}");
                $countStmt->execute($params);
                $total = (int)$countStmt->fetchColumn();

                $page = max(1, (int)$page);
                $perPage = max(1, min(200, (int)$perPage));
                $offset = ($page - 1) * $perPage;
                $stmt = $pdo->prepare("SELECT * FROM seo_pages {$whereSql} ORDER BY updated_at DESC LIMIT {$perPage} OFFSET {$offset}");
                $stmt->execute($params);
                $items = $stmt->fetchAll();

                return ['items' => $items, 'total' => $total, 'page' => $page, 'perPage' => $perPage, 'pages' => ceil($total / $perPage)];
            } catch (Exception $e) {
                // fallback to JSON
            }
        }

        // JSON Fallback
        $f = self::getJsonFile('seo_pages');
        $all = file_exists($f) ? (json_decode(file_get_contents($f), true) ?: []) : [];
        $filtered = [];
        foreach ($all as $item) {
            if ($type !== 'all' && ($item['page_type'] ?? '') !== $type) continue;
            if (!empty($search)) {
                $haystack = strtolower(($item['url_slug'] ?? '') . ' ' . ($item['meta_title'] ?? '') . ' ' . ($item['meta_description'] ?? '') . ' ' . ($item['focus_keyword'] ?? ''));
                if (strpos($haystack, strtolower($search)) === false) continue;
            }
            $filtered[] = $item;
        }
        $total = count($filtered);
        $offset = ($page - 1) * $perPage;
        $sliced = array_slice($filtered, $offset, $perPage);
        return ['items' => $sliced, 'total' => $total, 'page' => $page, 'perPage' => $perPage, 'pages' => ceil($total / $perPage)];
    }

    public static function savePageSeo($data, $adminId = 'admin') {
        $slug = '/' . ltrim($data['url_slug'] ?? '/', '/');
        $oldSlug = !empty($data['old_url_slug']) ? ('/' . ltrim($data['old_url_slug'], '/')) : null;

        // Auto 301 Redirect creation if slug changed
        if ($oldSlug && $oldSlug !== $slug && !empty($oldSlug)) {
            self::saveRedirect($oldSlug, $slug, 301, 1, $adminId);
            self::logAudit('auto_slug_redirect', "Created 301 redirect from {$oldSlug} to {$slug} on slug update", $adminId);
        }

        $record = [
            'page_type' => $data['page_type'] ?? 'page',
            'page_ref_id' => $data['page_ref_id'] ?? null,
            'url_slug' => $slug,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'focus_keyword' => $data['focus_keyword'] ?? null,
            'canonical_url' => $data['canonical_url'] ?? null,
            'robots_index' => isset($data['robots_index']) ? (int)$data['robots_index'] : 1,
            'robots_follow' => isset($data['robots_follow']) ? (int)$data['robots_follow'] : 1,
            'og_title' => $data['og_title'] ?? null,
            'og_description' => $data['og_description'] ?? null,
            'og_image' => $data['og_image'] ?? null,
            'schema_type' => $data['schema_type'] ?? 'WebPage',
            'custom_schema_json' => $data['custom_schema_json'] ?? null,
            'h1_override' => $data['h1_override'] ?? null,
            'cornerstone_content' => !empty($data['cornerstone_content']) ? 1 : 0,
            'breadcrumbs_title' => $data['breadcrumbs_title'] ?? null,
            'twitter_title' => $data['twitter_title'] ?? null,
            'twitter_description' => $data['twitter_description'] ?? null,
            'twitter_image' => $data['twitter_image'] ?? null,
            'twitter_card' => $data['twitter_card'] ?? 'summary_large_image',
            'advanced_robots' => $data['advanced_robots'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $sql = "INSERT INTO seo_pages (
                    page_type, page_ref_id, url_slug, meta_title, meta_description, meta_keywords,
                    focus_keyword, canonical_url, robots_index, robots_follow, og_title, og_description,
                    og_image, schema_type, custom_schema_json, h1_override, cornerstone_content,
                    breadcrumbs_title, twitter_title, twitter_description, twitter_image, twitter_card,
                    advanced_robots, updated_at
                ) VALUES (
                    :page_type, :page_ref_id, :url_slug, :meta_title, :meta_description, :meta_keywords,
                    :focus_keyword, :canonical_url, :robots_index, :robots_follow, :og_title, :og_description,
                    :og_image, :schema_type, :custom_schema_json, :h1_override, :cornerstone_content,
                    :breadcrumbs_title, :twitter_title, :twitter_description, :twitter_image, :twitter_card,
                    :advanced_robots, :updated_at
                ) ON DUPLICATE KEY UPDATE
                    page_type = VALUES(page_type),
                    page_ref_id = VALUES(page_ref_id),
                    meta_title = VALUES(meta_title),
                    meta_description = VALUES(meta_description),
                    meta_keywords = VALUES(meta_keywords),
                    focus_keyword = VALUES(focus_keyword),
                    canonical_url = VALUES(canonical_url),
                    robots_index = VALUES(robots_index),
                    robots_follow = VALUES(robots_follow),
                    og_title = VALUES(og_title),
                    og_description = VALUES(og_description),
                    og_image = VALUES(og_image),
                    schema_type = VALUES(schema_type),
                    custom_schema_json = VALUES(custom_schema_json),
                    h1_override = VALUES(h1_override),
                    cornerstone_content = VALUES(cornerstone_content),
                    breadcrumbs_title = VALUES(breadcrumbs_title),
                    twitter_title = VALUES(twitter_title),
                    twitter_description = VALUES(twitter_description),
                    twitter_image = VALUES(twitter_image),
                    twitter_card = VALUES(twitter_card),
                    advanced_robots = VALUES(advanced_robots),
                    updated_at = VALUES(updated_at)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($record);
            } catch (Exception $e) {
                error_log("Save Page SEO DB Error: " . $e->getMessage());
            }
        }

        // Sync JSON
        $f = self::getJsonFile('seo_pages');
        $all = file_exists($f) ? (json_decode(file_get_contents($f), true) ?: []) : [];
        $found = false;
        foreach ($all as &$item) {
            if ($item['url_slug'] === $slug) {
                $item = array_merge($item, $record);
                $found = true;
                break;
            }
        }
        if (!$found) {
            $record['id'] = count($all) + 1;
            $all[] = $record;
        }
        @file_put_contents($f, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);

        self::flushCache();
        self::logAudit('save_page_seo', "Updated SEO metadata for {$slug}", $adminId);
        return true;
    }

    public static function deletePageSeo($id, $adminId = 'admin') {
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("DELETE FROM seo_pages WHERE id = :id");
                $stmt->execute([':id' => $id]);
            } catch (Exception $e) {}
        }

        $f = self::getJsonFile('seo_pages');
        if (file_exists($f)) {
            $all = json_decode(file_get_contents($f), true) ?: [];
            $filtered = array_filter($all, function($p) use ($id) {
                return (int)($p['id'] ?? 0) !== (int)$id;
            });
            @file_put_contents($f, json_encode(array_values($filtered), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
        }

        self::flushCache();
        self::logAudit('delete_page_seo', "Deleted SEO override ID #{$id}", $adminId);
        return true;
    }

    // =========================================================================
    // 3. META TEMPLATES (Dynamic pattern engine)
    // =========================================================================

    public static function getTemplates() {
        $pdo = self::getPdo();
        $templates = [];
        if ($pdo) {
            try {
                $stmt = $pdo->query("SELECT page_type, title_template, description_template FROM seo_meta_templates");
                while ($row = $stmt->fetch()) {
                    $templates[$row['page_type']] = $row;
                }
            } catch (Exception $e) {}
        }
        if (empty($templates)) {
            $f = self::getJsonFile('seo_meta_templates');
            if (file_exists($f)) {
                $templates = json_decode(file_get_contents($f), true) ?: [];
            }
        }
        return $templates;
    }

    public static function saveTemplate($type, $titleTpl, $descTpl, $adminId = 'admin') {
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO seo_meta_templates (page_type, title_template, description_template) VALUES (:type, :t, :d) ON DUPLICATE KEY UPDATE title_template = :t2, description_template = :d2");
                $stmt->execute([':type' => $type, ':t' => $titleTpl, ':d' => $descTpl, ':t2' => $titleTpl, ':d2' => $descTpl]);
            } catch (Exception $e) {}
        }

        $f = self::getJsonFile('seo_meta_templates');
        $all = file_exists($f) ? (json_decode(file_get_contents($f), true) ?: []) : [];
        $all[$type] = [
            'page_type' => $type,
            'title_template' => $titleTpl,
            'description_template' => $descTpl
        ];
        @file_put_contents($f, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);

        self::flushCache();
        self::logAudit('update_meta_template', "Updated meta template for {$type}", $adminId);
        return true;
    }

    public static function applyTemplate($type, $vars = []) {
        $templates = self::getTemplates();
        $tpl = $templates[$type] ?? null;
        if (!$tpl) return null;

        $title = $tpl['title_template'] ?? '';
        $desc = $tpl['description_template'] ?? '';

        // Default global vars
        $settings = self::getSettings();
        $vars['site_name'] = $vars['site_name'] ?? ($settings['site_name'] ?? 'TrueNorth Group');
        $vars['site_description'] = $vars['site_description'] ?? ($settings['default_meta_description'] ?? '');

        foreach ($vars as $k => $v) {
            if (is_scalar($v)) {
                $title = str_replace('{' . $k . '}', (string)$v, $title);
                $desc = str_replace('{' . $k . '}', (string)$v, $desc);
            }
        }
        // Remove unreplaced placeholders
        $title = preg_replace('/\{[a-zA-Z0-9_-]+\}/', '', $title);
        $desc = preg_replace('/\{[a-zA-Z0-9_-]+\}/', '', $desc);

        return ['title' => trim($title), 'description' => trim($desc)];
    }

    // =========================================================================
    // 4. REDIRECTS (301, 302, 410) & LOOP / CHAIN DETECTION
    // =========================================================================

    public static function getRedirects($search = '') {
        $list = [];
        $dbOk = false;
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $sql = "SELECT * FROM seo_redirects";
                $params = [];
                if (!empty($search)) {
                    $sql .= " WHERE old_url LIKE :s1 OR new_url LIKE :s2";
                    $params[':s1'] = '%' . $search . '%';
                    $params[':s2'] = '%' . $search . '%';
                }
                $sql .= " ORDER BY created_at DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $list = $stmt->fetchAll();
                $dbOk = true;
            } catch (Exception $e) {
                error_log('SeoModel::getRedirects DB error: ' . $e->getMessage());
            }
        }
        if (!$dbOk || (empty($list) && empty($search))) {
            $f = self::getJsonFile('seo_redirects');
            if (file_exists($f)) {
                $list = json_decode(file_get_contents($f), true) ?: [];
                if (!empty($search)) {
                    $list = array_filter($list, function($r) use ($search) {
                        return stripos($r['old_url'], $search) !== false || stripos($r['new_url'], $search) !== false;
                    });
                }
            }
        }

        // Detect loops and chains
        $urlMap = [];
        foreach ($list as $r) {
            if (!empty($r['is_active'])) {
                $urlMap[$r['old_url']] = $r['new_url'];
            }
        }

        foreach ($list as &$r) {
            $r['has_loop'] = false;
            $r['has_chain'] = false;
            $target = $r['new_url'];
            if (isset($urlMap[$target])) {
                $r['has_chain'] = true;
                if ($urlMap[$target] === $r['old_url']) {
                    $r['has_loop'] = true;
                }
            }
        }
        return $list;
    }

    public static function saveRedirect($oldUrl, $newUrl, $statusCode = 301, $isActive = 1, $adminId = 'admin') {
        $oldUrl = '/' . ltrim(trim($oldUrl), '/');
        $newUrl = (strpos($newUrl, 'http://') === 0 || strpos($newUrl, 'https://') === 0) ? trim($newUrl) : ('/' . ltrim(trim($newUrl), '/'));
        $statusCode = in_array((int)$statusCode, [301, 302, 410]) ? (int)$statusCode : 301;

        if ($oldUrl === $newUrl) {
            return ['success' => false, 'error' => 'Source and destination URLs cannot be identical.'];
        }

        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO seo_redirects (old_url, new_url, status_code, is_active, created_at)
                    VALUES (:old, :new, :code, :act, NOW())
                    ON DUPLICATE KEY UPDATE new_url = VALUES(new_url), status_code = VALUES(status_code), is_active = VALUES(is_active)");
                $stmt->execute([
                    ':old' => $oldUrl,
                    ':new' => $newUrl,
                    ':code' => $statusCode,
                    ':act' => (int)$isActive
                ]);
            } catch (Exception $e) {
                error_log("Save Redirect DB Error: " . $e->getMessage());
            }
        }

        // Sync JSON
        $f = self::getJsonFile('seo_redirects');
        $all = file_exists($f) ? (json_decode(file_get_contents($f), true) ?: []) : [];
        $found = false;
        foreach ($all as &$r) {
            if ($r['old_url'] === $oldUrl) {
                $r['new_url'] = $newUrl;
                $r['status_code'] = $statusCode;
                $r['is_active'] = (int)$isActive;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $all[] = [
                'id' => count($all) + 1,
                'old_url' => $oldUrl,
                'new_url' => $newUrl,
                'status_code' => $statusCode,
                'hits' => 0,
                'is_active' => (int)$isActive,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        @file_put_contents($f, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);

        self::flushCache();
        self::logAudit('create_redirect', "Created {$statusCode} redirect from {$oldUrl} to {$newUrl}", $adminId);
        return ['success' => true];
    }

    public static function deleteRedirect($id, $adminId = 'admin') {
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("DELETE FROM seo_redirects WHERE id = :id");
                $stmt->execute([':id' => $id]);
            } catch (Exception $e) {}
        }
        $f = self::getJsonFile('seo_redirects');
        if (file_exists($f)) {
            $all = json_decode(file_get_contents($f), true) ?: [];
            $filtered = array_filter($all, function($r) use ($id) { return (int)($r['id'] ?? 0) !== (int)$id; });
            @file_put_contents($f, json_encode(array_values($filtered), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
        }
        self::flushCache();
        self::logAudit('delete_redirect', "Deleted redirect ID #{$id}", $adminId);
        return true;
    }

    public static function toggleRedirect($id, $adminId = 'admin') {
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("UPDATE seo_redirects SET is_active = NOT is_active WHERE id = :id");
                $stmt->execute([':id' => $id]);
            } catch (Exception $e) {}
        }
        $f = self::getJsonFile('seo_redirects');
        if (file_exists($f)) {
            $all = json_decode(file_get_contents($f), true) ?: [];
            foreach ($all as &$r) {
                if ((int)($r['id'] ?? 0) === (int)$id) {
                    $r['is_active'] = empty($r['is_active']) ? 1 : 0;
                    break;
                }
            }
            @file_put_contents($f, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
        }
        self::flushCache();
        self::logAudit('toggle_redirect', "Toggled redirect ID #{$id}", $adminId);
        return true;
    }

    /**
     * Check request URI against redirects and execute redirect immediately
     */
    public static function handleEarlyRedirect($requestUri) {
        $path = parse_url($requestUri, PHP_URL_PATH);
        $cleanPath = '/' . trim($path, '/');
        if ($cleanPath === '/') $cleanPath = '/';

        $pdo = self::getPdo();
        $match = null;
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM seo_redirects WHERE (old_url = :p OR old_url = :p2) AND is_active = 1 LIMIT 1");
                $stmt->execute([':p' => $cleanPath, ':p2' => rtrim($cleanPath, '/')]);
                $match = $stmt->fetch();
                if ($match) {
                    $stmtUp = $pdo->prepare("UPDATE seo_redirects SET hits = hits + 1 WHERE id = :id");
                    $stmtUp->execute([':id' => $match['id']]);
                }
            } catch (Exception $e) {}
        }

        if (!$match) {
            $f = self::getJsonFile('seo_redirects');
            if (file_exists($f)) {
                $all = json_decode(file_get_contents($f), true) ?: [];
                foreach ($all as &$r) {
                    if (!empty($r['is_active']) && ($r['old_url'] === $cleanPath || rtrim($r['old_url'], '/') === rtrim($cleanPath, '/'))) {
                        $match = $r;
                        $r['hits'] = ($r['hits'] ?? 0) + 1;
                        @file_put_contents($f, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
                        break;
                    }
                }
            }
        }

        if ($match) {
            $code = (int)($match['status_code'] ?? 301);
            if ($code === 410) {
                http_response_code(410);
                if (file_exists(dirname(__DIR__, 2) . '/app/Views/pages/404.php')) {
                    $is410 = true;
                    require_once dirname(__DIR__, 2) . '/app/Views/pages/404.php';
                } else {
                    echo "<h1>410 Gone</h1><p>This resource has been permanently removed.</p>";
                }
                exit();
            } else {
                $qs = parse_url($requestUri, PHP_URL_QUERY);
                $target = $match['new_url'];
                if (!empty($qs) && strpos($target, '?') === false) {
                    $target .= '?' . $qs;
                }
                header("Location: " . $target, true, $code);
                exit();
            }
        }
    }

    // =========================================================================
    // 5. 404 LOGGING & MONITOR
    // =========================================================================

    public static function log404($url, $referrer = null) {
        $cleanUrl = '/' . ltrim(parse_url($url, PHP_URL_PATH), '/');
        // Ignore static assets and crawler noise
        $ext = strtolower(pathinfo($cleanUrl, PATHINFO_EXTENSION));
        if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif', 'ico', 'css', 'js', 'map', 'woff', 'woff2'])) {
            return;
        }

        $referrer = mb_substr((string)($referrer ?: ($_SERVER['HTTP_REFERER'] ?? '')), 0, 500);
        if (strlen($cleanUrl) > 500) {
            return;
        }

        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO seo_404_log (url, referrer, hits, last_seen)
                    VALUES (:u, :ref, 1, NOW())
                    ON DUPLICATE KEY UPDATE hits = hits + 1, referrer = IF(VALUES(referrer) != '', VALUES(referrer), referrer), last_seen = NOW()");
                $stmt->execute([':u' => $cleanUrl, ':ref' => $referrer]);
                return;
            } catch (Exception $e) {}
        }

        $f = self::getJsonFile('seo_404_log');
        $all = file_exists($f) ? (json_decode(file_get_contents($f), true) ?: []) : [];
        $found = false;
        foreach ($all as &$item) {
            if ($item['url'] === $cleanUrl) {
                $item['hits'] = ($item['hits'] ?? 1) + 1;
                $item['last_seen'] = date('Y-m-d H:i:s');
                if (!empty($referrer)) $item['referrer'] = $referrer;
                $found = true;
                break;
            }
        }
        if (!$found) {
            if (count($all) >= 1000) {
                // Keep the 900 most-hit entries; drop the long tail of one-off junk URLs
                usort($all, function ($a, $b) { return ($b['hits'] ?? 0) <=> ($a['hits'] ?? 0); });
                $all = array_slice($all, 0, 900);
            }
            $all[] = [
                'id' => (int)(microtime(true) * 1000),
                'url' => mb_substr($cleanUrl, 0, 500),
                'referrer' => $referrer,
                'hits' => 1,
                'last_seen' => date('Y-m-d H:i:s')
            ];
        }
        @file_put_contents($f, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    public static function get404Logs($limit = 100) {
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM seo_404_log ORDER BY hits DESC, last_seen DESC LIMIT :lim");
                $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll();
            } catch (Exception $e) {}
        }

        $f = self::getJsonFile('seo_404_log');
        $all = file_exists($f) ? (json_decode(file_get_contents($f), true) ?: []) : [];
        usort($all, function($a, $b) {
            return ($b['hits'] ?? 0) <=> ($a['hits'] ?? 0);
        });
        return array_slice($all, 0, $limit);
    }

    public static function dismiss404($id) {
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("DELETE FROM seo_404_log WHERE id = :id");
                $stmt->execute([':id' => $id]);
            } catch (Exception $e) {}
        }
        $f = self::getJsonFile('seo_404_log');
        if (file_exists($f)) {
            $all = json_decode(file_get_contents($f), true) ?: [];
            $filtered = array_filter($all, function($item) use ($id) {
                return (int)($item['id'] ?? 0) !== (int)$id;
            });
            @file_put_contents($f, json_encode(array_values($filtered), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
        }
        return true;
    }

    // =========================================================================
    // 6. FAQ REPEATER ENGINE (seo_faqs)
    // =========================================================================

    public static function getFaqs($pageType, $pageRefId) {
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM seo_faqs WHERE page_type = :pt AND page_ref_id = :ref ORDER BY sort_order ASC, id ASC");
                $stmt->execute([':pt' => $pageType, ':ref' => (string)$pageRefId]);
                return $stmt->fetchAll();
            } catch (Exception $e) {}
        }

        $f = self::getJsonFile('seo_faqs');
        $all = file_exists($f) ? (json_decode(file_get_contents($f), true) ?: []) : [];
        $res = array_filter($all, function($item) use ($pageType, $pageRefId) {
            return ($item['page_type'] ?? '') === $pageType && (string)($item['page_ref_id'] ?? '') === (string)$pageRefId;
        });
        return array_values($res);
    }

    public static function saveFaqs($pageType, $pageRefId, $faqs, $adminId = 'admin') {
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $del = $pdo->prepare("DELETE FROM seo_faqs WHERE page_type = :pt AND page_ref_id = :ref");
                $del->execute([':pt' => $pageType, ':ref' => (string)$pageRefId]);

                $ins = $pdo->prepare("INSERT INTO seo_faqs (page_type, page_ref_id, question, answer, sort_order) VALUES (:pt, :ref, :q, :a, :ord)");
                foreach ($faqs as $idx => $faq) {
                    if (!empty($faq['question']) && !empty($faq['answer'])) {
                        $ins->execute([
                            ':pt' => $pageType,
                            ':ref' => (string)$pageRefId,
                            ':q' => trim($faq['question']),
                            ':a' => trim($faq['answer']),
                            ':ord' => $idx
                        ]);
                    }
                }
            } catch (Exception $e) {}
        }

        // Sync JSON
        $f = self::getJsonFile('seo_faqs');
        $all = file_exists($f) ? (json_decode(file_get_contents($f), true) ?: []) : [];
        $filtered = array_filter($all, function($item) use ($pageType, $pageRefId) {
            return !(($item['page_type'] ?? '') === $pageType && (string)($item['page_ref_id'] ?? '') === (string)$pageRefId);
        });
        foreach ($faqs as $idx => $faq) {
            if (!empty($faq['question']) && !empty($faq['answer'])) {
                $filtered[] = [
                    'id' => count($filtered) + 1,
                    'page_type' => $pageType,
                    'page_ref_id' => (string)$pageRefId,
                    'question' => trim($faq['question']),
                    'answer' => trim($faq['answer']),
                    'sort_order' => $idx
                ];
            }
        }
        @file_put_contents($f, json_encode(array_values($filtered), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
        self::flushCache();
        return true;
    }

    // =========================================================================
    // 7. AUDIT LOGGING
    // =========================================================================

    public static function logAudit($action, $details, $adminId = 'admin') {
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO seo_audit_log (admin_id, action, details, created_at) VALUES (:adm, :act, :det, NOW())");
                $stmt->execute([':adm' => $adminId, ':act' => $action, ':det' => $details]);
                return;
            } catch (Exception $e) {}
        }

        $f = self::getJsonFile('seo_audit_log');
        $all = file_exists($f) ? (json_decode(file_get_contents($f), true) ?: []) : [];
        array_unshift($all, [
            'id' => count($all) + 1,
            'admin_id' => $adminId,
            'action' => $action,
            'details' => $details,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        if (count($all) > 500) {
            $all = array_slice($all, 0, 500);
        }
        @file_put_contents($f, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    public static function getAuditLogs($limit = 100) {
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM seo_audit_log ORDER BY created_at DESC LIMIT :lim");
                $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll();
            } catch (Exception $e) {}
        }

        $f = self::getJsonFile('seo_audit_log');
        $all = file_exists($f) ? (json_decode(file_get_contents($f), true) ?: []) : [];
        return array_slice($all, 0, $limit);
    }

    // =========================================================================
    // 8. IMAGE SEO & ALT TEXT MANAGEMENT
    // =========================================================================

    public static function getImageAltLibrary() {
        $images = [];
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->query("SELECT pi.id, pi.image_url, pi.alt_text, pi.title_text, p.name AS product_name, p.sku
                    FROM product_images pi
                    JOIN products p ON pi.product_id = p.id
                    ORDER BY (pi.alt_text IS NULL OR pi.alt_text = '') DESC, pi.id DESC");
                $images = $stmt->fetchAll();
            } catch (Exception $e) {}
        }

        if (empty($images)) {
            // Read from product catalog
            if (class_exists('ProductModel')) {
                $prods = ProductModel::getSkus(false);
                foreach ($prods as $p) {
                    $img = $p['image'] ?? '';
                    if (!empty($img)) {
                        $images[] = [
                            'id' => $p['id'] ?? $p['sku'],
                            'image_url' => $img,
                            'alt_text' => $p['image_alt'] ?? '',
                            'title_text' => $p['image_title'] ?? '',
                            'product_name' => $p['name'] ?? '',
                            'sku' => $p['sku'] ?? $p['id']
                        ];
                    }
                }
            }
        }
        return $images;
    }

    public static function saveImageAlt($imageIdOrUrl, $altText, $titleText = '', $adminId = 'admin') {
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                if (is_numeric($imageIdOrUrl)) {
                    $stmt = $pdo->prepare("UPDATE product_images SET alt_text = :alt, title_text = :title WHERE id = :id");
                    $stmt->execute([':alt' => $altText, ':title' => $titleText, ':id' => (int)$imageIdOrUrl]);
                } else {
                    $stmt = $pdo->prepare("UPDATE product_images SET alt_text = :alt, title_text = :title WHERE image_url = :u");
                    $stmt->execute([':alt' => $altText, ':title' => $titleText, ':u' => $imageIdOrUrl]);
                }
            } catch (Exception $e) {}
        }

        // Also update JSON overrides if product exists
        $f = self::getJsonFile('edited_products');
        $edited = file_exists($f) ? (json_decode(file_get_contents($f), true) ?: []) : [];
        if (!is_numeric($imageIdOrUrl)) {
            $edited['img_alt_' . md5($imageIdOrUrl)] = ['alt' => $altText, 'title' => $titleText];
            @file_put_contents($f, json_encode($edited, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
        }

        self::flushCache();
        self::logAudit('update_image_alt', "Updated alt text for image '{$imageIdOrUrl}' to: {$altText}", $adminId);
        return true;
    }

    // =========================================================================
    // 9. ROBOTS.TXT & SITEMAP XML ENGINES
    // =========================================================================

    public static function getRobotsTxt() {
        $setting = self::getSetting('robots_txt_content');
        if (!empty($setting)) {
            return $setting;
        }
        $f = dirname(__DIR__, 2) . '/robots.txt';
        if (file_exists($f)) {
            return file_get_contents($f);
        }
        return "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /api/\n\nSitemap: https://wetruenorthgroup.com/sitemap.xml";
    }

    public static function saveRobotsTxt($content, $adminId = 'admin') {
        // Validate if admin blocked whole site
        $blocksSite = (bool)preg_match('/User-agent:\s*\*\s*\n\s*Disallow:\s*\/\s*($|\n)/i', $content);

        self::saveSettings(['robots_txt_content' => $content], $adminId);

        // Also write to physical file in root for direct web server fast access
        $rootFile = dirname(__DIR__, 2) . '/robots.txt';
        @file_put_contents($rootFile, $content);

        self::logAudit('update_robots_txt', "Updated robots.txt (Blocks all site: " . ($blocksSite ? 'YES' : 'NO') . ")", $adminId);
        return ['success' => true, 'blocks_all' => $blocksSite];
    }

    public static function generateSitemapXml() {
        $baseUrl = rtrim(self::getSetting('preferred_domain') === 'www' ? 'https://www.wetruenorthgroup.com' : 'https://wetruenorthgroup.com', '/');
        $now = date('Y-m-d');

        $staticPages = [
            ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => '/products', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => '/about', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/custom', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => '/sustainability', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => '/faq', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/blog', 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => '/contact', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/terms', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => '/privacy', 'priority' => '0.3', 'changefreq' => 'yearly']
        ];

        // Fetch categories
        $categories = [];
        if (class_exists('ProductModel')) {
            $categories = ProductModel::getCategories();
        }

        // Fetch products
        $products = [];
        if (class_exists('ProductModel')) {
            $products = ProductModel::getSkus(false);
        }

        // Fetch blog articles
        $blogPosts = [];
        if (class_exists('BlogModel')) {
            $blogPosts = BlogModel::getAllPosts();
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"\n";
        $xml .= "        xmlns:image=\"http://www.google.com/schemas/sitemap-image/1.1\">\n\n";

        // 1. Static Pages
        foreach ($staticPages as $sp) {
            $seo = self::getPageSeo($sp['loc']);
            if ($seo && empty($seo['robots_index'])) continue; // excluded by admin

            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($baseUrl . $sp['loc']) . "</loc>\n";
            $xml .= "    <lastmod>{$now}</lastmod>\n";
            $xml .= "    <changefreq>{$sp['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$sp['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        // 2. Categories
        foreach ($categories as $cat) {
            $slug = $cat['slug'] ?? '';
            if (empty($slug)) continue;
            $catUrl = '/products?category=' . urlencode($slug);
            $seo = self::getPageSeo($catUrl);
            if ($seo && empty($seo['robots_index'])) continue;

            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($baseUrl . $catUrl) . "</loc>\n";
            $xml .= "    <lastmod>{$now}</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.85</priority>\n";
            if (!empty($cat['image'])) {
                $imgSrc = (strpos($cat['image'], 'http') === 0) ? $cat['image'] : ($baseUrl . '/' . ltrim($cat['image'], '/'));
                $xml .= "    <image:image>\n";
                $xml .= "      <image:loc>" . htmlspecialchars($imgSrc) . "</image:loc>\n";
                $xml .= "      <image:title>" . htmlspecialchars($cat['name'] ?? 'Packaging') . "</image:title>\n";
                $xml .= "    </image:image>\n";
            }
            $xml .= "  </url>\n";
        }

        // 3. Products
        foreach ($products as $p) {
            $sku = $p['sku'] ?? ($p['id'] ?? '');
            if (empty($sku)) continue;
            $pUrl = '/product?id=' . urlencode($sku);
            $seo = self::getPageSeo($pUrl, 'product', $sku);
            if ($seo && empty($seo['robots_index'])) continue;

            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($baseUrl . $pUrl) . "</loc>\n";
            $xml .= "    <lastmod>" . date('Y-m-d', strtotime($p['updated_at'] ?? 'now')) . "</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.80</priority>\n";
            if (!empty($p['image'])) {
                $imgSrc = (strpos($p['image'], 'http') === 0) ? $p['image'] : ($baseUrl . '/' . ltrim($p['image'], '/'));
                $xml .= "    <image:image>\n";
                $xml .= "      <image:loc>" . htmlspecialchars($imgSrc) . "</image:loc>\n";
                $xml .= "      <image:title>" . htmlspecialchars($p['name'] ?? $sku) . "</image:title>\n";
                $xml .= "    </image:image>\n";
            }
            $xml .= "  </url>\n";
        }

        // 4. Blog Posts
        foreach ($blogPosts as $bp) {
            if (($bp['status'] ?? '') !== 'published') continue;
            $bSlug = $bp['slug'] ?? '';
            if (empty($bSlug)) continue;
            $bUrl = '/blog/' . urlencode($bSlug);

            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($baseUrl . $bUrl) . "</loc>\n";
            $xml .= "    <lastmod>" . date('Y-m-d', strtotime($bp['updated_at'] ?? ($bp['published_at'] ?? 'now'))) . "</lastmod>\n";
            $xml .= "    <changefreq>monthly</changefreq>\n";
            $xml .= "    <priority>0.75</priority>\n";
            if (!empty($bp['featured_image'])) {
                $imgSrc = (strpos($bp['featured_image'], 'http') === 0) ? $bp['featured_image'] : ($baseUrl . '/' . ltrim($bp['featured_image'], '/'));
                $xml .= "    <image:image>\n";
                $xml .= "      <image:loc>" . htmlspecialchars($imgSrc) . "</image:loc>\n";
                $xml .= "      <image:title>" . htmlspecialchars($bp['title'] ?? '') . "</image:title>\n";
                $xml .= "    </image:image>\n";
            }
            $xml .= "  </url>\n";
        }

        $xml .= "</urlset>";

        // Save physical file
        $sitemapFile = dirname(__DIR__, 2) . '/sitemap.xml';
        @file_put_contents($sitemapFile, $xml);
        return $xml;
    }

    // =========================================================================
    // 10. REAL-TIME AUDIT HEALTH CHECK
    // =========================================================================

    public static function getAuditHealth() {
        $stats = [
            'missing_title' => 0,
            'missing_desc' => 0,
            'missing_h1' => 0,
            'missing_alt' => 0,
            'duplicate_titles' => 0,
            'noindex_pages' => 0,
            'total_404_hits' => 0,
            'total_redirects' => 0,
            'redirect_loops' => 0,
            'redirect_chains' => 0,
            'total_pages_scanned' => 0,
            'seo_health_score' => 100
        ];

        // 1. Scan Redirects & 404s
        $redirects = self::getRedirects();
        $stats['total_redirects'] = count($redirects);
        foreach ($redirects as $r) {
            if (!empty($r['has_loop'])) $stats['redirect_loops']++;
            if (!empty($r['has_chain'])) $stats['redirect_chains']++;
        }

        $logs404 = self::get404Logs(500);
        foreach ($logs404 as $l) {
            $stats['total_404_hits'] += (int)($l['hits'] ?? 1);
        }

        // 2. Scan Products
        $titlesSeen = [];
        if (class_exists('ProductModel')) {
            $prods = ProductModel::getSkus(false);
            $stats['total_pages_scanned'] += count($prods);
            foreach ($prods as $p) {
                $t = $p['seoTitle'] ?? ($p['name'] ?? '');
                $d = $p['seoDescription'] ?? ($p['shortDescription'] ?? ($p['description'] ?? ''));
                if (mb_strlen(trim($t)) < 10) $stats['missing_title']++;
                if (mb_strlen(trim($d)) < 30) $stats['missing_desc']++;
                if (!empty($t)) {
                    $norm = strtolower(trim($t));
                    $titlesSeen[$norm] = ($titlesSeen[$norm] ?? 0) + 1;
                }
            }
        }

        // 3. Scan Blog Posts
        if (class_exists('BlogModel')) {
            $posts = BlogModel::getAllPosts();
            $stats['total_pages_scanned'] += count($posts);
            foreach ($posts as $bp) {
                $t = $bp['meta_title'] ?? ($bp['title'] ?? '');
                $d = $bp['meta_description'] ?? ($bp['excerpt'] ?? '');
                if (mb_strlen(trim($t)) < 10) $stats['missing_title']++;
                if (mb_strlen(trim($d)) < 30) $stats['missing_desc']++;
                if (!empty($t)) {
                    $norm = strtolower(trim($t));
                    $titlesSeen[$norm] = ($titlesSeen[$norm] ?? 0) + 1;
                }
            }
        }

        // 4. Scan Image Alt Texts
        $imgLib = self::getImageAltLibrary();
        foreach ($imgLib as $img) {
            if (empty(trim($img['alt_text'] ?? ''))) {
                $stats['missing_alt']++;
            }
        }

        // 5. Count Duplicate Titles
        foreach ($titlesSeen as $title => $count) {
            if ($count > 1) {
                $stats['duplicate_titles'] += ($count - 1);
            }
        }

        // Compute Score
        $deductions = ($stats['missing_title'] * 4) + ($stats['missing_desc'] * 2) + ($stats['missing_alt'] * 1) + ($stats['duplicate_titles'] * 3) + ($stats['redirect_loops'] * 10);
        $stats['seo_health_score'] = max(15, min(100, 100 - $deductions));

        return $stats;
    }

    public static function calculateSeoScore($title, $desc, $h1, $focusKeyword, $url, $hasImageAlt = true, $canonical = true) {
        $score = 0;
        $checks = [];

        $titleLen = mb_strlen(trim($title ?? ''));
        $descLen = mb_strlen(trim($desc ?? ''));
        $kw = strtolower(trim($focusKeyword ?? ''));

        // 1. Title Length (50-65 chars)
        if ($titleLen >= 45 && $titleLen <= 65) {
            $score += 20;
            $checks['title'] = ['status' => 'pass', 'label' => "Title length is optimal ({$titleLen} chars)"];
        } elseif ($titleLen > 0 && $titleLen < 45) {
            $score += 10;
            $checks['title'] = ['status' => 'warn', 'label' => "Title is short ({$titleLen} chars, aim for 50-60)"];
        } elseif ($titleLen > 65) {
            $score += 10;
            $checks['title'] = ['status' => 'warn', 'label' => "Title exceeds Google visible cutoff ({$titleLen} chars)"];
        } else {
            $checks['title'] = ['status' => 'fail', 'label' => "Missing Meta Title"];
        }

        // 2. Description Length (140-160 chars)
        if ($descLen >= 120 && $descLen <= 165) {
            $score += 20;
            $checks['desc'] = ['status' => 'pass', 'label' => "Meta description length is optimal ({$descLen} chars)"];
        } elseif ($descLen > 0 && $descLen < 120) {
            $score += 10;
            $checks['desc'] = ['status' => 'warn', 'label' => "Meta description is short ({$descLen} chars, aim for 140-160)"];
        } elseif ($descLen > 165) {
            $score += 10;
            $checks['desc'] = ['status' => 'warn', 'label' => "Meta description exceeds snippet length ({$descLen} chars)"];
        } else {
            $checks['desc'] = ['status' => 'fail', 'label' => "Missing Meta Description"];
        }

        // 3. Primary H1 tag
        if (!empty(trim($h1 ?? ''))) {
            $score += 15;
            $checks['h1'] = ['status' => 'pass', 'label' => "Primary H1 headline defined"];
        } else {
            $checks['h1'] = ['status' => 'warn', 'label' => "Using default page H1"];
        }

        // 4. Focus Keyword in Title, Description & URL
        if (!empty($kw)) {
            $inTitle = stripos($title, $kw) !== false;
            $inDesc = stripos($desc, $kw) !== false;
            $inUrl = stripos($url, str_replace(' ', '-', $kw)) !== false;

            if ($inTitle && $inDesc) {
                $score += 20;
                $checks['keyword'] = ['status' => 'pass', 'label' => "Focus keyword '{$kw}' present in title and description"];
            } elseif ($inTitle || $inDesc) {
                $score += 12;
                $checks['keyword'] = ['status' => 'warn', 'label' => "Focus keyword '{$kw}' present in only title or description"];
            } else {
                $checks['keyword'] = ['status' => 'fail', 'label' => "Focus keyword '{$kw}' missing from title & description"];
            }
        } else {
            $checks['keyword'] = ['status' => 'warn', 'label' => "No focus keyword designated"];
        }

        // 5. Image Alt
        if ($hasImageAlt) {
            $score += 15;
            $checks['image'] = ['status' => 'pass', 'label' => "Featured/Product image has descriptive alt text"];
        } else {
            $checks['image'] = ['status' => 'fail', 'label' => "Image missing descriptive alt text"];
        }

        // 6. Canonical
        if ($canonical) {
            $score += 10;
            $checks['canonical'] = ['status' => 'pass', 'label' => "Self-referencing canonical URL configured"];
        }

        return ['score' => min(100, $score), 'checks' => $checks];
    }

    // =========================================================================
    // 11. CACHING ENGINE
    // =========================================================================

    private static function getCache($key) {
        $file = self::getCacheDir() . '/' . md5($key) . '.cache';
        if (file_exists($file) && (time() - filemtime($file) < 3600)) {
            $data = @file_get_contents($file);
            return $data ? unserialize($data) : null;
        }
        return null;
    }

    private static function setCache($key, $data) {
        $file = self::getCacheDir() . '/' . md5($key) . '.cache';
        @file_put_contents($file, serialize($data));
    }

    public static function flushCache() {
        $dir = self::getCacheDir();
        if (is_dir($dir)) {
            $files = glob($dir . '/*.cache');
            if ($files) {
                foreach ($files as $f) {
                    @unlink($f);
                }
            }
        }
        // Also purge the full-page output cache so admin edits go live immediately
        if (class_exists('PageCache')) {
            PageCache::flush();
        }
    }

    // =========================================================================
    // 12. UNIFIED SEO HUB - entity enumeration, scoring, internal linking,
    //     breadcrumb config (powers the dashboard, bulk editor and link tools)
    // =========================================================================

    /** Hosts treated as internal for link analysis. */
    public static function siteHosts() {
        return ['wetruenorthgroup.com', 'localhost', '127.0.0.1'];
    }

    /** Breadcrumb display settings with sensible defaults. */
    public static function breadcrumbConfig() {
        $s = self::getSettings();
        return [
            'enabled'    => !isset($s['breadcrumbs_enabled']) || (string)$s['breadcrumbs_enabled'] !== '0',
            'show_home'  => !isset($s['breadcrumbs_show_home']) || (string)$s['breadcrumbs_show_home'] !== '0',
            'separator'  => (isset($s['breadcrumbs_separator']) && $s['breadcrumbs_separator'] !== '') ? $s['breadcrumbs_separator'] : '>',
            'home_label' => (isset($s['breadcrumbs_home_label']) && $s['breadcrumbs_home_label'] !== '') ? $s['breadcrumbs_home_label'] : 'Home',
        ];
    }

    /** Build one normalised SEO row (shared by dashboard and bulk editor). */
    private static function seoEntityRow($type, $refId, $url, $label, $title, $desc, $override = [], $fallbackH1 = '', $content = '') {
        $focus = $override['focus_keyword'] ?? '';
        $h1 = $override['h1_override'] ?? $fallbackH1;
        $sc = self::calculateSeoScore($title, $desc, $h1, $focus, $url, true, !empty($override['canonical_url']));
        $score = (int)($sc['score'] ?? 0);
        // "scored" = the admin has actually set SEO fields; otherwise the page uses
        // the global template/defaults and shows a neutral status (like Yoast's grey).
        $scored = (trim((string)$title) !== '' || trim((string)$desc) !== '' || trim((string)$focus) !== '');
        return [
            'type'        => $type,
            'ref_id'      => (string)$refId,
            'url'         => $url,
            'label'       => $label,
            'title'       => (string)$title,
            'desc'        => (string)$desc,
            'focus'       => (string)$focus,
            'title_len'   => mb_strlen((string)$title),
            'desc_len'    => mb_strlen((string)$desc),
            'score'       => $score,
            'scored'      => $scored,
            'status'      => !$scored ? 'none' : ($score >= 80 ? 'good' : ($score >= 50 ? 'ok' : 'poor')),
            'checks'      => $sc['checks'] ?? [],
            'noindex'     => isset($override['robots_index']) && (int)$override['robots_index'] === 0,
            'cornerstone' => !empty($override['cornerstone_content']),
            'content'     => (string)$content,
        ];
    }

    /**
     * Every indexable entity (static pages, products, published blog posts) with
     * its effective meta title/description, focus keyword, SEO score and status.
     */
    public static function getAllSeoEntities($filterType = 'all', $search = '') {
        $out = [];

        $staticPages = [
            ['/', 'home', 'Home'],
            ['/about', 'about', 'About Us'],
            ['/products', 'products', 'Products Catalogue'],
            ['/custom', 'custom', 'Custom Solutions'],
            ['/sustainability', 'sustainability', 'Sustainability'],
            ['/faq', 'faq', 'FAQ'],
            ['/contact', 'contact', 'Contact'],
            ['/terms', 'terms', 'Terms and Conditions'],
            ['/privacy', 'privacy', 'Privacy Policy'],
            ['/blog', 'blog', 'Blog Index'],
        ];
        foreach ($staticPages as $sp) {
            list($url, $ref, $label) = $sp;
            $o = self::getPageSeo($url, 'page', $ref) ?: [];
            $out[] = self::seoEntityRow('page', $ref, $url, $label, $o['meta_title'] ?? '', $o['meta_description'] ?? '', $o, $o['h1_override'] ?? $label);
        }

        if (class_exists('ProductModel')) {
            foreach (ProductModel::getSkus(false) as $p) {
                $id = $p['id'] ?? ($p['articleNo'] ?? '');
                if ($id === '') continue;
                $url = '/product?id=' . $id;
                $o = self::getPageSeo($url, 'product', $id) ?: [];
                $title = $o['meta_title'] ?? ($p['seoTitle'] ?? '');
                $desc  = $o['meta_description'] ?? ($p['seoDescription'] ?? ($p['shortDescription'] ?? ($p['description'] ?? '')));
                $out[] = self::seoEntityRow('product', $id, $url, $p['name'] ?? $id, $title, $desc, $o, $p['name'] ?? '', $p['description'] ?? '');
            }
        }

        if (class_exists('BlogModel')) {
            foreach (BlogModel::getAllPosts() as $bp) {
                if (($bp['status'] ?? 'published') !== 'published') continue;
                $slug = $bp['slug'] ?? '';
                if ($slug === '') continue;
                $url = '/blog/' . $slug;
                $o = self::getPageSeo($url, 'blog', $slug) ?: [];
                $title = $o['meta_title'] ?? ($bp['meta_title'] ?? ($bp['title'] ?? ''));
                $desc  = $o['meta_description'] ?? ($bp['meta_description'] ?? ($bp['excerpt'] ?? ''));
                $out[] = self::seoEntityRow('blog', $slug, $url, $bp['title'] ?? $slug, $title, $desc, $o, $bp['title'] ?? '', $bp['content'] ?? '');
            }
        }

        if ($filterType !== 'all' && $filterType !== '') {
            $out = array_values(array_filter($out, function ($r) use ($filterType) { return $r['type'] === $filterType; }));
        }
        if ($search !== '') {
            $needle = mb_strtolower($search);
            $out = array_values(array_filter($out, function ($r) use ($needle) {
                return mb_strpos(mb_strtolower($r['label'] . ' ' . $r['url'] . ' ' . $r['title'] . ' ' . $r['desc'] . ' ' . $r['focus']), $needle) !== false;
            }));
        }
        return $out;
    }

    /** Count internal vs outbound <a> links inside an HTML/text body. */
    public static function analyzeLinks($html) {
        $internal = 0; $outbound = 0; $targets = [];
        $hosts = array_map(function ($h) { return strtolower(preg_replace('/^www\./', '', $h)); }, self::siteHosts());
        if (preg_match_all('/<a\b[^>]*href=(?:"([^"]*)"|\'([^\']*)\')/i', (string)$html, $m, PREG_SET_ORDER)) {
            foreach ($m as $set) {
                $href = trim(html_entity_decode($set[1] !== '' ? $set[1] : ($set[2] ?? '')));
                if ($href === '' || $href[0] === '#') continue;
                if (preg_match('/^(mailto:|tel:|javascript:|whatsapp:|data:)/i', $href)) continue;
                $host = parse_url($href, PHP_URL_HOST);
                if ($host === null || $host === false) {
                    $internal++; $targets[] = $href;
                } elseif (in_array(strtolower(preg_replace('/^www\./', '', $host)), $hosts, true)) {
                    $internal++; $targets[] = $href;
                } else {
                    $outbound++;
                }
            }
        }
        return ['internal' => $internal, 'outbound' => $outbound, 'targets' => $targets];
    }

    /**
     * Link report for every blog post and product: outbound internal/external
     * counts from its own body, plus inbound internal links (orphan detection).
     */
    public static function getLinkReport() {
        $entities = array_values(array_filter(self::getAllSeoEntities(), function ($e) {
            return in_array($e['type'], ['blog', 'product'], true);
        }));
        $inbound = [];
        foreach ($entities as $e) {
            $a = self::analyzeLinks($e['content']);
            foreach ($a['targets'] as $t) {
                $path = parse_url($t, PHP_URL_PATH) ?: $t;
                $q = parse_url($t, PHP_URL_QUERY);
                $key = $path . ($q ? '?' . $q : '');
                $inbound[$key] = ($inbound[$key] ?? 0) + 1;
            }
        }
        $report = [];
        foreach ($entities as $e) {
            $a = self::analyzeLinks($e['content']);
            $report[] = [
                'type'     => $e['type'],
                'ref_id'   => $e['ref_id'],
                'url'      => $e['url'],
                'label'    => $e['label'],
                'internal' => $a['internal'],
                'outbound' => $a['outbound'],
                'inbound'  => (int)($inbound[$e['url']] ?? 0),
                'orphan'   => (int)($inbound[$e['url']] ?? 0) === 0,
            ];
        }
        return $report;
    }

    /**
     * Suggest internal links for a block of text: other entities whose focus
     * keyword or title phrase already appears in the text but is not yet linked.
     */
    public static function suggestInternalLinks($text, $excludeUrl = '', $limit = 15) {
        $plain = ' ' . mb_strtolower(preg_replace('/\s+/', ' ', strip_tags((string)$text))) . ' ';
        $suggestions = [];
        foreach (self::getAllSeoEntities() as $e) {
            if ($e['url'] === $excludeUrl || !in_array($e['type'], ['blog', 'product'], true)) continue;
            $terms = [];
            if ($e['focus'] !== '') $terms[] = $e['focus'];
            $terms[] = $e['label'];
            foreach (array_unique($terms) as $term) {
                $t = mb_strtolower(trim($term));
                if (mb_strlen($t) < 4) continue;
                if (mb_strpos($plain, ' ' . $t) !== false) {
                    $suggestions[$e['url']] = [
                        'url'    => $e['url'],
                        'label'  => $e['label'],
                        'term'   => $term,
                        'type'   => $e['type'],
                        'linked' => mb_stripos($text, $e['url']) !== false,
                    ];
                    break;
                }
            }
            if (count($suggestions) >= $limit) break;
        }
        return array_values($suggestions);
    }
}
