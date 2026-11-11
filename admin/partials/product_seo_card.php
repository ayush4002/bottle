<?php
// ==============================================================================
// TRUENORTH CMS — WORDPRESS YOAST & RANK MATH STYLE SEO SUITE
// Provides full 5-tab WordPress SEO panel: SEO, Readability, Schema, Social, Advanced
// Features live mobile/desktop SERP toggles, traffic-light scoring, and rich previews
// ==============================================================================

if (!class_exists('SeoModel')) {
    require_once dirname(__DIR__, 2) . '/app/Models/SeoModel.php';
}

$curSeoTitle = $prodSeo['meta_title'] ?? ($product['seoTitle'] ?? '');
$curSeoDesc = $prodSeo['meta_description'] ?? ($product['seoDescription'] ?? ($product['shortDescription'] ?? ''));
$curSlug = $prodSeo['url_slug'] ?? ($product['slug'] ?? '');
$curSlug = preg_replace('#^/product/#', '', $curSlug);
$curKeywords = $prodSeo['meta_keywords'] ?? '';
$curFocusKeyword = $prodSeo['focus_keyword'] ?? '';
$curCanonical = $prodSeo['canonical_url'] ?? '';
$curH1 = $prodSeo['h1_override'] ?? '';
$curRobotsIndex = isset($prodSeo['robots_index']) ? (int)$prodSeo['robots_index'] : 1;
$curRobotsFollow = isset($prodSeo['robots_follow']) ? (int)$prodSeo['robots_follow'] : 1;
$curOgTitle = $prodSeo['og_title'] ?? '';
$curOgDesc = $prodSeo['og_description'] ?? '';
$curOgImage = $prodSeo['og_image'] ?? ($product['image'] ?? '/logo.webp');
$curTwitterTitle = $prodSeo['twitter_title'] ?? '';
$curTwitterDesc = $prodSeo['twitter_description'] ?? '';
$curTwitterImage = $prodSeo['twitter_image'] ?? $curOgImage;
$curTwitterCard = $prodSeo['twitter_card'] ?? 'summary_large_image';
$curSchemaType = $prodSeo['schema_type'] ?? 'Product';
$curCustomSchema = $prodSeo['custom_schema_json'] ?? '';
$curCornerstone = !empty($prodSeo['cornerstone_content']) ? 1 : 0;
$curBreadcrumbTitle = $prodSeo['breadcrumbs_title'] ?? '';
$curAdvancedRobots = $prodSeo['advanced_robots'] ?? '';
$curFaqs = !empty($prodFaqs) ? $prodFaqs : [];
?>

<!-- WORDPRESS SEO METABOX CONTAINER -->
<div class="form-card wp-seo-metabox" id="wp-seo-panel" style="border: 1px solid #38bdf8; background: #0f172a; border-radius: 16px; margin-bottom: 28px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.35);">
  
  <!-- METABOX HEADER WITH OVERALL SCORES (YOAST STYLE) -->
  <div style="padding: 16px 24px; background: #1e293b; border-bottom: 1px solid #334155; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
    <div style="display: flex; align-items: center; gap: 12px;">
      <div style="width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, #0284c7, #0369a1); display: flex; align-items: center; justify-content: center; color: #fff;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><path d="m8 11 2 2 4-4"/></svg>
      </div>
      <div>
        <div style="font-size: 1.05rem; font-weight: 800; color: #f8fafc; display: flex; align-items: center; gap: 8px;">
          <span>WordPress SEO Suite</span>
          <span style="font-size: 0.72rem; padding: 2px 8px; border-radius: 12px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); font-weight: 700; text-transform: uppercase;">Yoast / Rank Math Engine</span>
        </div>
        <div style="font-size: 0.76rem; color: #94a3b8;">All-in-one on-page SEO, readability, structured schema &amp; social graph manager</div>
      </div>
    </div>

    <!-- LIVE METABOX SCORE PILLS (TRAFFIC LIGHTS) -->
    <div style="display: flex; align-items: center; gap: 10px;">
      <div id="wp-seo-overall-pill" style="display: flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); font-size: 0.8rem; font-weight: 700; color: #4ade80;">
        <span id="wp-seo-traffic-dot" style="width: 10px; height: 10px; border-radius: 50%; background: #22c55e;"></span>
        <span>SEO: <strong id="wp-seo-score-text">85/100 (Good)</strong></span>
      </div>

      <div id="wp-readability-overall-pill" style="display: flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.4); font-size: 0.8rem; font-weight: 700; color: #38bdf8;">
        <span id="wp-readability-traffic-dot" style="width: 10px; height: 10px; border-radius: 50%; background: #38bdf8;"></span>
        <span>Readability: <strong id="wp-readability-score-text">Good</strong></span>
      </div>
    </div>
  </div>

  <!-- WORDPRESS 5-TAB NAVIGATION STRIP -->
  <div style="display: flex; background: #0b1120; border-bottom: 1px solid #334155; padding: 0 16px; overflow-x: auto; gap: 4px;">
    <button type="button" class="wp-tab-btn active" onclick="switchWpSeoTab('seo', this)" style="padding: 14px 18px; background: transparent; border: none; border-bottom: 2px solid #38bdf8; color: #38bdf8; font-weight: 700; font-size: 0.88rem; cursor: pointer; display: flex; align-items: center; gap: 8px; white-space: nowrap;">
      <span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e;" id="tab-dot-seo"></span>
      <span>SEO</span>
    </button>

    <button type="button" class="wp-tab-btn" onclick="switchWpSeoTab('readability', this)" style="padding: 14px 18px; background: transparent; border: none; border-bottom: 2px solid transparent; color: #94a3b8; font-weight: 700; font-size: 0.88rem; cursor: pointer; display: flex; align-items: center; gap: 8px; white-space: nowrap;">
      <span style="width: 8px; height: 8px; border-radius: 50%; background: #38bdf8;" id="tab-dot-readability"></span>
      <span>Readability</span>
    </button>

    <button type="button" class="wp-tab-btn" onclick="switchWpSeoTab('schema', this)" style="padding: 14px 18px; background: transparent; border: none; border-bottom: 2px solid transparent; color: #94a3b8; font-weight: 700; font-size: 0.88rem; cursor: pointer; display: flex; align-items: center; gap: 8px; white-space: nowrap;">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
      <span>Schema</span>
    </button>

    <button type="button" class="wp-tab-btn" onclick="switchWpSeoTab('social', this)" style="padding: 14px 18px; background: transparent; border: none; border-bottom: 2px solid transparent; color: #94a3b8; font-weight: 700; font-size: 0.88rem; cursor: pointer; display: flex; align-items: center; gap: 8px; white-space: nowrap;">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
      <span>Social</span>
    </button>

    <button type="button" class="wp-tab-btn" onclick="switchWpSeoTab('advanced', this)" style="padding: 14px 18px; background: transparent; border: none; border-bottom: 2px solid transparent; color: #94a3b8; font-weight: 700; font-size: 0.88rem; cursor: pointer; display: flex; align-items: center; gap: 8px; white-space: nowrap;">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
      <span>Advanced</span>
    </button>
  </div>

  <!-- TAB BODIES -->
  <div style="padding: 24px;">

    <!-- =====================================================================
         TAB 1: SEO (FOCUS KEYPHRASE, SNIPPET PREVIEW, ANALYSIS)
         ===================================================================== -->
    <div id="wp-tab-content-seo" class="wp-seo-tab-content" style="display: block;">
      
      <!-- FOCUS KEYPHRASE (YOAST PATTERN) -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <label for="focus_keyword" style="display: block; font-size: 0.9rem; font-weight: 800; color: #f8fafc; margin-bottom: 6px;">
          Focus Keyphrase
        </label>
        <p style="font-size: 0.78rem; color: #94a3b8; margin: 0 0 10px 0;">
          Enter the primary search phrase you want this product to rank for in Google search results.
        </p>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <input type="text" id="focus_keyword" name="focus_keyword" value="<?= htmlspecialchars($curFocusKeyword) ?>" placeholder="e.g. 250ml pet bottle wholesale" class="form-control-smart" style="flex: 1; min-width: 260px;" oninput="runWordPressAnalysis()" />
          <button type="button" onclick="autoDetectKeyphrase()" style="background: #334155; border: 1px solid #475569; color: #38bdf8; font-size: 0.8rem; font-weight: 700; padding: 8px 14px; border-radius: 8px; cursor: pointer;">
            Detect from Name
          </button>
        </div>
      </div>

      <!-- GOOGLE SNIPPET PREVIEW (MOBILE VS DESKTOP TOGGLE) -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
          <div style="font-size: 0.82rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">
            Google Search Snippet Preview
          </div>
          <!-- Device Toggle Buttons (WordPress Classic) -->
          <div style="display: inline-flex; background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 3px;">
            <button type="button" id="btn-device-mobile" onclick="setSerpDevice('mobile')" style="padding: 6px 12px; background: #38bdf8; color: #0f172a; border: none; border-radius: 6px; font-size: 0.76rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
              Mobile result
            </button>
            <button type="button" id="btn-device-desktop" onclick="setSerpDevice('desktop')" style="padding: 6px 12px; background: transparent; color: #94a3b8; border: none; border-radius: 6px; font-size: 0.76rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
              Desktop result
            </button>
          </div>
        </div>

        <!-- AUTHENTIC GOOGLE MOBILE PREVIEW CONTAINER -->
        <div id="serp-container-mobile" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; max-width: 580px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); font-family: Roboto, -apple-system, sans-serif;">
          <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
            <div style="width: 26px; height: 26px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0;">
              <img src="/logo_svg.svg" style="width: 15px; height: 15px;" alt="Icon" />
            </div>
            <div>
              <div style="font-size: 0.8rem; font-weight: 500; color: #202124; line-height: 1.2;">TrueNorth Group</div>
              <div style="font-size: 0.72rem; color: #5f6368; word-break: break-all;">
                https://wetruenorthgroup.com &rsaquo; product &rsaquo; <span id="mobile-preview-slug"><?= htmlspecialchars($curSlug ?: 'item-slug') ?></span>
              </div>
            </div>
          </div>
          <div id="mobile-preview-title" style="font-size: 1.18rem; color: #1a0dab; font-weight: 400; line-height: 1.35; margin-bottom: 6px; cursor: pointer;">
            <?= htmlspecialchars($curSeoTitle ?: ($product['name'] ?? 'Product Title Preview')) ?> | TrueNorth Group
          </div>
          <div id="mobile-preview-desc" style="font-size: 0.85rem; color: #4d5156; line-height: 1.5; word-break: break-word;">
            <?= htmlspecialchars($curSeoDesc ?: 'High-precision primary packaging containers engineered in ISO certified cleanrooms. Material options, custom neck finishes, and B2B pricing.') ?>
          </div>
        </div>

        <!-- AUTHENTIC GOOGLE DESKTOP PREVIEW CONTAINER -->
        <div id="serp-container-desktop" style="display: none; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 24px; max-width: 680px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); font-family: Arial, sans-serif;">
          <div style="font-size: 0.8rem; color: #202124; margin-bottom: 3px;">
            https://wetruenorthgroup.com &rsaquo; product &rsaquo; <span id="desktop-preview-slug"><?= htmlspecialchars($curSlug ?: 'item-slug') ?></span>
          </div>
          <div id="desktop-preview-title" style="font-size: 1.28rem; color: #1a0dab; font-weight: 400; line-height: 1.3; margin-bottom: 4px; cursor: pointer; text-decoration: none;">
            <?= htmlspecialchars($curSeoTitle ?: ($product['name'] ?? 'Product Title Preview')) ?> | TrueNorth Group
          </div>
          <div id="desktop-preview-desc" style="font-size: 0.88rem; color: #4d5156; line-height: 1.48; word-break: break-word;">
            <?= htmlspecialchars($curSeoDesc ?: 'High-precision primary packaging containers engineered in ISO certified cleanrooms. Material options, custom neck finishes, and B2B pricing.') ?>
          </div>
        </div>
      </div>

      <!-- SNIPPET EDIT FIELDS (TITLE, SLUG, DESCRIPTION) -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        
        <!-- SEO TITLE WITH VARIABLE TAG INSERTERS -->
        <div style="margin-bottom: 20px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; flex-wrap: wrap; gap: 6px;">
            <label for="meta_title" style="font-size: 0.88rem; font-weight: 700; color: #f8fafc; margin: 0;">
              SEO Title Tag
            </label>
            <div style="display: flex; gap: 4px; align-items: center;">
              <span id="title-char-badge" style="font-size: 0.76rem; font-weight: 700; color: #4ade80;">0 / 60 chars</span>
            </div>
          </div>
          <!-- Variable Tags (Yoast Style) -->
          <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 8px;">
            <button type="button" onclick="insertTitleVar('Title')" style="background: #334155; color: #cbd5e1; border: 1px solid #475569; padding: 3px 8px; border-radius: 4px; font-size: 0.72rem; cursor: pointer;">+ Title</button>
            <button type="button" onclick="insertTitleVar('Sep')" style="background: #334155; color: #cbd5e1; border: 1px solid #475569; padding: 3px 8px; border-radius: 4px; font-size: 0.72rem; cursor: pointer;">+ Separator</button>
            <button type="button" onclick="insertTitleVar('SiteTitle')" style="background: #334155; color: #cbd5e1; border: 1px solid #475569; padding: 3px 8px; border-radius: 4px; font-size: 0.72rem; cursor: pointer;">+ Site title</button>
            <button type="button" onclick="insertTitleVar('Category')" style="background: #334155; color: #cbd5e1; border: 1px solid #475569; padding: 3px 8px; border-radius: 4px; font-size: 0.72rem; cursor: pointer;">+ Category</button>
          </div>
          <input type="text" id="meta_title" name="meta_title" value="<?= htmlspecialchars($curSeoTitle) ?>" placeholder="e.g. 250ml Boston Round PET Bottle | Bulk Wholesale Pricing | TrueNorth" class="form-control-smart" maxlength="80" oninput="runWordPressAnalysis()" />
          <div style="width: 100%; height: 5px; background: #334155; border-radius: 3px; margin-top: 6px; overflow: hidden;">
            <div id="title-progress-bar" style="height: 100%; width: 0%; background: #22c55e; transition: width 0.2s, background 0.2s;"></div>
          </div>
        </div>

        <!-- URL SLUG -->
        <div style="margin-bottom: 20px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
            <label for="seo_slug" style="font-size: 0.88rem; font-weight: 700; color: #f8fafc; margin: 0;">Slug (Permalink)</label>
            <button type="button" onclick="autoGenerateSlugFromProduct()" style="background: #334155; border: 1px solid #475569; color: #38bdf8; font-size: 0.76rem; font-weight: 700; padding: 3px 10px; border-radius: 6px; cursor: pointer;">
              Auto-Slug
            </button>
          </div>
          <div style="display: flex; align-items: center; background: #0f172a; border: 1px solid #475569; border-radius: 8px; overflow: hidden;">
            <span style="padding: 10px 14px; color: #64748b; font-size: 0.85rem; font-family: monospace; background: #0b1120; border-right: 1px solid #334155;">/product/</span>
            <input type="text" id="seo_slug" name="seo_slug" value="<?= htmlspecialchars($curSlug) ?>" placeholder="250ml-boston-round-pet-bottle" class="form-control-smart" style="border: none; background: transparent; font-family: monospace; color: #38bdf8; width: 100%;" oninput="runWordPressAnalysis()" />
            <input type="hidden" name="old_seo_slug" value="<?= htmlspecialchars($curSlug) ?>" />
          </div>
        </div>

        <!-- META DESCRIPTION -->
        <div style="margin-bottom: 14px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
            <label for="meta_description" style="font-size: 0.88rem; font-weight: 700; color: #f8fafc; margin: 0;">
              Meta Description
            </label>
            <span id="desc-char-badge" style="font-size: 0.76rem; font-weight: 700; color: #4ade80;">0 / 160 chars</span>
          </div>
          <textarea id="meta_description" name="meta_description" rows="3" class="form-control-smart" maxlength="220" placeholder="Target 120-160 chars. Write a compelling product summary with focus keyphrase to maximize search click-through rate." oninput="runWordPressAnalysis()"><?= htmlspecialchars($curSeoDesc) ?></textarea>
          <div style="width: 100%; height: 5px; background: #334155; border-radius: 3px; margin-top: 6px; overflow: hidden;">
            <div id="desc-progress-bar" style="height: 100%; width: 0%; background: #22c55e; transition: width 0.2s, background 0.2s;"></div>
          </div>
        </div>
      </div>

      <!-- CORNERSTONE CONTENT TOGGLE & PRIMARY H1 -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 18px;">
          <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer;">
            <input type="checkbox" name="cornerstone_content" value="1" <?= $curCornerstone ? 'checked' : '' ?> style="width: 20px; height: 20px; margin-top: 2px; accent-color: #38bdf8;" />
            <div>
              <div style="font-size: 0.9rem; font-weight: 800; color: #f8fafc;">Mark as cornerstone content</div>
              <div style="font-size: 0.76rem; color: #94a3b8; margin-top: 2px;">Cornerstone content should be the most important and comprehensive products on your website.</div>
            </div>
          </label>
        </div>

        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 18px;">
          <label for="h1_override" style="display: block; font-size: 0.88rem; font-weight: 700; color: #f8fafc; margin-bottom: 4px;">Primary H1 Override</label>
          <input type="text" id="h1_override" name="h1_override" value="<?= htmlspecialchars($curH1) ?>" placeholder="Leave blank to use product name" class="form-control-smart" oninput="runWordPressAnalysis()" />
        </div>
      </div>

      <!-- REAL-TIME SEO ANALYSIS (AUTHENTIC WORDPRESS TRAFFIC LIGHT REPORT) -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 22px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
          <div style="font-size: 1rem; font-weight: 800; color: #f8fafc; display: flex; align-items: center; gap: 8px;">
            <span>SEO Analysis</span>
            <span id="analysis-summary-badge" style="background: #0f172a; border: 1px solid #475569; padding: 2px 10px; border-radius: 12px; font-size: 0.75rem; color: #38bdf8;">Evaluating...</span>
          </div>
          <span style="font-size: 0.76rem; color: #94a3b8;">Updates automatically as you type</span>
        </div>

        <!-- PROBLEMS (RED) -->
        <div style="margin-bottom: 14px;">
          <div style="font-size: 0.82rem; font-weight: 800; color: #f87171; text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <span style="width: 10px; height: 10px; border-radius: 50%; background: #ef4444; display: inline-block;"></span>
            <span>Problems (<span id="cnt-problems">0</span>)</span>
          </div>
          <div id="list-problems" style="display: flex; flex-direction: column; gap: 6px; padding-left: 18px;">
            <!-- Injected by JS -->
          </div>
        </div>

        <!-- IMPROVEMENTS (AMBER/ORANGE) -->
        <div style="margin-bottom: 14px;">
          <div style="font-size: 0.82rem; font-weight: 800; color: #fbbf24; text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <span style="width: 10px; height: 10px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
            <span>Improvements (<span id="cnt-improvements">0</span>)</span>
          </div>
          <div id="list-improvements" style="display: flex; flex-direction: column; gap: 6px; padding-left: 18px;">
            <!-- Injected by JS -->
          </div>
        </div>

        <!-- GOOD RESULTS (GREEN) -->
        <div>
          <div style="font-size: 0.82rem; font-weight: 800; color: #4ade80; text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <span style="width: 10px; height: 10px; border-radius: 50%; background: #22c55e; display: inline-block;"></span>
            <span>Good results (<span id="cnt-good">0</span>)</span>
          </div>
          <div id="list-good" style="display: flex; flex-direction: column; gap: 6px; padding-left: 18px;">
            <!-- Injected by JS -->
          </div>
        </div>

      </div>

    </div>

    <!-- =====================================================================
         TAB 2: READABILITY (FLESCH SCORE, PASSIVE VOICE, SENTENCES)
         ===================================================================== -->
    <div id="wp-tab-content-readability" class="wp-seo-tab-content" style="display: none;">
      
      <!-- FLESCH READING EASE SCORE CARD -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
        <div>
          <div style="font-size: 0.82rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Flesch Reading Ease Score</div>
          <div id="flesch-score-value" style="font-size: 1.8rem; font-weight: 900; color: #38bdf8;">72.5 / 100</div>
          <div id="flesch-score-label" style="font-size: 0.82rem; color: #cbd5e1;">Fairly easy to read. Engaging for packaging procurement engineers and B2B buyers.</div>
        </div>
        <div style="background: #0f172a; padding: 12px 18px; border-radius: 10px; border: 1px solid #334155; text-align: right;">
          <div style="font-size: 0.76rem; color: #94a3b8;">Target Standard:</div>
          <div style="font-size: 0.9rem; font-weight: 700; color: #4ade80;">60 &ndash; 80 points</div>
        </div>
      </div>

      <!-- READABILITY ANALYSIS REPORT (TRAFFIC LIGHTS) -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 22px;">
        <div style="font-size: 1rem; font-weight: 800; color: #f8fafc; margin-bottom: 16px;">
          Readability Analysis Checks
        </div>

        <div id="readability-items-list" style="display: flex; flex-direction: column; gap: 10px; font-size: 0.84rem;">
          <!-- Dynamically populated by JS: Passive voice, Consecutive sentences, Subheadings, Paragraph length, Sentence length, Transition words -->
        </div>
      </div>

    </div>

    <!-- =====================================================================
         TAB 3: SCHEMA (STRUCTURED DATA & RICH SNIPPETS)
         ===================================================================== -->
    <div id="wp-tab-content-schema" class="wp-seo-tab-content" style="display: none;">
      
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
          <div>
            <label for="schema_type" style="font-size: 0.9rem; font-weight: 800; color: #f8fafc; margin: 0;">Primary Schema.org Entity</label>
            <div style="font-size: 0.76rem; color: #94a3b8;">Configures structured JSON-LD data for Google Rich Results.</div>
          </div>
          <select id="schema_type" name="schema_type" class="form-control-smart" style="max-width: 220px;" onchange="toggleSchemaTypeView()">
            <option value="Product" <?= $curSchemaType === 'Product' ? 'selected' : '' ?>>Product (with Offer &amp; Price)</option>
            <option value="WebPage" <?= $curSchemaType === 'WebPage' ? 'selected' : '' ?>>WebPage</option>
            <option value="ItemPage" <?= $curSchemaType === 'ItemPage' ? 'selected' : '' ?>>ItemPage</option>
            <option value="Article" <?= $curSchemaType === 'Article' ? 'selected' : '' ?>>Article / Technical Guide</option>
            <option value="FAQPage" <?= $curSchemaType === 'FAQPage' ? 'selected' : '' ?>>FAQPage</option>
          </select>
        </div>

        <!-- PRODUCT OFFER DETAILS PREVIEW -->
        <div id="schema-product-details-box" style="background: #0f172a; border: 1px solid #334155; border-radius: 10px; padding: 14px; font-size: 0.82rem; color: #cbd5e1; line-height: 1.6;">
          <strong style="color: #38bdf8;">Automatic Product Schema Entities Generated:</strong><br />
          &bull; <strong>Name:</strong> <?= htmlspecialchars($product['name'] ?? 'Container') ?><br />
          &bull; <strong>SKU / MPN:</strong> <?= htmlspecialchars($product['sku'] ?? ($product['id'] ?? 'TN-SKU')) ?><br />
          &bull; <strong>Brand:</strong> TrueNorth Group (Primary Packaging)<br />
          &bull; <strong>Offer:</strong> Currency: INR, Availability: InStock, Condition: NewCondition
        </div>
      </div>

      <!-- FAQ REPEATER FOR GOOGLE FAQPAGE RICH SNIPPETS -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
          <div>
            <div style="font-size: 0.9rem; font-weight: 800; color: #f8fafc; display: flex; align-items: center; gap: 6px;">
              <span>Product FAQs (Google FAQPage Schema)</span>
            </div>
            <div style="font-size: 0.76rem; color: #94a3b8;">Add common buyer questions (MOQ, certifications, resin) to claim Google search expanders.</div>
          </div>
          <button type="button" onclick="addWpFaqRow()" style="background: #0284c7; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; font-size: 0.82rem; font-weight: 700; cursor: pointer;">
            + Add FAQ Question
          </button>
        </div>

        <div id="wp-faq-rows-container">
          <?php if (!empty($curFaqs)): ?>
            <?php foreach ($curFaqs as $idx => $faq): ?>
              <div class="wp-faq-row-item" style="background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 14px; margin-bottom: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                  <span style="font-size: 0.78rem; font-weight: 700; color: #38bdf8;">Question #<?= $idx + 1 ?></span>
                  <button type="button" onclick="this.closest('.wp-faq-row-item').remove()" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 4px; padding: 2px 8px; font-size: 0.74rem; cursor: pointer;">
                    Remove
                  </button>
                </div>
                <input type="text" name="faq_questions[]" value="<?= htmlspecialchars($faq['question'] ?? '') ?>" placeholder="e.g. Can this container be manufactured with 100% PCR resin?" class="form-control-smart" style="margin-bottom: 8px;" />
                <textarea name="faq_answers[]" rows="2" placeholder="e.g. Yes, TrueNorth molds this bottle in 100% Post-Consumer Recycled (PCR) food-grade PET." class="form-control-smart"><?= htmlspecialchars($faq['answer'] ?? '') ?></textarea>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div id="wp-no-faqs-hint" style="color: #64748b; font-size: 0.82rem; font-style: italic; padding: 10px 0;">
              No FAQs added yet. Click "+ Add FAQ Question" to generate Google FAQ Rich Snippets.
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- CUSTOM JSON-LD WITH REAL-TIME SYNTAX VALIDATOR -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
          <label for="custom_schema_json" style="font-size: 0.88rem; font-weight: 700; color: #f8fafc; margin: 0;">
            Custom Schema.org JSON-LD (Optional)
          </label>
          <span id="json-validator-badge" style="font-size: 0.74rem; font-weight: 700; padding: 2px 8px; border-radius: 4px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3);">
            Ready
          </span>
        </div>
        <textarea id="custom_schema_json" name="custom_schema_json" rows="4" class="form-control-smart" style="font-family: monospace; font-size: 0.82rem;" placeholder='{"@context": "https://schema.org", "@type": "Product", ...}' oninput="validateCustomJson()"><?= htmlspecialchars($curCustomSchema) ?></textarea>
      </div>

    </div>

    <!-- =====================================================================
         TAB 4: SOCIAL (FACEBOOK OPEN GRAPH & TWITTER CARD PREVIEWS)
         ===================================================================== -->
    <div id="wp-tab-content-social" class="wp-seo-tab-content" style="display: none;">
      
      <!-- SUB-TABS: FACEBOOK VS TWITTER -->
      <div style="display: flex; gap: 8px; margin-bottom: 20px;">
        <button type="button" id="btn-social-fb" onclick="switchSocialSubTab('fb')" style="padding: 8px 16px; background: #0284c7; color: #fff; border: none; border-radius: 8px; font-size: 0.82rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
          <span>Facebook (OpenGraph)</span>
        </button>
        <button type="button" id="btn-social-tw" onclick="switchSocialSubTab('tw')" style="padding: 8px 16px; background: #1e293b; color: #94a3b8; border: 1px solid #334155; border-radius: 8px; font-size: 0.82rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
          <span>Twitter / X Card</span>
        </button>
      </div>

      <!-- FACEBOOK PANEL -->
      <div id="wp-social-fb-panel">
        <!-- Facebook Live Share Card Preview -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
          <div style="font-size: 0.8rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 12px;">Facebook Share Card Preview</div>
          <div style="background: #ffffff; border: 1px solid #dadde1; border-radius: 8px; overflow: hidden; max-width: 520px; font-family: Helvetica, Arial, sans-serif;">
            <div style="width: 100%; height: 260px; background: #0f172a; overflow: hidden; display: flex; align-items: center; justify-content: center;">
              <img id="fb-card-img" src="<?= htmlspecialchars($curOgImage) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='/logo.webp';" alt="OG Preview" />
            </div>
            <div style="padding: 12px 16px; background: #f0f2f5;">
              <div style="font-size: 0.72rem; color: #65676b; text-transform: uppercase; letter-spacing: 0.5px;">WETRUENORTHGROUP.COM</div>
              <div id="fb-card-title" style="font-size: 1.05rem; font-weight: 700; color: #050505; line-height: 1.3; margin: 4px 0;">
                <?= htmlspecialchars($curOgTitle ?: ($curSeoTitle ?: ($product['name'] ?? 'Product Title'))) ?>
              </div>
              <div id="fb-card-desc" style="font-size: 0.82rem; color: #65676b; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                <?= htmlspecialchars($curOgDesc ?: ($curSeoDesc ?: 'Engineered primary packaging containers manufactured in ISO certified cleanrooms.')) ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Facebook Fields -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
          <div style="margin-bottom: 16px;">
            <label for="og_title" style="display: block; font-size: 0.85rem; font-weight: 700; color: #f8fafc; margin-bottom: 6px;">Facebook Title (Defaults to SEO Title)</label>
            <input type="text" id="og_title" name="og_title" value="<?= htmlspecialchars($curOgTitle) ?>" placeholder="Leave blank to use SEO Title" class="form-control-smart" oninput="updateSocialPreviews()" />
          </div>
          <div style="margin-bottom: 16px;">
            <label for="og_description" style="display: block; font-size: 0.85rem; font-weight: 700; color: #f8fafc; margin-bottom: 6px;">Facebook Description</label>
            <textarea id="og_description" name="og_description" rows="2" class="form-control-smart" placeholder="Leave blank to use Meta Description" oninput="updateSocialPreviews()"><?= htmlspecialchars($curOgDesc) ?></textarea>
          </div>
          <div>
            <label for="og_image" style="display: block; font-size: 0.85rem; font-weight: 700; color: #f8fafc; margin-bottom: 6px;">Facebook Share Image URL</label>
            <input type="text" id="og_image" name="og_image" value="<?= htmlspecialchars($curOgImage) ?>" placeholder="/uploads/..." class="form-control-smart" oninput="updateSocialPreviews()" />
          </div>
        </div>
      </div>

      <!-- TWITTER PANEL -->
      <div id="wp-social-tw-panel" style="display: none;">
        <!-- Twitter Live Share Card Preview -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
          <div style="font-size: 0.8rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 12px;">Twitter / X Card Preview</div>
          <div style="background: #000000; border: 1px solid #2f3336; border-radius: 14px; overflow: hidden; max-width: 500px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
            <div style="width: 100%; height: 240px; background: #16181c; overflow: hidden; display: flex; align-items: center; justify-content: center;">
              <img id="tw-card-img" src="<?= htmlspecialchars($curTwitterImage) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='/logo.webp';" alt="Twitter Preview" />
            </div>
            <div style="padding: 12px 16px;">
              <div style="font-size: 0.75rem; color: #71767b;">wetruenorthgroup.com</div>
              <div id="tw-card-title" style="font-size: 0.95rem; font-weight: 700; color: #e7e9ea; margin: 3px 0;">
                <?= htmlspecialchars($curTwitterTitle ?: ($curSeoTitle ?: ($product['name'] ?? 'Product Title'))) ?>
              </div>
              <div id="tw-card-desc" style="font-size: 0.82rem; color: #71767b; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                <?= htmlspecialchars($curTwitterDesc ?: ($curSeoDesc ?: 'Engineered primary packaging containers manufactured in ISO certified cleanrooms.')) ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Twitter Fields -->
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
          <div style="margin-bottom: 16px;">
            <label for="twitter_card" style="display: block; font-size: 0.85rem; font-weight: 700; color: #f8fafc; margin-bottom: 6px;">Twitter Card Type</label>
            <select id="twitter_card" name="twitter_card" class="form-control-smart" style="max-width: 260px;">
              <option value="summary_large_image" <?= $curTwitterCard === 'summary_large_image' ? 'selected' : '' ?>>Summary with Large Image (Recommended)</option>
              <option value="summary" <?= $curTwitterCard === 'summary' ? 'selected' : '' ?>>Summary Card (Small Thumbnail)</option>
            </select>
          </div>
          <div style="margin-bottom: 16px;">
            <label for="twitter_title" style="display: block; font-size: 0.85rem; font-weight: 700; color: #f8fafc; margin-bottom: 6px;">Twitter Title</label>
            <input type="text" id="twitter_title" name="twitter_title" value="<?= htmlspecialchars($curTwitterTitle) ?>" placeholder="Leave blank to use SEO Title" class="form-control-smart" oninput="updateSocialPreviews()" />
          </div>
          <div style="margin-bottom: 16px;">
            <label for="twitter_description" style="display: block; font-size: 0.85rem; font-weight: 700; color: #f8fafc; margin-bottom: 6px;">Twitter Description</label>
            <textarea id="twitter_description" name="twitter_description" rows="2" class="form-control-smart" placeholder="Leave blank to use Meta Description" oninput="updateSocialPreviews()"><?= htmlspecialchars($curTwitterDesc) ?></textarea>
          </div>
          <div>
            <label for="twitter_image" style="display: block; font-size: 0.85rem; font-weight: 700; color: #f8fafc; margin-bottom: 6px;">Twitter Image URL</label>
            <input type="text" id="twitter_image" name="twitter_image" value="<?= htmlspecialchars($curTwitterImage) ?>" placeholder="/uploads/..." class="form-control-smart" oninput="updateSocialPreviews()" />
          </div>
        </div>
      </div>

    </div>

    <!-- =====================================================================
         TAB 5: ADVANCED (ROBOTS, CANONICAL, BREADCRUMBS, REDIRECTS)
         ===================================================================== -->
    <div id="wp-tab-content-advanced" class="wp-seo-tab-content" style="display: none;">
      
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 22px;">
        
        <!-- ROBOTS INDEX -->
        <div style="margin-bottom: 22px; padding-bottom: 18px; border-bottom: 1px solid #334155;">
          <label style="display: block; font-size: 0.9rem; font-weight: 800; color: #f8fafc; margin-bottom: 4px;">
            Allow search engines to show this Content in search results?
          </label>
          <div style="font-size: 0.78rem; color: #94a3b8; margin-bottom: 8px;">Controls the robots meta tag (index / noindex).</div>
          <select id="robots_index" name="robots_index" class="form-control-smart" style="max-width: 320px;">
            <option value="1" <?= $curRobotsIndex === 1 ? 'selected' : '' ?>>Yes (index - Recommended)</option>
            <option value="0" <?= $curRobotsIndex === 0 ? 'selected' : '' ?>>No (noindex - Hide from Search Engines)</option>
          </select>
        </div>

        <!-- ROBOTS FOLLOW -->
        <div style="margin-bottom: 22px; padding-bottom: 18px; border-bottom: 1px solid #334155;">
          <label style="display: block; font-size: 0.9rem; font-weight: 800; color: #f8fafc; margin-bottom: 4px;">
            Should search engines follow links on this Content?
          </label>
          <div style="font-size: 0.78rem; color: #94a3b8; margin-bottom: 8px;">Controls the robots meta tag (follow / nofollow).</div>
          <select id="robots_follow" name="robots_follow" class="form-control-smart" style="max-width: 320px;">
            <option value="1" <?= $curRobotsFollow === 1 ? 'selected' : '' ?>>Yes (follow - Recommended)</option>
            <option value="0" <?= $curRobotsFollow === 0 ? 'selected' : '' ?>>No (nofollow)</option>
          </select>
        </div>

        <!-- ADVANCED META ROBOTS -->
        <div style="margin-bottom: 22px; padding-bottom: 18px; border-bottom: 1px solid #334155;">
          <label style="display: block; font-size: 0.9rem; font-weight: 800; color: #f8fafc; margin-bottom: 6px;">
            Advanced Meta Robots
          </label>
          <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: #cbd5e1; cursor: pointer;">
              <input type="checkbox" name="adv_noimageindex" value="noimageindex" <?= strpos($curAdvancedRobots, 'noimageindex') !== false ? 'checked' : '' ?> style="accent-color: #38bdf8;" />
              <span>No Image Index</span>
            </label>
            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: #cbd5e1; cursor: pointer;">
              <input type="checkbox" name="adv_noarchive" value="noarchive" <?= strpos($curAdvancedRobots, 'noarchive') !== false ? 'checked' : '' ?> style="accent-color: #38bdf8;" />
              <span>No Archive (no cache in SERP)</span>
            </label>
            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: #cbd5e1; cursor: pointer;">
              <input type="checkbox" name="adv_nosnippet" value="nosnippet" <?= strpos($curAdvancedRobots, 'nosnippet') !== false ? 'checked' : '' ?> style="accent-color: #38bdf8;" />
              <span>No Snippet</span>
            </label>
          </div>
          <input type="hidden" id="advanced_robots" name="advanced_robots" value="<?= htmlspecialchars($curAdvancedRobots) ?>" />
        </div>

        <!-- BREADCRUMBS TITLE OVERRIDE -->
        <div style="margin-bottom: 22px; padding-bottom: 18px; border-bottom: 1px solid #334155;">
          <label for="breadcrumbs_title" style="display: block; font-size: 0.9rem; font-weight: 800; color: #f8fafc; margin-bottom: 4px;">
            Breadcrumbs Title Override
          </label>
          <div style="font-size: 0.78rem; color: #94a3b8; margin-bottom: 6px;">The title to use in breadcrumbs navigation for this product.</div>
          <input type="text" id="breadcrumbs_title" name="breadcrumbs_title" value="<?= htmlspecialchars($curBreadcrumbTitle) ?>" placeholder="Defaults to Product Name" class="form-control-smart" />
        </div>

        <!-- CANONICAL URL OVERRIDE -->
        <div>
          <label for="canonical_url" style="display: block; font-size: 0.9rem; font-weight: 800; color: #f8fafc; margin-bottom: 4px;">
            Canonical URL Override
          </label>
          <div style="font-size: 0.78rem; color: #94a3b8; margin-bottom: 6px;">
            The canonical URL that this page should point towards. Leave empty for default self-referencing canonical.
          </div>
          <input type="url" id="canonical_url" name="canonical_url" value="<?= htmlspecialchars($curCanonical) ?>" placeholder="https://wetruenorthgroup.com/product/..." class="form-control-smart" />
        </div>

      </div>

    </div>

  </div>

</div>

<!-- WORDPRESS SEO REAL-TIME ENGINE JAVASCRIPT -->
<script>
// Tab switcher
function switchWpSeoTab(tabId, btn) {
  document.querySelectorAll('.wp-seo-tab-content').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.wp-tab-btn').forEach(b => {
    b.style.borderBottomColor = 'transparent';
    b.style.color = '#94a3b8';
    b.classList.remove('active');
  });

  const activeContent = document.getElementById('wp-tab-content-' + tabId);
  if (activeContent) activeContent.style.display = 'block';

  if (btn) {
    btn.style.borderBottomColor = '#38bdf8';
    btn.style.color = '#38bdf8';
    btn.classList.add('active');
  }
}

// Device toggle for Google preview
function setSerpDevice(device) {
  const mBox = document.getElementById('serp-container-mobile');
  const dBox = document.getElementById('serp-container-desktop');
  const mBtn = document.getElementById('btn-device-mobile');
  const dBtn = document.getElementById('btn-device-desktop');

  if (device === 'mobile') {
    mBox.style.display = 'block';
    dBox.style.display = 'none';
    mBtn.style.background = '#38bdf8';
    mBtn.style.color = '#0f172a';
    dBtn.style.background = 'transparent';
    dBtn.style.color = '#94a3b8';
  } else {
    mBox.style.display = 'none';
    dBox.style.display = 'block';
    dBtn.style.background = '#38bdf8';
    dBtn.style.color = '#0f172a';
    mBtn.style.background = 'transparent';
    mBtn.style.color = '#94a3b8';
  }
}

// Social Subtab toggle
function switchSocialSubTab(network) {
  const fbP = document.getElementById('wp-social-fb-panel');
  const twP = document.getElementById('wp-social-tw-panel');
  const fbB = document.getElementById('btn-social-fb');
  const twB = document.getElementById('btn-social-tw');

  if (network === 'fb') {
    fbP.style.display = 'block';
    twP.style.display = 'none';
    fbB.style.background = '#0284c7';
    fbB.style.color = '#fff';
    twB.style.background = '#1e293b';
    twB.style.color = '#94a3b8';
  } else {
    fbP.style.display = 'none';
    twP.style.display = 'block';
    twB.style.background = '#0284c7';
    twB.style.color = '#fff';
    fbB.style.background = '#1e293b';
    fbB.style.color = '#94a3b8';
  }
}

// Insert snippet tags into SEO Title
function insertTitleVar(varName) {
  const input = document.getElementById('meta_title');
  const nameEl = document.getElementById('name') || document.getElementById('productName');
  const catEl = document.getElementById('categorySlug');
  const pName = nameEl ? nameEl.value.trim() : 'Product';
  const catName = catEl ? catEl.options[catEl.selectedIndex].text : 'Packaging';

  let insertText = '';
  if (varName === 'Title') insertText = pName;
  else if (varName === 'Sep') insertText = ' - ';
  else if (varName === 'SiteTitle') insertText = ' | TrueNorth Group';
  else if (varName === 'Category') insertText = catName;

  input.value += (input.value ? ' ' : '') + insertText;
  runWordPressAnalysis();
}

// Auto-generate slug
function autoGenerateSlugFromProduct() {
  const nameEl = document.getElementById('name') || document.getElementById('productName');
  if (!nameEl || !nameEl.value.trim()) {
    alert('Please enter a product name first.');
    return;
  }
  const clean = nameEl.value.toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
  document.getElementById('seo_slug').value = clean;
  runWordPressAnalysis();
}

// Auto-detect focus keyphrase
function autoDetectKeyphrase() {
  const nameEl = document.getElementById('name') || document.getElementById('productName');
  if (nameEl && nameEl.value) {
    const words = nameEl.value.toLowerCase().replace(/[^a-z0-9\s]/g, '').trim().split(/\s+/);
    document.getElementById('focus_keyword').value = words.slice(0, 4).join(' ');
    runWordPressAnalysis();
  }
}

// Add FAQ Row
function addWpFaqRow() {
  const container = document.getElementById('wp-faq-rows-container');
  const hint = document.getElementById('wp-no-faqs-hint');
  if (hint) hint.remove();

  const count = container.querySelectorAll('.wp-faq-row-item').length + 1;
  const div = document.createElement('div');
  div.className = 'wp-faq-row-item';
  div.style.cssText = 'background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 14px; margin-bottom: 10px;';
  div.innerHTML = `
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
      <span style="font-size: 0.78rem; font-weight: 700; color: #38bdf8;">Question #\${count}</span>
      <button type="button" onclick="this.closest('.wp-faq-row-item').remove()" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 4px; padding: 2px 8px; font-size: 0.74rem; cursor: pointer;">
        Remove
      </button>
    </div>
    <input type="text" name="faq_questions[]" placeholder="e.g. Can this container be autoclaved?" class="form-control-smart" style="margin-bottom: 8px;" />
    <textarea name="faq_answers[]" rows="2" placeholder="e.g. Yes, when molded with pharmaceutical-grade polypropylene closures." class="form-control-smart"></textarea>
  `;
  container.appendChild(div);
}

// Validate Custom JSON
function validateCustomJson() {
  const val = document.getElementById('custom_schema_json').value.trim();
  const badge = document.getElementById('json-validator-badge');
  if (!val) {
    badge.textContent = 'Optional';
    badge.style.background = 'rgba(148, 163, 184, 0.15)';
    badge.style.color = '#94a3b8';
    badge.style.borderColor = 'rgba(148, 163, 184, 0.3)';
    return;
  }
  try {
    JSON.parse(val);
    badge.textContent = 'Valid JSON-LD';
    badge.style.background = 'rgba(34, 197, 94, 0.15)';
    badge.style.color = '#4ade80';
    badge.style.borderColor = 'rgba(34, 197, 94, 0.4)';
  } catch (e) {
    badge.textContent = 'JSON Syntax Error';
    badge.style.background = 'rgba(239, 68, 68, 0.15)';
    badge.style.color = '#f87171';
    badge.style.borderColor = 'rgba(239, 68, 68, 0.4)';
  }
}

// Sync Social Card Previews
function updateSocialPreviews() {
  const ogT = document.getElementById('og_title').value.trim() || document.getElementById('meta_title').value.trim() || 'Product Title';
  const ogD = document.getElementById('og_description').value.trim() || document.getElementById('meta_description').value.trim() || 'Product summary description.';
  const ogI = document.getElementById('og_image').value.trim() || '/logo.webp';

  document.getElementById('fb-card-title').textContent = ogT;
  document.getElementById('fb-card-desc').textContent = ogD;
  document.getElementById('fb-card-img').src = ogI;

  const twT = document.getElementById('twitter_title').value.trim() || ogT;
  const twD = document.getElementById('twitter_description').value.trim() || ogD;
  const twI = document.getElementById('twitter_image').value.trim() || ogI;

  document.getElementById('tw-card-title').textContent = twT;
  document.getElementById('tw-card-desc').textContent = twD;
  document.getElementById('tw-card-img').src = twI;
}

// Syllable Counter Helper for Flesch Score
function countSyllables(word) {
  word = word.toLowerCase();
  if (word.length <= 3) return 1;
  word = word.replace(/(?:[^laeiouy]|ed|es|e)$/, '');
  word = word.replace(/^y/, '');
  const syllables = word.match(/[aeiouy]{1,2}/g);
  return syllables ? syllables.length : 1;
}

// MAIN WORDPRESS REAL-TIME SEO & READABILITY ANALYZER
function runWordPressAnalysis() {
  const nameEl = document.getElementById('name') || document.getElementById('productName');
  const pName = nameEl ? nameEl.value.trim() : '';
  const titleEl = document.getElementById('meta_title');
  const descEl = document.getElementById('meta_description');
  const slugEl = document.getElementById('seo_slug');
  const kwEl = document.getElementById('focus_keyword');
  const h1El = document.getElementById('h1_override');
  const contentEl = document.getElementById('description') || document.getElementById('shortDescription');

  const titleVal = titleEl.value.trim() || (pName ? (pName + ' | TrueNorth Group') : 'Product Title Preview | TrueNorth Group');
  const descVal = descEl.value.trim() || 'Wholesale high-precision primary packaging containers engineered in ISO certified cleanrooms.';
  const slugVal = slugEl.value.trim() || 'item-slug';
  const focusKw = kwEl.value.toLowerCase().trim();
  const rawContent = (contentEl ? contentEl.value : '') + ' ' + (pName ? pName : '');
  const cleanContent = rawContent.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();

  // 1. Update Previews
  document.getElementById('mobile-preview-title').textContent = titleVal;
  document.getElementById('mobile-preview-desc').textContent = descVal;
  document.getElementById('mobile-preview-slug').textContent = slugVal;

  document.getElementById('desktop-preview-title').textContent = titleVal;
  document.getElementById('desktop-preview-desc').textContent = descVal;
  document.getElementById('desktop-preview-slug').textContent = slugVal;

  // Title length & progress bar
  const tLen = titleEl.value.length;
  const tBadge = document.getElementById('title-char-badge');
  const tBar = document.getElementById('title-progress-bar');
  tBadge.textContent = tLen + ' / 60 chars';
  const tPercent = Math.min(100, (tLen / 60) * 100);
  tBar.style.width = tPercent + '%';
  if (tLen >= 45 && tLen <= 60) {
    tBar.style.background = '#22c55e';
    tBadge.style.color = '#4ade80';
  } else if (tLen > 60) {
    tBar.style.background = '#ef4444';
    tBadge.style.color = '#f87171';
  } else {
    tBar.style.background = '#f59e0b';
    tBadge.style.color = '#fbbf24';
  }

  // Desc length & progress bar
  const dLen = descEl.value.length;
  const dBadge = document.getElementById('desc-char-badge');
  const dBar = document.getElementById('desc-progress-bar');
  dBadge.textContent = dLen + ' / 160 chars';
  const dPercent = Math.min(100, (dLen / 160) * 100);
  dBar.style.width = dPercent + '%';
  if (dLen >= 120 && dLen <= 160) {
    dBar.style.background = '#22c55e';
    dBadge.style.color = '#4ade80';
  } else if (dLen > 160) {
    dBar.style.background = '#ef4444';
    dBadge.style.color = '#f87171';
  } else {
    dBar.style.background = '#f59e0b';
    dBadge.style.color = '#fbbf24';
  }

  updateSocialPreviews();

  // 2. SEO Analysis Checklist (Yoast Style)
  const problems = [];
  const improvements = [];
  const good = [];

  // Check: Focus Keyphrase Set
  if (!focusKw) {
    problems.push('Focus keyphrase: No focus keyphrase was set. Set a focus keyphrase to track ranking recommendations.');
  } else {
    // Keyphrase in title
    if (titleVal.toLowerCase().includes(focusKw)) {
      good.push('Keyphrase in SEO title: Your focus keyphrase appears in the SEO title.');
    } else {
      problems.push('Keyphrase in SEO title: Does not contain the focus keyphrase "' + focusKw + '".');
    }

    // Keyphrase in slug
    if (slugVal.toLowerCase().includes(focusKw.replace(/\s+/g, '-'))) {
      good.push('Keyphrase in slug: The focus keyphrase is present in the URL slug.');
    } else {
      improvements.push('Keyphrase in slug: URL slug does not contain the keyphrase.');
    }

    // Keyphrase in description
    if (descVal.toLowerCase().includes(focusKw)) {
      good.push('Keyphrase in meta description: The keyphrase was found in the meta description.');
    } else {
      problems.push('Keyphrase in meta description: Not found in the meta description.');
    }

    // Keyphrase density
    const words = cleanContent.toLowerCase().split(/\s+/).filter(Boolean);
    const kwCount = (cleanContent.toLowerCase().match(new RegExp(focusKw, 'g')) || []).length;
    const density = words.length > 0 ? ((kwCount / words.length) * 100).toFixed(1) : 0;
    if (kwCount >= 1 && density >= 0.5 && density <= 3.0) {
      good.push('Keyphrase density: Focus keyphrase found ' + kwCount + ' times (' + density + '% density). This is optimal.');
    } else if (kwCount === 0) {
      problems.push('Keyphrase density: Focus keyphrase was not found in the product content.');
    } else if (density > 3.0) {
      improvements.push('Keyphrase density: ' + density + '% is too high. Avoid keyword stuffing.');
    } else {
      improvements.push('Keyphrase density: Found ' + kwCount + ' time. Consider mentioning it at least 2 times.');
    }
  }

  // Check: SEO Title Length
  if (tLen >= 45 && tLen <= 60) {
    good.push('SEO title width: Good job! Title length (' + tLen + ' chars) fits Google displays.');
  } else if (tLen > 60) {
    problems.push('SEO title width: Too long (' + tLen + ' chars). Google may truncate it with an ellipsis.');
  } else {
    improvements.push('SEO title width: Too short (' + tLen + ' chars). Add more descriptive search keywords.');
  }

  // Check: Meta Description Length
  if (dLen >= 120 && dLen <= 160) {
    good.push('Meta description length: Well done (' + dLen + ' chars).');
  } else if (dLen > 160) {
    problems.push('Meta description length: Over 160 characters (' + dLen + '). Search engines will cut it off.');
  } else {
    improvements.push('Meta description length: Under 120 characters (' + dLen + '). Provide more benefits for searchers.');
  }

  // Check: Content text length
  const totalWords = cleanContent.split(/\s+/).filter(Boolean).length;
  if (totalWords >= 150) {
    good.push('Text length: The product description contains ' + totalWords + ' words, which provides good search depth.');
  } else {
    improvements.push('Text length: Contains only ' + totalWords + ' words. Recommended minimum is 150 words.');
  }

  // Render SEO Lists
  const renderList = (id, items) => {
    const container = document.getElementById(id);
    container.innerHTML = '';
    if (items.length === 0) {
      container.innerHTML = '<div style="color: #64748b; font-size: 0.8rem; font-style: italic;">None</div>';
    } else {
      items.forEach(txt => {
        const row = document.createElement('div');
        row.style.cssText = 'color: #cbd5e1; font-size: 0.83rem; line-height: 1.45;';
        row.innerHTML = '&bull; ' + txt;
        container.appendChild(row);
      });
    }
  };

  document.getElementById('cnt-problems').textContent = problems.length;
  document.getElementById('cnt-improvements').textContent = improvements.length;
  document.getElementById('cnt-good').textContent = good.length;

  renderList('list-problems', problems);
  renderList('list-improvements', improvements);
  renderList('list-good', good);

  // Calculate Overall SEO Score
  const totalChecks = problems.length + improvements.length + good.length;
  const score = totalChecks > 0 ? Math.round(((good.length * 100) + (improvements.length * 50)) / totalChecks) : 75;
  
  const scoreText = document.getElementById('wp-seo-score-text');
  const trafficDot = document.getElementById('wp-seo-traffic-dot');
  const tabDot = document.getElementById('tab-dot-seo');
  const summaryBadge = document.getElementById('analysis-summary-badge');

  if (score >= 80) {
    scoreText.textContent = score + '/100 (Good)';
    trafficDot.style.background = '#22c55e';
    tabDot.style.background = '#22c55e';
    summaryBadge.textContent = 'Good (' + score + '/100)';
    summaryBadge.style.color = '#4ade80';
  } else if (score >= 50) {
    scoreText.textContent = score + '/100 (OK)';
    trafficDot.style.background = '#f59e0b';
    tabDot.style.background = '#f59e0b';
    summaryBadge.textContent = 'OK (' + score + '/100)';
    summaryBadge.style.color = '#fbbf24';
  } else {
    scoreText.textContent = score + '/100 (Needs Work)';
    trafficDot.style.background = '#ef4444';
    tabDot.style.background = '#ef4444';
    summaryBadge.textContent = 'Needs Work (' + score + '/100)';
    summaryBadge.style.color = '#f87171';
  }

  // 3. READABILITY ANALYSIS (FLESCH READING EASE & WORDPRESS CRITERIA)
  const sentences = cleanContent.split(/[.!?]+/).filter(s => s.trim().length > 0);
  const wordsList = cleanContent.split(/\s+/).filter(Boolean);
  let totalSyllables = 0;
  wordsList.forEach(w => totalSyllables += countSyllables(w));

  const numSentences = Math.max(1, sentences.length);
  const numWords = Math.max(1, wordsList.length);

  // Flesch formula: 206.835 - (1.015 * (words/sentences)) - (84.6 * (syllables/words))
  let flesch = 206.835 - (1.015 * (numWords / numSentences)) - (84.6 * (totalSyllables / numWords));
  flesch = Math.max(0, Math.min(100, Math.round(flesch * 10) / 10));

  document.getElementById('flesch-score-value').textContent = flesch + ' / 100';
  const rDot = document.getElementById('wp-readability-traffic-dot');
  const rTabDot = document.getElementById('tab-dot-readability');
  const rText = document.getElementById('wp-readability-score-text');
  const fLabel = document.getElementById('flesch-score-label');

  if (flesch >= 60) {
    rDot.style.background = '#22c55e';
    rTabDot.style.background = '#22c55e';
    rText.textContent = 'Good (' + flesch + ')';
    fLabel.textContent = 'Standard / Easy to read. Engaging for packaging procurement engineers and B2B buyers.';
  } else if (flesch >= 40) {
    rDot.style.background = '#f59e0b';
    rTabDot.style.background = '#f59e0b';
    rText.textContent = 'Fair (' + flesch + ')';
    fLabel.textContent = 'Fairly complex language. Consider shorter sentences for higher readability.';
  } else {
    rDot.style.background = '#ef4444';
    rTabDot.style.background = '#ef4444';
    rText.textContent = 'Needs Work (' + flesch + ')';
    fLabel.textContent = 'Difficult to read. Shorten dense sentences and reduce overly technical jargon.';
  }

  // Readability item list
  const rList = document.getElementById('readability-items-list');
  rList.innerHTML = '';

  const addRItem = (isGood, title, detail) => {
    const div = document.createElement('div');
    div.style.cssText = 'display: flex; align-items: flex-start; gap: 10px; line-height: 1.4;';
    const color = isGood ? '#22c55e' : '#f59e0b';
    div.innerHTML = `
      <span style="width: 10px; height: 10px; border-radius: 50%; background: \${color}; display: inline-block; margin-top: 5px; flex-shrink: 0;"></span>
      <div>
        <strong style="color: #f8fafc;">\${title}:</strong> <span style="color: #cbd5e1;">\${detail}</span>
      </div>
    `;
    rList.appendChild(div);
  };

  // Sentence length check
  const longSentences = sentences.filter(s => s.split(/\s+/).filter(Boolean).length > 20);
  const longPercent = Math.round((longSentences.length / numSentences) * 100);
  if (longPercent <= 25) {
    addRItem(true, 'Sentence length', longPercent + '% of sentences contain more than 20 words, which is below the 25% threshold.');
  } else {
    addRItem(false, 'Sentence length', longPercent + '% of sentences contain more than 20 words, which is higher than the recommended 25%.');
  }

  // Paragraph length check
  const paragraphs = rawContent.split(/\n\s*\n/).filter(p => p.trim().length > 0);
  const maxParaWords = Math.max(...paragraphs.map(p => p.split(/\s+/).filter(Boolean).length), 0);
  if (maxParaWords <= 120) {
    addRItem(true, 'Paragraph length', 'None of your paragraphs exceed the 120-word limit. Good text formatting.');
  } else {
    addRItem(false, 'Paragraph length', 'Your longest paragraph has ' + maxParaWords + ' words. Consider breaking it up with subheadings.');
  }

  // Transition words check
  const transitionWordsRegex = /\b(however|therefore|in addition|because|furthermore|consequently|as a result|for example|moreover|although|while|whereas|similarly|specifically|thus)\b/i;
  const transitionCount = sentences.filter(s => transitionWordsRegex.test(s)).length;
  const transPercent = Math.round((transitionCount / numSentences) * 100);
  if (transPercent >= 20) {
    addRItem(true, 'Transition words', transPercent + '% of the sentences contain transition words. Smooth text flow.');
  } else {
    addRItem(false, 'Transition words', 'Only ' + transPercent + '% of sentences contain transition words. Use more connecting phrases (e.g. however, because, furthermore).');
  }

  // Subheading distribution
  const hasSubheadings = /<h[2-4][^>]*>/i.test(rawContent);
  if (numWords < 250 || hasSubheadings) {
    addRItem(true, 'Subheading distribution', 'Good text structure with appropriate section division.');
  } else {
    addRItem(false, 'Subheading distribution', 'Text is over 250 words without H2 or H3 subheadings. Add subheadings to guide readers.');
  }
}

// Advanced Robots checkbox sync
document.addEventListener('change', function(e) {
  if (e.target && e.target.name && e.target.name.startsWith('adv_')) {
    const checked = [];
    document.querySelectorAll('input[name^="adv_"]:checked').forEach(cb => checked.push(cb.value));
    const hidden = document.getElementById('advanced_robots');
    if (hidden) hidden.value = checked.join(', ');
  }
});

// Run initial evaluation on page load
document.addEventListener('DOMContentLoaded', function() {
  runWordPressAnalysis();
  validateCustomJson();
});
</script>
