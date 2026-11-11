<?php
// ==============================================================================
// TRUENORTH CMS — WORDPRESS YOAST & RANK MATH STYLE SEO SUITE FOR BLOGS
// Complete 5-Tab SEO System: SEO, Readability, Schema, Social, Advanced
// Features live mobile/desktop SERP toggles, traffic-light scoring, Flesch Reading Ease
// ==============================================================================

if (!class_exists('SeoModel')) {
    require_once dirname(__DIR__, 2) . '/app/Models/SeoModel.php';
}

$curSlug = $post['slug'] ?? '';
$curBlogSeo = [];
if (!empty($curSlug)) {
    $curBlogSeo = SeoModel::getPageBySlug('/blog/' . $curSlug) ?? [];
}

$curSeoTitle = !empty($curBlogSeo['meta_title']) ? $curBlogSeo['meta_title'] : (!empty($post['meta_title']) ? $post['meta_title'] : (!empty($title) ? ($title . ' — TrueNorth Packaging') : ''));
$curSeoDesc = !empty($curBlogSeo['meta_description']) ? $curBlogSeo['meta_description'] : (!empty($post['meta_description']) ? $post['meta_description'] : (!empty($excerpt) ? $excerpt : ''));
$curKeywords = !empty($curBlogSeo['meta_keywords']) ? $curBlogSeo['meta_keywords'] : ($post['tags'] ?? ($post['meta_keywords'] ?? ''));
$curFocusKeyword = !empty($curBlogSeo['focus_keyword']) ? $curBlogSeo['focus_keyword'] : ($post['focus_keyword'] ?? '');
$curCanonical = !empty($curBlogSeo['canonical_url']) ? $curBlogSeo['canonical_url'] : ($post['canonical_url'] ?? '');
$curH1 = !empty($curBlogSeo['h1_override']) ? $curBlogSeo['h1_override'] : ($post['h1_override'] ?? '');
$curRobotsIndex = isset($curBlogSeo['robots_index']) ? (int)$curBlogSeo['robots_index'] : (isset($post['robots_index']) ? (int)$post['robots_index'] : 1);
$curRobotsFollow = isset($curBlogSeo['robots_follow']) ? (int)$curBlogSeo['robots_follow'] : (isset($post['robots_follow']) ? (int)$post['robots_follow'] : 1);
$curOgTitle = !empty($curBlogSeo['og_title']) ? $curBlogSeo['og_title'] : ($post['og_title'] ?? '');
$curOgDesc = !empty($curBlogSeo['og_description']) ? $curBlogSeo['og_description'] : ($post['og_description'] ?? '');
$curOgImage = !empty($curBlogSeo['og_image']) ? $curBlogSeo['og_image'] : ($post['og_image'] ?? ($featuredImage ?? '/logo.webp'));
$curTwitterTitle = !empty($curBlogSeo['twitter_title']) ? $curBlogSeo['twitter_title'] : ($post['twitter_title'] ?? '');
$curTwitterDesc = !empty($curBlogSeo['twitter_description']) ? $curBlogSeo['twitter_description'] : ($post['twitter_description'] ?? '');
$curTwitterImage = !empty($curBlogSeo['twitter_image']) ? $curBlogSeo['twitter_image'] : ($post['twitter_image'] ?? $curOgImage);
$curTwitterCard = !empty($curBlogSeo['twitter_card']) ? $curBlogSeo['twitter_card'] : ($post['twitter_card'] ?? 'summary_large_image');
$curSchemaType = !empty($curBlogSeo['schema_type']) ? $curBlogSeo['schema_type'] : ($post['schema_type'] ?? 'BlogPosting');
$curCustomSchema = !empty($curBlogSeo['custom_schema_json']) ? $curBlogSeo['custom_schema_json'] : ($post['custom_schema_json'] ?? '');
$curCornerstone = !empty($curBlogSeo['cornerstone_content']) ? 1 : (!empty($post['cornerstone_content']) ? 1 : 0);
$curBreadcrumbTitle = !empty($curBlogSeo['breadcrumbs_title']) ? $curBlogSeo['breadcrumbs_title'] : ($post['breadcrumbs_title'] ?? '');
$curAdvancedRobots = !empty($curBlogSeo['advanced_robots']) ? $curBlogSeo['advanced_robots'] : ($post['advanced_robots'] ?? '');

$blogFaqs = [];
if (!empty($curSlug)) {
    $blogFaqs = SeoModel::getFaqs('blog', '/blog/' . $curSlug);
}
?>

<!-- WORDPRESS BLOG SEO METABOX CONTAINER -->
<div class="form-card wp-seo-metabox" id="wp-blog-seo-panel" style="border: 1px solid #38bdf8; background: #0f172a; border-radius: 16px; margin-bottom: 28px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.35);">
  
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
        <div style="font-size: 0.76rem; color: #94a3b8;">Article SEO, Flesch readability, rich schema &amp; social card manager</div>
      </div>
    </div>

    <!-- LIVE METABOX SCORE PILLS (TRAFFIC LIGHTS) -->
    <div style="display: flex; align-items: center; gap: 10px;">
      <div id="wpb-seo-overall-pill" style="display: flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); font-size: 0.8rem; font-weight: 700; color: #4ade80;">
        <span id="wpb-seo-traffic-dot" style="width: 10px; height: 10px; border-radius: 50%; background: #22c55e;"></span>
        <span>SEO: <strong id="wpb-seo-score-text">85/100 (Good)</strong></span>
      </div>
      <div id="wpb-readability-pill" style="display: flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.4); font-size: 0.8rem; font-weight: 700; color: #38bdf8;">
        <span id="wpb-readability-dot" style="width: 10px; height: 10px; border-radius: 50%; background: #38bdf8;"></span>
        <span>Readability: <strong id="wpb-readability-score-text">Good</strong></span>
      </div>
    </div>
  </div>

  <!-- TAB NAVIGATION BAR (5 WORDPRESS TABS) -->
  <div style="display: flex; background: #0b1120; border-bottom: 1px solid #334155; padding: 0 16px; overflow-x: auto;">
    <button type="button" class="wpb-seo-tab-btn active" onclick="switchWpbTab('seo')" id="wpbtab-btn-seo" style="display: flex; align-items: center; gap: 8px; padding: 14px 20px; border: none; background: transparent; color: #38bdf8; font-weight: 700; font-size: 0.88rem; border-bottom: 2px solid #38bdf8; cursor: pointer; transition: all 0.15s;">
      <span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e;" id="wpbtab-dot-seo"></span>
      <span>SEO</span>
    </button>
    <button type="button" class="wpb-seo-tab-btn" onclick="switchWpbTab('readability')" id="wpbtab-btn-readability" style="display: flex; align-items: center; gap: 8px; padding: 14px 20px; border: none; background: transparent; color: #94a3b8; font-weight: 600; font-size: 0.88rem; border-bottom: 2px solid transparent; cursor: pointer; transition: all 0.15s;">
      <span style="width: 8px; height: 8px; border-radius: 50%; background: #38bdf8;" id="wpbtab-dot-readability"></span>
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

    <!-- ===================================================================== -->
    <!-- TAB 1: SEO -->
    <!-- ===================================================================== -->
    <div id="wpb-sec-seo" class="wpb-tab-section">
      
      <!-- FOCUS KEYPHRASE (YOAST CORE) -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
          <label style="font-size: 0.88rem; font-weight: 700; color: #f8fafc; display: flex; align-items: center; gap: 8px;">
            <span>Focus Keyphrase</span>
            <span style="font-size: 0.72rem; color: #38bdf8; font-weight: normal;">(Yoast / Rank Math scoring target)</span>
          </label>
          <span style="font-size: 0.74rem; color: #94a3b8;">Primary search query for this article</span>
        </div>
        <input type="text" id="wpbFocusKeyword" name="focus_keyword" value="<?= htmlspecialchars($curFocusKeyword) ?>" placeholder="e.g. lotion pumps, dispensing closures, cleanroom bottle manufacturing" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.92rem;" oninput="runWpbSeoAnalysis()" />
        <div style="font-size: 0.74rem; color: #94a3b8; margin-top: 6px;">The focus keyphrase is evaluated across your title, slug, meta description, H1, first paragraph, and content density.</div>
      </div>

      <!-- GOOGLE SERP LIVE PREVIEW (MOBILE VS DESKTOP) -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
          <div style="font-size: 0.82rem; font-weight: 700; color: #cbd5e1; text-transform: uppercase; letter-spacing: 0.05em;">
            Google Search Result Snippet Preview
          </div>
          <div style="display: flex; background: #0f172a; border: 1px solid #475569; border-radius: 6px; padding: 2px;">
            <button type="button" id="wpbbtn-preview-mobile" onclick="toggleWpbSerpDevice('mobile')" style="padding: 5px 12px; font-size: 0.76rem; font-weight: 700; border: none; border-radius: 4px; background: #38bdf8; color: #0f172a; cursor: pointer;">
              Mobile
            </button>
            <button type="button" id="wpbbtn-preview-desktop" onclick="toggleWpbSerpDevice('desktop')" style="padding: 5px 12px; font-size: 0.76rem; font-weight: 700; border: none; border-radius: 4px; background: transparent; color: #94a3b8; cursor: pointer;">
              Desktop
            </button>
          </div>
        </div>

        <!-- SNIPPET CARD -->
        <div id="wpbserp-container" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; font-family: Roboto, Arial, sans-serif; max-width: 480px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
          <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
            <img src="/logo_svg.svg" style="width: 18px; height: 18px; border-radius: 50%;" alt="Favicon" />
            <div style="display: flex; flex-direction: column; line-height: 1.2;">
              <span style="font-size: 0.8rem; color: #202124; font-weight: 500;">TrueNorth Group</span>
              <span id="wpbserp-url-display" style="font-size: 0.72rem; color: #4d5156;">https://wetruenorthgroup.com &rsaquo; blog &rsaquo; ...</span>
            </div>
          </div>
          <div id="wpbserp-title-display" style="font-size: 1.25rem; color: #1a0dab; font-weight: 400; line-height: 1.3; cursor: pointer; margin-bottom: 4px; word-break: break-word;">
            Article Title &mdash; TrueNorth Packaging
          </div>
          <div id="wpbserp-desc-display" style="font-size: 0.86rem; color: #4d5156; line-height: 1.45; word-break: break-word;">
            Meta description snippet will render here dynamically as you write...
          </div>
        </div>
      </div>

      <!-- SEO TITLE WITH INSERT VARIABLE TAGS -->
      <div style="margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
          <label style="font-size: 0.86rem; font-weight: 700; color: #cbd5e1;">
            SEO Title
          </label>
          <span id="wpbTitleCounter" style="font-size: 0.76rem; font-weight: 700; color: #34d399;">0 / 60 chars</span>
        </div>

        <!-- TAG BUTTONS (YOAST STYLE) -->
        <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px;">
          <button type="button" onclick="insertWpbTag('wpbTitle', '{title}')" style="padding: 3px 9px; font-size: 0.74rem; background: #1e293b; border: 1px solid #475569; color: #38bdf8; border-radius: 4px; cursor: pointer;">+ Title</button>
          <button type="button" onclick="insertWpbTag('wpbTitle', '{separator}')" style="padding: 3px 9px; font-size: 0.74rem; background: #1e293b; border: 1px solid #475569; color: #38bdf8; border-radius: 4px; cursor: pointer;">+ Sep</button>
          <button type="button" onclick="insertWpbTag('wpbTitle', '{site_name}')" style="padding: 3px 9px; font-size: 0.74rem; background: #1e293b; border: 1px solid #475569; color: #38bdf8; border-radius: 4px; cursor: pointer;">+ Site title</button>
          <button type="button" onclick="insertWpbTag('wpbTitle', '{primary_category}')" style="padding: 3px 9px; font-size: 0.74rem; background: #1e293b; border: 1px solid #475569; color: #38bdf8; border-radius: 4px; cursor: pointer;">+ Category</button>
        </div>

        <input type="text" id="wpbTitle" name="meta_title" value="<?= htmlspecialchars($curSeoTitle) ?>" placeholder="Target 50-60 characters including focus keyphrase" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #1e293b; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.92rem;" oninput="runWpbSeoAnalysis()" />
        <div style="height: 4px; background: #334155; border-radius: 2px; margin-top: 6px; overflow: hidden;">
          <div id="wpbTitleBar" style="height: 100%; width: 0%; background: #34d399; transition: width 0.2s, background 0.2s;"></div>
        </div>
      </div>

      <!-- META DESCRIPTION -->
      <div style="margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
          <label style="font-size: 0.86rem; font-weight: 700; color: #cbd5e1;">
            Meta Description
          </label>
          <span id="wpbDescCounter" style="font-size: 0.76rem; font-weight: 700; color: #34d399;">0 / 160 chars</span>
        </div>
        <textarea id="wpbDesc" name="meta_description" rows="3" placeholder="Target 140-160 characters. Write a persuasive summary incorporating the focus keyphrase and target specifications." style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #1e293b; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem; line-height: 1.45; resize: vertical;" oninput="runWpbSeoAnalysis()"><?= htmlspecialchars($curSeoDesc) ?></textarea>
        <div style="height: 4px; background: #334155; border-radius: 2px; margin-top: 6px; overflow: hidden;">
          <div id="wpbDescBar" style="height: 100%; width: 0%; background: #34d399; transition: width 0.2s, background 0.2s;"></div>
        </div>
      </div>

      <!-- CORNERSTONE CONTENT & H1 HEADING OVERRIDE -->
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; align-items: center;">
        <div style="background: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 14px 16px; display: flex; align-items: center; gap: 10px;">
          <input type="checkbox" id="wpbCornerstone" name="cornerstone_content" value="1" <?= $curCornerstone ? 'checked' : '' ?> style="width: 18px; height: 18px; cursor: pointer;" onchange="runWpbSeoAnalysis()" />
          <label for="wpbCornerstone" style="font-size: 0.86rem; color: #f8fafc; font-weight: 600; cursor: pointer;">
            Cornerstone Content (High-priority article)
          </label>
        </div>
        <div>
          <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 4px;">H1 Heading Override</label>
          <input type="text" id="wpbH1Override" name="h1_override" value="<?= htmlspecialchars($curH1) ?>" placeholder="Defaults to Article Title" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: #1e293b; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" oninput="runWpbSeoAnalysis()" />
        </div>
      </div>

      <!-- YOAST-STYLE SEO ANALYSIS CHECKLIST (TRAFFIC LIGHTS) -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
        <div style="font-size: 0.9rem; font-weight: 800; color: #f8fafc; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between;">
          <span>Yoast / Rank Math SEO Analysis</span>
          <span style="font-size: 0.74rem; color: #94a3b8;">Real-Time On-Page Audit</span>
        </div>

        <!-- PROBLEMS (RED) -->
        <div style="margin-bottom: 14px;">
          <div style="font-size: 0.78rem; font-weight: 800; color: #f87171; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <span style="width: 8px; height: 8px; border-radius: 50%; background: #ef4444;"></span>
            <span>Problems (<span id="wpbCountProblems">0</span>)</span>
          </div>
          <div id="wpbListProblems" style="display: flex; flex-direction: column; gap: 6px; font-size: 0.82rem; padding-left: 14px;"></div>
        </div>

        <!-- IMPROVEMENTS (AMBER) -->
        <div style="margin-bottom: 14px;">
          <div style="font-size: 0.78rem; font-weight: 800; color: #fbbf24; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <span style="width: 8px; height: 8px; border-radius: 50%; background: #f59e0b;"></span>
            <span>Improvements (<span id="wpbCountImprovements">0</span>)</span>
          </div>
          <div id="wpbListImprovements" style="display: flex; flex-direction: column; gap: 6px; font-size: 0.82rem; padding-left: 14px;"></div>
        </div>

        <!-- GOOD RESULTS (GREEN) -->
        <div>
          <div style="font-size: 0.78rem; font-weight: 800; color: #4ade80; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e;"></span>
            <span>Good Results (<span id="wpbCountGood">0</span>)</span>
          </div>
          <div id="wpbListGood" style="display: flex; flex-direction: column; gap: 6px; font-size: 0.82rem; padding-left: 14px;"></div>
        </div>
      </div>

    </div>

    <!-- ===================================================================== -->
    <!-- TAB 2: READABILITY (FLESCH READING EASE & STRUCTURE) -->
    <!-- ===================================================================== -->
    <div id="wpb-sec-readability" class="wpb-tab-section" style="display: none;">
      
      <!-- FLESCH READING EASE SCORE CARD -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 22px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
          <div>
            <div style="font-size: 0.8rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Flesch Reading Ease Formula</div>
            <div id="wpbFleschScoreDisplay" style="font-size: 1.8rem; font-weight: 800; color: #38bdf8;">72.4 &mdash; Fairly Easy to Read</div>
          </div>
          <div id="wpbFleschBadge" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); padding: 5px 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 700;">
            Web Standard Grade
          </div>
        </div>
        <p style="font-size: 0.84rem; color: #cbd5e1; line-height: 1.5; margin: 0 0 14px 0;">
          Calculated automatically from your article body (<strong id="wpbArticleWordCount">0 words</strong>) based on average sentence length and syllables per word. Scores 60.0&ndash;80.0 are optimal for technical B2B packaging readers.
        </p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; background: #0f172a; padding: 12px 16px; border-radius: 8px;">
          <div>
            <div style="font-size: 0.72rem; color: #94a3b8;">Total Sentences</div>
            <div id="wpbStatSentences" style="font-size: 1.1rem; font-weight: 700; color: #fff;">0</div>
          </div>
          <div>
            <div style="font-size: 0.72rem; color: #94a3b8;">Avg Sentence Length</div>
            <div id="wpbStatAvgSentence" style="font-size: 1.1rem; font-weight: 700; color: #fff;">0 words</div>
          </div>
          <div>
            <div style="font-size: 0.72rem; color: #94a3b8;">Avg Syllables/Word</div>
            <div id="wpbStatAvgSyllable" style="font-size: 1.1rem; font-weight: 700; color: #fff;">1.5</div>
          </div>
          <div>
            <div style="font-size: 0.72rem; color: #94a3b8;">Estimated Reading Time</div>
            <div id="wpbStatReadingTime" style="font-size: 1.1rem; font-weight: 700; color: #fff;">1 min</div>
          </div>
        </div>
      </div>

      <!-- READABILITY CHECKLIST -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
        <div style="font-size: 0.9rem; font-weight: 800; color: #f8fafc; margin-bottom: 14px;">Readability Audit Criteria</div>
        <div id="wpbReadabilityChecklist" style="display: flex; flex-direction: column; gap: 10px; font-size: 0.84rem;"></div>
      </div>

    </div>

    <!-- ===================================================================== -->
    <!-- TAB 3: SCHEMA (STRUCTURED DATA & FAQS) -->
    <!-- ===================================================================== -->
    <div id="wpb-sec-schema" class="wpb-tab-section" style="display: none;">
      
      <!-- SCHEMA TYPE SELECTOR -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <label style="display: block; font-size: 0.86rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
          Schema.org Article Type
        </label>
        <select id="wpbSchemaType" name="schema_type" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.9rem;">
          <option value="BlogPosting" <?= $curSchemaType === 'BlogPosting' ? 'selected' : '' ?>>BlogPosting (Standard Blog Article)</option>
          <option value="Article" <?= $curSchemaType === 'Article' ? 'selected' : '' ?>>Article (General Editorial &amp; News)</option>
          <option value="TechArticle" <?= $curSchemaType === 'TechArticle' ? 'selected' : '' ?>>TechArticle (Engineering &amp; Technical Specs)</option>
          <option value="NewsArticle" <?= $curSchemaType === 'NewsArticle' ? 'selected' : '' ?>>NewsArticle (Press Release &amp; Updates)</option>
        </select>
        <div style="font-size: 0.74rem; color: #94a3b8; margin-top: 6px;">
          Automatically generates rich JSON-LD Article structured data including headline, author, publisher, datePublished, and featured image.
        </div>
      </div>

      <!-- FAQ REPEATER FOR GOOGLE FAQ ACCORDION -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
          <div>
            <div style="font-size: 0.9rem; font-weight: 800; color: #f8fafc;">Google FAQPage Rich Snippet Repeater</div>
            <div style="font-size: 0.74rem; color: #94a3b8;">Inject collapsible questions directly below your article listing in Google SERPs</div>
          </div>
          <button type="button" onclick="addWpbFaqRow()" style="padding: 6px 14px; background: #0284c7; color: #fff; border: none; border-radius: 6px; font-size: 0.78rem; font-weight: 700; cursor: pointer;">
            + Add FAQ Question
          </button>
        </div>
        <div id="wpbFaqContainer" style="display: flex; flex-direction: column; gap: 12px;">
          <?php if (!empty($blogFaqs)): ?>
            <?php foreach ($blogFaqs as $fq): ?>
              <div style="background: #0f172a; padding: 14px; border-radius: 8px; border: 1px solid #334155; display: flex; flex-direction: column; gap: 8px;">
                <div style="display: flex; gap: 8px; align-items: center;">
                  <input type="text" name="faq_questions[]" value="<?= htmlspecialchars($fq['question'] ?? '') ?>" placeholder="FAQ Question" style="flex: 1; padding: 8px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.85rem;" />
                  <button type="button" onclick="this.closest('div').parentElement.remove()" style="padding: 7px 12px; background: #ef4444; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 0.76rem;">Delete</button>
                </div>
                <textarea name="faq_answers[]" rows="2" placeholder="FAQ Answer (renders in Google SERP accordion)" style="width: 100%; box-sizing: border-box; padding: 8px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.85rem;"><?= htmlspecialchars($fq['answer'] ?? '') ?></textarea>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div id="wpbFaqEmptyNotice" style="font-size: 0.8rem; color: #94a3b8; padding: 8px 0;">No FAQ questions added yet. Click &quot;+ Add FAQ Question&quot; to inject rich accordion FAQs into Google SERPs.</div>
          <?php endif; ?>
        </div>
      </div>

      <!-- CUSTOM JSON-LD WITH LIVE SYNTAX VALIDATOR -->
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
          <label style="font-size: 0.86rem; font-weight: 700; color: #cbd5e1;">
            Custom JSON-LD Schema Override (Optional)
          </label>
          <span id="wpbJsonStatusBadge" style="font-size: 0.72rem; padding: 3px 8px; border-radius: 4px; background: #334155; color: #94a3b8; font-weight: 700;">
            No Custom JSON
          </span>
        </div>
        <textarea id="wpbCustomSchema" name="custom_schema_json" rows="6" placeholder='{"@context": "https://schema.org", "@type": "TechArticle", ...}' style="width: 100%; box-sizing: border-box; padding: 12px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #38bdf8; font-family: monospace; font-size: 0.84rem; line-height: 1.45;" oninput="validateWpbJson()"><?= htmlspecialchars($curCustomSchema) ?></textarea>
      </div>

    </div>

    <!-- ===================================================================== -->
    <!-- TAB 4: SOCIAL (FACEBOOK & TWITTER) -->
    <!-- ===================================================================== -->
    <div id="wpb-sec-social" class="wpb-tab-section" style="display: none;">
      
      <!-- SUB TABS: FACEBOOK VS TWITTER -->
      <div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid #334155; padding-bottom: 12px;">
        <button type="button" id="wpbsubtab-fb" onclick="switchWpbSocialSubTab('fb')" style="padding: 7px 16px; border: 1px solid #0284c7; background: #0284c7; color: #fff; border-radius: 6px; font-size: 0.82rem; font-weight: 700; cursor: pointer;">
          Facebook / Open Graph
        </button>
        <button type="button" id="wpbsubtab-tw" onclick="switchWpbSocialSubTab('tw')" style="padding: 7px 16px; border: 1px solid #475569; background: transparent; color: #94a3b8; border-radius: 6px; font-size: 0.82rem; font-weight: 700; cursor: pointer;">
          Twitter / X Card
        </button>
      </div>

      <!-- FACEBOOK SECTION -->
      <div id="wpbsocial-fb">
        
        <!-- CARD MOCKUP -->
        <div style="background: #1877f2; padding: 1px; border-radius: 12px; margin-bottom: 20px; max-width: 520px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.3);">
          <div style="background: #242526; color: #e4e6eb; font-family: system-ui, -apple-system, sans-serif;">
            <div id="wpbcard-fb-img" style="height: 230px; background: #1e293b url('<?= htmlspecialchars($curOgImage) ?>') center/cover no-repeat;"></div>
            <div style="padding: 12px 16px; background: #3a3b3c;">
              <div style="font-size: 0.72rem; color: #b0b3b8; text-transform: uppercase;">WETRUENORTHGROUP.COM</div>
              <div id="wpbcard-fb-title" style="font-size: 1rem; font-weight: 700; color: #e4e6eb; margin: 4px 0 2px 0; line-height: 1.3;">Article Title</div>
              <div id="wpbcard-fb-desc" style="font-size: 0.8rem; color: #b0b3b8; line-height: 1.35; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Meta description preview for Facebook/LinkedIn newsfeeds.</div>
            </div>
          </div>
        </div>

        <div style="margin-bottom: 16px;">
          <label style="display: block; font-size: 0.84rem; color: #cbd5e1; margin-bottom: 4px;">Facebook Title (og:title)</label>
          <input type="text" id="wpbOgTitle" name="og_title" value="<?= htmlspecialchars($curOgTitle) ?>" placeholder="Defaults to SEO Title" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" oninput="updateWpbSocialPreviews()" />
        </div>

        <div style="margin-bottom: 16px;">
          <label style="display: block; font-size: 0.84rem; color: #cbd5e1; margin-bottom: 4px;">Facebook Description (og:description)</label>
          <textarea id="wpbOgDescription" name="og_description" rows="2" placeholder="Defaults to Meta Description" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" oninput="updateWpbSocialPreviews()"><?= htmlspecialchars($curOgDesc) ?></textarea>
        </div>

        <div>
          <label style="display: block; font-size: 0.84rem; color: #cbd5e1; margin-bottom: 4px;">Facebook Image URL (og:image)</label>
          <input type="text" id="wpbOgImage" name="og_image" value="<?= htmlspecialchars($curOgImage) ?>" placeholder="/uploads/blog-cover.webp" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" oninput="updateWpbSocialPreviews()" />
        </div>

      </div>

      <!-- TWITTER / X SECTION -->
      <div id="wpbsocial-tw" style="display: none;">
        
        <div style="background: #000; border: 1px solid #2f3336; border-radius: 12px; margin-bottom: 20px; max-width: 520px; overflow: hidden; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
          <div id="wpbcard-tw-img" style="height: 210px; background: #1e293b url('<?= htmlspecialchars($curTwitterImage) ?>') center/cover no-repeat;"></div>
          <div style="padding: 12px 16px;">
            <div style="font-size: 0.72rem; color: #71767b;">wetruenorthgroup.com</div>
            <div id="wpbcard-tw-title" style="font-size: 0.96rem; font-weight: 700; color: #e7e9ea; margin: 3px 0;">Twitter Title</div>
            <div id="wpbcard-tw-desc" style="font-size: 0.8rem; color: #71767b; line-height: 1.35;">Twitter description snippet preview...</div>
          </div>
        </div>

        <div style="margin-bottom: 16px;">
          <label style="display: block; font-size: 0.84rem; color: #cbd5e1; margin-bottom: 4px;">Twitter Card Type</label>
          <select id="wpbTwitterCard" name="twitter_card" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;">
            <option value="summary_large_image" <?= $curTwitterCard === 'summary_large_image' ? 'selected' : '' ?>>Summary Large Image (Recommended)</option>
            <option value="summary" <?= $curTwitterCard === 'summary' ? 'selected' : '' ?>>Summary Small Card</option>
          </select>
        </div>

        <div style="margin-bottom: 16px;">
          <label style="display: block; font-size: 0.84rem; color: #cbd5e1; margin-bottom: 4px;">Twitter Title (twitter:title)</label>
          <input type="text" id="wpbTwitterTitle" name="twitter_title" value="<?= htmlspecialchars($curTwitterTitle) ?>" placeholder="Defaults to OG Title or Meta Title" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" oninput="updateWpbSocialPreviews()" />
        </div>

        <div style="margin-bottom: 16px;">
          <label style="display: block; font-size: 0.84rem; color: #cbd5e1; margin-bottom: 4px;">Twitter Description (twitter:description)</label>
          <textarea id="wpbTwitterDescription" name="twitter_description" rows="2" placeholder="Defaults to OG Description" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" oninput="updateWpbSocialPreviews()"><?= htmlspecialchars($curTwitterDesc) ?></textarea>
        </div>

        <div>
          <label style="display: block; font-size: 0.84rem; color: #cbd5e1; margin-bottom: 4px;">Twitter Image URL (twitter:image)</label>
          <input type="text" id="wpbTwitterImage" name="twitter_image" value="<?= htmlspecialchars($curTwitterImage) ?>" placeholder="Defaults to Facebook Image" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" oninput="updateWpbSocialPreviews()" />
        </div>

      </div>

    </div>

    <!-- ===================================================================== -->
    <!-- TAB 5: ADVANCED (ROBOTS, CANONICAL, BREADCRUMBS) -->
    <!-- ===================================================================== -->
    <div id="wpb-sec-advanced" class="wpb-tab-section" style="display: none;">
      
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <div style="font-size: 0.88rem; font-weight: 700; color: #f8fafc; margin-bottom: 14px;">Robots Meta Directives</div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
          <div>
            <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 6px;">Allow search engines to index this article?</label>
            <select id="wpbRobotsIndex" name="robots_index" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;">
              <option value="1" <?= $curRobotsIndex == 1 ? 'selected' : '' ?>>Yes (index) &mdash; Default</option>
              <option value="0" <?= $curRobotsIndex == 0 ? 'selected' : '' ?>>No (noindex)</option>
            </select>
          </div>
          <div>
            <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 6px;">Should search engines follow links in this article?</label>
            <select id="wpbRobotsFollow" name="robots_follow" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;">
              <option value="1" <?= $curRobotsFollow == 1 ? 'selected' : '' ?>>Yes (follow) &mdash; Default</option>
              <option value="0" <?= $curRobotsFollow == 0 ? 'selected' : '' ?>>No (nofollow)</option>
            </select>
          </div>
        </div>

        <div>
          <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 6px;">Advanced Robots Meta</label>
          <input type="text" id="wpbAdvancedRobots" name="advanced_robots" value="<?= htmlspecialchars($curAdvancedRobots) ?>" placeholder="noimageindex, noarchive, nosnippet, max-snippet:-1, max-image-preview:large" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" />
          <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 4px;">Comma separated crawl instructions</div>
        </div>
      </div>

      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
        <div style="margin-bottom: 16px;">
          <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 4px;">Breadcrumbs Title Override</label>
          <input type="text" id="wpbBreadcrumbsTitle" name="breadcrumbs_title" value="<?= htmlspecialchars($curBreadcrumbTitle) ?>" placeholder="Defaults to Article Title" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" />
        </div>

        <div>
          <label style="display: block; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 4px;">Canonical URL Override</label>
          <input type="url" id="wpbCanonicalUrl" name="canonical_url" value="<?= htmlspecialchars($curCanonical) ?>" placeholder="https://wetruenorthgroup.com/blog/custom-canonical-url" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" />
          <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 4px;">Leave blank to default to https://wetruenorthgroup.com/blog/[slug]</div>
        </div>
      </div>

    </div>

  </div>

</div>

<script>
// ==============================================================================
// WORDPRESS BLOG SEO SUITE INTERACTIVE CONTROLLER
// ==============================================================================
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

  if (dev === 'mobile') {
    mobBtn.style.background = '#38bdf8';
    mobBtn.style.color = '#0f172a';
    dskBtn.style.background = 'transparent';
    dskBtn.style.color = '#94a3b8';
    container.style.maxWidth = '480px';
  } else {
    dskBtn.style.background = '#38bdf8';
    dskBtn.style.color = '#0f172a';
    mobBtn.style.background = 'transparent';
    mobBtn.style.color = '#94a3b8';
    container.style.maxWidth = '640px';
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

function runWpbSeoAnalysis() {
  const focus = (document.getElementById('wpbFocusKeyword').value || '').trim().toLowerCase();
  const rawTitle = (document.getElementById('postTitle') ? document.getElementById('postTitle').value : '').trim();
  const title = (document.getElementById('wpbTitle').value || rawTitle || '').trim();
  const rawExcerpt = (document.getElementById('postExcerpt') ? document.getElementById('postExcerpt').value : '').trim();
  const desc = (document.getElementById('wpbDesc').value || rawExcerpt || '').trim();
  const slug = (document.getElementById('postSlug') ? document.getElementById('postSlug').value : '').trim();
  const h1 = (document.getElementById('wpbH1Override').value || rawTitle || '').trim();
  const content = (document.getElementById('postContent') ? document.getElementById('postContent').value : '').trim();
  const cleanBody = content.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
  const words = cleanBody ? cleanBody.split(' ').filter(Boolean) : [];
  const wordCount = words.length;

  // SERP preview update
  document.getElementById('wpbserp-title-display').textContent = title || (rawTitle ? (rawTitle + ' — TrueNorth Packaging') : 'Article Title Preview — TrueNorth Packaging');
  document.getElementById('wpbserp-desc-display').textContent = desc || (rawExcerpt || 'Meta description snippet will render here dynamically as you write...');
  document.getElementById('wpbserp-url-display').textContent = 'https://wetruenorthgroup.com › blog › ' + (slug || '...');

  // Title progress bar
  const tLen = title.length;
  const tCounter = document.getElementById('wpbTitleCounter');
  const tBar = document.getElementById('wpbTitleBar');
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
  const dCounter = document.getElementById('wpbDescCounter');
  const dBar = document.getElementById('wpbDescBar');
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

  if (!focus) {
    problems.push('No focus keyphrase entered. Set a focus keyphrase to evaluate search ranking factors.');
  } else {
    good.push('Focus keyphrase set: "' + focus + '".');

    if (title.toLowerCase().includes(focus)) {
      good.push('Keyphrase in SEO title: Keyphrase occurs in the SEO title.');
    } else {
      problems.push('Keyphrase in title: The focus keyphrase does not appear in the SEO title.');
    }

    if (desc.toLowerCase().includes(focus)) {
      good.push('Keyphrase in meta description: Keyphrase appears in the meta description.');
    } else {
      improvements.push('Keyphrase in description: Keyphrase missing from meta description.');
    }

    if (slug.toLowerCase().includes(focus.replace(/\s+/g, '-'))) {
      good.push('Keyphrase in slug: Keyphrase appears in the URL slug.');
    } else {
      improvements.push('Keyphrase in slug: URL slug does not contain the keyphrase.');
    }

    if (h1.toLowerCase().includes(focus)) {
      good.push('Keyphrase in H1: Target keyphrase present in H1 heading.');
    } else {
      improvements.push('Keyphrase in H1: Target keyphrase not found in H1 heading.');
    }

    // Keyphrase density in article body
    if (wordCount > 0) {
      const occurrences = (cleanBody.toLowerCase().match(new RegExp('\\\\b' + focus + '\\\\b', 'g')) || []).length;
      const density = ((occurrences / wordCount) * 100).toFixed(1);
      if (occurrences >= 1 && density <= 3.0) {
        good.push('Keyphrase density: The focus keyphrase was found ' + occurrences + ' time(s) (' + density + '% density). This is great.');
      } else if (occurrences === 0) {
        problems.push('Keyphrase in text: The focus keyphrase was not found in the article body.');
      } else {
        improvements.push('Keyphrase density: Density is ' + density + '%, which is high. Aim for 1-2.5%.');
      }
    }
  }

  // Title width
  if (tLen >= 45 && tLen <= 65) {
    good.push('SEO title width: Optimal length (' + tLen + ' characters).');
  } else if (tLen > 65) {
    improvements.push('SEO title width: Longer than recommended 60 chars (' + tLen + ' chars).');
  } else {
    problems.push('SEO title width: Too short (' + tLen + ' chars). Target 50-60 characters.');
  }

  // Meta description length
  if (dLen >= 120 && dLen <= 165) {
    good.push('Meta description length: Optimal length (' + dLen + ' characters).');
  } else if (dLen > 165) {
    improvements.push('Meta description length: Over 160 characters (' + dLen + ' chars).');
  } else {
    problems.push('Meta description length: Too short (' + dLen + ' chars). Target 140-160 characters.');
  }

  // Text length
  if (wordCount >= 300) {
    good.push('Text length: The text contains ' + wordCount + ' words. Good for B2B authority.');
  } else {
    problems.push('Text length: The text contains ' + wordCount + ' words. Minimum 300 words recommended.');
  }

  // Image check
  const featuredImg = document.getElementById('featuredImgInput') ? document.getElementById('featuredImgInput').value : '';
  if (featuredImg) {
    good.push('Featured image: Cover image attached.');
  } else {
    improvements.push('Featured image: No cover image attached.');
  }

  // Render Checklists
  renderWpbChecklist('wpbListProblems', problems, '#f87171');
  renderWpbChecklist('wpbListImprovements', improvements, '#fbbf24');
  renderWpbChecklist('wpbListGood', good, '#4ade80');

  document.getElementById('wpbCountProblems').textContent = problems.length;
  document.getElementById('wpbCountImprovements').textContent = improvements.length;
  document.getElementById('wpbCountGood').textContent = good.length;

  // Calculate overall SEO score
  let score = 50;
  score += good.length * 8;
  score -= problems.length * 12;
  score -= improvements.length * 4;
  score = Math.max(10, Math.min(100, score));

  const scoreText = document.getElementById('wpb-seo-score-text');
  const scorePill = document.getElementById('wpb-seo-overall-pill');
  const dot = document.getElementById('wpb-seo-traffic-dot');
  const tabDot = document.getElementById('wpbtab-dot-seo');

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

  // Update Readability & Social
  runWpbReadabilityAnalysis(cleanBody, words);
  updateWpbSocialPreviews();
}

function renderWpbChecklist(containerId, items, color) {
  const container = document.getElementById(containerId);
  if (!container) return;
  if (items.length === 0) {
    container.innerHTML = '<div style="color: #64748b; font-style: italic;">None</div>';
    return;
  }
  container.innerHTML = items.map(txt => '<div style="display: flex; align-items: flex-start; gap: 8px; color: ' + color + '; line-height: 1.4;"><span style="font-weight: 700;">&bull;</span> <span>' + txt + '</span></div>').join('');
}

function runWpbReadabilityAnalysis(text, words) {
  const totalWords = words.length;
  document.getElementById('wpbArticleWordCount').textContent = totalWords + ' words';

  // Count sentences
  const sentences = text ? text.split(/[.!?]+/).map(s => s.trim()).filter(Boolean) : [];
  const sentenceCount = sentences.length || 1;
  const avgSentenceLength = (totalWords / sentenceCount).toFixed(1);

  // Approximate syllables per word
  let totalSyllables = 0;
  words.forEach(w => {
    let word = w.toLowerCase().replace(/[^a-z]/g, '');
    if (!word) return;
    if (word.length <= 3) { totalSyllables += 1; return; }
    word = word.replace(/(?:[^laeiouy]|ed|es|e)$/, '');
    word = word.replace(/^y/, '');
    const m = word.match(/[aeiouy]{1,2}/g);
    totalSyllables += m ? m.length : 1;
  });
  const avgSyllables = totalWords > 0 ? (totalSyllables / totalWords).toFixed(2) : '1.50';

  // Flesch Reading Ease Formula: 206.835 - 1.015 * (words/sentences) - 84.6 * (syllables/words)
  let flesch = 70.0;
  if (totalWords > 15) {
    flesch = (206.835 - (1.015 * (totalWords / sentenceCount)) - (84.6 * (totalSyllables / totalWords))).toFixed(1);
    flesch = Math.max(0, Math.min(100, flesch));
  }

  // Update metrics DOM
  document.getElementById('wpbStatSentences').textContent = sentenceCount;
  document.getElementById('wpbStatAvgSentence').textContent = avgSentenceLength + ' words';
  document.getElementById('wpbStatAvgSyllable').textContent = avgSyllables;
  document.getElementById('wpbStatReadingTime').textContent = Math.max(1, Math.ceil(totalWords / 200)) + ' min';

  const fleschDisplay = document.getElementById('wpbFleschScoreDisplay');
  const fleschBadge = document.getElementById('wpbFleschBadge');
  const rPill = document.getElementById('wpb-readability-pill');
  const rText = document.getElementById('wpb-readability-score-text');
  const rDot = document.getElementById('wpb-readability-dot');
  const tabDot = document.getElementById('wpbtab-dot-readability');

  let gradeLabel = 'Fairly Easy to Read';
  let gradeColor = '#38bdf8';
  let isGood = true;

  if (flesch >= 80) {
    gradeLabel = 'Easy to Read';
    gradeColor = '#4ade80';
  } else if (flesch >= 60) {
    gradeLabel = 'Standard Web Grade';
    gradeColor = '#38bdf8';
  } else if (flesch >= 50) {
    gradeLabel = 'Fairly Difficult';
    gradeColor = '#fbbf24';
    isGood = false;
  } else {
    gradeLabel = 'Difficult';
    gradeColor = '#f87171';
    isGood = false;
  }

  fleschDisplay.textContent = flesch + ' — ' + gradeLabel;
  fleschDisplay.style.color = gradeColor;
  fleschBadge.textContent = gradeLabel;
  fleschBadge.style.color = gradeColor;

  if (isGood) {
    rText.textContent = 'Good';
    rPill.style.color = '#4ade80';
    rPill.style.borderColor = 'rgba(34, 197, 94, 0.4)';
    rPill.style.background = 'rgba(34, 197, 94, 0.15)';
    rDot.style.background = '#22c55e';
    if (tabDot) tabDot.style.background = '#22c55e';
  } else {
    rText.textContent = 'OK';
    rPill.style.color = '#fbbf24';
    rPill.style.borderColor = 'rgba(245, 158, 11, 0.4)';
    rPill.style.background = 'rgba(245, 158, 11, 0.15)';
    rDot.style.background = '#f59e0b';
    if (tabDot) tabDot.style.background = '#f59e0b';
  }

  // Readability criteria items
  const rItems = [];
  const longSentences = sentences.filter(s => s.split(' ').length > 20);
  const longPct = Math.round((longSentences.length / sentenceCount) * 100);

  if (longPct <= 25) {
    rItems.push({ pass: true, text: 'Sentence length: Great! Only ' + longPct + '% of sentences contain more than 20 words.' });
  } else {
    rItems.push({ pass: false, text: 'Sentence length: ' + longPct + '% of sentences contain more than 20 words (try to keep under 25%).' });
  }

  // Transition words check
  const transitionWords = ['furthermore', 'however', 'in addition', 'moreover', 'therefore', 'consequently', 'similarly', 'because', 'as a result', 'firstly', 'finally', 'specifically', 'for example'];
  let transCount = 0;
  transitionWords.forEach(tw => {
    if (text.toLowerCase().includes(tw)) transCount++;
  });
  if (transCount >= 2 || totalWords < 50) {
    rItems.push({ pass: true, text: 'Transition words: Well balanced use of connective phrases for smooth reading flow.' });
  } else {
    rItems.push({ pass: false, text: 'Transition words: Few transition words found. Add words like "furthermore", "however", or "therefore".' });
  }

  // Subheadings check
  const rawHtml = document.getElementById('postContent') ? document.getElementById('postContent').value : '';
  const hasSubheadings = /<h[234]/i.test(rawHtml);
  if (totalWords < 250 || hasSubheadings) {
    rItems.push({ pass: true, text: 'Subheading distribution: Good job breaking content with structural subheadings.' });
  } else {
    rItems.push({ pass: false, text: 'Subheading distribution: Long content without H2/H3 subheadings. Add subheadings to improve scanability.' });
  }

  const rList = document.getElementById('wpbReadabilityChecklist');
  if (rList) {
    rList.innerHTML = rItems.map(item => {
      const col = item.pass ? '#4ade80' : '#fbbf24';
      const bg = item.pass ? '#22c55e' : '#f59e0b';
      return '<div style="display: flex; align-items: center; gap: 10px; color: ' + col + ';"><span style="width: 8px; height: 8px; border-radius: 50%; background: ' + bg + ';"></span><span>' + item.text + '</span></div>';
    }).join('');
  }
}

function updateWpbSocialPreviews() {
  const rawTitle = document.getElementById('postTitle') ? document.getElementById('postTitle').value : '';
  const rawDesc = document.getElementById('postExcerpt') ? document.getElementById('postExcerpt').value : '';
  const t = document.getElementById('wpbOgTitle').value || document.getElementById('wpbTitle').value || rawTitle || 'Article Title';
  const d = document.getElementById('wpbOgDescription').value || document.getElementById('wpbDesc').value || rawDesc || 'Description snippet...';
  const featuredImg = document.getElementById('featuredImgInput') ? document.getElementById('featuredImgInput').value : '';
  const img = document.getElementById('wpbOgImage').value || featuredImg || '/logo_svg.svg';

  const fbT = document.getElementById('wpbcard-fb-title');
  const fbD = document.getElementById('wpbcard-fb-desc');
  const fbImg = document.getElementById('wpbcard-fb-img');
  if (fbT) fbT.textContent = t;
  if (fbD) fbD.textContent = d;
  if (fbImg && img) fbImg.style.backgroundImage = 'url(\"' + img + '\")';

  const twT = document.getElementById('wpbcard-tw-title');
  const twD = document.getElementById('wpbcard-tw-desc');
  const twImg = document.getElementById('wpbcard-tw-img');
  const customTwT = document.getElementById('wpbTwitterTitle').value || t;
  const customTwD = document.getElementById('wpbTwitterDescription').value || d;
  const customTwImg = document.getElementById('wpbTwitterImage').value || img;
  if (twT) twT.textContent = customTwT;
  if (twD) twD.textContent = customTwD;
  if (twImg && customTwImg) twImg.style.backgroundImage = 'url(\"' + customTwImg + '\")';
}

function validateWpbJson() {
  const val = (document.getElementById('wpbCustomSchema').value || '').trim();
  const badge = document.getElementById('wpbJsonStatusBadge');
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

function addWpbFaqRow(q = '', a = '') {
  const emptyNotice = document.getElementById('wpbFaqEmptyNotice');
  if (emptyNotice) emptyNotice.remove();
  const c = document.getElementById('wpbFaqContainer');
  const row = document.createElement('div');
  row.style.background = '#0f172a';
  row.style.padding = '14px';
  row.style.borderRadius = '8px';
  row.style.border = '1px solid #334155';
  row.style.display = 'flex';
  row.style.flexDirection = 'column';
  row.style.gap = '8px';
  row.innerHTML = `
    <div style="display: flex; gap: 8px; align-items: center;">
      <input type="text" name="faq_questions[]" value="${q.replace(/"/g, '&quot;')}" placeholder="FAQ Question" style="flex: 1; padding: 8px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.85rem;" />
      <button type="button" onclick="this.closest('div').parentElement.remove()" style="padding: 7px 12px; background: #ef4444; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 0.76rem;">Delete</button>
    </div>
    <textarea name="faq_answers[]" rows="2" placeholder="FAQ Answer (renders in Google SERP accordion)" style="width: 100%; box-sizing: border-box; padding: 8px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.85rem;">${a}</textarea>
  `;
  c.appendChild(row);
}

// Hook into post changes
document.addEventListener('DOMContentLoaded', function() {
  const pTitle = document.getElementById('postTitle');
  const pSlug = document.getElementById('postSlug');
  const pExcerpt = document.getElementById('postExcerpt');
  const pContent = document.getElementById('postContent');
  const pImg = document.getElementById('featuredImgInput');

  if (pTitle) pTitle.addEventListener('input', runWpbSeoAnalysis);
  if (pSlug) pSlug.addEventListener('input', runWpbSeoAnalysis);
  if (pExcerpt) pExcerpt.addEventListener('input', runWpbSeoAnalysis);
  if (pContent) pContent.addEventListener('input', runWpbSeoAnalysis);
  if (pImg) pImg.addEventListener('input', runWpbSeoAnalysis);

  runWpbSeoAnalysis();
  validateWpbJson();
});
</script>

