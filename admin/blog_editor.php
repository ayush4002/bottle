<?php
// ==============================================================================
// TRUENORTH CMS — WORDPRESS STYLE BLOG / POST EDITOR
// Complete WordPress Post experience matching Yoast SEO, Categories, Tags & Banners
// ==============================================================================

require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/BlogModel.php';
require_once dirname(__DIR__) . '/app/Models/SeoModel.php';

checkAdminAuth();
BlogModel::initStorage();
SeoModel::init();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = null;
$isEdit = false;

if ($id > 0) {
    $post = BlogModel::getPostById($id);
    if ($post) $isEdit = true;
}

// Defaults
$title = $post['title'] ?? 'Choosing the Right Packaging for Cosmetics: A Comprehensive Guide';
$slug = $post['slug'] ?? 'choosing-the-right-packaging-for-cosmetics-a-comprehensive-guide';
$excerpt = $post['excerpt'] ?? 'A comprehensive technical guide to selecting high-clarity PET bottles, luxury acrylic jars, and lotion dispensing pumps for cosmetic and personal care brands.';
$content = $post['content'] ?? '';
$featuredImage = $post['featured_image'] ?? '/hero_bottles_banner.webp';
$category = $post['category'] ?? 'Cosmetic Packaging';
$tags = $post['tags'] ?? 'cosmetics, packaging, PET bottles, primary packaging, jars, pumps';
$authorName = $post['author_name'] ?? 'TrueNorth Technical Division';
$authorRole = $post['author_role'] ?? 'Senior Packaging Materials Specialist';
$readTime = $post['read_time'] ?? '7 min read';
$status = $post['status'] ?? 'published';
$publishedAt = !empty($post['published_at']) ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : date('Y-m-d\TH:i');

$existingCategories = [
    'Cosmetic Packaging',
    'Dispensing Solutions',
    'Sustainable Packaging',
    'Pharma Primary Packaging',
    'Mould Engineering & Tech',
    'Industry Standards & ISO'
];

$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';
$csrfToken = generateCsrfToken();
?>

<style>
.wp-post-editor-layout {
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
  .wp-post-editor-layout {
    grid-template-columns: 1fr;
  }
}
</style>

<div style="max-width: 1360px; margin: 0 auto;">

  <!-- TOP BREADCRUMB & HEADER -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; flex-wrap: wrap; gap: 14px;">
    <div>
      <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 0.88rem; color: #94a3b8;">
        <a href="/admin/blog" style="color: #94a3b8; text-decoration: none;">&larr; Posts</a>
        <span>/</span>
        <span style="color: #38bdf8; font-weight: 600;">Edit Post</span>
      </div>
      <div style="display: flex; align-items: center; gap: 14px;">
        <h1 style="font-size: 1.8rem; font-weight: 800; color: #f8fafc; margin: 0;">
          Edit Post
        </h1>
        <a href="/admin/blog_editor" class="tn-btn-outline" style="padding: 5px 12px; font-size: 0.8rem; border-radius: 6px; color: #cbd5e1; border: 1px solid #475569; text-decoration: none; font-weight: 600;">
          Add New
        </a>
      </div>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
      <?php if (!empty($slug)): ?>
        <a href="/blog/<?= htmlspecialchars($slug) ?>" target="_blank" rel="noopener" style="display: inline-flex; align-items: center; gap: 6px; background: #0f172a; color: #38bdf8; border: 1px solid #0284c7; text-decoration: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; font-size: 0.88rem;">
          View Post &nearr;
        </a>
      <?php endif; ?>
      <button type="button" onclick="document.getElementById('postMainForm').submit();" class="tn-btn-gold" style="display: inline-flex; align-items: center; gap: 8px; border: none; padding: 11px 26px; border-radius: 8px; font-weight: 700; font-size: 0.92rem; cursor: pointer; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35);">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        <?= $isEdit ? 'Update Post' : 'Publish Post' ?>
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

  <!-- MAIN POST EDIT FORM -->
  <form id="postMainForm" method="POST" action="/admin/save_blog" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
    <input type="hidden" name="id" value="<?= (int)($post['id'] ?? 0) ?>" />
    <input type="hidden" name="old_slug" value="<?= htmlspecialchars($slug) ?>" />

    <div class="wp-post-editor-layout">

      <!-- ===================================================================
           LEFT COLUMN: TITLE, PERMALINK, CONTENT, THUMBNAIL, YOAST SEO & EXCERPT
           =================================================================== -->
      <div>

        <!-- 1. POST TITLE & PERMALINK -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 22px; margin-bottom: 22px;">
          <input type="text" id="postTitle" name="title" value="<?= htmlspecialchars($title) ?>" placeholder="Enter title here" required style="width: 100%; box-sizing: border-box; padding: 12px 16px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 1.35rem; font-weight: 700; margin-bottom: 12px;" oninput="syncPostTitle(this.value)" />
          
          <!-- Permalink Display (WordPress Pattern) -->
          <div style="display: flex; align-items: center; gap: 8px; font-size: 0.84rem; color: #94a3b8; flex-wrap: wrap;">
            <strong style="color: #cbd5e1;">Permalink:</strong>
            <span style="color: #64748b;">https://wetruenorthgroup.com/blog/</span>
            <input type="text" id="postSlug" name="slug" value="<?= htmlspecialchars($slug) ?>" style="padding: 4px 10px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #38bdf8; font-family: monospace; font-size: 0.84rem; min-width: 260px;" oninput="syncPostSlug(this.value)" />
            <button type="button" onclick="autoGenerateSlug()" style="padding: 4px 10px; background: #334155; color: #cbd5e1; border: none; border-radius: 4px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
              Auto Slug
            </button>
            <?php if (!empty($slug)): ?>
              <a href="/blog/<?= htmlspecialchars($slug) ?>" target="_blank" rel="noopener" style="color: #38bdf8; text-decoration: none; font-size: 0.8rem; margin-left: 6px;">View Post &nearr;</a>
            <?php endif; ?>
          </div>
        </div>

        <!-- 2. ARTICLE CONTENT EDITOR WITH TOOLBAR -->
        <div class="wp-box">
          <div class="wp-box-header">
            <div style="display: flex; align-items: center; gap: 8px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
              <span>Article Content</span>
            </div>
            <div style="font-size: 0.8rem; color: #94a3b8; display: flex; gap: 12px; align-items: center;">
              <span id="wordCountLabel">0 words</span>
              <span>&bull;</span>
              <span id="readingTimeLabel"><?= htmlspecialchars($readTime) ?></span>
            </div>
          </div>
          <div class="wp-box-body">

            <!-- Formatting Shortcut Toolbar -->
            <div style="display: flex; flex-wrap: wrap; gap: 6px; padding: 10px; background: #0f172a; border: 1px solid #475569; border-bottom: none; border-radius: 8px 8px 0 0;">
              <button type="button" onclick="insertTag('<h2>', '</h2>')" style="padding: 5px 10px; background: #1e293b; border: 1px solid #334155; color: #f8fafc; border-radius: 4px; font-weight: 700; cursor: pointer; font-size: 0.8rem;">H2</button>
              <button type="button" onclick="insertTag('<h3>', '</h3>')" style="padding: 5px 10px; background: #1e293b; border: 1px solid #334155; color: #f8fafc; border-radius: 4px; font-weight: 700; cursor: pointer; font-size: 0.8rem;">H3</button>
              <button type="button" onclick="insertTag('<strong>', '</strong>')" style="padding: 5px 10px; background: #1e293b; border: 1px solid #334155; color: #f8fafc; border-radius: 4px; font-weight: 700; cursor: pointer; font-size: 0.8rem;">B</button>
              <button type="button" onclick="insertTag('<em>', '</em>')" style="padding: 5px 10px; background: #1e293b; border: 1px solid #334155; color: #f8fafc; border-radius: 4px; font-style: italic; cursor: pointer; font-size: 0.8rem;">I</button>
              <button type="button" onclick="insertTag('<p>', '</p>')" style="padding: 5px 10px; background: #1e293b; border: 1px solid #334155; color: #f8fafc; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">&lt;p&gt;</button>
              <button type="button" onclick="insertBulletListTemplate()" style="padding: 5px 10px; background: #1e293b; border: 1px solid #334155; color: #f8fafc; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">Bullet List</button>
              <button type="button" onclick="insertCalloutTemplate()" style="padding: 5px 10px; background: rgba(184, 137, 46, 0.2); border: 1px solid rgba(184, 137, 46, 0.4); color: #d4a754; border-radius: 4px; font-weight: 600; cursor: pointer; font-size: 0.8rem;">+ Callout Box</button>
            </div>

            <textarea id="postContent" name="content" rows="18" placeholder="Write comprehensive packaging insights, engineering guides, or case studies here..." required style="width: 100%; box-sizing: border-box; padding: 14px 16px; background: #0f172a; border: 1px solid #475569; border-radius: 0 0 8px 8px; color: #fff; font-size: 0.94rem; line-height: 1.6; font-family: ui-monospace, Menlo, Monaco, Consolas, monospace;" oninput="updateContentStats(); if (typeof runWpbSeoAnalysis === 'function') runWpbSeoAnalysis();"><?= htmlspecialchars($content) ?></textarea>

          </div>
        </div>

        <!-- 3. ARTICLE BANNER / COVER IMAGE PREVIEW -->
        <div class="wp-box">
          <div class="wp-box-header">
            <span>Post Banner &amp; Header Media</span>
          </div>
          <div class="wp-box-body">
            <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
              <div style="width: 240px; height: 120px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                <img id="postBannerPreview" src="<?= htmlspecialchars($featuredImage) ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Banner Preview" onerror="this.src='/hero_bottles_banner.webp';" />
              </div>
              <div style="flex: 1; min-width: 240px;">
                <input type="text" id="featuredImgInput" name="featured_image" value="<?= htmlspecialchars($featuredImage) ?>" placeholder="/hero_bottles_banner.webp" class="form-control-smart" style="width: 100%; box-sizing: border-box; margin-bottom: 8px;" oninput="updateBannerPreviews(this.value)" />
                <div style="display: flex; gap: 10px; align-items: center;">
                  <label style="padding: 6px 12px; background: #334155; color: #f8fafc; border-radius: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                    Upload New Image
                    <input type="file" name="featured_image_file" accept="image/*" style="display: none;" onchange="previewUploadImage(this)" />
                  </label>
                  <a href="/admin/media" target="_blank" style="color: #38bdf8; font-size: 0.78rem; text-decoration: none;">Media Library</a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 4. WORDPRESS YOAST SEO 5-TAB SUITE METABOX -->
        <?php include __DIR__ . '/partials/blog_seo_card.php'; ?>

        <!-- 5. EXCERPT METABOX (MATCHING SCREENSHOT) -->
        <div class="wp-box">
          <div class="wp-box-header">
            <span>Excerpt</span>
          </div>
          <div class="wp-box-body">
            <p style="font-size: 0.78rem; color: #94a3b8; margin: 0 0 8px 0;">Excerpts are optional hand-crafted summaries of your content that can be used in your theme and search listings.</p>
            <textarea id="postExcerpt" name="excerpt" rows="3" class="form-control-smart" style="width: 100%; box-sizing: border-box; line-height: 1.5;" oninput="if (typeof runWpbSeoAnalysis === 'function') runWpbSeoAnalysis();"><?= htmlspecialchars($excerpt) ?></textarea>
          </div>
        </div>

        <!-- 6. DISCUSSION METABOX (MATCHING SCREENSHOT) -->
        <div class="wp-box">
          <div class="wp-box-header">
            <span>Discussion</span>
          </div>
          <div class="wp-box-body" style="display: flex; gap: 24px; flex-wrap: wrap;">
            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: #cbd5e1; cursor: pointer;">
              <input type="checkbox" name="allow_comments" value="1" checked style="width: 16px; height: 16px;" />
              <span>Allow comments</span>
            </label>
            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: #cbd5e1; cursor: pointer;">
              <input type="checkbox" name="allow_trackbacks" value="1" checked style="width: 16px; height: 16px;" />
              <span>Allow trackbacks and pingbacks on this page</span>
            </label>
          </div>
        </div>

      </div>

      <!-- ===================================================================
           RIGHT COLUMN: PUBLISH BOX, FORMAT, CATEGORIES, TAGS & FEATURED IMAGE
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
                <strong style="color: #f8fafc; text-transform: capitalize;"><?= htmlspecialchars($status) ?></strong>
              </div>
              <div style="display: flex; justify-content: space-between;">
                <span>Visibility:</span>
                <strong style="color: #f8fafc;">Public</strong>
              </div>
              <div style="display: flex; justify-content: space-between;">
                <span>Revisions:</span>
                <strong style="color: #cbd5e1;">15</strong>
              </div>
              <div style="display: flex; justify-content: space-between;">
                <span>Published on:</span>
                <strong style="color: #cbd5e1;"><?= date('M d, Y \a\t H:i', strtotime($post['published_at'] ?? 'now')) ?></strong>
              </div>
              
              <!-- Yoast SEO Indicator in Publish Box -->
              <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 6px; border-top: 1px dashed #334155;">
                <span style="font-weight: 600;">Yoast SEO:</span>
                <div style="display: flex; align-items: center; gap: 6px;">
                  <span id="sideBlogSeoDot" style="width: 9px; height: 9px; border-radius: 50%; background: #22c55e;"></span>
                  <strong id="sideBlogSeoText" style="color: #f8fafc; font-size: 0.8rem;">Good</strong>
                </div>
              </div>

              <!-- Readability Indicator in Publish Box -->
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: 600;">Readability:</span>
                <div style="display: flex; align-items: center; gap: 6px;">
                  <span id="sideBlogReadDot" style="width: 9px; height: 9px; border-radius: 50%; background: #eab308;"></span>
                  <strong id="sideBlogReadText" style="color: #f8fafc; font-size: 0.8rem;">OK</strong>
                </div>
              </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px;">
              <a href="/admin/blog" style="color: #ef4444; font-size: 0.8rem; text-decoration: none; font-weight: 600;">Cancel</a>
              <button type="submit" class="tn-btn-gold" style="flex: 1; border: none; padding: 10px 16px; border-radius: 8px; font-weight: 700; font-size: 0.88rem; cursor: pointer; text-align: center; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35);">
                <?= $isEdit ? 'Update' : 'Publish' ?>
              </button>
            </div>

          </div>
        </div>

        <!-- FORMAT BOX (MATCHING SCREENSHOT) -->
        <div class="wp-sidebar-box">
          <div class="wp-sidebar-box-header">
            <span>Format</span>
          </div>
          <div class="wp-sidebar-box-body" style="display: flex; flex-direction: column; gap: 8px; font-size: 0.84rem; color: #cbd5e1;">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
              <input type="radio" name="post_format" value="standard" checked />
              <span>Standard</span>
            </label>
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
              <input type="radio" name="post_format" value="aside" />
              <span>Aside</span>
            </label>
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
              <input type="radio" name="post_format" value="image" />
              <span>Image</span>
            </label>
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
              <input type="radio" name="post_format" value="video" />
              <span>Video</span>
            </label>
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
              <input type="radio" name="post_format" value="quote" />
              <span>Quote</span>
            </label>
          </div>
        </div>

        <!-- CATEGORIES BOX (MATCHING SCREENSHOT) -->
        <div class="wp-sidebar-box">
          <div class="wp-sidebar-box-header">
            <span>Categories</span>
          </div>
          <div class="wp-sidebar-box-body">
            <div style="display: flex; gap: 12px; margin-bottom: 12px; font-size: 0.8rem; border-bottom: 1px solid #334155; padding-bottom: 6px;">
              <span style="color: #38bdf8; font-weight: 700;">All Categories</span>
              <span style="color: #64748b;">Most Used</span>
            </div>
            <div style="max-height: 160px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; font-size: 0.85rem; color: #cbd5e1; margin-bottom: 12px;">
              <?php foreach ($existingCategories as $catItem): ?>
                <?php $checked = ($category === $catItem) || (is_array($category) && in_array($catItem, $category)); ?>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                  <input type="checkbox" name="categories[]" value="<?= htmlspecialchars($catItem) ?>" <?= $checked ? 'checked' : '' ?> onchange="syncPrimaryCategory(this)" />
                  <span><?= htmlspecialchars($catItem) ?></span>
                </label>
              <?php endforeach; ?>
            </div>
            <input type="hidden" id="primaryCategoryInput" name="category" value="<?= htmlspecialchars(is_array($category) ? ($category[0] ?? 'Cosmetic Packaging') : $category) ?>" />
            <a href="javascript:void(0)" onclick="document.getElementById('newCatRow').style.display='flex';" style="color: #38bdf8; font-size: 0.8rem; text-decoration: none;">+ Add New Category</a>
            <div id="newCatRow" style="display: none; gap: 6px; margin-top: 8px;">
              <input type="text" id="newCatInput" placeholder="New category name" class="form-control-smart" style="flex: 1; font-size: 0.8rem; padding: 6px;" />
              <button type="button" onclick="addNewCategoryInline()" style="padding: 6px 10px; background: #334155; color: #fff; border: none; border-radius: 4px; font-size: 0.78rem; cursor: pointer;">Add</button>
            </div>
          </div>
        </div>

        <!-- TAGS BOX (MATCHING SCREENSHOT) -->
        <div class="wp-sidebar-box">
          <div class="wp-sidebar-box-header">
            <span>Tags</span>
          </div>
          <div class="wp-sidebar-box-body">
            <div style="display: flex; gap: 6px; margin-bottom: 10px;">
              <input type="text" id="tagAddInput" placeholder="Add new tag" class="form-control-smart" style="flex: 1; font-size: 0.82rem; padding: 6px 10px;" />
              <button type="button" onclick="addTagInline()" style="padding: 6px 12px; background: #334155; color: #fff; border: none; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer;">Add</button>
            </div>
            <div style="font-size: 0.74rem; color: #94a3b8; margin-bottom: 10px;">Separate tags with commas</div>
            <input type="text" id="postTags" name="tags" value="<?= htmlspecialchars($tags) ?>" class="form-control-smart" style="width: 100%; box-sizing: border-box; font-size: 0.82rem; margin-bottom: 8px;" />
            <div id="tagPillsWrap" style="display: flex; flex-wrap: wrap; gap: 4px;"></div>
          </div>
        </div>

        <!-- FEATURED IMAGE BOX (MATCHING SCREENSHOT) -->
        <div class="wp-sidebar-box">
          <div class="wp-sidebar-box-header">
            <span>Featured Image</span>
          </div>
          <div class="wp-sidebar-box-body">
            <div style="width: 100%; height: 160px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
              <img id="featuredImgPreview" src="<?= htmlspecialchars($featuredImage) ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Featured Image" onerror="this.src='/hero_bottles_banner.webp';" />
            </div>
            <div style="font-size: 0.78rem; color: #38bdf8; margin-bottom: 8px; cursor: pointer;" onclick="document.getElementById('featuredFileInput').click();">
              Click image to edit or update
            </div>
            <input type="file" id="featuredFileInput" name="featured_image_file" accept="image/*" style="display: none;" onchange="previewUploadImage(this)" />
            <a href="javascript:void(0)" onclick="removeFeaturedImage()" style="color: #ef4444; font-size: 0.76rem; text-decoration: none;">Remove featured image</a>
          </div>
        </div>

      </div>

    </div>

  </form>

</div>

<script>
function syncPostTitle(val) {
  const t = val.trim();
  const seoT = document.getElementById('wpbTitle');
  if (seoT && (!seoT.value || seoT.dataset.touched !== '1')) {
    seoT.value = t + ' — TrueNorth Group';
  }
  const ogT = document.getElementById('wpbOgTitle');
  if (ogT && (!ogT.value || ogT.dataset.touched !== '1')) {
    ogT.value = t;
  }
  if (typeof runWpbSeoAnalysis === 'function') runWpbSeoAnalysis();
}

function syncPostSlug(val) {
  const clean = val.replace(/^\/+/, '').trim();
  const wpbSlug = document.getElementById('wpbSlugInput');
  if (wpbSlug && wpbSlug.value !== clean) wpbSlug.value = clean;
  const serpUrl = document.getElementById('wpbserp-url-display');
  if (serpUrl) serpUrl.textContent = 'https://wetruenorthgroup.com › blog › ' + (clean || '...');
  if (typeof runWpbSeoAnalysis === 'function') runWpbSeoAnalysis();
}

function autoGenerateSlug() {
  const title = document.getElementById('postTitle').value;
  if (!title) return;
  const slug = title.toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
  document.getElementById('postSlug').value = slug;
  syncPostSlug(slug);
}

function insertTag(startTag, endTag) {
  const el = document.getElementById('postContent');
  const start = el.selectionStart;
  const end = el.selectionEnd;
  const selText = el.value.substring(start, end);
  const replacement = startTag + selText + endTag;
  el.value = el.value.substring(0, start) + replacement + el.value.substring(end);
  el.focus();
  el.selectionStart = start + startTag.length;
  el.selectionEnd = el.selectionStart + selText.length;
  updateContentStats();
  if (typeof runWpbSeoAnalysis === 'function') runWpbSeoAnalysis();
}

function insertBulletListTemplate() {
  insertTag('', '\n<ul>\n  <li>Technical parameter or benefit point</li>\n</ul>\n');
}

function insertCalloutTemplate() {
  insertTag('', '\n<div class="blog-callout-box">\n  <h4>Engineering Note</h4>\n  <p>Specifications...</p>\n</div>\n');
}

function updateContentStats() {
  const text = document.getElementById('postContent').value || '';
  const clean = text.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
  const words = clean ? clean.split(/\s+/).filter(Boolean).length : 0;
  const wordLabel = document.getElementById('wordCountLabel');
  if (wordLabel) wordLabel.textContent = words + ' words';
  
  const mins = Math.max(1, Math.ceil(words / 200));
  const rtLabel = document.getElementById('readingTimeLabel');
  if (rtLabel) rtLabel.textContent = mins + ' min read';
}

function updateBannerPreviews(src) {
  const p1 = document.getElementById('postBannerPreview');
  const p2 = document.getElementById('featuredImgPreview');
  const p3 = document.getElementById('wpbserp-thumb-img');
  if (p1) p1.src = src;
  if (p2) p2.src = src;
  if (p3) p3.src = src;
}

function previewUploadImage(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      updateBannerPreviews(e.target.result);
      const txt = document.getElementById('featuredImgInput');
      if (txt) txt.value = 'Uploaded: ' + input.files[0].name;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function removeFeaturedImage() {
  updateBannerPreviews('/hero_bottles_banner.webp');
  const txt = document.getElementById('featuredImgInput');
  if (txt) txt.value = '';
}

function syncPrimaryCategory(cb) {
  if (cb.checked) {
    document.getElementById('primaryCategoryInput').value = cb.value;
  }
}

function addNewCategoryInline() {
  const inp = document.getElementById('newCatInput');
  const val = (inp.value || '').trim();
  if (!val) return;
  const list = document.querySelector('.wp-sidebar-box-body label').parentElement;
  const lbl = document.createElement('label');
  lbl.style.cssText = 'display:flex;align-items:center;gap:8px;cursor:pointer;';
  lbl.innerHTML = '<input type="checkbox" name="categories[]" value="' + val + '" checked onchange="syncPrimaryCategory(this)" /><span>' + val + '</span>';
  list.appendChild(lbl);
  document.getElementById('primaryCategoryInput').value = val;
  inp.value = '';
  document.getElementById('newCatRow').style.display = 'none';
}

function renderTagPills() {
  const tagsInput = document.getElementById('postTags');
  const wrap = document.getElementById('tagPillsWrap');
  if (!tagsInput || !wrap) return;
  const list = tagsInput.value.split(',').map(s => s.trim()).filter(Boolean);
  wrap.innerHTML = list.map(t => '<span style="background:#0f172a;color:#38bdf8;border:1px solid #334155;padding:2px 8px;border-radius:4px;font-size:0.75rem;">' + t + '</span>').join('');
}

function addTagInline() {
  const inp = document.getElementById('tagAddInput');
  const val = (inp.value || '').trim();
  if (!val) return;
  const tagsInput = document.getElementById('postTags');
  let cur = tagsInput.value.split(',').map(s => s.trim()).filter(Boolean);
  if (!cur.includes(val)) cur.push(val);
  tagsInput.value = cur.join(', ');
  inp.value = '';
  renderTagPills();
}

document.addEventListener('DOMContentLoaded', function() {
  updateContentStats();
  renderTagPills();
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
