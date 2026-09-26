<?php
// ==========================================================================
// TRUENORTH GROUP — INQUIRY MODEL
// Handles persistent storage for wholesale quotes & RFQ inquiries
// Dual storage: MySQL DB (primary) + config/inquiries.json (fail-safe backup)
// ==========================================================================

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';

class InquiryModel {

    private static $jsonFile = null;

    private static function getJsonFile() {
        if (self::$jsonFile === null) {
            self::$jsonFile = (defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2)) . '/config/inquiries.json';
        }
        return self::$jsonFile;
    }

    /**
     * Auto-create inquiries table if MySQL DB is connected
     */
    public static function initTable() {
        if (class_exists('Database')) {
            $db = Database::getInstance();
            if ($db && $db->isConnected()) {
                try {
                    $pdo = $db->getConnection();
                    $sql = "CREATE TABLE IF NOT EXISTS `inquiries` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `quote_id` varchar(50) NOT NULL,
                      `name` varchar(150) NOT NULL,
                      `company` varchar(150) DEFAULT NULL,
                      `email` varchar(150) NOT NULL,
                      `phone` varchar(50) DEFAULT NULL,
                      `country` varchar(100) DEFAULT NULL,
                      `product` varchar(255) DEFAULT NULL,
                      `inquiry` text NOT NULL,
                      `status` enum('new','contacted','closed') DEFAULT 'new',
                      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (`id`),
                      KEY `quote_id` (`quote_id`),
                      KEY `status` (`status`),
                      KEY `created_at` (`created_at`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                    $pdo->exec($sql);
                } catch (Exception $e) {
                    error_log("InquiryModel::initTable Error: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Save new inquiry into MySQL and JSON backup
     */
    public static function saveInquiry($data, $quoteId = null) {
        if (empty($quoteId)) {
            $quoteId = 'INQ-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        }

        $name = trim($data['name'] ?? $data['Full_Name'] ?? $data['customer_name'] ?? 'Client');
        $company = trim($data['company'] ?? $data['Company_Name'] ?? $data['company_name'] ?? '');
        $email = trim($data['email'] ?? $data['Email'] ?? $data['Email_Address'] ?? '');
        $phone = trim($data['phone'] ?? $data['Phone'] ?? $data['phone_number'] ?? '');
        $country = trim($data['country'] ?? $data['Country'] ?? '');
        $product = trim($data['product'] ?? $data['Product_Name'] ?? $data['selected_product'] ?? '');
        $inquiry = trim($data['inquiry'] ?? $data['Inquiry_Details'] ?? $data['Additional_Requirements'] ?? $data['message'] ?? '');
        $createdAt = date('Y-m-d H:i:s');
        $status = 'new';

        $savedDbId = null;

        // 1. Save to MySQL DB
        self::initTable();
        if (class_exists('Database')) {
            $db = Database::getInstance();
            if ($db && $db->isConnected()) {
                try {
                    $pdo = $db->getConnection();
                    $stmt = $pdo->prepare("
                        INSERT INTO `inquiries` (`quote_id`, `name`, `company`, `email`, `phone`, `country`, `product`, `inquiry`, `status`, `created_at`)
                        VALUES (:quote_id, :name, :company, :email, :phone, :country, :product, :inquiry, :status, :created_at)
                    ");
                    $stmt->execute([
                        ':quote_id'   => $quoteId,
                        ':name'       => $name,
                        ':company'    => $company,
                        ':email'      => $email,
                        ':phone'      => $phone,
                        ':country'    => $country,
                        ':product'    => $product,
                        ':inquiry'    => $inquiry,
                        ':status'     => $status,
                        ':created_at' => $createdAt
                    ]);
                    $savedDbId = (int)$pdo->lastInsertId();
                } catch (Exception $e) {
                    error_log("InquiryModel::saveInquiry DB Error: " . $e->getMessage());
                }
            }
        }

        // 2. Save to JSON backup
        $record = [
            'id'         => $savedDbId ?: time(),
            'quote_id'   => $quoteId,
            'name'       => $name,
            'company'    => $company,
            'email'      => $email,
            'phone'      => $phone,
            'country'    => $country,
            'product'    => $product,
            'inquiry'    => $inquiry,
            'status'     => $status,
            'created_at' => $createdAt
        ];

        self::saveToJson($record);

        return $record;
    }

    /**
     * Get all inquiries with optional status filtering & search query
     */
    public static function getAllInquiries($statusFilter = null, $searchQuery = null) {
        self::initTable();

        $results = [];
        $dbLoaded = false;

        // 1. Try MySQL
        if (class_exists('Database')) {
            $db = Database::getInstance();
            if ($db && $db->isConnected()) {
                try {
                    $pdo = $db->getConnection();
                    $sql = "SELECT * FROM `inquiries` WHERE 1=1";
                    $params = [];

                    if (!empty($statusFilter) && $statusFilter !== 'all') {
                        $sql .= " AND `status` = :status";
                        $params[':status'] = $statusFilter;
                    }

                    if (!empty($searchQuery)) {
                        $sql .= " AND (`quote_id` LIKE :q OR `name` LIKE :q OR `company` LIKE :q OR `email` LIKE :q OR `product` LIKE :q)";
                        $params[':q'] = '%' . $searchQuery . '%';
                    }

                    $sql .= " ORDER BY `created_at` DESC, `id` DESC";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $dbLoaded = true;
                } catch (Exception $e) {
                    error_log("InquiryModel::getAllInquiries DB Error: " . $e->getMessage());
                }
            }
        }

        // 2. Fallback to JSON if DB returned empty or wasn't connected
        if (!$dbLoaded || empty($results)) {
            $jsonItems = self::getFromJson();
            if (!empty($jsonItems)) {
                $results = $jsonItems;

                if (!empty($statusFilter) && $statusFilter !== 'all') {
                    $results = array_values(array_filter($results, function($r) use ($statusFilter) {
                        return ($r['status'] ?? 'new') === $statusFilter;
                    }));
                }

                if (!empty($searchQuery)) {
                    $q = strtolower(trim($searchQuery));
                    $results = array_values(array_filter($results, function($r) use ($q) {
                        return strpos(strtolower($r['quote_id'] ?? ''), $q) !== false ||
                               strpos(strtolower($r['name'] ?? ''), $q) !== false ||
                               strpos(strtolower($r['company'] ?? ''), $q) !== false ||
                               strpos(strtolower($r['email'] ?? ''), $q) !== false ||
                               strpos(strtolower($r['product'] ?? ''), $q) !== false;
                    }));
                }
            }
        }

        return $results;
    }

    /**
     * Get single inquiry by quote_id or numeric ID
     */
    public static function getInquiry($idOrQuoteId) {
        self::initTable();

        if (class_exists('Database')) {
            $db = Database::getInstance();
            if ($db && $db->isConnected()) {
                try {
                    $pdo = $db->getConnection();
                    $stmt = $pdo->prepare("SELECT * FROM `inquiries` WHERE `id` = :id OR `quote_id` = :qid LIMIT 1");
                    $stmt->execute([':id' => $idOrQuoteId, ':qid' => $idOrQuoteId]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($row) return $row;
                } catch (Exception $e) {}
            }
        }

        $jsonItems = self::getFromJson();
        foreach ($jsonItems as $item) {
            if (($item['id'] ?? '') == $idOrQuoteId || ($item['quote_id'] ?? '') === $idOrQuoteId) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Update inquiry status ('new', 'contacted', 'closed')
     */
    public static function updateStatus($idOrQuoteId, $newStatus) {
        $allowed = ['new', 'contacted', 'closed'];
        if (!in_array($newStatus, $allowed)) return false;

        self::initTable();

        // Update DB
        if (class_exists('Database')) {
            $db = Database::getInstance();
            if ($db && $db->isConnected()) {
                try {
                    $pdo = $db->getConnection();
                    $stmt = $pdo->prepare("UPDATE `inquiries` SET `status` = :st WHERE `id` = :id OR `quote_id` = :qid");
                    $stmt->execute([':st' => $newStatus, ':id' => $idOrQuoteId, ':qid' => $idOrQuoteId]);
                } catch (Exception $e) {}
            }
        }

        // Update JSON
        $items = self::getFromJson();
        foreach ($items as &$item) {
            if (($item['id'] ?? '') == $idOrQuoteId || ($item['quote_id'] ?? '') === $idOrQuoteId) {
                $item['status'] = $newStatus;
            }
        }
        file_put_contents(self::getJsonFile(), json_encode($items, JSON_PRETTY_PRINT));

        return true;
    }

    /**
     * Delete an inquiry
     */
    public static function deleteInquiry($idOrQuoteId) {
        self::initTable();

        if (class_exists('Database')) {
            $db = Database::getInstance();
            if ($db && $db->isConnected()) {
                try {
                    $pdo = $db->getConnection();
                    $stmt = $pdo->prepare("DELETE FROM `inquiries` WHERE `id` = :id OR `quote_id` = :qid");
                    $stmt->execute([':id' => $idOrQuoteId, ':qid' => $idOrQuoteId]);
                } catch (Exception $e) {}
            }
        }

        $items = self::getFromJson();
        $filtered = array_values(array_filter($items, function($item) use ($idOrQuoteId) {
            return ($item['id'] ?? '') != $idOrQuoteId && ($item['quote_id'] ?? '') !== $idOrQuoteId;
        }));
        file_put_contents(self::getJsonFile(), json_encode($filtered, JSON_PRETTY_PRINT));

        return true;
    }

    /**
     * Get aggregate inquiry statistics
     */
    public static function getStats() {
        $all = self::getAllInquiries();
        $total = count($all);
        $new = 0;
        $contacted = 0;
        $closed = 0;

        foreach ($all as $item) {
            $st = $item['status'] ?? 'new';
            if ($st === 'new') $new++;
            elseif ($st === 'contacted') $contacted++;
            elseif ($st === 'closed') $closed++;
        }

        return [
            'total'     => $total,
            'new'       => $new,
            'contacted' => $contacted,
            'closed'    => $closed
        ];
    }

    // JSON Helper Methods
    private static function getFromJson() {
        $file = self::getJsonFile();
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            if (is_array($data)) {
                usort($data, function($a, $b) {
                    return strtotime($b['created_at'] ?? 'now') - strtotime($a['created_at'] ?? 'now');
                });
                return $data;
            }
        }
        return [];
    }

    private static function saveToJson($record) {
        $file = self::getJsonFile();
        $items = self::getFromJson();
        array_unshift($items, $record);
        // Retain latest 1,000 inquiries in file
        if (count($items) > 1000) {
            $items = array_slice($items, 0, 1000);
        }
        @file_put_contents($file, json_encode($items, JSON_PRETTY_PRINT));
    }
}
