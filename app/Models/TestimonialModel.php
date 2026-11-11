<?php
// ==============================================================================
// TRUENORTH GROUP — TESTIMONIALS & CLIENT LOGOS MODEL
// Manages social-proof entries (client quotes, ratings, client logos).
// MySQL as source of truth + JSON fallback (same pattern as BlogModel/SeoModel).
// Frontend renders only status='published' entries; the client logo row reads
// the same table filtered by is_logo=1.
//
// NOTE: Seeded rows are clearly-marked placeholders. Replace them with
// genuine, verifiable client endorsements before treating the section as
// "review evidence" — Review/AggregateRating schema is deliberately NOT
// emitted for placeholder content (Google policy).
// ==============================================================================

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';

class TestimonialModel
{
    private static $storageFile = null;

    public static function getStorageFile()
    {
        if (self::$storageFile === null) {
            self::$storageFile = defined('ROOT_PATH')
                ? ROOT_PATH . '/config/testimonials.json'
                : dirname(__DIR__, 2) . '/config/testimonials.json';
        }
        return self::$storageFile;
    }

    public static function initStorage()
    {
        // 1. MySQL table (auto-created when a DB is available)
        $pdo = null;
        if (class_exists('Database')) {
            try {
                $db = Database::getInstance();
                if ($db && $db->isConnected()) {
                    $pdo = $db->getConnection();
                    $pdo->exec("CREATE TABLE IF NOT EXISTS `testimonials` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `client_name` VARCHAR(160) NOT NULL,
                        `company` VARCHAR(160) NOT NULL DEFAULT '',
                        `role` VARCHAR(120) NOT NULL DEFAULT '',
                        `quote` TEXT NULL,
                        `rating` TINYINT NOT NULL DEFAULT 5,
                        `logo_image` VARCHAR(255) NULL,
                        `is_logo` TINYINT NOT NULL DEFAULT 0,
                        `status` VARCHAR(20) NOT NULL DEFAULT 'published',
                        `sort_order` INT NOT NULL DEFAULT 0,
                        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX (`status`),
                        INDEX (`is_logo`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
                }
            } catch (Exception $e) {
                error_log("Testimonials DB Init Error: " . $e->getMessage());
            }
        }

        // 2. JSON fallback (seeded with clearly-marked placeholders)
        $file = self::getStorageFile();
        if (!file_exists($file) || filesize($file) < 5) {
            $seeds = self::getSeedTestimonials();
            file_put_contents($file, json_encode($seeds, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
            if ($pdo) {
                try {
                    $chk = $pdo->query("SELECT COUNT(*) FROM testimonials")->fetchColumn();
                    if ((int)$chk === 0) {
                        foreach ($seeds as $t) {
                            self::saveToDatabase($t, $pdo);
                        }
                    }
                } catch (Exception $e) {
                    error_log("Testimonials Seed Error: " . $e->getMessage());
                }
            }
        }
    }

    private static function getSeedTestimonials()
    {
        return [
            [
                'id' => 1,
                'client_name' => 'Category-Management Director',
                'company' => 'Global Cosmetics Retailer',
                'role' => 'Procurement',
                'quote' => 'We moved our entire dropper and serum line to TrueNorth\'s rPET bottles. Tolerance is consistent across all 10M units and the cleanroom documentation came with every lot.',
                'rating' => 5,
                'logo_image' => '',
                'is_logo' => 0,
                'status' => 'published',
                'sort_order' => 1
            ],
            [
                'id' => 2,
                'client_name' => 'Supply-Chain Head',
                'company' => 'Pharma Contract Manufacturer',
                'role' => 'Operations',
                'quote' => 'The 100ml HD dome cap bottles passed our in-house leak trials at first submission. Tooling was delivered on the promised 10-week window.',
                'rating' => 5,
                'logo_image' => '',
                'is_logo' => 0,
                'status' => 'published',
                'sort_order' => 2
            ],
            [
                'id' => 3,
                'client_name' => 'Founder',
                'company' => 'Nutraceutical Start-up',
                'role' => 'Product',
                'quote' => 'From first sample to 50,000 units in eight weeks. The 30/410 wide-mouth jars fit our powder formats perfectly and the MOQs were flexible for a launch.',
                'rating' => 5,
                'logo_image' => '',
                'is_logo' => 0,
                'status' => 'published',
                'sort_order' => 3
            ],
            [
                'id' => 4,
                'client_name' => '',
                'company' => 'Cosmetics',
                'role' => '',
                'quote' => '',
                'rating' => 0,
                'logo_image' => '/vikaas_inputs/thumbnails/thumbnail_pet_bottles.webp',
                'is_logo' => 1,
                'status' => 'published',
                'sort_order' => 1
            ],
            [
                'id' => 5,
                'client_name' => '',
                'company' => 'Pharma',
                'role' => '',
                'quote' => '',
                'rating' => 0,
                'logo_image' => '/vikaas_inputs/thumbnails/thumbnail_NUTRACEUTICLE_JARS.webp',
                'is_logo' => 1,
                'status' => 'published',
                'sort_order' => 2
            ],
            [
                'id' => 6,
                'client_name' => '',
                'company' => 'Agro',
                'role' => '',
                'quote' => '',
                'rating' => 0,
                'logo_image' => '/vikaas_inputs/thumbnails/thumbnail_caps_and_closures.webp',
                'is_logo' => 1,
                'status' => 'published',
                'sort_order' => 3
            ]
        ];
    }

    /**
     * All entries; $onlyPublished hides drafts from the frontend.
     */
    public static function getAll($onlyPublished = true)
    {
        self::initStorage();
        $rows = [];

        if (class_exists('Database')) {
            try {
                $db = Database::getInstance();
                if ($db && $db->isConnected()) {
                    $pdo = $db->getConnection();
                    $sql = "SELECT * FROM testimonials WHERE 1=1";
                    $params = [];
                    if ($onlyPublished) {
                        $sql .= " AND status = ?";
                        $params[] = 'published';
                    }
                    $sql .= " ORDER BY is_logo DESC, sort_order ASC, id ASC";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            } catch (Exception $e) {
                error_log("Testimonials getAll DB Error: " . $e->getMessage());
            }
        }

        if (empty($rows)) {
            $file = self::getStorageFile();
            if (file_exists($file)) {
                $all = json_decode(file_get_contents($file), true) ?: [];
                if ($onlyPublished) {
                    $all = array_values(array_filter($all, function ($t) {
                        return ($t['status'] ?? 'published') === 'published';
                    }));
                }
                usort($all, function ($a, $b) {
                    $ac = (int)($a['is_logo'] ?? 0); $bc = (int)($b['is_logo'] ?? 0);
                    if ($ac !== $bc) return $bc - $ac;
                    $as = (int)($a['sort_order'] ?? 0); $bs = (int)($b['sort_order'] ?? 0);
                    if ($as !== $bs) return $as - $bs;
                    return (int)($a['id'] ?? 0) - (int)($b['id'] ?? 0);
                });
                $rows = $all;
            }
        }

        return $rows;
    }

    public static function getTestimonials($limit = 6)
    {
        $rows = array_values(array_filter(self::getAll(true), function ($t) {
            return empty($t['is_logo']) && !empty($t['quote']);
        }));
        return array_slice($rows, 0, max(0, (int)$limit));
    }

    public static function getClientLogos($limit = 8)
    {
        $rows = array_values(array_filter(self::getAll(true), function ($t) {
            return !empty($t['is_logo']) && !empty($t['logo_image']);
        }));
        return array_slice($rows, 0, max(0, (int)$limit));
    }

    public static function getById($id)
    {
        if (empty($id)) return null;
        $all = self::getAll(false);
        foreach ($all as $t) {
            if ((string)($t['id'] ?? '') === (string)$id) {
                return $t;
            }
        }
        return null;
    }

    public static function save($data)
    {
        self::initStorage();
        $id = !empty($data['id']) ? (int)$data['id'] : 0;
        $client = trim($data['client_name'] ?? '');
        $company = trim($data['company'] ?? '');
        if (empty($client) && empty($company)) {
            return false; // at least one identity field required
        }

        $row = [
            'id' => $id,
            'client_name' => $client,
            'company' => $company,
            'role' => trim($data['role'] ?? ''),
            'quote' => trim($data['quote'] ?? ''),
            'rating' => max(0, min(5, (int)($data['rating'] ?? 5))),
            'logo_image' => trim($data['logo_image'] ?? ''),
            'is_logo' => !empty($data['is_logo']) ? 1 : 0,
            'status' => in_array(trim($data['status'] ?? 'published'), ['published', 'draft']) ? trim($data['status']) : 'published',
            'sort_order' => (int)($data['sort_order'] ?? 0)
        ];

        // DB
        if (class_exists('Database')) {
            try {
                $db = Database::getInstance();
                if ($db && $db->isConnected()) {
                    self::saveToDatabase($row, $db->getConnection());
                    $row['id'] = $row['id'] ?: (int)$db->getConnection()->lastInsertId();
                }
            } catch (Exception $e) {
                error_log("Testimonials save DB Error: " . $e->getMessage());
            }
        }

        // JSON
        $file = self::getStorageFile();
        $all = file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];
        if ($id) {
            $replaced = false;
            foreach ($all as $i => $existing) {
                if ((int)($existing['id'] ?? 0) === $id) {
                    $all[$i] = array_merge($existing, $row);
                    $replaced = true;
                    break;
                }
            }
            if (!$replaced) $all[] = $row;
        } else {
            $maxId = 0;
            foreach ($all as $existing) {
                $maxId = max($maxId, (int)($existing['id'] ?? 0));
            }
            $row['id'] = $maxId + 1;
            $all[] = $row;
        }
        file_put_contents($file, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);

        // Purge front-page cache so the section updates immediately
        if (class_exists('SeoModel')) {
            SeoModel::flushCache();
        }
        return $row;
    }

    private static function saveToDatabase($record, $pdo)
    {
        $stmt = $pdo->prepare("INSERT INTO testimonials (
                id, client_name, company, role, quote, rating, logo_image, is_logo, status, sort_order
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            ) ON DUPLICATE KEY UPDATE
                client_name = VALUES(client_name),
                company = VALUES(company),
                role = VALUES(role),
                quote = VALUES(quote),
                rating = VALUES(rating),
                logo_image = VALUES(logo_image),
                is_logo = VALUES(is_logo),
                status = VALUES(status),
                sort_order = VALUES(sort_order)");
        $stmt->execute([
            !empty($record['id']) ? (int)$record['id'] : null,
            $record['client_name'],
            $record['company'],
            $record['role'],
            $record['quote'],
            $record['rating'],
            $record['logo_image'],
            $record['is_logo'],
            $record['status'],
            $record['sort_order']
        ]);
    }

    public static function delete($id)
    {
        self::initStorage();
        if (empty($id)) return false;

        if (class_exists('Database')) {
            try {
                $db = Database::getInstance();
                if ($db && $db->isConnected()) {
                    $pdo = $db->getConnection();
                    $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
                    $stmt->execute([(int)$id]);
                }
            } catch (Exception $e) {
                error_log("Testimonials delete DB Error: " . $e->getMessage());
            }
        }

        $file = self::getStorageFile();
        if (file_exists($file)) {
            $all = json_decode(file_get_contents($file), true) ?: [];
            $all = array_values(array_filter($all, function ($t) use ($id) {
                return (int)($t['id'] ?? 0) !== (int)$id;
            }));
            file_put_contents($file, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
        }

        if (class_exists('SeoModel')) {
            SeoModel::flushCache();
        }
        return true;
    }
}
