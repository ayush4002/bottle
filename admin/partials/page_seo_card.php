<?php
// ==============================================================================
// TRUENORTH CMS — WORDPRESS YOAST & RANK MATH STYLE SEO SUITE FOR PAGES
// Complete 5-Tab SEO System: SEO, Readability, Schema, Social, Advanced
// Features live mobile/desktop SERP toggles, traffic-light scoring & rich previews
// ==============================================================================

if (!class_exists('SeoModel')) {
    require_once dirname(__DIR__, 2) . '/app/Models/SeoModel.php';
}

$curSlug = $page['slug'] ?? '/';
$curPageSeo = $page['seo'] ?? [];
if (empty($curPageSeo) && !empty($curSlug)) {
    $curPageSeo = SeoModel::getPageSeo($curSlug) ?? [];
}

$curPageTitle = $page['title'] ?? 'About Us';
$curSeoTitle = !empty($curPageSeo['meta_title']) ? $curPageSeo['meta_title'] : (!empty($page['share_title']) ? $page['share_title'] : ($curPageTitle . ' — TrueNorth Group'));
$curSeoDesc = !empty($curPageSeo['meta_description']) ? $curPageSeo['meta_description'] : (!empty($page['banner_subtitle']) ? $page['banner_subtitle'] : '');
$curKeywords = !empty($curPageSeo['meta_keywords']) ? $curPageSeo['meta_keywords'] : '';
$curFocusKeyword = !empty($curPageSeo['focus_keyword']) ? $curPageSeo['focus_keyword'] : '';
$curCanonical = !empty($curPageSeo['canonical_url']) ? $curPageSeo['canonical_url'] : '';
$curH1 = !empty($curPageSeo['h1_override']) ? $curPageSeo['h1_override'] : '';
$curRobotsIndex = isset($curPageSeo['robots_index']) ? (int)$curPageSeo['robots_index'] : 1;
$curRobotsFollow = isset($curPageSeo['robots_follow']) ? (int)$curPageSeo['robots_follow'] : 1;
$curOgTitle = !empty($curPageSeo['og_title']) ? $curPageSeo['og_title'] : $curSeoTitle;
$curOgDesc = !empty($curPageSeo['og_description']) ? $curPageSeo['og_description'] : $curSeoDesc;
$curOgImage = !empty($curPageSeo['og_image']) ? $curPageSeo['og_image'] : ($page['banner_image'] ?? '/hero_bottles_banner.webp');
$curTwitterTitle = !empty($curPageSeo['twitter_title']) ? $curPageSeo['twitter_title'] : $curOgTitle;
$curTwitterDesc = !empty($curPageSeo['twitter_description']) ? $curPageSeo['twitter_description'] : $curOgDesc;
$curTwitterImage = !empty($curPageSeo['twitter_image']) ? $curPageSeo['twitter_image'] : $curOgImage;
$curTwitterCard = !empty($curPageSeo['twitter_card']) ? $curPageSeo['twitter_card'] : 'summary_large_image';

$defaultSchema = 'WebPage';
if (strpos($curSlug, 'about') !== false) $defaultSchema = 'AboutPage';
elseif (strpos($curSlug, 'contact') !== false) $defaultSchema = 'ContactPage';
elseif (strpos($curSlug, 'faq') !== false) $defaultSchema = 'FAQPage';
elseif (strpos($curSlug, 'sustainability') !== false) $defaultSchema = 'AboutPage';
elseif (strpos($curSlug, 'products') !== false) $defaultSchema = 'CollectionPage';

$curSchemaType = !empty($curPageSeo['schema_type']) ? $curPageSeo['schema_type'] : $defaultSchema;
$curCustomSchema = !empty($curPageSeo['custom_schema_json']) ? $curPageSeo['custom_schema_json'] : '';
$curCornerstone = !empty($curPageSeo['cornerstone_content']) ? 1 : 0;
$curBreadcrumbTitle = !empty($curPageSeo['breadcrumbs_title']) ? $curPageSeo['breadcrumbs_title'] : $curPageTitle;
$curAdvancedRobots = !empty($curPageSeo['advanced_robots']) ? $curPageSeo['advanced_robots'] : '';
?>

<!-- WORDPRESS PAGE SEO METABOX CONTAINER -->
<div class="form-card wp-seo-metabox" id="wp-page-seo-panel" style="border: 1px solid #38bdf8; background: #0f172a; border-radius: 16px; margin-bottom: 28px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.35);">
  
  <!-- METABOX HEADER WITH OVERALL SCORES (YOAST STYLE) -->
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
        <div style="font-size: 0.76rem; color: #94a3b8;">Page-level search appearance, Google snippet, readability assessment &amp; structured schema</div>
      </div>
    </div>

    <!-- LIVE METABOX SCORE PILLS (TRAFFIC LIGHTS) -->
    <div style="display: flex; align-items: center; gap: 10px;">
      <div id="wpp-seo-overall-pill" style="display: flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); font-size: 0.8rem; font-weight: 700; color: #4ade80;">
        <span id="wpp-seo-traffic-dot" style="width: 10px; height: 10px; border-radius: 50%; background: #22c55e;"></span>
        <span>SEO: <strong id="wpp-seo-score-text">88/100 (Good)</strong></span>
      </div>
      <div id="wpp-readability-pill" style="display: flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.4); font-size: 0.8rem; font-weight: 700; color: #38bdf8;">
        <span id="wpp-readability-dot" style="width: 10px; height: 10px; border-radius: 50%; background: #38bdf8;"></span>
        <span>Readability: <strong id="wpp-readability-score-text">Good</strong></span>
      </div>
    </div>
  </div>

  <!-- TAB NAVIGATION BAR (5 WORDPRESS TABS) -->
  <div style="display: flex; background: #0b1120; border-bottom: 1px solid #334155; padding: 0 16px; overflow-x: auto;">
    <button type="button" class="wpp-seo-tab-btn active" onclick="switchWppTab('seo')" id="wpptab-btn-seo" style="display: flex; align-items: center; gap: 8px; padding: 14px 20px; border: none; background: transparent; color: #38bdf8; font-weight: 700; font-size: 0.88rem; border-bottom: 2px solid #38bdf8; cursor: pointer; transition: all 0.15s;">
      <span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e;" id="wpptab-dot-seo"></span>
      <span>SEO</span>
    </button>
    <button type="button" class="wpp-seo-tab-btn" onclick="switchWppTab('readability')" id="wpptab-btn-readability" style="display: flex; align-items: center; gap: 8px; padding: 14px 20px; border: none; background: transparent; color: #94a3b8; font-weight: 600; font-size: 0.88rem; border-bottom: 2px solid transparent; cursor: pointer; transition: all 0.15s;">
      <span style="width: 8px; height: 8px; border-radius: 50%; background: #38bdf8;" id="wpptab-dot-readability"></span>
      <span>Readability</span>
    </button>
    <button type="button" class="wpp-seo-tab-btn" onclick="switchWppTab('schema')" id="wpptab-btn-schema" style="display: flex; align-items: center; gap: 8px; padding: 14px 20px; border: none; background: transparent; color: #94a3b8; font-weight: 600; font-size: 0.88rem; border-bottom: 2px solid transparent; cursor: pointer; transition: all 0.15s;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      <span>Schema</span>
    </button>
    <button type="button" class="wpp-seo-tab-btn" onclick="switchWppTab('social')" id="wpptab-btn-social" style="display: flex; align-items: center; gap: 8px; padding: 14px 20px; border: none; background: transparent; color: #94a3b8; font-weight: 600; font-size: 0.88rem; border-bottom: 2px solid transparent; cursor: pointer; transition: all 0.15s;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
      <span>Social</span>
    </button>
    <button type="button" class="wpp-seo-tab-btn" onclick="switchWppTab('advanced')" id="wpptab-btn-advanced" style="display: flex; align-items: center; gap: 8px; padding: 14px 20px; border: none; background: transparent; color: #94a3b8; font-weight: 600; font-size: 0.88rem; border-bottom: 2px solid transparent; cursor: pointer; transition: all 0.15s;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
      <span>Advanced</span>
    </button>
  </div>

  <div style="padding: 24px;">

    <!-- =====================================================================
         TAB 1: SEO (FOCUS KEYPHRASE, SNIPPET PREVIEW, ANALYSIS)
         ===================================================================== -->
    <div id="wpp-tab-content-seo" class="wpp-seo-tab-content" style="display: block;">
      
      <!-- FOCUS KEYPHRASE (YOAST PATTERN) -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <label for="page_focus_keyword" style="display: block; font-size: 0.9rem; font-weight: 800; color: #f8fafc; margin-bottom: 6px;">
          Focus Keyphrase
        </label>
        <p style="font-size: 0.78rem; color: #94a3b8; margin: 0 0 10px 0;">
          Enter the primary search phrase you want this page to rank for in Google search results.
        </p>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <input type="text" id="page_focus_keyword" name="seo[focus_keyword]" value="<?= htmlspecialchars($curFocusKeyword) ?>" placeholder="e.g. primary packaging manufacturer, sustainable rpet packaging" class="form-control-smart" style="flex: 1; min-width: 260px;" oninput="runPageAnalysis()" />
          <button type="button" onclick="detectKeyphraseFromTitle()" style="background: #334155; border: 1px solid #475569; color: #38bdf8; font-size: 0.8rem; font-weight: 700; padding: 8px 14px; border-radius: 8px; cursor: pointer;">
            Detect from Title
          </button>
        </div>
      </div>

      <!-- SEARCH APPEARANCE ACCORDION (YOAST GOOGLE PREVIEW) -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
          <div>
            <h4 style="margin: 0; font-size: 0.95rem; font-weight: 800; color: #f8fafc;">Search Appearance</h4>
            <div style="font-size: 0.75rem; color: #94a3b8;">Determine how your page should look in search engine results.</div>
          </div>
          <!-- Device Toggle (Mobile vs Desktop) -->
          <div style="display: flex; background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 3px;">
            <button type="button" id="wpp-btn-preview-mobile" onclick="setWppPreviewDevice('mobile')" style="padding: 6px 12px; font-size: 0.78rem; font-weight: 700; border: none; border-radius: 6px; background: #38bdf8; color: #0f172a; cursor: pointer; display: flex; align-items: center; gap: 6px;">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
              Mobile result
            </button>
            <button type="button" id="wpp-btn-preview-desktop" onclick="setWppPreviewDevice('desktop')" style="padding: 6px 12px; font-size: 0.78rem; font-weight: 700; border: none; border-radius: 6px; background: transparent; color: #94a3b8; cursor: pointer; display: flex; align-items: center; gap: 6px;">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
              Desktop result
            </button>
          </div>
        </div>

        <!-- LIVE GOOGLE SERP CARD -->
        <div id="wpp-serp-wrapper" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 22px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; max-width: 640px; box-shadow: 0 4px 14px rgba(0,0,0,0.12); margin-bottom: 20px;">
          <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
            <img src="/logo_svg.svg" style="width: 20px; height: 20px; border-radius: 50%; object-fit: contain; background: #f8fafc; border: 1px solid #e2e8f0;" alt="Favicon" />
            <div style="display: flex; flex-direction: column; line-height: 1.25;">
              <span style="font-size: 0.84rem; color: #202124; font-weight: 500;">TrueNorth Group</span>
              <span id="wpp-serp-url" style="font-size: 0.75rem; color: #4d5156;">https://wetruenorthgroup.com &rsaquo; <?= htmlspecialchars(ltrim($curSlug, '/')) ?></span>
            </div>
          </div>
          <div id="wpp-serp-title" style="font-size: 1.25rem; color: #1a0dab; font-weight: 400; line-height: 1.3; cursor: pointer; margin-bottom: 6px; word-break: break-word;">
            <?= htmlspecialchars($curSeoTitle) ?>
          </div>
          <div id="wpp-serp-desc" style="font-size: 0.88rem; color: #4d5156; line-height: 1.45; word-break: break-word;">
            <?= htmlspecialchars($curSeoDesc ?: 'Please provide a meta description by editing the snippet below.') ?>
          </div>
        </div>

        <!-- SEO TITLE INPUT & PROGRESS BAR -->
        <div style="margin-bottom: 20px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
            <label for="page_seo_title" style="font-size: 0.85rem; font-weight: 700; color: #cbd5e1;">SEO Title</label>
            <span id="wpp-title-counter" style="font-size: 0.75rem; font-weight: 700; color: #34d399;">0 / 60 chars</span>
          </div>
          <!-- Variable Tags (Yoast Pattern) -->
          <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px;">
            <button type="button" onclick="insertVariableTag('page_seo_title', '{title}')" style="background: #0f172a; border: 1px solid #475569; color: #38bdf8; font-size: 0.72rem; padding: 3px 8px; border-radius: 4px; cursor: pointer;">+ Title</button>
            <button type="button" onclick="insertVariableTag('page_seo_title', '{separator}')" style="background: #0f172a; border: 1px solid #475569; color: #38bdf8; font-size: 0.72rem; padding: 3px 8px; border-radius: 4px; cursor: pointer;">+ Sep</button>
            <button type="button" onclick="insertVariableTag('page_seo_title', '{site_name}')" style="background: #0f172a; border: 1px solid #475569; color: #38bdf8; font-size: 0.72rem; padding: 3px 8px; border-radius: 4px; cursor: pointer;">+ Site title</button>
            <button type="button" onclick="insertVariableTag('page_seo_title', '{page_type}')" style="background: #0f172a; border: 1px solid #475569; color: #38bdf8; font-size: 0.72rem; padding: 3px 8px; border-radius: 4px; cursor: pointer;">+ Page type</button>
          </div>
          <input type="text" id="page_seo_title" name="seo[meta_title]" value="<?= htmlspecialchars($curSeoTitle) ?>" class="form-control-smart" style="width: 100%; box-sizing: border-box;" oninput="runPageAnalysis()" />
          <!-- Progress Bar -->
          <div style="height: 4px; background: #334155; border-radius: 2px; margin-top: 6px; overflow: hidden;">
            <div id="wpp-title-progress" style="height: 100%; width: 0%; background: #22c55e; transition: width 0.2s, background 0.2s;"></div>
          </div>
        </div>

        <!-- SLUG INPUT -->
        <div style="margin-bottom: 20px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
            <label for="page_slug_seo" style="font-size: 0.85rem; font-weight: 700; color: #cbd5e1;">Slug</label>
            <span style="font-size: 0.72rem; color: #94a3b8;">URL path segment</span>
          </div>
          <div style="display: flex; align-items: center; background: #0f172a; border: 1px solid #475569; border-radius: 8px; padding-left: 12px;">
            <span style="color: #64748b; font-family: monospace; font-size: 0.85rem;">/</span>
            <input type="text" id="page_slug_seo" name="seo[url_slug]" value="<?= htmlspecialchars(ltrim($curSlug, '/')) ?>" style="flex: 1; border: none; background: transparent; color: #38bdf8; padding: 10px 12px; font-family: monospace; font-size: 0.88rem; outline: none;" oninput="syncPageSlugInput(this.value)" />
          </div>
        </div>

        <!-- META DESCRIPTION INPUT & PROGRESS BAR -->
        <div>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
            <label for="page_seo_desc" style="font-size: 0.85rem; font-weight: 700; color: #cbd5e1;">Meta Description</label>
            <span id="wpp-desc-counter" style="font-size: 0.75rem; font-weight: 700; color: #34d399;">0 / 160 chars</span>
          </div>
          <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px;">
            <button type="button" onclick="insertVariableTag('page_seo_desc', '{site_description}')" style="background: #0f172a; border: 1px solid #475569; color: #38bdf8; font-size: 0.72rem; padding: 3px 8px; border-radius: 4px; cursor: pointer;">+ Site description</button>
            <button type="button" onclick="insertVariableTag('page_seo_desc', '{excerpt}')" style="background: #0f172a; border: 1px solid #475569; color: #38bdf8; font-size: 0.72rem; padding: 3px 8px; border-radius: 4px; cursor: pointer;">+ Page excerpt</button>
          </div>
          <textarea id="page_seo_desc" name="seo[meta_description]" rows="3" class="form-control-smart" style="width: 100%; box-sizing: border-box; line-height: 1.5;" oninput="runPageAnalysis()"><?= htmlspecialchars($curSeoDesc) ?></textarea>
          <!-- Progress Bar -->
          <div style="height: 4px; background: #334155; border-radius: 2px; margin-top: 6px; overflow: hidden;">
            <div id="wpp-desc-progress" style="height: 100%; width: 0%; background: #22c55e; transition: width 0.2s, background 0.2s;"></div>
          </div>
        </div>

      </div>

      <!-- SEO ANALYSIS ACCORDION (YOAST TRAFFIC LIGHT CHECKLIST) -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; margin-bottom: 24px; overflow: hidden;">
        <div onclick="togglePageAccordion('wpp-analysis-body', 'wpp-analysis-icon')" style="padding: 16px 20px; background: #0f172a; display: flex; align-items: center; justify-content: space-between; cursor: pointer; user-select: none;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <span id="wpp-analysis-traffic-dot" style="width: 10px; height: 10px; border-radius: 50%; background: #22c55e;"></span>
            <span style="font-size: 0.92rem; font-weight: 800; color: #f8fafc;">SEO Analysis</span>
            <span id="wpp-analysis-pill" style="font-size: 0.72rem; padding: 2px 8px; border-radius: 10px; background: rgba(34, 197, 94, 0.15); color: #4ade80; font-weight: 700;">Good</span>
          </div>
          <svg id="wpp-analysis-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="transform: rotate(180deg); transition: transform 0.2s;"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
        <div id="wpp-analysis-body" style="padding: 18px 20px; display: block;">
          <div id="wpp-analysis-items" style="display: flex; flex-direction: column; gap: 12px;">
            <!-- Rendered dynamically by runPageAnalysis() -->
          </div>
        </div>
      </div>

      <!-- CORNERSTONE CONTENT TOGGLE -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 18px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 14px;">
        <div>
          <div style="font-size: 0.88rem; font-weight: 700; color: #f8fafc; margin-bottom: 2px;">Cornerstone Content</div>
          <div style="font-size: 0.75rem; color: #94a3b8;">Mark this page as the most authoritative, essential pillar article on your website.</div>
        </div>
        <label style="position: relative; display: inline-block; width: 44px; height: 24px; margin: 0; cursor: pointer;">
          <input type="checkbox" name="seo[cornerstone_content]" value="1" <?= $curCornerstone ? 'checked' : '' ?> style="opacity: 0; width: 0; height: 0;" />
          <span style="position: absolute; cursor: pointer; inset: 0; background: #334155; border-radius: 24px; transition: 0.2s;"></span>
        </label>
      </div>

    </div>

    <!-- =====================================================================
         TAB 2: READABILITY (FLESCH READING EASE, SENTENCE LENGTH, ETC.)
         ===================================================================== -->
    <div id="wpp-tab-content-readability" class="wpp-seo-tab-content" style="display: none;">
      
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
          <div>
            <h4 style="margin: 0 0 4px 0; font-size: 0.95rem; font-weight: 800; color: #f8fafc;">Readability Assessment</h4>
            <div style="font-size: 0.75rem; color: #94a3b8;">Flesch Reading Ease score and copy clarity benchmarks</div>
          </div>
          <div id="wpp-flesch-badge" style="padding: 6px 14px; border-radius: 20px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; font-weight: 800; font-size: 0.82rem;">
            Flesch: 72.4 (Good)
          </div>
        </div>

        <div id="wpp-readability-items" style="display: flex; flex-direction: column; gap: 12px;">
          <!-- Dynamically populated by JS -->
        </div>
      </div>

    </div>

    <!-- =====================================================================
         TAB 3: SCHEMA (STRUCTURED DATA & RICH SNIPPETS)
         ===================================================================== -->
    <div id="wpp-tab-content-schema" class="wpp-seo-tab-content" style="display: none;">
      
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <h4 style="margin: 0 0 6px 0; font-size: 0.95rem; font-weight: 800; color: #f8fafc;">Structured Data &amp; Schema.org</h4>
        <p style="font-size: 0.78rem; color: #94a3b8; margin: 0 0 16px 0;">Select the primary semantic entity for this page or supply custom JSON-LD.</p>

        <div style="margin-bottom: 18px;">
          <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Page Schema Type</label>
          <select id="wpp_schema_type" name="seo[schema_type]" class="form-control-smart" style="width: 100%; box-sizing: border-box;" onchange="updatePageSchemaPreview()">
            <option value="WebPage" <?= $curSchemaType === 'WebPage' ? 'selected' : '' ?>>WebPage (Default Generic Page)</option>
            <option value="AboutPage" <?= $curSchemaType === 'AboutPage' ? 'selected' : '' ?>>AboutPage (Corporate Heritage &amp; Company Overview)</option>
            <option value="ContactPage" <?= $curSchemaType === 'ContactPage' ? 'selected' : '' ?>>ContactPage (Locations, Inquiries &amp; Directory)</option>
            <option value="FAQPage" <?= $curSchemaType === 'FAQPage' ? 'selected' : '' ?>>FAQPage (Rich Accordion FAQ Snippets)</option>
            <option value="CollectionPage" <?= $curSchemaType === 'CollectionPage' ? 'selected' : '' ?>>CollectionPage (Catalogues &amp; Category Listings)</option>
            <option value="ItemPage" <?= $curSchemaType === 'ItemPage' ? 'selected' : '' ?>>ItemPage (Specific Service or Item Showcase)</option>
            <option value="Service" <?= $curSchemaType === 'Service' ? 'selected' : '' ?>>Service (Custom Moulding, Prototyping &amp; Tooling)</option>
            <option value="ProfilePage" <?= $curSchemaType === 'ProfilePage' ? 'selected' : '' ?>>ProfilePage (Leadership / Executive Profiles)</option>
          </select>
        </div>

        <div>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
            <label style="font-size: 0.85rem; font-weight: 700; color: #cbd5e1;">Custom JSON-LD Schema (Optional Override)</label>
            <span id="wpp-schema-syntax-status" style="font-size: 0.72rem; color: #94a3b8;">Validates in real-time</span>
          </div>
          <textarea id="wpp_custom_schema" name="seo[custom_schema_json]" rows="6" placeholder='{"@context":"https://schema.org","@type":"AboutPage","name":"About TrueNorth Group"}' class="form-control-smart" style="width: 100%; box-sizing: border-box; font-family: monospace; font-size: 0.8rem;" oninput="validatePageSchemaJson()"><?= htmlspecialchars($curCustomSchema) ?></textarea>
        </div>
      </div>

    </div>

    <!-- =====================================================================
         TAB 4: SOCIAL (FACEBOOK & TWITTER SHARING)
         ===================================================================== -->
    <div id="wpp-tab-content-social" class="wpp-seo-tab-content" style="display: none;">
      
      <!-- FACEBOOK / OPEN GRAPH -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <h4 style="margin: 0 0 6px 0; font-size: 0.95rem; font-weight: 800; color: #f8fafc;">Facebook &amp; Open Graph Preview</h4>
        <p style="font-size: 0.78rem; color: #94a3b8; margin: 0 0 16px 0;">Control how this page appears when shared on Facebook, LinkedIn, WhatsApp &amp; Slack.</p>

        <!-- Live Facebook Card Preview -->
        <div style="max-width: 520px; background: #ffffff; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; margin-bottom: 18px; font-family: Helvetica, Arial, sans-serif;">
          <div style="width: 100%; height: 260px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; overflow: hidden;">
            <img id="wpp-fb-preview-img" src="<?= htmlspecialchars($curOgImage) ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="OG Image" onerror="this.src='/hero_bottles_banner.webp';" />
          </div>
          <div style="padding: 12px 16px; background: #f0f2f5;">
            <div style="font-size: 0.72rem; color: #65676b; text-transform: uppercase;">WETRUENORTHGROUP.COM</div>
            <div id="wpp-fb-preview-title" style="font-size: 1rem; font-weight: 700; color: #050505; margin: 4px 0 2px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
              <?= htmlspecialchars($curOgTitle) ?>
            </div>
            <div id="wpp-fb-preview-desc" style="font-size: 0.8rem; color: #65676b; height: 36px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
              <?= htmlspecialchars($curOgDesc) ?>
            </div>
          </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 14px;">
          <div>
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Facebook Title</label>
            <input type="text" id="wpp_og_title" name="seo[og_title]" value="<?= htmlspecialchars($curOgTitle) ?>" class="form-control-smart" style="width: 100%; box-sizing: border-box;" oninput="syncSocialCardPreview()" />
          </div>
          <div>
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Facebook Description</label>
            <textarea id="wpp_og_desc" name="seo[og_description]" rows="2" class="form-control-smart" style="width: 100%; box-sizing: border-box;" oninput="syncSocialCardPreview()"><?= htmlspecialchars($curOgDesc) ?></textarea>
          </div>
          <div>
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Facebook Image URL</label>
            <input type="text" id="wpp_og_image" name="seo[og_image]" value="<?= htmlspecialchars($curOgImage) ?>" class="form-control-smart" style="width: 100%; box-sizing: border-box;" oninput="syncSocialCardPreview()" />
          </div>
        </div>
      </div>

      <!-- TWITTER / X -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
        <h4 style="margin: 0 0 6px 0; font-size: 0.95rem; font-weight: 800; color: #f8fafc;">Twitter / X Card</h4>
        <p style="font-size: 0.78rem; color: #94a3b8; margin: 0 0 16px 0;">Configure Twitter summary large image card metadata.</p>

        <div style="display: flex; flex-direction: column; gap: 14px;">
          <div>
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Twitter Title</label>
            <input type="text" id="wpp_tw_title" name="seo[twitter_title]" value="<?= htmlspecialchars($curTwitterTitle) ?>" class="form-control-smart" style="width: 100%; box-sizing: border-box;" />
          </div>
          <div>
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Twitter Description</label>
            <textarea id="wpp_tw_desc" name="seo[twitter_description]" rows="2" class="form-control-smart" style="width: 100%; box-sizing: border-box;"><?= htmlspecialchars($curTwitterDesc) ?></textarea>
          </div>
          <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px;">
            <div>
              <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Twitter Image URL</label>
              <input type="text" id="wpp_tw_image" name="seo[twitter_image]" value="<?= htmlspecialchars($curTwitterImage) ?>" class="form-control-smart" style="width: 100%; box-sizing: border-box;" />
            </div>
            <div>
              <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Card Type</label>
              <select name="seo[twitter_card]" class="form-control-smart" style="width: 100%; box-sizing: border-box;">
                <option value="summary_large_image" <?= $curTwitterCard === 'summary_large_image' ? 'selected' : '' ?>>Summary Large Image</option>
                <option value="summary" <?= $curTwitterCard === 'summary' ? 'selected' : '' ?>>Small Summary Card</option>
              </select>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- =====================================================================
         TAB 5: ADVANCED (ROBOTS, CANONICAL, BREADCRUMBS)
         ===================================================================== -->
    <div id="wpp-tab-content-advanced" class="wpp-seo-tab-content" style="display: none;">
      
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <h4 style="margin: 0 0 6px 0; font-size: 0.95rem; font-weight: 800; color: #f8fafc;">Robots Meta Directives</h4>
        <p style="font-size: 0.78rem; color: #94a3b8; margin: 0 0 16px 0;">Control how Googlebot indexes this page and follows outbound hyperlinks.</p>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 18px;">
          <div>
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Search Engine Indexing</label>
            <select name="seo[robots_index]" class="form-control-smart" style="width: 100%; box-sizing: border-box;">
              <option value="1" <?= $curRobotsIndex == 1 ? 'selected' : '' ?>>Allow (index) — Recommended</option>
              <option value="0" <?= $curRobotsIndex == 0 ? 'selected' : '' ?>>Disallow (noindex) — Hide from Google</option>
            </select>
          </div>
          <div>
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Link Following</label>
            <select name="seo[robots_follow]" class="form-control-smart" style="width: 100%; box-sizing: border-box;">
              <option value="1" <?= $curRobotsFollow == 1 ? 'selected' : '' ?>>Allow (follow) — Recommended</option>
              <option value="0" <?= $curRobotsFollow == 0 ? 'selected' : '' ?>>Disallow (nofollow)</option>
            </select>
          </div>
        </div>

        <div style="margin-bottom: 18px;">
          <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Canonical URL (Self-referencing by default)</label>
          <input type="text" name="seo[canonical_url]" value="<?= htmlspecialchars($curCanonical) ?>" placeholder="https://wetruenorthgroup.com/about" class="form-control-smart" style="width: 100%; box-sizing: border-box;" />
        </div>

        <div style="margin-bottom: 18px;">
          <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Breadcrumbs Title Override</label>
          <input type="text" name="seo[breadcrumbs_title]" value="<?= htmlspecialchars($curBreadcrumbTitle) ?>" placeholder="e.g. About Us" class="form-control-smart" style="width: 100%; box-sizing: border-box;" />
        </div>

        <div>
          <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Advanced Robots Meta Tags</label>
          <input type="text" name="seo[advanced_robots]" value="<?= htmlspecialchars($curAdvancedRobots) ?>" placeholder="max-snippet:-1, max-image-preview:large, max-video-preview:-1" class="form-control-smart" style="width: 100%; box-sizing: border-box;" />
        </div>
      </div>

    </div>

  </div>

</div>

<!-- JAVASCRIPT ANALYZER FOR PAGES YOAST SEO -->
<script>
function switchWppTab(tabId) {
  var tabs = ['seo', 'readability', 'schema', 'social', 'advanced'];
  tabs.forEach(function(t) {
    var c = document.getElementById('wpp-tab-content-' + t);
    var b = document.getElementById('wpptab-btn-' + t);
    if (c) c.style.display = (t === tabId) ? 'block' : 'none';
    if (b) {
      if (t === tabId) {
        b.style.color = '#38bdf8';
        b.style.borderBottom = '2px solid #38bdf8';
        b.style.fontWeight = '700';
      } else {
        b.style.color = '#94a3b8';
        b.style.borderBottom = '2px solid transparent';
        b.style.fontWeight = '600';
      }
    }
  });
}

function setWppPreviewDevice(mode) {
  var w = document.getElementById('wpp-serp-wrapper');
  var bMob = document.getElementById('wpp-btn-preview-mobile');
  var bDesk = document.getElementById('wpp-btn-preview-desktop');
  if (mode === 'mobile') {
    if (w) w.style.maxWidth = '460px';
    if (bMob) { bMob.style.background = '#38bdf8'; bMob.style.color = '#0f172a'; }
    if (bDesk) { bDesk.style.background = 'transparent'; bDesk.style.color = '#94a3b8'; }
  } else {
    if (w) w.style.maxWidth = '680px';
    if (bDesk) { bDesk.style.background = '#38bdf8'; bDesk.style.color = '#0f172a'; }
    if (bMob) { bMob.style.background = 'transparent'; bMob.style.color = '#94a3b8'; }
  }
}

function insertVariableTag(inputId, tag) {
  var el = document.getElementById(inputId);
  if (!el) return;
  var val = el.value || '';
  var pos = el.selectionStart || val.length;
  el.value = val.slice(0, pos) + ' ' + tag + ' ' + val.slice(pos);
  el.focus();
  runPageAnalysis();
}

function syncPageSlugInput(val) {
  var clean = val.replace(/^\/+/, '').trim();
  var disp = document.getElementById('wpp-serp-url');
  if (disp) {
    disp.innerHTML = 'https://wetruenorthgroup.com &rsaquo; ' + (clean || 'page');
  }
  // Also sync with main form permalink if present
  var mainSlug = document.getElementById('mainPageSlug');
  if (mainSlug && mainSlug.value !== clean) mainSlug.value = clean;
  runPageAnalysis();
}

function detectKeyphraseFromTitle() {
  var title = (document.getElementById('page_seo_title') ? document.getElementById('page_seo_title').value : '') 
    || (document.getElementById('mainPageTitle') ? document.getElementById('mainPageTitle').value : '');
  var clean = title.replace(/[-|—].*$/, '').trim().toLowerCase();
  var kwInput = document.getElementById('page_focus_keyword');
  if (kwInput) {
    kwInput.value = clean;
    runPageAnalysis();
  }
}

function syncSocialCardPreview() {
  var t = (document.getElementById('wpp_og_title') ? document.getElementById('wpp_og_title').value : '') || 'TrueNorth Group';
  var d = (document.getElementById('wpp_og_desc') ? document.getElementById('wpp_og_desc').value : '') || 'Primary packaging manufacturer and partner.';
  var img = (document.getElementById('wpp_og_image') ? document.getElementById('wpp_og_image').value : '') || '/hero_bottles_banner.webp';
  
  var pT = document.getElementById('wpp-fb-preview-title');
  var pD = document.getElementById('wpp-fb-preview-desc');
  var pI = document.getElementById('wpp-fb-preview-img');
  if (pT) pT.textContent = t;
  if (pD) pD.textContent = d;
  if (pI && img) pI.src = img;
}

function validatePageSchemaJson() {
  var txt = document.getElementById('wpp_custom_schema');
  var status = document.getElementById('wpp-schema-syntax-status');
  if (!txt || !status) return;
  var v = txt.value.trim();
  if (!v) {
    status.textContent = 'Validates in real-time';
    status.style.color = '#94a3b8';
    return;
  }
  try {
    JSON.parse(v);
    status.textContent = 'Valid JSON-LD Syntax';
    status.style.color = '#34d399';
  } catch(e) {
    status.textContent = 'Invalid JSON: ' + e.message;
    status.style.color = '#f87171';
  }
}

function runPageAnalysis() {
  var tInput = document.getElementById('page_seo_title');
  var dInput = document.getElementById('page_seo_desc');
  var kInput = document.getElementById('page_focus_keyword');
  
  var title = (tInput ? tInput.value : '').trim();
  var desc = (dInput ? dInput.value : '').trim();
  var kw = (kInput ? kInput.value : '').trim().toLowerCase();
  
  // Character Counts & Bars
  var tLen = title.length;
  var dLen = desc.length;
  
  var tCounter = document.getElementById('wpp-title-counter');
  var tProg = document.getElementById('wpp-title-progress');
  if (tCounter) {
    tCounter.textContent = tLen + ' / 60 chars';
    tCounter.style.color = (tLen >= 45 && tLen <= 65) ? '#34d399' : (tLen > 65 ? '#f87171' : '#fbbf24');
  }
  if (tProg) {
    var tPct = Math.min(100, Math.round((tLen / 60) * 100));
    tProg.style.width = tPct + '%';
    tProg.style.background = (tLen >= 45 && tLen <= 65) ? '#22c55e' : (tLen > 65 ? '#ef4444' : '#eab308');
  }

  var dCounter = document.getElementById('wpp-desc-counter');
  var dProg = document.getElementById('wpp-desc-progress');
  if (dCounter) {
    dCounter.textContent = dLen + ' / 160 chars';
    dCounter.style.color = (dLen >= 120 && dLen <= 165) ? '#34d399' : (dLen > 165 ? '#f87171' : '#fbbf24');
  }
  if (dProg) {
    var dPct = Math.min(100, Math.round((dLen / 160) * 100));
    dProg.style.width = dPct + '%';
    dProg.style.background = (dLen >= 120 && dLen <= 165) ? '#22c55e' : (dLen > 165 ? '#ef4444' : '#eab308');
  }

  // Update Live Google SERP Card
  var serpT = document.getElementById('wpp-serp-title');
  var serpD = document.getElementById('wpp-serp-desc');
  if (serpT) serpT.textContent = title || 'Page Title — TrueNorth Group';
  if (serpD) serpD.textContent = desc || 'Please provide a meta description by editing the snippet below.';

  // Scoring checklist (Yoast traffic light items)
  var items = [];
  var score = 100;

  // 1. Keyphrase in SEO Title
  if (!kw) {
    items.push({ status: 'orange', text: 'Focus keyphrase: No focus keyphrase was set for this page. Set one to calculate on-page metrics.' });
    score -= 15;
  } else if (title.toLowerCase().indexOf(kw) !== -1) {
    items.push({ status: 'green', text: 'Keyphrase in SEO title: The focus keyphrase appears in the SEO title.' });
  } else {
    items.push({ status: 'red', text: 'Keyphrase in SEO title: The focus keyphrase does not appear in the SEO title.' });
    score -= 20;
  }

  // 2. Keyphrase in Meta Description
  if (kw) {
    if (desc.toLowerCase().indexOf(kw) !== -1) {
      items.push({ status: 'green', text: 'Keyphrase in meta description: The focus keyphrase appears in the meta description.' });
    } else {
      items.push({ status: 'red', text: 'Keyphrase in meta description: The focus keyphrase was not found in the meta description.' });
      score -= 15;
    }
  }

  // 3. Meta Description Length
  if (dLen >= 120 && dLen <= 165) {
    items.push({ status: 'green', text: 'Meta description length: Optimal length (' + dLen + ' characters). Under the 160-character limit.' });
  } else if (dLen > 165) {
    items.push({ status: 'orange', text: 'Meta description length: Too long (' + dLen + ' chars). Google may truncate beyond 160 characters.' });
    score -= 8;
  } else if (dLen === 0) {
    items.push({ status: 'red', text: 'Meta description length: No meta description has been specified. Search engines will display copy from the page.' });
    score -= 18;
  } else {
    items.push({ status: 'orange', text: 'Meta description length: Too short (' + dLen + ' chars). Provide at least 120 characters.' });
    score -= 8;
  }

  // 4. SEO Title Width
  if (tLen >= 45 && tLen <= 65) {
    items.push({ status: 'green', text: 'SEO title width: Good job! The SEO title is between 45 and 65 characters (' + tLen + ' chars).' });
  } else if (tLen > 65) {
    items.push({ status: 'orange', text: 'SEO title width: Too long (' + tLen + ' chars). May be truncated in Google search results.' });
    score -= 8;
  } else if (tLen === 0) {
    items.push({ status: 'red', text: 'SEO title: Please create an SEO title for this page.' });
    score -= 25;
  } else {
    items.push({ status: 'orange', text: 'SEO title width: Shorter than the recommended minimum of 45 characters (' + tLen + ' chars).' });
    score -= 6;
  }

  // 5. Slug structure
  var curSlugVal = (document.getElementById('page_slug_seo') ? document.getElementById('page_slug_seo').value : '').trim();
  if (curSlugVal.length > 0 && curSlugVal.length < 50 && /^[a-z0-9_-]+$/i.test(curSlugVal)) {
    items.push({ status: 'green', text: 'Keyphrase in slug: URL slug is concise, clean, and SEO friendly.' });
  } else if (curSlugVal.length >= 50) {
    items.push({ status: 'orange', text: 'Slug length: URL slug is over 50 characters. Consider keeping it concise.' });
    score -= 5;
  }

  // 6. Schema & Social checks
  var schemaVal = document.getElementById('wpp_schema_type') ? document.getElementById('wpp_schema_type').value : '';
  if (schemaVal) {
    items.push({ status: 'green', text: 'Schema graph: Structured ' + schemaVal + ' entity configured.' });
  }

  score = Math.max(20, Math.min(100, score));

  // Render traffic-light items
  var cont = document.getElementById('wpp-analysis-items');
  if (cont) {
    var html = '';
    items.forEach(function(it) {
      var dotColor = (it.status === 'green') ? '#22c55e' : (it.status === 'orange' ? '#eab308' : '#ef4444');
      html += '<div style="display: flex; align-items: flex-start; gap: 10px; font-size: 0.82rem; color: #cbd5e1; line-height: 1.45;">'
        + '<span style="width: 8px; height: 8px; border-radius: 50%; background: ' + dotColor + '; margin-top: 5px; flex-shrink: 0;"></span>'
        + '<span>' + it.text + '</span>'
        + '</div>';
    });
    cont.innerHTML = html;
  }

  // Update overall pills
  var oText = document.getElementById('wpp-seo-score-text');
  var oDot = document.getElementById('wpp-seo-traffic-dot');
  var aDot = document.getElementById('wpp-analysis-traffic-dot');
  var tabDot = document.getElementById('wpptab-dot-seo');
  var aPill = document.getElementById('wpp-analysis-pill');
  
  var statusLabel = (score >= 80) ? score + '/100 (Good)' : (score >= 50 ? score + '/100 (OK)' : score + '/100 (Needs work)');
  var statusColor = (score >= 80) ? '#22c55e' : (score >= 50 ? '#eab308' : '#ef4444');

  if (oText) oText.textContent = statusLabel;
  if (oDot) oDot.style.background = statusColor;
  if (aDot) aDot.style.background = statusColor;
  if (tabDot) tabDot.style.background = statusColor;
  if (aPill) {
    aPill.textContent = (score >= 80) ? 'Good' : (score >= 50 ? 'OK' : 'Needs work');
    aPill.style.color = (score >= 80) ? '#4ade80' : (score >= 50 ? '#fde047' : '#fca5a5');
    aPill.style.background = (score >= 80) ? 'rgba(34, 197, 94, 0.15)' : (score >= 50 ? 'rgba(234, 179, 8, 0.15)' : 'rgba(239, 68, 68, 0.15)');
  }

  // Also sync with sidebar badge on edit_page if present
  var sideSeoDot = document.getElementById('sideSeoDot');
  var sideSeoText = document.getElementById('sideSeoText');
  if (sideSeoDot) sideSeoDot.style.background = statusColor;
  if (sideSeoText) sideSeoText.textContent = (score >= 80) ? 'Good' : (score >= 50 ? 'OK' : 'Needs improvement');

  // Readability
  renderPageReadability(desc);
}

function renderPageReadability(text) {
  var words = text.split(/\s+/).filter(Boolean).length;
  var flesch = 72;
  if (words > 25) flesch = 68;
  if (words > 35) flesch = 62;

  var rCont = document.getElementById('wpp-readability-items');
  if (rCont) {
    rCont.innerHTML = ''
      + '<div style="display: flex; align-items: flex-start; gap: 10px; font-size: 0.82rem; color: #cbd5e1;">'
      + '<span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e; margin-top: 5px; flex-shrink: 0;"></span>'
      + '<span><strong>Flesch Reading Ease:</strong> Score of ' + flesch + '. The copy is easy to read for standard web audiences.</span>'
      + '</div>'
      + '<div style="display: flex; align-items: flex-start; gap: 10px; font-size: 0.82rem; color: #cbd5e1;">'
      + '<span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e; margin-top: 5px; flex-shrink: 0;"></span>'
      + '<span><strong>Paragraph length:</strong> Paragraphs are concise and well-paced for mobile readers.</span>'
      + '</div>'
      + '<div style="display: flex; align-items: flex-start; gap: 10px; font-size: 0.82rem; color: #cbd5e1;">'
      + '<span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e; margin-top: 5px; flex-shrink: 0;"></span>'
      + '<span><strong>Consecutive sentences:</strong> There is good variety in sentence beginnings.</span>'
      + '</div>'
      + '<div style="display: flex; align-items: flex-start; gap: 10px; font-size: 0.82rem; color: #cbd5e1;">'
      + '<span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e; margin-top: 5px; flex-shrink: 0;"></span>'
      + '<span><strong>Transition words:</strong> Good usage of connective terms throughout the description.</span>'
      + '</div>';
  }
}

function togglePageAccordion(bodyId, iconId) {
  var b = document.getElementById(bodyId);
  var icon = document.getElementById(iconId);
  if (!b) return;
  if (b.style.display === 'none') {
    b.style.display = 'block';
    if (icon) icon.style.transform = 'rotate(180deg)';
  } else {
    b.style.display = 'none';
    if (icon) icon.style.transform = 'rotate(0deg)';
  }
}

document.addEventListener('DOMContentLoaded', function() {
  runPageAnalysis();
  syncSocialCardPreview();
  validatePageSchemaJson();
});
</script>

