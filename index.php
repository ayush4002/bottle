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
require_once __DIR__ . '/app/Models/PageModel.php';
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

// Product Detail Route (e.g. /product/agro-100ml-agro-long or legacy /product?id=...)
if ($path === 'product' || $path === 'product_detail' || strpos($path, 'product/') === 0) {
    // 301 redirect legacy query-param URLs to clean slug URLs
    if (($path === 'product' || $path === 'product_detail') && !empty($_GET['id'])) {
        $legacyId = trim($_GET['id']);
        $p = class_exists('ProductModel') ? ProductModel::getPublishedSkuById($legacyId) : null;
        $slug = !empty($p['slug']) ? $p['slug'] : strtolower(trim(preg_replace('/[^a-z0-9-]+/i', '-', $legacyId), '-'));
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Location: /product/" . urlencode($slug), true, 301);
        exit();
    }

    $skuId = null;
    if (strpos($path, 'product/') === 0) {
        $parts = explode('/', $path);
        $skuId = strtolower(trim(end($parts)));
    }
    if ($skuId) {
        // Canonical redirect: if the segment is a case-variant or non-canonical alias of a
        // product (e.g. the lowercased-SKU guess "/product/cos-10ml-x" vs the real slug
        // "/product/cosmetic-10ml-x"), 301 to the canonical slug URL so there is one clean
        // URL per product. The lookup is case-insensitive (ProductModel::getSkuById).
        $resolved = class_exists('ProductModel') ? ProductModel::getSkuById($skuId) : null;
        $canonSlug = !empty($resolved['slug']) ? strtolower($resolved['slug']) : $skuId;
        if ($canonSlug !== '' && $canonSlug !== $skuId) {
            header("Cache-Control: no-cache, no-store, must-revalidate");
            header("Location: /product/" . rawurlencode($canonSlug), true, 301);
            exit();
        }
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

// Clean Category & Industry Routes: /products/{category-or-industry}
if (strpos($path, 'products/') === 0) {
    $subSlug = strtolower(trim(substr($path, 9), '/'));

    // Check Industry Markets
    $industryMap = [
        'cosmetics' => 'cosmetics',
        'pharmaceuticals' => 'pharma',
        'pharma' => 'pharma',
        'agrochemicals' => 'agro',
        'agro' => 'agro',
        'nutraceuticals' => 'nutraceuticals',
        'liquor' => 'liquor'
    ];

    if (isset($industryMap[$subSlug])) {
        // Enforce canonical names
        if ($subSlug === 'pharma') {
            header("Cache-Control: no-cache, no-store, must-revalidate");
            header("Location: /products/pharmaceuticals", true, 301);
            exit();
        }
        if ($subSlug === 'agro') {
            header("Cache-Control: no-cache, no-store, must-revalidate");
            header("Location: /products/agrochemicals", true, 301);
            exit();
        }
        $_GET['industry'] = $industryMap[$subSlug];
        $cacheKey = PageCache::keyFor('route-products-industry-' . $industryMap[$subSlug]);
        if ($cached = PageCache::get($cacheKey)) {
            PageCache::serve($cached);
        }
        ob_start();
        PageController::renderPage('products', []);
        $cachedHtml = ob_get_clean();
        PageCache::put($cacheKey, $cachedHtml, http_response_code());
        echo $cachedHtml;
        exit();
    } elseif (class_exists('ProductModel')) {
        $cat = ProductModel::getCategoryBySlug($subSlug);
        if ($cat) {
            $_GET['category'] = $subSlug;
            $cacheKey = PageCache::keyFor('route-products-category-' . $subSlug);
            if ($cached = PageCache::get($cacheKey)) {
                PageCache::serve($cached);
            }
            ob_start();
            PageController::renderPage('products', []);
            $cachedHtml = ob_get_clean();
            PageCache::put($cacheKey, $cachedHtml, http_response_code());
            echo $cachedHtml;
            exit();
        }
    }
}

// Canonical Query-Param Redirects for /products
if ($path === 'products') {
    if (!empty($_GET['category']) && empty($_GET['series']) && empty($_GET['industry']) && empty($_GET['q'])) {
        $cSlug = strtolower(trim($_GET['category']));
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Location: /products/" . urlencode($cSlug), true, 301);
        exit();
    }
    if (!empty($_GET['industry']) && empty($_GET['category']) && empty($_GET['series']) && empty($_GET['q'])) {
        $iSlug = strtolower(trim($_GET['industry']));
        $targetInd = ($iSlug === 'pharma') ? 'pharmaceuticals' : (($iSlug === 'agro') ? 'agrochemicals' : $iSlug);
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Location: /products/" . urlencode($targetInd), true, 301);
        exit();
    }
}

// Page Routes
$page = 'home';
if (!empty($path)) {
    $reqSlug = '/' . trim($path, '/');

    // 1. Dynamic Page Lookup via PageModel (handles all custom & updated slugs)
    $matchedPage = PageModel::hasPage($reqSlug);
    if ($matchedPage) {
        $page = !empty($matchedPage['template']) ? $matchedPage['template'] : (!empty($matchedPage['id']) ? $matchedPage['id'] : 'home');
    } else {
        // 2. Fallback to standard core routes and known aliases
        switch ($path) {
            case 'about':
            case 'about-us':
            case 'company':
            case 'our-company':
                $page = 'about';
                break;
            case 'products':
            case 'catalogue':
            case 'catalog':
                $page = 'products';
                break;
            case 'product':
            case 'product_detail':
                header("Location: /products", true, 301);
                exit();
            case 'contact':
            case 'contact-us':
            case 'locations':
                $page = 'contact';
                break;
            case 'custom':
            case 'custom-solutions':
                $page = 'custom';
                break;
            case 'faq':
            case 'faqs':
            case 'frequently-asked-questions':
                $page = 'faq';
                break;
            case 'sustainability':
            case 'rpet':
                $page = 'sustainability';
                break;
            case 'terms':
                header("Cache-Control: no-cache, no-store, must-revalidate");
                header("Location: /terms-conditions", true, 301);
                exit();
            case 'terms-conditions':
            case 'general-terms':
                $page = 'terms';
                break;
            case 'privacy':
                header("Cache-Control: no-cache, no-store, must-revalidate");
                header("Location: /privacy-policy", true, 301);
                exit();
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
}

// Render selected PHP page (with full-page output cache)
$cacheKey = PageCache::keyFor('route-' . $page . '-' . md5($path));
if ($cached = PageCache::get($cacheKey)) {
    PageCache::serve($cached);
}
ob_start();
PageController::renderPage($page, ['record' => $matchedPage ?? []]);
$cachedHtml = ob_get_clean();
PageCache::put($cacheKey, $cachedHtml, http_response_code());
echo $cachedHtml;
exit();
