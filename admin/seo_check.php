<?php
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/SeoModel.php';
require_once dirname(__DIR__) . '/app/Models/ProductModel.php';
require_once dirname(__DIR__) . '/app/Models/BlogModel.php';

checkAdminAuth();
SeoModel::init();

$issues = [];
$checkedCount = 0;

// Scan Static Pages
$staticPages = ['/', '/products', '/about', '/custom', '/sustainability', '/blog', '/contact', '/terms', '/privacy'];
foreach ($staticPages as $url) {
    $checkedCount++;
    $seo = SeoModel::getPageSeo($url);
    $t = $seo['meta_title'] ?? '';
    $d = $seo['meta_description'] ?? '';
    $h1 = $seo['h1_override'] ?? '';

    if (empty($t)) {
        $issues[] = ['type' => 'Warning', 'url' => $url, 'issue' => 'Missing dedicated meta title override (using template/fallback)', 'action' => "/admin/seo_pages?search=" . urlencode($url)];
    }
    if (empty($d)) {
        $issues[] = ['type' => 'Warning', 'url' => $url, 'issue' => 'Missing dedicated meta description override', 'action' => "/admin/seo_pages?search=" . urlencode($url)];
    }
}

// Scan Products for missing descriptions or alt text
if (class_exists('ProductModel')) {
    $prods = ProductModel::getAllProducts();
    foreach ($prods as $p) {
        $checkedCount++;
        $sku = $p['sku'] ?? ($p['id'] ?? ($p['articleNo'] ?? ''));
        $pUrl = '/product?id=' . urlencode((string)$sku);
        $name = $p['name'] ?? '';
        $desc = $p['description'] ?? ($p['shortDescription'] ?? '');

        if (empty(trim($name))) {
            $issues[] = ['type' => 'Error', 'url' => $pUrl, 'issue' => "Product SKU {$sku} has no title name", 'action' => "/admin/edit_product?id=" . $sku];
        }
        if (empty(trim($desc))) {
            $issues[] = ['type' => 'Warning', 'url' => $pUrl, 'issue' => "Product {$sku} missing description", 'action' => "/admin/edit_product?id=" . $sku];
        }
    }
}

// Scan Images
$imgLib = SeoModel::getImageAltLibrary();
$missingAltCount = 0;
foreach ($imgLib as $img) {
    if (empty(trim($img['alt_text'] ?? ''))) {
        $missingAltCount++;
    }
}
if ($missingAltCount > 0) {
    $issues[] = [
        'type' => 'Notice',
        'url' => 'Media & Images',
        'issue' => "{$missingAltCount} packaging images lack descriptive alt attributes",
        'action' => '/admin/seo_images'
    ];
}

// Scan Redirects for Loops / Chains
$redirects = SeoModel::getRedirects();
foreach ($redirects as $r) {
    if (!empty($r['has_loop'])) {
        $issues[] = [
            'type' => 'Critical',
            'url' => $r['old_url'],
            'issue' => "Redirect loop detected! Source redirects to {$r['new_url']} which loops back.",
            'action' => '/admin/seo_redirects'
        ];
    } elseif (!empty($r['has_chain'])) {
        $issues[] = [
            'type' => 'Warning',
            'url' => $r['old_url'],
            'issue' => "Redirect chain detected! Points to {$r['new_url']} which has another redirect rule.",
            'action' => '/admin/seo_redirects'
        ];
    }
}
?>

<div style="max-width: 1200px; margin: 0 auto;">

  <!-- PAGE HEADER -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
      <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 0.88rem; color: #94a3b8;">
        <a href="/admin/seo" style="color: #94a3b8; text-decoration: none;">&larr; SEO Overview</a>
        <span>/</span>
        <span style="color: #38bdf8; font-weight: 600;">Crawl &amp; Audit Scanner</span>
      </div>
      <h1 style="font-size: 1.8rem; font-weight: 800; color: #f8fafc; margin: 0;">
        Internal Website SEO Crawler &amp; Diagnostics
      </h1>
      <p style="color: #94a3b8; font-size: 0.88rem; margin: 4px 0 0 0;">
        Deep scan completed across <?= $checkedCount ?> URLs, catalogue products, redirects, and media assets.
      </p>
    </div>
    <div>
      <a href="/admin/seo_check" class="tn-btn-gold" style="padding: 10px 20px; border-radius: 8px; font-weight: 700; text-decoration: none; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35);">
        &#8635; Run Deep Scan Again
      </a>
    </div>
  </div>

  <!-- AUDIT SUMMARY -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; overflow: hidden; margin-bottom: 30px;">
    <div style="padding: 16px 20px; background: #0f172a; border-bottom: 1px solid #334155; font-size: 1.05rem; font-weight: 800; color: #f8fafc;">
      Diagnostic Findings (<?= count($issues) ?> items identified)
    </div>

    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.86rem;">
        <thead>
          <tr style="color: #94a3b8; border-bottom: 1px solid #334155; background: #162032;">
            <th style="padding: 12px 18px; width: 100px;">Severity</th>
            <th style="padding: 12px 18px; width: 250px;">URL / Asset</th>
            <th style="padding: 12px 18px;">Diagnostic Details</th>
            <th style="padding: 12px 18px; text-align: right;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($issues)): ?>
            <tr>
              <td colspan="4" style="padding: 40px; text-align: center; color: #34d399; font-weight: 700; font-size: 1.1rem;">
                Flawless! No SEO structural errors, broken loops, or missing metadata found.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($issues as $iss): ?>
              <?php
                $sev = $iss['type'];
                $sCol = ($sev === 'Critical') ? '#ef4444' : (($sev === 'Error') ? '#f87171' : (($sev === 'Warning') ? '#fbbf24' : '#38bdf8'));
                $sBg = ($sev === 'Critical') ? 'rgba(239, 68, 68, 0.2)' : (($sev === 'Error') ? 'rgba(239, 68, 68, 0.12)' : (($sev === 'Warning') ? 'rgba(251, 191, 36, 0.15)' : 'rgba(56, 189, 248, 0.15)'));
              ?>
              <tr style="border-bottom: 1px solid #334155;">
                <td style="padding: 12px 18px;">
                  <span style="background: <?= $sBg ?>; color: <?= $sCol ?>; padding: 2px 8px; border-radius: 4px; font-weight: 700; font-size: 0.76rem;">
                    <?= htmlspecialchars($sev) ?>
                  </span>
                </td>
                <td style="padding: 12px 18px; font-family: monospace; color: #38bdf8; font-weight: 600;">
                  <?= htmlspecialchars($iss['url']) ?>
                </td>
                <td style="padding: 12px 18px; color: #cbd5e1;">
                  <?= htmlspecialchars($iss['issue']) ?>
                </td>
                <td style="padding: 12px 18px; text-align: right; white-space: nowrap;">
                  <a href="<?= htmlspecialchars($iss['action']) ?>" style="padding: 5px 12px; background: #334155; color: #fff; text-decoration: none; border-radius: 4px; font-size: 0.78rem; font-weight: 600;">
                    Resolve &rarr;
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>

