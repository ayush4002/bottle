<?php
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/SeoModel.php';

checkAdminAuth();
SeoModel::init();

$msg = '';
$err = '';
$adminUser = $_SESSION['admin_user'] ?? 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $err = "Invalid security token.";
    } else {
        $types = ['product', 'category', 'blog', 'static'];
        foreach ($types as $t) {
            $titleTpl = trim($_POST['title_' . $t] ?? '');
            $descTpl = trim($_POST['desc_' . $t] ?? '');
            if (!empty($titleTpl)) {
                SeoModel::saveTemplate($t, $titleTpl, $descTpl, $adminUser);
            }
        }
        $msg = "Dynamic SEO Meta Templates successfully saved!";
    }
}

$templates = SeoModel::getTemplates();
$csrfToken = generateCsrfToken();
?>

<div style="max-width: 1200px; margin: 0 auto;">

  <!-- PAGE HEADER -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
      <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 0.88rem; color: #94a3b8;">
        <a href="/admin/seo" style="color: #94a3b8; text-decoration: none;">&larr; SEO Overview</a>
        <span>/</span>
        <span style="color: #38bdf8; font-weight: 600;">Meta Templates</span>
      </div>
      <h1 style="font-size: 1.8rem; font-weight: 800; color: #f8fafc; margin: 0;">
        Automated Meta Title &amp; Description Templates
      </h1>
      <p style="color: #94a3b8; font-size: 0.88rem; margin: 4px 0 0 0;">
        Applied dynamically whenever a product, category, or article has no manual SEO override.
      </p>
    </div>
    <div>
      <button type="button" onclick="document.getElementById('templatesForm').submit();" class="tn-btn-gold" style="padding: 11px 24px; border: none; border-radius: 8px; font-weight: 700; font-size: 0.92rem; cursor: pointer; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35);">
        SAVE TEMPLATES
      </button>
    </div>
  </div>

  <?php if (!empty($msg)): ?>
    <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px;">
      <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <!-- VARIABLE TOKENS CHEAT SHEET -->
  <div style="background: #0f172a; border: 1px solid #334155; border-radius: 12px; padding: 18px 24px; margin-bottom: 24px;">
    <div style="font-size: 0.86rem; font-weight: 700; color: #f8fafc; margin-bottom: 10px;">
      Supported Dynamic Variables:
    </div>
    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
      <code style="background: #1e293b; color: #38bdf8; padding: 4px 8px; border-radius: 4px; font-size: 0.82rem;">{site_name}</code>
      <code style="background: #1e293b; color: #38bdf8; padding: 4px 8px; border-radius: 4px; font-size: 0.82rem;">{product_name}</code>
      <code style="background: #1e293b; color: #38bdf8; padding: 4px 8px; border-radius: 4px; font-size: 0.82rem;">{category_name}</code>
      <code style="background: #1e293b; color: #38bdf8; padding: 4px 8px; border-radius: 4px; font-size: 0.82rem;">{price}</code>
      <code style="background: #1e293b; color: #38bdf8; padding: 4px 8px; border-radius: 4px; font-size: 0.82rem;">{material}</code>
      <code style="background: #1e293b; color: #38bdf8; padding: 4px 8px; border-radius: 4px; font-size: 0.82rem;">{neck_finish}</code>
      <code style="background: #1e293b; color: #38bdf8; padding: 4px 8px; border-radius: 4px; font-size: 0.82rem;">{article_title}</code>
      <code style="background: #1e293b; color: #38bdf8; padding: 4px 8px; border-radius: 4px; font-size: 0.82rem;">{article_excerpt}</code>
      <code style="background: #1e293b; color: #38bdf8; padding: 4px 8px; border-radius: 4px; font-size: 0.82rem;">{page_name}</code>
    </div>
  </div>

  <form id="templatesForm" method="POST" action="/admin/seo_templates">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />

    <!-- 1. PRODUCT TEMPLATE -->
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
      <h3 style="margin: 0 0 16px 0; font-size: 1.1rem; color: #38bdf8; font-weight: 800; border-bottom: 1px solid #334155; padding-bottom: 10px;">
        1. Product Catalog Template
      </h3>
      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Title Template</label>
        <input type="text" name="title_product" value="<?= htmlspecialchars($templates['product']['title_template'] ?? '{product_name} - Wholesale High-Tolerance Packaging | {site_name}') ?>" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
      </div>
      <div>
        <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Description Template</label>
        <textarea name="desc_product" rows="3" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem; line-height: 1.4;"><?= htmlspecialchars($templates['product']['description_template'] ?? 'Wholesale {product_name} manufactured in ISO certified cleanrooms. Material: {material}, Neck Finish: {neck_finish}. Get instant B2B quotation & free samples.') ?></textarea>
      </div>
    </div>

    <!-- 2. CATEGORY TEMPLATE -->
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
      <h3 style="margin: 0 0 16px 0; font-size: 1.1rem; color: #38bdf8; font-weight: 800; border-bottom: 1px solid #334155; padding-bottom: 10px;">
        2. Category &amp; Industry Listings Template
      </h3>
      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Title Template</label>
        <input type="text" name="title_category" value="<?= htmlspecialchars($templates['category']['title_template'] ?? '{category_name} Packaging & Containers Directory | {site_name}') ?>" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
      </div>
      <div>
        <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Description Template</label>
        <textarea name="desc_category" rows="3" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem; line-height: 1.4;"><?= htmlspecialchars($templates['category']['description_template'] ?? 'Browse comprehensive range of {category_name} engineered for pharmaceutical, cosmetic, and personal-care applications. Direct manufacturer volume pricing.') ?></textarea>
      </div>
    </div>

    <!-- 3. BLOG POST TEMPLATE -->
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
      <h3 style="margin: 0 0 16px 0; font-size: 1.1rem; color: #38bdf8; font-weight: 800; border-bottom: 1px solid #334155; padding-bottom: 10px;">
        3. Blog Post &amp; Technical Articles Template
      </h3>
      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Title Template</label>
        <input type="text" name="title_blog" value="<?= htmlspecialchars($templates['blog']['title_template'] ?? '{article_title} | Technical Packaging Guide | {site_name}') ?>" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
      </div>
      <div>
        <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Description Template</label>
        <textarea name="desc_blog" rows="3" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem; line-height: 1.4;"><?= htmlspecialchars($templates['blog']['description_template'] ?? '{article_excerpt}') ?></textarea>
      </div>
    </div>

    <!-- 4. STATIC PAGE TEMPLATE -->
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
      <h3 style="margin: 0 0 16px 0; font-size: 1.1rem; color: #38bdf8; font-weight: 800; border-bottom: 1px solid #334155; padding-bottom: 10px;">
        4. Static Information Pages Template
      </h3>
      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Title Template</label>
        <input type="text" name="title_static" value="<?= htmlspecialchars($templates['static']['title_template'] ?? '{page_name} | {site_name}') ?>" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" />
      </div>
      <div>
        <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Description Template</label>
        <textarea name="desc_static" rows="3" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem; line-height: 1.4;"><?= htmlspecialchars($templates['static']['description_template'] ?? '{site_description}') ?></textarea>
      </div>
    </div>

    <div style="text-align: right; margin-bottom: 40px;">
      <button type="submit" class="tn-btn-gold" style="padding: 13px 32px; border: none; border-radius: 8px; font-weight: 700; font-size: 0.98rem; cursor: pointer; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35);">
        SAVE TEMPLATES
      </button>
    </div>

  </form>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>

