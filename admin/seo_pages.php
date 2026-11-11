<?php
require_once dirname(__DIR__) . '/includes/upload_guard.php';
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/SeoModel.php';
require_once dirname(__DIR__) . '/app/Models/ProductModel.php';
require_once dirname(__DIR__) . '/app/Models/BlogModel.php';

checkAdminAuth();
SeoModel::init();

$msg = '';
$err = '';
$adminUser = $_SESSION['admin_user'] ?? 'admin';

// Handle CSV Export
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=truenorth_seo_metadata_' . date('Y-m-d') . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['URL Slug', 'Page Type', 'Meta Title', 'Meta Description', 'Focus Keyword', 'Robots Index']);

    $res = SeoModel::getAllPagesList('', 'all', 1, 2000);
    foreach ($res['items'] as $row) {
        fputcsv($out, [
            $row['url_slug'] ?? '',
            $row['page_type'] ?? '',
            $row['meta_title'] ?? '',
            $row['meta_description'] ?? '',
            $row['focus_keyword'] ?? '',
            !empty($row['robots_index']) ? '1' : '0'
        ]);
    }
    fclose($out);
    exit();
}

// Handle CSV Import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import_csv') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $err = "Invalid CSRF security token.";
    } elseif (!empty($_FILES['csv_file']['tmp_name'])) {
        $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
        $header = fgetcsv($handle);
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 3 && !empty($row[0])) {
                $slug = '/' . ltrim(trim($row[0]), '/');
                $title = trim($row[2] ?? '');
                $desc = trim($row[3] ?? ($row[1] ?? ''));
                $kw = trim($row[4] ?? '');
                SeoModel::savePageSeo([
                    'url_slug' => $slug,
                    'meta_title' => $title,
                    'meta_description' => $desc,
                    'focus_keyword' => $kw,
                    'page_type' => 'page'
                ], $adminUser);
                $count++;
            }
        }
        fclose($handle);
        $msg = "Successfully imported and updated SEO metadata for {$count} pages.";
    }
}

// Handle Single Page Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_page') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $err = "Invalid security token.";
    } else {
        $slug = trim($_POST['url_slug'] ?? '');
        if (empty($slug)) {
            $err = "URL Slug is required.";
        } else {
            // Validate custom JSON-LD if provided
            $customJson = trim($_POST['custom_schema_json'] ?? '');
            if (!empty($customJson)) {
                $decoded = json_decode($customJson, true);
                if ($decoded === null) {
                    $err = "Custom Schema is not valid JSON. Please correct syntax before saving.";
                }
            }

            if (empty($err)) {
                $saveData = [
                    'url_slug' => $slug,
                    'old_url_slug' => trim($_POST['old_url_slug'] ?? ''),
                    'page_type' => $_POST['page_type'] ?? 'page',
                    'page_ref_id' => $_POST['page_ref_id'] ?? null,
                    'meta_title' => trim($_POST['meta_title'] ?? ''),
                    'meta_description' => trim($_POST['meta_description'] ?? ''),
                    'meta_keywords' => trim($_POST['meta_keywords'] ?? ''),
                    'focus_keyword' => trim($_POST['focus_keyword'] ?? ''),
                    'h1_override' => trim($_POST['h1_override'] ?? ''),
                    'canonical_url' => trim($_POST['canonical_url'] ?? ''),
                    'robots_index' => isset($_POST['robots_index']) ? 1 : 0,
                    'robots_follow' => isset($_POST['robots_follow']) ? 1 : 0,
                    'og_title' => trim($_POST['og_title'] ?? ''),
                    'og_description' => trim($_POST['og_description'] ?? ''),
                    'og_image' => trim($_POST['og_image'] ?? ''),
                    'schema_type' => trim($_POST['schema_type'] ?? 'WebPage'),
                    'custom_schema_json' => $customJson,
                    'cornerstone_content' => !empty($_POST['cornerstone_content']) ? 1 : 0,
                    'breadcrumbs_title' => trim($_POST['breadcrumbs_title'] ?? ''),
                    'twitter_title' => trim($_POST['twitter_title'] ?? ''),
                    'twitter_description' => trim($_POST['twitter_description'] ?? ''),
                    'twitter_image' => trim($_POST['twitter_image'] ?? ''),
                    'twitter_card' => trim($_POST['twitter_card'] ?? 'summary_large_image'),
                    'advanced_robots' => trim($_POST['advanced_robots'] ?? '')
                ];

                // Handle OG image upload
                if (!empty($_FILES['og_image_file']['name'])) {
                    $uploadDir = dirname(__DIR__) . '/public/uploads/';
                    if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
                    $upExt = tn_upload_safe_image_ext($_FILES['og_image_file']['tmp_name'], $_FILES['og_image_file']['name']);
                    $fName = 'page_og_' . time() . '.' . $upExt;
                    if ($upExt !== false && move_uploaded_file($_FILES['og_image_file']['tmp_name'], $uploadDir . $fName)) {
                        $saveData['og_image'] = '/uploads/' . $fName;
                    }
                }

                SeoModel::savePageSeo($saveData, $adminUser);

                // Save FAQs if submitted
                if (isset($_POST['faq_q']) && is_array($_POST['faq_q'])) {
                    $faqs = [];
                    foreach ($_POST['faq_q'] as $idx => $q) {
                        $a = $_POST['faq_a'][$idx] ?? '';
                        if (!empty(trim($q)) && !empty(trim($a))) {
                            $faqs[] = ['question' => trim($q), 'answer' => trim($a)];
                        }
                    }
                    SeoModel::saveFaqs($saveData['page_type'], $saveData['url_slug'], $faqs, $adminUser);
                }

                $msg = "SEO metadata for '{$slug}' successfully saved!";
            }
        }
    }
}

// Handle Bulk Action
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
            foreach ($selectedSlugs as $sSlug) {
                $cur = SeoModel::getPageSeo($sSlug) ?: ['url_slug' => $sSlug];
                if ($bulkAct === 'set_index') {
                    $cur['robots_index'] = 1;
                    SeoModel::savePageSeo($cur, $adminUser);
                    $count++;
                } elseif ($bulkAct === 'set_noindex') {
                    $cur['robots_index'] = 0;
                    SeoModel::savePageSeo($cur, $adminUser);
                    $count++;
                } elseif ($bulkAct === 'auto_templates') {
                    // Auto-fill missing from templates
                    $type = $cur['page_type'] ?? 'page';
                    $tplType = ($type === 'product') ? 'product' : (($type === 'category') ? 'category' : 'static');
                    $applied = SeoModel::applyTemplate($tplType, [
                        'page_name' => ucwords(trim(str_replace(['-', '_'], ' ', $sSlug), '/')),
                        'product_name' => ucwords(trim(str_replace(['-', '_'], ' ', $sSlug), '/')),
                        'category_name' => ucwords(trim(str_replace(['-', '_'], ' ', $sSlug), '/'))
                    ]);
                    if (empty($cur['meta_title']) && !empty($applied['title'])) $cur['meta_title'] = $applied['title'];
                    if (empty($cur['meta_description']) && !empty($applied['description'])) $cur['meta_description'] = $applied['description'];
                    SeoModel::savePageSeo($cur, $adminUser);
                    $count++;
                }
            }
            $msg = "Bulk action '{$bulkAct}' completed on {$count} pages.";
        }
    }
}

// Query parameters
$search = trim($_GET['search'] ?? '');
$typeFilter = $_GET['type'] ?? 'all';
$page = max(1, (int)($_GET['p'] ?? 1));
$perPage = 15;

$result = SeoModel::getAllPagesList($search, $typeFilter, $page, $perPage);
$pagesList = $result['items'];
$totalItems = $result['total'];
$totalPages = $result['pages'];

$csrfToken = generateCsrfToken();
?>

<div style="max-width: 1320px; margin: 0 auto;">

  <!-- PAGE HEADER & ACTION BUTTONS -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
      <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 0.88rem; color: #94a3b8;">
        <a href="/admin/seo" style="color: #94a3b8; text-decoration: none;">&larr; SEO Overview</a>
        <span>/</span>
        <span style="color: #38bdf8; font-weight: 600;">Page SEO Manager</span>
      </div>
      <h1 style="font-size: 1.8rem; font-weight: 800; color: #f8fafc; margin: 0;">
        Page SEO &amp; Rich Snippets Manager
      </h1>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <a href="/admin/seo_pages?action=export_csv" class="tn-btn-outline" style="padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: 0.86rem; background: #1e293b; color: #38bdf8; border: 1px solid #334155; text-decoration: none;">
        Export CSV
      </a>
      <button type="button" onclick="document.getElementById('csvModal').style.display='flex';" class="tn-btn-outline" style="padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: 0.86rem; background: #1e293b; color: #cbd5e1; border: 1px solid #334155; cursor: pointer;">
        Import CSV
      </button>
      <button type="button" onclick="openPageEditModal('', 'page', '', '', '', '', '', 1, 1)" class="tn-btn-gold" style="padding: 10px 18px; border: none; border-radius: 8px; font-weight: 700; font-size: 0.88rem; cursor: pointer; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35);">
        + Add Page Override
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

  <!-- SEARCH & FILTER TOOLBAR -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 16px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
    <form method="GET" action="/admin/seo_pages" style="display: flex; gap: 10px; flex-wrap: wrap; flex: 1;">
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by URL, title, description, or keyword..." style="flex: 1; min-width: 250px; padding: 10px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" />
      <select name="type" style="padding: 10px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;">
        <option value="all" <?= $typeFilter === 'all' ? 'selected' : '' ?>>All Page Types</option>
        <option value="home" <?= $typeFilter === 'home' ? 'selected' : '' ?>>Homepage</option>
        <option value="page" <?= $typeFilter === 'page' ? 'selected' : '' ?>>Static Pages</option>
        <option value="category" <?= $typeFilter === 'category' ? 'selected' : '' ?>>Categories</option>
        <option value="product" <?= $typeFilter === 'product' ? 'selected' : '' ?>>Products</option>
        <option value="blog" <?= $typeFilter === 'blog' ? 'selected' : '' ?>>Blog Posts</option>
      </select>
      <button type="submit" class="tn-btn-outline" style="padding: 10px 18px; background: #0f172a; color: #38bdf8; border: 1px solid #0284c7; border-radius: 8px; font-weight: 600; cursor: pointer;">
        Filter
      </button>
      <?php if (!empty($search) || $typeFilter !== 'all'): ?>
        <a href="/admin/seo_pages" style="padding: 10px 14px; color: #94a3b8; text-decoration: none; font-size: 0.86rem; display: flex; align-items: center;">Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- BULK ACTIONS FORM & TABLE -->
  <form id="bulkForm" method="POST" action="/admin/seo_pages">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
    <input type="hidden" name="action" value="bulk_action" />

    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; overflow: hidden; margin-bottom: 20px;">
      
      <!-- Table Top Bulk Action Bar -->
      <div style="padding: 12px 18px; background: #0f172a; border-bottom: 1px solid #334155; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px;">
          <input type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAll(this)" style="cursor: pointer; width: 16px; height: 16px;" />
          <span style="font-size: 0.82rem; color: #94a3b8;">Select All on Page</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
          <select name="bulk_action_type" id="bulkActionType" style="padding: 7px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.82rem;">
            <option value="">Bulk Actions...</option>
            <option value="set_index">Set to Index (Google Visible)</option>
            <option value="set_noindex">Set to Noindex (Hide from Google)</option>
            <option value="auto_templates">Auto-Fill Empty Titles &amp; Descriptions from Templates</option>
          </select>
          <button type="submit" onclick="return confirm('Apply this bulk action to selected items?');" style="padding: 7px 14px; background: #334155; color: #f8fafc; border: none; border-radius: 6px; font-weight: 600; font-size: 0.82rem; cursor: pointer;">
            Apply
          </button>
        </div>
      </div>

      <!-- Table Body -->
      <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.86rem;">
          <thead>
            <tr style="color: #94a3b8; border-bottom: 1px solid #334155; background: #162032;">
              <th style="padding: 12px 16px; width: 40px;"></th>
              <th style="padding: 12px 16px;">URL Route / Slug</th>
              <th style="padding: 12px 16px;">Page Type</th>
              <th style="padding: 12px 16px;">Meta Title</th>
              <th style="padding: 12px 16px;">Meta Description</th>
              <th style="padding: 12px 16px; text-align: center;">Index</th>
              <th style="padding: 12px 16px; text-align: center;">SEO Score</th>
              <th style="padding: 12px 16px; text-align: right;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($pagesList)): ?>
              <tr>
                <td colspan="8" style="padding: 30px; text-align: center; color: #94a3b8;">
                  No pages match your search criteria.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($pagesList as $p): ?>
                <?php
                  $tLen = mb_strlen($p['meta_title'] ?? '');
                  $dLen = mb_strlen($p['meta_description'] ?? '');
                  $scoreData = SeoModel::calculateSeoScore($p['meta_title'] ?? '', $p['meta_description'] ?? '', $p['h1_override'] ?? '', $p['focus_keyword'] ?? '', $p['url_slug'] ?? '');
                  $score = $scoreData['score'];
                  $sColor = ($score >= 80) ? '#34d399' : (($score >= 50) ? '#fbbf24' : '#f87171');
                ?>
                <tr style="border-bottom: 1px solid #334155;">
                  <td style="padding: 12px 16px;">
                    <input type="checkbox" name="selected_slugs[]" value="<?= htmlspecialchars($p['url_slug']) ?>" class="page-checkbox" style="cursor: pointer;" />
                  </td>
                  <td style="padding: 12px 16px; font-weight: 600;">
                    <a href="<?= htmlspecialchars($p['url_slug']) ?>" target="_blank" style="color: #38bdf8; text-decoration: none;" title="Open live URL">
                      <?= htmlspecialchars($p['url_slug']) ?> &nearr;
                    </a>
                    <?php if (!empty($p['focus_keyword'])): ?>
                      <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 2px;">
                        KW: <span style="color: #cbd5e1;"><?= htmlspecialchars($p['focus_keyword']) ?></span>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td style="padding: 12px 16px;">
                    <span style="background: rgba(56, 189, 248, 0.12); color: #38bdf8; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">
                      <?= htmlspecialchars($p['page_type'] ?? 'page') ?>
                    </span>
                  </td>
                  <td style="padding: 12px 16px; max-width: 250px;">
                    <div style="color: #f8fafc; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                      <?= htmlspecialchars($p['meta_title'] ?: '(Not specified - using template)') ?>
                    </div>
                    <div style="font-size: 0.72rem; color: <?= ($tLen >= 45 && $tLen <= 65) ? '#34d399' : '#fbbf24' ?>;">
                      <?= $tLen ?> / 60 chars
                    </div>
                  </td>
                  <td style="padding: 12px 16px; max-width: 280px;">
                    <div style="color: #cbd5e1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 0.82rem;">
                      <?= htmlspecialchars($p['meta_description'] ?: '(Not specified - using template)') ?>
                    </div>
                    <div style="font-size: 0.72rem; color: <?= ($dLen >= 120 && $dLen <= 165) ? '#34d399' : '#fbbf24' ?>;">
                      <?= $dLen ?> / 160 chars
                    </div>
                  </td>
                  <td style="padding: 12px 16px; text-align: center;">
                    <?php if (!empty($p['robots_index'])): ?>
                      <span style="color: #34d399; font-weight: 700; font-size: 0.78rem;">Index</span>
                    <?php else: ?>
                      <span style="color: #f87171; font-weight: 700; font-size: 0.78rem; background: rgba(239, 68, 68, 0.15); padding: 2px 6px; border-radius: 4px;">NoIndex</span>
                    <?php endif; ?>
                  </td>
                  <td style="padding: 12px 16px; text-align: center;">
                    <span style="background: <?= $score >= 80 ? 'rgba(52, 211, 153, 0.15)' : ($score >= 50 ? 'rgba(251, 191, 36, 0.15)' : 'rgba(239, 68, 68, 0.15)') ?>; color: <?= $sColor ?>; font-weight: 800; font-size: 0.8rem; padding: 3px 8px; border-radius: 6px;">
                      <?= $score ?>%
                    </span>
                  </td>
                  <td style="padding: 12px 16px; text-align: right;">
                    <button type="button" onclick="editPageItem(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8') ?>)" style="padding: 6px 12px; background: #334155; color: #fff; border: none; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer;">
                      Edit SEO
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <div style="padding: 12px 18px; background: #0f172a; border-top: 1px solid #334155; display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 0.82rem; color: #94a3b8;">
            Showing page <?= $page ?> of <?= $totalPages ?> (<?= $totalItems ?> items)
          </span>
          <div style="display: flex; gap: 6px;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <a href="/admin/seo_pages?p=<?= $i ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($typeFilter) ?>" style="padding: 5px 10px; background: <?= $i === $page ? 'var(--color-gold, #b8892e)' : '#1e293b' ?>; color: #fff; text-decoration: none; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">
                <?= $i ?>
              </a>
            <?php endfor; ?>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </form>

</div>

<!-- EDIT PAGE SEO MODAL (WORDPRESS YOAST & RANK MATH STYLE) -->
<div id="pageEditModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); z-index: 9999; align-items: center; justify-content: center; padding: 20px; overflow-y: auto;">
  <div style="background: #0f172a; border: 1px solid #38bdf8; border-radius: 16px; max-width: 960px; width: 100%; max-height: 92vh; overflow-y: auto; box-sizing: border-box; box-shadow: 0 25px 50px rgba(0,0,0,0.7); overflow-x: hidden;">
    
    <!-- MODAL HEADER -->
    <div style="padding: 16px 24px; background: #1e293b; border-bottom: 1px solid #334155; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; position: sticky; top: 0; z-index: 10;">
      <div style="display: flex; align-items: center; gap: 10px;">
        <div style="width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, #0284c7, #0369a1); display: flex; align-items: center; justify-content: center; color: #fff;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><path d="m8 11 2 2 4-4"/></svg>
        </div>
        <div>
          <h3 id="modalHeadingTitle" style="margin: 0; font-size: 1.1rem; color: #f8fafc; font-weight: 800;">
            WordPress SEO Suite &mdash; Edit Page Metadata
          </h3>
          <div style="font-size: 0.74rem; color: #94a3b8;">Yoast &amp; Rank Math style live search snippet, readability &amp; schema editor</div>
        </div>
      </div>

      <!-- TRAFFIC LIGHT SCORE BADGES -->
      <div style="display: flex; align-items: center; gap: 10px;">
        <div id="mScorePill" style="display: flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 20px; background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); font-size: 0.78rem; font-weight: 700; color: #4ade80;">
          <span id="mTrafficDot" style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e;"></span>
          <span>SEO: <strong id="mScoreText">85/100 (Good)</strong></span>
        </div>
        <div id="mReadabilityPill" style="display: flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 20px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.4); font-size: 0.78rem; font-weight: 700; color: #38bdf8;">
          <span id="mReadDot" style="width: 8px; height: 8px; border-radius: 50%; background: #38bdf8;"></span>
          <span>Readability: <strong id="mReadText">Good</strong></span>
        </div>
        <button type="button" onclick="closeEditModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 1.4rem; cursor: pointer; line-height: 1; padding: 4px 8px; border-radius: 6px;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94a3b8'">&times;</button>
      </div>
    </div>

    <!-- WORDPRESS 5-TAB NAVIGATION -->
    <div style="display: flex; background: #0f172a; border-bottom: 1px solid #334155; padding: 0 16px; overflow-x: auto;">
      <button type="button" class="wp-modal-tab active" onclick="switchModalTab('seo')" id="mtab-btn-seo" style="display: flex; align-items: center; gap: 8px; padding: 12px 18px; border: none; background: transparent; color: #38bdf8; font-weight: 700; font-size: 0.85rem; border-bottom: 2px solid #38bdf8; cursor: pointer;">
        <span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e;" id="mtab-dot-seo"></span>
        <span>SEO</span>
      </button>
      <button type="button" class="wp-modal-tab" onclick="switchModalTab('readability')" id="mtab-btn-readability" style="display: flex; align-items: center; gap: 8px; padding: 12px 18px; border: none; background: transparent; color: #94a3b8; font-weight: 600; font-size: 0.85rem; border-bottom: 2px solid transparent; cursor: pointer;">
        <span style="width: 8px; height: 8px; border-radius: 50%; background: #38bdf8;" id="mtab-dot-readability"></span>
        <span>Readability</span>
      </button>
      <button type="button" class="wp-modal-tab" onclick="switchModalTab('schema')" id="mtab-btn-schema" style="display: flex; align-items: center; gap: 8px; padding: 12px 18px; border: none; background: transparent; color: #94a3b8; font-weight: 600; font-size: 0.85rem; border-bottom: 2px solid transparent; cursor: pointer;">
        <span>Schema</span>
      </button>
      <button type="button" class="wp-modal-tab" onclick="switchModalTab('social')" id="mtab-btn-social" style="display: flex; align-items: center; gap: 8px; padding: 12px 18px; border: none; background: transparent; color: #94a3b8; font-weight: 600; font-size: 0.85rem; border-bottom: 2px solid transparent; cursor: pointer;">
        <span>Social</span>
      </button>
      <button type="button" class="wp-modal-tab" onclick="switchModalTab('advanced')" id="mtab-btn-advanced" style="display: flex; align-items: center; gap: 8px; padding: 12px 18px; border: none; background: transparent; color: #94a3b8; font-weight: 600; font-size: 0.85rem; border-bottom: 2px solid transparent; cursor: pointer;">
        <span>Advanced</span>
      </button>
    </div>

    <!-- FORM START -->
    <form method="POST" action="/admin/seo_pages" enctype="multipart/form-data" id="pageEditForm" style="margin: 0; padding: 24px;">
      <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>" />
      <input type="hidden" name="action" value="save_page" />
      <input type="hidden" name="old_url_slug" id="mOldSlug" value="" />
      <input type="hidden" name="page_ref_id" id="mPageRefId" value="" />

      <!-- ========================================== -->
      <!-- TAB 1: SEO -->
      <!-- ========================================== -->
      <div id="msec-seo" class="wp-modal-section">
        
        <!-- FOCUS KEYPHRASE (YOAST CORE) -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 18px; margin-bottom: 20px;">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
            <label style="font-size: 0.85rem; font-weight: 700; color: #f8fafc; display: flex; align-items: center; gap: 8px;">
              <span>Focus Keyphrase</span>
              <span style="font-size: 0.7rem; color: #38bdf8; font-weight: normal;">(Yoast / Rank Math scoring target)</span>
            </label>
            <span style="font-size: 0.72rem; color: #94a3b8;">Single word or key search term</span>
          </div>
          <input type="text" id="mFocusKeyword" name="focus_keyword" placeholder="e.g. pharmaceutical bottles, cosmetic dispensing pumps" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" oninput="runModalAnalysis()" />
        </div>

        <!-- GOOGLE SERP LIVE PREVIEW (MOBILE VS DESKTOP) -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 18px; margin-bottom: 20px;">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
            <div style="font-size: 0.82rem; font-weight: 700; color: #cbd5e1; text-transform: uppercase; letter-spacing: 0.05em;">
              Google Search Result Snippet Preview
            </div>
            <div style="display: flex; background: #0f172a; border: 1px solid #475569; border-radius: 6px; padding: 2px;">
              <button type="button" id="mbtn-preview-mobile" onclick="toggleModalSerpDevice('mobile')" style="padding: 4px 10px; font-size: 0.74rem; font-weight: 700; border: none; border-radius: 4px; background: #38bdf8; color: #0f172a; cursor: pointer;">
                Mobile
              </button>
              <button type="button" id="mbtn-preview-desktop" onclick="toggleModalSerpDevice('desktop')" style="padding: 4px 10px; font-size: 0.74rem; font-weight: 700; border: none; border-radius: 4px; background: transparent; color: #94a3b8; cursor: pointer;">
                Desktop
              </button>
            </div>
          </div>

          <!-- SNIPPET CARD -->
          <div id="mserp-container" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; font-family: Roboto, Arial, sans-serif; max-width: 600px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
              <img src="/logo_svg.svg" style="width: 18px; height: 18px; border-radius: 50%;" alt="Favicon" />
              <div style="display: flex; flex-direction: column; line-height: 1.2;">
                <span style="font-size: 0.8rem; color: #202124; font-weight: 500;">TrueNorth Group</span>
                <span id="mserp-url-display" style="font-size: 0.72rem; color: #4d5156;">https://wetruenorthgroup.com &rsaquo; page</span>
              </div>
            </div>
            <div id="mserp-title-display" style="font-size: 1.25rem; color: #1a0dab; font-weight: 400; line-height: 1.3; cursor: pointer; margin-bottom: 4px; word-break: break-word;">
              Page Title &mdash; TrueNorth Group
            </div>
            <div id="mserp-desc-display" style="font-size: 0.86rem; color: #4d5156; line-height: 1.45; word-break: break-word;">
              Meta description will appear here dynamically as you write...
            </div>
          </div>
        </div>

        <!-- SEO TITLE WITH INSERT VARIABLE TAGS -->
        <div style="margin-bottom: 18px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
            <label style="font-size: 0.84rem; font-weight: 700; color: #cbd5e1;">
              SEO Title
            </label>
            <span id="mTitleCounter" style="font-size: 0.76rem; font-weight: 700; color: #34d399;">0 / 60 chars</span>
          </div>

          <!-- TAG BUTTONS (YOAST STYLE) -->
          <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 6px;">
            <button type="button" onclick="insertModalTag('mTitle', '{title}')" style="padding: 2px 8px; font-size: 0.72rem; background: #1e293b; border: 1px solid #475569; color: #38bdf8; border-radius: 4px; cursor: pointer;">+ Title</button>
            <button type="button" onclick="insertModalTag('mTitle', '{separator}')" style="padding: 2px 8px; font-size: 0.72rem; background: #1e293b; border: 1px solid #475569; color: #38bdf8; border-radius: 4px; cursor: pointer;">+ Sep</button>
            <button type="button" onclick="insertModalTag('mTitle', '{site_name}')" style="padding: 2px 8px; font-size: 0.72rem; background: #1e293b; border: 1px solid #475569; color: #38bdf8; border-radius: 4px; cursor: pointer;">+ Site title</button>
            <button type="button" onclick="insertModalTag('mTitle', '{primary_category}')" style="padding: 2px 8px; font-size: 0.72rem; background: #1e293b; border: 1px solid #475569; color: #38bdf8; border-radius: 4px; cursor: pointer;">+ Category</button>
          </div>

          <input type="text" id="mTitle" name="meta_title" placeholder="Target 50-60 characters including primary keywords" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: #1e293b; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;" oninput="runModalAnalysis()" />
          <div style="height: 4px; background: #334155; border-radius: 2px; margin-top: 6px; overflow: hidden;">
            <div id="mTitleBar" style="height: 100%; width: 0%; background: #34d399; transition: width 0.2s, background 0.2s;"></div>
          </div>
        </div>

        <!-- SLUG & PAGE TYPE -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px; margin-bottom: 18px;">
          <div>
            <label style="display: block; font-size: 0.84rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
              URL Slug *
            </label>
            <input type="text" id="mSlug" name="url_slug" required placeholder="/about-us or /product/slug" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: #1e293b; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem; font-family: monospace;" oninput="runModalAnalysis()" />
          </div>
          <div>
            <label style="display: block; font-size: 0.84rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
              Page Type
            </label>
            <select id="mPageType" name="page_type" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;">
              <option value="page">Standard Page</option>
              <option value="product">Product Detail</option>
              <option value="category">Category</option>
              <option value="blog">Blog Post</option>
            </select>
          </div>
        </div>

        <!-- META DESCRIPTION -->
        <div style="margin-bottom: 18px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
            <label style="font-size: 0.84rem; font-weight: 700; color: #cbd5e1;">
              Meta Description
            </label>
            <span id="mDescCounter" style="font-size: 0.76rem; font-weight: 700; color: #34d399;">0 / 160 chars</span>
          </div>
          <textarea id="mDesc" name="meta_description" rows="3" placeholder="Target 140-160 characters. Write a compelling summary with focus keyphrase." style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: #1e293b; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem; line-height: 1.45; resize: vertical;" oninput="runModalAnalysis()"></textarea>
          <div style="height: 4px; background: #334155; border-radius: 2px; margin-top: 6px; overflow: hidden;">
            <div id="mDescBar" style="height: 100%; width: 0%; background: #34d399; transition: width 0.2s, background 0.2s;"></div>
          </div>
        </div>

        <!-- CORNERSTONE CONTENT & H1 OVERRIDE -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px; align-items: center;">
          <div style="background: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 12px 14px; display: flex; align-items: center; gap: 10px;">
            <input type="checkbox" id="mCornerstone" name="cornerstone_content" value="1" style="width: 18px; height: 18px; cursor: pointer;" />
            <label for="mCornerstone" style="font-size: 0.84rem; color: #f8fafc; font-weight: 600; cursor: pointer;">
              Cornerstone Content (High-priority page)
            </label>
          </div>
          <div>
            <label style="display: block; font-size: 0.8rem; color: #cbd5e1; margin-bottom: 4px;">H1 Heading Override</label>
            <input type="text" id="mH1Override" name="h1_override" placeholder="Defaults to page title" style="width: 100%; box-sizing: border-box; padding: 9px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.85rem;" oninput="runModalAnalysis()" />
          </div>
        </div>

        <!-- YOAST-STYLE SEO ANALYSIS CHECKLIST (TRAFFIC LIGHTS) -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 18px;">
          <div style="font-size: 0.86rem; font-weight: 800; color: #f8fafc; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between;">
            <span>SEO Analysis &amp; Audit</span>
            <span style="font-size: 0.74rem; color: #94a3b8;">Real-time Yoast Checklist</span>
          </div>

          <!-- PROBLEMS (RED) -->
          <div style="margin-bottom: 12px;">
            <div style="font-size: 0.76rem; font-weight: 800; color: #f87171; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <span style="width: 8px; height: 8px; border-radius: 50%; background: #ef4444;"></span>
              <span>Problems (<span id="mCountProblems">0</span>)</span>
            </div>
            <div id="mListProblems" style="display: flex; flex-direction: column; gap: 6px; font-size: 0.82rem; padding-left: 14px;"></div>
          </div>

          <!-- IMPROVEMENTS (AMBER) -->
          <div style="margin-bottom: 12px;">
            <div style="font-size: 0.76rem; font-weight: 800; color: #fbbf24; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <span style="width: 8px; height: 8px; border-radius: 50%; background: #f59e0b;"></span>
              <span>Improvements (<span id="mCountImprovements">0</span>)</span>
            </div>
            <div id="mListImprovements" style="display: flex; flex-direction: column; gap: 6px; font-size: 0.82rem; padding-left: 14px;"></div>
          </div>

          <!-- GOOD RESULTS (GREEN) -->
          <div>
            <div style="font-size: 0.76rem; font-weight: 800; color: #4ade80; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e;"></span>
              <span>Good Results (<span id="mCountGood">0</span>)</span>
            </div>
            <div id="mListGood" style="display: flex; flex-direction: column; gap: 6px; font-size: 0.82rem; padding-left: 14px;"></div>
          </div>
        </div>

      </div>

      <!-- ========================================== -->
      <!-- TAB 2: READABILITY -->
      <!-- ========================================== -->
      <div id="msec-readability" class="wp-modal-section" style="display: none;">
        
        <!-- FLESCH READING EASE METRIC -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
          <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
            <div>
              <div style="font-size: 0.82rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Flesch Reading Ease Score</div>
              <div id="mFleschScoreDisplay" style="font-size: 1.8rem; font-weight: 800; color: #38bdf8;">72.5 &mdash; Fairly Easy to Read</div>
            </div>
            <span style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); padding: 4px 10px; border-radius: 6px; font-size: 0.76rem; font-weight: 700;">
              Web Standard Grade
            </span>
          </div>
          <p style="font-size: 0.82rem; color: #cbd5e1; line-height: 1.5; margin: 0;">
            Evaluates readability using average sentence length and syllables per word. Scores between 60.0 and 80.0 are considered optimal for web visitors and search crawlers.
          </p>
        </div>

        <!-- READABILITY AUDIT CHECKLIST -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 18px;">
          <div style="font-size: 0.86rem; font-weight: 800; color: #f8fafc; margin-bottom: 12px;">Readability Criteria</div>
          <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.82rem;">
            <div style="display: flex; align-items: center; gap: 10px; color: #4ade80;">
              <span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e;"></span>
              <span><strong>Sentence length:</strong> Great. Concisely formulated text under 20 words per sentence.</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px; color: #4ade80;">
              <span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e;"></span>
              <span><strong>Paragraph length:</strong> None of the text blocks exceed the 120-word threshold.</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px; color: #4ade80;">
              <span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e;"></span>
              <span><strong>Transition words:</strong> Well balanced use of connective phrases for smooth readability.</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px; color: #4ade80;">
              <span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e;"></span>
              <span><strong>Subheading distribution:</strong> Content structured with clear hierarchical headers.</span>
            </div>
          </div>
        </div>

      </div>

      <!-- ========================================== -->
      <!-- TAB 3: SCHEMA (STRUCTURED DATA) -->
      <!-- ========================================== -->
      <div id="msec-schema" class="wp-modal-section" style="display: none;">
        
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 18px; margin-bottom: 20px;">
          <label style="display: block; font-size: 0.84rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
            Schema.org Structured Data Type
          </label>
          <select id="mSchemaType" name="schema_type" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;">
            <option value="WebPage">WebPage (General Informational Page)</option>
            <option value="Product">Product (E-commerce / Spec with Offers)</option>
            <option value="Article">Article (General Editorial)</option>
            <option value="BlogPosting">BlogPosting (Technical / Packaging Guide)</option>
            <option value="AboutPage">AboutPage (Company &amp; Certifications)</option>
            <option value="ContactPage">ContactPage (Sales &amp; Seaport Logistics)</option>
            <option value="CollectionPage">CollectionPage (Product Category Listing)</option>
            <option value="FAQPage">FAQPage (Direct Google Accordion Answers)</option>
          </select>
          <div style="font-size: 0.74rem; color: #94a3b8; margin-top: 6px;">Google uses this schema for rich snippet cards, review stars, and knowledge graph carousels.</div>
        </div>

        <!-- FAQ REPEATER -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 18px; margin-bottom: 20px;">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
            <div style="font-size: 0.86rem; font-weight: 700; color: #f8fafc;">
              Google FAQPage Rich Snippet Questions
            </div>
            <button type="button" onclick="addModalFaqRow()" style="padding: 5px 12px; background: #0284c7; color: #fff; border: none; border-radius: 6px; font-size: 0.76rem; font-weight: 700; cursor: pointer;">
              + Add FAQ Question
            </button>
          </div>
          <div id="mFaqContainer" style="display: flex; flex-direction: column; gap: 10px;">
            <div style="font-size: 0.78rem; color: #94a3b8;">No FAQ questions added yet. Click &quot;+ Add FAQ Question&quot; to inject rich accordion FAQs into Google SERPs.</div>
          </div>
        </div>

        <!-- CUSTOM JSON-LD WITH LIVE SYNTAX VALIDATOR -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 18px;">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <label style="font-size: 0.84rem; font-weight: 700; color: #cbd5e1;">
              Custom JSON-LD Schema Override (Optional)
            </label>
            <span id="mJsonStatusBadge" style="font-size: 0.72rem; padding: 2px 8px; border-radius: 4px; background: #334155; color: #94a3b8; font-weight: 700;">
              No Custom JSON
            </span>
          </div>
          <textarea id="mCustomSchema" name="custom_schema_json" rows="6" placeholder='{"@context": "https://schema.org", "@type": "FAQPage", ...}' style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #38bdf8; font-family: monospace; font-size: 0.82rem; line-height: 1.4;" oninput="validateModalJson()"></textarea>
        </div>

      </div>

      <!-- ========================================== -->
      <!-- TAB 4: SOCIAL (FACEBOOK & TWITTER) -->
      <!-- ========================================== -->
      <div id="msec-social" class="wp-modal-section" style="display: none;">
        
        <!-- SUB TABS: FACEBOOK VS TWITTER -->
        <div style="display: flex; gap: 10px; margin-bottom: 18px; border-bottom: 1px solid #334155; padding-bottom: 10px;">
          <button type="button" id="msubtab-fb" onclick="switchSocialSubTab('fb')" style="padding: 6px 14px; border: 1px solid #0284c7; background: #0284c7; color: #fff; border-radius: 6px; font-size: 0.8rem; font-weight: 700; cursor: pointer;">
            Facebook / Open Graph
          </button>
          <button type="button" id="msubtab-tw" onclick="switchSocialSubTab('tw')" style="padding: 6px 14px; border: 1px solid #475569; background: transparent; color: #94a3b8; border-radius: 6px; font-size: 0.8rem; font-weight: 700; cursor: pointer;">
            Twitter / X Card
          </button>
        </div>

        <!-- FACEBOOK SECTION -->
        <div id="msocial-fb">
          
          <!-- SOCIAL CARD MOCKUP -->
          <div style="background: #1877f2; padding: 1px; border-radius: 12px; margin-bottom: 18px; max-width: 520px; overflow: hidden;">
            <div style="background: #242526; color: #e4e6eb; font-family: system-ui, -apple-system, sans-serif;">
              <div id="mcard-fb-img" style="height: 220px; background: #1e293b url('/vikaas_inputs/thumbnails/thumbnail_pet_bottles.webp') center/cover no-repeat;"></div>
              <div style="padding: 12px 16px; background: #3a3b3c;">
                <div style="font-size: 0.72rem; color: #b0b3b8; text-transform: uppercase;">WETRUENORTHGROUP.COM</div>
                <div id="mcard-fb-title" style="font-size: 1rem; font-weight: 700; color: #e4e6eb; margin: 4px 0 2px 0; line-height: 1.3;">Page Title</div>
                <div id="mcard-fb-desc" style="font-size: 0.8rem; color: #b0b3b8; line-height: 1.35; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Meta description preview for social feed.</div>
              </div>
            </div>
          </div>

          <div style="margin-bottom: 14px;">
            <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 4px;">Facebook Title (og:title)</label>
            <input type="text" id="mOgTitle" name="og_title" placeholder="Defaults to SEO Title" style="width: 100%; box-sizing: border-box; padding: 9px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.85rem;" oninput="updateSocialPreviews()" />
          </div>

          <div style="margin-bottom: 14px;">
            <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 4px;">Facebook Description (og:description)</label>
            <textarea id="mOgDescription" name="og_description" rows="2" placeholder="Defaults to Meta Description" style="width: 100%; box-sizing: border-box; padding: 9px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.85rem;" oninput="updateSocialPreviews()"></textarea>
          </div>

          <div>
            <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 4px;">Facebook Image URL (og:image)</label>
            <input type="text" id="mOgImage" name="og_image" placeholder="/uploads/og-image.webp" style="width: 100%; box-sizing: border-box; padding: 9px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.85rem;" oninput="updateSocialPreviews()" />
          </div>

        </div>

        <!-- TWITTER / X SECTION -->
        <div id="msocial-tw" style="display: none;">
          
          <div style="background: #000; border: 1px solid #2f3336; border-radius: 12px; margin-bottom: 18px; max-width: 520px; overflow: hidden; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
            <div id="mcard-tw-img" style="height: 200px; background: #1e293b url('/vikaas_inputs/thumbnails/thumbnail_pet_bottles.webp') center/cover no-repeat;"></div>
            <div style="padding: 10px 14px;">
              <div style="font-size: 0.72rem; color: #71767b;">wetruenorthgroup.com</div>
              <div id="mcard-tw-title" style="font-size: 0.95rem; font-weight: 700; color: #e7e9ea; margin: 3px 0;">Twitter Title</div>
              <div id="mcard-tw-desc" style="font-size: 0.8rem; color: #71767b; line-height: 1.35;">Twitter description snippet preview...</div>
            </div>
          </div>

          <div style="margin-bottom: 14px;">
            <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 4px;">Twitter Card Type</label>
            <select id="mTwitterCard" name="twitter_card" style="width: 100%; box-sizing: border-box; padding: 9px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.85rem;">
              <option value="summary_large_image">Summary Large Image (Recommended)</option>
              <option value="summary">Summary Small Card</option>
            </select>
          </div>

          <div style="margin-bottom: 14px;">
            <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 4px;">Twitter Title (twitter:title)</label>
            <input type="text" id="mTwitterTitle" name="twitter_title" placeholder="Defaults to OG Title or Meta Title" style="width: 100%; box-sizing: border-box; padding: 9px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.85rem;" oninput="updateSocialPreviews()" />
          </div>

          <div>
            <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 4px;">Twitter Description (twitter:description)</label>
            <textarea id="mTwitterDescription" name="twitter_description" rows="2" placeholder="Defaults to OG Description" style="width: 100%; box-sizing: border-box; padding: 9px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.85rem;" oninput="updateSocialPreviews()"></textarea>
          </div>

        </div>

      </div>

      <!-- ========================================== -->
      <!-- TAB 5: ADVANCED -->
      <!-- ========================================== -->
      <div id="msec-advanced" class="wp-modal-section" style="display: none;">
        
        <!-- ROBOTS DIRECTIVES -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 18px; margin-bottom: 20px;">
          <div style="font-size: 0.86rem; font-weight: 700; color: #f8fafc; margin-bottom: 14px;">Search Engine Robots Directives</div>
          
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
              <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 6px;">Allow search engines to show this Page in search results?</label>
              <select id="mRobotsIndex" name="robots_index" style="width: 100%; box-sizing: border-box; padding: 9px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.85rem;">
                <option value="1">Yes (index) &mdash; Default</option>
                <option value="0">No (noindex)</option>
              </select>
            </div>
            <div>
              <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 6px;">Should search engines follow links on this Page?</label>
              <select id="mRobotsFollow" name="robots_follow" style="width: 100%; box-sizing: border-box; padding: 9px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.85rem;">
                <option value="1">Yes (follow) &mdash; Default</option>
                <option value="0">No (nofollow)</option>
              </select>
            </div>
          </div>

          <div>
            <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 6px;">Advanced Robots Meta</label>
            <input type="text" id="mAdvancedRobots" name="advanced_robots" placeholder="noimageindex, noarchive, nosnippet, max-snippet:-1" style="width: 100%; box-sizing: border-box; padding: 9px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.85rem;" />
            <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 4px;">Comma separated crawl instructions (e.g. max-snippet:-1, max-image-preview:large)</div>
          </div>
        </div>

        <!-- BREADCRUMBS TITLE & CANONICAL -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 18px;">
          <div style="margin-bottom: 14px;">
            <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 4px;">Breadcrumbs Title Override</label>
            <input type="text" id="mBreadcrumbsTitle" name="breadcrumbs_title" placeholder="Defaults to page title" style="width: 100%; box-sizing: border-box; padding: 9px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.85rem;" />
          </div>

          <div>
            <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 4px;">Canonical URL Override</label>
            <input type="url" id="mCanonicalUrl" name="canonical_url" placeholder="https://wetruenorthgroup.com/custom-canonical-target" style="width: 100%; box-sizing: border-box; padding: 9px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.85rem;" />
            <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 4px;">Leave blank to default to self-referencing canonical</div>
          </div>
        </div>

      </div>

      <!-- MODAL FOOTER -->
      <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #334155; padding-top: 18px; margin-top: 24px;">
        <button type="button" onclick="closeEditModal()" style="padding: 10px 18px; background: #334155; color: #cbd5e1; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
          Cancel
        </button>
        <button type="submit" class="tn-btn-gold" style="padding: 10px 26px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">
          Save Metadata
        </button>
      </div>

    </form>
  </div>
</div>

<script>
// ==============================================================================
// WORDPRESS SEO SUITE INTERACTIVE CONTROLLER (MODAL)
// ==============================================================================
let currentModalDevice = 'mobile';

function switchModalTab(tabKey) {
  document.querySelectorAll('.wp-modal-section').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.wp-modal-tab').forEach(btn => {
    btn.style.color = '#94a3b8';
    btn.style.borderBottom = '2px solid transparent';
    btn.style.fontWeight = '600';
  });

  const sec = document.getElementById('msec-' + tabKey);
  const btn = document.getElementById('mtab-btn-' + tabKey);
  if (sec) sec.style.display = 'block';
  if (btn) {
    btn.style.color = '#38bdf8';
    btn.style.borderBottom = '2px solid #38bdf8';
    btn.style.fontWeight = '700';
  }
}

function switchSocialSubTab(subKey) {
  const fbSec = document.getElementById('msocial-fb');
  const twSec = document.getElementById('msocial-tw');
  const fbBtn = document.getElementById('msubtab-fb');
  const twBtn = document.getElementById('msubtab-tw');

  if (subKey === 'fb') {
    fbSec.style.display = 'block';
    twSec.style.display = 'none';
    fbBtn.style.background = '#0284c7';
    fbBtn.style.borderColor = '#0284c7';
    fbBtn.style.color = '#fff';
    twBtn.style.background = 'transparent';
    twBtn.style.borderColor = '#475569';
    twBtn.style.color = '#94a3b8';
  } else {
    fbSec.style.display = 'none';
    twSec.style.display = 'block';
    twBtn.style.background = '#0284c7';
    twBtn.style.borderColor = '#0284c7';
    twBtn.style.color = '#fff';
    fbBtn.style.background = 'transparent';
    fbBtn.style.borderColor = '#475569';
    fbBtn.style.color = '#94a3b8';
  }
}

function toggleModalSerpDevice(dev) {
  currentModalDevice = dev;
  const mobBtn = document.getElementById('mbtn-preview-mobile');
  const dskBtn = document.getElementById('mbtn-preview-desktop');
  const container = document.getElementById('mserp-container');

  if (dev === 'mobile') {
    mobBtn.style.background = '#38bdf8';
    mobBtn.style.color = '#0f172a';
    dskBtn.style.background = 'transparent';
    dskBtn.style.color = '#94a3b8';
    container.style.maxWidth = '420px';
  } else {
    dskBtn.style.background = '#38bdf8';
    dskBtn.style.color = '#0f172a';
    mobBtn.style.background = 'transparent';
    mobBtn.style.color = '#94a3b8';
    container.style.maxWidth = '640px';
  }
}

function insertModalTag(fieldId, tag) {
  const input = document.getElementById(fieldId);
  if (!input) return;
  const start = input.selectionStart || input.value.length;
  const end = input.selectionEnd || input.value.length;
  const text = input.value;
  input.value = text.substring(0, start) + ' ' + tag + ' ' + text.substring(end);
  input.focus();
  runModalAnalysis();
}

function runModalAnalysis() {
  const focus = (document.getElementById('mFocusKeyword').value || '').trim().toLowerCase();
  const title = (document.getElementById('mTitle').value || '').trim();
  const desc = (document.getElementById('mDesc').value || '').trim();
  const slug = (document.getElementById('mSlug').value || '').trim();
  const h1 = (document.getElementById('mH1Override').value || '').trim();

  // Update SERP preview
  document.getElementById('mserp-title-display').textContent = title || 'Page Title — TrueNorth Packaging';
  document.getElementById('mserp-desc-display').textContent = desc || 'Meta description snippet will render here dynamically as you write...';
  const cleanSlug = slug.startsWith('/') ? slug : ('/' + slug);
  document.getElementById('mserp-url-display').textContent = 'https://wetruenorthgroup.com' + cleanSlug;

  // Title progress bar
  const tLen = title.length;
  const tCounter = document.getElementById('mTitleCounter');
  const tBar = document.getElementById('mTitleBar');
  tCounter.textContent = tLen + ' / 60 chars';
  const tPct = Math.min(100, Math.round((tLen / 60) * 100));
  tBar.style.width = tPct + '%';
  if (tLen >= 45 && tLen <= 65) {
    tCounter.style.color = '#34d399';
    tBar.style.background = '#22c55e';
  } else if (tLen > 0) {
    tCounter.style.color = '#fbbf24';
    tBar.style.background = '#f59e0b';
  } else {
    tCounter.style.color = '#f87171';
    tBar.style.background = '#ef4444';
  }

  // Description progress bar
  const dLen = desc.length;
  const dCounter = document.getElementById('mDescCounter');
  const dBar = document.getElementById('mDescBar');
  dCounter.textContent = dLen + ' / 160 chars';
  const dPct = Math.min(100, Math.round((dLen / 160) * 100));
  dBar.style.width = dPct + '%';
  if (dLen >= 120 && dLen <= 165) {
    dCounter.style.color = '#34d399';
    dBar.style.background = '#22c55e';
  } else if (dLen > 0) {
    dCounter.style.color = '#fbbf24';
    dBar.style.background = '#f59e0b';
  } else {
    dCounter.style.color = '#f87171';
    dBar.style.background = '#ef4444';
  }

  // Yoast Checklist Audit
  const problems = [];
  const improvements = [];
  const good = [];

  // Keyphrase check
  if (!focus) {
    problems.push('No focus keyphrase entered. Enter a focus keyphrase to evaluate on-page SEO.');
  } else {
    good.push('Focus keyphrase set: "' + focus + '".');

    // Keyphrase in title
    if (title.toLowerCase().includes(focus)) {
      good.push('Keyphrase in SEO title: Keyphrase occurs in the SEO title.');
    } else {
      problems.push('Keyphrase in title: The focus keyphrase does not appear in the SEO title.');
    }

    // Keyphrase in description
    if (desc.toLowerCase().includes(focus)) {
      good.push('Keyphrase in description: Keyphrase found in meta description.');
    } else {
      improvements.push('Keyphrase in description: Keyphrase missing from meta description.');
    }

    // Keyphrase in slug
    if (slug.toLowerCase().includes(focus.replace(/s+/g, '-'))) {
      good.push('Keyphrase in slug: Keyphrase appears in the URL slug.');
    } else {
      improvements.push('Keyphrase in slug: URL slug does not contain the target keyphrase.');
    }
  }

  // Title length
  if (tLen >= 45 && tLen <= 65) {
    good.push('SEO title width: Optimal length (' + tLen + ' characters).');
  } else if (tLen > 65) {
    improvements.push('SEO title width: Longer than recommended 60 chars (' + tLen + ' chars). May be truncated in Google.');
  } else {
    problems.push('SEO title width: Too short (' + tLen + ' chars). Target 50-60 characters.');
  }

  // Meta description length
  if (dLen >= 120 && dLen <= 165) {
    good.push('Meta description length: Optimal length (' + dLen + ' characters).');
  } else if (dLen > 165) {
    improvements.push('Meta description length: Over 160 characters (' + dLen + ' chars). May get cut off in SERPs.');
  } else {
    problems.push('Meta description length: Too short (' + dLen + ' chars). Target 140-160 characters.');
  }

  // H1 Check
  if (h1) {
    good.push('H1 Override set: Custom H1 heading specified.');
  }

  // Render checklists
  renderModalChecklist('mListProblems', problems, '#f87171');
  renderModalChecklist('mListImprovements', improvements, '#fbbf24');
  renderModalChecklist('mListGood', good, '#4ade80');

  document.getElementById('mCountProblems').textContent = problems.length;
  document.getElementById('mCountImprovements').textContent = improvements.length;
  document.getElementById('mCountGood').textContent = good.length;

  // Calculate overall score
  let score = 50;
  score += good.length * 12;
  score -= problems.length * 15;
  score -= improvements.length * 5;
  score = Math.max(10, Math.min(100, score));

  const scoreText = document.getElementById('mScoreText');
  const scorePill = document.getElementById('mScorePill');
  const dot = document.getElementById('mTrafficDot');
  const tabDot = document.getElementById('mtab-dot-seo');

  if (score >= 80) {
    scoreText.textContent = score + '/100 (Good)';
    scorePill.style.color = '#4ade80';
    scorePill.style.borderColor = 'rgba(34, 197, 94, 0.4)';
    scorePill.style.background = 'rgba(34, 197, 94, 0.15)';
    dot.style.background = '#22c55e';
    if (tabDot) tabDot.style.background = '#22c55e';
  } else if (score >= 50) {
    scoreText.textContent = score + '/100 (OK)';
    scorePill.style.color = '#fbbf24';
    scorePill.style.borderColor = 'rgba(245, 158, 11, 0.4)';
    scorePill.style.background = 'rgba(245, 158, 11, 0.15)';
    dot.style.background = '#f59e0b';
    if (tabDot) tabDot.style.background = '#f59e0b';
  } else {
    scoreText.textContent = score + '/100 (Needs Work)';
    scorePill.style.color = '#f87171';
    scorePill.style.borderColor = 'rgba(239, 68, 68, 0.4)';
    scorePill.style.background = 'rgba(239, 68, 68, 0.15)';
    dot.style.background = '#ef4444';
    if (tabDot) tabDot.style.background = '#ef4444';
  }

  updateSocialPreviews();
}

function renderModalChecklist(containerId, items, color) {
  const container = document.getElementById(containerId);
  if (!container) return;
  if (items.length === 0) {
    container.innerHTML = '<div style="color: #64748b; font-style: italic;">None</div>';
    return;
  }
  container.innerHTML = items.map(txt => '<div style="display: flex; align-items: flex-start; gap: 8px; color: ' + color + '; line-height: 1.4;"><span style="font-weight: 700;">&bull;</span> <span>' + txt + '</span></div>').join('');
}

function updateSocialPreviews() {
  const t = document.getElementById('mOgTitle').value || document.getElementById('mTitle').value || 'Page Title';
  const d = document.getElementById('mOgDescription').value || document.getElementById('mDesc').value || 'Description snippet...';
  const img = document.getElementById('mOgImage').value || '/vikaas_inputs/thumbnails/thumbnail_pet_bottles.webp';

  const fbT = document.getElementById('mcard-fb-title');
  const fbD = document.getElementById('mcard-fb-desc');
  const fbImg = document.getElementById('mcard-fb-img');
  if (fbT) fbT.textContent = t;
  if (fbD) fbD.textContent = d;
  if (fbImg && img) fbImg.style.backgroundImage = 'url("' + img + '")';

  const twT = document.getElementById('mcard-tw-title');
  const twD = document.getElementById('mcard-tw-desc');
  const twImg = document.getElementById('mcard-tw-img');
  const customTwT = document.getElementById('mTwitterTitle').value || t;
  const customTwD = document.getElementById('mTwitterDescription').value || d;
  if (twT) twT.textContent = customTwT;
  if (twD) twD.textContent = customTwD;
  if (twImg && img) twImg.style.backgroundImage = 'url("' + img + '")';
}

function validateModalJson() {
  const val = (document.getElementById('mCustomSchema').value || '').trim();
  const badge = document.getElementById('mJsonStatusBadge');
  if (!badge) return;
  if (!val) {
    badge.textContent = 'No Custom JSON';
    badge.style.background = '#334155';
    badge.style.color = '#94a3b8';
    return;
  }
  try {
    JSON.parse(val);
    badge.textContent = 'Valid JSON-LD';
    badge.style.background = 'rgba(34, 197, 94, 0.2)';
    badge.style.color = '#4ade80';
  } catch (e) {
    badge.textContent = 'Invalid JSON Syntax';
    badge.style.background = 'rgba(239, 68, 68, 0.2)';
    badge.style.color = '#f87171';
  }
}

function addModalFaqRow(q = '', a = '') {
  const c = document.getElementById('mFaqContainer');
  if (c.innerHTML.includes('No FAQ questions added yet')) {
    c.innerHTML = '';
  }
  const row = document.createElement('div');
  row.style.background = '#0f172a';
  row.style.padding = '12px';
  row.style.borderRadius = '8px';
  row.style.border = '1px solid #334155';
  row.style.display = 'flex';
  row.style.flexDirection = 'column';
  row.style.gap = '8px';
  row.innerHTML = `
    <div style="display: flex; gap: 8px; align-items: center;">
      <input type="text" name="faq_q[]" value="${q.replace(/"/g, '&quot;')}" placeholder="FAQ Question" style="flex: 1; padding: 7px 10px; background: #1e293b; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.82rem;" />
      <button type="button" onclick="this.closest('div').parentElement.remove()" style="padding: 6px 10px; background: #ef4444; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 0.75rem;">Delete</button>
    </div>
    <textarea name="faq_a[]" rows="2" placeholder="FAQ Answer (rendered in Google rich snippets)" style="width: 100%; box-sizing: border-box; padding: 7px 10px; background: #1e293b; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.82rem;">${a}</textarea>
  `;
  c.appendChild(row);
}

function editPageItem(data) {
  document.getElementById('modalHeadingTitle').textContent = data.url_slug ? ('Edit SEO: ' + data.url_slug) : 'Add New Page SEO Override';
  document.getElementById('mSlug').value = data.url_slug || '';
  document.getElementById('mOldSlug').value = data.url_slug || '';
  document.getElementById('mPageType').value = data.page_type || 'page';
  document.getElementById('mTitle').value = data.meta_title || '';
  document.getElementById('mDesc').value = data.meta_description || '';
  document.getElementById('mFocusKeyword').value = data.focus_keyword || '';
  document.getElementById('mH1Override').value = data.h1_override || '';
  document.getElementById('mCanonicalUrl').value = data.canonical_url || '';
  document.getElementById('mRobotsIndex').value = (data.robots_index !== undefined) ? data.robots_index : '1';
  document.getElementById('mRobotsFollow').value = (data.robots_follow !== undefined) ? data.robots_follow : '1';
  document.getElementById('mCornerstone').checked = !!data.cornerstone_content;
  document.getElementById('mBreadcrumbsTitle').value = data.breadcrumbs_title || '';
  document.getElementById('mAdvancedRobots').value = data.advanced_robots || '';
  document.getElementById('mOgTitle').value = data.og_title || '';
  document.getElementById('mOgDescription').value = data.og_description || '';
  document.getElementById('mOgImage').value = data.og_image || '';
  document.getElementById('mTwitterTitle').value = data.twitter_title || '';
  document.getElementById('mTwitterDescription').value = data.twitter_description || '';
  document.getElementById('mTwitterCard').value = data.twitter_card || 'summary_large_image';
  document.getElementById('mSchemaType').value = data.schema_type || 'WebPage';
  document.getElementById('mCustomSchema').value = data.custom_schema_json || '';
  document.getElementById('mPageRefId').value = data.page_ref_id || '';

  document.getElementById('pageEditModal').style.display = 'flex';
  switchModalTab('seo');
  runModalAnalysis();
  validateModalJson();
}

function openPageEditModal(slug, type, title, desc, kw, h1, canonical, rIndex, rFollow) {
  editPageItem({
    url_slug: slug,
    page_type: type,
    meta_title: title,
    meta_description: desc,
    focus_keyword: kw,
    h1_override: h1,
    canonical_url: canonical,
    robots_index: rIndex,
    robots_follow: rFollow
  });
}
</script>
<!-- CSV IMPORT MODAL -->
<div id="csvModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
  <div style="background: #1e293b; border: 1px solid #475569; border-radius: 12px; max-width: 500px; width: 100%; padding: 24px; box-sizing: border-box;">
    <h3 style="font-size: 1.2rem; font-weight: 800; color: #f8fafc; margin: 0 0 12px 0;">Import SEO Meta CSV</h3>
    <p style="font-size: 0.85rem; color: #94a3b8; margin: 0 0 16px 0;">
      Upload a CSV file containing columns: <code>URL Slug</code>, <code>Page Type</code>, <code>Meta Title</code>, <code>Meta Description</code>, <code>Focus Keyword</code>.
    </p>
    <form method="POST" action="/admin/seo_pages" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
      <input type="hidden" name="action" value="import_csv" />
      <input type="file" name="csv_file" accept=".csv" required style="width: 100%; margin-bottom: 20px; color: #cbd5e1;" />
      <div style="display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" onclick="document.getElementById('csvModal').style.display='none';" style="padding: 8px 16px; background: #334155; color: #fff; border: none; border-radius: 6px; cursor: pointer;">Cancel</button>
        <button type="submit" class="tn-btn-gold" style="padding: 8px 18px; border: none; border-radius: 6px; font-weight: 700; cursor: pointer;">Upload &amp; Import</button>
      </div>
    </form>
  </div>
</div>

<script>
function toggleSelectAll(master) {
  document.querySelectorAll('.page-checkbox').forEach(cb => cb.checked = master.checked);
}

function closeEditModal() {
  document.getElementById('pageEditModal').style.display = 'none';
}

function openPageEditModal(slug, type, title, desc, kw, h1, canonical, rIndex, rFollow) {
  document.getElementById('modalHeadingTitle').textContent = slug ? ('Edit SEO: ' + slug) : 'Add New Page SEO Override';
  document.getElementById('mSlug').value = slug || '';
  document.getElementById('mOldSlug').value = slug || '';
  document.getElementById('mPageType').value = type || 'page';
  document.getElementById('mTitle').value = title || '';
  document.getElementById('mDesc').value = desc || '';
  document.getElementById('mFocusKeyword').value = kw || '';
  document.getElementById('mH1Override').value = h1 || '';
  document.getElementById('mCanonicalUrl').value = canonical || '';
  document.getElementById('mRobotsIndex').value = (rIndex !== undefined) ? rIndex : '1';
  document.getElementById('mRobotsFollow').value = (rFollow !== undefined) ? rFollow : '1';
  document.getElementById('pageEditModal').style.display = 'flex';
  updateLiveSerp();
}

function editPageItem(data) {
  openPageEditModal(
    data.url_slug || '',
    data.page_type || 'page',
    data.meta_title || '',
    data.meta_description || '',
    data.focus_keyword || '',
    data.h1_override || '',
    data.canonical_url || '',
    (data.robots_index !== undefined) ? data.robots_index : 1,
    (data.robots_follow !== undefined) ? data.robots_follow : 1
  );
  document.getElementById('mOgTitle').value = data.og_title || '';
  document.getElementById('mOgImage').value = data.og_image || '';
  document.getElementById('mSchemaType').value = data.schema_type || 'WebPage';
  document.getElementById('mCustomSchema').value = data.custom_schema_json || '';
  document.getElementById('mPageRefId').value = data.page_ref_id || '';
}

function updateLiveSerp() {
  const title = document.getElementById('mTitle').value || 'Packaging Solutions — TrueNorth Group';
  const desc = document.getElementById('mDesc').value || 'Detailed technical analysis of primary packaging containers and dispensing closure engineering.';
  const slug = document.getElementById('mSlug').value || '...';

  document.getElementById('liveSerpTitle').textContent = title;
  document.getElementById('liveSerpDesc').textContent = desc;
  document.getElementById('liveSerpUrl').textContent = 'https://wetruenorthgroup.com' + (slug.startsWith('/') ? slug : ('/' + slug));

  const tLen = document.getElementById('mTitle').value.length;
  document.getElementById('titleCounter').textContent = tLen + ' / 60';
  document.getElementById('titleCounter').style.color = (tLen >= 45 && tLen <= 65) ? '#34d399' : '#fbbf24';

  const dLen = document.getElementById('mDesc').value.length;
  document.getElementById('descCounter').textContent = dLen + ' / 160';
  document.getElementById('descCounter').style.color = (dLen >= 120 && dLen <= 165) ? '#34d399' : '#fbbf24';
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

