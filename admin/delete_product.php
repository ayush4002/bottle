<?php
require_once __DIR__ . '/auth.php';
checkAdminAuth();

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Models/ProductModel.php';

$id = trim($_GET['id'] ?? '');

if (!empty($id)) {
    ProductModel::deleteProduct($id);
    header("Location: /admin/products.php?msg=" . urlencode("Product '{$id}' archived/deleted successfully."));
    exit();
}

header("Location: /admin/products.php?err=" . urlencode("No product ID specified for deletion."));
exit();
