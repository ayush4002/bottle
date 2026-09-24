<?php
// ==========================================================================
// TRUENORTH GROUP — STANDALONE PHP FRONT CONTROLLER & ROUTER
// Handles clean URL routing, views, API dispatching & static assets
// ==========================================================================

// PHP CLI server static file check
if (php_sapi_name() === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (is_file($file) && !preg_match('/\.php$/', $file)) {
        return false;
    }
    if (preg_match('/\.(png|jpe?g|webp|gif|svg|ico)$/i', $file)) {
        header("Content-Type: image/jpeg", true, 200);
        $fallback = __DIR__ . '/brand_assets/pet_bottles/100ml_boston_white.jpeg';
        if (file_exists($fallback)) {
            readfile($fallback);
        }
        exit();
    }
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Models/ProductModel.php';
require_once __DIR__ . '/app/Controllers/PageController.php';
require_once __DIR__ . '/app/Controllers/ProductController.php';
require_once __DIR__ . '/app/Controllers/QuoteController.php';

// Parse request path
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = trim($requestUri, '/');

// Admin Routes
if ($path === 'admin' || $path === 'admin/' || $path === 'admin/index.php') {
    require_once __DIR__ . '/admin/index.php';
    exit();
}
if (strpos($path, 'admin/') === 0) {
    if (file_exists(__DIR__ . '/' . $path)) {
        require_once __DIR__ . '/' . $path;
        exit();
    }
    if (file_exists(__DIR__ . '/' . $path . '.php')) {
        require_once __DIR__ . '/' . $path . '.php';
        exit();
    }
}

// API Routes
if ($path === 'api/quote' || $path === 'quote.php') {
    QuoteController::handleInquiry();
    exit();
}

if ($path === 'api/products') {
    ProductController::handleProductsApi();
    exit();
}

if ($path === 'api/search') {
    ProductController::handleSearchApi();
    exit();
}

// Page Routes
$page = 'home';
if (!empty($path)) {
    switch ($path) {
        case 'about':
            $page = 'about';
            break;
        case 'products':
        case 'product':
            $page = 'products';
            break;
        case 'contact':
        case 'locations':
            $page = 'contact';
            break;
        case 'custom':
        case 'custom-solutions':
            $page = 'custom';
            break;
        case 'sustainability':
        case 'rpet':
            $page = 'sustainability';
            break;
        case 'market':
        case 'markets':
            $page = 'home'; // Renders home view with market anchor
            break;
        default:
            $page = 'home';
            break;
    }
}

// Render selected page
PageController::renderPage($page);
