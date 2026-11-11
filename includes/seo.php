<?php
// ==============================================================================
// TRUENORTH GROUP — FRONTEND SEO META ENGINE
// Generates fully compliant, optimized HTML <head> meta tags, canonicals,
// Open Graph, Twitter Cards, verification codes, and tracking scripts.
// Follows strict priority: Page Override -> Meta Template -> Global Default.
// ==============================================================================

require_once dirname(__DIR__) . '/app/Models/SeoModel.php';

function seo_meta($context = []) {
    SeoModel::init();
    $settings = SeoModel::getSettings();

    $preferredDomain = $settings['preferred_domain'] ?? 'non-www';
    $defaultHost = ($preferredDomain === 'www') ? 'https://www.wetruenorthgroup.com' : 'https://wetruenorthgroup.com';
    $siteName = $settings['site_name'] ?? 'TrueNorth Group';
    $titleSuffix = $settings['title_suffix'] ?? (' | ' . $siteName);
    
    // M4: Optimized 140-160 char default meta description
    $defaultDesc = $settings['default_meta_description'] ?? 'TrueNorth Group is an ISO 9001 certified primary packaging manufacturer of precision PET bottles, jars, closures, and pumps for pharma and cosmetics.';
    
    // M2: High-resolution 1200x630 default social share image
    $defaultOgImage = (!empty($settings['default_og_image']) && $settings['default_og_image'] !== '/logo.webp') 
        ? $settings['default_og_image'] 
        : '/hero_bottles_banner.webp';
        
    $favicon = $settings['favicon'] ?? '/logo_svg.svg';
    $twitterHandle = $settings['twitter_handle'] ?? '@TrueNorthPack';

    // Parse current request
    $reqUri = $_SERVER['REQUEST_URI'] ?? '/';
    $reqPath = parse_url($reqUri, PHP_URL_PATH);
    $cleanPath = '/' . ltrim($reqPath, '/');
    if ($cleanPath === '//') $cleanPath = '/';

    $pageType = $context['page_type'] ?? 'page';
    $pageRefId = $context['page_ref_id'] ?? null;

    // H2: Dedicated 404 Detection
    $is404 = (http_response_code() === 404) 
        || ($pageType === '404') 
        || ($pageRefId === '404')
        || (!empty($context['is_404']))
        || ($cleanPath === '/404');

    // Check Priority 1: Page-Level Override (seo_pages)
    $pageOverride = $is404 ? [] : SeoModel::getPageSeo($cleanPath, $pageType, $pageRefId);

    // Context attributes
    $product = $context['product'] ?? null;
    $category = $context['category'] ?? null;
    $article = $context['article'] ?? null;

    // Static Page Default Meta (prevents duplicate homepage titles on inner pages)
    $staticAliases = [
        '/rpet' => '/sustainability',
        '/catalogue' => '/products',
        '/locations' => '/contact',
        '/custom-solutions' => '/custom',
        '/frequently-asked-questions' => '/faq',
        '/terms-conditions' => '/terms',
        '/general-terms' => '/terms',
        '/privacy-policy' => '/privacy',
        '/privacy-statement' => '/privacy'
    ];
    $lookupPath = $staticAliases[$cleanPath] ?? $cleanPath;

    $staticPageMeta = [
        '/sustainability' => [
            'title' => 'Sustainable rPET & PCR Packaging Solutions — ' . $siteName,
            'desc'  => 'Certified food-grade rPET bottles, wide-mouth PCR jars, and lightweighted packaging engineered to reduce carbon footprints without compromising barrier protection.',
            'keywords' => 'sustainable packaging, rPET bottles, PCR jars, food grade recycled plastics, lightweight blow molding'
        ],
        '/about' => [
            'title' => 'About ' . $siteName . ' — Primary Packaging Manufacturer',
            'desc'  => 'Over 25 years of engineering excellence in precision injection blow molding, ISO 9001 certified cleanroom manufacturing, and global primary packaging supply.',
            'keywords' => 'packaging manufacturer, cleanroom molding, injection blow molding, TrueNorth Group, pharma bottles'
        ],
        '/contact' => [
            'title' => 'Contact ' . $siteName . ' — Global Packaging Sales & Support',
            'desc'  => 'Connect with our packaging engineers and commercial teams in Delhi, Daman, and Ningbo. Request quotations, technical drawings, and custom tooling.',
            'keywords' => 'contact packaging manufacturer, packaging quote, TrueNorth contact, bottle manufacturer sales'
        ],
        '/custom' => [
            'title' => 'Custom Mould Development & Bespoke Packaging — ' . $siteName,
            'desc'  => 'End-to-end bespoke packaging engineering: 3D CAD modeling, rapid prototype pilot moulds, custom neck finishes, and commercial blow-mould tooling in 21 days.',
            'keywords' => 'custom bottle moulds, bespoke packaging engineering, prototype moulds, CAD bottle design'
        ],
        '/faq' => [
            'title' => 'Frequently Asked Questions & Packaging Specifications — ' . $siteName,
            'desc'  => 'Find answers to common questions regarding minimum order quantities (MOQs), food-grade certifications, resin selections, lead times, and global shipping.',
            'keywords' => 'packaging FAQ, bottle MOQ, rPET certification questions, cleanroom manufacturing FAQ'
        ],
        '/terms' => [
            'title' => 'General Terms & Conditions of Supply — ' . $siteName,
            'desc'  => 'Review the commercial terms, quality benchmarks, delivery conditions, and contractual provisions governing TrueNorth Group packaging supply agreements.',
            'keywords' => 'terms and conditions, packaging supply agreement, commercial terms'
        ],
        '/privacy' => [
            'title' => 'Privacy & Data Protection Policy — ' . $siteName,
            'desc'  => 'TrueNorth Group privacy statement outlining our data handling standards, NDA commitments for custom designs, and client information protection.',
            'keywords' => 'privacy policy, NDA packaging designs, data protection'
        ],
        '/products' => [
            'title' => 'Primary Packaging Catalogue — Bottles, Jars & Closures — ' . $siteName,
            'desc'  => 'Browse our complete B2B catalogue of precision PET bottles, HDPE containers, cosmetic jars, caps, lotion pumps, and trigger sprayers.',
            'keywords' => 'packaging catalogue, PET bottles wholesale, cosmetic jars, dispensing pumps'
        ]
    ];

    // 1. Title Resolution
    $rawTitle = '';
    if ($is404) {
        $rawTitle = 'Page Not Found — ' . $siteName;
        $seoTitle = $rawTitle;
    } elseif (!empty($pageOverride['meta_title'])) {
        $rawTitle = $pageOverride['meta_title'];
    } elseif ($pageType === 'product' && !empty($product)) {
        $tpl = SeoModel::applyTemplate('product', [
            'product_name' => $product['name'] ?? ($product['fullTitle'] ?? 'Packaging Container'),
            'material' => $product['specs']['material'] ?? ($product['material'] ?? 'PET'),
            'neck_finish' => $product['specs']['neck'] ?? ($product['neck'] ?? 'Standard'),
            'price' => $product['regularPrice'] ?? '0'
        ]);
        $rawTitle = $tpl['title'] ?? ($product['name'] . $titleSuffix);
    } elseif ($pageType === 'category' && !empty($category)) {
        $tpl = SeoModel::applyTemplate('category', [
            'category_name' => $category['name'] ?? ucwords(str_replace('-', ' ', (string)$pageRefId))
        ]);
        $rawTitle = $tpl['title'] ?? (($category['name'] ?? 'Packaging') . $titleSuffix);
    } elseif ($pageType === 'blog' && !empty($article)) {
        $tpl = SeoModel::applyTemplate('blog', [
            'article_title' => $article['title'] ?? 'Packaging Insights',
            'article_excerpt' => $article['excerpt'] ?? ''
        ]);
        $rawTitle = $tpl['title'] ?? ($article['title'] . $titleSuffix);
    } elseif (!empty($context['title'])) {
        $rawTitle = $context['title'];
    } elseif (isset($staticPageMeta[$lookupPath]['title'])) {
        $rawTitle = $staticPageMeta[$lookupPath]['title'];
    } else {
        // M4: Optimized 50-60 char homepage title
        $rawTitle = $siteName . ' — PET Bottles, Jars & Packaging Manufacturer';
    }

    if (!$is404) {
        // Append suffix if not present
        if (!empty($titleSuffix) && strpos($rawTitle, trim($titleSuffix)) === false && strpos($rawTitle, $siteName) === false) {
            $seoTitle = $rawTitle . $titleSuffix;
        } else {
            $seoTitle = $rawTitle;
        }
        // Limit to recommended Google SERP length
        if (mb_strlen($seoTitle) > 70) {
            $seoTitle = mb_substr($seoTitle, 0, 68) . '...';
        }
    }

    // 2. Meta Description Resolution
    $seoDesc = '';
    if ($is404) {
        $seoDesc = 'The requested packaging page could not be found. Explore our catalogue of certified PET bottles, cosmetic jars, caps, and dispensing closures.';
    } elseif (!empty($pageOverride['meta_description'])) {
        $seoDesc = $pageOverride['meta_description'];
    } elseif ($pageType === 'product' && !empty($product)) {
        $tpl = SeoModel::applyTemplate('product', [
            'product_name' => $product['name'] ?? 'Container',
            'material' => $product['specs']['material'] ?? ($product['material'] ?? 'PET'),
            'neck_finish' => $product['specs']['neck'] ?? ($product['neck'] ?? 'Standard'),
            'price' => $product['regularPrice'] ?? '0'
        ]);
        $seoDesc = $tpl['description'] ?? ($product['shortDescription'] ?? ($product['description'] ?? $defaultDesc));
    } elseif ($pageType === 'category' && !empty($category)) {
        $tpl = SeoModel::applyTemplate('category', [
            'category_name' => $category['name'] ?? 'Packaging'
        ]);
        $seoDesc = $tpl['description'] ?? ($category['desc'] ?? $defaultDesc);
    } elseif ($pageType === 'blog' && !empty($article)) {
        $seoDesc = !empty($article['meta_description']) ? $article['meta_description'] : ($article['excerpt'] ?? substr(strip_tags($article['content'] ?? ''), 0, 155));
    } elseif (!empty($context['description'])) {
        $seoDesc = $context['description'];
    } elseif (isset($staticPageMeta[$lookupPath]['desc'])) {
        $seoDesc = $staticPageMeta[$lookupPath]['desc'];
    } else {
        $seoDesc = $defaultDesc;
    }

    $seoDesc = strip_tags($seoDesc);
    $seoDesc = preg_replace('/\s+/', ' ', trim($seoDesc));
    if (mb_strlen($seoDesc) > 165) {
        $seoDesc = mb_substr($seoDesc, 0, 162) . '...';
    }

    // 3. Keywords
    $seoKeywords = $is404 ? '' : ($pageOverride['meta_keywords'] ?? ($context['keywords'] ?? ($staticPageMeta[$lookupPath]['keywords'] ?? 'PET bottles, cosmetic packaging, HDPE bottles, plastic jars, lotion pumps, mist sprays, wholesale packaging manufacturer, cleanroom molding')));

    // 4. Canonical URL (Strip tracking & filter query parameters)
    $cleanCanonical = $defaultHost . $cleanPath;
    if ($is404) {
        $cleanCanonical = $defaultHost . '/404';
    } elseif (!empty($pageOverride['canonical_url'])) {
        $cleanCanonical = $pageOverride['canonical_url'];
    } elseif (!empty($context['canonical'])) {
        $cleanCanonical = $context['canonical'];
    } elseif ($pageType === 'product' && !empty($product['id'])) {
        $cleanCanonical = $defaultHost . '/product?id=' . urlencode($product['id']);
    } elseif ($pageType === 'category' && !empty($category['slug'])) {
        $cleanCanonical = $defaultHost . '/products?category=' . urlencode($category['slug']);
    }

    // 5. Robots Meta Directives
    $isNoIndex = false;
    if ($is404) {
        $isNoIndex = true;
    } elseif (isset($pageOverride['robots_index']) && (int)$pageOverride['robots_index'] === 0) {
        $isNoIndex = true;
    } elseif (!empty($context['noindex'])) {
        $isNoIndex = true;
    } elseif (in_array($cleanPath, ['/admin', '/admin/login', '/login', '/404', '/quote.php']) || strpos($cleanPath, '/admin') === 0) {
        $isNoIndex = true;
    } elseif (isset($_GET['q']) || isset($_GET['filter']) || isset($_GET['sort'])) {
        $isNoIndex = true; // Auto noindex on faceted search query URLs
    }

    $robotsFollow = (!isset($pageOverride['robots_follow']) || (int)$pageOverride['robots_follow'] === 1) ? 'follow' : 'nofollow';
    $robotsMeta = $isNoIndex 
        ? "noindex, {$robotsFollow}" 
        : "index, {$robotsFollow}, max-image-preview:large, max-snippet:-1, max-video-preview:-1";

    // 6. Open Graph & Twitter Cards
    $ogType = ($pageType === 'product') ? 'product' : (($pageType === 'blog' && !empty($article)) ? 'article' : 'website');
    $ogTitle = $is404 ? $seoTitle : ($pageOverride['og_title'] ?? ($context['og_title'] ?? $seoTitle));
    $ogDesc = $is404 ? $seoDesc : ($pageOverride['og_description'] ?? ($context['og_description'] ?? $seoDesc));
    $ogImgRaw = $pageOverride['og_image'] ?? ($context['og_image'] ?? $defaultOgImage);
    $ogImage = (strpos($ogImgRaw, 'http://') === 0 || strpos($ogImgRaw, 'https://') === 0) 
        ? $ogImgRaw 
        : ($defaultHost . '/' . ltrim($ogImgRaw, '/'));

    // 7. Third-Party Tracking & Verification
    $googleVerification = $settings['google_verification'] ?? '';
    $bingVerification = $settings['bing_verification'] ?? '';
    $ga4Id = $settings['ga4_id'] ?? '';
    $gtmId = $settings['gtm_id'] ?? '';
    $fbPixel = $settings['facebook_pixel_id'] ?? '';
    $headerScripts = $settings['header_scripts'] ?? '';
    $footerScripts = $settings['footer_scripts'] ?? '';

    // Render output
    $html = "\n  <!-- Primary Meta Tags -->\n";
    $html .= "  <title>" . htmlspecialchars($seoTitle, ENT_QUOTES, 'UTF-8') . "</title>\n";
    $html .= "  <meta name=\"title\" content=\"" . htmlspecialchars($seoTitle, ENT_QUOTES, 'UTF-8') . "\" />\n";
    $html .= "  <meta name=\"description\" content=\"" . htmlspecialchars($seoDesc, ENT_QUOTES, 'UTF-8') . "\" />\n";
    if (!empty($seoKeywords)) {
        $html .= "  <meta name=\"keywords\" content=\"" . htmlspecialchars($seoKeywords, ENT_QUOTES, 'UTF-8') . "\" />\n";
    }
    $html .= "  <link rel=\"canonical\" href=\"" . htmlspecialchars($cleanCanonical, ENT_QUOTES, 'UTF-8') . "\" />\n";
    $html .= "  <meta name=\"robots\" content=\"" . htmlspecialchars($robotsMeta, ENT_QUOTES, 'UTF-8') . "\" />\n";

    // Open Graph
    $html .= "\n  <!-- Open Graph / Facebook / LinkedIn -->\n";
    $html .= "  <meta property=\"og:type\" content=\"" . htmlspecialchars($ogType, ENT_QUOTES, 'UTF-8') . "\" />\n";
    $html .= "  <meta property=\"og:url\" content=\"" . htmlspecialchars($cleanCanonical, ENT_QUOTES, 'UTF-8') . "\" />\n";
    $html .= "  <meta property=\"og:site_name\" content=\"" . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . "\" />\n";
    $html .= "  <meta property=\"og:title\" content=\"" . htmlspecialchars($ogTitle, ENT_QUOTES, 'UTF-8') . "\" />\n";
    $html .= "  <meta property=\"og:description\" content=\"" . htmlspecialchars($ogDesc, ENT_QUOTES, 'UTF-8') . "\" />\n";
    $html .= "  <meta property=\"og:image\" content=\"" . htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') . "\" />\n";

    // Twitter Card
    $html .= "\n  <!-- Twitter / X Card -->\n";
    $html .= "  <meta name=\"twitter:card\" content=\"summary_large_image\" />\n";
    $html .= "  <meta name=\"twitter:url\" content=\"" . htmlspecialchars($cleanCanonical, ENT_QUOTES, 'UTF-8') . "\" />\n";
    $html .= "  <meta name=\"twitter:title\" content=\"" . htmlspecialchars($ogTitle, ENT_QUOTES, 'UTF-8') . "\" />\n";
    $html .= "  <meta name=\"twitter:description\" content=\"" . htmlspecialchars($ogDesc, ENT_QUOTES, 'UTF-8') . "\" />\n";
    $html .= "  <meta name=\"twitter:image\" content=\"" . htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') . "\" />\n";
    if (!empty($twitterHandle)) {
        $html .= "  <meta name=\"twitter:site\" content=\"" . htmlspecialchars($twitterHandle, ENT_QUOTES, 'UTF-8') . "\" />\n";
    }

    // Favicon & Icons
    $html .= "\n  <!-- Favicon -->\n";
    $html .= "  <link rel=\"icon\" type=\"image/svg+xml\" href=\"" . htmlspecialchars($favicon, ENT_QUOTES, 'UTF-8') . "\" />\n";
    $html .= "  <link rel=\"apple-touch-icon\" href=\"/logo_svg.svg\" />\n";

    // Search Engine Verification Tags
    if (!empty($googleVerification)) {
        $html .= "  <meta name=\"google-site-verification\" content=\"" . htmlspecialchars($googleVerification, ENT_QUOTES, 'UTF-8') . "\" />\n";
    }
    if (!empty($bingVerification)) {
        $html .= "  <meta name=\"msvalidate.01\" content=\"" . htmlspecialchars($bingVerification, ENT_QUOTES, 'UTF-8') . "\" />\n";
    }

    // Google Tag Manager / Analytics (Head Script)
    if (!empty($gtmId)) {
        $cleanGtm = htmlspecialchars($gtmId, ENT_QUOTES, 'UTF-8');
        $html .= "\n  <!-- Google Tag Manager -->\n";
        $html .= "  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{$cleanGtm}');</script>\n";
    } elseif (!empty($ga4Id)) {
        $cleanGa4 = htmlspecialchars($ga4Id, ENT_QUOTES, 'UTF-8');
        $html .= "\n  <!-- Google Analytics 4 (gtag.js) -->\n";
        $html .= "  <script async src=\"https://www.googletagmanager.com/gtag/js?id={$cleanGa4}\"></script>\n";
        $html .= "  <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{$cleanGa4}');</script>\n";
    }

    // Facebook Pixel
    if (!empty($fbPixel)) {
        $cleanFb = htmlspecialchars($fbPixel, ENT_QUOTES, 'UTF-8');
        $html .= "\n  <!-- Meta Pixel Code -->\n";
        $html .= "  <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','{$cleanFb}');fbq('track','PageView');</script>\n";
    }

    // Custom Header Scripts from Admin
    if (!empty($headerScripts)) {
        $html .= "\n  <!-- Custom Header Scripts -->\n  " . $headerScripts . "\n";
    }

    return $html;
}
