<?php
// ==========================================================================
// TRUENORTH GROUP — STANDALONE PHP FRONT CONTROLLER & ROUTER
// Handles clean URL routing, views, API dispatching & database models
// ==========================================================================

// 1. UNIVERSAL CASE-INSENSITIVE ASSET RESOLVER (FOR LINUX/CPANEL & LOCAL DEV)
$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$ext = strtolower(pathinfo($uriPath, PATHINFO_EXTENSION));

$assetMimes = [
    'css'   => 'text/css',
    'js'    => 'application/javascript',
    'svg'   => 'image/svg+xml',
    'png'   => 'image/png',
    'jpg'   => 'image/jpeg',
    'jpeg'  => 'image/jpeg',
    'webp'  => 'image/webp',
    'gif'   => 'image/gif',
    'ico'   => 'image/x-icon',
    'woff'  => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf'   => 'font/ttf',
    'pdf'   => 'application/pdf',
];

if (isset($assetMimes[$ext])) {
    $cleanRel = ltrim($uriPath, '/');

    // Check direct file or public/ folder match
    $candidates = [
        __DIR__ . '/' . $cleanRel,
        __DIR__ . '/public/' . $cleanRel,
    ];
    foreach ($candidates as $cand) {
        if (is_file($cand)) {
            header("Content-Type: " . $assetMimes[$ext]);
            header("Cache-Control: public, max-age=31536000, immutable");
            readfile($cand);
            exit();
        }
    }

    // Case-insensitive search on Linux filesystem
    $dirPart = dirname($cleanRel);
    $filePart = basename($cleanRel);
    $searchFolders = [
        __DIR__ . ($dirPart !== '.' ? '/' . $dirPart : ''),
        __DIR__ . '/public' . ($dirPart !== '.' ? '/' . $dirPart : '')
    ];

    foreach ($searchFolders as $sf) {
        if (is_dir($sf)) {
            $scanned = @scandir($sf);
            if ($scanned) {
                foreach ($scanned as $f) {
                    if (strcasecmp($f, $filePart) === 0 && is_file($sf . '/' . $f)) {
                        header("Content-Type: " . $assetMimes[$ext]);
                        header("Cache-Control: public, max-age=31536000, immutable");
                        readfile($sf . '/' . $f);
                        exit();
                    }
                }
            }
        }
    }

    // Fallback image for missing packaging asset (prevents broken 404 image icons)
    if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'svg'])) {
        $fallbacks = [
            __DIR__ . '/brand_assets/pet_bottles/100ml_boston_white.jpeg',
            __DIR__ . '/public/brand_assets/pet_bottles/100ml_boston_white.jpeg',
            __DIR__ . '/logo_svg.svg',
            __DIR__ . '/public/logo_svg.svg'
        ];
        foreach ($fallbacks as $fb) {
            if (is_file($fb)) {
                $fbExt = strtolower(pathinfo($fb, PATHINFO_EXTENSION));
                header("Content-Type: " . ($assetMimes[$fbExt] ?? 'image/jpeg'));
                header("Cache-Control: public, max-age=86400");
                readfile($fb);
                exit();
            }
        }
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

// SEO & Crawler Direct Routes
if ($path === 'sitemap.xml' || $path === 'sitemap') {
    require_once __DIR__ . '/sitemap.php';
    exit();
}

if ($path === 'robots.txt') {
    header("Content-Type: text/plain; charset=utf-8");
    if (file_exists(__DIR__ . '/robots.txt')) {
        readfile(__DIR__ . '/robots.txt');
    }
    exit();
}

if ($path === 'site.webmanifest') {
    header("Content-Type: application/manifest+json; charset=utf-8");
    if (file_exists(__DIR__ . '/site.webmanifest')) {
        readfile(__DIR__ . '/site.webmanifest');
    }
    exit();
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

// Product Detail Route (e.g. /product?id=123 or /product/123)
if ($path === 'product' || strpos($path, 'product/') === 0) {
    $skuId = $_GET['id'] ?? null;
    if (!$skuId && strpos($path, 'product/') === 0) {
        $parts = explode('/', $path);
        $skuId = end($parts);
    }
    if ($skuId) {
        PageController::renderProductDetail($skuId);
        exit();
    }
}

// Page Routes
$page = 'home';
if (!empty($path)) {
    switch ($path) {
        case 'about':
            $page = 'about';
            break;
        case 'products':
        case 'catalogue':
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
        default:
            $page = 'home';
            break;
    }
}

// Render selected PHP page
PageController::renderPage($page);
