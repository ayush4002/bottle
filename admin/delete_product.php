<?php
require_once __DIR__ . '/auth.php';
checkAdminAuth();

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Models/ProductModel.php';

$id = trim($_REQUEST['id'] ?? '');
$action = trim($_REQUEST['action'] ?? 'archive');
$returnUrl = trim($_REQUEST['return_url'] ?? ($_SERVER['HTTP_REFERER'] ?? '/admin/products.php'));
$isAjax = !empty($_REQUEST['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

if (empty($id)) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No product ID specified.']);
        exit();
    }
    header("Location: /admin/products.php?err=" . urlencode("No product ID specified."));
    exit();
}

$product = ProductModel::getSkuById($id);
$prodName = $product['name'] ?? $id;

if ($action === 'permanent') {
    ProductModel::permanentDeleteProduct($id);
    $msg = "Product '{$prodName}' permanently deleted from database.";
} elseif ($action === 'restore') {
    ProductModel::restoreProduct($id);
    $msg = "Product '{$prodName}' restored to active catalogue successfully!";
} else {
    ProductModel::deleteProduct($id);
    $msg = "Product '{$prodName}' archived successfully. It is now hidden from the live website.";
}

if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'id' => $id,
        'action' => $action,
        'message' => $msg
    ]);
    exit();
}

// Redirect back preserving current filters/page
// Clean out any old msg or err from returnUrl to avoid duplicate query params
$parsedUrl = parse_url($returnUrl);
$path = $parsedUrl['path'] ?? '/admin/products.php';
$queryParams = [];
if (!empty($parsedUrl['query'])) {
    parse_str($parsedUrl['query'], $queryParams);
}
unset($queryParams['msg'], $queryParams['err']);
$queryParams['msg'] = $msg;
$finalRedirect = $path . '?' . http_build_query($queryParams);

header("Location: " . $finalRedirect);
exit();
