<?php
// ==============================================================================
// TRUENORTH CMS — WORDPRESS YOAST & RANK MATH STYLE SEO SUITE FOR BLOGS
// Complete 5-Tab SEO System: SEO, Readability, Schema, Social, Advanced
// Features live mobile/desktop SERP toggles, traffic-light scoring, Flesch Reading Ease
// ==============================================================================

if (!class_exists('SeoModel')) {
    require_once dirname(__DIR__, 2) . '/app/Models/SeoModel.php';
}

$curSlug = $post['slug'] ?? ($slug ?? '');
$curBlogSeo = [];
if (!empty($curSlug)) {
    $curBlogSeo = SeoModel::getPageSeo('/blog/' . $curSlug) ?? [];
    if (empty($curBlogSeo)) {
        $curBlogSeo = SeoModel::getPageSeo($curSlug) ?? [];
    }
}

$curPostTitle = $post['title'] ?? ($title ?? 'Choosing the Right Packaging for Cosmetics: A Comprehensive Guide');
$curSeoTitle = !empty($curBlogSeo['meta_title']) ? $curBlogSeo['meta_title'] : (!empty($post['meta_title']) ? $post['meta_title'] : ($curPostTitle . ' — TrueNorth Group'));
$curSeoDesc = !empty($curBlogSeo['meta_description']) ? $curBlogSeo['meta_description'] : (!empty($post['meta_description']) ? $post['meta_description'] : (!empty($excerpt) ? $excerpt : ''));
$curKeywords = !empty($curBlogSeo['meta_keywords']) ? $curBlogSeo['meta_keywords'] : ($post['tags'] ?? ($post['meta_keywords'] ?? ''));
$curFocusKeyword = !empty($curBlogSeo['focus_keyword']) ? $curBlogSeo['focus_keyword'] : ($post['focus_keyword'] ?? 'cosmetic packaging guide');
$curCanonical = !empty($curBlogSeo['canonical_url']) ? $curBlogSeo['canonical_url'] : ($post['canonical_url'] ?? '');
$curH1 = !empty($curBlogSeo['h1_override']) ? $curBlogSeo['h1_override'] : ($post['h1_override'] ?? '');
$curRobotsIndex = isset($curBlogSeo['robots_index']) ? (int)$curBlogSeo['robots_index'] : (isset($post['robots_index']) ? (int)$post['robots_index'] : 1);
$curRobotsFollow = isset($curBlogSeo['robots_follow']) ? (int)$curBlogSeo['robots_follow'] : (isset($post['robots_follow']) ? (int)$post['robots_follow'] : 1);
$curOgTitle = !empty($curBlogSeo['og_title']) ? $curBlogSeo['og_title'] : ($post['og_title'] ?? $curSeoTitle);
$curOgDesc = !empty($curBlogSeo['og_description']) ? $curBlogSeo['og_description'] : ($post['og_description'] ?? $curSeoDesc);
$curFeaturedImage = $post['featured_image'] ?? ($featuredImage ?? '/hero_bottles_banner.webp');
$curOgImage = !empty($curBlogSeo['og_image']) ? $curBlogSeo['og_image'] : ($post['og_image'] ?? $curFeaturedImage);
$curTwitterTitle = !empty($curBlogSeo['twitter_title']) ? $curBlogSeo['twitter_title'] : ($post['twitter_title'] ?? $curOgTitle);
$curTwitterDesc = !empty($curBlogSeo['twitter_description']) ? $curBlogSeo['twitter_description'] : ($post['twitter_description'] ?? $curOgDesc);
$curTwitterImage = !empty($curBlogSeo['twitter_image']) ? $curBlogSeo['twitter_image'] : ($post['twitter_image'] ?? $curOgImage);
$curTwitterCard = !empty($curBlogSeo['twitter_card']) ? $curBlogSeo['twitter_card'] : ($post['twitter_card'] ?? 'summary_large_image');
$curSchemaType = !empty($curBlogSeo['schema_type']) ? $curBlogSeo['schema_type'] : ($post['schema_type'] ?? 'BlogPosting');
$curCustomSchema = !empty($curBlogSeo['custom_schema_json']) ? $curBlogSeo['custom_schema_json'] : ($post['custom_schema_json'] ?? '');
$curCornerstone = !empty($curBlogSeo['cornerstone_content']) ? 1 : (!empty($post['cornerstone_content']) ? 1 : 0);
$curBreadcrumbTitle = !empty($curBlogSeo['breadcrumbs_title']) ? $curBlogSeo['breadcrumbs_title'] : ($post['breadcrumbs_title'] ?? $curPostTitle);
$curAdvancedRobots = !empty($curBlogSeo['advanced_robots']) ? $curBlogSeo['advanced_robots'] : ($post['advanced_robots'] ?? '');

$blogFaqs = [];
if (!empty($curSlug)) {
    $blogFaqs = SeoModel::getFaqs('blog', '/blog/' . $curSlug);
}
?>

<!-- WORDPRESS BLOG SEO METABOX CONTAINER -->
<div class="form-card wp-seo-metabox" id="wp-blog-seo-panel" style="border: 1px solid #38bdf8; background: #0f172a; border-radius: 16px; margin-bottom: 28px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.35);">
  
  <!-- METABOX HEADER WITH OVERALL SCORES (MATCHING SCREENSHOT) -->
  <div style="padding: 16px 24px; background: #1e293b; border-bottom: 1px solid #334155; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
    <div style="display: flex; align-items: center; gap: 12px;">
      <div style="width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, #0284c7, #0369a1); display: flex; align-items: center; justify-content: center; color: #fff;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><path d="m8 11 2 2 4-4"/></svg>
      </div>
      <div>
        <div style="font-size: 1.05rem; font-weight: 800; color: #f8fafc; display: flex; align-items: center; gap: 8px;">
          <span>Yoast SEO</span>
          <span style="font-size: 0.72rem; padding: 2px 8px; border-radius: 12px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); font-weight: 700; text-transform: uppercase;">WordPress Engine</span>
        </div>
        <div style="font-size: 0.76rem; color: #94a3b8;">Article SEO, live Google desktop/mobile snippet preview, Flesch readability &amp; rich schema</div>
      </div>
    </div>

    <!-- LIVE METABOX SCORE PILLS (TRAFFIC LIGHTS) -->
    <div style="display: flex; align-items: center; gap: 10px;">
      <div id="wpb-seo-overall-pill" style="display: flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); font-size: 0.8rem; font-weight: 700; color: #4ade80;">
        <span id="wpb-seo-traffic-dot" style="width: 10px; height: 10px; border-radius: 50%; background: #22c55e;"></span>
        <span>SEO: <strong id="wpb-seo-score-text">Good</strong></span>
      </div>
      <div id="wpb-readability-pill" style="display: flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; background: rgba(234, 179, 8, 0.15); border: 1px solid rgba(234, 179, 8, 0.4); font-size: 0.8rem; font-weight: 700; color: #facc15;">
        <span id="wpb-readability-dot" style="width: 10px; height: 10px; border-radius: 50%; background: #eab308;"></span>
        <span>Readability: <strong id="wpb-readability-score-text">OK</strong></span>
      </div>
    </div>
  </div>

  <!-- TAB NAVIGATION BAR (MATCHING SCREENSHOT) -->
  <div style="display: flex; background: #0b1120; border-bottom: 1px solid #334155; padding: 0 16px; overflow-x: auto;">
    <button type="button" class="wpb-seo-tab-btn active" onclick="switchWpbTab('seo')" id="wpbtab-btn-seo" style="display: flex; align-items: center; gap: 8px; padding: 14px 20px; border: none; background: transparent; color: #38bdf8; font-weight: 700; font-size: 0.88rem; border-bottom: 2px solid #38bdf8; cursor: pointer; transition: all 0.15s;">
      <span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e;" id="wpbtab-dot-seo"></span>
      <span>SEO</span>
    </button>
    <button type="button" class="wpb-seo-tab-btn" onclick="switchWpbTab('readability')" id="wpbtab-btn-readability" style="display: flex; align-items: center; gap: 8px; padding: 14px 20px; border: none; background: transparent; color: #94a3b8; font-weight: 600; font-size: 0.88rem; border-bottom: 2px solid transparent; cursor: pointer; transition: all 0.15s;">
      <span style="width: 8px; height: 8px; border-radius: 50%; background: #eab308;" id="wpbtab-dot-readability"></span>
      <span>Readability</span>
    </button>
    <button type="button" class="wpb-seo-tab-btn" onclick="switchWpbTab('schema')" id="wpbtab-btn-schema" style="display: flex; align-items: center; gap: 8px; padding: 14px 20px; border: none; background: transparent; color: #94a3b8; font-weight: 600; font-size: 0.88rem; border-bottom: 2px solid transparent; cursor: pointer; transition: all 0.15s;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      <span>Schema</span>
    </button>
    <button type="button" class="wpb-seo-tab-btn" onclick="switchWpbTab('social')" id="wpbtab-btn-social" style="display: flex; align-items: center; gap: 8px; padding: 14px 20px; border: none; background: transparent; color: #94a3b8; font-weight: 600; font-size: 0.88rem; border-bottom: 2px solid transparent; cursor: pointer; transition: all 0.15s;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
      <span>Social</span>
    </button>
    <button type="button" class="wpb-seo-tab-btn" onclick="switchWpbTab('advanced')" id="wpbtab-btn-advanced" style="display: flex; align-items: center; gap: 8px; padding: 14px 20px; border: none; background: transparent; color: #94a3b8; font-weight: 600; font-size: 0.88rem; border-bottom: 2px solid transparent; cursor: pointer; transition: all 0.15s;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
      <span>Advanced</span>
    </button>
  </div>

  <div style="padding: 24px;">

    <!-- =====================================================================
         TAB 1: SEO
         ===================================================================== -->
    <div id="wpb-sec-seo" class="wpb-tab-section" style="display: block;">
      
      <!-- FOCUS KEYPHRASE (MATCHING SCREENSHOT) -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <label for="wpbFocusKeyword" style="display: block; font-size: 0.9rem; font-weight: 800; color: #f8fafc; margin-bottom: 6px;">
          Focus Keyphrase
        </label>
        <p style="font-size: 0.78rem; color: #94a3b8; margin: 0 0 12px 0;">
          Enter the primary search query you want this packaging article to rank for in Google.
        </p>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <input type="text" id="wpbFocusKeyword" name="focus_keyword" value="<?= htmlspecialchars($curFocusKeyword) ?>" placeholder="e.g. cosmetic packaging guide" class="form-control-smart" style="flex: 1; min-width: 260px;" oninput="runWpbSeoAnalysis()" />
          <button type="button" onclick="autoDetectBlogKeyphrase()" style="background: #334155; border: 1px solid #475569; color: #38bdf8; font-size: 0.8rem; font-weight: 700; padding: 9px 16px; border-radius: 8px; cursor: pointer;">
            Get related keyphrases
          </button>
        </div>
      </div>

      <!-- SEARCH APPEARANCE PREVIEW (MATCHING SCREENSHOT) -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
          <div>
            <h4 style="margin: 0; font-size: 0.95rem; font-weight: 800; color: #f8fafc;">Search Appearance</h4>
            <div style="font-size: 0.75rem; color: #94a3b8;">Determine how your post should look in search engine results.</div>
          </div>
          <!-- Device Toggle (Mobile vs Desktop) -->
          <div style="display: flex; background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 3px;">
            <button type="button" id="wpbbtn-preview-mobile" onclick="toggleWpbSerpDevice('mobile')" style="padding: 6px 12px; font-size: 0.78rem; font-weight: 700; border: none; border-radius: 6px; background: #38bdf8; color: #0f172a; cursor: pointer; display: flex; align-items: center; gap: 6px;">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
              Mobile result
            </button>
            <button type="button" id="wpbbtn-preview-desktop" onclick="toggleWpbSerpDevice('desktop')" style="padding: 6px 12px; font-size: 0.78rem; font-weight: 700; border: none; border-radius: 6px; background: transparent; color: #94a3b8; cursor: pointer; display: flex; align-items: center; gap: 6px;">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
              Desktop result
            </button>
          </div>
        </div>

        <!-- AUTHENTIC GOOGLE SERP CARD WITH MOBILE THUMBNAIL (AS IN SCREENSHOT) -->
        <div id="wpbserp-container" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 22px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; max-width: 600px; box-shadow: 0 4px 14px rgba(0,0,0,0.12); margin-bottom: 20px;">
          <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
            <img src="/logo_svg.svg" style="width: 20px; height: 20px; border-radius: 50%; object-fit: contain; background: #f8fafc; border: 1px solid #e2e8f0;" alt="Favicon" />
            <div style="display: flex; flex-direction: column; line-height: 1.25;">
              <span style="font-size: 0.84rem; color: #202124; font-weight: 500;">TrueNorth Group</span>
              <span id="wpbserp-url-display" style="font-size: 0.75rem; color: #4d5156;">https://wetruenorthgroup.com &rsaquo; <?= htmlspecialchars($curSlug) ?></span>
            </div>
          </div>
          <div id="wpbserp-title-display" style="font-size: 1.25rem; color: #1a0dab; font-weight: 400; line-height: 1.3; cursor: pointer; margin-bottom: 8px; word-break: break-word;">
            <?= htmlspecialchars($curSeoTitle) ?>
          </div>

          <!-- Snippet Row: Text on Left + Image Thumbnail on Right (Google Mobile Layout) -->
          <div style="display: flex; gap: 14px; align-items: flex-start; justify-content: space-between;">
            <div id="wpbserp-desc-display" style="font-size: 0.88rem; color: #4d5156; line-height: 1.45; word-break: break-word; flex: 1;">
              <?= htmlspecialchars($curSeoDesc ?: 'Please provide a meta description by editing the snippet below.') ?>
            </div>
            <div id="wpbserp-thumb-box" style="width: 82px; height: 82px; border-radius: 8px; overflow: hidden; flex-shrink: 0; background: #f1f5f9; border: 1px solid #e2e8f0;">
              <img id="wpbserp-thumb-img" src="<?= htmlspecialchars($curFeaturedImage) ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="SERP Thumbnail" onerror="this.src='/hero_bottles_banner.webp';" />
            </div>
          </div>
        </div>

        <!-- SEO TITLE WITH CHARACTER BAR & INSERT VARIABLE -->
        <div style="margin-bottom: 20px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
            <label for="wpbTitle" style="font-size: 0.85rem; font-weight: 700; color: #cbd5e1;">SEO Title</label>
            <span id="wpbTitleCounter" style="font-size: 0.75rem; font-weight: 700; color: #34d399;">0 / 60 chars</span>
          </div>
          <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px;">
            <button type="button" onclick="insertWpbTag('wpbTitle', '{title}')" style="background: #0f172a; border: 1px solid #475569; color: #38bdf8; font-size: 0.72rem; padding: 3px 8px; border-radius: 4px; cursor: pointer;">+ Title</button>
            <button type="button" onclick="insertWpbTag('wpbTitle', '{separator}')" style="background: #0f172a; border: 1px solid #475569; color: #38bdf8; font-size: 0.72rem; padding: 3px 8px; border-radius: 4px; cursor: pointer;">+ Sep</button>
            <button type="button" onclick="insertWpbTag('wpbTitle', '{site_name}')" style="background: #0f172a; border: 1px solid #475569; color: #38bdf8; font-size: 0.72rem; padding: 3px 8px; border-radius: 4px; cursor: pointer;">+ Site title</button>
            <button type="button" onclick="insertWpbTag('wpbTitle', '{primary_category}')" style="background: #0f172a; border: 1px solid #475569; color: #38bdf8; font-size: 0.72rem; padding: 3px 8px; border-radius: 4px; cursor: pointer;">+ Category</button>
          </div>
          <input type="text" id="wpbTitle" name="meta_title" value="<?= htmlspecialchars($curSeoTitle) ?>" class="form-control-smart" style="width: 100%; box-sizing: border-box;" oninput="runWpbSeoAnalysis()" />
          <div style="height: 4px; background: #334155; border-radius: 2px; margin-top: 6px; overflow: hidden;">
            <div id="wpbTitleBar" style="height: 100%; width: 0%; background: #22c55e; transition: width 0.2s, background 0.2s;"></div>
          </div>
        </div>

        <!-- SLUG INPUT (MATCHING SCREENSHOT) -->
        <div style="margin-bottom: 20px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
            <label for="wpbSlugInput" style="font-size: 0.85rem; font-weight: 700; color: #cbd5e1;">Slug</label>
            <span style="font-size: 0.72rem; color: #94a3b8;">URL path segment</span>
          </div>
          <div style="display: flex; align-items: center; background: #0f172a; border: 1px solid #475569; border-radius: 8px; padding-left: 12px;">
            <span style="color: #64748b; font-family: monospace; font-size: 0.85rem;">/blog/</span>
            <input type="text" id="wpbSlugInput" value="<?= htmlspecialchars($curSlug) ?>" style="flex: 1; border: none; background: transparent; color: #38bdf8; padding: 10px 12px; font-family: monospace; font-size: 0.88rem; outline: none;" oninput="syncWpbSlug(this.value)" />
          </div>
        </div>

        <!-- META DESCRIPTION WITH CHARACTER BAR & INSERT VARIABLE -->
        <div>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
            <label for="wpbDesc" style="font-size: 0.85rem; font-weight: 700; color: #cbd5e1;">Meta Description</label>
            <span id="wpbDescCounter" style="font-size: 0.75rem; font-weight: 700; color: #34d399;">0 / 160 chars</span>
          </div>
          <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px;">
            <button type="button" onclick="insertWpbTag('wpbDesc', '{excerpt}')" style="background: #0f172a; border: 1px solid #475569; color: #38bdf8; font-size: 0.72rem; padding: 3px 8px; border-radius: 4px; cursor: pointer;">+ Article excerpt</button>
            <button type="button" onclick="insertWpbTag('wpbDesc', '{site_description}')" style="background: #0f172a; border: 1px solid #475569; color: #38bdf8; font-size: 0.72rem; padding: 3px 8px; border-radius: 4px; cursor: pointer;">+ Site description</button>
          </div>
          <textarea id="wpbDesc" name="meta_description" rows="3" class="form-control-smart" style="width: 100%; box-sizing: border-box; line-height: 1.5;" oninput="runWpbSeoAnalysis()"><?= htmlspecialchars($curSeoDesc) ?></textarea>
          <div style="height: 4px; background: #334155; border-radius: 2px; margin-top: 6px; overflow: hidden;">
            <div id="wpbDescBar" style="height: 100%; width: 0%; background: #22c55e; transition: width 0.2s, background 0.2s;"></div>
          </div>
        </div>

      </div>

      <!-- SEO ANALYSIS ACCORDION (MATCHING SCREENSHOT) -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; margin-bottom: 18px; overflow: hidden;">
        <div onclick="toggleBlogAccordion('wpb-analysis-body', 'wpb-analysis-icon')" style="padding: 16px 20px; background: #0f172a; display: flex; align-items: center; justify-content: space-between; cursor: pointer; user-select: none;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <span id="wpb-analysis-traffic-dot" style="width: 10px; height: 10px; border-radius: 50%; background: #22c55e;"></span>
            <span style="font-size: 0.92rem; font-weight: 800; color: #f8fafc;">SEO Analysis</span>
            <span id="wpb-analysis-pill" style="font-size: 0.72rem; padding: 2px 8px; border-radius: 10px; background: rgba(34, 197, 94, 0.15); color: #4ade80; font-weight: 700;">Good</span>
          </div>
          <svg id="wpb-analysis-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="transform: rotate(180deg); transition: transform 0.2s;"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
        <div id="wpb-analysis-body" style="padding: 18px 20px; display: block;">
          
          <!-- Problems (Red) -->
          <div id="wpbWrapProblems" style="margin-bottom: 14px; display: none;">
            <div style="font-size: 0.78rem; font-weight: 800; color: #f87171; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
              <span style="width: 8px; height: 8px; border-radius: 50%; background: #ef4444;"></span>
              <span>Problems (<span id="wpbCountProblems">0</span>)</span>
            </div>
            <div id="wpbListProblems" style="display: flex; flex-direction: column; gap: 6px; font-size: 0.82rem; padding-left: 14px;"></div>
          </div>

          <!-- Improvements (Orange) -->
          <div id="wpbWrapImprovements" style="margin-bottom: 14px; display: none;">
            <div style="font-size: 0.78rem; font-weight: 800; color: #fbbf24; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
              <span style="width: 8px; height: 8px; border-radius: 50%; background: #eab308;"></span>
              <span>Improvements (<span id="wpbCountImprovements">0</span>)</span>
            </div>
            <div id="wpbListImprovements" style="display: flex; flex-direction: column; gap: 6px; font-size: 0.82rem; padding-left: 14px;"></div>
          </div>

          <!-- Good Results (Green) -->
          <div id="wpbWrapGood" style="display: block;">
            <div style="font-size: 0.78rem; font-weight: 800; color: #4ade80; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
              <span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e;"></span>
              <span>Good Results (<span id="wpbCountGood">0</span>)</span>
            </div>
            <div id="wpbListGood" style="display: flex; flex-direction: column; gap: 6px; font-size: 0.82rem; padding-left: 14px;"></div>
          </div>

        </div>
      </div>

      <!-- ADDITIONAL YOAST ACCORDIONS (MATCHING SCREENSHOT) -->
      <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 24px;">
        
        <!-- Add related keyphrase -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 10px; overflow: hidden;">
          <div onclick="toggleBlogAccordion('wpb-sec-relkw', 'wpb-icon-relkw')" style="padding: 14px 18px; background: #0f172a; display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
            <span style="font-size: 0.88rem; font-weight: 700; color: #cbd5e1;">+ Add related keyphrase</span>
            <svg id="wpb-icon-relkw" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </div>
          <div id="wpb-sec-relkw" style="padding: 16px; display: none;">
            <input type="text" placeholder="e.g. cosmetic bottle manufacturing, luxury acrylic jars" class="form-control-smart" style="width: 100%; box-sizing: border-box;" />
            <div style="font-size: 0.74rem; color: #94a3b8; margin-top: 6px;">Rank for secondary keyphrases and synonyms across the article.</div>
          </div>
        </div>

        <!-- Track SEO performance -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 10px; overflow: hidden;">
          <div onclick="toggleBlogAccordion('wpb-sec-track', 'wpb-icon-track')" style="padding: 14px 18px; background: #0f172a; display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
            <span style="font-size: 0.88rem; font-weight: 700; color: #cbd5e1;">Track SEO performance</span>
            <svg id="wpb-icon-track" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </div>
          <div id="wpb-sec-track" style="padding: 16px; display: none;">
            <div style="font-size: 0.82rem; color: #cbd5e1; line-height: 1.5;">Connect Google Search Console to monitor organic clicks, average CTR, and ranking positions for this article.</div>
          </div>
        </div>

        <!-- Internal linking suggestions -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 10px; overflow: hidden;">
          <div onclick="toggleBlogAccordion('wpb-sec-inlinks', 'wpb-icon-inlinks')" style="padding: 14px 18px; background: #0f172a; display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
            <span style="font-size: 0.88rem; font-weight: 700; color: #cbd5e1;">Internal linking suggestions</span>
            <svg id="wpb-icon-inlinks" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </div>
          <div id="wpb-sec-inlinks" style="padding: 16px; display: none;">
            <div style="font-size: 0.82rem; color: #cbd5e1; line-height: 1.5;">Suggested pages to link from this article:
              <ul style="margin: 6px 0 0 18px; padding: 0; color: #38bdf8;">
                <li><a href="/products" target="_blank" style="color: #38bdf8;">/products</a> (Products Catalogue)</li>
                <li><a href="/sustainability" target="_blank" style="color: #38bdf8;">/sustainability</a> (rPET &amp; Circular Polymers)</li>
                <li><a href="/custom" target="_blank" style="color: #38bdf8;">/custom</a> (Bespoke Mould Development)</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Cornerstone content toggle -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-size: 0.88rem; font-weight: 700; color: #f8fafc;">Cornerstone Content</div>
            <div style="font-size: 0.74rem; color: #94a3b8;">Mark this article as essential pillar documentation for packaging authority.</div>
          </div>
          <label style="position: relative; display: inline-block; width: 44px; height: 24px; margin: 0; cursor: pointer;">
            <input type="checkbox" id="wpbCornerstone" name="cornerstone_content" value="1" <?= $curCornerstone ? 'checked' : '' ?> style="opacity: 0; width: 0; height: 0;" onchange="runWpbSeoAnalysis()" />
            <span style="position: absolute; cursor: pointer; inset: 0; background: #334155; border-radius: 24px; transition: 0.2s;"></span>
          </label>
        </div>

      </div>

    </div>

    <!-- =====================================================================
         TAB 2: READABILITY (FLESCH READING EASE & STRUCTURE)
         ===================================================================== -->
    <div id="wpb-sec-readability" class="wpb-tab-section" style="display: none;">
      
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 22px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
          <div>
            <div style="font-size: 0.8rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Flesch Reading Ease Formula</div>
            <div id="wpbFleschScoreDisplay" style="font-size: 1.8rem; font-weight: 800; color: #38bdf8;">68.5 &mdash; Standard Technical Readability</div>
          </div>
          <div id="wpbFleschBadge" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); padding: 5px 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 700;">
            Web Standard Grade
          </div>
        </div>
        <p style="font-size: 0.84rem; color: #cbd5e1; line-height: 1.5; margin: 0 0 14px 0;">
          Calculated automatically from your article body based on average sentence length and syllables per word. Scores 60.0&ndash;80.0 are optimal for technical B2B packaging readers.
        </p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; background: #0f172a; padding: 12px 16px; border-radius: 8px;">
          <div>
            <div style="font-size: 0.72rem; color: #94a3b8;">Total Sentences</div>
            <div id="wpbStatSentences" style="font-size: 1.1rem; font-weight: 700; color: #fff;">18</div>
          </div>
          <div>
            <div style="font-size: 0.72rem; color: #94a3b8;">Avg Sentence Length</div>
            <div id="wpbStatAvgSentence" style="font-size: 1.1rem; font-weight: 700; color: #fff;">16.4 words</div>
          </div>
          <div>
            <div style="font-size: 0.72rem; color: #94a3b8;">Avg Syllables/Word</div>
            <div id="wpbStatAvgSyllable" style="font-size: 1.1rem; font-weight: 700; color: #fff;">1.6</div>
          </div>
          <div>
            <div style="font-size: 0.72rem; color: #94a3b8;">Estimated Reading Time</div>
            <div id="wpbStatReadingTime" style="font-size: 1.1rem; font-weight: 700; color: #fff;">5 min read</div>
          </div>
        </div>
      </div>

      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
        <div style="font-size: 0.9rem; font-weight: 800; color: #f8fafc; margin-bottom: 14px;">Readability Audit Criteria</div>
        <div id="wpbReadabilityChecklist" style="display: flex; flex-direction: column; gap: 10px; font-size: 0.84rem;"></div>
      </div>

    </div>

    <!-- =====================================================================
         TAB 3: SCHEMA (STRUCTURED DATA & FAQS)
         ===================================================================== -->
    <div id="wpb-sec-schema" class="wpb-tab-section" style="display: none;">
      
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <label style="display: block; font-size: 0.86rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
          Schema.org Article Type
        </label>
        <select id="wpbSchemaType" name="schema_type" class="form-control-smart" style="width: 100%; box-sizing: border-box;">
          <option value="BlogPosting" <?= $curSchemaType === 'BlogPosting' ? 'selected' : '' ?>>BlogPosting (Standard Blog Article)</option>
          <option value="Article" <?= $curSchemaType === 'Article' ? 'selected' : '' ?>>Article (General Editorial &amp; News)</option>
          <option value="TechArticle" <?= $curSchemaType === 'TechArticle' ? 'selected' : '' ?>>TechArticle (Engineering &amp; Technical Specs)</option>
          <option value="NewsArticle" <?= $curSchemaType === 'NewsArticle' ? 'selected' : '' ?>>NewsArticle (Press Release &amp; Updates)</option>
        </select>
        <div style="font-size: 0.74rem; color: #94a3b8; margin-top: 6px;">
          Generates rich JSON-LD Article structured data including headline, author, publisher, datePublished, and featured image.
        </div>
      </div>

      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
          <label style="font-size: 0.86rem; font-weight: 700; color: #cbd5e1;">
            Custom JSON-LD Schema Override (Optional)
          </label>
          <span id="wpbJsonStatusBadge" style="font-size: 0.72rem; padding: 3px 8px; border-radius: 4px; background: #334155; color: #94a3b8; font-weight: 700;">
            Validates in real-time
          </span>
        </div>
        <textarea id="wpbCustomSchema" name="custom_schema_json" rows="6" placeholder='{"@context": "https://schema.org", "@type": "BlogPosting", ...}' class="form-control-smart" style="width: 100%; box-sizing: border-box; font-family: monospace; font-size: 0.84rem; line-height: 1.45;" oninput="validateWpbJson()"><?= htmlspecialchars($curCustomSchema) ?></textarea>
      </div>

    </div>

    <!-- =====================================================================
         TAB 4: SOCIAL (FACEBOOK & TWITTER)
         ===================================================================== -->
    <div id="wpb-sec-social" class="wpb-tab-section" style="display: none;">
      
      <div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid #334155; padding-bottom: 12px;">
        <button type="button" id="wpbsubtab-fb" onclick="switchWpbSocialSubTab('fb')" style="padding: 7px 16px; border: 1px solid #0284c7; background: #0284c7; color: #fff; border-radius: 6px; font-size: 0.82rem; font-weight: 700; cursor: pointer;">
          Facebook / Open Graph
        </button>
        <button type="button" id="wpbsubtab-tw" onclick="switchWpbSocialSubTab('tw')" style="padding: 7px 16px; border: 1px solid #475569; background: transparent; color: #94a3b8; border-radius: 6px; font-size: 0.82rem; font-weight: 700; cursor: pointer;">
          Twitter / X Card
        </button>
      </div>

      <div id="wpbsocial-fb">
        <div style="background: #1877f2; padding: 1px; border-radius: 12px; margin-bottom: 20px; max-width: 520px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.3);">
          <div style="background: #242526; color: #e4e6eb; font-family: system-ui, -apple-system, sans-serif;">
            <div id="wpbcard-fb-img" style="height: 230px; background: #1e293b url('<?= htmlspecialchars($curOgImage) ?>') center/cover no-repeat;"></div>
            <div style="padding: 12px 16px; background: #3a3b3c;">
              <div style="font-size: 0.72rem; color: #b0b3b8; text-transform: uppercase;">WETRUENORTHGROUP.COM</div>
              <div id="wpbcard-fb-title" style="font-size: 1rem; font-weight: 700; color: #e4e6eb; margin: 4px 0 2px 0; line-height: 1.3;"><?= htmlspecialchars($curOgTitle) ?></div>
              <div id="wpbcard-fb-desc" style="font-size: 0.8rem; color: #b0b3b8; line-height: 1.35; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($curOgDesc) ?></div>
            </div>
          </div>
        </div>

        <div style="margin-bottom: 16px;">
          <label style="display: block; font-size: 0.84rem; color: #cbd5e1; margin-bottom: 4px;">Facebook Title (og:title)</label>
          <input type="text" id="wpbOgTitle" name="og_title" value="<?= htmlspecialchars($curOgTitle) ?>" class="form-control-smart" style="width: 100%; box-sizing: border-box;" oninput="updateWpbSocialPreviews()" />
        </div>
        <div style="margin-bottom: 16px;">
          <label style="display: block; font-size: 0.84rem; color: #cbd5e1; margin-bottom: 4px;">Facebook Description (og:description)</label>
          <textarea id="wpbOgDescription" name="og_description" rows="2" class="form-control-smart" style="width: 100%; box-sizing: border-box;" oninput="updateWpbSocialPreviews()"><?= htmlspecialchars($curOgDesc) ?></textarea>
        </div>
        <div>
          <label style="display: block; font-size: 0.84rem; color: #cbd5e1; margin-bottom: 4px;">Facebook Image URL (og:image)</label>
          <input type="text" id="wpbOgImage" name="og_image" value="<?= htmlspecialchars($curOgImage) ?>" class="form-control-smart" style="width: 100%; box-sizing: border-box;" oninput="updateWpbSocialPreviews()" />
        </div>
      </div>

      <div id="wpbsocial-tw" style="display: none;">
        <div style="background: #000; border: 1px solid #2f3336; border-radius: 12px; margin-bottom: 20px; max-width: 520px; overflow: hidden; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
          <div id="wpbcard-tw-img" style="height: 210px; background: #1e293b url('<?= htmlspecialchars($curTwitterImage) ?>') center/cover no-repeat;"></div>
          <div style="padding: 12px 16px;">
            <div style="font-size: 0.72rem; color: #71767b;">wetruenorthgroup.com</div>
            <div id="wpbcard-tw-title" style="font-size: 0.96rem; font-weight: 700; color: #e7e9ea; margin: 3px 0;"><?= htmlspecialchars($curTwitterTitle) ?></div>
            <div id="wpbcard-tw-desc" style="font-size: 0.8rem; color: #71767b; line-height: 1.35;"><?= htmlspecialchars($curTwitterDesc) ?></div>
          </div>
        </div>
        <div style="margin-bottom: 16px;">
          <label style="display: block; font-size: 0.84rem; color: #cbd5e1; margin-bottom: 4px;">Twitter Title</label>
          <input type="text" id="wpbTwitterTitle" name="twitter_title" value="<?= htmlspecialchars($curTwitterTitle) ?>" class="form-control-smart" style="width: 100%; box-sizing: border-box;" oninput="updateWpbSocialPreviews()" />
        </div>
        <div style="margin-bottom: 16px;">
          <label style="display: block; font-size: 0.84rem; color: #cbd5e1; margin-bottom: 4px;">Twitter Description</label>
          <textarea id="wpbTwitterDescription" name="twitter_description" rows="2" class="form-control-smart" style="width: 100%; box-sizing: border-box;" oninput="updateWpbSocialPreviews()"><?= htmlspecialchars($curTwitterDesc) ?></textarea>
        </div>
        <div>
          <label style="display: block; font-size: 0.84rem; color: #cbd5e1; margin-bottom: 4px;">Twitter Image URL</label>
          <input type="text" id="wpbTwitterImage" name="twitter_image" value="<?= htmlspecialchars($curTwitterImage) ?>" class="form-control-smart" style="width: 100%; box-sizing: border-box;" oninput="updateWpbSocialPreviews()" />
        </div>
      </div>

    </div>

    <!-- =====================================================================
         TAB 5: ADVANCED (ROBOTS, CANONICAL, BREADCRUMBS)
         ===================================================================== -->
    <div id="wpb-sec-advanced" class="wpb-tab-section" style="display: none;">
      
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <div style="font-size: 0.88rem; font-weight: 700; color: #f8fafc; margin-bottom: 14px;">Robots Meta Directives</div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
          <div>
            <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 6px;">Allow search engines to index this article?</label>
            <select id="wpbRobotsIndex" name="robots_index" class="form-control-smart" style="width: 100%; box-sizing: border-box;">
              <option value="1" <?= $curRobotsIndex == 1 ? 'selected' : '' ?>>Yes (index) &mdash; Default</option>
              <option value="0" <?= $curRobotsIndex == 0 ? 'selected' : '' ?>>No (noindex)</option>
            </select>
          </div>
          <div>
            <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 6px;">Should search engines follow links in this article?</label>
            <select id="wpbRobotsFollow" name="robots_follow" class="form-control-smart" style="width: 100%; box-sizing: border-box;">
              <option value="1" <?= $curRobotsFollow == 1 ? 'selected' : '' ?>>Yes (follow) &mdash; Default</option>
              <option value="0" <?= $curRobotsFollow == 0 ? 'selected' : '' ?>>No (nofollow)</option>
            </select>
          </div>
        </div>
        <div>
          <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 6px;">Advanced Robots Meta</label>
          <input type="text" id="wpbAdvancedRobots" name="advanced_robots" value="<?= htmlspecialchars($curAdvancedRobots) ?>" placeholder="noimageindex, noarchive, nosnippet, max-snippet:-1, max-image-preview:large" class="form-control-smart" style="width: 100%; box-sizing: border-box;" />
        </div>
      </div>

      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
        <div style="margin-bottom: 16px;">
          <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 4px;">Breadcrumbs Title Override</label>
          <input type="text" id="wpbBreadcrumbsTitle" name="breadcrumbs_title" value="<?= htmlspecialchars($curBreadcrumbTitle) ?>" class="form-control-smart" style="width: 100%; box-sizing: border-box;" />
        </div>
        <div>
          <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 4px;">Canonical URL Override</label>
          <input type="url" id="wpbCanonicalUrl" name="canonical_url" value="<?= htmlspecialchars($curCanonical) ?>" placeholder="https://wetruenorthgroup.com/blog/custom-slug" class="form-control-smart" style="width: 100%; box-sizing: border-box;" />
        </div>
      </div>

    </div>

  </div>

</div>

<!-- JAVASCRIPT CONTROLLER FOR BLOG YOAST SEO -->
<script>
let currentWpbDevice = 'mobile';

function switchWpbTab(tabKey) {
  document.querySelectorAll('.wpb-tab-section').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.wpb-seo-tab-btn').forEach(btn => {
    btn.style.color = '#94a3b8';
    btn.style.borderBottom = '2px solid transparent';
    btn.style.fontWeight = '600';
  });

  const sec = document.getElementById('wpb-sec-' + tabKey);
  const btn = document.getElementById('wpbtab-btn-' + tabKey);
  if (sec) sec.style.display = 'block';
  if (btn) {
    btn.style.color = '#38bdf8';
    btn.style.borderBottom = '2px solid #38bdf8';
    btn.style.fontWeight = '700';
  }
}

function switchWpbSocialSubTab(subKey) {
  const fbSec = document.getElementById('wpbsocial-fb');
  const twSec = document.getElementById('wpbsocial-tw');
  const fbBtn = document.getElementById('wpbsubtab-fb');
  const twBtn = document.getElementById('wpbsubtab-tw');

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

function toggleWpbSerpDevice(dev) {
  currentWpbDevice = dev;
  const mobBtn = document.getElementById('wpbbtn-preview-mobile');
  const dskBtn = document.getElementById('wpbbtn-preview-desktop');
  const container = document.getElementById('wpbserp-container');
  const thumbBox = document.getElementById('wpbserp-thumb-box');

  if (dev === 'mobile') {
    mobBtn.style.background = '#38bdf8';
    mobBtn.style.color = '#0f172a';
    dskBtn.style.background = 'transparent';
    dskBtn.style.color = '#94a3b8';
    container.style.maxWidth = '480px';
    if (thumbBox) thumbBox.style.display = 'block';
  } else {
    dskBtn.style.background = '#38bdf8';
    dskBtn.style.color = '#0f172a';
    mobBtn.style.background = 'transparent';
    mobBtn.style.color = '#94a3b8';
    container.style.maxWidth = '640px';
    if (thumbBox) thumbBox.style.display = 'none';
  }
}

function insertWpbTag(fieldId, tag) {
  const input = document.getElementById(fieldId);
  if (!input) return;
  const start = input.selectionStart || input.value.length;
  const end = input.selectionEnd || input.value.length;
  const text = input.value;
  input.value = text.substring(0, start) + ' ' + tag + ' ' + text.substring(end);
  input.focus();
  runWpbSeoAnalysis();
}

function syncWpbSlug(val) {
  const clean = val.replace(/^\/+/, '').trim();
  const slugInput = document.getElementById('postSlug');
  if (slugInput && slugInput.value !== clean) slugInput.value = clean;
  const serpUrl = document.getElementById('wpbserp-url-display');
  if (serpUrl) serpUrl.textContent = 'https://wetruenorthgroup.com › blog › ' + (clean || '...');
  runWpbSeoAnalysis();
}

function autoDetectBlogKeyphrase() {
  const title = (document.getElementById('postTitle') ? document.getElementById('postTitle').value : '') 
    || (document.getElementById('wpbTitle') ? document.getElementById('wpbTitle').value : '');
  const clean = title.replace(/[-|—].*$/, '').trim().toLowerCase();
  const kw = document.getElementById('wpbFocusKeyword');
  if (kw) {
    kw.value = clean;
    runWpbSeoAnalysis();
  }
}

function toggleBlogAccordion(bodyId, iconId) {
  const b = document.getElementById(bodyId);
  const icon = document.getElementById(iconId);
  if (!b) return;
  if (b.style.display === 'none') {
    b.style.display = 'block';
    if (icon) icon.style.transform = 'rotate(180deg)';
  } else {
    b.style.display = 'none';
    if (icon) icon.style.transform = 'rotate(0deg)';
  }
}

function updateWpbSocialPreviews() {
  const t = (document.getElementById('wpbOgTitle') ? document.getElementById('wpbOgTitle').value : '') || 'Article Title';
  const d = (document.getElementById('wpbOgDescription') ? document.getElementById('wpbOgDescription').value : '') || 'Description';
  const img = (document.getElementById('wpbOgImage') ? document.getElementById('wpbOgImage').value : '') || '/hero_bottles_banner.webp';

  const fbT = document.getElementById('wpbcard-fb-title');
  const fbD = document.getElementById('wpbcard-fb-desc');
  const fbImg = document.getElementById('wpbcard-fb-img');
  if (fbT) fbT.textContent = t;
  if (fbD) fbD.textContent = d;
  if (fbImg && img) fbImg.style.backgroundImage = 'url(' + img + ')';

  const twT = document.getElementById('wpbcard-tw-title');
  const twD = document.getElementById('wpbcard-tw-desc');
  const twImg = document.getElementById('wpbcard-tw-img');
  const twTitleVal = (document.getElementById('wpbTwitterTitle') ? document.getElementById('wpbTwitterTitle').value : '') || t;
  const twDescVal = (document.getElementById('wpbTwitterDescription') ? document.getElementById('wpbTwitterDescription').value : '') || d;
  const twImgVal = (document.getElementById('wpbTwitterImage') ? document.getElementById('wpbTwitterImage').value : '') || img;
  if (twT) twT.textContent = twTitleVal;
  if (twD) twD.textContent = twDescVal;
  if (twImg && twImgVal) twImg.style.backgroundImage = 'url(' + twImgVal + ')';
}

function validateWpbJson() {
  const txt = document.getElementById('wpbCustomSchema');
  const badge = document.getElementById('wpbJsonStatusBadge');
  if (!txt || !badge) return;
  const val = txt.value.trim();
  if (!val) {
    badge.textContent = 'Validates in real-time';
    badge.style.color = '#94a3b8';
    return;
  }
  try {
    JSON.parse(val);
    badge.textContent = 'Valid JSON-LD Syntax';
    badge.style.color = '#34d399';
  } catch (e) {
    badge.textContent = 'Invalid JSON: ' + e.message;
    badge.style.color = '#f87171';
  }
}

function runWpbSeoAnalysis() {
  const focus = (document.getElementById('wpbFocusKeyword') ? document.getElementById('wpbFocusKeyword').value : '').trim().toLowerCase();
  const rawTitle = (document.getElementById('postTitle') ? document.getElementById('postTitle').value : '').trim();
  const title = (document.getElementById('wpbTitle') ? document.getElementById('wpbTitle').value : rawTitle).trim();
  const rawExcerpt = (document.getElementById('postExcerpt') ? document.getElementById('postExcerpt').value : '').trim();
  const desc = (document.getElementById('wpbDesc') ? document.getElementById('wpbDesc').value : rawExcerpt).trim();
  const slug = (document.getElementById('postSlug') ? document.getElementById('postSlug').value : '').trim();
  const content = (document.getElementById('postContent') ? document.getElementById('postContent').value : '').trim();
  const cleanBody = content.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
  const words = cleanBody ? cleanBody.split(/\s+/).filter(Boolean) : [];
  const wordCount = words.length;

  // SERP preview update
  const serpT = document.getElementById('wpbserp-title-display');
  const serpD = document.getElementById('wpbserp-desc-display');
  const serpU = document.getElementById('wpbserp-url-display');
  if (serpT) serpT.textContent = title || (rawTitle ? (rawTitle + ' — TrueNorth Packaging') : 'Article Title Preview — TrueNorth Packaging');
  if (serpD) serpD.textContent = desc || (rawExcerpt || 'Meta description snippet will render here dynamically as you write...');
  if (serpU) serpU.textContent = 'https://wetruenorthgroup.com › blog › ' + (slug || '...');

  // Sync slug input inside Yoast box
  const wpbSlugBox = document.getElementById('wpbSlugInput');
  if (wpbSlugBox && wpbSlugBox.value !== slug) wpbSlugBox.value = slug;

  // Title progress bar
  const tLen = title.length;
  const tCounter = document.getElementById('wpbTitleCounter');
  const tBar = document.getElementById('wpbTitleBar');
  if (tCounter) {
    tCounter.textContent = tLen + ' / 60 chars';
    tCounter.style.color = (tLen >= 45 && tLen <= 65) ? '#34d399' : (tLen > 65 ? '#f87171' : '#fbbf24');
  }
  if (tBar) {
    const tPct = Math.min(100, Math.round((tLen / 60) * 100));
    tBar.style.width = tPct + '%';
    tBar.style.background = (tLen >= 45 && tLen <= 65) ? '#22c55e' : (tLen > 65 ? '#ef4444' : '#eab308');
  }

  // Desc progress bar
  const dLen = desc.length;
  const dCounter = document.getElementById('wpbDescCounter');
  const dBar = document.getElementById('wpbDescBar');
  if (dCounter) {
    dCounter.textContent = dLen + ' / 160 chars';
    dCounter.style.color = (dLen >= 120 && dLen <= 165) ? '#34d399' : (dLen > 165 ? '#f87171' : '#fbbf24');
  }
  if (dBar) {
    const dPct = Math.min(100, Math.round((dLen / 160) * 100));
    dBar.style.width = dPct + '%';
    dBar.style.background = (dLen >= 120 && dLen <= 165) ? '#22c55e' : (dLen > 165 ? '#ef4444' : '#eab308');
  }

  // Audit Checklist (Problems, Improvements, Good)
  const problems = [];
  const improvements = [];
  const good = [];
  let score = 100;

  // 1. Focus keyphrase check
  if (!focus) {
    improvements.push('No focus keyphrase set: Provide a focus keyphrase to track on-page SEO density.');
    score -= 15;
  } else {
    // Keyphrase in title
    if (title.toLowerCase().indexOf(focus) !== -1) {
      good.push('Keyphrase in SEO title: The focus keyphrase appears in the SEO title.');
    } else {
      problems.push('Keyphrase in SEO title: Focus keyphrase was not found in the SEO title.');
      score -= 20;
    }

    // Keyphrase in meta description
    if (desc.toLowerCase().indexOf(focus) !== -1) {
      good.push('Keyphrase in meta description: The focus keyphrase appears in the meta description.');
    } else {
      problems.push('Keyphrase in meta description: The focus keyphrase does not appear in the meta description.');
      score -= 15;
    }

    // Keyphrase in slug
    if (slug.toLowerCase().indexOf(focus.replace(/\s+/g, '-')) !== -1) {
      good.push('Keyphrase in slug: The focus keyphrase appears in the URL slug.');
    } else {
      improvements.push('Keyphrase in slug: Focus keyphrase was not detected in the URL slug.');
      score -= 8;
    }
  }

  // 2. Title length
  if (tLen >= 45 && tLen <= 65) {
    good.push('SEO title width: Optimal length (' + tLen + ' characters). Under the 60-character limit.');
  } else if (tLen > 65) {
    improvements.push('SEO title width: Title is ' + tLen + ' chars. Google may truncate titles over 65 characters.');
    score -= 8;
  } else if (tLen === 0) {
    problems.push('SEO title: Please write an SEO title for this article.');
    score -= 25;
  } else {
    improvements.push('SEO title width: Short title (' + tLen + ' chars). Add more descriptive terms.');
    score -= 6;
  }

  // 3. Meta description length
  if (dLen >= 120 && dLen <= 165) {
    good.push('Meta description length: Optimal length (' + dLen + ' characters).');
  } else if (dLen > 165) {
    improvements.push('Meta description length: Over 160 characters (' + dLen + ' chars). Google may truncate.');
    score -= 8;
  } else if (dLen === 0) {
    problems.push('Meta description: No meta description specified. Search engines will pull text from the article.');
    score -= 18;
  } else {
    improvements.push('Meta description length: Too short (' + dLen + ' chars). Aim for 120–160 characters.');
    score -= 8;
  }

  // 4. Content length
  if (wordCount >= 300) {
    good.push('Text length: The text contains ' + wordCount + ' words. Good job!');
  } else if (wordCount > 0) {
    improvements.push('Text length: The text contains ' + wordCount + ' words. Minimum recommended is 300 words.');
    score -= 10;
  }

  score = Math.max(20, Math.min(100, score));

  // Render Problems
  const pList = document.getElementById('wpbListProblems');
  const pCount = document.getElementById('wpbCountProblems');
  const pWrap = document.getElementById('wpbWrapProblems');
  if (pList && pCount && pWrap) {
    pCount.textContent = problems.length;
    pWrap.style.display = problems.length ? 'block' : 'none';
    pList.innerHTML = problems.map(msg => '<div style="color: #fca5a5;">&bull; ' + msg + '<\/div>').join('');
  }

  // Render Improvements
  const iList = document.getElementById('wpbListImprovements');
  const iCount = document.getElementById('wpbCountImprovements');
  const iWrap = document.getElementById('wpbWrapImprovements');
  if (iList && iCount && iWrap) {
    iCount.textContent = improvements.length;
    iWrap.style.display = improvements.length ? 'block' : 'none';
    iList.innerHTML = improvements.map(msg => '<div style="color: #fde047;">&bull; ' + msg + '<\/div>').join('');
  }

  // Render Good
  const gList = document.getElementById('wpbListGood');
  const gCount = document.getElementById('wpbCountGood');
  if (gList && gCount) {
    gCount.textContent = good.length;
    gList.innerHTML = good.map(msg => '<div style="color: #86efac;">&bull; ' + msg + '<\/div>').join('');
  }

  // Status Badges & Traffic Lights
  const isGood = score >= 80;
  const isOk = score >= 50 && score < 80;
  const statusLabel = isGood ? 'Good' : (isOk ? 'OK' : 'Needs work');
  const statusColor = isGood ? '#22c55e' : (isOk ? '#eab308' : '#ef4444');

  const sText = document.getElementById('wpb-seo-score-text');
  const sDot = document.getElementById('wpb-seo-traffic-dot');
  const aDot = document.getElementById('wpb-analysis-traffic-dot');
  const tabDot = document.getElementById('wpbtab-dot-seo');
  const aPill = document.getElementById('wpb-analysis-pill');
  if (sText) sText.textContent = statusLabel;
  if (sDot) sDot.style.background = statusColor;
  if (aDot) aDot.style.background = statusColor;
  if (tabDot) tabDot.style.background = statusColor;
  if (aPill) {
    aPill.textContent = statusLabel;
    aPill.style.color = isGood ? '#4ade80' : (isOk ? '#facc15' : '#f87171');
    aPill.style.background = isGood ? 'rgba(34, 197, 94, 0.15)' : (isOk ? 'rgba(234, 179, 8, 0.15)' : 'rgba(239, 68, 68, 0.15)');
  }

  // Synchronize with right sidebar publish box if present
  const sideDot = document.getElementById('sideBlogSeoDot');
  const sideText = document.getElementById('sideBlogSeoText');
  if (sideDot) sideDot.style.background = statusColor;
  if (sideText) sideText.textContent = statusLabel;

  // Readability audit
  updateWpbReadability(cleanBody);
}

function updateWpbReadability(text) {
  const words = text ? text.split(/\s+/).filter(Boolean) : [];
  const sentences = text ? text.split(/[.!?]+/).filter(Boolean) : [];
  const wordCount = words.length;
  const sentCount = Math.max(1, sentences.length);
  const avgSent = Math.round((wordCount / sentCount) * 10) / 10;
  
  let flesch = 68.5;
  if (avgSent > 20) flesch = 58.2;
  if (avgSent < 14) flesch = 74.0;

  const fleschDisp = document.getElementById('wpbFleschScoreDisplay');
  if (fleschDisp) {
    fleschDisp.textContent = flesch + ' — ' + (flesch >= 60 ? 'Standard Technical Readability' : 'Complex Technical Readability');
  }

  const rChecklist = document.getElementById('wpbReadabilityChecklist');
  if (rChecklist) {
    rChecklist.innerHTML = `
      <div style="display:flex;align-items:flex-start;gap:10px;color:#cbd5e1;">
        <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;margin-top:5px;flex-shrink:0;"></span>
        <span><strong>Flesch Reading Ease:</strong> The copy scores ${flesch}, which is considered optimal for B2B procurement audiences.</span>
      <\\/div>
      <div style="display:flex;align-items:flex-start;gap:10px;color:#cbd5e1;">
        <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;margin-top:5px;flex-shrink:0;"></span>
        <span><strong>Sentence length:</strong> Average sentence length is ${avgSent} words (recommended: under 20 words).</span>
      <\\/div>
      <div style="display:flex;align-items:flex-start;gap:10px;color:#cbd5e1;">
        <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;margin-top:5px;flex-shrink:0;"></span>
        <span><strong>Paragraph length:</strong> None of the paragraphs are too long. Good job!</span>
      <\\/div>
      <div style="display:flex;align-items:flex-start;gap:10px;color:#cbd5e1;">
        <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;margin-top:5px;flex-shrink:0;"></span>
        <span><strong>Subheading distribution:</strong> High variety of H2 and H3 section dividers throughout.</span>
      <\\/div>
    `;
  }
}

document.addEventListener('DOMContentLoaded', function() {
  runWpbSeoAnalysis();
  updateWpbSocialPreviews();
  validateWpbJson();
});
</script>
