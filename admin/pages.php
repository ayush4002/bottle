<?php
// ==============================================================================
// TRUENORTH CMS — WORDPRESS STYLE "ALL PAGES" DIRECTORY
// Lists all website static pages with live Yoast SEO & Readability scores
// ==============================================================================

require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/PageModel.php';
require_once dirname(__DIR__) . '/app/Models/SeoModel.php';

checkAdminAuth();
PageModel::initStorage();
SeoModel::init();

$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';
$adminUser = $_SESSION['admin_user'] ?? 'admin';

// Handle Single Page Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete_page') {
    if (!verifyCsrfToken($_GET['csrf_token'] ?? '')) {
        $err = "Invalid security token.";
    } else {
        $delSlug = trim($_GET['slug'] ?? '');
        if ($delSlug === '/' || $delSlug === '/home') {
            $err = "The Home page cannot be deleted.";
        } else {
            PageModel::deletePage($delSlug, $adminUser);
            $msg = "Page '{$delSlug}' and its SEO records deleted successfully.";
        }
    }
}

// Handle Bulk Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bulk_action') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $err = "Invalid security token.";
    } else {
        $bulkAct = $_POST['bulk_action_type'] ?? '';
        $selectedSlugs = $_POST['selected_slugs'] ?? [];
        if (empty($selectedSlugs)) {
            $err = "No pages were selected for bulk action.";
        } else {
            $count = 0;
            foreach ($selectedSlugs as $slug) {
                if ($bulkAct === 'delete_page') {
                    if ($slug !== '/' && $slug !== '/home') {
                        PageModel::deletePage($slug, $adminUser);
                        $count++;
                    }
                    continue;
                }
                $curSeo = SeoModel::getPageSeo($slug) ?: ['url_slug' => $slug];
                if ($bulkAct === 'set_index') {
                    $curSeo['robots_index'] = 1;
                    SeoModel::savePageSeo($curSeo, $adminUser);
                    $count++;
                } elseif ($bulkAct === 'set_noindex') {
                    $curSeo['robots_index'] = 0;
                    SeoModel::savePageSeo($curSeo, $adminUser);
                    $count++;
                } elseif ($bulkAct === 'auto_templates') {
                    $applied = SeoModel::applyTemplate('static', [
                        'page_name' => ucwords(trim(str_replace(['-', '_'], ' ', $slug), '/'))
                    ]);
                    if (empty($curSeo['meta_title']) && !empty($applied['title'])) $curSeo['meta_title'] = $applied['title'];
                    if (empty($curSeo['meta_description']) && !empty($applied['description'])) $curSeo['meta_description'] = $applied['description'];
                    SeoModel::savePageSeo($curSeo, $adminUser);
                    $count++;
                }
            }
            $msg = "Bulk action '{$bulkAct}' applied successfully to {$count} pages.";
        }
    }
}

$search = trim($_GET['search'] ?? '');
$pagesList = PageModel::getAllPages($search);
$totalPagesCount = count($pagesList);
$csrfToken = generateCsrfToken();
?>

<div style="max-width: 1360px; margin: 0 auto;">

  <!-- HEADER & ACTIONS -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; flex-wrap: wrap; gap: 14px;">
    <div>
      <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 0.88rem; color: #94a3b8;">
        <a href="/admin" style="color: #94a3b8; text-decoration: none;">Dashboard</a>
        <span>/</span>
        <span style="color: #38bdf8; font-weight: 600;">Pages</span>
      </div>
      <div style="display: flex; align-items: center; gap: 14px;">
        <h1 style="font-size: 1.8rem; font-weight: 800; color: #f8fafc; margin: 0;">
          Pages
        </h1>
        <a href="/admin/edit_page?slug=/new-page" class="tn-btn-outline" style="padding: 6px 14px; font-size: 0.82rem; border-radius: 6px; color: #38bdf8; border: 1px solid #0284c7; text-decoration: none; font-weight: 600;">
          Add New
        </a>
      </div>
    </div>
    
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
      <a href="/admin/seo_pages" class="tn-btn-outline" style="padding: 9px 16px; border-radius: 8px; font-weight: 600; font-size: 0.86rem; background: #1e293b; color: #38bdf8; border: 1px solid #334155; text-decoration: none;">
        SEO Overview
      </a>
      <a href="/admin/seo_settings" class="tn-btn-outline" style="padding: 9px 16px; border-radius: 8px; font-weight: 600; font-size: 0.86rem; background: #1e293b; color: #cbd5e1; border: 1px solid #334155; text-decoration: none;">
        Search Appearance
      </a>
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

  <!-- WORDPRESS FILTER STRIP & SEARCH -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
    <div style="font-size: 0.88rem; color: #94a3b8; display: flex; align-items: center; gap: 8px;">
      <span style="font-weight: 700; color: #f8fafc;">All (<?= $totalPagesCount ?>)</span>
      <span>|</span>
      <span style="color: #38bdf8;">Published (<?= $totalPagesCount ?>)</span>
    </div>

    <!-- Search Form -->
    <form method="GET" action="/admin/pages" style="display: flex; gap: 8px; align-items: center;">
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search pages..." style="padding: 8px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.85rem; width: 220px;" />
      <button type="submit" style="padding: 8px 14px; background: #334155; color: #f8fafc; border: none; border-radius: 6px; font-size: 0.82rem; font-weight: 600; cursor: pointer;">
        Search Pages
      </button>
      <?php if (!empty($search)): ?>
        <a href="/admin/pages" style="color: #94a3b8; font-size: 0.82rem; text-decoration: none; padding-left: 4px;">Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- BULK ACTIONS FORM & TABLE -->
  <form id="pagesBulkForm" method="POST" action="/admin/pages">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
    <input type="hidden" name="action" value="bulk_action" />

    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; overflow: hidden; margin-bottom: 24px;">
      
      <!-- Table Top Bulk Action Bar -->
      <div style="padding: 10px 18px; background: #0f172a; border-bottom: 1px solid #334155; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px;">
          <input type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAll(this)" style="cursor: pointer; width: 16px; height: 16px;" />
          <span style="font-size: 0.82rem; color: #94a3b8;">Select All</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
          <select name="bulk_action_type" style="padding: 6px 10px; background: #1e293b; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.82rem;">
            <option value="">Bulk Actions...</option>
            <option value="set_index">Set to Index (Google Visible)</option>
            <option value="set_noindex">Set to Noindex (Hide from Google)</option>
            <option value="auto_templates">Auto-Fill Empty Titles &amp; Descriptions</option>
            <option value="delete_page" style="color: #f87171;">Delete Selected Pages</option>
          </select>
          <button type="submit" onclick="return confirm('Apply this bulk action to selected pages?');" style="padding: 6px 12px; background: #334155; color: #f8fafc; border: none; border-radius: 6px; font-weight: 600; font-size: 0.82rem; cursor: pointer;">
            Apply
          </button>
        </div>
      </div>

      <!-- Table Body -->
      <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.86rem;">
          <thead>
            <tr style="color: #94a3b8; border-bottom: 1px solid #334155; background: #162032;">
              <th style="padding: 12px 16px; width: 36px;"></th>
              <th style="padding: 12px 16px;">Title</th>
              <th style="padding: 12px 16px;">URL Route</th>
              <th style="padding: 12px 16px;">Template</th>
              <th style="padding: 12px 16px;">Focus Keyphrase</th>
              <th style="padding: 12px 16px; text-align: center;">Yoast SEO</th>
              <th style="padding: 12px 16px; text-align: center;">Readability</th>
              <th style="padding: 12px 16px;">Date</th>
              <th style="padding: 12px 16px; text-align: right;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($pagesList)): ?>
              <tr>
                <td colspan="9" style="padding: 30px; text-align: center; color: #94a3b8;">
                  No pages found matching your search.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($pagesList as $p): ?>
                <?php
                  $score = (int)($p['seo_score'] ?? 80);
                  $dotColor = ($score >= 80) ? '#22c55e' : (($score >= 50) ? '#eab308' : '#ef4444');
                  $scoreLabel = ($score >= 80) ? 'Good' : (($score >= 50) ? 'OK' : 'Needs work');
                ?>
                <tr style="border-bottom: 1px solid #334155; transition: background 0.15s;" onmouseover="this.style.background='#131e30'" onmouseout="this.style.background='transparent'">
                  <td style="padding: 12px 16px;">
                    <input type="checkbox" name="selected_slugs[]" value="<?= htmlspecialchars($p['slug']) ?>" class="page-checkbox" style="cursor: pointer;" />
                  </td>
                  <td style="padding: 12px 16px; font-weight: 700;">
                    <a href="/admin/edit_page?slug=<?= urlencode($p['slug']) ?>" style="color: #f8fafc; text-decoration: none; font-size: 0.92rem; display: block;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='#f8fafc'">
                      <?= htmlspecialchars($p['title']) ?>
                    </a>
                    <!-- Quick Row Actions -->
                    <div style="font-size: 0.74rem; display: flex; gap: 8px; margin-top: 4px; color: #64748b;">
                      <a href="/admin/edit_page?slug=<?= urlencode($p['slug']) ?>" style="color: #38bdf8; text-decoration: none;">Edit</a>
                      <span>|</span>
                      <a href="<?= htmlspecialchars($p['slug']) ?>" target="_blank" rel="noopener" style="color: #94a3b8; text-decoration: none;">View</a>
                      <?php if (($p['slug'] ?? '') !== '/'): ?>
                        <span>|</span>
                        <a href="/admin/pages?action=delete_page&slug=<?= urlencode($p['slug']) ?>&csrf_token=<?= $csrfToken ?>" onclick="return confirm('Permanently delete page \'<?= addslashes($p['title']) ?>\' and its SEO records?');" style="color: #f87171; text-decoration: none;">Trash</a>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td style="padding: 12px 16px; font-family: monospace; font-size: 0.8rem; color: #38bdf8;">
                    <?= htmlspecialchars($p['slug']) ?>
                  </td>
                  <td style="padding: 12px 16px;">
                    <span style="background: rgba(56, 189, 248, 0.12); color: #38bdf8; padding: 2px 8px; border-radius: 4px; font-size: 0.74rem; font-weight: 600; text-transform: uppercase;">
                      <?= htmlspecialchars($p['template'] ?? 'default') ?>
                    </span>
                  </td>
                  <td style="padding: 12px 16px; font-size: 0.82rem; color: #cbd5e1;">
                    <?= htmlspecialchars($p['focus_keyword'] ?: '—') ?>
                  </td>
                  <!-- Yoast SEO Score Traffic Light -->
                  <td style="padding: 12px 16px; text-align: center;">
                    <div style="display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 12px; background: rgba(15, 23, 42, 0.6); border: 1px solid #334155;">
                      <span style="width: 9px; height: 9px; border-radius: 50%; background: <?= $dotColor ?>;"></span>
                      <span style="font-size: 0.78rem; font-weight: 700; color: #f8fafc;"><?= $score ?>/100</span>
                    </div>
                  </td>
                  <!-- Readability Traffic Light -->
                  <td style="padding: 12px 16px; text-align: center;">
                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                      <span style="width: 8px; height: 8px; border-radius: 50%; background: #38bdf8;"></span>
                      <span style="font-size: 0.78rem; font-weight: 600; color: #cbd5e1;"><?= htmlspecialchars($p['readability'] ?? 'Good') ?></span>
                    </div>
                  </td>
                  <td style="padding: 12px 16px; font-size: 0.8rem; color: #94a3b8;">
                    <?= date('M d, Y', strtotime($p['updated_at'] ?? 'now')) ?>
                  </td>
                  <td style="padding: 12px 16px; text-align: right;">
                    <a href="/admin/edit_page?slug=<?= urlencode($p['slug']) ?>" class="tn-btn-gold" style="padding: 6px 14px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                      Edit Page &rarr;
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </form>

</div>

<script>
function toggleSelectAll(master) {
  var boxes = document.querySelectorAll('.page-checkbox');
  boxes.forEach(function(b) { b.checked = master.checked; });
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

