<?php
require_once __DIR__ . '/../config/database.php';

echo "=== TRUENORTH REMOVE ALL PRODUCTS ===\n";

$db = Database::getInstance();
if ($db && $db->isConnected()) {
    try {
        $pdo = $db->getConnection();
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $pdo->exec("TRUNCATE TABLE product_images");
        $pdo->exec("TRUNCATE TABLE products");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        echo "MySQL: Truncated `products` and `product_images` successfully.\n";
        $pCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $iCount = $pdo->query("SELECT COUNT(*) FROM product_images")->fetchColumn();
        echo "MySQL Count: $pCount products, $iCount images.\n";
    } catch (Exception $e) {
        echo "MySQL Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "MySQL is not connected.\n";
}
