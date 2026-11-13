<?php
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/SeoModel.php';
require_once dirname(__DIR__) . '/app/Models/ProductModel.php';
require_once dirname(__DIR__) . '/app/Models/BlogModel.php';

checkAdminAuth();
SeoModel::init();

$adminUser = $_SESSION['admin_user'] ?? 'admin';
$csrfToken = generateCsrfToken();

// ---- Save (only rows whose title or description actually changed) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'bulk_save') {
    $saved = 0;
    $rows = $_POST['rows'] ?? [];
    foreach ((is_array($rows) ? $rows : []) as $row) {
        $url  = trim($row['url'] ?? '');
        $type = $row['type'] ?? 'page';
        $ref  = $row['ref'] ?? '';
        if ($url === '') continue;
        $newTitle = trim($row['title'] ?? '');
        $newDesc  = trim($row['desc'] ?? '');
        $oldTitle = trim($row['otitle'] ?? '');
        $oldDesc  = trim($row['odesc'] ?? '');
        if ($newTitle === $oldTitle && $newDesc === $oldDesc) continue;

        // Merge into the existing record so focus keyword, canonical, robots etc. survive
        $existing = SeoModel::getPageSeo($url, $type, $ref) ?: [];
        $data = array_merge($existing, [
            'url_slug'         => $url,
            'page_type'        => $type,
            'page_ref_id'      => $ref,
            'meta_title'       => $newTitle,
            'meta_description' => $newDesc,
        ]);
        unset($data['old_url_slug']);
        SeoModel::savePageSeo($data, $adminUser);
        $saved++;
    }
    $q = http_build_query(array_filter([
        'type' => $_POST['f_type'] ?? '', 'q' => $_POST['f_q'] ?? '', 'pg' => $_POST['f_pg'] ?? '',
        'msg' => $saved . ' row(s) updated. SEO titles & descriptions saved.',
    ]));
    header('Location: /admin/seo_bulk?' . $q);
    exit();
}

$type = $_GET['type'] ?? 'all';
$q = trim($_GET['q'] ?? '');
$pg = max(1, (int)($_GET['pg'] ?? 1));
$perPage = 50;

$all = SeoModel::getAllSeoEntities($type, $q);
$total = count($all);
$pages = max(1, (int)ceil($total / $perPage));
$pg = min($pg, $pages);
$rows = array_slice($all, ($pg - 1) * $perPage, $perPage);

$typeCounts = ['all' => 0, 'page' => 0, 'product' => 0, 'blog' => 0];
foreach (SeoModel::getAllSeoEntities('all', $q) as $e) { $typeCounts['all']++; $typeCounts[$e['type']] = ($typeCounts[$e['type']] ?? 0) + 1; }

function tn_bulk_link($type, $q, $t) {
    return '/admin/seo_bulk?' . http_build_query(array_filter(['type' => $t, 'q' => $q]));
}
$statusColors = ['good' => '#34d399', 'ok' => '#fbbf24', 'poor' => '#f87171', 'none' => '#64748b'];
?>

<div style="max-width: 1320px; margin: 0 auto;">
  <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; flex-wrap: wrap; margin-bottom: 20px;">
    <div>
      <h1 style="font-size: 1.7rem; font-weight: 800; color: #f8fafc; margin: 0 0 6px 0;">Bulk SEO Editor</h1>
      <p style="color: #94a3b8; font-size: 0.9rem; margin: 0;">Edit the SEO title and meta description of every page, product and post in one table, then save them all at once. Leave a cell blank to fall back to the global template.</p>
    </div>
    <a href="/admin/seo" class="tn-btn-outline" style="padding: 9px 16px; border-radius: 8px; background: #1e293b; color: #cbd5e1; border: 1px solid #334155; text-decoration: none; font-size: 0.86rem; font-weight: 600;">← SEO Dashboard</a>
  </div>

  <?php if (!empty($_GET['msg'])): ?>
    <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 12px 16px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 18px;"><?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>

  <!-- Filters -->
  <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-bottom: 16px;">
    <div style="display: flex; gap: 6px; background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: 4px;">
      <?php foreach (['all' => 'All', 'page' => 'Pages', 'product' => 'Products', 'blog' => 'Blog'] as $k => $lab): ?>
        <a href="<?= htmlspecialchars(tn_bulk_link($type, $q, $k)) ?>" style="padding: 7px 14px; border-radius: 7px; font-size: 0.84rem; font-weight: 600; text-decoration: none; <?= $type === $k ? 'background:#0284c7;color:#fff;' : 'color:#94a3b8;' ?>"><?= $lab ?> <span style="opacity:0.7;">(<?= (int)($typeCounts[$k] ?? 0) ?>)</span></a>
      <?php endforeach; ?>
    </div>
    <form method="GET" action="/admin/seo_bulk" style="display: flex; gap: 8px; flex: 1; min-width: 220px;">
      <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>" />
      <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search title, URL, keyword…" style="flex: 1; padding: 9px 13px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
      <button type="submit" class="tn-btn-outline" style="padding: 9px 16px; border-radius: 8px; background: #0f172a; color: #cbd5e1; border: 1px solid #475569; font-weight: 600; cursor: pointer;">Search</button>
    </form>
  </div>

  <form method="POST" action="/admin/seo_bulk">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
    <input type="hidden" name="action" value="bulk_save" />
    <input type="hidden" name="f_type" value="<?= htmlspecialchars($type) ?>" />
    <input type="hidden" name="f_q" value="<?= htmlspecialchars($q) ?>" />
    <input type="hidden" name="f_pg" value="<?= (int)$pg ?>" />

    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; overflow: hidden;">
      <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; min-width: 820px;">
          <thead>
            <tr style="background: #0f172a; color: #94a3b8; text-align: left;">
              <th style="padding: 11px 16px; font-weight: 700; width: 20%;">Page</th>
              <th style="padding: 11px 12px; font-weight: 700; width: 38%;">SEO title</th>
              <th style="padding: 11px 12px; font-weight: 700; width: 42%;">Meta description</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $i => $r): ?>
              <tr style="border-top: 1px solid #334155; vertical-align: top;">
                <td style="padding: 12px 16px;">
                  <input type="hidden" name="rows[<?= $i ?>][type]" value="<?= htmlspecialchars($r['type']) ?>" />
                  <input type="hidden" name="rows[<?= $i ?>][ref]" value="<?= htmlspecialchars($r['ref_id']) ?>" />
                  <input type="hidden" name="rows[<?= $i ?>][url]" value="<?= htmlspecialchars($r['url']) ?>" />
                  <input type="hidden" name="rows[<?= $i ?>][otitle]" value="<?= htmlspecialchars($r['title']) ?>" />
                  <input type="hidden" name="rows[<?= $i ?>][odesc]" value="<?= htmlspecialchars($r['desc']) ?>" />
                  <div style="display: flex; align-items: center; gap: 7px; margin-bottom: 4px;">
                    <span title="SEO score" style="width: 9px; height: 9px; border-radius: 50%; background: <?= $statusColors[$r['status']] ?? '#64748b' ?>; flex-shrink: 0;"></span>
                    <span style="font-weight: 600; color: #e2e8f0;"><?= htmlspecialchars(mb_strimwidth($r['label'], 0, 34, '…')) ?></span>
                  </div>
                  <a href="<?= htmlspecialchars($r['url']) ?>" target="_blank" rel="noopener" style="color: #64748b; font-size: 0.76rem; text-decoration: none;"><?= htmlspecialchars(mb_strimwidth($r['url'], 0, 38, '…')) ?></a>
                  <div style="font-size: 0.68rem; color: #64748b; text-transform: uppercase; margin-top: 3px;"><?= htmlspecialchars($r['type']) ?><?= $r['status'] === 'none' ? ' · using template' : '' ?></div>
                </td>
                <td style="padding: 12px 12px;">
                  <input type="text" name="rows[<?= $i ?>][title]" value="<?= htmlspecialchars($r['title']) ?>" class="tn-bulk-title" placeholder="Default template title" style="width: 100%; box-sizing: border-box; padding: 9px 11px; background: #0f172a; border: 1px solid #475569; border-radius: 7px; color: #fff; font-size: 0.84rem;" />
                  <div class="tn-bulk-count" data-min="45" data-max="60" style="font-size: 0.72rem; color: #64748b; margin-top: 4px;"></div>
                </td>
                <td style="padding: 12px 12px;">
                  <textarea name="rows[<?= $i ?>][desc]" rows="2" class="tn-bulk-desc" placeholder="Default template description" style="width: 100%; box-sizing: border-box; padding: 9px 11px; background: #0f172a; border: 1px solid #475569; border-radius: 7px; color: #fff; font-size: 0.84rem; resize: vertical;"><?= htmlspecialchars($r['desc']) ?></textarea>
                  <div class="tn-bulk-count" data-min="120" data-max="160" style="font-size: 0.72rem; color: #64748b; margin-top: 4px;"></div>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
              <tr><td colspan="3" style="padding: 24px; text-align: center; color: #64748b;">Nothing matches this filter.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; gap: 14px; flex-wrap: wrap; margin-top: 18px;">
      <div style="color: #94a3b8; font-size: 0.84rem;">
        Showing <?= count($rows) ?> of <?= $total ?> · page <?= $pg ?>/<?= $pages ?>
        <?php if ($pages > 1): ?>
          <span style="margin-left: 10px;">
            <?php for ($p = 1; $p <= $pages; $p++): ?>
              <a href="/admin/seo_bulk?<?= http_build_query(array_filter(['type' => $type, 'q' => $q, 'pg' => $p])) ?>" style="display: inline-block; padding: 3px 9px; margin: 0 2px; border-radius: 5px; text-decoration: none; <?= $p === $pg ? 'background:#0284c7;color:#fff;' : 'color:#94a3b8;background:#1e293b;border:1px solid #334155;' ?>"><?= $p ?></a>
            <?php endfor; ?>
          </span>
        <?php endif; ?>
      </div>
      <button type="submit" class="tn-btn-gold" style="padding: 12px 28px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35);">Save all changes</button>
    </div>
  </form>
</div>

<script>
(function () {
  function colour(len, min, max) { if (!len) return '#64748b'; if (len >= min && len <= max) return '#34d399'; if (len > max) return '#f87171'; return '#fbbf24'; }
  document.querySelectorAll('.tn-bulk-title, .tn-bulk-desc').forEach(function (el) {
    var counter = el.parentElement.querySelector('.tn-bulk-count');
    var min = +counter.getAttribute('data-min'), max = +counter.getAttribute('data-max');
    function upd() { var n = el.value.length; counter.textContent = n + ' chars (aim ' + min + '–' + max + ')'; counter.style.color = colour(n, min, max); }
    el.addEventListener('input', upd); upd();
  });
})();
</script>
