<?php
// ==============================================================================
// TRUENORTH GROUP — DYNAMIC XML SITEMAP GENERATOR
// Automatically updates when new products or categories are added in CMS
// URL: https://wetruenorthgroup.com/sitemap.xml
// ==============================================================================

header("Content-Type: application/xml; charset=utf-8");
header("X-Robots-Tag: noindex");

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Models/ProductModel.php';

// Canonical domain for sitemap URLs (Always HTTPS for Google / Bing)
$baseUrl = 'https://wetruenorthgroup.com';

$today = date('Y-m-d');

// Static / Core Site Pages
$corePages = [
    [
        'url' => $baseUrl . '/',
        'lastmod' => $today,
        'changefreq' => 'daily',
        'priority' => '1.0'
    ],
    [
        'url' => $baseUrl . '/products',
        'lastmod' => $today,
        'changefreq' => 'daily',
        'priority' => '0.9'
    ],
    [
        'url' => $baseUrl . '/about',
        'lastmod' => date('Y-m-d', strtotime('-7 days')),
        'changefreq' => 'weekly',
        'priority' => '0.8'
    ],
    [
        'url' => $baseUrl . '/contact',
        'lastmod' => date('Y-m-d', strtotime('-7 days')),
        'changefreq' => 'monthly',
        'priority' => '0.8'
    ],
    [
        'url' => $baseUrl . '/custom',
        'lastmod' => date('Y-m-d', strtotime('-14 days')),
        'changefreq' => 'monthly',
        'priority' => '0.7'
    ],
    [
        'url' => $baseUrl . '/sustainability',
        'lastmod' => date('Y-m-d', strtotime('-14 days')),
        'changefreq' => 'monthly',
        'priority' => '0.7'
    ]
];

// Fetch categories
$categories = ProductModel::getCategories();
$categoryPages = [];
foreach ($categories as $cat) {
    $slug = $cat['slug'] ?? '';
    if (!empty($slug)) {
        $categoryPages[] = [
            'url' => $baseUrl . '/products?category=' . urlencode($slug),
            'lastmod' => $today,
            'changefreq' => 'weekly',
            'priority' => '0.8'
        ];
    }
}

// Fetch all published products
$skus = ProductModel::getSkus(false);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">

  <!-- Core Pages -->
<?php foreach ($corePages as $page): ?>
  <url>
    <loc><?= htmlspecialchars($page['url'], ENT_XML1) ?></loc>
    <lastmod><?= $page['lastmod'] ?></lastmod>
    <changefreq><?= $page['changefreq'] ?></changefreq>
    <priority><?= $page['priority'] ?></priority>
  </url>
<?php endforeach; ?>

  <!-- Category Landing Pages -->
<?php foreach ($categoryPages as $catPage): ?>
  <url>
    <loc><?= htmlspecialchars($catPage['url'], ENT_XML1) ?></loc>
    <lastmod><?= $catPage['lastmod'] ?></lastmod>
    <changefreq><?= $catPage['changefreq'] ?></changefreq>
    <priority><?= $catPage['priority'] ?></priority>
  </url>
<?php endforeach; ?>

  <!-- Individual Product Pages (<?= count($skus) ?> items) -->
<?php foreach ($skus as $sku): ?>
  <?php
    $skuId = $sku['id'] ?? '';
    if (empty($skuId)) continue;
    $pUrl = $baseUrl . '/product?id=' . urlencode($skuId);
    $pName = $sku['name'] ?? ($sku['fullTitle'] ?? 'Packaging Product');
    $pImage = $sku['image'] ?? '';
    $lastMod = !empty($sku['updatedAt']) ? date('Y-m-d', strtotime($sku['updatedAt'])) : $today;
  ?>
  <url>
    <loc><?= htmlspecialchars($pUrl, ENT_XML1) ?></loc>
    <lastmod><?= $lastMod ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
    <?php if (!empty($pImage)): ?>
      <?php
        $imgUrl = (strpos($pImage, 'http') === 0) ? $pImage : ($baseUrl . '/' . ltrim($pImage, '/'));
      ?>
    <image:image>
      <image:loc><?= htmlspecialchars($imgUrl, ENT_XML1) ?></image:loc>
      <image:title><?= htmlspecialchars($pName, ENT_XML1) ?></image:title>
      <image:caption><?= htmlspecialchars(($sku['shortDescription'] ?? $pName) . ' — TrueNorth Group Packaging', ENT_XML1) ?></image:caption>
    </image:image>
    <?php endif; ?>
  </url>
<?php endforeach; ?>

</urlset>
