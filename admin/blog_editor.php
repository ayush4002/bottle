<?php
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/BlogModel.php';

checkAdminAuth();

BlogModel::initStorage();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = null;
$isEdit = false;

if ($id > 0) {
    $post = BlogModel::getPostById($id);
    if ($post) {
        $isEdit = true;
    }
}

// Defaults for new article
$title = $post['title'] ?? '';
$slug = $post['slug'] ?? '';
$excerpt = $post['excerpt'] ?? '';
$content = $post['content'] ?? '';
$featuredImage = $post['featured_image'] ?? '/vikaas_inputs/thumbnails/thumbnail_pet_bottles.webp';
$category = $post['category'] ?? 'Sustainable Packaging';
$tags = $post['tags'] ?? '';
$authorName = $post['author_name'] ?? 'TrueNorth Technical Division';
$authorRole = $post['author_role'] ?? 'Senior Packaging Engineer';
$readTime = $post['read_time'] ?? '5 min read';
$status = $post['status'] ?? 'published';
$publishedAt = !empty($post['published_at']) ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : date('Y-m-d\TH:i');

// SEO Fields
$metaTitle = $post['meta_title'] ?? '';
$metaDescription = $post['meta_description'] ?? '';
$metaKeywords = $post['meta_keywords'] ?? '';
$canonicalUrl = $post['canonical_url'] ?? '';
$ogTitle = $post['og_title'] ?? '';
$ogDescription = $post['og_description'] ?? '';
$ogImage = $post['og_image'] ?? '';
$twitterTitle = $post['twitter_title'] ?? '';
$twitterDescription = $post['twitter_description'] ?? '';
$twitterImage = $post['twitter_image'] ?? '';
$robotsMeta = $post['robots_meta'] ?? 'index, follow';
$schemaType = $post['schema_type'] ?? 'BlogPosting';

$existingCategories = [
    'Sustainable Packaging',
    'Pharma Primary Packaging',
    'Dispensing Solutions',
    'Cosmetics & Personal Care',
    'Mould Engineering & Tech',
    'Industry Standards & ISO'
];

$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';
?>

<style>
.blog-editor-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 24px;
  align-items: start;
}
.blog-sidebar-sticky {
  position: sticky;
  top: 20px;
  align-self: start;
}
@media (max-width: 1024px) {
  .blog-editor-grid {
    grid-template-columns: 1fr;
  }
  .blog-sidebar-sticky {
    position: static;
  }
}
</style>

<div style="max-width: 1280px; margin: 0 auto;">

  <!-- PAGE HEADER -->
  <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
      <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
        <a href="/admin/blog" style="color: #94a3b8; text-decoration: none; font-size: 0.88rem; display: flex; align-items: center; gap: 4px;">
          &larr; Back to Articles
        </a>
        <span style="color: #475569;">/</span>
        <span style="color: #38bdf8; font-size: 0.88rem; font-weight: 600;"><?= $isEdit ? 'Edit Article' : 'New Article' ?></span>
      </div>
      <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0; color: #f8fafc;">
        <?= $isEdit ? 'Edit Article: ' . htmlspecialchars($title) : 'Write New Packaging Article &amp; Technical Guide' ?>
      </h1>
    </div>
    <div style="display: flex; gap: 10px;">
      <?php if ($isEdit && $status === 'published'): ?>
        <a href="/blog/<?= htmlspecialchars($slug) ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background: #334155; color: #fff; text-decoration: none; padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: 0.88rem;">
          View Live &nearr;
        </a>
      <?php endif; ?>
      <button type="button" onclick="document.getElementById('blogMainForm').submit();" class="tn-btn-gold" style="display: inline-flex; align-items: center; gap: 8px; border: none; padding: 11px 24px; border-radius: 8px; font-weight: 700; font-size: 0.92rem; cursor: pointer; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35);">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        <?= $isEdit ? 'UPDATE & SAVE ARTICLE' : 'PUBLISH ARTICLE TO LIVE SITE' ?>
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

  <!-- FORM WRAPPER (TWO COLUMNS) -->
  <form id="blogMainForm" method="POST" action="/admin/save_blog" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
    <input type="hidden" name="id" value="<?= $id ?>" />

    <div class="blog-editor-grid">
      
      <!-- LEFT COLUMN: CONTENT WRITING -->
      <div>
        
        <!-- ARTICLE TITLE & SLUG CARD -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
          <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em;">
            Article Title *
          </label>
          <input type="text" id="postTitle" name="title" value="<?= htmlspecialchars($title) ?>" placeholder="e.g., Selecting High-Clarity rPET for Cosmetics & Personal Care" required style="width: 100%; box-sizing: border-box; padding: 14px 16px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 1.25rem; font-weight: 700; margin-bottom: 14px;" oninput="syncSeoPreview()" />

          <!-- URL Slug Control -->
          <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-size: 0.84rem; color: #94a3b8; font-weight: 600;">URL Permalink:</span>
            <span style="font-size: 0.84rem; color: #64748b;">https://wetruenorthgroup.com/blog/</span>
            <input type="text" id="postSlug" name="slug" value="<?= htmlspecialchars($slug) ?>" placeholder="auto-generated-slug" style="flex: 1; min-width: 200px; box-sizing: border-box; padding: 6px 10px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #38bdf8; font-size: 0.84rem; font-family: monospace;" oninput="syncSeoPreview()" />
            <button type="button" onclick="autoGenerateSlug()" style="background: #334155; color: #cbd5e1; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
              Auto Slug
            </button>
          </div>
        </div>

        <!-- EXCERPT / LEAD SUMMARY -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <label style="font-size: 0.85rem; font-weight: 700; color: #cbd5e1; text-transform: uppercase; letter-spacing: 0.05em;">
              Excerpt / Lead Summary (1-2 sentences)
            </label>
            <span id="excerptCount" style="font-size: 0.78rem; color: #94a3b8;">0 / 180 chars</span>
          </div>
          <textarea id="postExcerpt" name="excerpt" rows="3" placeholder="Provide a concise, high-impact summary of this article to entice readers and search engines..." style="width: 100%; box-sizing: border-box; padding: 12px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.92rem; line-height: 1.5; resize: vertical;" oninput="updateExcerptCount(); syncSeoPreview();"><?= htmlspecialchars($excerpt) ?></textarea>
        </div>

        <!-- RICH CONTENT EDITOR WITH FORMATTING TOOLBAR -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
            <label style="font-size: 0.85rem; font-weight: 700; color: #cbd5e1; text-transform: uppercase; letter-spacing: 0.05em;">
              Article Body Content (HTML / Rich Text)
            </label>
            <div style="font-size: 0.8rem; color: #94a3b8; display: flex; gap: 12px;">
              <span id="wordCountLabel">0 words</span>
              <span>&bull;</span>
              <span id="readingTimeLabel">1 min read</span>
            </div>
          </div>

          <!-- Formatting Shortcut Toolbar -->
          <div style="display: flex; flex-wrap: wrap; gap: 6px; padding: 8px; background: #0f172a; border: 1px solid #475569; border-bottom: none; border-radius: 8px 8px 0 0;">
            <button type="button" onclick="insertH2()" style="padding: 5px 10px; background: #1e293b; border: 1px solid #334155; color: #f8fafc; border-radius: 4px; font-weight: 700; cursor: pointer; font-size: 0.8rem;">H2</button>
            <button type="button" onclick="insertH3()" style="padding: 5px 10px; background: #1e293b; border: 1px solid #334155; color: #f8fafc; border-radius: 4px; font-weight: 700; cursor: pointer; font-size: 0.8rem;">H3</button>
            <button type="button" onclick="insertBold()" style="padding: 5px 10px; background: #1e293b; border: 1px solid #334155; color: #f8fafc; border-radius: 4px; font-weight: 700; cursor: pointer; font-size: 0.8rem;">B</button>
            <button type="button" onclick="insertItalic()" style="padding: 5px 10px; background: #1e293b; border: 1px solid #334155; color: #f8fafc; border-radius: 4px; font-style: italic; cursor: pointer; font-size: 0.8rem;">I</button>
            <button type="button" onclick="insertParagraph()" style="padding: 5px 10px; background: #1e293b; border: 1px solid #334155; color: #f8fafc; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">&lt;p&gt;</button>
            <button type="button" onclick="insertBulletListSnippet()" style="padding: 5px 10px; background: #1e293b; border: 1px solid #334155; color: #f8fafc; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">Bullet List</button>
            <button type="button" onclick="insertQuoteSnippet()" style="padding: 5px 10px; background: #1e293b; border: 1px solid #334155; color: #f8fafc; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">Quote</button>
            <button type="button" onclick="insertCalloutSnippet()" style="padding: 5px 10px; background: rgba(184, 137, 46, 0.2); border: 1px solid rgba(184, 137, 46, 0.4); color: #d4a754; border-radius: 4px; font-weight: 600; cursor: pointer; font-size: 0.8rem;">+ Callout Card</button>
            <button type="button" onclick="insertTableSnippet()" style="padding: 5px 10px; background: #1e293b; border: 1px solid #334155; color: #38bdf8; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">+ Spec Table</button>
          </div>

          <textarea id="postContent" name="content" rows="18" placeholder="Write comprehensive packaging insights, engineering guides, or case studies here..." required style="width: 100%; box-sizing: border-box; padding: 14px 16px; background: #0f172a; border: 1px solid #475569; border-radius: 0 0 8px 8px; color: #fff; font-size: 0.94rem; line-height: 1.6; font-family: ui-monospace, Menlo, Monaco, Consolas, monospace;" oninput="updateContentStats(); checkSeoChecklist();"><?= htmlspecialchars($content) ?></textarea>
        </div>

        <!-- WORDPRESS YOAST & RANK MATH 5-TAB SEO SUITE -->
        <?php include __DIR__ . '/partials/blog_seo_card.php'; ?>
      </div>

      <!-- RIGHT COLUMN: PUBLISHING & METADATA -->
      <div class="blog-sidebar-sticky">
        
        <!-- PUBLISHING STATUS & ACTIONS -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
          <h3 style="margin: 0 0 16px 0; font-size: 1.05rem; color: #f8fafc; font-weight: 700; border-bottom: 1px solid #334155; padding-bottom: 10px;">
            Publish Status &amp; Visibility
          </h3>

          <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Publication Status</label>
            <select name="status" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem; font-weight: 600;">
              <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published (Live on Public Blog)</option>
              <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft (Internal Only)</option>
            </select>
          </div>

          <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Publication Date</label>
            <input type="datetime-local" name="published_at" value="<?= htmlspecialchars($publishedAt) ?>" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" />
          </div>

          <button type="submit" class="tn-btn-gold" style="width: 100%; padding: 13px; border: none; border-radius: 8px; font-weight: 700; font-size: 0.95rem; cursor: pointer; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35); margin-bottom: 10px;">
            <?= $isEdit ? 'SAVE &amp; UPDATE ARTICLE' : 'PUBLISH ARTICLE NOW' ?>
          </button>
        </div>

        <!-- CATEGORY & AUTHOR -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
          <h3 style="margin: 0 0 16px 0; font-size: 1.05rem; color: #f8fafc; font-weight: 700; border-bottom: 1px solid #334155; padding-bottom: 10px;">
            Category &amp; Author
          </h3>

          <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Packaging Category *</label>
            <select name="category" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;">
              <?php foreach ($existingCategories as $ec): ?>
                <option value="<?= htmlspecialchars($ec) ?>" <?= $category === $ec ? 'selected' : '' ?>>
                  <?= htmlspecialchars($ec) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Author Name</label>
            <input type="text" name="author_name" value="<?= htmlspecialchars($authorName) ?>" placeholder="TrueNorth Technical Division" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" />
          </div>

          <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Author Role / Byline</label>
            <input type="text" name="author_role" value="<?= htmlspecialchars($authorRole) ?>" placeholder="Senior Packaging Engineer" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" />
          </div>

          <div>
            <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Reading Time</label>
            <input type="text" id="readTimeInput" name="read_time" value="<?= htmlspecialchars($readTime) ?>" placeholder="e.g., 5 min read" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" />
          </div>
        </div>

        <!-- FEATURED IMAGE & MEDIA -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
          <h3 style="margin: 0 0 16px 0; font-size: 1.05rem; color: #f8fafc; font-weight: 700; border-bottom: 1px solid #334155; padding-bottom: 10px;">
            Featured Cover Image
          </h3>

          <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Image URL / Path</label>
            <input type="text" id="featuredImgInput" name="featured_image" value="<?= htmlspecialchars($featuredImage) ?>" placeholder="/vikaas_inputs/thumbnails/thumbnail_pet_bottles.webp" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.85rem;" oninput="updateImagePreview()" />
          </div>

          <div style="margin-bottom: 14px;">
            <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Or Upload New Image Asset</label>
            <input type="file" name="featured_image_file" accept="image/*" style="width: 100%; font-size: 0.84rem; color: #94a3b8;" onchange="previewUploadedImage(this)" />
          </div>

          <!-- Preview Box -->
          <div style="background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 10px; text-align: center;">
            <img id="imgPreview" src="<?= htmlspecialchars($featuredImage) ?>" alt="Cover Preview" style="width: 100%; height: 180px; object-fit: cover; border-radius: 6px;" onerror="this.src='/logo_svg.svg'" />
          </div>

          <!-- Quick Thumbnails Selector -->
          <div style="margin-top: 14px;">
            <label style="display: block; font-size: 0.76rem; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">Quick Presets:</label>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px;">
              <img src="/vikaas_inputs/thumbnails/thumbnail_pet_bottles.webp" title="PET Bottles" onclick="setPresetImg(this.src)" style="width: 100%; height: 42px; object-fit: cover; border-radius: 4px; border: 1px solid #475569; cursor: pointer;" />
              <img src="/uploads/products/pharma-bottles/pharma-100ml-dome-pet.webp" title="Pharma Bottles" onclick="setPresetImg(this.src)" style="width: 100%; height: 42px; object-fit: cover; border-radius: 4px; border: 1px solid #475569; cursor: pointer;" />
              <img src="/uploads/products/pumps/lotion-pump-nb101-a.webp" title="Pumps" onclick="setPresetImg(this.src)" style="width: 100%; height: 42px; object-fit: cover; border-radius: 4px; border: 1px solid #475569; cursor: pointer;" />
              <img src="/uploads/products/jars/jar-150cc-rd.webp" title="Jars" onclick="setPresetImg(this.src)" style="width: 100%; height: 42px; object-fit: cover; border-radius: 4px; border: 1px solid #475569; cursor: pointer;" />
            </div>
          </div>

        </div>

      </div>

    </div>
  </form>

</div>

<script>
// Live slug generation
function autoGenerateSlug() {
  const title = document.getElementById('postTitle').value;
  if (!title) return;
  const slug = title.toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
  document.getElementById('postSlug').value = slug;
  syncSeoPreview();
}

function updateExcerptCount() {
  const text = document.getElementById('postExcerpt').value || '';
  document.getElementById('excerptCount').textContent = text.length + ' / 180 chars';
}

function updateContentStats() {
  const text = document.getElementById('postContent').value || '';
  const clean = text.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
  const words = clean ? clean.split(' ').length : 0;
  document.getElementById('wordCountLabel').textContent = words + ' words';
  
  const mins = Math.max(1, Math.ceil(words / 200));
  const rt = mins + ' min read';
  document.getElementById('readingTimeLabel').textContent = rt;
  if (!document.getElementById('readTimeInput').value || document.getElementById('readTimeInput').value.includes('min read')) {
    document.getElementById('readTimeInput').value = rt;
  }
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
  checkSeoChecklist();
}

function insertH2() { insertTag('<h2>', '</h2>'); }
function insertH3() { insertTag('<h3>', '</h3>'); }
function insertBold() { insertTag('<strong>', '</strong>'); }
function insertItalic() { insertTag('<em>', '</em>'); }
function insertParagraph() { insertTag('<p>', '</p>'); }

function insertBulletListSnippet() {
  const snippet = `\n<ul>\n  <li>Strategic specification or benefit point</li>\n  <li>Precision packaging engineering parameter</li>\n</ul>\n`;
  insertTag('', snippet);
}

function insertQuoteSnippet() {
  const snippet = `\n<blockquote>\n  <p>Technical insight, client quote, or industry standard statement goes here...</p>\n</blockquote>\n`;
  insertTag('', snippet);
}

function insertCalloutSnippet() {
  const snippet = `\n<div class="blog-callout-box">\n  <h4>Key Takeaway</h4>\n  <p>Highlight your most crucial packaging takeaway or recommendation here.</p>\n</div>\n`;
  insertTag('', snippet);
}

function insertTableSnippet() {
  const snippet = `\n<table style="width: 100%; border-collapse: collapse; margin: 18px 0; font-size: 0.9rem;">
  <thead>
    <tr style="background: #16273f; color: #fff; text-align: left;">
      <th style="padding: 10px; border: 1px solid #334155;">Parameter</th>
      <th style="padding: 10px; border: 1px solid #334155;">Specification</th>
      <th style="padding: 10px; border: 1px solid #334155;">Standard</th>
    </tr>
  </thead>
  <tbody>
    <tr style="border-bottom: 1px solid #e2e8f0;">
      <td style="padding: 8px 10px; font-weight: 600;">Polymer Grade</td>
      <td style="padding: 8px 10px;">Virgin / 100% rPET</td>
      <td style="padding: 8px 10px;">ISO 9001:2015</td>
    </tr>
  </tbody>
</table>\n`;
  insertTag('', snippet);
}

function updateImagePreview() {
  const val = document.getElementById('featuredImgInput').value;
  document.getElementById('imgPreview').src = val || '/logo_svg.svg';
  checkSeoChecklist();
}

function setPresetImg(src) {
  try {
    const u = new URL(src);
    document.getElementById('featuredImgInput').value = u.pathname;
    updateImagePreview();
  } catch(e) {
    document.getElementById('featuredImgInput').value = src;
    updateImagePreview();
  }
}

function previewUploadedImage(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('imgPreview').src = e.target.result;
    }
    reader.readAsDataURL(input.files[0]);
  }
}

// SEO & Google SERP sync (Delegates to WordPress SEO Suite)
function syncSeoPreview() {
  if (typeof runWpbSeoAnalysis === 'function') {
    runWpbSeoAnalysis();
  }
}

function checkSeoChecklist() {
  if (typeof runWpbSeoAnalysis === 'function') {
    runWpbSeoAnalysis();
  }
}

function old_unused_syncSeoPreview() {
  const title = document.getElementById('postTitle').value;
  const slug = document.getElementById('postSlug').value;
  const excerpt = document.getElementById('postExcerpt').value;
  
  let mTitle = document.getElementById('metaTitleInput').value || (title ? (title + ' — TrueNorth Packaging') : 'Article Title Preview — TrueNorth Packaging');
  let mDesc = document.getElementById('metaDescInput').value || excerpt || 'Detailed technical analysis of primary packaging containers, cleanroom molding standards, and dispensing closure engineering.';

  // SERP DOM update
  document.getElementById('serpTitlePreview').textContent = mTitle;
  document.getElementById('serpDescPreview').textContent = mDesc;
  document.getElementById('serpUrlPreview').textContent = 'https://wetruenorthgroup.com › blog › ' + (slug || '...');

  // Character Bars
  const tLen = (document.getElementById('metaTitleInput').value || title || '').length;
  document.getElementById('titleCountBadge').textContent = tLen + ' / 60 chars';
  const tPercent = Math.min(100, (tLen / 60) * 100);
  const tBar = document.getElementById('titleBar');
  tBar.style.width = tPercent + '%';
  tBar.style.background = (tLen >= 40 && tLen <= 65) ? '#34d399' : ((tLen > 65) ? '#ef4444' : '#fbbf24');

  const dLen = (document.getElementById('metaDescInput').value || excerpt || '').length;
  document.getElementById('descCountBadge').textContent = dLen + ' / 160 chars';
  const dPercent = Math.min(100, (dLen / 160) * 100);
  const dBar = document.getElementById('descBar');
  dBar.style.width = dPercent + '%';
  dBar.style.background = (dLen >= 120 && dLen <= 165) ? '#34d399' : ((dLen > 165) ? '#ef4444' : '#fbbf24');

  checkSeoChecklist();
}

function checkSeoChecklist() {
  const title = document.getElementById('postTitle').value;
  const mTitle = document.getElementById('metaTitleInput').value || title;
  const mDesc = document.getElementById('metaDescInput').value || document.getElementById('postExcerpt').value;
  const keywords = document.getElementById('metaKeywordsInput').value;
  const img = document.getElementById('featuredImgInput').value;
  const content = document.getElementById('postContent').value || '';
  const clean = content.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
  const words = clean ? clean.split(' ').length : 0;

  setCheck('chkTitle', mTitle.length >= 35 && mTitle.length <= 70, 'Title length (' + mTitle.length + ' chars)');
  setCheck('chkDesc', mDesc.length >= 100 && mDesc.length <= 170, 'Meta description (' + mDesc.length + ' chars)');
  setCheck('chkKeywords', keywords.trim().length > 3, 'Focus keywords configured (' + (keywords ? keywords.split(',').length : 0) + ')');
  setCheck('chkImage', !!img, 'Featured cover image attached');
  setCheck('chkWords', words >= 250, 'Content depth (' + words + ' words)');
}

function setCheck(id, pass, text) {
  const el = document.getElementById(id);
  if (!el) return;
  if (pass) {
    el.innerHTML = '<span style="color: #34d399; font-weight: 700;">&#10003;</span> <span style="color: #6ee7b7;">' + text + '</span>';
  } else {
    el.innerHTML = '<span style="color: #94a3b8;">&bull;</span> <span style="color: #94a3b8;">' + text + '</span>';
  }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
  updateExcerptCount();
  updateContentStats();
  syncSeoPreview();
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

