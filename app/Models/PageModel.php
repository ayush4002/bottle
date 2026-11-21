<?php
// ==============================================================================
// TRUENORTH GROUP — PAGE CONTENT & METADATA MODEL
// Manages website static pages, banner overrides, template sections & Yoast SEO sync.
// ==============================================================================

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once __DIR__ . '/SeoModel.php';

class PageModel {

    private static $storageFile = null;

    public static function getStorageFile() {
        if (self::$storageFile === null) {
            self::$storageFile = dirname(__DIR__, 2) . '/config/cache/pages_content.json';
        }
        return self::$storageFile;
    }

    /**
     * Safe JSON encode for storage writes. json_encode() can return false when
     * page content holds invalid UTF-8; writing that false to disk would
     * truncate pages_content.json to 0 bytes and reset every custom page to
     * defaults on the next read. Substitute bad bytes instead of failing.
     */
    private static function jsonEnc($data) {
        $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        $out = json_encode($data, $flags);
        if ($out === false) {
            $out = json_encode($data, $flags | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR);
        }
        return $out === false ? '[]' : $out;
    }

    /**
     * Default core pages catalogue with initial templates, banners & Yoast SEO
     */
    public static function defaultPages() {
        $siteName = 'TrueNorth Group';
        return [
            [
                'id' => 'home',
                'title' => 'Home',
                'slug' => '/',
                'template' => 'home',
                'status' => 'published',
                'author' => 'admin',
                'order_num' => 0,
                'banner_image' => '/hero_bottles_banner.webp',
                'share_title' => 'TrueNorth Group — Precision Primary Packaging Partner',
                'banner_subtitle' => 'ISO 9001 Certified Manufacturer of PET Bottles, Cosmetic Jars, Caps & Dispensing Pumps',
                'banner_video' => '',
                'content' => '',
                'featured_image' => '/hero_bottles_banner.webp',
                'updated_at' => date('Y-m-d H:i:s'),
                'seo' => [
                    'focus_keyword' => 'primary packaging manufacturer',
                    'meta_title' => 'TrueNorth Group — Precision Primary Packaging Partner',
                    'meta_description' => 'TrueNorth Group is an ISO 9001 certified primary packaging manufacturer producing high-precision PET bottles, jars, closures, and dispensing pumps for pharma and cosmetics.',
                    'schema_type' => 'Organization',
                    'robots_index' => 1,
                    'robots_follow' => 1,
                    'cornerstone_content' => 1
                ]
            ],
            [
                'id' => 'about',
                'title' => 'About Us',
                'slug' => '/about',
                'template' => 'about',
                'status' => 'published',
                'author' => 'admin',
                'order_num' => 1,
                'banner_image' => '/hero_bottles_banner.webp',
                'share_title' => 'About Us | TrueNorth Group | Wetruenorthgroup Group',
                'banner_subtitle' => 'TrueNorth Group is an Indian multinational conglomerate company based in Gurgaon, over 25 years in primary packaging engineering.',
                'banner_video' => '',
                'content' => '',
                'featured_image' => '/hero_bottles_banner.webp',
                'updated_at' => date('Y-m-d H:i:s'),
                'seo' => [
                    'focus_keyword' => 'primary packaging manufacturer',
                    'meta_title' => 'About TrueNorth Group — Primary Packaging Manufacturer',
                    'meta_description' => 'Over 25 years of engineering excellence in precision injection blow molding, ISO 9001 certified cleanroom manufacturing, and global primary packaging supply.',
                    'schema_type' => 'AboutPage',
                    'robots_index' => 1,
                    'robots_follow' => 1,
                    'cornerstone_content' => 1
                ]
            ],
            [
                'id' => 'sustainability',
                'title' => 'Sustainability',
                'slug' => '/sustainability',
                'template' => 'sustainability',
                'status' => 'published',
                'author' => 'admin',
                'order_num' => 2,
                'banner_image' => '/hero_bottles_banner.webp',
                'share_title' => 'Sustainable rPET & PCR Primary Packaging Solutions',
                'banner_subtitle' => 'Certified Food-Grade rPET, Post-Consumer Recycled Polymers & Lightweighting Engineering',
                'banner_video' => '',
                'content' => '',
                'featured_image' => '/hero_bottles_banner.webp',
                'updated_at' => date('Y-m-d H:i:s'),
                'seo' => [
                    'focus_keyword' => 'sustainable rpet packaging',
                    'meta_title' => 'Sustainable rPET & PCR Packaging Solutions — TrueNorth Group',
                    'meta_description' => 'Certified food-grade rPET bottles, wide-mouth PCR jars, and lightweighted packaging engineered to reduce carbon footprints without compromising barrier protection.',
                    'schema_type' => 'AboutPage',
                    'robots_index' => 1,
                    'robots_follow' => 1,
                    'cornerstone_content' => 1
                ]
            ],
            [
                'id' => 'products',
                'title' => 'Products Catalogue',
                'slug' => '/products',
                'template' => 'products',
                'status' => 'published',
                'author' => 'admin',
                'order_num' => 3,
                'banner_image' => '/hero_bottles_banner.webp',
                'share_title' => 'Complete Packaging Catalogue — Bottles, Jars & Pumps',
                'banner_subtitle' => 'Precision Engineered Containers for Pharma, Personal Care & Cosmetics',
                'banner_video' => '',
                'content' => '',
                'featured_image' => '/hero_bottles_banner.webp',
                'updated_at' => date('Y-m-d H:i:s'),
                'seo' => [
                    'focus_keyword' => 'wholesale pet bottles',
                    'meta_title' => 'Products Catalogue — Wholesale PET Bottles, Jars & Pumps',
                    'meta_description' => 'Explore precision-molded PET bottles, wide-mouth pharmaceutical jars, tamper-evident caps, and cosmetic lotion pumps with technical drawings and volume pricing.',
                    'schema_type' => 'CollectionPage',
                    'robots_index' => 1,
                    'robots_follow' => 1,
                    'cornerstone_content' => 1
                ]
            ],
            [
                'id' => 'custom',
                'title' => 'Custom Solutions',
                'slug' => '/custom',
                'template' => 'custom',
                'status' => 'published',
                'author' => 'admin',
                'order_num' => 4,
                'banner_image' => '/hero_bottles_banner.webp',
                'share_title' => 'Bespoke Packaging Engineering & Rapid Mould Tooling',
                'banner_subtitle' => 'Custom 3D CAD Concept Design to Pilot Sampling in 21 Days',
                'banner_video' => '',
                'content' => '',
                'featured_image' => '/hero_bottles_banner.webp',
                'updated_at' => date('Y-m-d H:i:s'),
                'seo' => [
                    'focus_keyword' => 'custom bottle moulds',
                    'meta_title' => 'Custom Mould Development & Bespoke Packaging — TrueNorth Group',
                    'meta_description' => 'End-to-end bespoke packaging engineering: 3D CAD modeling, rapid prototype pilot moulds, custom neck finishes, and commercial blow-mould tooling in 21 days.',
                    'schema_type' => 'Service',
                    'robots_index' => 1,
                    'robots_follow' => 1,
                    'cornerstone_content' => 0
                ]
            ],
            [
                'id' => 'faq',
                'title' => 'FAQ',
                'slug' => '/faq',
                'template' => 'faq',
                'status' => 'published',
                'author' => 'admin',
                'order_num' => 5,
                'banner_image' => '/hero_bottles_banner.webp',
                'share_title' => 'Frequently Asked Questions & Packaging Specifications',
                'banner_subtitle' => 'Technical Guidance on MOQs, Resin Selection, Cleanroom & Shipping',
                'banner_video' => '',
                'content' => '',
                'featured_image' => '/hero_bottles_banner.webp',
                'updated_at' => date('Y-m-d H:i:s'),
                'seo' => [
                    'focus_keyword' => 'packaging manufacturer faq',
                    'meta_title' => 'Frequently Asked Questions & Packaging Specifications — TrueNorth Group',
                    'meta_description' => 'Find answers to common questions regarding minimum order quantities (MOQs), food-grade certifications, resin selections, lead times, and global shipping.',
                    'schema_type' => 'FAQPage',
                    'robots_index' => 1,
                    'robots_follow' => 1,
                    'cornerstone_content' => 0
                ]
            ],
            [
                'id' => 'contact',
                'title' => 'Contact Us',
                'slug' => '/contact',
                'template' => 'contact',
                'status' => 'published',
                'author' => 'admin',
                'order_num' => 6,
                'banner_image' => '/hero_bottles_banner.webp',
                'share_title' => 'Contact TrueNorth Group Sales & Global Support',
                'banner_subtitle' => 'Engineering Offices & Cleanroom Production Facilities in Delhi, Daman & Ningbo',
                'banner_video' => '',
                'content' => '',
                'featured_image' => '/hero_bottles_banner.webp',
                'updated_at' => date('Y-m-d H:i:s'),
                'seo' => [
                    'focus_keyword' => 'packaging manufacturer contact',
                    'meta_title' => 'Contact TrueNorth Group — Global Packaging Sales & Support',
                    'meta_description' => 'Connect with our packaging engineers and commercial teams in Delhi, Daman, and Ningbo. Request quotations, technical drawings, and custom tooling.',
                    'schema_type' => 'ContactPage',
                    'robots_index' => 1,
                    'robots_follow' => 1,
                    'cornerstone_content' => 0
                ]
            ],
            [
                'id' => 'blog',
                'title' => 'Blog',
                'slug' => '/blog',
                'template' => 'blog',
                'status' => 'published',
                'author' => 'admin',
                'order_num' => 7,
                'banner_image' => '/hero_bottles_banner.webp',
                'share_title' => 'Packaging Engineering Insights & Industry Articles',
                'banner_subtitle' => 'Technical Guides on Polymer Selection, Barrier Properties & Compliance',
                'banner_video' => '',
                'content' => '',
                'featured_image' => '/hero_bottles_banner.webp',
                'updated_at' => date('Y-m-d H:i:s'),
                'seo' => [
                    'focus_keyword' => 'packaging industry insights',
                    'meta_title' => 'Packaging Engineering Insights & Industry Articles — TrueNorth Group',
                    'meta_description' => 'In-depth packaging engineering guides, sustainability analyses, rPET regulation updates, and technical insights from TrueNorth Group engineers.',
                    'schema_type' => 'Blog',
                    'robots_index' => 1,
                    'robots_follow' => 1,
                    'cornerstone_content' => 0
                ]
            ],
            [
                'id' => 'terms',
                'title' => 'Terms & Conditions',
                'slug' => '/terms',
                'template' => 'terms',
                'status' => 'published',
                'author' => 'admin',
                'order_num' => 8,
                'banner_image' => '/hero_bottles_banner.webp',
                'share_title' => 'General Terms & Conditions of Supply',
                'banner_subtitle' => 'Commercial Benchmarks, Delivery Protocols & Quality Assurances',
                'banner_video' => '',
                'content' => '',
                'featured_image' => '/hero_bottles_banner.webp',
                'updated_at' => date('Y-m-d H:i:s'),
                'seo' => [
                    'focus_keyword' => 'packaging supply terms',
                    'meta_title' => 'General Terms & Conditions of Supply — TrueNorth Group',
                    'meta_description' => 'Review the commercial terms, quality benchmarks, delivery conditions, and contractual provisions governing TrueNorth Group packaging supply agreements.',
                    'schema_type' => 'WebPage',
                    'robots_index' => 1,
                    'robots_follow' => 1,
                    'cornerstone_content' => 0
                ]
            ],
            [
                'id' => 'privacy',
                'title' => 'Privacy Policy',
                'slug' => '/privacy',
                'template' => 'privacy',
                'status' => 'published',
                'author' => 'admin',
                'order_num' => 9,
                'banner_image' => '/hero_bottles_banner.webp',
                'share_title' => 'Corporate Privacy & Confidentiality Benchmark',
                'banner_subtitle' => 'Data Protection, Customer Confidentiality & NDA Frameworks',
                'banner_video' => '',
                'content' => '',
                'featured_image' => '/hero_bottles_banner.webp',
                'updated_at' => date('Y-m-d H:i:s'),
                'seo' => [
                    'focus_keyword' => 'packaging privacy policy',
                    'meta_title' => 'Privacy & Data Protection Policy — TrueNorth Group',
                    'meta_description' => 'Review how TrueNorth Group handles client proprietary designs, commercial requests, technical NDAs, and digital communication data.',
                    'schema_type' => 'WebPage',
                    'robots_index' => 1,
                    'robots_follow' => 1,
                    'cornerstone_content' => 0
                ]
            ]
        ];
    }

    /**
     * Initialize storage and seed defaults
     */
    public static function initStorage() {
        $file = self::getStorageFile();
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        if (!file_exists($file) || filesize($file) < 5) {
            $defaults = self::defaultPages();
            @file_put_contents($file, self::jsonEnc($defaults), LOCK_EX);

            // Also seed into SeoModel if not present
            foreach ($defaults as $p) {
                if (!empty($p['seo'])) {
                    $cur = SeoModel::getPageSeo($p['slug']);
                    if (!$cur || empty($cur['meta_title'])) {
                        $saveData = array_merge($p['seo'], [
                            'url_slug' => $p['slug'],
                            'page_type' => ($p['slug'] === '/' ? 'home' : 'page'),
                            'page_ref_id' => $p['id']
                        ]);
                        SeoModel::savePageSeo($saveData, 'admin');
                    }
                }
            }
        }
    }

    /**
     * Get all pages with their Yoast SEO scores
     */
    public static function getAllPages($search = '') {
        self::initStorage();
        $file = self::getStorageFile();
        $pages = json_decode(@file_get_contents($file), true) ?: self::defaultPages();

        $search = trim(strtolower($search));
        $res = [];
        foreach ($pages as $p) {
            if (!empty($search)) {
                $matched = strpos(strtolower($p['title'] ?? ''), $search) !== false
                    || strpos(strtolower($p['slug'] ?? ''), $search) !== false
                    || strpos(strtolower($p['template'] ?? ''), $search) !== false;
                if (!$matched) continue;
            }

            // Merge with live SeoModel data
            $seo = SeoModel::getPageSeo($p['slug'], ($p['slug'] === '/' ? 'home' : 'page'), $p['id']) ?: ($p['seo'] ?? []);
            
            // Calculate live Yoast SEO score
            $title = $seo['meta_title'] ?? ($p['title'] . ' | TrueNorth Group');
            $desc = $seo['meta_description'] ?? ($p['banner_subtitle'] ?? '');
            $kw = $seo['focus_keyword'] ?? '';
            $scoreData = SeoModel::calculateSeoScore($title, $desc, '', $kw, $p['slug']);
            
            $p['seo_score'] = $scoreData['score'] ?? 80;
            $p['seo_details'] = $scoreData;
            $p['focus_keyword'] = $kw;
            $p['seo_title'] = $title;
            $p['seo_desc'] = $desc;
            $p['seo_robots_index'] = isset($seo['robots_index']) ? (int)$seo['robots_index'] : 1;
            
            // Readability rating
            $descWords = str_word_count(strip_tags($desc));
            $p['readability'] = ($descWords >= 15 && $descWords <= 40) ? 'Good' : 'Needs improvement';

            $res[] = $p;
        }

        // Sort by order_num asc
        usort($res, function($a, $b) {
            return ($a['order_num'] ?? 0) <=> ($b['order_num'] ?? 0);
        });

        return $res;
    }

    /**
     * Check if a page actually exists in storage by slug
     */
    public static function hasPage($slug) {
        self::initStorage();
        $slug = '/' . ltrim(trim($slug), '/');
        if ($slug === '//') $slug = '/';
        $normSlug = ($slug !== '/') ? rtrim($slug, '/') : '/';

        $file = self::getStorageFile();
        $pages = json_decode(@file_get_contents($file), true) ?: self::defaultPages();

        foreach ($pages as $p) {
            $pSlug = '/' . ltrim(trim($p['slug'] ?? ''), '/');
            $normPSlug = ($pSlug !== '/') ? rtrim($pSlug, '/') : '/';
            if ($normPSlug === $normSlug) {
                return $p;
            }
        }
        return null;
    }

    /**
     * Update page slug across pages content storage and create 301 redirect
     */
    public static function updatePageSlug($oldSlug, $newSlug, $adminUser = 'admin') {
        self::initStorage();
        $oldSlug = '/' . ltrim(trim($oldSlug), '/');
        $newSlug = '/' . ltrim(trim($newSlug), '/');
        if ($oldSlug === $newSlug) return true;

        $file = self::getStorageFile();
        $pages = json_decode(@file_get_contents($file), true) ?: self::defaultPages();
        $found = false;

        foreach ($pages as &$p) {
            if ($p['slug'] === $oldSlug || (isset($p['id']) && '/' . ltrim($p['id'], '/') === $oldSlug)) {
                $p['slug'] = $newSlug;
                $p['updated_at'] = date('Y-m-d H:i:s');
                $found = true;
                break;
            }
        }
        unset($p);

        if ($found) {
            @file_put_contents($file, self::jsonEnc($pages), LOCK_EX);
            SeoModel::saveRedirect($oldSlug, $newSlug, 301, 1, $adminUser);
            if (class_exists('PageCache')) {
                PageCache::flush();
            }
        }
        return $found;
    }

    /**
     * Get single page by slug
     */
    public static function getPageBySlug($slug) {
        self::initStorage();
        $slug = '/' . ltrim(trim($slug), '/');
        if ($slug === '//') $slug = '/';

        $file = self::getStorageFile();
        $pages = json_decode(@file_get_contents($file), true) ?: self::defaultPages();

        foreach ($pages as $p) {
            if ($p['slug'] === $slug) {
                // Merge with SeoModel
                $seo = SeoModel::getPageSeo($p['slug'], ($p['slug'] === '/' ? 'home' : 'page'), $p['id']) ?: ($p['seo'] ?? []);
                $p['seo'] = $seo;
                return $p;
            }
        }

        // If not found, return a fallback template
        $cleanId = trim($slug, '/');
        return [
            'id' => !empty($cleanId) ? $cleanId : 'home',
            'title' => ucwords(str_replace(['-', '_'], ' ', !empty($cleanId) ? $cleanId : 'Home')),
            'slug' => $slug,
            'template' => 'default',
            'status' => 'published',
            'author' => 'admin',
            'order_num' => 99,
            'banner_image' => '/hero_bottles_banner.webp',
            'share_title' => ucwords(str_replace(['-', '_'], ' ', !empty($cleanId) ? $cleanId : 'Home')) . ' — TrueNorth Group',
            'banner_subtitle' => 'Primary packaging solutions by TrueNorth Group.',
            'banner_video' => '',
            'content' => '',
            'featured_image' => '/hero_bottles_banner.webp',
            'updated_at' => date('Y-m-d H:i:s'),
            'seo' => SeoModel::getPageSeo($slug) ?: []
        ];
    }

    /**
     * Save page content, banner settings & Yoast SEO
     */
    public static function savePage($data, $adminUser = 'admin') {
        self::initStorage();
        $slug = '/' . ltrim(trim($data['slug'] ?? '/'), '/');
        if ($slug === '//') $slug = '/';
        
        $oldSlug = !empty($data['old_slug']) ? ('/' . ltrim(trim($data['old_slug']), '/')) : null;
        if ($oldSlug === '//') $oldSlug = '/';

        $file = self::getStorageFile();
        $pages = json_decode(@file_get_contents($file), true) ?: self::defaultPages();

        $pageId = !empty($data['id']) ? $data['id'] : trim($slug, '/');
        if (empty($pageId)) $pageId = 'home';

        $found = false;
        $updatedPages = [];
        foreach ($pages as $p) {
            if ($p['slug'] === $slug || ($oldSlug && $p['slug'] === $oldSlug) || ($p['id'] ?? '') === $pageId) {
                $p['id'] = $pageId;
                $p['title'] = trim($data['title'] ?? $p['title']);
                $p['slug'] = $slug;
                $p['template'] = trim($data['template'] ?? ($p['template'] ?? 'default'));
                $p['status'] = trim($data['status'] ?? ($p['status'] ?? 'published'));
                $p['banner_image'] = trim($data['banner_image'] ?? ($p['banner_image'] ?? ''));
                $p['share_title'] = trim($data['share_title'] ?? ($p['share_title'] ?? ''));
                $p['banner_subtitle'] = trim($data['banner_subtitle'] ?? ($p['banner_subtitle'] ?? ''));
                $p['banner_video'] = trim($data['banner_video'] ?? ($p['banner_video'] ?? ''));
                $p['content'] = $data['content'] ?? ($p['content'] ?? '');
                $p['featured_image'] = trim($data['featured_image'] ?? ($p['featured_image'] ?? ''));
                $p['order_num'] = (int)($data['order_num'] ?? ($p['order_num'] ?? 0));
                $p['updated_at'] = date('Y-m-d H:i:s');
                $found = true;
            }
            $updatedPages[] = $p;
        }

        if (!$found) {
            $updatedPages[] = [
                'id' => $pageId,
                'title' => trim($data['title'] ?? 'New Page'),
                'slug' => $slug,
                'template' => trim($data['template'] ?? 'default'),
                'status' => trim($data['status'] ?? 'published'),
                'author' => $adminUser,
                'order_num' => (int)($data['order_num'] ?? 99),
                'banner_image' => trim($data['banner_image'] ?? '/hero_bottles_banner.webp'),
                'share_title' => trim($data['share_title'] ?? ''),
                'banner_subtitle' => trim($data['banner_subtitle'] ?? ''),
                'banner_video' => trim($data['banner_video'] ?? ''),
                'content' => $data['content'] ?? '',
                'featured_image' => trim($data['featured_image'] ?? '/hero_bottles_banner.webp'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }

        @file_put_contents($file, self::jsonEnc($updatedPages), LOCK_EX);

        // Sync with SeoModel (Always runs so slug change registers 301 redirect and updates SEO store).
        // Seed from the existing SEO record first: the Pages editor form does not
        // expose every field (e.g. h1_override, meta_keywords are set in the
        // SEO Pages modal), and savePageSeo nulls any field it is not given —
        // so without this, saving a page here would silently wipe those values.
        $seoData = (isset($data['seo']) && is_array($data['seo'])) ? $data['seo'] : [];
        $existingSeo = SeoModel::getPageSeo($oldSlug ?: $slug, ($slug === '/' ? 'home' : 'page'), $pageId);
        $existingSeo = is_array($existingSeo) ? $existingSeo : [];
        $seoSave = array_merge($existingSeo, $seoData, [
            'url_slug' => $slug,
            'old_url_slug' => $oldSlug,
            'page_type' => ($slug === '/' ? 'home' : 'page'),
            'page_ref_id' => $pageId
        ]);
        if (empty($seoSave['meta_title']) && !empty($data['title'])) {
            $seoSave['meta_title'] = $data['title'];
        }
        SeoModel::savePageSeo($seoSave, $adminUser);

        // Purge full-page output cache so visitor hits reflect updates immediately
        if (class_exists('PageCache')) {
            PageCache::flush();
        }

        return true;
    }

    /**
     * Delete page and its associated SEO records
     */
    public static function deletePage($slugOrId, $adminUser = 'admin') {
        self::initStorage();
        $slug = '/' . ltrim(trim($slugOrId), '/');
        if ($slug === '//') $slug = '/';

        if ($slug === '/') return false; // Prevent deleting home page

        $file = self::getStorageFile();
        $pages = json_decode(@file_get_contents($file), true) ?: self::defaultPages();
        $filtered = array_values(array_filter($pages, function($p) use ($slug, $slugOrId) {
            return ($p['slug'] ?? '') !== $slug && ($p['id'] ?? '') !== $slugOrId;
        }));

        @file_put_contents($file, self::jsonEnc($filtered), LOCK_EX);

        // Delete SEO record from SeoModel
        if (class_exists('SeoModel')) {
            SeoModel::deletePageSeo($slug, $adminUser);
        }

        if (class_exists('PageCache')) {
            PageCache::flush();
        }

        return true;
    }
}

