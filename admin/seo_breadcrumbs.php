<?php
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/SeoModel.php';

checkAdminAuth();
SeoModel::init();

$msg = '';
$err = '';
$adminUser = $_SESSION['admin_user'] ?? 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_breadcrumbs') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $err = "Invalid security token. Please refresh and try again.";
    } else {
        $sep = trim($_POST['breadcrumbs_separator'] ?? '>');
        if ($sep === '') $sep = '>';
        $home = trim($_POST['breadcrumbs_home_label'] ?? 'Home');
        if ($home === '') $home = 'Home';
        SeoModel::saveSettings([
            'breadcrumbs_enabled'    => isset($_POST['breadcrumbs_enabled']) ? '1' : '0',
            'breadcrumbs_show_home'  => isset($_POST['breadcrumbs_show_home']) ? '1' : '0',
            'breadcrumbs_separator'  => mb_substr($sep, 0, 8),
            'breadcrumbs_home_label' => mb_substr($home, 0, 40),
        ], $adminUser);
        $msg = "Breadcrumb settings saved. They apply across the whole site immediately.";
    }
}

$bc = SeoModel::breadcrumbConfig();
$csrfToken = generateCsrfToken();
$sepList = ['>', '/', '»', '›', '•', '-', '|'];
?>

<div style="max-width: 820px; margin: 0 auto;">
  <div style="margin-bottom: 22px;">
    <h1 style="font-size: 1.7rem; font-weight: 800; color: #f8fafc; margin: 0 0 6px 0;">Breadcrumbs</h1>
    <p style="color: #94a3b8; font-size: 0.9rem; margin: 0;">Control the breadcrumb trail shown on product, blog and inner pages. The breadcrumb structured data (BreadcrumbList schema) is generated automatically and uses the Home label you set here.</p>
  </div>

  <?php if ($msg): ?>
    <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 12px 16px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 20px;"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>
  <?php if ($err): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 12px 16px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 20px;"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <form method="POST" action="/admin/seo_breadcrumbs">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
    <input type="hidden" name="action" value="save_breadcrumbs" />

    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px; margin-bottom: 20px;">

      <!-- Live preview -->
      <div style="margin-bottom: 22px;">
        <div style="font-size: 0.78rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Live preview</div>
        <div id="bc-preview" style="background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 14px 16px; color: #cbd5e1; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;"></div>
      </div>

      <label style="display: flex; align-items: center; gap: 12px; margin-bottom: 18px; cursor: pointer;">
        <input type="checkbox" name="breadcrumbs_enabled" id="bc_enabled" value="1" <?= $bc['enabled'] ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: #0284c7;" />
        <span>
          <span style="display: block; font-weight: 700; color: #f1f5f9;">Show breadcrumbs on the site</span>
          <span style="display: block; font-size: 0.82rem; color: #94a3b8;">Turn the visible breadcrumb trail on or off. The schema (for Google) stays on regardless.</span>
        </span>
      </label>

      <label style="display: flex; align-items: center; gap: 12px; margin-bottom: 22px; cursor: pointer;">
        <input type="checkbox" name="breadcrumbs_show_home" id="bc_home" value="1" <?= $bc['show_home'] ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: #0284c7;" />
        <span>
          <span style="display: block; font-weight: 700; color: #f1f5f9;">Start the trail with a Home link</span>
          <span style="display: block; font-size: 0.82rem; color: #94a3b8;">Prefix every breadcrumb trail with a link back to the homepage.</span>
        </span>
      </label>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px;">
        <div>
          <label for="bc_sep" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 8px;">Separator between items</label>
          <input type="text" name="breadcrumbs_separator" id="bc_sep" value="<?= htmlspecialchars($bc['separator']) ?>" maxlength="8" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.95rem;" />
          <div style="margin-top: 8px; display: flex; gap: 6px; flex-wrap: wrap;">
            <?php foreach ($sepList as $sp): ?>
              <button type="button" class="bc-sep-pick" data-sep="<?= htmlspecialchars($sp) ?>" style="background: #0f172a; border: 1px solid #475569; color: #cbd5e1; border-radius: 6px; padding: 5px 11px; cursor: pointer; font-size: 0.95rem;"><?= htmlspecialchars($sp) ?></button>
            <?php endforeach; ?>
          </div>
        </div>
        <div>
          <label for="bc_label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 8px;">Home label</label>
          <input type="text" name="breadcrumbs_home_label" id="bc_label" value="<?= htmlspecialchars($bc['home_label']) ?>" maxlength="40" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.95rem;" />
        </div>
      </div>
    </div>

    <button type="submit" class="tn-btn-gold" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 26px; border: none; border-radius: 8px; font-weight: 700; font-size: 0.92rem; cursor: pointer; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35);">Save Breadcrumb Settings</button>
  </form>
</div>

<script>
(function () {
  var sep = document.getElementById('bc_sep');
  var label = document.getElementById('bc_label');
  var home = document.getElementById('bc_home');
  var enabled = document.getElementById('bc_enabled');
  var prev = document.getElementById('bc-preview');
  function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
  function render() {
    if (!enabled.checked) { prev.innerHTML = '<span style="color:#64748b;font-style:italic;">Breadcrumbs are turned off.</span>'; return; }
    var s = ' <span style="color:#64748b;">' + esc(sep.value || '>') + '</span> ';
    var trail = [];
    if (home.checked) trail.push('<a style="color:#38bdf8;text-decoration:none;">' + esc(label.value || 'Home') + '</a>');
    trail.push('<a style="color:#38bdf8;text-decoration:none;">Products</a>');
    trail.push('<a style="color:#38bdf8;text-decoration:none;">Pharma Bottles</a>');
    trail.push('<span style="color:#e2e8f0;font-weight:600;">60ml Round PET</span>');
    prev.innerHTML = trail.join(s);
  }
  [sep, label, home, enabled].forEach(function (el) { el.addEventListener('input', render); el.addEventListener('change', render); });
  document.querySelectorAll('.bc-sep-pick').forEach(function (b) {
    b.addEventListener('click', function () { sep.value = this.getAttribute('data-sep'); render(); });
  });
  render();
})();
</script>
