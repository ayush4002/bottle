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

$isPrivateAsset = (bool)preg_match('#(^|/)\.\.(/|$)|^/?(config|app|includes|database|scripts|_archive_unused|admin/partials)(/|$)|(^|/)\.#i', (string)$uriPath);
if (isset($assetMimes[$ext]) && !$isPrivateAsset) {
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
            __DIR__ . '/logo_svg.svg',
            __DIR__ . '/public/logo_svg.svg',
            __DIR__ . '/logo.webp',
            __DIR__ . '/public/logo.webp'
        ];
        foreach ($fallbacks as $fb) {
            if (is_file($fb)) {
                $fbExt = strtolower(pathinfo($fb, PATHINFO_EXTENSION));
                http_response_code(404);
                header("Content-Type: " . ($assetMimes[$fbExt] ?? 'image/jpeg'));
                header("Cache-Control: public, max-age=3600");
                readfile($fb);
                exit();
            }
        }
    }
}

if (file_exists(__DIR__ . '/config/config.php')) {
    require_once __DIR__ . '/config/config.php';
} elseif (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}
require_once __DIR__ . '/app/Models/ProductModel.php';
require_once __DIR__ . '/app/Models/SeoModel.php';
require_once __DIR__ . '/includes/redirect_handler.php';
require_once __DIR__ . '/app/Controllers/PageController.php';
require_once __DIR__ . '/app/Controllers/ProductController.php';
require_once __DIR__ . '/app/Controllers/QuoteController.php';
require_once __DIR__ . '/app/Controllers/BlogController.php';
require_once __DIR__ . '/includes/page_cache.php';
require_once __DIR__ . '/includes/dims.php';
require_once __DIR__ . '/includes/llms.php';

// Normalise public query params: scalars only, bounded length
foreach (['category', 'series', 'industry', 'q', 'id', 'page'] as $qp) {
    if (isset($_GET[$qp])) {
        $_GET[$qp] = is_scalar($_GET[$qp]) ? mb_substr(trim((string)$_GET[$qp]), 0, 120) : '';
        if ($_GET[$qp] === '') unset($_GET[$qp]);
    }
}

// Parse request path
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = trim($requestUri, '/');

// Short URL shortcut: /login -> /admin/login
if ($path === 'login' || $path === 'login/') {
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    header("Location: /admin/login" . (!empty($qs) ? '?' . $qs : ''), true, 301);
    exit();
}

// Canonical Admin clean URLs: 301 redirect any .php requests to extensionless clean URLs
if ($path === 'admin/index.php') {
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    header("Location: /admin" . (!empty($qs) ? '?' . $qs : ''), true, 301);
    exit();
}
if (preg_match('#^admin/([a-zA-Z0-9_-]+)\.php$#i', $path, $matches)) {
    $cleanSub = $matches[1];
    $redirectTarget = ($cleanSub === 'index') ? '/admin' : '/admin/' . $cleanSub;
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    if (!empty($qs)) {
        $redirectTarget .= '?' . $qs;
    }
    header("Location: " . $redirectTarget, true, 301);
    exit();
}

// Admin Routes
if ($path === 'admin' || $path === 'admin/' || $path === 'admin/index') {
    require_once __DIR__ . '/admin/index.php';
    exit();
}
if (strpos($path, 'admin/') === 0) {
    $subPath = rtrim(substr($path, 6), '/');
    // Only top-level admin scripts (no sub-directories, no traversal, not auth/header/footer partials)
    if (preg_match('/^[a-z0-9_-]+$/i', $subPath)
        && !in_array(strtolower($subPath), ['auth', 'header', 'footer'], true)
        && is_file(__DIR__ . '/admin/' . $subPath . '.php')) {
        require_once __DIR__ . '/admin/' . $subPath . '.php';
        exit();
    }
}

// SEO & Crawler Direct Routes
if ($path === 'sitemap.xml' || $path === 'sitemap') {
    header("Content-Type: application/xml; charset=utf-8");
    header("X-Robots-Tag: noindex");
    echo SeoModel::generateSitemapXml();
    exit();
}

if ($path === 'robots.txt') {
    header("Content-Type: text/plain; charset=utf-8");
    echo SeoModel::getRobotsTxt();
    exit();
}

if ($path === 'llms.txt') {
    header("Content-Type: text/plain; charset=utf-8");
    header("X-Robots-Tag: noindex");
    echo tn_llms_txt();
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

// Blog Routes (e.g. /blog, /blog/evolution-rpet-cosmetic-packaging, /insights, /articles)
if ($path === 'blog' || $path === 'blog/' || $path === 'insights' || $path === 'articles') {
    $cacheKey = PageCache::keyFor('blog');
    if ($cached = PageCache::get($cacheKey)) {
        PageCache::serve($cached);
    }
    ob_start();
    BlogController::index();
    $cachedHtml = ob_get_clean();
    PageCache::put($cacheKey, $cachedHtml, http_response_code());
    echo $cachedHtml;
    exit();
}
if (strpos($path, 'blog/') === 0) {
    $blogSlug = trim(substr($path, 5), '/');
    if (!empty($blogSlug)) {
        $cacheKey = PageCache::keyFor('blog-' . $blogSlug);
        if ($cached = PageCache::get($cacheKey)) {
            PageCache::serve($cached);
        }
        ob_start();
        BlogController::view($blogSlug);
        $cachedHtml = ob_get_clean();
        PageCache::put($cacheKey, $cachedHtml, http_response_code());
        echo $cachedHtml;
        exit();
    }
}

// Product Detail Route (e.g. /product?id=123 or /product_detail?id=123 or /product/123)
if ($path === 'product' || $path === 'product_detail' || strpos($path, 'product/') === 0) {
    $skuId = $_GET['id'] ?? null;
    if (!$skuId && strpos($path, 'product/') === 0) {
        $parts = explode('/', $path);
        $skuId = end($parts);
    }
    if ($skuId) {
        $cacheKey = PageCache::keyFor('product-' . $skuId);
        if ($cached = PageCache::get($cacheKey)) {
            PageCache::serve($cached);
        }
        ob_start();
        PageController::renderProductDetail($skuId);
        $cachedHtml = ob_get_clean();
        PageCache::put($cacheKey, $cachedHtml, http_response_code());
        echo $cachedHtml;
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
        case 'product':
        case 'product_detail':
            header("Location: /products", true, 301);
            exit();
        case 'contact':
        case 'locations':
            $page = 'contact';
            break;
        case 'custom':
        case 'custom-solutions':
            $page = 'custom';
            break;
        case 'faq':
        case 'frequently-asked-questions':
            $page = 'faq';
            break;
        case 'sustainability':
        case 'rpet':
            $page = 'sustainability';
            break;
        case 'terms':
        case 'terms-conditions':
        case 'general-terms':
            $page = 'terms';
            break;
        case 'privacy':
        case 'privacy-policy':
        case 'privacy-statement':
            $page = 'privacy';
            break;
        default:
            http_response_code(404);
            SeoModel::log404($requestUri, $_SERVER['HTTP_REFERER'] ?? '');
            $page = '404';
            break;
    }
}

// Render selected PHP page (with full-page output cache)
$cacheKey = PageCache::keyFor('route-' . $page);
if ($cached = PageCache::get($cacheKey)) {
    PageCache::serve($cached);
}
ob_start();
PageController::renderPage($page);
$cachedHtml = ob_get_clean();
PageCache::put($cacheKey, $cachedHtml, http_response_code());
echo $cachedHtml;
exit();
