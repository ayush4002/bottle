<?php
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/SeoModel.php';

checkAdminAuth();
SeoModel::init();

$msg = '';
$err = '';
$adminUser = $_SESSION['admin_user'] ?? 'admin';

// Handle Robots.txt Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_robots') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $err = "Invalid security token.";
    } else {
        $content = $_POST['robots_txt_content'] ?? '';
        $res = SeoModel::saveRobotsTxt($content, $adminUser);
        if (!empty($res['blocks_all'])) {
            $msg = "Warning: robots.txt updated, but 'Disallow: /' was detected which blocks all search engines from indexing the site!";
        } else {
            $msg = "robots.txt successfully updated!";
        }
    }
}

// Handle Schema Toggles Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_schemas') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $err = "Invalid security token.";
    } else {
        $schemaKeys = [
            'schema_org_enabled', 'schema_website_enabled', 'schema_breadcrumbs_enabled',
            'schema_product_enabled', 'schema_article_enabled', 'schema_faq_enabled',
            'schema_local_business_enabled'
        ];
        $saveData = [];
        foreach ($schemaKeys as $k) {
            $saveData[$k] = isset($_POST[$k]) ? '1' : '0';
        }
        SeoModel::saveSettings($saveData, $adminUser);
        $msg = "Schema.org structured data settings successfully updated.";
    }
}

// Handle Sitemap Regeneration
if (isset($_GET['action']) && $_GET['action'] === 'regenerate_sitemap') {
    SeoModel::generateSitemapXml();
    $msg = "sitemap.xml successfully regenerated with fresh URLs, priorities, and image metadata!";
}

$settings = SeoModel::getSettings();
$robotsContent = SeoModel::getRobotsTxt();
$csrfToken = generateCsrfToken();
?>

<div style="max-width: 1200px; margin: 0 auto;">

  <!-- PAGE HEADER -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
      <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 0.88rem; color: #94a3b8;">
        <a href="/admin/seo" style="color: #94a3b8; text-decoration: none;">&larr; SEO Overview</a>
        <span>/</span>
        <span style="color: #38bdf8; font-weight: 600;">Sitemap, Robots &amp; Schema</span>
      </div>
      <h1 style="font-size: 1.8rem; font-weight: 800; color: #f8fafc; margin: 0;">
        XML Sitemap, Robots.txt &amp; Schema Manager
      </h1>
    </div>
    <div style="display: flex; gap: 10px;">
      <a href="/admin/seo_sitemap?action=regenerate_sitemap&csrf_token=<?= urlencode($csrfToken) ?>" class="tn-btn-gold" style="padding: 10px 18px; border-radius: 8px; font-weight: 700; font-size: 0.88rem; text-decoration: none; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35);">
        &#8635; Regenerate sitemap.xml
      </a>
    </div>
  </div>

  <?php if (!empty($msg)): ?>
    <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px;">
      <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($err)): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px;">
      <?= htmlspecialchars($err) ?>
    </div>
  <?php endif; ?>

  <!-- 1. DYNAMIC SITEMAP MANAGER -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; padding-bottom: 12px; margin-bottom: 18px; flex-wrap: wrap; gap: 10px;">
      <h3 style="margin: 0; font-size: 1.15rem; color: #38bdf8; font-weight: 800;">
        1. Dynamic XML Sitemap Manager
      </h3>
      <a href="/sitemap.xml" target="_blank" style="color: #38bdf8; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
        Open https://wetruenorthgroup.com/sitemap.xml &nearr;
      </a>
    </div>

    <p style="color: #cbd5e1; font-size: 0.88rem; line-height: 1.5; margin: 0 0 18px 0;">
      Your XML sitemap is automatically served dynamically and includes all indexable core pages, product categories, catalogue SKUs, and published technical articles with associated high-resolution WebP images.
    </p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px;">
      <div style="background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 14px;">
        <div style="font-size: 0.76rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">Core Static Pages</div>
        <div style="font-size: 1.2rem; font-weight: 800; color: #f8fafc; margin: 4px 0;">9 URLs</div>
        <div style="font-size: 0.78rem; color: #34d399;">Priority: 0.80 - 1.00</div>
      </div>
      <div style="background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 14px;">
        <div style="font-size: 0.76rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">Packaging Categories</div>
        <div style="font-size: 1.2rem; font-weight: 800; color: #f8fafc; margin: 4px 0;">8 Categories</div>
        <div style="font-size: 0.78rem; color: #34d399;">Priority: 0.85</div>
      </div>
      <div style="background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 14px;">
        <div style="font-size: 0.76rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">Products Catalog</div>
        <div style="font-size: 1.2rem; font-weight: 800; color: #f8fafc; margin: 4px 0;">295+ Containers</div>
        <div style="font-size: 0.78rem; color: #34d399;">Priority: 0.80</div>
      </div>
      <div style="background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 14px;">
        <div style="font-size: 0.76rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">Technical Articles</div>
        <div style="font-size: 1.2rem; font-weight: 800; color: #f8fafc; margin: 4px 0;">3 Guides</div>
        <div style="font-size: 0.78rem; color: #34d399;">Priority: 0.75</div>
      </div>
    </div>
  </div>

  <!-- 2. ROBOTS.TXT EDITOR -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; padding-bottom: 12px; margin-bottom: 18px; flex-wrap: wrap; gap: 10px;">
      <h3 style="margin: 0; font-size: 1.15rem; color: #38bdf8; font-weight: 800;">
        2. Robots.txt Crawler Directives Editor
      </h3>
      <a href="/robots.txt" target="_blank" style="color: #38bdf8; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
        Open /robots.txt &nearr;
      </a>
    </div>

    <form method="POST" action="/admin/seo_sitemap">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
      <input type="hidden" name="action" value="save_robots" />

      <label style="display: block; font-size: 0.84rem; font-weight: 700; color: #cbd5e1; margin-bottom: 8px;">
        Robots.txt Content (Served dynamically from database to search crawlers)
      </label>
      <textarea name="robots_txt_content" rows="10" style="width: 100%; box-sizing: border-box; padding: 14px 16px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #38bdf8; font-family: ui-monospace, Menlo, Monaco, Consolas, monospace; font-size: 0.9rem; line-height: 1.6; margin-bottom: 14px;"><?= htmlspecialchars($robotsContent) ?></textarea>

      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <span style="font-size: 0.78rem; color: #94a3b8;">
          Caution: Do not use <code>Disallow: /</code> unless intentionally deindexing the entire site.
        </span>
        <button type="submit" class="tn-btn-gold" style="padding: 10px 22px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">
          Save robots.txt
        </button>
      </div>
    </form>
  </div>

  <!-- 3. SCHEMA.ORG TOGGLES -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
    <div style="border-bottom: 1px solid #334155; padding-bottom: 12px; margin-bottom: 18px;">
      <h3 style="margin: 0; font-size: 1.15rem; color: #38bdf8; font-weight: 800;">
        3. Schema.org Rich Results &amp; Structured Data Toggles
      </h3>
      <p style="color: #94a3b8; font-size: 0.84rem; margin: 4px 0 0 0;">
        Enable or disable specific JSON-LD structured data graph types rendered on frontend templates.
      </p>
    </div>

    <form method="POST" action="/admin/seo_sitemap">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
      <input type="hidden" name="action" value="save_schemas" />

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <label style="display: flex; align-items: flex-start; gap: 12px; background: #0f172a; padding: 14px; border-radius: 8px; border: 1px solid #334155; cursor: pointer;">
          <input type="checkbox" name="schema_org_enabled" value="1" <?= !empty($settings['schema_org_enabled']) ? 'checked' : '' ?> style="margin-top: 3px;" />
          <div>
            <strong style="color: #f8fafc; font-size: 0.88rem; display: block;">Organization Schema</strong>
            <span style="color: #94a3b8; font-size: 0.78rem;">Outputs legal name, logo, contact points, and corporate address.</span>
          </div>
        </label>

        <label style="display: flex; align-items: flex-start; gap: 12px; background: #0f172a; padding: 14px; border-radius: 8px; border: 1px solid #334155; cursor: pointer;">
          <input type="checkbox" name="schema_website_enabled" value="1" <?= !empty($settings['schema_website_enabled']) ? 'checked' : '' ?> style="margin-top: 3px;" />
          <div>
            <strong style="color: #f8fafc; font-size: 0.88rem; display: block;">WebSite &amp; Sitelinks Searchbox</strong>
            <span style="color: #94a3b8; font-size: 0.78rem;">Enables Google Sitelinks Searchbox SearchAction on homepage.</span>
          </div>
        </label>

        <label style="display: flex; align-items: flex-start; gap: 12px; background: #0f172a; padding: 14px; border-radius: 8px; border: 1px solid #334155; cursor: pointer;">
          <input type="checkbox" name="schema_breadcrumbs_enabled" value="1" <?= !empty($settings['schema_breadcrumbs_enabled']) ? 'checked' : '' ?> style="margin-top: 3px;" />
          <div>
            <strong style="color: #f8fafc; font-size: 0.88rem; display: block;">BreadcrumbList Schema</strong>
            <span style="color: #94a3b8; font-size: 0.78rem;">Generates navigational path hierarchy for inner pages in SERPs.</span>
          </div>
        </label>

        <label style="display: flex; align-items: flex-start; gap: 12px; background: #0f172a; padding: 14px; border-radius: 8px; border: 1px solid #334155; cursor: pointer;">
          <input type="checkbox" name="schema_product_enabled" value="1" <?= !empty($settings['schema_product_enabled']) ? 'checked' : '' ?> style="margin-top: 3px;" />
          <div>
            <strong style="color: #f8fafc; font-size: 0.88rem; display: block;">Product &amp; Offer Schema</strong>
            <span style="color: #94a3b8; font-size: 0.78rem;">Outputs SKU, availability, price, currency, and brand metadata.</span>
          </div>
        </label>

        <label style="display: flex; align-items: flex-start; gap: 12px; background: #0f172a; padding: 14px; border-radius: 8px; border: 1px solid #334155; cursor: pointer;">
          <input type="checkbox" name="schema_article_enabled" value="1" <?= !empty($settings['schema_article_enabled']) ? 'checked' : '' ?> style="margin-top: 3px;" />
          <div>
            <strong style="color: #f8fafc; font-size: 0.88rem; display: block;">Article &amp; BlogPosting Schema</strong>
            <span style="color: #94a3b8; font-size: 0.78rem;">Enables rich article cards, author, and publisher metadata on articles.</span>
          </div>
        </label>

        <label style="display: flex; align-items: flex-start; gap: 12px; background: #0f172a; padding: 14px; border-radius: 8px; border: 1px solid #334155; cursor: pointer;">
          <input type="checkbox" name="schema_faq_enabled" value="1" <?= !empty($settings['schema_faq_enabled']) ? 'checked' : '' ?> style="margin-top: 3px;" />
          <div>
            <strong style="color: #f8fafc; font-size: 0.88rem; display: block;">FAQPage Schema</strong>
            <span style="color: #94a3b8; font-size: 0.78rem;">Renders expandable Q&amp;A rich snippets in Google SERPs from FAQ repeater.</span>
          </div>
        </label>

        <label style="display: flex; align-items: flex-start; gap: 12px; background: #0f172a; padding: 14px; border-radius: 8px; border: 1px solid #334155; cursor: pointer;">
          <input type="checkbox" name="schema_local_business_enabled" value="1" <?= !empty($settings['schema_local_business_enabled']) ? 'checked' : '' ?> style="margin-top: 3px;" />
          <div>
            <strong style="color: #f8fafc; font-size: 0.88rem; display: block;">LocalBusiness Schema</strong>
            <span style="color: #94a3b8; font-size: 0.78rem;">Outputs geographical coordinates, office hours, and local headquarters.</span>
          </div>
        </label>

      </div>

      <div style="text-align: right;">
        <button type="submit" class="tn-btn-gold" style="padding: 10px 22px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">
          Save Schema Toggles
        </button>
      </div>
    </form>
  </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>

