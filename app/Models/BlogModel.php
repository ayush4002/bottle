<?php
// ==============================================================================
// TRUENORTH GROUP — ENTERPRISE BLOG & SEO CONTENT MODEL
// Manages technical articles, case studies, packaging guides & complete SEO metadata.
// Supports MySQL Database as single source of truth + JSON fallback.
// ==============================================================================

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';

class BlogModel {

    private static $storageFile = null;

    public static function getStorageFile() {
        if (self::$storageFile === null) {
            self::$storageFile = defined('ROOT_PATH') 
                ? ROOT_PATH . '/config/blog_posts.json' 
                : dirname(__DIR__, 2) . '/config/blog_posts.json';
        }
        return self::$storageFile;
    }

    /**
     * Initialize blog storage and seed with default industry articles if empty
     */
    public static function initStorage() {
        $file = self::getStorageFile();
        
        // 1. Check or Create MySQL Table if DB is available
        if (class_exists('Database')) {
            try {
                $db = Database::getInstance();
                if ($db && $db->isConnected()) {
                    $pdo = $db->getConnection();
                    $pdo->exec("CREATE TABLE IF NOT EXISTS `blog_posts` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `title` VARCHAR(255) NOT NULL,
                        `slug` VARCHAR(255) NOT NULL UNIQUE,
                        `excerpt` TEXT NULL,
                        `content` MEDIUMTEXT NULL,
                        `featured_image` VARCHAR(255) NULL,
                        `category` VARCHAR(100) NOT NULL DEFAULT 'Sustainable Packaging',
                        `tags` VARCHAR(255) NULL,
                        `author_name` VARCHAR(120) NOT NULL DEFAULT 'TrueNorth Technical Division',
                        `author_role` VARCHAR(120) NOT NULL DEFAULT 'Senior Packaging Engineer',
                        `read_time` VARCHAR(50) NOT NULL DEFAULT '5 min read',
                        `status` VARCHAR(20) NOT NULL DEFAULT 'published',
                        `views_count` INT NOT NULL DEFAULT 0,
                        `meta_title` VARCHAR(255) NULL,
                        `meta_description` TEXT NULL,
                        `meta_keywords` VARCHAR(255) NULL,
                        `canonical_url` VARCHAR(255) NULL,
                        `og_title` VARCHAR(255) NULL,
                        `og_description` TEXT NULL,
                        `og_image` VARCHAR(255) NULL,
                        `twitter_title` VARCHAR(255) NULL,
                        `twitter_description` TEXT NULL,
                        `twitter_image` VARCHAR(255) NULL,
                        `robots_meta` VARCHAR(60) NOT NULL DEFAULT 'index, follow',
                        `schema_type` VARCHAR(60) NOT NULL DEFAULT 'BlogPosting',
                        `published_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX (`slug`),
                        INDEX (`category`),
                        INDEX (`status`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
                }
            } catch (Exception $e) {
                error_log("Blog DB Table Init Error: " . $e->getMessage());
            }
        }

        // 2. Check JSON File
        if (!file_exists($file) || filesize($file) < 5) {
            $seeds = self::getSeedArticles();
            file_put_contents($file, json_encode($seeds, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
            
            // Seed DB as well if empty
            if (isset($pdo)) {
                try {
                    $chk = $pdo->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
                    if ($chk == 0) {
                        foreach ($seeds as $art) {
                            self::saveToDatabase($art, $pdo);
                        }
                    }
                } catch (Exception $e) {
                    error_log("Blog DB Seed Error: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Retrieve all posts with optional filtering
     */
    public static function getAllPosts($onlyPublished = false, $category = null, $tag = null, $search = null) {
        self::initStorage();
        $posts = [];

        // 1. Try DB first
        if (class_exists('Database')) {
            try {
                $db = Database::getInstance();
                if ($db && $db->isConnected()) {
                    $pdo = $db->getConnection();
                    $sql = "SELECT * FROM blog_posts WHERE 1=1";
                    $params = [];

                    if ($onlyPublished) {
                        $sql .= " AND status = 'published'";
                    }
                    if (!empty($category) && $category !== 'all') {
                        $sql .= " AND (LOWER(category) = LOWER(?) OR LOWER(REPLACE(category, ' ', '-')) = LOWER(?))";
                        $params[] = $category;
                        $params[] = $category;
                    }
                    if (!empty($search)) {
                        $sql .= " AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ? OR tags LIKE ?)";
                        $term = '%' . $search . '%';
                        $params[] = $term;
                        $params[] = $term;
                        $params[] = $term;
                        $params[] = $term;
                    }

                    $sql .= " ORDER BY published_at DESC, id DESC";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            } catch (Exception $e) {
                error_log("Blog getAllPosts DB Error: " . $e->getMessage());
            }
        }

        // 2. JSON Fallback
        if (empty($posts)) {
            $file = self::getStorageFile();
            if (file_exists($file)) {
                $all = json_decode(file_get_contents($file), true) ?: [];
                
                $posts = array_filter($all, function($p) use ($onlyPublished, $category, $tag, $search) {
                    if ($onlyPublished && ($p['status'] ?? 'published') !== 'published') {
                        return false;
                    }
                    if (!empty($category) && $category !== 'all') {
                        $catClean = strtolower(str_replace(' ', '-', $p['category'] ?? ''));
                        $reqClean = strtolower(str_replace(' ', '-', $category));
                        if ($catClean !== $reqClean && strtolower($p['category'] ?? '') !== strtolower($category)) {
                            return false;
                        }
                    }
                    if (!empty($search)) {
                        $haystack = strtolower(($p['title'] ?? '') . ' ' . ($p['excerpt'] ?? '') . ' ' . ($p['content'] ?? '') . ' ' . ($p['tags'] ?? ''));
                        if (strpos($haystack, strtolower($search)) === false) {
                            return false;
                        }
                    }
                    return true;
                });

                // Sort newest first
                usort($posts, function($a, $b) {
                    return strtotime($b['published_at'] ?? 'now') - strtotime($a['published_at'] ?? 'now');
                });
            }
        }

        return array_values($posts);
    }

    /**
     * Get single post by URL slug
     */
    public static function getPostBySlug($slug) {
        if (empty($slug)) return null;
        self::initStorage();

        if (class_exists('Database')) {
            try {
                $db = Database::getInstance();
                if ($db && $db->isConnected()) {
                    $pdo = $db->getConnection();
                    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE slug = ? LIMIT 1");
                    $stmt->execute([$slug]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($row) return $row;
                }
            } catch (Exception $e) {
                error_log("Blog getPostBySlug DB Error: " . $e->getMessage());
            }
        }

        $all = self::getAllPosts(false);
        foreach ($all as $p) {
            if (($p['slug'] ?? '') === $slug) {
                return $p;
            }
        }
        return null;
    }

    /**
     * Get single post by ID
     */
    public static function getPostById($id) {
        if (empty($id)) return null;
        self::initStorage();

        if (class_exists('Database')) {
            try {
                $db = Database::getInstance();
                if ($db && $db->isConnected()) {
                    $pdo = $db->getConnection();
                    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ? LIMIT 1");
                    $stmt->execute([(int)$id]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($row) return $row;
                }
            } catch (Exception $e) {
                error_log("Blog getPostById DB Error: " . $e->getMessage());
            }
        }

        $all = self::getAllPosts(false);
        foreach ($all as $p) {
            if ((int)($p['id'] ?? 0) === (int)$id) {
                return $p;
            }
        }
        return null;
    }

    /**
     * Save / Update a blog article with all SEO fields
     */
    public static function savePost($data) {
        self::initStorage();
        $id = !empty($data['id']) ? (int)$data['id'] : 0;
        
        $title = trim($data['title'] ?? '');
        if (empty($title)) return false;

        $slug = trim($data['slug'] ?? '');
        if (empty($slug)) {
            $slug = self::generateSlug($title, $id);
        } else {
            $slug = self::generateSlug($slug, $id);
        }

        $excerpt = trim($data['excerpt'] ?? '');
        $content = trim($data['content'] ?? '');
        $featuredImage = trim($data['featured_image'] ?? '/vikaas_inputs/thumbnails/thumbnail_pet_bottles.webp');
        $category = trim($data['category'] ?? 'Sustainable Packaging');
        $tags = trim($data['tags'] ?? '');
        $authorName = trim($data['author_name'] ?? 'TrueNorth Technical Division');
        $authorRole = trim($data['author_role'] ?? 'Senior Packaging Engineer');
        
        // Auto calculate reading time if not explicitly provided
        $wordCount = str_word_count(strip_tags($content));
        $calculatedMins = max(1, (int)ceil($wordCount / 200));
        $readTime = !empty($data['read_time']) ? trim($data['read_time']) : "{$calculatedMins} min read";

        $status = in_array(trim($data['status'] ?? 'published'), ['published', 'draft']) ? trim($data['status']) : 'published';
        $viewsCount = isset($data['views_count']) ? (int)$data['views_count'] : 0;

        // SEO Fields
        $metaTitle = trim($data['meta_title'] ?? '');
        if (empty($metaTitle)) {
            $metaTitle = $title . ' | TrueNorth Group';
        }
        
        $metaDesc = trim($data['meta_description'] ?? '');
        if (empty($metaDesc)) {
            $metaDesc = !empty($excerpt) ? $excerpt : substr(strip_tags($content), 0, 155) . '...';
        }

        $metaKeywords = trim($data['meta_keywords'] ?? ($tags ?: 'PET bottles, primary packaging, packaging manufacturing, rPET, ISO 9001'));
        $canonicalUrl = trim($data['canonical_url'] ?? '');
        $ogTitle = trim($data['og_title'] ?? $metaTitle);
        $ogDesc = trim($data['og_description'] ?? $metaDesc);
        $ogImage = trim($data['og_image'] ?? $featuredImage);
        $twitterTitle = trim($data['twitter_title'] ?? $ogTitle);
        $twitterDesc = trim($data['twitter_description'] ?? $ogDesc);
        $twitterImage = trim($data['twitter_image'] ?? $ogImage);
        $robotsMeta = trim($data['robots_meta'] ?? ($status === 'published' ? 'index, follow' : 'noindex, nofollow'));
        $schemaType = in_array(trim($data['schema_type'] ?? 'BlogPosting'), ['BlogPosting', 'Article', 'TechArticle']) ? trim($data['schema_type']) : 'BlogPosting';

        $publishedAt = !empty($data['published_at']) ? date('Y-m-d H:i:s', strtotime($data['published_at'])) : date('Y-m-d H:i:s');
        $updatedAt = date('Y-m-d H:i:s');

        $record = [
            'id' => $id,
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $content,
            'featured_image' => $featuredImage,
            'category' => $category,
            'tags' => $tags,
            'author_name' => $authorName,
            'author_role' => $authorRole,
            'read_time' => $readTime,
            'status' => $status,
            'views_count' => $viewsCount,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDesc,
            'meta_keywords' => $metaKeywords,
            'canonical_url' => $canonicalUrl,
            'og_title' => $ogTitle,
            'og_description' => $ogDesc,
            'og_image' => $ogImage,
            'twitter_title' => $twitterTitle,
            'twitter_description' => $twitterDesc,
            'twitter_image' => $twitterImage,
            'robots_meta' => $robotsMeta,
            'schema_type' => $schemaType,
            'published_at' => $publishedAt,
            'updated_at' => $updatedAt
        ];

        // 1. Save to Database
        if (class_exists('Database')) {
            try {
                $db = Database::getInstance();
                if ($db && $db->isConnected()) {
                    $pdo = $db->getConnection();
                    $savedDbId = self::saveToDatabase($record, $pdo);
                    if ($savedDbId && empty($record['id'])) {
                        $record['id'] = (int)$savedDbId;
                    }
                }
            } catch (Exception $e) {
                error_log("Blog savePost DB Error: " . $e->getMessage());
            }
        }

        // 2. Save to JSON File
        $file = self::getStorageFile();
        $all = file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];
        
        $isExisting = false;
        if (!empty($record['id'])) {
            foreach ($all as $idx => $item) {
                if ((int)($item['id'] ?? 0) === (int)$record['id'] || ($item['slug'] ?? '') === $record['slug']) {
                    $all[$idx] = $record;
                    $isExisting = true;
                    break;
                }
            }
        }

        if (!$isExisting) {
            $maxId = 0;
            foreach ($all as $item) {
                if ((int)($item['id'] ?? 0) > $maxId) {
                    $maxId = (int)$item['id'];
                }
            }
            if (empty($record['id'])) {
                $record['id'] = $maxId + 1;
            }
            $all[] = $record;
        }

        file_put_contents($file, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);

        return $record;
    }

    /**
     * Delete blog article
     */
    public static function deletePost($id) {
        self::initStorage();
        if (empty($id)) return false;

        if (class_exists('Database')) {
            try {
                $db = Database::getInstance();
                if ($db && $db->isConnected()) {
                    $pdo = $db->getConnection();
                    $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ? OR slug = ?");
                    $stmt->execute([(int)$id, (string)$id]);
                }
            } catch (Exception $e) {
                error_log("Blog deletePost DB Error: " . $e->getMessage());
            }
        }

        $file = self::getStorageFile();
        if (file_exists($file)) {
            $all = json_decode(file_get_contents($file), true) ?: [];
            $filtered = array_values(array_filter($all, function($p) use ($id) {
                return (int)($p['id'] ?? 0) !== (int)$id && ($p['slug'] ?? '') !== (string)$id;
            }));
            file_put_contents($file, json_encode($filtered, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
        }

        return true;
    }

    /**
     * Increment view counter for an article
     */
    public static function incrementViews($slugOrId) {
        if (empty($slugOrId)) return;
        
        if (class_exists('Database')) {
            try {
                $db = Database::getInstance();
                if ($db && $db->isConnected()) {
                    $pdo = $db->getConnection();
                    $stmt = $pdo->prepare("UPDATE blog_posts SET views_count = views_count + 1 WHERE id = ? OR slug = ?");
                    $stmt->execute([(int)$slugOrId, (string)$slugOrId]);
                }
            } catch (Exception $e) {
                // non-fatal
            }
        }

        $file = self::getStorageFile();
        if (file_exists($file)) {
            $all = json_decode(file_get_contents($file), true) ?: [];
            foreach ($all as &$p) {
                if ((string)($p['id'] ?? '') === (string)$slugOrId || ($p['slug'] ?? '') === (string)$slugOrId) {
                    $p['views_count'] = ((int)($p['views_count'] ?? 0)) + 1;
                    break;
                }
            }
            file_put_contents($file, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
        }
    }

    /**
     * Get unique categories with post counts
     */
    public static function getCategories() {
        $posts = self::getAllPosts(true);
        $counts = [];
        foreach ($posts as $p) {
            $cat = $p['category'] ?? 'Packaging';
            $counts[$cat] = ($counts[$cat] ?? 0) + 1;
        }
        
        $result = [];
        foreach ($counts as $name => $count) {
            $result[] = [
                'name' => $name,
                'slug' => strtolower(str_replace([' & ', ' '], ['-', '-'], $name)),
                'count' => $count
            ];
        }
        return $result;
    }

    /**
     * Get recent posts for sidebars or related reading
     */
    public static function getRecentPosts($limit = 3, $excludeSlug = null) {
        $posts = self::getAllPosts(true);
        $filtered = [];
        foreach ($posts as $p) {
            if ($excludeSlug && ($p['slug'] ?? '') === $excludeSlug) {
                continue;
            }
            $filtered[] = $p;
            if (count($filtered) >= $limit) break;
        }
        return $filtered;
    }

    /**
     * Helper to generate unique SEO URL slug
     */
    public static function generateSlug($title, $existingId = null) {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        $slug = trim(preg_replace('/-+/', '-', $slug), '-');
        if (empty($slug)) $slug = 'packaging-article-' . time();

        // Check uniqueness
        $all = self::getAllPosts(false);
        $base = $slug;
        $counter = 1;
        
        $conflict = true;
        while ($conflict) {
            $conflict = false;
            foreach ($all as $p) {
                if (($p['slug'] ?? '') === $slug && (int)($p['id'] ?? 0) !== (int)$existingId) {
                    $conflict = true;
                    $slug = $base . '-' . $counter;
                    $counter++;
                    break;
                }
            }
        }

        return $slug;
    }

    private static function saveToDatabase($record, $pdo) {
        $stmt = $pdo->prepare("INSERT INTO blog_posts (
            id, title, slug, excerpt, content, featured_image, category, tags, author_name, author_role,
            read_time, status, views_count, meta_title, meta_description, meta_keywords, canonical_url,
            og_title, og_description, og_image, twitter_title, twitter_description, twitter_image,
            robots_meta, schema_type, published_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?
        ) ON DUPLICATE KEY UPDATE
            title = VALUES(title),
            slug = VALUES(slug),
            excerpt = VALUES(excerpt),
            content = VALUES(content),
            featured_image = VALUES(featured_image),
            category = VALUES(category),
            tags = VALUES(tags),
            author_name = VALUES(author_name),
            author_role = VALUES(author_role),
            read_time = VALUES(read_time),
            status = VALUES(status),
            views_count = VALUES(views_count),
            meta_title = VALUES(meta_title),
            meta_description = VALUES(meta_description),
            meta_keywords = VALUES(meta_keywords),
            canonical_url = VALUES(canonical_url),
            og_title = VALUES(og_title),
            og_description = VALUES(og_description),
            og_image = VALUES(og_image),
            twitter_title = VALUES(twitter_title),
            twitter_description = VALUES(twitter_description),
            twitter_image = VALUES(twitter_image),
            robots_meta = VALUES(robots_meta),
            schema_type = VALUES(schema_type),
            published_at = VALUES(published_at),
            updated_at = VALUES(updated_at)");

        $idVal = !empty($record['id']) ? (int)$record['id'] : null;

        $stmt->execute([
            $idVal,
            $record['title'],
            $record['slug'],
            $record['excerpt'] ?? '',
            $record['content'] ?? '',
            $record['featured_image'] ?? '',
            $record['category'] ?? 'Sustainable Packaging',
            $record['tags'] ?? '',
            $record['author_name'] ?? 'TrueNorth Technical Division',
            $record['author_role'] ?? 'Senior Packaging Engineer',
            $record['read_time'] ?? '5 min read',
            $record['status'] ?? 'published',
            (int)($record['views_count'] ?? 0),
            $record['meta_title'] ?? '',
            $record['meta_description'] ?? '',
            $record['meta_keywords'] ?? '',
            $record['canonical_url'] ?? '',
            $record['og_title'] ?? '',
            $record['og_description'] ?? '',
            $record['og_image'] ?? '',
            $record['twitter_title'] ?? '',
            $record['twitter_description'] ?? '',
            $record['twitter_image'] ?? '',
            $record['robots_meta'] ?? 'index, follow',
            $record['schema_type'] ?? 'BlogPosting',
            $record['published_at'] ?? date('Y-m-d H:i:s'),
            $record['updated_at'] ?? date('Y-m-d H:i:s')
        ]);

        return $pdo->lastInsertId() ?: $idVal;
    }

    /**
     * Default seed articles written with packaging authority and full SEO optimization
     */
    private static function getSeedArticles() {
        return [
            [
                'id' => 1,
                'title' => 'The Evolution of 100% Recycled PET (rPET) in Cosmetic & Personal Care Packaging',
                'slug' => 'evolution-rpet-cosmetic-packaging',
                'excerpt' => 'How advanced solid-state polycondensation (SSP) and optical sorting enable 100% rPET bottles to achieve glass-like optical clarity while meeting rigorous global sustainability mandates.',
                'content' => '<h2>The Transition from Virgin Polymer to Food-Grade Post-Consumer Resin</h2>
<p>Over the past decade, personal care and cosmetic brands have faced intense consumer and regulatory pressure to eliminate virgin fossil-based plastics. However, early adoption of Post-Consumer Recycled (PCR) PET was plagued by aesthetic compromises—notably grey hazing, yellow tinting, and molecular weight degradation during melt processing.</p>
<p>Today, through advanced mechanical recycling, multiple vacuum decontamination stages, and <strong>Solid-State Polycondensation (SSP)</strong>, TrueNorth Group delivers 100% food-grade rPET containers that rival virgin resin in mechanical tensile strength, top-load crush resistance, and crystal-clear optical brilliance.</p>

<h3>Overcoming the Optical Yellowing & Haze Challenge</h3>
<p>The primary technical hurdle in cosmetic-grade rPET is the accumulation of degradation by-products (such as acetaldehyde and cyclic oligomers) caused by repeated thermal heat histories. TrueNorth collaborates with audited European and Indian recycling partners utilising multi-spectral laser optical sorters to isolate clear flake fractions, followed by continuous vacuum-reactor polycondensation to restore intrinsic viscosity (IV) to a minimum of <strong>0.80 – 0.84 dL/g</strong>.</p>

<div class="blog-callout-box">
  <h4>Key Engineering Takeaways for Brand Specifiers</h4>
  <ul>
    <li><strong>Intrinsic Viscosity (IV) Retention:</strong> Maintains 0.82 dL/g for standard 24/410 and 28/410 neck threads without thread ovalization.</li>
    <li><strong>Regulatory Compliance:</strong> Certified non-objection status under US FDA 21 CFR and European EFSA food-contact safety standards.</li>
    <li><strong>Carbon Footprint Reduction:</strong> Up to 79% reduction in cradle-to-gate greenhouse gas emissions compared to virgin PET pellets.</li>
  </ul>
</div>

<h3>Compatibility with Color Masterbatches & UV Inhibitors</h3>
<p>Contrary to the misconception that recycled polymer limits aesthetic versatility, modern rPET readily accepts amber, cobalt blue, emerald green, and custom Pantone opaque masterbatches. Furthermore, for formulations containing delicate botanical extracts, vitamin C, or essential oils, TrueNorth incorporates specialized <em>UV-absorber additives</em> directly into the preform blow-moulding cycle, blocking harmful ultraviolet radiation up to 390nm without impairing recyclability in standard PET streams.</p>

<h3>Scalable MOQs and Rapid Prototyping</h3>
<p>Whether you require pilot test runs of 5,000 units or high-speed multi-cavity production scaling to millions of units per quarter, TrueNorth Group offers standard stock moulds alongside dedicated tooling engineering to fast-track your brand\'s circular packaging roadmap.</p>',
                'featured_image' => '/vikaas_inputs/thumbnails/thumbnail_pet_bottles.webp',
                'category' => 'Sustainable Packaging',
                'tags' => 'rPET, sustainable packaging, cosmetic bottles, PCR resin, circular economy, ISO 9001',
                'author_name' => 'TrueNorth Technical Division',
                'author_role' => 'Senior Polymer Materials Engineer',
                'read_time' => '5 min read',
                'status' => 'published',
                'views_count' => 148,
                'meta_title' => '100% rPET in Cosmetic Packaging: Clarity & Tech Standards | TrueNorth',
                'meta_description' => 'Technical guide to 100% recycled PET (rPET) for cosmetic and personal care packaging. Learn how optical sorting and SSP ensure glass-like clarity and high IV.',
                'meta_keywords' => 'rPET bottles, cosmetic packaging, recycled PET, PCR bottles, sustainable primary packaging, ISO 9001 cleanroom',
                'canonical_url' => 'https://wetruenorthgroup.com/blog/evolution-rpet-cosmetic-packaging',
                'og_title' => '100% rPET in Cosmetic Packaging: Clarity & Technical Standards',
                'og_description' => 'How advanced recycling and SSP polycondensation allow personal care brands to transition to 100% rPET without compromising on clarity or seal integrity.',
                'og_image' => 'https://wetruenorthgroup.com/vikaas_inputs/thumbnails/thumbnail_pet_bottles.webp',
                'twitter_title' => '100% rPET in Cosmetic Packaging: Clarity & Technical Standards',
                'twitter_description' => 'How advanced recycling and SSP polycondensation allow personal care brands to transition to 100% rPET without compromising on clarity or seal integrity.',
                'twitter_image' => 'https://wetruenorthgroup.com/vikaas_inputs/thumbnails/thumbnail_pet_bottles.webp',
                'robots_meta' => 'index, follow',
                'schema_type' => 'BlogPosting',
                'published_at' => '2026-10-18 10:30:00',
                'updated_at' => '2026-11-01 12:15:00'
            ],
            [
                'id' => 2,
                'title' => 'Class 10,000 Cleanroom Manufacturing: Why It Matters for Pharmaceutical PET Bottles',
                'slug' => 'cleanroom-manufacturing-pharma-pet-bottles',
                'excerpt' => 'A deep dive into controlled environment blow-moulding protocols under ISO 14644-1 Class 7/Class 10,000 to eliminate particulate contamination in oral liquid medications.',
                'content' => '<h2>The Critical Role of Environmental Particulate Control in Primary Pharma Packaging</h2>
<p>In oral liquid syrups, suspensions, and ocular formulations, airborne particulate matter poses a severe contamination risk. Traditional warehouse manufacturing environments can expose molten plastic parisons to dust, airborne fibers, and micro-contaminants during high-speed transfer.</p>
<p>To ensure total pharmaceutical compliance, TrueNorth Group operates <strong>Class 10,000 (ISO Class 7) controlled cleanrooms</strong> equipped with positive pressure differential airflow, HEPA H14 filtration, and automated laminar-flow robotic handling.</p>

<h3>HEPA Air Filtration & Static Charge Elimination</h3>
<p>Fresh ambient air undergoes multi-stage pre-filtration before passing through terminal High-Efficiency Particulate Air (HEPA) filters operating at 99.995% efficiency down to 0.3-micron particulate size. Because virgin PET generates significant triboelectric static during the injection stretch blow moulding (ISBM) cycle, ionized air blowers are integrated directly into the bottle ejection track to neutralize static cling and prevent ambient dust attraction.</p>

<div class="blog-callout-box">
  <h4>Pharmaceutical Cleanroom Quality Benchmarks</h4>
  <ul>
    <li><strong>Air Exchange Rate:</strong> Exceeding 60 HEPA-filtered air changes per hour (ACH).</li>
    <li><strong>Microbiological Bioburden:</strong> Routine routine surface contact plates and settle-plate screening.</li>
    <li><strong>Torque & Neck Finish Uniformity:</strong> Laser-micrometer thread screening for hermetic Pilfer-Proof (ROPP) and CRC child-resistant seals.</li>
  </ul>
</div>

<h3>TÜV NORD ISO 9001:2015 & cGMP Quality Documentation</h3>
<p>Every production batch is accompanied by comprehensive Certificate of Analysis (CoA) documentation detailing dimensional inspection, drop impact resistance, vacuum decay leak testing, and resin migration limits compliant with global pharmacopoeia standards (USP &lt;661&gt; and Ph. Eur. 3.1.15).</p>',
                'featured_image' => '/uploads/products/pharma-bottles/pharma-100ml-dome-pet.webp',
                'category' => 'Pharma Primary Packaging',
                'tags' => 'pharmaceutical bottles, cleanroom packaging, Class 10000 cleanroom, ISO 14644, PET syrup bottles, TÜV NORD',
                'author_name' => 'TrueNorth Technical Division',
                'author_role' => 'Quality Assurance & Regulatory Director',
                'read_time' => '6 min read',
                'status' => 'published',
                'views_count' => 112,
                'meta_title' => 'Class 10,000 Cleanroom PET Pharma Bottles | TrueNorth Group',
                'meta_description' => 'Explore the engineering protocols behind Class 10,000 cleanroom blow-moulding for pharmaceutical PET syrup bottles, particle control, and ISO compliance.',
                'meta_keywords' => 'cleanroom bottle manufacturing, Class 10000 cleanroom, pharma PET bottles, syrup packaging, ISO 9001, HEPA filtered molding',
                'canonical_url' => 'https://wetruenorthgroup.com/blog/cleanroom-manufacturing-pharma-pet-bottles',
                'og_title' => 'Class 10,000 Cleanroom Manufacturing for Pharmaceutical PET Bottles',
                'og_description' => 'Explore the engineering protocols behind Class 10,000 cleanroom blow-moulding for pharmaceutical PET syrup bottles, particle control, and ISO compliance.',
                'og_image' => 'https://wetruenorthgroup.com/uploads/products/pharma-bottles/pharma-100ml-dome-pet.webp',
                'twitter_title' => 'Class 10,000 Cleanroom Manufacturing for Pharmaceutical PET Bottles',
                'twitter_description' => 'Explore the engineering protocols behind Class 10,000 cleanroom blow-moulding for pharmaceutical PET syrup bottles, particle control, and ISO compliance.',
                'twitter_image' => 'https://wetruenorthgroup.com/uploads/products/pharma-bottles/pharma-100ml-dome-pet.webp',
                'robots_meta' => 'index, follow',
                'schema_type' => 'BlogPosting',
                'published_at' => '2026-10-25 14:00:00',
                'updated_at' => '2026-11-02 09:40:00'
            ],
            [
                'id' => 3,
                'title' => 'Lotion Pumps vs. Fine-Mist Sprayers: Technical Guide to Dispenser Dosage & Viscosity',
                'slug' => 'lotion-pumps-vs-fine-mist-sprayers-guide',
                'excerpt' => 'Selecting the right actuator dosage, dip tube diameter, and spring mechanism for formulations ranging from water-thin facial toners to high-viscosity body creams.',
                'content' => '<h2>Understanding Fluid Dynamics in Mechanical Dispensing Systems</h2>
<p>Pairing a container with an incompatible closure is the leading cause of consumer dissatisfaction in personal care and cosmetic products. Issues such as actuator priming failure, nozzle spitting, and closure leakages during air transit occur when liquid rheology does not align with the pump\'s chamber volume, ball valve density, and spring tension.</p>

<h3>Viscosity Thresholds & Output Specifications</h3>
<table style="width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 0.9rem;">
  <thead>
    <tr style="background: #16273f; color: #fff; text-align: left;">
      <th style="padding: 10px 14px; border: 1px solid #334155;">Dispenser Type</th>
      <th style="padding: 10px 14px; border: 1px solid #334155;">Target Viscosity Range</th>
      <th style="padding: 10px 14px; border: 1px solid #334155;">Standard Output Dosage</th>
      <th style="padding: 10px 14px; border: 1px solid #334155;">Common Neck Finishes</th>
    </tr>
  </thead>
  <tbody>
    <tr style="border-bottom: 1px solid #e2e8f0;">
      <td style="padding: 10px 14px; font-weight: 600;">Fine-Mist Sprayer</td>
      <td style="padding: 10px 14px;">1 – 150 cPs (Water-thin liquids)</td>
      <td style="padding: 10px 14px;">0.12ml – 0.20ml per stroke</td>
      <td style="padding: 10px 14px;">18/410, 20/410, 24/410</td>
    </tr>
    <tr style="border-bottom: 1px solid #e2e8f0;">
      <td style="padding: 10px 14px; font-weight: 600;">Treatment Serum Pump</td>
      <td style="padding: 10px 14px;">150 – 5,000 cPs (Light gels, oils)</td>
      <td style="padding: 10px 14px;">0.20ml – 0.50ml per stroke</td>
      <td style="padding: 10px 14px;">18/410, 20/410</td>
    </tr>
    <tr>
      <td style="padding: 10px 14px; font-weight: 600;">Standard Lotion Pump</td>
      <td style="padding: 10px 14px;">5,000 – 45,000 cPs (Emulsions, creams)</td>
      <td style="padding: 10px 14px;">1.20ml – 2.00ml per stroke</td>
      <td style="padding: 10px 14px;">24/410, 28/410, 33/410</td>
    </tr>
  </tbody>
</table>

<h3>External Spring vs. Product-Contact Springs</h3>
<p>For sensitive cosmeceutical formulations containing active acids (AHAs, BHAs) or high electrolyte content, standard stainless steel springs (SUS304) may undergo micro-corrosion or oxidize over prolonged shelf-life. TrueNorth supplies specialized <strong>External Spring Pumps</strong> where the metal spring is positioned entirely outside the liquid flow path, ensuring 100% purity and neutral ingredient contact.</p>

<div class="blog-callout-box">
  <h4>Pre-Shipment Seal Integrity Verification</h4>
  <p>All TrueNorth dispensing closures undergo automated vacuum-chamber leak testing at -0.06 MPa for 5 continuous minutes, followed by 360-degree inversion testing to guarantee 100% leak-proof transit across marine export containers and commercial e-commerce shipments.</p>
</div>',
                'featured_image' => '/uploads/products/pumps/lotion-pump-nb101-a.webp',
                'category' => 'Dispensing Solutions',
                'tags' => 'lotion pumps, mist sprayers, cosmetic closures, dispensing systems, 24/410 neck, packaging engineering',
                'author_name' => 'TrueNorth Technical Division',
                'author_role' => 'Senior Closure Design Specialist',
                'read_time' => '4 min read',
                'status' => 'published',
                'views_count' => 89,
                'meta_title' => 'Lotion Pumps vs. Fine Mist Sprayers: Dosage & Viscosity Guide',
                'meta_description' => 'Technical guide comparing lotion pumps and fine-mist sprayers. Learn how to select output dosages, spring types, and neck finishes for your product viscosity.',
                'meta_keywords' => 'lotion pumps wholesale, fine mist sprayers, 24-410 lotion pump, cosmetic dispensers, closure manufacturer, external spring pump',
                'canonical_url' => 'https://wetruenorthgroup.com/blog/lotion-pumps-vs-fine-mist-sprayers-guide',
                'og_title' => 'Lotion Pumps vs. Fine-Mist Sprayers: Technical Guide to Dosage & Viscosity',
                'og_description' => 'How to choose between lotion pumps and fine-mist sprayers based on fluid rheology, output dosage, and container compatibility.',
                'og_image' => 'https://wetruenorthgroup.com/uploads/products/pumps/lotion-pump-nb101-a.webp',
                'twitter_title' => 'Lotion Pumps vs. Fine-Mist Sprayers: Technical Guide to Dosage & Viscosity',
                'twitter_description' => 'How to choose between lotion pumps and fine-mist sprayers based on fluid rheology, output dosage, and container compatibility.',
                'twitter_image' => 'https://wetruenorthgroup.com/uploads/products/pumps/lotion-pump-nb101-a.webp',
                'robots_meta' => 'index, follow',
                'schema_type' => 'BlogPosting',
                'published_at' => '2026-10-30 11:20:00',
                'updated_at' => '2026-11-02 16:30:00'
            ]
        ];
    }
}

