<?php
// ==============================================================================
// TRUENORTH GROUP — ENTERPRISE DYNAMIC SEO ENGINE
// Dynamically generates canonicals, Open Graph, Twitter Cards, & Schema.org JSON-LD
// ==============================================================================

$seoBaseUrl = 'https://wetruenorthgroup.com';
$reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$canonicalUrl = $seoBaseUrl . $reqPath;

// Base Defaults (Homepage)
$seoTitle = 'TrueNorth Group — Primary Packaging Specialist | PET Bottles & Jars';
$seoDesc = 'TrueNorth Group is an ISO 9001 certified hybrid primary packaging specialist manufacturing and distributing high-grade PET bottles, HDPE containers, cosmetic jars, pumps, and custom closures.';
$seoKeywords = 'PET bottles, cosmetic packaging, HDPE bottles, plastic jars, lotion pumps, mist sprays, wholesale packaging manufacturer, cleanroom molding';
$ogType = 'website';
$ogImage = $seoBaseUrl . '/logo.webp';
$jsonLdList = [];

// Determine page specifics
if (!empty($sku)) {
    // 1. Single Product Detail Page
    $pName = $sku['name'] ?? ($sku['fullTitle'] ?? 'Packaging Container');
    $pCap = $sku['specs']['capacity'] ?? ($sku['capacity'] ?? ($sku['volume'] ?? ''));
    $pMat = $sku['specs']['material'] ?? ($sku['material'] ?? 'PET');
    $pNeck = $sku['specs']['neck'] ?? ($sku['neck'] ?? '');
    
    $seoTitle = ($sku['seoTitle'] ?? '') ?: "{$pName} | TrueNorth Packaging";
    $seoDesc = ($sku['seoDescription'] ?? '') ?: ($sku['shortDescription'] ?? ($sku['description'] ?? "Wholesale high-precision {$pName} engineered in certified cleanrooms. Material: {$pMat}, Volume: {$pCap}, Neck Finish: {$pNeck}. Request an RFQ quote."));
    $seoKeywords = "{$pName}, {$pMat} bottle, {$pCap} container, {$pNeck} neck, wholesale packaging, bulk quote, ISO certified packaging";
    $canonicalUrl = $seoBaseUrl . '/product?id=' . urlencode($sku['id']);
    $ogType = 'product';
    if (!empty($sku['image'])) {
        $ogImage = (strpos($sku['image'], 'http') === 0) ? $sku['image'] : ($seoBaseUrl . '/' . ltrim($sku['image'], '/'));
    }

    // Product Schema (JSON-LD)
    $jsonLdList[] = [
        '@context' => 'https://schema.org/',
        '@type' => 'Product',
        'name' => $pName,
        'image' => [$ogImage],
        'description' => strip_tags($seoDesc),
        'sku' => $sku['id'],
        'mpn' => $sku['articleNo'] ?? $sku['id'],
        'brand' => [
            '@type' => 'Brand',
            'name' => 'TrueNorth Group'
        ],
        'manufacturer' => [
            '@type' => 'Organization',
            'name' => 'TrueNorth Group',
            'url' => $seoBaseUrl
        ],
        'offers' => [
            '@type' => 'Offer',
            'url' => $canonicalUrl,
            'priceCurrency' => 'INR',
            'price' => !empty($sku['regularPrice']) ? (float)$sku['regularPrice'] : '0.00',
            'priceValidUntil' => date('Y-12-31', strtotime('+1 year')),
            'availability' => 'https://schema.org/InStock',
            'itemCondition' => 'https://schema.org/NewCondition'
        ]
    ];

    // BreadcrumbList Schema
    $jsonLdList[] = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $seoBaseUrl . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Products', 'item' => $seoBaseUrl . '/products'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $pName, 'item' => $canonicalUrl]
        ]
    ];

} elseif (($activePage ?? '') === 'products') {
    // 2. Catalog / Products Page
    $catParam = $_GET['category'] ?? '';
    $qParam = $_GET['q'] ?? '';
    if (!empty($catParam)) {
        $catTitle = ucwords(str_replace(['-', '_'], ' ', $catParam));
        $seoTitle = "{$catTitle} Catalog & Wholesale Containers | TrueNorth Group";
        $seoDesc = "Commercial wholesale range of {$catTitle}. High-tolerance precision blow molding, cleanroom production, and worldwide shipping. Request bulk samples & pricing.";
        $canonicalUrl = $seoBaseUrl . '/products?category=' . urlencode($catParam);
    } elseif (!empty($qParam)) {
        $seoTitle = "Search: " . htmlspecialchars($qParam) . " | Packaging Catalog — TrueNorth";
        $seoDesc = "Packaging search results for " . htmlspecialchars($qParam) . " across PET bottles, jars, pumps, and closures.";
        $canonicalUrl = $seoBaseUrl . '/products?q=' . urlencode($qParam);
    } else {
        $seoTitle = 'Packaging Catalog & Containers Directory | TrueNorth Group';
        $seoDesc = 'Browse 400+ PET bottles, HDPE containers, cosmetic jars, foamers, lotion pumps, mist sprayers, and tamper-evident closures. ISO certified factory direct.';
        $canonicalUrl = $seoBaseUrl . '/products';
    }
} elseif (($activePage ?? '') === 'about') {
    // 3. About Page
    $seoTitle = 'About TrueNorth Group — Hybrid Primary Packaging Specialist & Manufacturer';
    $seoDesc = 'Learn about TrueNorth Group, our 25+ years heritage, Class 10,000 cleanroom manufacturing hubs, TÜV NORD ISO 9001 certification, and global supply network.';
    $canonicalUrl = $seoBaseUrl . '/about';
} elseif (($activePage ?? '') === 'contact') {
    // 4. Contact & RFQ Page
    $seoTitle = 'Request a Wholesale Quote & Contact Engineering | TrueNorth Group';
    $seoDesc = 'Get custom volume pricing, technical CAD drawings, and complimentary sample kits within 24 hours. Contact TrueNorth Group primary packaging specialists.';
    $canonicalUrl = $seoBaseUrl . '/contact';
} elseif (($activePage ?? '') === 'custom') {
    // 5. Custom Solutions
    $seoTitle = 'Custom Mold Design & Tailored Packaging Engineering | TrueNorth Group';
    $seoDesc = 'Bespoke container tooling, 3D rapid prototyping, custom neck finishes, and private labeling solutions engineered to your exact product specifications.';
    $canonicalUrl = $seoBaseUrl . '/custom';
} elseif (($activePage ?? '') === 'sustainability') {
    // 6. Sustainability
    $seoTitle = 'Sustainable Packaging Solutions — PCR-PET & Ocean Bound Plastics | TrueNorth';
    $seoDesc = 'Eco-conscious packaging innovation. 100% Post-Consumer Recycled (PCR) PET bottles, lightweighting engineering, and closed-loop circular packaging solutions.';
    $canonicalUrl = $seoBaseUrl . '/sustainability';
}

// Global Organization Schema
$jsonLdList[] = [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => 'TrueNorth Group',
    'legalName' => 'TrueNorth Packaging Group',
    'url' => $seoBaseUrl,
    'logo' => $seoBaseUrl . '/logo.webp',
    'image' => $seoBaseUrl . '/logo.webp',
    'description' => 'Hybrid primary packaging specialist manufacturing and distributing PET bottles, jars, caps, and pumps worldwide.',
    'email' => 'info@wetruenorthgroup.com',
    'telephone' => '+91 98765 43210',
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => 'Global Manufacturing & Cleanroom Production Hubs',
        'addressLocality' => 'Mumbai',
        'addressRegion' => 'MH',
        'addressCountry' => 'IN'
    ],
    'contactPoint' => [
        '@type' => 'ContactPoint',
        'telephone' => '+91 98765 43210',
        'contactType' => 'Sales & Technical Inquiries',
        'email' => 'info@wetruenorthgroup.com',
        'areaServed' => 'Global',
        'availableLanguage' => ['English', 'Hindi']
    ]
];

// Global WebSite Schema with Sitelinks Searchbox
$jsonLdList[] = [
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => 'TrueNorth Group',
    'url' => $seoBaseUrl,
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => [
            '@type' => 'EntryPoint',
            'urlTemplate' => $seoBaseUrl . '/products?q={search_term_string}'
        ],
        'query-input' => 'required name=search_term_string'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  
  <!-- Primary Meta Tags -->
  <title><?= htmlspecialchars($seoTitle) ?></title>
  <meta name="title" content="<?= htmlspecialchars($seoTitle) ?>" />
  <meta name="description" content="<?= htmlspecialchars($seoDesc) ?>" />
  <meta name="keywords" content="<?= htmlspecialchars($seoKeywords) ?>" />
  <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>" />
  <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />

  <!-- Open Graph / Facebook / LinkedIn -->
  <meta property="og:type" content="<?= htmlspecialchars($ogType) ?>" />
  <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>" />
  <meta property="og:site_name" content="TrueNorth Group" />
  <meta property="og:title" content="<?= htmlspecialchars($seoTitle) ?>" />
  <meta property="og:description" content="<?= htmlspecialchars($seoDesc) ?>" />
  <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>" />
  <meta property="og:image:alt" content="<?= htmlspecialchars($seoTitle) ?>" />
  <meta property="og:locale" content="en_US" />

  <!-- Twitter / X -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:url" content="<?= htmlspecialchars($canonicalUrl) ?>" />
  <meta name="twitter:title" content="<?= htmlspecialchars($seoTitle) ?>" />
  <meta name="twitter:description" content="<?= htmlspecialchars($seoDesc) ?>" />
  <meta name="twitter:image" content="<?= htmlspecialchars($ogImage) ?>" />

  <!-- Mobile & PWA Configuration -->
  <link rel="manifest" href="/site.webmanifest" />
  <meta name="theme-color" content="#16273f" />
  <meta name="msapplication-config" content="/browserconfig.xml" />
  <meta name="msapplication-TileColor" content="#16273f" />
  <link rel="author" href="/humans.txt" />

  <!-- Favicons -->
  <link rel="icon" type="image/svg+xml" href="/logo_svg.svg" />
  <link rel="shortcut icon" href="/favicon.svg" />
  <link rel="apple-touch-icon" href="/logo.webp" />
  <link rel="stylesheet" href="/styles.css?v=1.0.8" />

  <!-- Structured Data (JSON-LD) -->
  <?php foreach ($jsonLdList as $schemaItem): ?>
  <script type="application/ld+json">
  <?= json_encode($schemaItem, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
  </script>
  <?php endforeach; ?>
</head>
<body>

  <!-- =========================================================================
       TOP UTILITY BAR (Exact Frapak Top Strip)
       ========================================================================= -->
  <div class="frapak-top-utility-bar">
    <div class="frapak-container top-bar-inner">
      
      <!-- Search Input with Live Autocomplete -->
      <form action="/products" method="GET" class="frapak-search-box" id="top-search-form" onsubmit="if(!this.q.value.trim()) return false;">
        <input type="text" name="q" id="top-search-input" placeholder="Search bottles, jars, pumps, caps..." autocomplete="off" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" />
        <button type="submit" aria-label="Search">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        </button>
        <div class="search-results-popover" id="top-search-popover"></div>
      </form>

      <!-- Certifications & Language Selector -->
      <div class="top-bar-right">
        <span>Audited Cleanroom Manufacturing • Food-Grade ISO Certified</span>
        <div class="lang-selector">
          <span style="display: inline-flex; align-items: center; gap: 6px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            EN
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
          </span>
        </div>
      </div>

    </div>
  </div>

  <!-- =========================================================================
       MAIN NAVIGATION HEADER (White Sticky Header)
       ========================================================================= -->
  <header class="frapak-main-header">
    <div class="frapak-container header-nav-inner">
      
      <!-- Logo -->
      <a href="/" class="frapak-logo">
        <img src="/logo_svg.svg" alt="TrueNorth Group Logo" class="frapak-logo-img" />
      </a>

      <!-- Exact Client Headers: Home - About - Products - Custom Solutions - Contact -->
      <ul class="frapak-nav-list">
        <li><a href="/" class="frapak-nav-link <?= ($activePage ?? 'home') === 'home' ? 'active' : '' ?>">Home</a></li>
        <li><a href="/about" class="frapak-nav-link <?= ($activePage ?? '') === 'about' ? 'active' : '' ?>">About</a></li>
        <li><a href="/products" class="frapak-nav-link <?= ($activePage ?? '') === 'products' ? 'active' : '' ?>">Products</a></li>
        <li><a href="/custom" class="frapak-nav-link <?= ($activePage ?? '') === 'custom' ? 'active' : '' ?>">Custom Solutions</a></li>
        <li><a href="/sustainability" class="frapak-nav-link <?= ($activePage ?? '') === 'sustainability' ? 'active' : '' ?>">Sustainability</a></li>
        <li><a href="/contact" class="frapak-nav-link <?= ($activePage ?? '') === 'contact' ? 'active' : '' ?>">Contact</a></li>
      </ul>

      <!-- Header Actions (Inquiry + Mobile Hamburger) -->
      <div class="header-actions-wrap">
        <button class="frapak-cart-inquiry-btn" onclick="window.openInquiry()" aria-label="Open Inquiry">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          <span>INQUIRY</span>
        </button>

        <!-- Mobile Hamburger Button -->
        <button class="frapak-mobile-menu-toggle" id="frapak-mobile-menu-btn" onclick="window.toggleMobileMenu()" aria-label="Toggle Navigation Menu" aria-expanded="false">
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
  <div class="frapak-mobile-nav-backdrop" id="mobile-nav-backdrop" onclick="window.closeMobileMenu()"></div>
  <aside class="frapak-mobile-nav-drawer" id="mobile-nav-drawer" aria-label="Mobile Navigation">
    <div class="mobile-nav-header">
      <a href="/" class="mobile-nav-logo" onclick="window.closeMobileMenu();">
        <img src="/logo_svg.svg" alt="TrueNorth Group Logo" />
      </a>
      <button class="mobile-nav-close-btn" onclick="window.closeMobileMenu()" aria-label="Close navigation menu">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
      </button>
    </div>

    <!-- Mobile Search Box -->
    <div class="mobile-nav-search">
      <form action="/products" method="GET" class="mobile-search-input-wrap" id="mobile-search-form" onsubmit="if(!this.q.value.trim()) return false;">
        <input type="text" name="q" id="mobile-search-input" placeholder="Search packaging..." autocomplete="off" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" />
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
      <a href="/products" class="mobile-nav-item <?= ($activePage ?? '') === 'products' ? 'active' : '' ?>" onclick="window.closeMobileMenu();">
        <span>Products Catalogue</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      </a>
      <a href="/custom" class="mobile-nav-item <?= ($activePage ?? '') === 'custom' ? 'active' : '' ?>" onclick="window.closeMobileMenu();">
        <span>Custom Solutions</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      </a>
      <a href="/sustainability" class="mobile-nav-item <?= ($activePage ?? '') === 'sustainability' ? 'active' : '' ?>" onclick="window.closeMobileMenu();">
        <span>Sustainability</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      </a>
      <a href="/contact" class="mobile-nav-item <?= ($activePage ?? '') === 'contact' ? 'active' : '' ?>" onclick="window.closeMobileMenu();">
        <span>Contact & Locations</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      </a>
    </nav>

    <!-- Drawer Footer Actions -->
    <div class="mobile-nav-footer">
      <button class="frapak-btn-gold mobile-nav-inquiry-btn" onclick="window.closeMobileMenu(); window.openInquiry();">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        SEND AN INQUIRY
      </button>
      <div class="mobile-nav-cert-badge">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <span>ISO 15593 Food-Grade • Class 10,000 Cleanroom</span>
      </div>
    </div>
  </aside>

  <script>
    window.toggleMobileMenu = function() {
      var d = document.getElementById('mobile-nav-drawer');
      var b = document.getElementById('mobile-nav-backdrop');
      var btn = document.getElementById('frapak-mobile-menu-btn');
      if (d && b) {
        var isOpen = d.classList.contains('open') || d.classList.contains('active');
        if (isOpen) {
          window.closeMobileMenu();
        } else {
          d.classList.add('open', 'active');
          b.classList.add('open', 'active');
          if (btn) {
            btn.classList.add('active');
            btn.setAttribute('aria-expanded', 'true');
          }
          document.body.style.overflow = 'hidden';
        }
      }
    };

    window.closeMobileMenu = function() {
      var d = document.getElementById('mobile-nav-drawer');
      var b = document.getElementById('mobile-nav-backdrop');
      var btn = document.getElementById('frapak-mobile-menu-btn');
      if (d) d.classList.remove('open', 'active');
      if (b) b.classList.remove('open', 'active');
      if (btn) {
        btn.classList.remove('active');
        btn.setAttribute('aria-expanded', 'false');
      }
      document.body.style.overflow = '';
    };
  </script>

