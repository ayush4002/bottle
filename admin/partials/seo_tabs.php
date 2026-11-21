<?php
// ==============================================================================
// UNIFIED SEO HUB — shared WordPress-style tab bar + light "wp-admin" skin.
// One "SEO" item in the sidebar; these tabs switch between the tools.
// The <style> below reskins the (otherwise dark) SEO screens to a clean
// light WordPress/Yoast-like look. It is original CSS — it only remaps this
// app's own colour palette; no plugin code or assets are used.
// Scoped to body.tnwp-skin (set by admin/header.php on SEO pages).
// ==============================================================================
$tnSeoCurrent = $currentPage ?? '';
$tnSeoTabs = [
    ['seo',             'Dashboard'],
    ['seo_settings',    'Search Appearance'],
    ['seo_pages',       'Titles & Meta'],
    ['seo_bulk',        'Bulk Editor'],
    ['seo_templates',   'Templates'],
    ['seo_links',       'Internal Links'],
    ['seo_breadcrumbs', 'Breadcrumbs'],
    ['seo_redirects',   'Redirects'],
    ['seo_404',         '404 Monitor'],
    ['seo_sitemap',     'Sitemap & Schema'],
    ['seo_images',      'Image SEO'],
];
$tnSeoLabels = [
    'seo' => 'Dashboard', 'seo_settings' => 'Search Appearance', 'seo_pages' => 'Titles & Meta',
    'seo_bulk' => 'Bulk Editor', 'seo_templates' => 'Templates', 'seo_links' => 'Internal Links',
    'seo_breadcrumbs' => 'Breadcrumbs', 'seo_redirects' => 'Redirects', 'seo_404' => '404 Monitor',
    'seo_sitemap' => 'Sitemap & Schema', 'seo_images' => 'Image SEO',
];
$tnSeoHeading = $tnSeoLabels[$tnSeoCurrent] ?? 'SEO';
?>
<style>
/* ===================== LIGHT WORDPRESS-STYLE SEO SKIN ===================== */
body.tnwp-skin .cms-content{background:#f0f0f1 !important;}
body.tnwp-skin .cms-content a{color:#2271b1;}

/* Page heading strip (wp-admin style) */
.tnwp-head{display:flex;align-items:center;gap:10px;margin:4px 0 4px;}
.tnwp-head h1{font-size:1.5rem !important;font-weight:600 !important;color:#1d2327 !important;margin:0 !important;}
.tnwp-sub{color:#646970;font-size:0.86rem;margin:0 0 16px;}

/* WordPress nav-tab bar */
.tnwp-tabs{display:flex;gap:2px;overflow-x:auto;border-bottom:1px solid #c3c4c7;margin:0 0 22px;padding:0;}
.tnwp-tabs::-webkit-scrollbar{height:6px;}
.tnwp-tabs::-webkit-scrollbar-thumb{background:#c3c4c7;border-radius:3px;}
.tnwp-tab{display:inline-flex;align-items:center;white-space:nowrap;padding:10px 14px;font-size:0.86rem;font-weight:500;color:#50575e;text-decoration:none;border:1px solid transparent;border-bottom:none;margin-bottom:-1px;border-radius:4px 4px 0 0;flex-shrink:0;}
.tnwp-tab:hover{color:#1d2327;background:#f6f7f7;}
.tnwp-tab.active{background:#f0f0f1;color:#1d2327;font-weight:600;border-color:#c3c4c7;border-bottom:1px solid #f0f0f1;}

/* --- surfaces: cards & panels become white --- */
body.tnwp-skin [style*="background: #1e293b"],
body.tnwp-skin [style*="background:#1e293b"],
body.tnwp-skin [style*="background: #0f172a"],
body.tnwp-skin [style*="background:#0f172a"]{background-color:#fff !important;}
body.tnwp-skin [style*="background: #162032"],
body.tnwp-skin [style*="background:#162032"],
body.tnwp-skin [style*="background: #0b1120"],
body.tnwp-skin [style*="background:#0b1120"],
body.tnwp-skin [style*="background: #162a"],
body.tnwp-skin [style*="background: #233"]{background-color:#f6f7f7 !important;}

/* --- borders --- */
body.tnwp-skin [style*="solid #334155"]{border-color:#dcdcde !important;}
body.tnwp-skin [style*="solid #475569"]{border-color:#8c8f94 !important;}
body.tnwp-skin [style*="solid #1e293b"]{border-color:#dcdcde !important;}
body.tnwp-skin [style*="bottom: 1px solid #334155"],
body.tnwp-skin [style*="top: 1px solid #334155"]{border-color:#dcdcde !important;}

/* --- text colours --- */
body.tnwp-skin [style*="color: #f8fafc"],
body.tnwp-skin [style*="color:#f8fafc"],
body.tnwp-skin [style*="color: #f1f5f9"],
body.tnwp-skin [style*="color:#f1f5f9"],
body.tnwp-skin [style*="color: #e2e8f0"],
body.tnwp-skin [style*="color:#e2e8f0"]{color:#1d2327 !important;}
body.tnwp-skin [style*="color: #cbd5e1"],
body.tnwp-skin [style*="color:#cbd5e1"]{color:#3c434a !important;}
body.tnwp-skin [style*="color: #94a3b8"],
body.tnwp-skin [style*="color:#94a3b8"]{color:#646970 !important;}
body.tnwp-skin [style*="color: #64748b"],
body.tnwp-skin [style*="color:#64748b"]{color:#787c82 !important;}
body.tnwp-skin [style*="color: #38bdf8"],
body.tnwp-skin [style*="color:#38bdf8"]{color:#2271b1 !important;}
/* pastel alert text on light tints -> readable */
body.tnwp-skin [style*="color: #6ee7b7"],
body.tnwp-skin [style*="color:#6ee7b7"]{color:#0a6b2e !important;}
body.tnwp-skin [style*="color: #fca5a5"],
body.tnwp-skin [style*="color:#fca5a5"]{color:#b32d2e !important;}

/* --- form fields: always white with dark text (fixes dark-input inline styles) --- */
body.tnwp-skin input:not([type=checkbox]):not([type=radio]):not([type=submit]):not([type=button]),
body.tnwp-skin select,
body.tnwp-skin textarea{background:#fff !important;color:#1d2327 !important;border:1px solid #8c8f94 !important;border-radius:4px !important;}
body.tnwp-skin input::placeholder,
body.tnwp-skin textarea::placeholder{color:#8c8f94 !important;}
body.tnwp-skin input:focus,body.tnwp-skin select:focus,body.tnwp-skin textarea:focus{border-color:#2271b1 !important;box-shadow:0 0 0 1px #2271b1 !important;outline:2px solid transparent !important;}

/* --- buttons --- */
body.tnwp-skin .tn-btn-gold{background:#2271b1 !important;border:1px solid #2271b1 !important;color:#fff !important;box-shadow:none !important;border-radius:4px !important;}
body.tnwp-skin .tn-btn-gold:hover{background:#135e96 !important;border-color:#135e96 !important;}
body.tnwp-skin .tn-btn-outline{background:#f6f7f7 !important;border:1px solid #2271b1 !important;color:#2271b1 !important;border-radius:4px !important;}
body.tnwp-skin .tn-btn-outline:hover{background:#f0f0f1 !important;}
body.tnwp-skin button[style*="background: #334155"],
body.tnwp-skin button[style*="background:#334155"]{background:#f6f7f7 !important;color:#1d2327 !important;border:1px solid #dcdcde !important;}

/* --- tables --- */
body.tnwp-skin table thead tr,
body.tnwp-skin table th[style*="background: #0f172a"],
body.tnwp-skin table tr[style*="background: #0f172a"]{background:#f6f7f7 !important;}
body.tnwp-skin table td,body.tnwp-skin table th{border-color:#dcdcde !important;}

/* --- code / monospace blocks --- */
body.tnwp-skin code,body.tnwp-skin pre{background:#f6f7f7 !important;color:#1d2327 !important;}

/* --- scrollbars inside skinned area --- */
body.tnwp-skin .cms-content ::-webkit-scrollbar-thumb{background:#c3c4c7;}
</style>

<div class="tnwp-head">
  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2271b1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><path d="m8 11 2 2 4-4"/></svg>
  <h1>SEO <span style="color:#646970;font-weight:400;">— <?= htmlspecialchars($tnSeoHeading) ?></span></h1>
</div>
<nav class="tnwp-tabs" aria-label="SEO sections">
  <?php foreach ($tnSeoTabs as $t):
      list($page, $label) = $t;
      $active = ($tnSeoCurrent === $page) ? 'active' : '';
  ?>
    <a href="/admin/<?= $page ?>" class="tnwp-tab <?= $active ?>"><?= htmlspecialchars($label) ?></a>
  <?php endforeach; ?>
</nav>
