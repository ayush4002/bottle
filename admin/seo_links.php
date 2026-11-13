<?php
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/SeoModel.php';
require_once dirname(__DIR__) . '/app/Models/BlogModel.php';
require_once dirname(__DIR__) . '/app/Models/ProductModel.php';

checkAdminAuth();
SeoModel::init();

$csrfToken = generateCsrfToken();

// Map blog slug -> numeric id (for edit links) and gather content for suggestions
$blogPosts = class_exists('BlogModel') ? BlogModel::getAllPosts() : [];
$slugToId = [];
$slugToContent = [];
foreach ($blogPosts as $bp) {
    if (!empty($bp['slug'])) {
        $slugToId[$bp['slug']] = $bp['id'] ?? 0;
        $slugToContent[$bp['slug']] = $bp['content'] ?? '';
    }
}

// Suggestions request
$suggestFor = $_GET['suggest'] ?? '';
$suggestions = null;
$suggestLabel = '';
if ($suggestFor !== '' && isset($slugToContent[$suggestFor])) {
    $suggestLabel = $suggestFor;
    $suggestions = SeoModel::suggestInternalLinks($slugToContent[$suggestFor], '/blog/' . $suggestFor, 20);
}

$report = SeoModel::getLinkReport();

// Summary
$totalInternal = array_sum(array_column($report, 'internal'));
$totalOutbound = array_sum(array_column($report, 'outbound'));
$orphans = array_values(array_filter($report, function ($r) { return $r['orphan']; }));
usort($report, function ($a, $b) {
    if ($a['orphan'] !== $b['orphan']) return $a['orphan'] ? -1 : 1; // orphans first
    return $a['inbound'] <=> $b['inbound'];
});

function tn_links_edit($r, $slugToId) {
    if ($r['type'] === 'product') return '/admin/edit_product?id=' . urlencode($r['ref_id']);
    $id = $slugToId[$r['ref_id']] ?? 0;
    return $id ? '/admin/blog_editor?id=' . (int)$id : '/blog/' . urlencode($r['ref_id']);
}
?>

<div style="max-width: 1240px; margin: 0 auto;">
  <div style="margin-bottom: 22px;">
    <h1 style="font-size: 1.7rem; font-weight: 800; color: #f8fafc; margin: 0 0 6px 0;">Internal Links</h1>
    <p style="color: #94a3b8; font-size: 0.9rem; margin: 0;">Count the links in and out of every product and blog post, spot orphan pages that nothing links to, and get internal-link suggestions while writing.</p>
  </div>

  <!-- Summary tiles -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; margin-bottom: 24px;">
    <?php
      $tiles = [
        ['Pages analysed', count($report), '#38bdf8'],
        ['Internal links', $totalInternal, '#34d399'],
        ['Outbound links', $totalOutbound, '#fbbf24'],
        ['Orphan pages', count($orphans), count($orphans) ? '#f87171' : '#34d399'],
      ];
      foreach ($tiles as $t):
    ?>
      <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 18px 20px;">
        <div style="font-size: 1.9rem; font-weight: 800; color: <?= $t[2] ?>; line-height: 1;"><?= (int)$t[1] ?></div>
        <div style="font-size: 0.82rem; color: #94a3b8; margin-top: 6px;"><?= htmlspecialchars($t[0]) ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Internal link suggestions -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 22px; margin-bottom: 24px;">
    <h2 style="font-size: 1.1rem; font-weight: 800; color: #f1f5f9; margin: 0 0 6px;">Internal-link suggestions</h2>
    <p style="color: #94a3b8; font-size: 0.86rem; margin: 0 0 14px;">Pick a blog post. We scan its text for products and other posts you mention but haven't linked, so you can add internal links.</p>
    <form method="GET" action="/admin/seo_links" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
      <select name="suggest" style="flex: 1; min-width: 240px; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;">
        <option value="">Choose a blog post…</option>
        <?php foreach ($blogPosts as $bp): if (empty($bp['slug'])) continue; ?>
          <option value="<?= htmlspecialchars($bp['slug']) ?>" <?= $suggestFor === $bp['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($bp['title'] ?? $bp['slug']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="tn-btn-gold" style="padding: 10px 20px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">Find suggestions</button>
    </form>

    <?php if (is_array($suggestions)): ?>
      <div style="margin-top: 16px; border-top: 1px solid #334155; padding-top: 16px;">
        <?php if (empty($suggestions)): ?>
          <p style="color: #94a3b8; font-size: 0.88rem; margin: 0;">No new internal-link opportunities found in “<?= htmlspecialchars($suggestLabel) ?>”. Set focus keywords on your products and posts to get more matches.</p>
        <?php else: ?>
          <p style="color: #cbd5e1; font-size: 0.88rem; margin: 0 0 12px;">Mentions found in “<?= htmlspecialchars($suggestLabel) ?>” you could link to:</p>
          <div style="display: flex; flex-direction: column; gap: 8px;">
            <?php foreach ($suggestions as $sug): ?>
              <div style="display: flex; align-items: center; gap: 12px; background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 10px 14px; flex-wrap: wrap;">
                <span style="font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: <?= $sug['type'] === 'product' ? '#38bdf8' : '#a78bfa' ?>;"><?= htmlspecialchars($sug['type']) ?></span>
                <span style="font-weight: 600; color: #e2e8f0;"><?= htmlspecialchars($sug['label']) ?></span>
                <span style="font-size: 0.8rem; color: #64748b;">matched “<?= htmlspecialchars($sug['term']) ?>”</span>
                <code style="margin-left: auto; font-size: 0.78rem; color: #94a3b8;"><?= htmlspecialchars($sug['url']) ?></code>
                <?php if ($sug['linked']): ?>
                  <span style="font-size: 0.72rem; color: #34d399; font-weight: 700;">✓ already linked</span>
                <?php else: ?>
                  <span style="font-size: 0.72rem; color: #fbbf24; font-weight: 700;">not linked</span>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Link counter table -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; overflow: hidden;">
    <div style="padding: 16px 20px; border-bottom: 1px solid #334155;">
      <h2 style="font-size: 1.1rem; font-weight: 800; color: #f1f5f9; margin: 0;">Link counter</h2>
      <p style="color: #94a3b8; font-size: 0.82rem; margin: 4px 0 0;">Orphan pages (nothing links to them) are listed first — add an internal link from a related page to help them rank.</p>
    </div>
    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; font-size: 0.86rem;">
        <thead>
          <tr style="background: #0f172a; color: #94a3b8; text-align: left;">
            <th style="padding: 11px 16px; font-weight: 700;">Page</th>
            <th style="padding: 11px 12px; font-weight: 700; text-align: center;">Type</th>
            <th style="padding: 11px 12px; font-weight: 700; text-align: center;" title="Internal links pointing TO this page">Inbound</th>
            <th style="padding: 11px 12px; font-weight: 700; text-align: center;" title="Internal links in this page's body">Internal out</th>
            <th style="padding: 11px 12px; font-weight: 700; text-align: center;" title="External links in this page's body">External</th>
            <th style="padding: 11px 16px; font-weight: 700; text-align: right;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($report as $r): ?>
            <tr style="border-top: 1px solid #334155;">
              <td style="padding: 11px 16px;">
                <a href="<?= htmlspecialchars($r['url']) ?>" target="_blank" rel="noopener" style="color: #e2e8f0; font-weight: 600; text-decoration: none;"><?= htmlspecialchars(mb_strimwidth($r['label'], 0, 52, '…')) ?></a>
                <?php if ($r['orphan']): ?><span style="margin-left: 8px; font-size: 0.68rem; font-weight: 700; color: #f87171; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); padding: 2px 7px; border-radius: 20px;">ORPHAN</span><?php endif; ?>
              </td>
              <td style="padding: 11px 12px; text-align: center; color: <?= $r['type'] === 'product' ? '#38bdf8' : '#a78bfa' ?>; font-weight: 700; font-size: 0.72rem; text-transform: uppercase;"><?= htmlspecialchars($r['type']) ?></td>
              <td style="padding: 11px 12px; text-align: center; color: <?= $r['inbound'] ? '#34d399' : '#f87171' ?>; font-weight: 700;"><?= (int)$r['inbound'] ?></td>
              <td style="padding: 11px 12px; text-align: center; color: #cbd5e1;"><?= (int)$r['internal'] ?></td>
              <td style="padding: 11px 12px; text-align: center; color: #cbd5e1;"><?= (int)$r['outbound'] ?></td>
              <td style="padding: 11px 16px; text-align: right;">
                <a href="<?= htmlspecialchars(tn_links_edit($r, $slugToId)) ?>" style="color: #38bdf8; text-decoration: none; font-weight: 600; font-size: 0.82rem;">Edit →</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($report)): ?>
            <tr><td colspan="6" style="padding: 24px; text-align: center; color: #64748b;">No blog posts or products with content to analyse yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
