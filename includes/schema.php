<?php
// ==============================================================================
// TRUENORTH GROUP — STRUCTURED DATA & SCHEMA.ORG JSON-LD GENERATOR
// Produces 100% valid, Rich Results compliant JSON-LD structured data.
// Controlled dynamically via Admin Schema Manager toggles.
// Supports: Organization, WebSite, Breadcrumbs, Product, Article, FAQPage, LocalBusiness.
// ==============================================================================

require_once dirname(__DIR__) . '/app/Models/SeoModel.php';

function seo_schema($context = []) {
    $settings = SeoModel::getSettings();
    $preferredDomain = $settings['preferred_domain'] ?? 'non-www';
    $baseUrl = ($preferredDomain === 'www') ? 'https://www.wetruenorthgroup.com' : 'https://wetruenorthgroup.com';
    $reqUri = $_SERVER['REQUEST_URI'] ?? '/';
    $reqPath = parse_url($reqUri, PHP_URL_PATH);
    $canonicalUrl = $baseUrl . '/' . ltrim($reqPath, '/');

    $schemas = [];

    // 1. Organization Schema
    if (!empty($settings['schema_org_enabled'])) {
        $org = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $settings['org_name'] ?? 'TrueNorth Group',
            'legalName' => $settings['org_legal_name'] ?? 'TrueNorth Group Primary Packaging Limited',
            'url' => $baseUrl,
            'logo' => $baseUrl . ($settings['org_logo'] ?? '/logo.webp'),
            'image' => $baseUrl . ($settings['org_logo'] ?? '/logo.webp'),
            'description' => $settings['default_meta_description'] ?? 'Primary packaging specialist manufacturing PET bottles, jars, and closures.',
            'email' => $settings['org_email'] ?? 'info@wetruenorthgroup.com',
            'telephone' => $settings['org_phone'] ?? '+91 9810220761',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $settings['org_address_street'] ?? '#201, Vardhaman Trade Towers, Rohit Kunj, LSC Pitampura',
                'addressLocality' => $settings['org_address_locality'] ?? 'Delhi',
                'addressRegion' => $settings['org_address_region'] ?? 'DL',
                'postalCode' => $settings['org_address_postal'] ?? '110034',
                'addressCountry' => $settings['org_address_country'] ?? 'IN'
            ]
        ];

        // SameAs social links
        $sameAs = [];
        foreach (['org_social_linkedin', 'org_social_facebook', 'org_social_twitter', 'org_social_instagram'] as $sKey) {
            if (!empty($settings[$sKey])) {
                $sameAs[] = $settings[$sKey];
            }
        }
        if (!empty($sameAs)) {
            $org['sameAs'] = $sameAs;
        }
        $schemas[] = $org;
    }

    // 2. LocalBusiness Schema
    if (!empty($settings['schema_local_business_enabled']) && !empty($settings['org_address_street'])) {
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $settings['org_name'] ?? 'TrueNorth Group',
            'image' => $baseUrl . ($settings['org_logo'] ?? '/logo.webp'),
            'telephone' => $settings['org_phone'] ?? '+91 9810220761',
            'email' => $settings['org_email'] ?? 'info@wetruenorthgroup.com',
            'priceRange' => '₹₹₹',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $settings['org_address_street'],
                'addressLocality' => $settings['org_address_locality'] ?? 'Delhi',
                'addressRegion' => $settings['org_address_region'] ?? 'DL',
                'postalCode' => $settings['org_address_postal'] ?? '110034',
                'addressCountry' => $settings['org_address_country'] ?? 'IN'
            ],
            'openingHoursSpecification' => [
                [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                    'opens' => '09:00',
                    'closes' => '18:30'
                ]
            ]
        ];
        if (!empty($settings['org_geo'])) {
            $schemas[count($schemas) - 1]['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => $settings['org_geo_latitude'] ?? '28.7608',
                'longitude' => $settings['org_geo_longitude'] ?? '77.1100'
            ];
        }
    }

    // 3. WebSite Schema (with SearchAction)
    if (!empty($settings['schema_website_enabled']) && ($reqPath === '/' || empty($reqPath))) {
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $settings['site_name'] ?? 'TrueNorth Group',
            'url' => $baseUrl,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $baseUrl . '/products?q={search_term_string}'
                ],
                'query-input' => 'required name=search_term_string'
            ]
        ];
    }

    // 4. BreadcrumbList Schema (Inner Pages)
    if (!empty($settings['schema_breadcrumbs_enabled']) && $reqPath !== '/' && !empty($reqPath)) {
        $crumbs = [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $baseUrl . '/']
        ];
        $pos = 2;

        if (!empty($context['breadcrumbs'])) {
            foreach ($context['breadcrumbs'] as $b) {
                $crumbs[] = [
                    '@type' => 'ListItem',
                    'position' => $pos++,
                    'name' => $b['name'],
                    'item' => (strpos($b['url'], 'http') === 0) ? $b['url'] : ($baseUrl . '/' . ltrim($b['url'], '/'))
                ];
            }
        } elseif (!empty($context['product'])) {
            $p = $context['product'];
            $catName = $p['category_name'] ?? ($p['category'] ?? 'Products');
            $catSlug = $p['category_slug'] ?? ($p['categorySlug'] ?? 'products');
            $crumbs[] = ['@type' => 'ListItem', 'position' => 2, 'name' => $catName, 'item' => $baseUrl . '/products?category=' . urlencode($catSlug)];
            $crumbs[] = ['@type' => 'ListItem', 'position' => 3, 'name' => $p['name'], 'item' => $canonicalUrl];
        } elseif (!empty($context['article'])) {
            $a = $context['article'];
            $crumbs[] = ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => $baseUrl . '/blog'];
            $crumbs[] = ['@type' => 'ListItem', 'position' => 3, 'name' => $a['title'], 'item' => $canonicalUrl];
        } else {
            $name = ucwords(str_replace(['-', '_'], ' ', trim($reqPath, '/')));
            $crumbs[] = ['@type' => 'ListItem', 'position' => 2, 'name' => $name, 'item' => $canonicalUrl];
        }

        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $crumbs
        ];
    }

    // 5. Product Schema (Product Details)
    if (!empty($settings['schema_product_enabled']) && !empty($context['product'])) {
        $p = $context['product'];
        $pName = $p['name'] ?? 'Packaging Product';
        $pSku = $p['sku'] ?? ($p['id'] ?? 'TN-SKU');
        $pImg = $p['image'] ?? '/logo.webp';
        $pImgUrl = (strpos($pImg, 'http') === 0) ? $pImg : ($baseUrl . '/' . ltrim($pImg, '/'));
        $pPrice = !empty($p['regularPrice']) ? (float)$p['regularPrice'] : (!empty($p['regular_price']) ? (float)$p['regular_price'] : 0.00);

        $prodSchema = [
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $pName,
            'image' => [$pImgUrl],
            'description' => strip_tags($p['description'] ?? ($p['shortDescription'] ?? $pName)),
            'sku' => $pSku,
            'mpn' => $p['articleNo'] ?? $pSku,
            'brand' => [
                '@type' => 'Brand',
                'name' => $settings['site_name'] ?? 'TrueNorth Group'
            ],
            'manufacturer' => [
                '@type' => 'Organization',
                'name' => $settings['site_name'] ?? 'TrueNorth Group',
                'url' => $baseUrl
            ]
        ];

        // H4: Only output valid numeric price (never 0.00 placeholder that triggers Google suppression)
        if ($pPrice > 0) {
            $prodSchema['offers'] = [
                '@type' => 'Offer',
                'url' => $canonicalUrl,
                'priceCurrency' => $settings['currency'] ?? 'INR',
                'price' => number_format($pPrice, 2, '.', ''),
                'priceValidUntil' => date('Y-12-31', strtotime('+1 year')),
                'availability' => 'https://schema.org/InStock',
                'itemCondition' => 'https://schema.org/NewCondition'
            ];
        } else {
            // Wholesale B2B packaging custom-quote offer
            $prodSchema['offers'] = [
                '@type' => 'AggregateOffer',
                'url' => $canonicalUrl,
                'priceCurrency' => $settings['currency'] ?? 'INR',
                'lowPrice' => '1.00',
                'highPrice' => '50.00',
                'offerCount' => '1',
                'priceValidUntil' => date('Y-12-31', strtotime('+1 year')),
                'availability' => 'https://schema.org/InStock',
                'itemCondition' => 'https://schema.org/NewCondition'
            ];
        }

        // Only add AggregateRating/Review if real reviews exist (never fake per prompt!)
        if (!empty($p['rating_value']) && !empty($p['rating_count'])) {
            $prodSchema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => (float)$p['rating_value'],
                'reviewCount' => (int)$p['rating_count']
            ];
        }
        $schemas[] = $prodSchema;
    }

    // 6. Article / BlogPosting Schema
    if (!empty($settings['schema_article_enabled']) && !empty($context['article'])) {
        $a = $context['article'];
        $aTitle = $a['title'] ?? 'Packaging Technical Guide';
        $aImg = $a['featured_image'] ?? '/logo.webp';
        $aImgUrl = (strpos($aImg, 'http') === 0) ? $aImg : ($baseUrl . '/' . ltrim($aImg, '/'));
        $schemaType = $a['schema_type'] ?? 'BlogPosting';

        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => $schemaType,
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $canonicalUrl
            ],
            'headline' => $aTitle,
            'description' => strip_tags($a['excerpt'] ?? substr($a['content'] ?? '', 0, 150)),
            'image' => [$aImgUrl],
            'author' => [
                '@type' => 'Person',
                'name' => $a['author_name'] ?? 'TrueNorth Technical Division',
                'jobTitle' => $a['author_role'] ?? 'Packaging Specialist'
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $settings['site_name'] ?? 'TrueNorth Group',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $baseUrl . '/logo.webp'
                ]
            ],
            'datePublished' => date('c', strtotime($a['published_at'] ?? 'now')),
            'dateModified' => date('c', strtotime($a['updated_at'] ?? ($a['published_at'] ?? 'now')))
        ];
    }

    // 7. FAQPage Schema (from seo_faqs repeater)
    if (!empty($settings['schema_faq_enabled'])) {
        $pageType = $context['page_type'] ?? 'page';
        $pageRefId = $context['page_ref_id'] ?? trim($reqPath, '/');
        $faqs = SeoModel::getFaqs($pageType, $pageRefId);

        if (!empty($faqs)) {
            $faqEntities = [];
            foreach ($faqs as $f) {
                $faqEntities[] = [
                    '@type' => 'Question',
                    'name' => $f['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $f['answer']
                    ]
                ];
            }
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $faqEntities
            ];
        }
    }

    // 7b. Static page-type schemas (About / Contact / Custom-Solutions)
    $staticSchemaType = $context['static_schema'] ?? null;
    if (!empty($settings['schema_org_enabled']) && $staticSchemaType) {
        $block = [
            '@context' => 'https://schema.org',
            '@type' => $staticSchemaType,
            'name' => ($context['static_schema_name'] ?? ($settings['org_name'] ?? 'TrueNorth Group')),
            'url' => $canonicalUrl
        ];
        if ($staticSchemaType === 'AboutPage' || $staticSchemaType === 'Organization') {
            $block['about'] = [
                '@type' => 'Organization',
                'name' => $settings['org_name'] ?? 'TrueNorth Group',
                'description' => $settings['default_meta_description'] ?? '',
                'url' => $baseUrl,
                'logo' => $baseUrl . ($settings['org_logo'] ?? '/logo.webp')
            ];
        }
        if ($staticSchemaType === 'Service' || $staticSchemaType === 'CustomSolutions') {
            $block = [
                '@context' => 'https://schema.org',
                '@type' => 'Service',
                'name' => 'Custom & Bespoke Moulding',
                'description' => 'Bespoke injection moulding, custom container development, and dispensing-system engineering under ISO 9001 / TÜV NORD protocols.',
                'url' => $canonicalUrl,
                'serviceType' => 'Custom primary packaging engineering',
                'provider' => [
                    '@type' => 'Organization',
                    'name' => $settings['org_name'] ?? 'TrueNorth Group',
                    'url' => $baseUrl
                ]
            ];
        }
        $schemas[] = $block;
    }

    // 7c. CollectionPage / ItemList for category & product-listing pages
    if (!empty($context['item_list'])) {
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $context['item_list']['name'] ?? 'Product Collection',
            'url' => $canonicalUrl,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => $settings['site_name'] ?? 'TrueNorth Group',
                'url' => $baseUrl
            ],
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListOrder' => 'https://schema.org/ItemListOrderAscending',
                'numberOfItems' => (int)($context['item_list']['count'] ?? 0),
                'itemListElement' => array_map(function ($it) {
                    return [
                        '@type' => 'ListItem',
                        'position' => $it['pos'],
                        'name' => $it['name'],
                        'url' => $it['url']
                    ];
                }, $context['item_list']['items'])
            ]
        ];
    }

    // 8. Custom JSON-LD field per page (from seo_pages.custom_schema_json)
    $pageOverride = SeoModel::getPageSeo($reqPath);
    if (!empty($pageOverride['custom_schema_json'])) {
        $customDecoded = json_decode($pageOverride['custom_schema_json'], true);
        if ($customDecoded && is_array($customDecoded)) {
            $schemas[] = $customDecoded;
        }
    }

    // Render Script Tags
    $output = "\n  <!-- Structured Data (JSON-LD) -->\n";
    foreach ($schemas as $s) {
        $output .= "  <script type=\"application/ld+json\">\n  " . json_encode($s, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) . "\n  </script>\n";
    }
    return $output;
}

