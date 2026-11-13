<?php
require_once __DIR__ . '/auth.php';
checkAdminAuth();

$currentUser = $_SESSION['admin_user'] ?? 'Admin';
$currentRole = $_SESSION['admin_role'] ?? 'Super Admin';
$csrfToken = generateCsrfToken();

// Breadcrumb generator
$reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$pathParts = explode('/', trim($reqPath, '/'));
$lastPart = end($pathParts);
$currentPage = preg_replace('/\.php$/i', '', $lastPart);
if (empty($currentPage) || $currentPage === 'admin') {
    $currentPage = 'index';
}
$breadcrumbs = [
    'index' => 'Dashboard Overview',
    'products' => 'Product Catalogue',
    'add_product' => 'Add New Product',
    'edit_product' => 'Edit Product',
    'categories' => 'Category Management',
    'inventory' => 'Inventory & Price Manager',
    'media' => 'Media Library',
    'settings' => 'Website Settings',
    'users' => 'Admin Users',
    'change_password' => 'Change Password',
    'inquiries' => 'Client Inquiries',
    'blog' => 'Blog & SEO Articles',
    'blog_editor' => 'Blog & SEO Editor',
    'seo_redirects' => 'Redirect Manager',
    'seo_404' => '404 Error Monitor'
];
$crumbTitle = $breadcrumbs[$currentPage] ?? ucfirst(str_replace('_', ' ', $currentPage));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0" />
  <title><?= htmlspecialchars($crumbTitle) ?> — TrueNorth Group CMS</title>
  <link rel="icon" type="image/svg+xml" href="/logo_svg.svg" />
  <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>" />
  <script>
  // Attach the CSRF token to every admin form submit, same-origin fetch() and XHR,
  // and to state-changing admin links (delete/toggle/dismiss/regenerate).
  (function () {
    var token = <?= json_encode($csrfToken, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    window.TN_CSRF = token;
    function sameOrigin(url) {
      try { return new URL(url, location.href).origin === location.origin; } catch (e) { return false; }
    }
    function withToken(url) {
      var u = new URL(url, location.href);
      if (!u.searchParams.has('csrf_token')) u.searchParams.set('csrf_token', token);
      return u.pathname + u.search + u.hash;
    }
    function needsToken(url) {
      try {
        var u = new URL(url, location.href);
        if (u.origin !== location.origin || u.pathname.indexOf('/admin') !== 0) return false;
        if (/\/admin\/delete_/.test(u.pathname)) return true;
        return ['delete', 'toggle', 'dismiss', 'dismiss_404', 'regenerate_sitemap', 'permanent', 'restore', 'archive'].indexOf(u.searchParams.get('action')) !== -1;
      } catch (e) { return false; }
    }
    window.tnCsrfUrl = function (url) { return needsToken(url) ? withToken(url) : url; };
    // After a one-shot action link runs, drop the token/action from the address bar
    // (keeps it out of history and stops a refresh from repeating the action)
    try {
      var cur = new URL(location.href);
      if (cur.searchParams.has('csrf_token')) {
        ['csrf_token', 'action', 'user', 'id'].forEach(function (k) { cur.searchParams.delete(k); });
        history.replaceState(null, '', cur.pathname + (cur.searchParams.toString() ? '?' + cur.searchParams.toString() : '') + cur.hash);
      }
    } catch (e) {}
    function addTokenToForm(f) {
      if (!f || (f.getAttribute('method') || '').toLowerCase() !== 'post' || !sameOrigin(f.action || location.href)) return;
      if (!f.querySelector('input[name="csrf_token"]')) {
        var i = document.createElement('input');
        i.type = 'hidden'; i.name = 'csrf_token'; i.value = token;
        f.appendChild(i);
      }
    }
    document.addEventListener('submit', function (e) { addTokenToForm(e.target); }, true);
    var origSubmit = HTMLFormElement.prototype.submit;
    HTMLFormElement.prototype.submit = function () { addTokenToForm(this); return origSubmit.call(this); };
    document.addEventListener('DOMContentLoaded', function () {
      Array.prototype.forEach.call(document.querySelectorAll('form'), addTokenToForm);
    });
    document.addEventListener('click', function (e) {
      var a = e.target && e.target.closest ? e.target.closest('a[href]') : null;
      if (a && needsToken(a.href)) a.href = withToken(a.href);
    }, true);
    var origFetch = window.fetch;
    if (origFetch) {
      window.fetch = function (input, init) {
        var url = typeof input === 'string' ? input : (input && input.url) || '';
        if (sameOrigin(url)) {
          init = init || {};
          var h = new Headers(init.headers || (typeof input !== 'string' && input.headers) || {});
          h.set('X-CSRF-Token', token);
          init.headers = h;
        }
        return origFetch.call(this, input, init);
      };
    }
    var origOpen = XMLHttpRequest.prototype.open, origSend = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.open = function (m, url) { this._tnSame = sameOrigin(url); return origOpen.apply(this, arguments); };
    XMLHttpRequest.prototype.send = function () { if (this._tnSame) { try { this.setRequestHeader('X-CSRF-Token', token); } catch (e) {} } return origSend.apply(this, arguments); };
  })();
  </script>
  <link rel="stylesheet" href="/styles.css?v=1.0.8" />
  <style>
    :root {
      --cms-bg: #0f172a;
      --cms-sidebar-bg: #1e293b;
      --cms-card-bg: #1e293b;
      --cms-border: #334155;
      --cms-text: #f8fafc;
      --cms-muted: #94a3b8;
      --cms-accent: #0284c7;
    }
    *, *::before, *::after {
      box-sizing: border-box;
    }
    html, body {
      background: var(--cms-bg);
      color: var(--cms-text);
      font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      margin: 0;
      padding: 0;
      min-height: 100vh;
      max-width: 100vw;
      overflow-x: hidden;
    }
    body {
      display: flex;
    }
    
    /* SIDEBAR STYLING */
    .cms-sidebar {
      width: 260px;
      background: var(--cms-sidebar-bg);
      border-right: 1px solid var(--cms-border);
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
      z-index: 1000;
    }
    .cms-sidebar-brand {
      padding: 20px;
      border-bottom: 1px solid var(--cms-border);
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      color: #ffffff;
      flex: 1;
    }
    .cms-sidebar-brand img {
      height: 34px;
      filter: brightness(0) invert(1);
    }
    .cms-sidebar-brand span {
      font-size: 1.05rem;
      font-weight: 800;
      letter-spacing: 0.5px;
    }
    .cms-sidebar-header-mobile {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid var(--cms-border);
    }
    .cms-sidebar-close {
      display: none;
      background: transparent;
      border: none;
      color: #94a3b8;
      font-size: 1.8rem;
      width: 44px;
      height: 44px;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      border-radius: 8px;
      margin-right: 8px;
    }
    .cms-sidebar-close:hover, .cms-sidebar-close:active {
      color: #ffffff;
      background: rgba(255, 255, 255, 0.1);
    }
    .cms-nav {
      padding: 16px 12px;
      flex: 1;
      overflow-y: auto;
      -webkit-overflow-scrolling: touch;
    }
    .cms-nav-section {
      font-size: 0.72rem;
      font-weight: 700;
      color: var(--cms-muted);
      text-transform: uppercase;
      letter-spacing: 0.8px;
      padding: 12px 12px 6px 12px;
      margin-top: 10px;
    }
    .cms-nav-link {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 14px;
      color: #cbd5e1;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.9rem;
      border-radius: 8px;
      margin-bottom: 4px;
      min-height: 44px;
      transition: all 0.2s;
    }
    .cms-nav-link:hover, .cms-nav-link.active {
      background: #334155;
      color: #ffffff;
    }
    .cms-nav-link svg {
      width: 20px;
      height: 20px;
      stroke-width: 2;
    }

    /* BACKDROP OVERLAY FOR MOBILE */
    .cms-mobile-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.8);
      backdrop-filter: blur(4px);
      z-index: 990;
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.25s ease, visibility 0.25s ease;
    }

    /* MAIN CONTENT WRAPPER */
    .cms-main-wrapper {
      margin-left: 260px;
      flex: 1;
      display: flex;
      flex-direction: column;
      min-width: 0;
      width: calc(100% - 260px);
    }

    /* MOBILE HEADER BAR */
    .cms-mobile-bar {
      display: none;
    }

    /* TOP HEADER BAR (DESKTOP) */
    .cms-desktop-header {
      background: var(--cms-sidebar-bg);
      border-bottom: 1px solid var(--cms-border);
      padding: 16px 32px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 90;
    }
    .cms-breadcrumbs {
      font-size: 0.85rem;
      color: var(--cms-muted);
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .cms-breadcrumbs a {
      color: #38bdf8;
      text-decoration: none;
    }
    .cms-user-profile {
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .cms-role-badge {
      background: rgba(14, 165, 233, 0.15);
      color: #38bdf8;
      border: 1px solid rgba(14, 165, 233, 0.3);
      padding: 4px 10px;
      border-radius: 6px;
      font-size: 0.72rem;
      font-weight: 700;
    }
    .btn-logout-cms {
      color: #ef4444;
      text-decoration: none;
      font-size: 0.85rem;
      font-weight: 600;
      border: 1px solid rgba(239, 68, 68, 0.4);
      padding: 8px 14px;
      border-radius: 6px;
      min-height: 44px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }
    .btn-logout-cms:hover {
      background: rgba(239, 68, 68, 0.15);
    }
    .cms-content {
      padding: 32px;
      flex: 1;
      width: 100%;
    }

    /* RESPONSIVE MOBILE BREAKPOINTS (< 992px) */
    @media (max-width: 991px) {
      .cms-desktop-header {
        display: none !important;
      }
      .cms-mobile-bar {
        display: flex !important;
        align-items: center;
        justify-content: space-between;
        background: var(--cms-sidebar-bg);
        border-bottom: 1px solid var(--cms-border);
        padding: 8px 14px;
        position: sticky;
        top: 0;
        z-index: 800;
        height: 56px;
        width: 100%;
        box-sizing: border-box;
      }
      .cms-hamburger {
        background: transparent;
        border: none;
        color: #f8fafc;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border-radius: 8px;
        padding: 0;
      }
      .cms-hamburger:active {
        background: rgba(255, 255, 255, 0.1);
      }
      .cms-mobile-brand {
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        color: #ffffff;
        font-weight: 800;
        font-size: 0.95rem;
      }
      .cms-mobile-brand img {
        height: 26px;
        filter: brightness(0) invert(1);
      }
      .cms-mobile-user {
        display: flex;
        align-items: center;
        gap: 8px;
      }
      .cms-role-badge-sm {
        background: rgba(14, 165, 233, 0.15);
        color: #38bdf8;
        border: 1px solid rgba(14, 165, 233, 0.3);
        padding: 3px 6px;
        border-radius: 4px;
        font-size: 0.68rem;
        font-weight: 700;
      }
      .btn-logout-icon {
        color: #ef4444;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 8px;
        border: 1px solid rgba(239, 68, 68, 0.4);
        background: rgba(239, 68, 68, 0.1);
        text-decoration: none;
      }

      /* MOBILE DRAWER SIDEBAR */
      .cms-sidebar {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        bottom: 0 !important;
        width: 280px !important;
        max-width: 85vw !important;
        z-index: 1000 !important;
        transform: translateX(-100%) !important;
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 4px 0 24px rgba(0, 0, 0, 0.5) !important;
      }
      .cms-sidebar.cms-drawer-open {
        transform: translateX(0) !important;
      }
      .cms-sidebar-close {
        display: flex !important;
      }
      .cms-mobile-overlay {
        display: block !important;
      }
      .cms-mobile-overlay.cms-overlay-open {
        opacity: 1 !important;
        visibility: visible !important;
      }

      /* WRAPPER & CONTENT RESETS */
      .cms-main-wrapper {
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100vw !important;
        overflow-x: hidden !important;
      }
      .cms-content {
        padding: 16px 12px !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
      }

      /* UNIVERSAL CARD & CONTAINER OVERRIDES */
      .smart-form-container, .admin-options-container {
        max-width: 100% !important;
        width: 100% !important;
      }
      .form-card, .section-card, .filter-card, .table-container, .stat-card {
        padding: 16px 12px !important;
        border-radius: 12px !important;
        margin-bottom: 16px !important;
        width: 100% !important;
      }

      /* MULTI-COLUMN GRIDS -> SINGLE COLUMN */
      .form-row, .form-row-2, .form-row-3 {
        grid-template-columns: 1fr !important;
        gap: 14px !important;
      }

      /* HEADERS & TOOLBARS */
      .dash-header, .panel-header, .card-header-smart {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 12px !important;
      }
      .dash-header h1 {
        font-size: 1.4rem !important;
      }
      .dash-header p {
        font-size: 0.82rem !important;
      }
      .dash-header a, .dash-header button {
        width: 100% !important;
        justify-content: center !important;
        box-sizing: border-box !important;
      }

      /* TOUCH TARGET ENHANCEMENT (MIN 44-52px) */
      input[type="text"], input[type="number"], input[type="email"], input[type="password"],
      select, textarea, .form-control, .form-control-smart, .filter-input, .filter-select {
        font-size: 16px !important; /* Prevents auto-zoom on iOS */
        min-height: 48px !important;
        padding: 12px 14px !important;
        width: 100% !important;
        border-radius: 8px !important;
        box-sizing: border-box !important;
      }

      /* CHIP SELECTION GROUPS */
      .chip-group {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 8px !important;
      }
      .select-chip {
        min-height: 44px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 8px 14px !important;
        font-size: 0.85rem !important;
        border-radius: 8px !important;
      }

      /* MEDIA GALLERY GRID */
      .media-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px !important;
      }

      /* MODALS */
      .modal-content, .modal-dialog {
        width: calc(100% - 20px) !important;
        max-width: 100% !important;
        margin: 10px auto !important;
        padding: 16px !important;
        box-sizing: border-box !important;
      }

      /* STATS GRID */
      .stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px !important;
      }

      /* INLINE FLEX/GRID STRUCTS IN USERS, SETTINGS, INDEX */
      div[style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
        gap: 16px !important;
      }
      div[style*="max-width: 650px"], div[style*="max-width: 500px"] {
        max-width: 100% !important;
        padding: 20px 16px !important;
      }

      /* TABLES SCROLL WRAPPER */
      .table-container {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
      }
    }

    @media (max-width: 420px) {
      .media-grid {
        grid-template-columns: 1fr !important;
      }
      .stats-grid {
        grid-template-columns: 1fr !important;
      }
    }
  </style>
</head>
<body>

  <!-- MOBILE OVERLAY BACKDROP -->
  <div class="cms-mobile-overlay" id="cmsMobileOverlay"></div>

  <!-- SIDEBAR NAVIGATION (SLIDE-IN ON MOBILE) -->
  <aside class="cms-sidebar" id="cmsSidebar">
    <div class="cms-sidebar-header-mobile">
      <a href="/admin" class="cms-sidebar-brand">
        <img src="/logo_svg.svg" alt="TrueNorth Group Logo" />
        <span>TrueNorth CMS</span>
      </a>
      <button class="cms-sidebar-close" id="cmsSidebarClose" aria-label="Close Navigation">&times;</button>
    </div>

    <nav class="cms-nav">
      <div class="cms-nav-section">Main Menu</div>
      <a href="/admin" class="cms-nav-link <?= $currentPage == 'index' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Dashboard
      </a>
      <a href="/admin/inquiries" class="cms-nav-link <?= $currentPage == 'inquiries' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        Client Inquiries
        <?php
          if (file_exists(dirname(__DIR__) . '/app/Models/InquiryModel.php')) {
              require_once dirname(__DIR__) . '/app/Models/InquiryModel.php';
              $inqStats = InquiryModel::getStats();
              if (($inqStats['new'] ?? 0) > 0) {
                  echo '<span style="background: #0284c7; color: #fff; font-size: 0.72rem; font-weight: 800; padding: 2px 7px; border-radius: 999px; margin-left: auto;">' . $inqStats['new'] . '</span>';
              }
          }
        ?>
      </a>

      <div class="cms-nav-section">Catalogue & Products</div>
      <a href="/admin/products" class="cms-nav-link <?= in_array($currentPage, ['products', 'edit_product']) ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
        Products List
      </a>
      <a href="/admin/add_product" class="cms-nav-link <?= $currentPage == 'add_product' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        Add New Product
      </a>
      <a href="/admin/categories" class="cms-nav-link <?= $currentPage == 'categories' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        Categories
      </a>
      <a href="/admin/inventory" class="cms-nav-link <?= $currentPage == 'inventory' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20 7h-9M14 17H5M17 12H3"/></svg>
        Inventory & Prices
      </a>

      <div class="cms-nav-section">Content &amp; SEO</div>
      <a href="/admin/blog" class="cms-nav-link <?= in_array($currentPage, ['blog', 'blog_editor']) ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="10" x2="16" y2="10"/><line x1="8" y1="14" x2="12" y2="14"/></svg>
        Blog &amp; SEO Articles
      </a>

      <a href="/admin/testimonials" class="cms-nav-link <?= in_array($currentPage, ['testimonials', 'testimonial_editor']) ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 3v18h18"/><path d="M7 16l4-4 3 3 5-6"/><path d="M7 9h4"/></svg>
        Testimonials &amp; Client Logos
      </a>

      <div class="cms-nav-section">Technical SEO</div>
      <a href="/admin/seo" class="cms-nav-link <?= $currentPage == 'seo' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><path d="m8 11 2 2 4-4"/></svg>
        SEO Overview &amp; Audit
      </a>
      <a href="/admin/seo_pages" class="cms-nav-link <?= $currentPage == 'seo_pages' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        Page SEO Manager
      </a>
      <a href="/admin/seo_bulk" class="cms-nav-link <?= $currentPage == 'seo_bulk' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        Bulk SEO Editor
      </a>
      <a href="/admin/seo_templates" class="cms-nav-link <?= $currentPage == 'seo_templates' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
        Meta Templates
      </a>
      <a href="/admin/seo_redirects" class="cms-nav-link <?= $currentPage == 'seo_redirects' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
        Redirect Manager
      </a>
      <a href="/admin/seo_404" class="cms-nav-link <?= $currentPage == 'seo_404' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        404 Monitor
      </a>
      <a href="/admin/seo_sitemap" class="cms-nav-link <?= $currentPage == 'seo_sitemap' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M10 6.5h4"/><path d="M6.5 10v4"/><path d="M17.5 10v4"/><path d="M10 17.5h4"/></svg>
        Sitemap, Robots &amp; Schema
      </a>
      <a href="/admin/seo_images" class="cms-nav-link <?= $currentPage == 'seo_images' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        Image Alt Tool
      </a>
      <a href="/admin/seo_links" class="cms-nav-link <?= $currentPage == 'seo_links' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
        Internal Links
      </a>
      <a href="/admin/seo_breadcrumbs" class="cms-nav-link <?= $currentPage == 'seo_breadcrumbs' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="9 6 15 12 9 18"/><polyline points="15 6 21 12 15 18"/></svg>
        Breadcrumbs
      </a>
      <a href="/admin/seo_settings" class="cms-nav-link <?= $currentPage == 'seo_settings' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        Global SEO Settings
      </a>

      <div class="cms-nav-section">Media &amp; Settings</div>
      <a href="/admin/media" class="cms-nav-link <?= $currentPage == 'media' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        Media Library
      </a>
      <a href="/admin/settings" class="cms-nav-link <?= $currentPage == 'settings' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        Website Settings
      </a>
      <a href="/admin/users" class="cms-nav-link <?= $currentPage == 'users' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Admin Users
      </a>
      <a href="/admin/change_password" class="cms-nav-link <?= $currentPage == 'change_password' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        Change Password
      </a>

      <div style="margin-top: 20px; padding: 0 12px;">
        <a href="/" target="_blank" style="display: flex; align-items: center; justify-content: center; width: 100%; box-sizing: border-box; background: #334155; color: #fff; text-decoration: none; padding: 12px; border-radius: 8px; font-weight: 700; font-size: 0.88rem; min-height: 44px;">View Live Site &nearr;</a>
      </div>
    </nav>
  </aside>

  <!-- MAIN WRAPPER -->
  <div class="cms-main-wrapper">
    
    <!-- MOBILE HEADER BAR (< 992px) -->
    <header class="cms-mobile-bar">
      <button class="cms-hamburger" id="cmsHamburger" aria-label="Open Navigation Menu">
        <svg viewBox="0 0 24 24" width="26" height="26" stroke="currentColor" stroke-width="2.2" fill="none">
          <line x1="3" y1="6" x2="21" y2="6"/>
          <line x1="3" y1="12" x2="21" y2="12"/>
          <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>

      <a href="/admin" class="cms-mobile-brand">
        <img src="/logo_svg.svg" alt="TrueNorth Group" />
        <span>TrueNorth CMS</span>
      </a>

      <div class="cms-mobile-user">
        <span class="cms-role-badge-sm"><?= htmlspecialchars($currentRole) ?></span>
        <a href="/admin/logout" class="btn-logout-icon" title="Logout" onclick="event.preventDefault(); document.getElementById('tnLogoutForm').submit();">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </a>
      </div>
    </header>

    <!-- DESKTOP TOP HEADER (>= 992px) -->
    <header class="cms-header cms-desktop-header">
      <div class="cms-breadcrumbs">
        <a href="/admin">Admin</a>
        <span>/</span>
        <strong><?= htmlspecialchars($crumbTitle) ?></strong>
      </div>

      <div class="cms-user-profile">
        <span class="cms-role-badge"><?= htmlspecialchars($currentRole) ?></span>
        <span style="font-size: 0.88rem; color: var(--cms-muted);">Hello, <strong><?= htmlspecialchars($currentUser) ?></strong></span>
        <a href="/admin/logout" class="btn-logout-cms" onclick="event.preventDefault(); document.getElementById('tnLogoutForm').submit();">Logout</a>
        <form id="tnLogoutForm" method="post" action="/admin/logout" style="display:none;"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" /></form>
      </div>
    </header>

    <div class="cms-content">

