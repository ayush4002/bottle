<?php
// ==============================================================================
// TRUENORTH GROUP — ENTERPRISE DYNAMIC SEO ENGINE
// Dynamically generates canonicals, Open Graph, Twitter Cards, & Schema.org JSON-LD
// Priority: Page-level override (seo_pages) -> Meta template -> Global default
// ==============================================================================

if (empty($company) && class_exists('ProductModel')) {
    $company = ProductModel::getCompanyInfo();
}

$rootPath = dirname(__DIR__, 3);
require_once $rootPath . '/includes/seo.php';
require_once $rootPath . '/includes/schema.php';

$preferredDomain = SeoModel::getSetting('preferred_domain') ?: 'non-www';
$seoBaseUrl = ($preferredDomain === 'www') ? 'https://www.wetruenorthgroup.com' : 'https://wetruenorthgroup.com';

$seoContext = [
    'is_404' => (($activePage ?? '') === '404' || http_response_code() === 404),
    'page_type' => (($activePage ?? '') === '404' || http_response_code() === 404) ? '404' : (!empty($sku) ? 'product' : (!empty($post) ? 'blog' : (($activePage ?? '') === 'products' ? (isset($_GET['category']) ? 'category' : 'page') : 'page'))),
    'page_ref_id' => (($activePage ?? '') === '404' || http_response_code() === 404) ? '404' : (!empty($sku) ? ($sku['id'] ?? ($sku['slug'] ?? '')) : (!empty($post) ? ($post['slug'] ?? ($post['id'] ?? '')) : ($_GET['category'] ?? ($activePage ?? 'home')))),
    'product' => $sku ?? null,
    'article' => $post ?? null,
    'category' => isset($_GET['category']) ? ['slug' => $_GET['category'], 'name' => ucwords(str_replace(['-', '_'], ' ', $_GET['category']))] : null,
    'title' => $pageTitle ?? null,
    'description' => $pageDesc ?? null,
    // Note: FAQPage schema is emitted by schema.php block 7 via the FAQ repeater;
    // 'faq' is intentionally absent here.
    'static_schema' => [
        'about' => 'AboutPage',
        'contact' => 'ContactPage',
        'custom' => 'Service',
        'sustainability' => 'AboutPage'
    ][($activePage ?? '')] ?? null,
    'static_schema_name' => ['about' => 'About Us', 'contact' => 'Contact & Locations', 'custom' => 'Custom & Bespoke Solutions', 'sustainability' => 'Sustainability & rPET', 'faq' => 'Frequently Asked Questions'][($activePage ?? '')] ?? null
];
// Industry landing pages (/products?industry=x) are distinct content: give them their own
// title, description and a self-referencing canonical instead of pointing at /products.
if (($activePage ?? '') === 'products' && empty($sku) && !empty($_GET['industry']) && empty($_GET['category']) && class_exists('ProductModel')) {
    $tnIndustry = ProductModel::getIndustryBySlug((string)$_GET['industry']);
    if ($tnIndustry) {
        $seoContext['title'] = ($tnIndustry['headline'] ?? ($tnIndustry['name'] . ' Packaging')) . ' | TrueNorth Group';
        if (!empty($tnIndustry['desc'])) $seoContext['description'] = $tnIndustry['desc'];
        $seoContext['canonical'] = $seoBaseUrl . '/products?industry=' . urlencode($tnIndustry['slug'] ?? (string)$_GET['industry']);
    }
}

// Product-collection ItemList (checklist §4): fire on category filter / full catalogue
if (($activePage ?? '') === 'products' && empty($sku) && class_exists('ProductModel')) {
    $listSkus = (isset($_GET['category']) && !empty($_GET['category']))
        ? (ProductModel::getSkusByCategory($_GET['category']) ?: [])
        : array_slice(ProductModel::getSkus() ?: [], 0, 20);
    if (!empty($listSkus)) {
        $items = [];
        $n = 0;
        foreach ($listSkus as $i => $s) {
            $n++;
            $items[] = [
                'pos' => $i + 1,
                'name' => $s['name'] ?? ($s['id'] ?? 'Product'),
                'url' => $seoBaseUrl . '/product?id=' . urlencode($s['id'] ?? ($s['sku'] ?? ''))
            ];
        }
        $seoContext['item_list'] = [
            'name' => (isset($_GET['category']) ? ucwords(str_replace(['-', '_'], ' ', $_GET['category'])) . ' Collection' : 'All Products'),
            'count' => count($listSkus),
            'items' => $items
        ];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  
  <?= seo_meta($seoContext) ?>

  <!-- Mobile & PWA Configuration -->
  <link rel="manifest" href="/site.webmanifest" />
  <meta name="theme-color" content="#16273f" />
  <meta name="msapplication-config" content="/browserconfig.xml" />
  <meta name="msapplication-TileColor" content="#16273f" />
  <link rel="author" href="/humans.txt" />
  <?php
  // Stable asset version: change only when the file actually changes, so the
  // 1-year browser cache + edge cache stay effective between deploys.
  $cssFile = (defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 3)) . '/styles.css';
  $cssVersion = @filemtime($cssFile);
  ?>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" />
  <link rel="stylesheet" href="/styles.css?v=<?= $cssVersion ?: 1 ?>" />

  <?php
  // LCP: preload the homepage hero image (rendered as a CSS background in styles.css)
  if (($activePage ?? '') === 'home') {
      echo "  <link rel=\"preload\" as=\"image\" href=\"/hero_bottles_banner.webp\" fetchpriority=\"high\" />\n";
  }
  ?>

  <?php
    $tnBC = class_exists('SeoModel') ? SeoModel::breadcrumbConfig() : ['enabled'=>true,'separator'=>'>','home_label'=>'Home','show_home'=>true];
    $tnSepCss = json_encode((string)$tnBC['separator'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  ?>
  <style>
    :root{--tn-crumb-sep:<?= $tnSepCss ?>;}
    .tn-breadcrumbs .separator,nav[aria-label="Breadcrumb"] .separator{font-size:0 !important;}
    .tn-breadcrumbs .separator::before,nav[aria-label="Breadcrumb"] .separator::before{content:var(--tn-crumb-sep);font-size:0.86rem;}
    <?php if (!$tnBC['enabled']): ?>.tn-breadcrumbs,nav[aria-label="Breadcrumb"]{display:none !important;}<?php endif; ?>
  </style>
  <?= seo_schema($seoContext) ?>
</head>
<body>
  <a href="#main-content" style="position: absolute; left: -9999px; top: 0; z-index: 200; background: #16273f; color: #fff; padding: 10px 16px; border-radius: 0 0 8px 0; font-size: 0.85rem;">Skip to main content</a>

  <!-- =========================================================================
       TOP UTILITY BAR (Exact TrueNorth Top Strip)
       ========================================================================= -->
  <div class="tn-top-utility-bar">
    <div class="tn-container top-bar-inner">
      
      <!-- Search Input with Live Autocomplete -->
      <form action="/products" method="GET" class="tn-search-box" id="top-search-form" onsubmit="if(!this.q.value.trim()) return false;">
        <input type="text" name="q" aria-label="Search products and articles" id="top-search-input" placeholder="Search bottles, jars, pumps, caps..." autocomplete="off" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" />
        <button type="submit" aria-label="Search">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        </button>
        <div class="search-results-popover" id="top-search-popover"></div>
      </form>

      <!-- Top Utility Bar Right: Contact Strip & Corner Short Language Button (No Emoji) -->
      <div class="top-bar-right">
        <div class="top-bar-item top-bar-contact-group">
          <a href="tel:<?= htmlspecialchars($company['phone_tel'] ?? '+919810220761') ?>" class="top-bar-link top-bar-phone" title="Call us">
            <svg class="top-bar-icon top-bar-icon-gold" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <span><?= htmlspecialchars($company['phone'] ?? '+91 9810220761') ?></span>
          </a>
          <span class="top-bar-slash" aria-hidden="true">/</span>
          <a href="mailto:<?= htmlspecialchars($company['email'] ?? 'info@wetruenorthgroup.com') ?>" class="top-bar-link top-bar-email" title="Email us">
            <svg class="top-bar-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            <span><?= htmlspecialchars($company['email'] ?? 'info@wetruenorthgroup.com') ?></span>
          </a>
        </div>

        <span class="top-bar-divider" aria-hidden="true"></span>

        <!-- Corner Language Dropdown (Small compact menu, no popup modal) -->
        <div class="top-bar-lang-dropdown-wrapper" id="top-bar-lang-wrapper">
          <button type="button" class="top-bar-lang-btn" id="top-bar-lang-btn" onclick="window.toggleLanguageDropdown(event)" title="Select Language" aria-haspopup="true" aria-expanded="false">
            <svg class="top-bar-icon top-bar-icon-gold" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
            </svg>
            <span class="top-bar-lang-name" id="top-lang-name">EN</span>
            <svg class="top-bar-lang-arrow" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </button>

          <!-- Small Floating Dropdown Menu -->
          <div class="tn-lang-dropdown-menu" id="tn-lang-dropdown-menu" role="menu" aria-label="Select Language">
            <div class="tn-lang-dropdown-list">
              <button type="button" class="tn-lang-dropdown-item active" data-lang="en" onclick="window.selectLanguage('en', 'English', 'EN')">
                <span class="tn-lang-item-name">English</span>
                <span class="tn-lang-item-code">EN</span>
              </button>
              <button type="button" class="tn-lang-dropdown-item" data-lang="es" onclick="window.selectLanguage('es', 'Español', 'ES')">
                <span class="tn-lang-item-name">Español</span>
                <span class="tn-lang-item-code">ES</span>
              </button>
              <button type="button" class="tn-lang-dropdown-item" data-lang="fr" onclick="window.selectLanguage('fr', 'Français', 'FR')">
                <span class="tn-lang-item-name">Français</span>
                <span class="tn-lang-item-code">FR</span>
              </button>
              <button type="button" class="tn-lang-dropdown-item" data-lang="de" onclick="window.selectLanguage('de', 'Deutsch', 'DE')">
                <span class="tn-lang-item-name">Deutsch</span>
                <span class="tn-lang-item-code">DE</span>
              </button>
              <button type="button" class="tn-lang-dropdown-item" data-lang="ar" onclick="window.selectLanguage('ar', 'العربية', 'AR')">
                <span class="tn-lang-item-name">العربية</span>
                <span class="tn-lang-item-code">AR</span>
              </button>
              <button type="button" class="tn-lang-dropdown-item" data-lang="ru" onclick="window.selectLanguage('ru', 'Русский', 'RU')">
                <span class="tn-lang-item-name">Русский</span>
                <span class="tn-lang-item-code">RU</span>
              </button>
              <button type="button" class="tn-lang-dropdown-item" data-lang="zh-CN" onclick="window.selectLanguage('zh-CN', '简体中文', 'ZH')">
                <span class="tn-lang-item-name">简体中文</span>
                <span class="tn-lang-item-code">ZH</span>
              </button>
              <button type="button" class="tn-lang-dropdown-item" data-lang="ja" onclick="window.selectLanguage('ja', '日本語', 'JA')">
                <span class="tn-lang-item-name">日本語</span>
                <span class="tn-lang-item-code">JA</span>
              </button>
              <button type="button" class="tn-lang-dropdown-item" data-lang="pt" onclick="window.selectLanguage('pt', 'Português', 'PT')">
                <span class="tn-lang-item-name">Português</span>
                <span class="tn-lang-item-code">PT</span>
              </button>
              <button type="button" class="tn-lang-dropdown-item" data-lang="it" onclick="window.selectLanguage('it', 'Italiano', 'IT')">
                <span class="tn-lang-item-name">Italiano</span>
                <span class="tn-lang-item-code">IT</span>
              </button>
              <button type="button" class="tn-lang-dropdown-item" data-lang="hi" onclick="window.selectLanguage('hi', 'हिन्दी', 'HI')">
                <span class="tn-lang-item-name">हिन्दी</span>
                <span class="tn-lang-item-code">HI</span>
              </button>
              <button type="button" class="tn-lang-dropdown-item" data-lang="nl" onclick="window.selectLanguage('nl', 'Nederlands', 'NL')">
                <span class="tn-lang-item-name">Nederlands</span>
                <span class="tn-lang-item-code">NL</span>
              </button>
              <button type="button" class="tn-lang-dropdown-item" data-lang="tr" onclick="window.selectLanguage('tr', 'Türkçe', 'TR')">
                <span class="tn-lang-item-name">Türkçe</span>
                <span class="tn-lang-item-code">TR</span>
              </button>
              <button type="button" class="tn-lang-dropdown-item" data-lang="ko" onclick="window.selectLanguage('ko', '한국어', 'KO')">
                <span class="tn-lang-item-name">한국어</span>
                <span class="tn-lang-item-code">KO</span>
              </button>
              <button type="button" class="tn-lang-dropdown-item" data-lang="vi" onclick="window.selectLanguage('vi', 'Tiếng Việt', 'VI')">
                <span class="tn-lang-item-name">Tiếng Việt</span>
                <span class="tn-lang-item-code">VI</span>
              </button>
              <button type="button" class="tn-lang-dropdown-item" data-lang="id" onclick="window.selectLanguage('id', 'Bahasa Indo', 'ID')">
                <span class="tn-lang-item-name">Bahasa Indo</span>
                <span class="tn-lang-item-code">ID</span>
              </button>
              <button type="button" class="tn-lang-dropdown-item" data-lang="pl" onclick="window.selectLanguage('pl', 'Polski', 'PL')">
                <span class="tn-lang-item-name">Polski</span>
                <span class="tn-lang-item-code">PL</span>
              </button>
              <button type="button" class="tn-lang-dropdown-item" data-lang="th" onclick="window.selectLanguage('th', 'ไทย', 'TH')">
                <span class="tn-lang-item-name">ไทย</span>
                <span class="tn-lang-item-code">TH</span>
              </button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- =========================================================================
       MAIN NAVIGATION HEADER (White Sticky Header)
       ========================================================================= -->
  <header class="tn-main-header">
    <div class="tn-container header-nav-inner">
      
      <!-- Logo -->
      <a href="/" class="tn-logo">
        <img src="/logo_svg.svg" alt="TrueNorth Group Logo" class="tn-logo-img" width="160" height="40" />
      </a>

      <!-- Exact Client Headers: Home - About - Products - Custom Solutions - Contact -->
      <ul class="tn-nav-list">
        <li><a href="/" class="tn-nav-link <?= ($activePage ?? 'home') === 'home' ? 'active' : '' ?>">Home</a></li>
        <li><a href="/about" class="tn-nav-link <?= ($activePage ?? '') === 'about' ? 'active' : '' ?>">About</a></li>
        <li><a href="/products" class="tn-nav-link <?= ($activePage ?? '') === 'products' ? 'active' : '' ?>">Products</a></li>
        <li><a href="/custom" class="tn-nav-link <?= ($activePage ?? '') === 'custom' ? 'active' : '' ?>">Custom Solutions</a></li>
        <li><a href="/sustainability" class="tn-nav-link <?= ($activePage ?? '') === 'sustainability' ? 'active' : '' ?>">Sustainability</a></li>
        <li><a href="/blog" class="tn-nav-link <?= ($activePage ?? '') === 'blog' ? 'active' : '' ?>">Blog</a></li>
        <li><a href="/contact" class="tn-nav-link <?= ($activePage ?? '') === 'contact' ? 'active' : '' ?>">Contact</a></li>
      </ul>

      <!-- Header Actions (Inquiry + Mobile Hamburger) -->
      <div class="header-actions-wrap">
        <button class="tn-cart-inquiry-btn" onclick="window.openInquiry()" aria-label="Open Inquiry">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          <span>INQUIRY</span>
        </button>

        <!-- Mobile Hamburger Button -->
        <button class="tn-mobile-menu-toggle" id="tn-mobile-menu-btn" onclick="window.toggleMobileMenu()" aria-label="Toggle Navigation Menu" aria-expanded="false">
          <span class="hamburger-bar"></span>
          <span class="hamburger-bar"></span>
          <span class="hamburger-bar"></span>
        </button>
      </div>

    </div>
  </header>

  <!-- =========================================================================
       MOBILE NAVIGATION DRAWER & BACKDROP
       ========================================================================= -->
  <div class="tn-mobile-nav-backdrop" id="mobile-nav-backdrop" onclick="window.closeMobileMenu()"></div>
  <aside class="tn-mobile-nav-drawer" id="mobile-nav-drawer" aria-label="Mobile Navigation">
    <div class="mobile-nav-header">
      <a href="/" class="mobile-nav-logo" onclick="window.closeMobileMenu();">
        <img src="/logo_svg.svg" alt="TrueNorth Group Logo" width="140" height="35" />
      </a>
      <button class="mobile-nav-close-btn" onclick="window.closeMobileMenu()" aria-label="Close navigation menu">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
      </button>
    </div>

    <!-- Mobile Search Box -->
    <div class="mobile-nav-search">
      <form action="/products" method="GET" class="mobile-search-input-wrap" id="mobile-search-form" onsubmit="if(!this.q.value.trim()) return false;">
        <input type="text" name="q" aria-label="Search products and articles" id="mobile-search-input" placeholder="Search packaging..." autocomplete="off" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" />
        <button type="submit" aria-label="Search">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        </button>
      </form>
      <div class="search-results-popover" id="mobile-search-popover"></div>
    </div>

    <!-- Mobile Navigation Links -->
    <nav class="mobile-nav-links">
      <a href="/" class="mobile-nav-item <?= ($activePage ?? 'home') === 'home' ? 'active' : '' ?>" onclick="window.closeMobileMenu();">
        <span>Home</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      </a>
      <a href="/about" class="mobile-nav-item <?= ($activePage ?? '') === 'about' ? 'active' : '' ?>" onclick="window.closeMobileMenu();">
        <span>About</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      </a>
      <a href="/products" class="mobile-nav-item <?= ($activePage ?? '') === 'products' && empty($_GET['industry']) ? 'active' : '' ?>" onclick="window.closeMobileMenu();">
        <span>Products Catalogue</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      </a>
      <div style="padding: 6px 16px 12px; display: flex; flex-wrap: wrap; gap: 6px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
        <span style="font-size: 0.68rem; font-weight: 800; color: #64748b; text-transform: uppercase; width: 100%; margin-bottom: 2px; letter-spacing: 0.05em;">Browse by Industry:</span>
        <a href="/products?industry=cosmetics" onclick="window.closeMobileMenu();" style="font-size: 0.76rem; color: #1e293b; background: #fff; border: 1px solid #cbd5e1; padding: 4px 8px; border-radius: 4px; text-decoration: none; font-weight: 600;">Cosmetics</a>
        <a href="/products?industry=pharma" onclick="window.closeMobileMenu();" style="font-size: 0.76rem; color: #1e293b; background: #fff; border: 1px solid #cbd5e1; padding: 4px 8px; border-radius: 4px; text-decoration: none; font-weight: 600;">Pharma</a>
        <a href="/products?industry=agro" onclick="window.closeMobileMenu();" style="font-size: 0.76rem; color: #1e293b; background: #fff; border: 1px solid #cbd5e1; padding: 4px 8px; border-radius: 4px; text-decoration: none; font-weight: 600;">Agro</a>
        <a href="/products?industry=nutraceuticals" onclick="window.closeMobileMenu();" style="font-size: 0.76rem; color: #1e293b; background: #fff; border: 1px solid #cbd5e1; padding: 4px 8px; border-radius: 4px; text-decoration: none; font-weight: 600;">Nutraceuticals</a>
        <a href="/products?industry=liquor" onclick="window.closeMobileMenu();" style="font-size: 0.76rem; color: #1e293b; background: #fff; border: 1px solid #cbd5e1; padding: 4px 8px; border-radius: 4px; text-decoration: none; font-weight: 600;">Liquor</a>
      </div>
      <a href="/custom" class="mobile-nav-item <?= ($activePage ?? '') === 'custom' ? 'active' : '' ?>" onclick="window.closeMobileMenu();">
        <span>Custom Solutions</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      </a>
      <a href="/sustainability" class="mobile-nav-item <?= ($activePage ?? '') === 'sustainability' ? 'active' : '' ?>" onclick="window.closeMobileMenu();">
        <span>Sustainability</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      </a>
      <a href="/blog" class="mobile-nav-item <?= ($activePage ?? '') === 'blog' ? 'active' : '' ?>" onclick="window.closeMobileMenu();">
        <span>Blog &amp; Insights</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      </a>
      <a href="/faq" class="mobile-nav-item <?= ($activePage ?? '') === 'faq' ? 'active' : '' ?>" onclick="window.closeMobileMenu();">
        <span>FAQ</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      </a>
      <a href="/contact" class="mobile-nav-item <?= ($activePage ?? '') === 'contact' ? 'active' : '' ?>" onclick="window.closeMobileMenu();">
        <span>Contact & Locations</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      </a>
    </nav>

    <!-- Mobile Global Compliance Strip & Direct Contact -->
    <div class="mobile-nav-lang-section" style="padding: 12px 16px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
      <div style="margin-bottom: 10px; position: relative;">
        <button type="button" id="mobile-lang-btn" onclick="window.toggleMobileLangDropdown(event)" style="width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; cursor: pointer; font-family: inherit; font-size: 0.82rem; font-weight: 700; color: #16273f;">
          <span style="display: flex; align-items: center; gap: 8px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--color-gold)" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            <span id="mobile-lang-name">Language: English (EN)</span>
          </span>
          <svg class="mobile-lang-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.5" style="transition: transform 0.2s ease;"><polyline points="6 9 12 15 18 9"/></svg>
        </button>

        <div class="mobile-lang-dropdown-menu" id="mobile-lang-dropdown-menu" style="display: none; margin-top: 6px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; max-height: 220px; overflow-y: auto; box-shadow: 0 4px 14px rgba(0,0,0,0.08); padding: 4px 0;">
          <div class="tn-lang-dropdown-list">
            <button type="button" class="tn-lang-dropdown-item active" data-lang="en" onclick="window.selectLanguage('en', 'English', 'EN')">
              <span class="tn-lang-item-name">English</span>
              <span class="tn-lang-item-code">EN</span>
            </button>
            <button type="button" class="tn-lang-dropdown-item" data-lang="es" onclick="window.selectLanguage('es', 'Español', 'ES')">
              <span class="tn-lang-item-name">Español</span>
              <span class="tn-lang-item-code">ES</span>
            </button>
            <button type="button" class="tn-lang-dropdown-item" data-lang="fr" onclick="window.selectLanguage('fr', 'Français', 'FR')">
              <span class="tn-lang-item-name">Français</span>
              <span class="tn-lang-item-code">FR</span>
            </button>
            <button type="button" class="tn-lang-dropdown-item" data-lang="de" onclick="window.selectLanguage('de', 'Deutsch', 'DE')">
              <span class="tn-lang-item-name">Deutsch</span>
              <span class="tn-lang-item-code">DE</span>
            </button>
            <button type="button" class="tn-lang-dropdown-item" data-lang="ar" onclick="window.selectLanguage('ar', 'العربية', 'AR')">
              <span class="tn-lang-item-name">العربية</span>
              <span class="tn-lang-item-code">AR</span>
            </button>
            <button type="button" class="tn-lang-dropdown-item" data-lang="ru" onclick="window.selectLanguage('ru', 'Русский', 'RU')">
              <span class="tn-lang-item-name">Русский</span>
              <span class="tn-lang-item-code">RU</span>
            </button>
            <button type="button" class="tn-lang-dropdown-item" data-lang="zh-CN" onclick="window.selectLanguage('zh-CN', '简体中文', 'ZH')">
              <span class="tn-lang-item-name">简体中文</span>
              <span class="tn-lang-item-code">ZH</span>
            </button>
            <button type="button" class="tn-lang-dropdown-item" data-lang="ja" onclick="window.selectLanguage('ja', '日本語', 'JA')">
              <span class="tn-lang-item-name">日本語</span>
              <span class="tn-lang-item-code">JA</span>
            </button>
            <button type="button" class="tn-lang-dropdown-item" data-lang="pt" onclick="window.selectLanguage('pt', 'Português', 'PT')">
              <span class="tn-lang-item-name">Português</span>
              <span class="tn-lang-item-code">PT</span>
            </button>
            <button type="button" class="tn-lang-dropdown-item" data-lang="it" onclick="window.selectLanguage('it', 'Italiano', 'IT')">
              <span class="tn-lang-item-name">Italiano</span>
              <span class="tn-lang-item-code">IT</span>
            </button>
            <button type="button" class="tn-lang-dropdown-item" data-lang="hi" onclick="window.selectLanguage('hi', 'हिन्दी', 'HI')">
              <span class="tn-lang-item-name">हिन्दी</span>
              <span class="tn-lang-item-code">HI</span>
            </button>
            <button type="button" class="tn-lang-dropdown-item" data-lang="nl" onclick="window.selectLanguage('nl', 'Nederlands', 'NL')">
              <span class="tn-lang-item-name">Nederlands</span>
              <span class="tn-lang-item-code">NL</span>
            </button>
            <button type="button" class="tn-lang-dropdown-item" data-lang="tr" onclick="window.selectLanguage('tr', 'Türkçe', 'TR')">
              <span class="tn-lang-item-name">Türkçe</span>
              <span class="tn-lang-item-code">TR</span>
            </button>
            <button type="button" class="tn-lang-dropdown-item" data-lang="ko" onclick="window.selectLanguage('ko', '한국어', 'KO')">
              <span class="tn-lang-item-name">한국어</span>
              <span class="tn-lang-item-code">KO</span>
            </button>
            <button type="button" class="tn-lang-dropdown-item" data-lang="vi" onclick="window.selectLanguage('vi', 'Tiếng Việt', 'VI')">
              <span class="tn-lang-item-name">Tiếng Việt</span>
              <span class="tn-lang-item-code">VI</span>
            </button>
            <button type="button" class="tn-lang-dropdown-item" data-lang="id" onclick="window.selectLanguage('id', 'Bahasa Indo', 'ID')">
              <span class="tn-lang-item-name">Bahasa Indo</span>
              <span class="tn-lang-item-code">ID</span>
            </button>
            <button type="button" class="tn-lang-dropdown-item" data-lang="pl" onclick="window.selectLanguage('pl', 'Polski', 'PL')">
              <span class="tn-lang-item-name">Polski</span>
              <span class="tn-lang-item-code">PL</span>
            </button>
            <button type="button" class="tn-lang-dropdown-item" data-lang="th" onclick="window.selectLanguage('th', 'ไทย', 'TH')">
              <span class="tn-lang-item-name">ไทย</span>
              <span class="tn-lang-item-code">TH</span>
            </button>
          </div>
        </div>
      </div>
      <div style="font-size: 0.75rem; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--color-gold)" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <span>TÜV NORD ISO 9001 • Class 10,000 Cleanroom</span>
      </div>
      <div style="font-size: 0.8rem; font-weight: 600; color: #1e293b; display: flex; flex-direction: column; gap: 6px;">
        <a href="tel:<?= htmlspecialchars($company['phone_tel'] ?? '+919810220761') ?>" style="color: var(--color-gold-text, #8A6516); text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          <span><?= htmlspecialchars($company['phone'] ?? '+91 9810220761') ?></span>
        </a>
        <a href="mailto:<?= htmlspecialchars($company['email'] ?? 'info@wetruenorthgroup.com') ?>" style="color: #475569; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
          <span><?= htmlspecialchars($company['email'] ?? 'info@wetruenorthgroup.com') ?></span>
        </a>
      </div>
    </div>

    <!-- Drawer Footer Actions -->
    <div class="mobile-nav-footer">
      <button class="tn-btn-gold mobile-nav-inquiry-btn" onclick="window.closeMobileMenu(); window.openInquiry();">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        SEND AN INQUIRY
      </button>
      <div class="mobile-nav-cert-badge">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <span>TÜV NORD ISO 9001:2015 • Class 10,000 Cleanroom</span>
      </div>
    </div>
  </aside>

  <!-- Mobile drawer functions (toggleMobileMenu / closeMobileMenu) live in /js/site-main.js -->

