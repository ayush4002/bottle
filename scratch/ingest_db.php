<?php
// ==========================================================================
// TRUENORTH GROUP — DATABASE INGESTION & SYNCHRONIZATION SCRIPT
// Reads manifest_enriched.json and updates MySQL products and product_images
// ==========================================================================

require_once __DIR__ . '/../config/database.php';

$pdo = Database::getInstance()->getConnection();
if (!$pdo) {
    echo "ERROR: Unable to connect to MySQL database.\n";
    exit(1);
}

$manifestFile = __DIR__ . '/manifest_enriched.json';
if (!file_exists($manifestFile)) {
    echo "ERROR: manifest_enriched.json not found yet.\n";
    exit(1);
}

$json = file_get_contents($manifestFile);
$items = json_decode($json, true);
if (empty($items)) {
    echo "ERROR: No items in manifest.\n";
    exit(1);
}

echo "Found " . count($items) . " products in manifest.\n";

$pdo->beginTransaction();

try {
    // Disable foreign keys temporarily to truncate cleanly
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE product_images");
    $pdo->exec("TRUNCATE TABLE products");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    $prodStmt = $pdo->prepare("
        INSERT INTO products (
            sku, name, slug, category_slug, category_name, subcategory, 
            gender, short_desc, description, regular_price, sale_price, 
            stock_qty, stock_status, status, is_featured, 
            capacity, neck_finish, material, weight, moq, 
            seo_title, seo_description, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, 
            'Unisex', ?, ?, 0.00, NULL, 
            1000, 'in_stock', 'published', 0, 
            ?, ?, ?, ?, ?, 
            ?, ?, NOW(), NOW()
        )
    ");

    $imgStmt = $pdo->prepare("
        INSERT INTO product_images (product_id, image_url, is_primary, sort_order)
        VALUES (?, ?, 1, 0)
    ");

    $inserted = 0;
    foreach ($items as $item) {
        $prodStmt->execute([
            $item['sku'],
            $item['name'],
            $item['slug'],
            $item['category_slug'],
            $item['category_name'],
            $item['subcategory'],
            $item['short_desc'],
            $item['description'],
            $item['capacity'],
            $item['neck_finish'],
            $item['material'],
            $item['weight'],
            $item['moq'],
            $item['seo_title'],
            $item['seo_description']
        ]);

        $productId = (int)$pdo->lastInsertId();

        $imgStmt->execute([
            $productId,
            $item['image_url']
        ]);

        $inserted++;
    }

    $pdo->commit();
    echo "SUCCESS: Successfully inserted $inserted products and images into MySQL!\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR during DB insert: " . $e->getMessage() . "\n";
    exit(1);
}

// Verify counts
$pCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$iCount = $pdo->query("SELECT COUNT(*) FROM product_images")->fetchColumn();
echo "Verified DB Count: $pCount products, $iCount product_images.\n";
