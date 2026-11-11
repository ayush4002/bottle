<?php
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/SeoModel.php';
require_once dirname(__DIR__) . '/app/Models/ProductModel.php';

checkAdminAuth();
SeoModel::init();

$msg = '';
$err = '';
$adminUser = $_SESSION['admin_user'] ?? 'admin';

// Handle Bulk Save of Alt Texts
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_alts') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $err = "Invalid security token.";
    } else {
        $alts = $_POST['alts'] ?? [];
        $titles = $_POST['titles'] ?? [];
        $count = 0;
        foreach ($alts as $idOrUrl => $alt) {
            $title = $titles[$idOrUrl] ?? '';
            SeoModel::saveImageAlt($idOrUrl, trim($alt), trim($title), $adminUser);
            $count++;
        }
        $msg = "Successfully updated alt text for {$count} images!";
    }
}

$images = SeoModel::getImageAltLibrary();
$filterMissing = isset($_GET['missing']) && $_GET['missing'] === '0' ? false : true;

if ($filterMissing) {
    $images = array_filter($images, function($img) {
        return empty(trim($img['alt_text'] ?? ''));
    });
}

$csrfToken = generateCsrfToken();
?>

<div style="max-width: 1280px; margin: 0 auto;">

  <!-- PAGE HEADER -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
      <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 0.88rem; color: #94a3b8;">
        <a href="/admin/seo" style="color: #94a3b8; text-decoration: none;">&larr; SEO Overview</a>
        <span>/</span>
        <span style="color: #38bdf8; font-weight: 600;">Image SEO &amp; Alt Tags</span>
      </div>
      <h1 style="font-size: 1.8rem; font-weight: 800; color: #f8fafc; margin: 0;">
        Image SEO Optimization &amp; Alt Tag Tool
      </h1>
      <p style="color: #94a3b8; font-size: 0.88rem; margin: 4px 0 0 0;">
        Ensure all packaging photos and technical diagrams have keyword-rich descriptive alt attributes for Google Images and web accessibility.
      </p>
    </div>
    <div style="display: flex; gap: 10px;">
      <button type="button" onclick="autoFillAllEmptyAlts()" class="tn-btn-outline" style="padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: 0.86rem; background: #1e293b; color: #38bdf8; border: 1px solid #334155; cursor: pointer;">
        Auto-Fill from Product Names
      </button>
      <button type="button" onclick="document.getElementById('imageAltForm').submit();" class="tn-btn-gold" style="padding: 10px 22px; border: none; border-radius: 8px; font-weight: 700; font-size: 0.88rem; cursor: pointer; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35);">
        SAVE ALL ALT TEXTS
      </button>
    </div>
  </div>

  <?php if (!empty($msg)): ?>
    <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 12px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 20px;">
      <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($err)): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 12px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 20px;">
      <?= htmlspecialchars($err) ?>
    </div>
  <?php endif; ?>

  <!-- FILTER TOGGLE -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 14px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
    <div style="font-size: 0.92rem; font-weight: 700; color: #f8fafc;">
      Filter Visuals:
    </div>
    <div style="display: flex; gap: 10px;">
      <a href="/admin/seo_images?missing=1" style="padding: 6px 14px; border-radius: 6px; font-size: 0.82rem; font-weight: 600; text-decoration: none; background: <?= $filterMissing ? '#0284c7' : '#0f172a' ?>; color: #fff; border: 1px solid #334155;">
        Missing Alt Text Only (<?= count($images) ?>)
      </a>
      <a href="/admin/seo_images?missing=0" style="padding: 6px 14px; border-radius: 6px; font-size: 0.82rem; font-weight: 600; text-decoration: none; background: <?= !$filterMissing ? '#0284c7' : '#0f172a' ?>; color: #fff; border: 1px solid #334155;">
        All Catalog Images
      </a>
    </div>
  </div>

  <!-- IMAGES TABLE -->
  <form id="imageAltForm" method="POST" action="/admin/seo_images">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
    <input type="hidden" name="action" value="save_alts" />

    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; overflow: hidden; margin-bottom: 30px;">
      <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.86rem;">
          <thead>
            <tr style="color: #94a3b8; border-bottom: 1px solid #334155; background: #162032;">
              <th style="padding: 12px 16px; width: 80px;">Thumbnail</th>
              <th style="padding: 12px 16px; width: 220px;">Product / Container</th>
              <th style="padding: 12px 16px;">Descriptive Alt Text (Accessibility &amp; Image Search)</th>
              <th style="padding: 12px 16px; width: 200px;">Title Tooltip (Optional)</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($images)): ?>
              <tr>
                <td colspan="4" style="padding: 40px; text-align: center; color: #34d399; font-weight: 600;">
                  All catalog and content images have valid descriptive alt text!
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($images as $img): ?>
                <?php $key = $img['id'] ?? $img['image_url']; ?>
                <tr style="border-bottom: 1px solid #334155;">
                  <td style="padding: 12px 16px;">
                    <div style="width: 58px; height: 58px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                      <img src="<?= htmlspecialchars($img['image_url']) ?>" style="max-height: 100%; max-width: 100%; object-fit: contain;" alt="<?= htmlspecialchars($img['alt_text'] ?? '') ?>" onerror="this.src='/logo_svg.svg'" />
                    </div>
                  </td>
                  <td style="padding: 12px 16px;">
                    <div style="font-weight: 700; color: #f8fafc; margin-bottom: 2px;" class="prod-name-label">
                      <?= htmlspecialchars($img['product_name'] ?? 'Packaging Product') ?>
                    </div>
                    <div style="font-size: 0.75rem; color: #94a3b8; font-family: monospace;">
                      SKU: <?= htmlspecialchars($img['sku'] ?? 'TN-SKU') ?>
                    </div>
                  </td>
                  <td style="padding: 12px 16px;">
                    <input type="text" name="alts[<?= htmlspecialchars($key) ?>]" value="<?= htmlspecialchars($img['alt_text'] ?? '') ?>" class="alt-input" data-default="<?= htmlspecialchars($img['product_name'] ?? 'Primary packaging container') ?>" placeholder="e.g. 100ml Amber PET Pharma Bottle with Tamper Evident Cap" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #38bdf8; font-size: 0.88rem;" />
                  </td>
                  <td style="padding: 12px 16px;">
                    <input type="text" name="titles[<?= htmlspecialchars($key) ?>]" value="<?= htmlspecialchars($img['title_text'] ?? '') ?>" placeholder="Optional image title..." style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #cbd5e1; font-size: 0.85rem;" />
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div style="text-align: right; margin-bottom: 40px;">
      <button type="submit" class="tn-btn-gold" style="padding: 12px 28px; border: none; border-radius: 8px; font-weight: 700; font-size: 0.95rem; cursor: pointer; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35);">
        SAVE ALL ALT TEXTS
      </button>
    </div>
  </form>

</div>

<script>
function autoFillAllEmptyAlts() {
  document.querySelectorAll('.alt-input').forEach(input => {
    if (!input.value.trim()) {
      const def = input.getAttribute('data-default');
      if (def) {
        input.value = def + ' - TrueNorth Primary Packaging';
      }
    }
  });
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

