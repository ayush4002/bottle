<?php
// ==============================================================================
// TRUENORTH CMS — WORDPRESS STYLE IMAGE SEO & MEDIA ATTACHMENTS
// Complete Image SEO suite with authentic WordPress "Attachment Details" modal dialog,
// live Google accessibility audits, grid & table modes, dimensions and auto-save
// ==============================================================================

require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/app/Models/SeoModel.php';
require_once dirname(__DIR__) . '/app/Models/ProductModel.php';

checkAdminAuth();
SeoModel::init();

// 1. Handle Single Attachment JSON API call
if (isset($_GET['action']) && $_GET['action'] === 'get_single_json') {
    header('Content-Type: application/json; charset=utf-8');
    $reqUrl = trim($_GET['url'] ?? '');
    $item = SeoModel::getImageSeo($reqUrl);
    echo json_encode(['success' => true, 'item' => $item]);
    exit;
}

// 2. Handle Auto-Fill All Action
if (isset($_GET['action']) && $_GET['action'] === 'auto_fill_all') {
    $adminUser = $_SESSION['admin_user'] ?? 'admin';
    $map = SeoModel::getSeoImagesMap();
    $updatedCount = 0;
    foreach ($map as $k => $item) {
        if (empty(trim($item['alt_text'] ?? ''))) {
            $baseName = pathinfo($item['file_name'] ?? $k, PATHINFO_FILENAME);
            $cleanName = ucwords(str_replace(['-', '_', '.'], ' ', $baseName));
            $upTo = !empty($item['uploaded_to']) && $item['uploaded_to'] !== 'Unattached' ? $item['uploaded_to'] : $cleanName;
            $newAlt = $upTo . ' — TrueNorth Primary Packaging';
            $item['alt_text'] = $newAlt;
            if (empty(trim($item['title_text'] ?? ''))) {
                $item['title_text'] = $upTo;
            }
            SeoModel::saveImageSeo($k, $item, $adminUser);
            $updatedCount++;
        }
    }
    header("Location: /admin/seo_images?msg=" . urlencode("Auto-generated descriptive alt tags for {$updatedCount} media assets!"));
    exit;
}

// 3. Handle Delete Image Action
if ((isset($_GET['action']) && $_GET['action'] === 'delete_img') || (isset($_POST['action']) && $_POST['action'] === 'delete_img')) {
    $reqUrl = trim($_REQUEST['url'] ?? '');
    $adminUser = $_SESSION['admin_user'] ?? 'admin';
    if (!empty($reqUrl)) {
        // Always drop the stored SEO metadata record for this URL.
        SeoModel::deleteImageSeo($reqUrl, $adminUser);
        // Only unlink a real, existing image file that is contained inside PUBLIC_PATH.
        $cleanUrl = ltrim($reqUrl, '/');
        $candidateSources = [
            $cleanUrl,
            'uploads/' . basename($cleanUrl),
            'uploads/products/' . basename($cleanUrl)
        ];
        foreach ($candidateSources as $src) {
            $cand = function_exists('tn_public_asset_path') ? tn_public_asset_path($src) : null;
            if ($cand !== null) {
                @unlink($cand);
                break;
            }
        }
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
            exit;
        }
        header("Location: /admin/seo_images?msg=" . urlencode("Image and SEO record deleted successfully."));
        exit;
    }
}

$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';
$adminUser = $_SESSION['admin_user'] ?? 'admin';

// Filters & View Mode
$filter = $_GET['filter'] ?? 'all'; // all, missing, optimized
$search = trim($_GET['q'] ?? '');
$viewMode = $_GET['view'] ?? 'grid'; // grid or list
$page = max(1, (int)($_GET['p'] ?? 1));

$result = SeoModel::getAllImagesSeo($filter, $search, $page, 32);
$images = $result['items'];
$stats = $result['stats'];
$totalPages = $result['totalPages'];

// Handle Bulk Form Save (from List View)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_alts') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $err = "Invalid security token.";
    } else {
        $alts = $_POST['alts'] ?? [];
        $titles = $_POST['titles'] ?? [];
        $count = 0;
        foreach ($alts as $urlKey => $alt) {
            $title = $titles[$urlKey] ?? '';
            SeoModel::saveImageSeo($urlKey, [
                'alt_text' => trim($alt),
                'title_text' => trim($title)
            ], $adminUser);
            $count++;
        }
        $msg = "Successfully saved SEO alt tags for {$count} images!";
        // Re-fetch after save
        $result = SeoModel::getAllImagesSeo($filter, $search, $page, 32);
        $images = $result['items'];
        $stats = $result['stats'];
    }
}

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/partials/seo_tabs.php';
$csrfToken = generateCsrfToken();
?>

<div style="max-width: 1360px; margin: 0 auto;">

  <!-- PAGE HEADER & ACTIONS -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; flex-wrap: wrap; gap: 14px;">
    <div>
      <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 0.88rem; color: #94a3b8;">
        <a href="/admin/seo" style="color: #94a3b8; text-decoration: none;">&larr; SEO Overview</a>
        <span>/</span>
        <span style="color: #38bdf8; font-weight: 600;">Image SEO &amp; Media Attachments</span>
      </div>
      <div style="display: flex; align-items: center; gap: 14px;">
        <h1 style="font-size: 1.8rem; font-weight: 800; color: #f8fafc; margin: 0;">
          Image SEO &amp; Alt Tag Tool
        </h1>
        <span style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); padding: 4px 10px; border-radius: 12px; font-size: 0.76rem; font-weight: 700; text-transform: uppercase;">
          WordPress Attachment Engine
        </span>
      </div>
    </div>
    
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
      <a href="/admin/seo_images?action=auto_fill_all" onclick="return confirm('Auto-generate keyword-rich descriptive alt tags for all images missing an alt attribute?');" class="tn-btn-outline" style="padding: 10px 18px; border-radius: 8px; font-weight: 700; font-size: 0.86rem; background: #1e293b; color: #38bdf8; border: 1px solid #334155; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        Auto-Fill Missing Alt Tags
      </a>
      <a href="/admin/media" class="tn-btn-gold" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; padding: 11px 22px; border-radius: 8px; font-weight: 700; font-size: 0.88rem; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35);">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Upload New Media
      </a>
    </div>
  </div>

  <?php if (!empty($msg)): ?>
    <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 22px;">
      <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($err)): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 22px;">
      <?= htmlspecialchars($err) ?>
    </div>
  <?php endif; ?>

  <!-- STATS CARDS (WORDPRESS SEO SCOREBOARD) -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
      <div style="font-size: 0.78rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Total Media Assets</div>
      <div style="font-size: 2rem; font-weight: 800; color: #f8fafc;"><?= number_format($stats['total']) ?></div>
      <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Indexed across catalog &amp; blog</div>
    </div>
    
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
      <div style="font-size: 0.78rem; font-weight: 700; color: #34d399; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">SEO Alt Ready</div>
      <div style="font-size: 2rem; font-weight: 800; color: #34d399;"><?= number_format($stats['optimized']) ?></div>
      <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Optimized for Google Images</div>
    </div>

    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
      <div style="font-size: 0.78rem; font-weight: 700; color: <?= $stats['missing'] > 0 ? '#fbbf24' : '#94a3b8' ?>; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Missing Alt Text</div>
      <div style="font-size: 2rem; font-weight: 800; color: <?= $stats['missing'] > 0 ? '#fbbf24' : '#94a3b8' ?>;"><?= number_format($stats['missing']) ?></div>
      <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Requires accessibility alt attribute</div>
    </div>

    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
      <div style="font-size: 0.78rem; font-weight: 700; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Image Health Score</div>
      <div style="font-size: 2rem; font-weight: 800; color: #38bdf8;"><?= $stats['coverage'] ?>%</div>
      <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">WCAG 2.1 accessibility rating</div>
    </div>
  </div>

  <!-- TOOLBAR: FILTER PILLS, SEARCH & VIEW SWITCH (MATCHING WORDPRESS) -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
    
    <!-- Filter Pills -->
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
      <a href="/admin/seo_images?filter=all&view=<?= htmlspecialchars($viewMode) ?>&q=<?= urlencode($search) ?>" style="padding: 7px 14px; border-radius: 6px; font-size: 0.82rem; font-weight: 700; text-decoration: none; <?= $filter === 'all' ? 'background: #0284c7; color: #fff;' : 'background: #0f172a; color: #94a3b8; border: 1px solid #334155;' ?>">
        All Images (<?= $stats['total'] ?>)
      </a>
      <a href="/admin/seo_images?filter=missing&view=<?= htmlspecialchars($viewMode) ?>&q=<?= urlencode($search) ?>" style="padding: 7px 14px; border-radius: 6px; font-size: 0.82rem; font-weight: 700; text-decoration: none; <?= $filter === 'missing' ? 'background: #d97706; color: #fff;' : 'background: #0f172a; color: #94a3b8; border: 1px solid #334155;' ?>">
        Missing Alt Text (<?= $stats['missing'] ?>)
      </a>
      <a href="/admin/seo_images?filter=optimized&view=<?= htmlspecialchars($viewMode) ?>&q=<?= urlencode($search) ?>" style="padding: 7px 14px; border-radius: 6px; font-size: 0.82rem; font-weight: 700; text-decoration: none; <?= $filter === 'optimized' ? 'background: #059669; color: #fff;' : 'background: #0f172a; color: #94a3b8; border: 1px solid #334155;' ?>">
        Optimized (<?= $stats['optimized'] ?>)
      </a>
    </div>

    <!-- Search & View Mode Switch -->
    <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
      <form method="GET" action="/admin/seo_images" style="display: flex; gap: 8px; align-items: center;">
        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>" />
        <input type="hidden" name="view" value="<?= htmlspecialchars($viewMode) ?>" />
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name, alt or product..." style="padding: 8px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.84rem; min-width: 220px;" />
        <button type="submit" style="padding: 8px 14px; background: #334155; color: #fff; border: none; border-radius: 6px; font-weight: 600; font-size: 0.82rem; cursor: pointer;">
          Search
        </button>
        <?php if (!empty($search)): ?>
          <a href="/admin/seo_images?filter=<?= htmlspecialchars($filter) ?>&view=<?= htmlspecialchars($viewMode) ?>" style="color: #94a3b8; font-size: 0.8rem; text-decoration: none;">Clear</a>
        <?php endif; ?>
      </form>

      <!-- View Switcher (Grid vs List) -->
      <div style="display: flex; background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 2px;">
        <a href="/admin/seo_images?filter=<?= htmlspecialchars($filter) ?>&view=grid&q=<?= urlencode($search) ?>" title="WordPress Grid View" style="padding: 6px 12px; border-radius: 6px; display: flex; align-items: center; justify-content: center; <?= $viewMode === 'grid' ? 'background: #38bdf8; color: #0f172a;' : 'color: #94a3b8;' ?> text-decoration: none;">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        </a>
        <a href="/admin/seo_images?filter=<?= htmlspecialchars($filter) ?>&view=list&q=<?= urlencode($search) ?>" title="WordPress List View" style="padding: 6px 12px; border-radius: 6px; display: flex; align-items: center; justify-content: center; <?= $viewMode === 'list' ? 'background: #38bdf8; color: #0f172a;' : 'color: #94a3b8;' ?> text-decoration: none;">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        </a>
      </div>
    </div>

  </div>

  <!-- VIEW 1: WORDPRESS PHOTO GRID VIEW -->
  <?php if ($viewMode === 'grid'): ?>
    <?php if (empty($images)): ?>
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 48px; text-align: center; color: #94a3b8;">
        <p style="font-size: 1.1rem; margin: 0 0 10px 0;">No media images found matching your filter.</p>
        <a href="/admin/seo_images" style="color: #38bdf8; text-decoration: none; font-weight: 600;">Reset Filters</a>
      </div>
    <?php else: ?>
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; margin-bottom: 30px;">
        <?php foreach ($images as $idx => $img): ?>
          <?php
            $hasAlt = !empty(trim($img['alt_text'] ?? ''));
            $badgeBg = $hasAlt ? 'rgba(34, 197, 94, 0.15)' : 'rgba(239, 68, 68, 0.15)';
            $badgeColor = $hasAlt ? '#86efac' : '#fca5a5';
            $badgeBorder = $hasAlt ? 'rgba(34, 197, 94, 0.4)' : 'rgba(239, 68, 68, 0.4)';
            $dotColor = $hasAlt ? '#22c55e' : '#ef4444';
            $badgeText = $hasAlt ? 'Alt Ready' : 'Missing Alt';
          ?>
          <div class="img-card" data-img-url="<?= htmlspecialchars($img['url']) ?>" onclick="openAttachmentModalByIndex(<?= $idx ?>)" style="background: #1e293b; border: 1px solid #334155; border-radius: 10px; overflow: hidden; cursor: pointer; transition: transform 0.15s, border-color 0.15s; display: flex; flex-direction: column;" onmouseover="this.style.borderColor='#38bdf8'; this.style.transform='translateY(-2px)';" onmouseout="this.style.borderColor='#334155'; this.style.transform='translateY(0)';">
            
            <!-- Thumbnail Box -->
            <div style="height: 140px; background: #090e1a; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; padding: 8px;">
              <img src="<?= htmlspecialchars($img['url']) ?>" style="max-height: 100%; max-width: 100%; object-fit: contain;" alt="<?= htmlspecialchars($img['alt_text'] ?? '') ?>" onerror="this.src='/logo_svg.svg';" />
              
              <!-- SEO Badge in corner -->
              <div class="card-seo-badge" style="position: absolute; top: 8px; right: 8px; display: flex; align-items: center; gap: 4px; padding: 2px 7px; border-radius: 10px; background: <?= $badgeBg ?>; border: 1px solid <?= $badgeBorder ?>; color: <?= $badgeColor ?>; font-size: 0.68rem; font-weight: 700;">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: <?= $dotColor ?>;"></span>
                <span><?= $badgeText ?></span>
              </div>
            </div>

            <!-- Details Foot -->
            <div style="padding: 10px 12px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
              <div style="font-size: 0.8rem; font-weight: 700; color: #f8fafc; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-bottom: 2px;" title="<?= htmlspecialchars($img['file_name'] ?? basename($img['url'])) ?>">
                <?= htmlspecialchars($img['file_name'] ?? basename($img['url'])) ?>
              </div>
              <div style="font-size: 0.72rem; color: #94a3b8; display: flex; justify-content: space-between; align-items: center;">
                <span><?= htmlspecialchars($img['dimensions'] ?? '800x800') ?></span>
                <span style="color: #38bdf8; font-weight: 600;">Details &rarr;</span>
              </div>
            </div>

          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  <!-- VIEW 2: WORDPRESS TABLE / LIST VIEW -->
  <?php else: ?>
    <form id="bulkImageAltForm" method="POST" action="/admin/seo_images?filter=<?= htmlspecialchars($filter) ?>&view=list">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
      <input type="hidden" name="action" value="save_alts" />

      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; overflow: hidden; margin-bottom: 24px;">
        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.86rem;">
            <thead>
              <tr style="color: #94a3b8; border-bottom: 1px solid #334155; background: #0f172a; font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.05em;">
                <th style="padding: 12px 16px; width: 68px;">File</th>
                <th style="padding: 12px 16px; width: 200px;">File Name &amp; Title</th>
                <th style="padding: 12px 16px;">Alternative Text (SEO &amp; Accessibility)</th>
                <th style="padding: 12px 16px; width: 180px;">Attached To</th>
                <th style="padding: 12px 16px; width: 100px;">Dimensions</th>
                <th style="padding: 12px 16px; text-align: center; width: 110px;">Status</th>
                <th style="padding: 12px 16px; text-align: right; width: 110px;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($images)): ?>
                <tr>
                  <td colspan="7" style="padding: 36px; text-align: center; color: #94a3b8;">
                    No images found matching your search.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($images as $idx => $img): ?>
                  <?php
                    $hasAlt = !empty(trim($img['alt_text'] ?? ''));
                    $rowUrl = $img['url'];
                  ?>
                  <tr data-img-url="<?= htmlspecialchars($rowUrl) ?>" style="border-bottom: 1px solid #334155; transition: background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                    
                    <!-- Thumb -->
                    <td style="padding: 12px 16px;">
                      <div onclick="openAttachmentModalByIndex(<?= $idx ?>)" style="width: 52px; height: 52px; background: #090e1a; border: 1px solid #475569; border-radius: 6px; overflow: hidden; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                        <img src="<?= htmlspecialchars($rowUrl) ?>" style="max-height: 100%; max-width: 100%; object-fit: contain;" alt="<?= htmlspecialchars($img['alt_text'] ?? '') ?>" onerror="this.src='/logo_svg.svg';" />
                      </div>
                    </td>

                    <!-- File Name & Title -->
                    <td style="padding: 12px 16px;">
                      <div style="font-weight: 700; color: #f8fafc; font-size: 0.88rem; margin-bottom: 2px;">
                        <a href="javascript:void(0)" onclick="openAttachmentModalByIndex(<?= $idx ?>)" style="color: #f8fafc; text-decoration: none;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='#f8fafc'">
                          <?= htmlspecialchars($img['file_name'] ?? basename($rowUrl)) ?>
                        </a>
                      </div>
                      <div style="font-size: 0.74rem; color: #64748b;">
                        <?= htmlspecialchars($img['file_size'] ?? '32 KB') ?>
                      </div>
                    </td>

                    <!-- Inline Alt Input -->
                    <td style="padding: 12px 16px;">
                      <input type="text" name="alts[<?= htmlspecialchars($rowUrl) ?>]" value="<?= htmlspecialchars($img['alt_text'] ?? '') ?>" placeholder="Descriptive alternative text for Google Images..." class="form-control-smart" style="width: 100%; box-sizing: border-box; padding: 8px 12px; font-size: 0.85rem;" />
                    </td>

                    <!-- Attached To -->
                    <td style="padding: 12px 16px; font-size: 0.82rem; color: #cbd5e1;">
                      <?php if (!empty($img['uploaded_to_url'])): ?>
                        <a href="<?= htmlspecialchars($img['uploaded_to_url']) ?>" target="_blank" rel="noopener" style="color: #38bdf8; text-decoration: none;">
                          <?= htmlspecialchars($img['uploaded_to'] ?? 'Page/Product') ?>
                        </a>
                      <?php else: ?>
                        <span><?= htmlspecialchars($img['uploaded_to'] ?? 'Unattached') ?></span>
                      <?php endif; ?>
                    </td>

                    <!-- Dimensions -->
                    <td style="padding: 12px 16px; font-size: 0.78rem; color: #94a3b8; font-family: monospace;">
                      <?= htmlspecialchars($img['dimensions'] ?? '800x800') ?>
                    </td>

                    <!-- Status Pill -->
                    <td style="padding: 12px 16px; text-align: center;">
                      <span class="tbl-status-pill" style="display: inline-flex; align-items: center; gap: 5px; font-size: 0.75rem; font-weight: 700;">
                        <?php if ($hasAlt): ?>
                          <span style="width:7px; height:7px; border-radius:50%; background:#22c55e;"></span>
                          <span style="color:#86efac;">Optimized</span>
                        <?php else: ?>
                          <span style="width:7px; height:7px; border-radius:50%; background:#ef4444;"></span>
                          <span style="color:#fca5a5;">Missing Alt</span>
                        <?php endif; ?>
                      </span>
                    </td>

                    <!-- Action -->
                    <td style="padding: 12px 16px; text-align: right;">
                      <button type="button" onclick="openAttachmentModalByIndex(<?= $idx ?>)" style="padding: 6px 12px; background: #334155; color: #f8fafc; border: none; border-radius: 6px; font-weight: 600; font-size: 0.8rem; cursor: pointer;">
                        Edit &rarr;
                      </button>
                    </td>

                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div style="padding: 14px 20px; background: #0f172a; border-top: 1px solid #334155; display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 0.82rem; color: #94a3b8;">Click any row or thumbnail to open the WordPress Attachment Details dialog.</span>
          <button type="submit" class="tn-btn-gold" style="padding: 9px 22px; border: none; border-radius: 6px; font-weight: 700; font-size: 0.88rem; cursor: pointer;">
            Save All Inline Changes
          </button>
        </div>

      </div>
    </form>
  <?php endif; ?>

  <!-- PAGINATION CONTROLS -->
  <?php if ($totalPages > 1): ?>
    <div style="display: flex; justify-content: center; gap: 6px; margin-bottom: 40px;">
      <?php for ($i = 1; $i <= min(10, $totalPages); $i++): ?>
        <a href="/admin/seo_images?filter=<?= htmlspecialchars($filter) ?>&view=<?= htmlspecialchars($viewMode) ?>&p=<?= $i ?>&q=<?= urlencode($search) ?>" style="padding: 7px 12px; border-radius: 6px; font-size: 0.84rem; font-weight: 700; text-decoration: none; <?= $page === $i ? 'background: #0284c7; color: #fff;' : 'background: #1e293b; color: #94a3b8; border: 1px solid #334155;' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>

</div>

<!-- ATTACHMENT DETAILS MODAL INCLUSION -->
<?php include __DIR__ . '/partials/attachment_details_modal.php'; ?>

<script>
// Seed JavaScript with the current page dataset for instant navigation
document.addEventListener('DOMContentLoaded', function() {
  const dataset = <?= json_encode($images, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  setAttachmentDataset(dataset);
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
