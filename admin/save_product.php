<?php
require_once __DIR__ . '/auth.php';
checkAdminAuth();

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Models/ProductModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /admin/products.php");
    exit();
}

// Verify CSRF Token if present
if (!empty($_POST['csrf_token']) && !empty($_SESSION['csrf_token'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        header("Location: /admin/products.php?err=" . urlencode("CSRF token validation failed. Please try again."));
        exit();
    }
}

$isEdit = !empty($_POST['is_edit']);
$id = trim($_POST['id'] ?? '');

$name = trim($_POST['name'] ?? '');
$fullTitle = trim($_POST['fullTitle'] ?? $name);
$articleNo = trim($_POST['articleNo'] ?? '');
$websiteNo = trim($_POST['websiteNo'] ?? '');
$categorySlug = trim($_POST['categorySlug'] ?? 'pet-bottles');
$serie = trim($_POST['serie'] ?? 'Custom Solutions');
$gender = trim($_POST['gender'] ?? 'Unisex');

$regularPrice = isset($_POST['regularPrice']) && $_POST['regularPrice'] !== '' ? max(0, (float)$_POST['regularPrice']) : 0;
$salePrice = isset($_POST['salePrice']) && $_POST['salePrice'] !== '' ? max(0, (float)$_POST['salePrice']) : null;

if ($salePrice !== null && $salePrice > $regularPrice && $regularPrice > 0) {
    $salePrice = $regularPrice; // Ensure sale price <= regular price
}

$stockQty = isset($_POST['stockQty']) && $_POST['stockQty'] !== '' ? max(0, (int)$_POST['stockQty']) : 1000;
$stockStatus = trim($_POST['stockStatus'] ?? ($stockQty > 0 ? 'in_stock' : 'out_of_stock'));

// Determine status: if draft button was clicked, force draft; otherwise default to published
if (isset($_POST['submit_draft'])) {
    $status = 'draft';
} else {
    $status = !empty($_POST['status']) ? trim($_POST['status']) : 'published';
}

$isFeatured = !empty($_POST['isFeatured']);

$capacity = trim($_POST['capacity'] ?? '');
$neck = trim($_POST['neck'] ?? '');
$material = trim($_POST['material'] ?? 'PET / Plastic');
$weight = trim($_POST['weight'] ?? '');
$moq = trim($_POST['moq'] ?? '5,000 pcs');

$sizesRaw = trim($_POST['sizes'] ?? '');
$sizes = !empty($sizesRaw) ? array_map('trim', explode(',', $sizesRaw)) : [];

$video = trim($_POST['video'] ?? '');
$shortDescription = trim($_POST['shortDescription'] ?? '');
$description = trim($_POST['description'] ?? '');
$seoTitle = trim($_POST['seoTitle'] ?? '');
$seoDescription = trim($_POST['seoDescription'] ?? '');

if (empty($name)) {
    $redirectUrl = $isEdit ? "/admin/edit_product.php?id=" . urlencode($id) . "&err=" . urlencode("Product name is required.") : "/admin/add_product.php?err=" . urlencode("Product name is required.");
    header("Location: " . $redirectUrl);
    exit();
}

// Find Category Name
$categories = ProductModel::getCategories();
$categoryName = 'PET bottles';
foreach ($categories as $cat) {
    if ($cat['slug'] === $categorySlug) {
        $categoryName = $cat['name'];
        break;
    }
}

// Handle Multi-Image Uploads (Slots 1, 2, 3, 4)
$images = [];

// Helper function to handle slot upload
function handleImageSlot($slotKey, $existingKey) {
    if (isset($_FILES[$slotKey]) && $_FILES[$slotKey]['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES[$slotKey]['tmp_name'];
        $fileName = $_FILES[$slotKey]['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadDir = UPLOAD_DIR;
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $newFileName = 'prod_' . time() . '_' . rand(100, 999) . '.' . $fileExtension;
            $destPath = $uploadDir . '/' . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                @copy($destPath, PUBLIC_PATH . '/' . $newFileName);
                return UPLOAD_URL . '/' . $newFileName;
            }
        }
    }
    // Return existing image if no new file uploaded
    return trim($_POST[$existingKey] ?? '');
}

// Single product image input fallback (from add_product.php)
if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
    $img1 = handleImageSlot('product_image', 'existing_img1');
} else {
    $img1 = handleImageSlot('product_img1', 'existing_img1');
}

$img2 = handleImageSlot('product_img2', 'existing_img2');
$img3 = handleImageSlot('product_img3', 'existing_img3');
$img4 = handleImageSlot('product_img4', 'existing_img4');

if (empty($img1)) $img1 = '/logo_svg.svg';

$images = array_values(array_filter([$img1, $img2, $img3, $img4]));

$seriesList = ProductModel::getSeries();
$seriesSlug = '';
foreach ($seriesList as $sItem) {
    if (strcasecmp($sItem['name'] ?? '', $serie) === 0 || strcasecmp($sItem['slug'] ?? '', $serie) === 0) {
        $seriesSlug = $sItem['slug'];
        break;
    }
}
if (empty($seriesSlug)) {
    $seriesSlug = ProductModel::getSeriesSlugBySubcategory($serie);
}
if (empty($seriesSlug)) {
    $seriesSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $serie), '-'));
}
if (empty($seriesSlug)) $seriesSlug = 'custom-series';

if (empty($articleNo)) {
    $articleNo = 'TN-' . strtoupper(substr($categorySlug, 0, 3)) . '-' . rand(100, 999);
}

if (empty($id)) {
    $id = 'PROD-' . time() . '-' . rand(100, 999);
}

$originalSku = trim($_POST['original_sku'] ?? ($_POST['id'] ?? ''));
$dbId = !empty($_POST['db_id']) ? (int)$_POST['db_id'] : 0;
$returnUrl = trim($_POST['return_url'] ?? '');

$productData = [
    'id' => $id,
    'original_sku' => $originalSku,
    'db_id' => $dbId,
    'name' => $name,
    'fullTitle' => $fullTitle,
    'articleNo' => $articleNo,
    'websiteNo' => $websiteNo,
    'categorySlug' => $categorySlug,
    'categoryName' => $categoryName,
    'seriesSlug' => $seriesSlug,
    'serie' => $serie,
    'gender' => $gender,
    'regularPrice' => $regularPrice,
    'salePrice' => $salePrice,
    'stockQty' => $stockQty,
    'stockStatus' => $stockStatus,
    'status' => $status,
    'isFeatured' => $isFeatured,
    'image' => $img1,
    'images' => $images,
    'video' => $video,
    'capacity' => $capacity,
    'neck' => $neck,
    'material' => $material,
    'weight' => $weight,
    'moq' => $moq,
    'sizes' => $sizes,
    'shortDescription' => $shortDescription,
    'description' => $description,
    'seoTitle' => $seoTitle,
    'seoDescription' => $seoDescription
];

$saved = ProductModel::saveProduct($productData);

$isAjax = !empty($_POST['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

if ($saved) {
    if ($status === 'archived') {
        $statusText = 'archived (hidden from live site)';
    } elseif ($status === 'draft') {
        $statusText = 'saved as draft';
    } else {
        $statusText = 'published to live catalogue';
    }
    $successMsg = $isEdit ? "Product '{$name}' updated and {$statusText} successfully!" : "Product '{$name}' {$statusText} successfully!";

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => $successMsg, 'product' => $saved]);
        exit();
    }

    $redirectUrl = !empty($returnUrl) ? $returnUrl : "/admin/products.php";
    $sep = strpos($redirectUrl, '?') !== false ? '&' : '?';
    header("Location: " . $redirectUrl . $sep . "msg=" . urlencode($successMsg));
    exit();
} else {
    $errorMsg = "Failed to save product in database. Please check that SKU and product name are unique.";

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $errorMsg]);
        exit();
    }

    $targetUrl = $isEdit ? "/admin/edit_product.php?id=" . urlencode($originalSku ?: $id) . "&err=" . urlencode($errorMsg) : "/admin/add_product.php?err=" . urlencode($errorMsg);
    header("Location: " . $targetUrl);
    exit();
}
