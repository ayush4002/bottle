<?php
// ==============================================================================
// TRUENORTH CMS — WORDPRESS STYLE EDIT PAGE
// Provides WordPress Page editing experience with AB Page Banners, Sections & Yoast SEO
// ==============================================================================

require_once dirname(__DIR__) . '/includes/upload_guard.php';
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/app/Models/PageModel.php';
require_once dirname(__DIR__) . '/app/Models/SeoModel.php';

checkAdminAuth();
PageModel::initStorage();
SeoModel::init();

$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';
$adminUser = $_SESSION['admin_user'] ?? 'admin';

// Determine page to edit
$reqSlug = $_GET['slug'] ?? ($_GET['id'] ?? '/about');
$reqSlug = '/' . ltrim(trim($reqSlug), '/');
if ($reqSlug === '//') $reqSlug = '/';

// Handle Page Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_page') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $err = "Invalid security token.";
    } else {
        $delSlug = trim($_POST['slug'] ?? $reqSlug);
        if ($delSlug === '/' || $delSlug === '/home') {
            $err = "The Home page cannot be deleted.";
        } else {
            PageModel::deletePage($delSlug, $adminUser);
            $delRedirect = "/admin/pages?msg=" . urlencode("Page '{$delSlug}' and its SEO records deleted successfully.");
            if (!headers_sent()) {
                header("Location: " . $delRedirect);
            } else {
                echo "<script>window.location.href = " . json_encode($delRedirect) . ";</script>";
            }
            exit();
        }
    }
}

// Handle POST Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_page') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $err = "Invalid security token. Please refresh and try again.";
    } else {
        $slug = '/' . ltrim(trim($_POST['slug'] ?? $reqSlug), '/');
        if ($slug === '//') $slug = '/';

        $oldSlug = trim($_POST['old_slug'] ?? $slug);

        $bannerImage = trim($_POST['banner_image'] ?? '');
        $featuredImage = trim($_POST['featured_image'] ?? '');

        // Handle Banner image upload
        if (!empty($_FILES['banner_image_file']['name'])) {
            $upDir = dirname(__DIR__) . '/public/uploads/';
            if (!is_dir($upDir)) @mkdir($upDir, 0755, true);
            $ext = tn_upload_safe_image_ext($_FILES['banner_image_file']['tmp_name'], $_FILES['banner_image_file']['name']);
            if ($ext !== false) {
                $fName = 'page_banner_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['banner_image_file']['tmp_name'], $upDir . $fName)) {
                    $bannerImage = '/uploads/' . $fName;
                }
            }
        }

        // Handle Featured image upload
        if (!empty($_FILES['featured_image_file']['name'])) {
            $upDir = dirname(__DIR__) . '/public/uploads/';
            if (!is_dir($upDir)) @mkdir($upDir, 0755, true);
            $ext = tn_upload_safe_image_ext($_FILES['featured_image_file']['tmp_name'], $_FILES['featured_image_file']['name']);
            if ($ext !== false) {
                $fName = 'page_featured_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['featured_image_file']['tmp_name'], $upDir . $fName)) {
                    $featuredImage = '/uploads/' . $fName;
                }
            }
        }

        $saveData = [
            'id' => trim($_POST['id'] ?? trim($slug, '/')),
            'title' => trim($_POST['title'] ?? 'Untitled Page'),
            'slug' => $slug,
            'old_slug' => $oldSlug,
            'template' => trim($_POST['template'] ?? 'default'),
            'status' => trim($_POST['status'] ?? 'published'),
            'order_num' => (int)($_POST['order_num'] ?? 0),
            'banner_image' => $bannerImage,
            'share_title' => trim($_POST['share_title'] ?? ''),
            'banner_subtitle' => trim($_POST['banner_subtitle'] ?? ''),
            'banner_video' => trim($_POST['banner_video'] ?? ''),
            'content' => $_POST['content'] ?? '',
            'featured_image' => $featuredImage,
            'seo' => $_POST['seo'] ?? []
        ];

        // Ensure SEO slug matches page slug
        if (!isset($saveData['seo']['url_slug']) || empty($saveData['seo']['url_slug'])) {
            $saveData['seo']['url_slug'] = $slug;
        } else {
            $saveData['seo']['url_slug'] = '/' . ltrim(trim($saveData['seo']['url_slug']), '/');
        }

        // Handle OG image file upload if provided
        if (!empty($_FILES['og_image_file']['name'])) {
            $upDir = dirname(__DIR__) . '/public/uploads/';
            if (!is_dir($upDir)) @mkdir($upDir, 0755, true);
            $ext = tn_upload_safe_image_ext($_FILES['og_image_file']['tmp_name'], $_FILES['og_image_file']['name']);
            if ($ext !== false) {
                $fName = 'page_og_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['og_image_file']['tmp_name'], $upDir . $fName)) {
                    $saveData['seo']['og_image'] = '/uploads/' . $fName;
                }
            }
        }

        PageModel::savePage($saveData, $adminUser);
        $msg = "Page '{$saveData['title']}' and Yoast SEO metadata updated successfully.";
        $redirectUrl = "/admin/edit_page?slug=" . urlencode($saveData['slug']) . "&msg=" . urlencode($msg);
        if (!headers_sent()) {
            header("Location: " . $redirectUrl);
        } else {
            echo "<script>window.location.href = " . json_encode($redirectUrl) . ";</script>";
        }
        exit();
    }
}

require_once __DIR__ . '/header.php';

$page = PageModel::getPageBySlug($reqSlug);
$pageSeo = $page['seo'] ?? [];
$isEdit = !empty($page['title']);
$csrfToken = generateCsrfToken();
?>

<style>
.wp-page-editor-layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 320px;
  gap: 24px;
  align-items: start;
}
.wp-sidebar-box {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  margin-bottom: 20px;
  overflow: hidden;
}
.wp-sidebar-box-header {
  padding: 12px 18px;
  background: #0f172a;
  border-bottom: 1px solid #334155;
  font-size: 0.88rem;
  font-weight: 800;
  color: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.wp-sidebar-box-body {
  padding: 18px;
}
.wp-box {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  margin-bottom: 24px;
  overflow: hidden;
}
.wp-box-header {
  padding: 14px 20px;
  background: #0f172a;
  border-bottom: 1px solid #334155;
  font-size: 0.95rem;
  font-weight: 800;
  color: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.wp-box-body {
  padding: 22px;
}
@media (max-width: 1024px) {
  .wp-page-editor-layout {
    grid-template-columns: 1fr;
  }
}
</style>

<div style="max-width: 1360px; margin: 0 auto;">

  <!-- TOP BREADCRUMB & HEADER -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; flex-wrap: wrap; gap: 14px;">
    <div>
      <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 0.88rem; color: #94a3b8;">
        <a href="/admin/pages" style="color: #94a3b8; text-decoration: none;">&larr; Pages</a>
        <span>/</span>
        <span style="color: #38bdf8; font-weight: 600;">Edit Page</span>
      </div>
      <div style="display: flex; align-items: center; gap: 14px;">
        <h1 style="font-size: 1.8rem; font-weight: 800; color: #f8fafc; margin: 0;">
          Edit Page
        </h1>
        <a href="/admin/edit_page?slug=/new-page" class="tn-btn-outline" style="padding: 5px 12px; font-size: 0.8rem; border-radius: 6px; color: #cbd5e1; border: 1px solid #475569; text-decoration: none; font-weight: 600;">
          Add New
        </a>
      </div>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
      <a href="<?= htmlspecialchars($page['slug']) ?>" target="_blank" rel="noopener" style="display: inline-flex; align-items: center; gap: 6px; background: #0f172a; color: #38bdf8; border: 1px solid #0284c7; text-decoration: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; font-size: 0.88rem;">
        View Page &nearr;
      </a>
      <button type="button" onclick="document.getElementById('editPageMainForm').submit();" class="tn-btn-gold" style="display: inline-flex; align-items: center; gap: 8px; border: none; padding: 11px 26px; border-radius: 8px; font-weight: 700; font-size: 0.92rem; cursor: pointer; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35);">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        Update Page
      </button>
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

  <!-- MAIN EDIT FORM -->
  <form id="editPageMainForm" method="POST" action="/admin/edit_page?slug=<?= urlencode($page['slug']) ?>" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
    <input type="hidden" name="action" value="save_page" />
    <input type="hidden" name="id" value="<?= htmlspecialchars($page['id'] ?? '') ?>" />
    <input type="hidden" name="old_slug" value="<?= htmlspecialchars($page['slug']) ?>" />

    <div class="wp-page-editor-layout">

      <!-- ===================================================================
           LEFT COLUMN: TITLE, PERMALINK, BANNERS, CONTENT & YOAST SEO
           =================================================================== -->
      <div>

        <!-- 1. PAGE TITLE & PERMALINK -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 22px; margin-bottom: 22px;">
          <input type="text" id="mainPageTitle" name="title" value="<?= htmlspecialchars($page['title'] ?? '') ?>" placeholder="Enter title here" required style="width: 100%; box-sizing: border-box; padding: 12px 16px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 1.35rem; font-weight: 700; margin-bottom: 12px;" oninput="syncMainTitle(this.value)" />
          
          <!-- Permalink Display (WordPress Pattern) -->
          <div style="display: flex; align-items: center; gap: 8px; font-size: 0.84rem; color: #94a3b8; flex-wrap: wrap;">
            <strong style="color: #cbd5e1;">Permalink:</strong>
            <span style="color: #64748b;">https://wetruenorthgroup.com</span>
            <input type="text" id="mainPageSlug" name="slug" value="<?= htmlspecialchars($page['slug']) ?>" style="padding: 4px 10px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #38bdf8; font-family: monospace; font-size: 0.84rem; min-width: 160px;" oninput="syncMainSlug(this.value)" />
            <a href="<?= htmlspecialchars($page['slug']) ?>" target="_blank" rel="noopener" style="color: #38bdf8; text-decoration: none; font-size: 0.8rem; margin-left: 6px;">View Page &nearr;</a>
          </div>
        </div>

        <!-- 2. AB PAGE BANNER IMAGES METABOX (MATCHING USER SCREENSHOT) -->
        <div class="wp-box">
          <div class="wp-box-header">
            <div style="display: flex; align-items: center; gap: 8px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              <span>AB Page Banner Images</span>
            </div>
          </div>
          <div class="wp-box-body">
            
            <!-- Banner Img Thumbnail & Upload -->
            <div style="margin-bottom: 18px;">
              <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 8px;">
                Banner Img
              </label>
              <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                <div style="width: 220px; height: 110px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                  <img id="bannerImgPreview" src="<?= htmlspecialchars($page['banner_image'] ?: '/hero_bottles_banner.webp') ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Banner Preview" onerror="this.src='/hero_bottles_banner.webp';" />
                </div>
                <div style="flex: 1; min-width: 240px;">
                  <input type="text" id="bannerImgInput" name="banner_image" value="<?= htmlspecialchars($page['banner_image'] ?? '') ?>" placeholder="/hero_bottles_banner.webp" class="form-control-smart" style="width: 100%; box-sizing: border-box; margin-bottom: 8px;" oninput="document.getElementById('bannerImgPreview').src=this.value;" />
                  <div style="display: flex; gap: 10px; align-items: center;">
                    <label style="padding: 6px 12px; background: #334155; color: #f8fafc; border-radius: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                      Upload New File
                      <input type="file" name="banner_image_file" accept="image/*" style="display: none;" onchange="previewUploadFile(this, 'bannerImgPreview', 'bannerImgInput')" />
                    </label>
                    <a href="/admin/media" target="_blank" style="color: #38bdf8; font-size: 0.78rem; text-decoration: none;">Media Library</a>
                  </div>
                </div>
              </div>
            </div>

            <!-- Share Title -->
            <div style="margin-bottom: 18px;">
              <label for="share_title" style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
                Share Title
              </label>
              <input type="text" id="share_title" name="share_title" value="<?= htmlspecialchars($page['share_title'] ?? '') ?>" placeholder="e.g. About Us | TrueNorth Group | Wetruenorthgroup Group" class="form-control-smart" style="width: 100%; box-sizing: border-box;" />
            </div>

            <!-- Meta Description / Banner Subtitle -->
            <div style="margin-bottom: 18px;">
              <label for="banner_subtitle" style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
                Meta Description / Banner Subtitle
              </label>
              <textarea id="banner_subtitle" name="banner_subtitle" rows="3" class="form-control-smart" style="width: 100%; box-sizing: border-box;"><?= htmlspecialchars($page['banner_subtitle'] ?? '') ?></textarea>
            </div>

            <!-- Banner Video -->
            <div>
              <label for="banner_video" style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
                Banner Video (Optional URL or Embed)
              </label>
              <input type="text" id="banner_video" name="banner_video" value="<?= htmlspecialchars($page['banner_video'] ?? '') ?>" placeholder="https://..." class="form-control-smart" style="width: 100%; box-sizing: border-box;" />
            </div>

          </div>
        </div>

        <!-- 3. TEMPLATE SECTIONS METABOX -->
        <div class="wp-box">
          <div class="wp-box-header">
            <div style="display: flex; align-items: center; gap: 8px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
              <span><?= htmlspecialchars($page['title']) ?> &mdash; Template &amp; Content</span>
            </div>
            <span style="font-size: 0.75rem; color: #38bdf8; background: rgba(56, 189, 248, 0.15); padding: 3px 10px; border-radius: 12px; font-weight: 700; text-transform: uppercase;">
              Template: <?= htmlspecialchars($page['template'] ?? 'default') ?>
            </span>
          </div>
          <div class="wp-box-body">
            
            <div style="margin-bottom: 18px;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <label style="font-size: 0.85rem; font-weight: 700; color: #cbd5e1;">Top Content (HTML / WYSIWYG)</label>
                <div style="font-size: 0.74rem; color: #94a3b8;">Formatted HTML or description markup</div>
              </div>
              <textarea id="pageContent" name="content" rows="8" placeholder="Enter paragraph content or template HTML overrides..." class="form-control-smart" style="width: 100%; box-sizing: border-box; font-family: monospace; font-size: 0.85rem; line-height: 1.5;"><?= htmlspecialchars($page['content'] ?? '') ?></textarea>
            </div>

            <div style="background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 14px 18px; font-size: 0.82rem; color: #94a3b8; display: flex; align-items: center; justify-content: space-between;">
              <span>The structural view is served from <code style="color: #38bdf8;">app/Views/pages/<?= htmlspecialchars($page['template'] ?: 'about') ?>.php</code></span>
              <a href="<?= htmlspecialchars($page['slug']) ?>" target="_blank" rel="noopener" style="color: #38bdf8; text-decoration: none; font-weight: 600;">Preview Live &nearr;</a>
            </div>

          </div>
        </div>

        <!-- 4. THE FULL WORDPRESS YOAST SEO METABOX FOR PAGES -->
        <?php include __DIR__ . '/partials/page_seo_card.php'; ?>

      </div>

      <!-- ===================================================================
           RIGHT COLUMN: PUBLISH BOX, PAGE ATTRIBUTES & FEATURED IMAGE
           =================================================================== -->
      <div>

        <!-- PUBLISH METABOX (MATCHING SCREENSHOT) -->
        <div class="wp-sidebar-box">
          <div class="wp-sidebar-box-header">
            <span>Publish</span>
            <span style="font-size: 0.72rem; color: #34d399; font-weight: 700;">● Active</span>
          </div>
          <div class="wp-sidebar-box-body">
            
            <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.84rem; color: #94a3b8; margin-bottom: 18px; border-bottom: 1px solid #334155; padding-bottom: 14px;">
              <div style="display: flex; justify-content: space-between;">
                <span>Status:</span>
                <strong style="color: #f8fafc; text-transform: capitalize;"><?= htmlspecialchars($page['status'] ?? 'published') ?></strong>
              </div>
              <div style="display: flex; justify-content: space-between;">
                <span>Visibility:</span>
                <strong style="color: #f8fafc;">Public</strong>
              </div>
              <div style="display: flex; justify-content: space-between;">
                <span>Last Updated:</span>
                <strong style="color: #cbd5e1;"><?= date('M d, Y', strtotime($page['updated_at'] ?? 'now')) ?></strong>
              </div>
              
              <!-- Yoast SEO Status In Publish Box (Traffic Lights) -->
              <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 6px; border-top: 1px dashed #334155;">
                <span style="font-weight: 600;">Yoast SEO:</span>
                <div style="display: flex; align-items: center; gap: 6px;">
                  <span id="sideSeoDot" style="width: 9px; height: 9px; border-radius: 50%; background: #22c55e;"></span>
                  <strong id="sideSeoText" style="color: #f8fafc; font-size: 0.8rem;">Good</strong>
                </div>
              </div>

              <!-- Readability Status In Publish Box -->
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: 600;">Readability:</span>
                <div style="display: flex; align-items: center; gap: 6px;">
                  <span style="width: 9px; height: 9px; border-radius: 50%; background: #38bdf8;"></span>
                  <strong style="color: #f8fafc; font-size: 0.8rem;"><?= htmlspecialchars($page['readability'] ?? 'Good') ?></strong>
                </div>
              </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px;">
              <?php if (($page['slug'] ?? '') !== '/'): ?>
                <button type="button" onclick="confirmDeleteThisPage()" style="color: #f87171; background: transparent; border: none; font-size: 0.82rem; cursor: pointer; text-decoration: underline; padding: 0;">
                  Move to Trash
                </button>
              <?php else: ?>
                <a href="/admin/pages" style="color: #94a3b8; font-size: 0.8rem; text-decoration: none;">Cancel</a>
              <?php endif; ?>
              <button type="submit" class="tn-btn-gold" style="flex: 1; border: none; padding: 10px 16px; border-radius: 8px; font-weight: 700; font-size: 0.88rem; cursor: pointer; text-align: center; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35);">
                Update Page
              </button>
            </div>

          </div>
        </div>

        <!-- PAGE ATTRIBUTES METABOX (MATCHING SCREENSHOT) -->
        <div class="wp-sidebar-box">
          <div class="wp-sidebar-box-header">
            <span>Page Attributes</span>
          </div>
          <div class="wp-sidebar-box-body">
            
            <div style="margin-bottom: 14px;">
              <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
                Parent
              </label>
              <select name="parent_page" class="form-control-smart" style="width: 100%; box-sizing: border-box;">
                <option value="">(no parent)</option>
                <option value="home">Home</option>
                <option value="products">Products Catalogue</option>
              </select>
            </div>

            <div style="margin-bottom: 14px;">
              <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
                Template
              </label>
              <select name="template" class="form-control-smart" style="width: 100%; box-sizing: border-box;">
                <option value="about" <?= ($page['template'] ?? '') === 'about' ? 'selected' : '' ?>>About</option>
                <option value="sustainability" <?= ($page['template'] ?? '') === 'sustainability' ? 'selected' : '' ?>>Sustainability</option>
                <option value="products" <?= ($page['template'] ?? '') === 'products' ? 'selected' : '' ?>>Products</option>
                <option value="custom" <?= ($page['template'] ?? '') === 'custom' ? 'selected' : '' ?>>Custom Solutions</option>
                <option value="faq" <?= ($page['template'] ?? '') === 'faq' ? 'selected' : '' ?>>FAQ</option>
                <option value="contact" <?= ($page['template'] ?? '') === 'contact' ? 'selected' : '' ?>>Contact</option>
                <option value="blog" <?= ($page['template'] ?? '') === 'blog' ? 'selected' : '' ?>>Blog</option>
                <option value="terms" <?= ($page['template'] ?? '') === 'terms' ? 'selected' : '' ?>>Terms &amp; Conditions</option>
                <option value="privacy" <?= ($page['template'] ?? '') === 'privacy' ? 'selected' : '' ?>>Privacy Policy</option>
                <option value="home" <?= ($page['template'] ?? '') === 'home' ? 'selected' : '' ?>>Homepage</option>
                <option value="default" <?= ($page['template'] ?? '') === 'default' ? 'selected' : '' ?>>Default Template</option>
              </select>
            </div>

            <div>
              <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
                Order
              </label>
              <input type="number" name="order_num" value="<?= (int)($page['order_num'] ?? 0) ?>" class="form-control-smart" style="width: 100%; box-sizing: border-box;" />
            </div>

          </div>
        </div>

        <!-- FEATURED IMAGE METABOX (MATCHING SCREENSHOT) -->
        <div class="wp-sidebar-box">
          <div class="wp-sidebar-box-header">
            <span>Featured Image</span>
          </div>
          <div class="wp-sidebar-box-body">
            <div style="width: 100%; height: 160px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
              <img id="featuredImgPreview" src="<?= htmlspecialchars($page['featured_image'] ?: '/hero_bottles_banner.webp') ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Featured Image Preview" onerror="this.src='/hero_bottles_banner.webp';" />
            </div>
            <input type="text" id="featuredImgInput" name="featured_image" value="<?= htmlspecialchars($page['featured_image'] ?? '') ?>" placeholder="/hero_bottles_banner.webp" class="form-control-smart" style="width: 100%; box-sizing: border-box; margin-bottom: 8px;" oninput="document.getElementById('featuredImgPreview').src=this.value;" />
            <div style="display: flex; gap: 8px;">
              <label style="flex: 1; text-align: center; padding: 6px 10px; background: #334155; color: #f8fafc; border-radius: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                Set featured image
                <input type="file" name="featured_image_file" accept="image/*" style="display: none;" onchange="previewUploadFile(this, 'featuredImgPreview', 'featuredImgInput')" />
              </label>
            </div>
          </div>
        </div>

      </div>

    </div>

  </form>

</div>

<script>
function syncMainTitle(val) {
  var t = val.trim();
  var seoT = document.getElementById('page_seo_title');
  if (seoT && (!seoT.value || seoT.dataset.touched !== '1')) {
    seoT.value = t + ' — TrueNorth Group';
  }
  var shareT = document.getElementById('share_title');
  if (shareT && (!shareT.value || shareT.dataset.touched !== '1')) {
    shareT.value = t + ' | TrueNorth Group | Wetruenorthgroup Group';
  }
  if (typeof runPageAnalysis === 'function') runPageAnalysis();
}

function syncMainSlug(val) {
  var clean = val.replace(/^\/+/, '').trim();
  var seoSlug = document.getElementById('page_slug_seo');
  if (seoSlug && seoSlug.value !== clean) {
    seoSlug.value = clean;
  }
  var serpUrl = document.getElementById('wpp-serp-url');
  if (serpUrl) serpUrl.innerHTML = 'https://wetruenorthgroup.com &rsaquo; ' + (clean || 'page');
}

function previewUploadFile(input, imgId, inputId) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      var img = document.getElementById(imgId);
      if (img) img.src = e.target.result;
      var txt = document.getElementById(inputId);
      if (txt) txt.value = 'File selected: ' + input.files[0].name;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function confirmDeleteThisPage() {
  if (confirm("Are you sure you want to permanently delete this page and its SEO metadata?")) {
    var f = document.createElement('form');
    f.method = 'POST';
    f.action = '/admin/edit_page?slug=<?= urlencode($page['slug']) ?>';
    f.innerHTML = `
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
      <input type="hidden" name="action" value="delete_page" />
      <input type="hidden" name="slug" value="<?= htmlspecialchars(addslashes($page['slug'])) ?>" />
    `;
    document.body.appendChild(f);
    f.submit();
  }
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

