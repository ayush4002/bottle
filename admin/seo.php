<?php
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/SeoModel.php';

checkAdminAuth();
SeoModel::init();

$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';

// Run health scan
$health = SeoModel::getAuditHealth();
$auditLogs = SeoModel::getAuditLogs(15);

// Unified content-SEO scoring across every page, product and post
$seoEntities = SeoModel::getAllSeoEntities();
$seoDist = ['good' => 0, 'ok' => 0, 'poor' => 0, 'none' => 0];
foreach ($seoEntities as $e) { $seoDist[$e['status']] = ($seoDist[$e['status']] ?? 0) + 1; }
$seoScored = array_values(array_filter($seoEntities, function ($e) { return !empty($e['scored']); }));
$avgScore = $seoScored ? (int) round(array_sum(array_column($seoScored, 'score')) / count($seoScored)) : 0;
$seoNeedsWork = array_values(array_filter($seoScored, function ($e) { return $e['score'] < 80; }));
usort($seoNeedsWork, function ($a, $b) { return $a['score'] <=> $b['score']; });
$seoNeedsWork = array_slice($seoNeedsWork, 0, 12);
$seoStatusColors = ['good' => '#34d399', 'ok' => '#fbbf24', 'poor' => '#f87171', 'none' => '#64748b'];
function tn_seo_edit_link($e) {
    if ($e['type'] === 'product') return '/admin/edit_product?id=' . urlencode($e['ref_id']);
    return '/admin/seo_bulk?type=' . $e['type'] . '&q=' . urlencode($e['label']);
}

$healthScore = $health['seo_health_score'];

$scoreColor = '#34d399';
$scoreBg = 'rgba(52, 211, 153, 0.15)';
if ($healthScore < 70) {
    $scoreColor = '#f87171';
    $scoreBg = 'rgba(239, 68, 68, 0.15)';
} elseif ($healthScore < 85) {
    $scoreColor = '#fbbf24';
    $scoreBg = 'rgba(251, 191, 36, 0.15)';
}
?>

<div style="max-width: 1320px; margin: 0 auto;">

  <!-- HEADER -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
      <h1 style="font-size: 1.8rem; font-weight: 800; color: #f8fafc; margin: 0 0 6px 0;">
        Technical SEO Health &amp; Audit Dashboard
      </h1>
      <p style="color: #94a3b8; font-size: 0.9rem; margin: 0;">
        Comprehensive on-page audit, crawl health, redirect diagnostics, and metadata status.
      </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <a href="/sitemap.xml" target="_blank" class="tn-btn-outline" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: 0.88rem; background: #1e293b; color: #38bdf8; border: 1px solid #334155; text-decoration: none;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        View Live sitemap.xml
      </a>
      <a href="/robots.txt" target="_blank" class="tn-btn-outline" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: 0.88rem; background: #1e293b; color: #cbd5e1; border: 1px solid #334155; text-decoration: none;">
        View robots.txt
      </a>
      <a href="/admin/seo_pages" class="tn-btn-gold" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 8px; font-weight: 700; font-size: 0.88rem; text-decoration: none; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35);">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Manage Page SEO
      </a>
    </div>
  </div>

  <?php if (!empty($msg)): ?>
    <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 12px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 20px;">
      <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <!-- HEALTH SCORE BANNER -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
    <div style="display: flex; align-items: center; gap: 20px;">
      <div style="width: 84px; height: 84px; border-radius: 50%; background: <?= $scoreBg ?>; border: 3px solid <?= $scoreColor ?>; display: flex; flex-direction: column; align-items: center; justify-content: center;">
        <span style="font-size: 1.8rem; font-weight: 900; color: <?= $scoreColor ?>; line-height: 1;"><?= $healthScore ?></span>
        <span style="font-size: 0.65rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">/ 100</span>
      </div>
      <div>
        <div style="font-size: 1.25rem; font-weight: 800; color: #f8fafc; margin-bottom: 4px;">
          Website SEO Optimization Rating
        </div>
        <p style="font-size: 0.88rem; color: #94a3b8; margin: 0; max-width: 650px;">
          Calculated across <?= $health['total_pages_scanned'] ?> catalogue and static pages, meta lengths, image attributes, robots directives, and link redirects.
        </p>
      </div>
    </div>
    <div style="display: flex; gap: 10px;">
      <a href="/admin/seo" class="tn-btn-outline" style="padding: 10px 18px; background: #0f172a; color: #f8fafc; border: 1px solid #475569; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
        Re-Scan Website
      </a>
      <a href="/admin/seo_settings" class="tn-btn-outline" style="padding: 10px 18px; background: #0f172a; color: #38bdf8; border: 1px solid #0284c7; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
        Global Settings
      </a>
    </div>
  </div>

  <!-- DIAGNOSTIC STATS GRID -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    
    <!-- Missing Titles -->
    <div style="background: #1e293b; border: 1px solid <?= $health['missing_title'] > 0 ? '#f87171' : '#334155' ?>; border-radius: 12px; padding: 18px;">
      <div style="font-size: 0.76rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Missing / Short Titles</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: <?= $health['missing_title'] > 0 ? '#f87171' : '#34d399' ?>; margin-bottom: 8px;">
        <?= $health['missing_title'] ?>
      </div>
      <a href="/admin/seo_pages?filter=missing_title" style="color: #38bdf8; font-size: 0.8rem; font-weight: 600; text-decoration: none;">
        Review Pages &rarr;
      </a>
    </div>

    <!-- Missing Descriptions -->
    <div style="background: #1e293b; border: 1px solid <?= $health['missing_desc'] > 0 ? '#fbbf24' : '#334155' ?>; border-radius: 12px; padding: 18px;">
      <div style="font-size: 0.76rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Missing / Short Descriptions</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: <?= $health['missing_desc'] > 0 ? '#fbbf24' : '#34d399' ?>; margin-bottom: 8px;">
        <?= $health['missing_desc'] ?>
      </div>
      <a href="/admin/seo_pages?filter=missing_desc" style="color: #38bdf8; font-size: 0.8rem; font-weight: 600; text-decoration: none;">
        Review Descriptions &rarr;
      </a>
    </div>

    <!-- Images Missing Alt -->
    <div style="background: #1e293b; border: 1px solid <?= $health['missing_alt'] > 0 ? '#fbbf24' : '#334155' ?>; border-radius: 12px; padding: 18px;">
      <div style="font-size: 0.76rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Images Missing Alt Text</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: <?= $health['missing_alt'] > 0 ? '#fbbf24' : '#34d399' ?>; margin-bottom: 8px;">
        <?= $health['missing_alt'] ?>
      </div>
      <a href="/admin/seo_images" style="color: #38bdf8; font-size: 0.8rem; font-weight: 600; text-decoration: none;">
        Bulk Update Alt Tags &rarr;
      </a>
    </div>

    <!-- Duplicate Titles -->
    <div style="background: #1e293b; border: 1px solid <?= $health['duplicate_titles'] > 0 ? '#f87171' : '#334155' ?>; border-radius: 12px; padding: 18px;">
      <div style="font-size: 0.76rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Duplicate Titles</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: <?= $health['duplicate_titles'] > 0 ? '#f87171' : '#34d399' ?>; margin-bottom: 8px;">
        <?= $health['duplicate_titles'] ?>
      </div>
      <a href="/admin/seo_pages" style="color: #38bdf8; font-size: 0.8rem; font-weight: 600; text-decoration: none;">
        Resolve Duplicates &rarr;
      </a>
    </div>

    <!-- 404 Hits Logged -->
    <div style="background: #1e293b; border: 1px solid <?= $health['total_404_hits'] > 0 ? '#f87171' : '#334155' ?>; border-radius: 12px; padding: 18px;">
      <div style="font-size: 0.76rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Total 404 Error Hits</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: <?= $health['total_404_hits'] > 0 ? '#f87171' : '#34d399' ?>; margin-bottom: 8px;">
        <?= $health['total_404_hits'] ?>
      </div>
      <a href="/admin/seo_redirects#tab-404" style="color: #38bdf8; font-size: 0.8rem; font-weight: 600; text-decoration: none;">
        View Broken URLs &rarr;
      </a>
    </div>

    <!-- Active Redirects -->
    <div style="background: #1e293b; border: 1px solid <?= ($health['redirect_loops'] > 0 || $health['redirect_chains'] > 0) ? '#f87171' : '#334155' ?>; border-radius: 12px; padding: 18px;">
      <div style="font-size: 0.76rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Active 301 Redirects</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: #38bdf8; margin-bottom: 8px;">
        <?= $health['total_redirects'] ?>
      </div>
      <?php if ($health['redirect_loops'] > 0): ?>
        <span style="color: #f87171; font-size: 0.76rem; font-weight: 700; display: block; margin-bottom: 4px;">Warning: <?= $health['redirect_loops'] ?> Loop Detected!</span>
      <?php endif; ?>
      <a href="/admin/seo_redirects" style="color: #38bdf8; font-size: 0.8rem; font-weight: 600; text-decoration: none;">
        Manage Redirects &rarr;
      </a>
    </div>

  </div>

  <!-- CONTENT SEO SCORES (all pages/products/posts) -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 22px; margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 14px; flex-wrap: wrap; margin-bottom: 16px;">
      <div>
        <h3 style="font-size: 1.1rem; font-weight: 800; color: #f8fafc; margin: 0 0 4px;">Content SEO Scores</h3>
        <p style="color: #94a3b8; font-size: 0.82rem; margin: 0;">Average score of pages with SEO set: <strong style="color: #f1f5f9;"><?= $avgScore ?>/100</strong> across <?= count($seoEntities) ?> pages, products &amp; posts.</p>
      </div>
      <a href="/admin/seo_bulk" class="tn-btn-gold" style="padding: 9px 18px; border-radius: 8px; font-weight: 700; font-size: 0.84rem; text-decoration: none; box-shadow: 0 4px 14px rgba(184,137,46,0.35);">Open Bulk Editor</a>
    </div>

    <!-- distribution bar -->
    <?php $distTotal = max(1, array_sum($seoDist)); ?>
    <div style="display: flex; height: 12px; border-radius: 6px; overflow: hidden; margin-bottom: 8px;">
      <?php foreach (['good' => 'Good', 'ok' => 'OK', 'poor' => 'Poor', 'none' => 'No SEO set'] as $k => $lab): if (($seoDist[$k] ?? 0) <= 0) continue; ?>
        <div title="<?= $lab ?>: <?= (int)$seoDist[$k] ?>" style="width: <?= round($seoDist[$k] / $distTotal * 100, 2) ?>%; background: <?= $seoStatusColors[$k] ?>;"></div>
      <?php endforeach; ?>
    </div>
    <div style="display: flex; gap: 16px; flex-wrap: wrap; font-size: 0.78rem; color: #94a3b8; margin-bottom: 18px;">
      <span><span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:#34d399;margin-right:5px;"></span>Good <?= (int)$seoDist['good'] ?></span>
      <span><span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:#fbbf24;margin-right:5px;"></span>OK <?= (int)$seoDist['ok'] ?></span>
      <span><span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:#f87171;margin-right:5px;"></span>Needs work <?= (int)$seoDist['poor'] ?></span>
      <span><span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:#64748b;margin-right:5px;"></span>Using template <?= (int)$seoDist['none'] ?></span>
    </div>

    <?php if (!empty($seoNeedsWork)): ?>
      <div style="font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 10px;">Pages to improve first</div>
      <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.84rem;">
          <thead>
            <tr style="background: #0f172a; color: #94a3b8; text-align: left;">
              <th style="padding: 9px 14px; font-weight: 700;">Page</th>
              <th style="padding: 9px 12px; font-weight: 700; text-align: center;">Type</th>
              <th style="padding: 9px 12px; font-weight: 700; text-align: center;">Score</th>
              <th style="padding: 9px 12px; font-weight: 700;">Main issue</th>
              <th style="padding: 9px 14px; font-weight: 700; text-align: right;">Fix</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($seoNeedsWork as $e):
              $issue = 'Improve meta content';
              foreach ($e['checks'] as $c) { if (($c['status'] ?? '') === 'fail') { $issue = $c['label']; break; } }
              if ($issue === 'Improve meta content') { foreach ($e['checks'] as $c) { if (($c['status'] ?? '') === 'warn') { $issue = $c['label']; break; } } }
            ?>
              <tr style="border-top: 1px solid #334155;">
                <td style="padding: 9px 14px;"><a href="<?= htmlspecialchars($e['url']) ?>" target="_blank" rel="noopener" style="color: #e2e8f0; font-weight: 600; text-decoration: none;"><?= htmlspecialchars(mb_strimwidth($e['label'], 0, 44, '…')) ?></a></td>
                <td style="padding: 9px 12px; text-align: center; font-size: 0.72rem; text-transform: uppercase; color: #94a3b8;"><?= htmlspecialchars($e['type']) ?></td>
                <td style="padding: 9px 12px; text-align: center;"><span style="display: inline-block; min-width: 34px; padding: 2px 8px; border-radius: 20px; font-weight: 800; font-size: 0.78rem; color: #0f172a; background: <?= $seoStatusColors[$e['status']] ?>;"><?= (int)$e['score'] ?></span></td>
                <td style="padding: 9px 12px; color: #cbd5e1;"><?= htmlspecialchars($issue) ?></td>
                <td style="padding: 9px 14px; text-align: right;"><a href="<?= htmlspecialchars(tn_seo_edit_link($e)) ?>" style="color: #38bdf8; text-decoration: none; font-weight: 600;">Edit →</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p style="color: #6ee7b7; font-size: 0.88rem; margin: 0;">Every page with SEO set scores 80+. Nice work.</p>
    <?php endif; ?>
  </div>

  <!-- FAST QUICK-LINKS TOOLBAR -->
  <div style="background: #0f172a; border: 1px solid #334155; border-radius: 12px; padding: 18px 24px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
    <div style="font-size: 0.92rem; font-weight: 700; color: #f8fafc;">
      Quick Technical SEO Tools:
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <a href="/admin/seo_settings" style="padding: 8px 14px; background: #1e293b; border: 1px solid #475569; color: #f8fafc; border-radius: 6px; text-decoration: none; font-size: 0.82rem; font-weight: 600;">Global Settings</a>
      <a href="/admin/seo_pages" style="padding: 8px 14px; background: #1e293b; border: 1px solid #475569; color: #f8fafc; border-radius: 6px; text-decoration: none; font-size: 0.82rem; font-weight: 600;">Page SEO Manager</a>
      <a href="/admin/seo_templates" style="padding: 8px 14px; background: #1e293b; border: 1px solid #475569; color: #f8fafc; border-radius: 6px; text-decoration: none; font-size: 0.82rem; font-weight: 600;">Meta Templates</a>
      <a href="/admin/seo_redirects" style="padding: 8px 14px; background: #1e293b; border: 1px solid #475569; color: #f8fafc; border-radius: 6px; text-decoration: none; font-size: 0.82rem; font-weight: 600;">Redirects &amp; 404s</a>
      <a href="/admin/seo_sitemap" style="padding: 8px 14px; background: #1e293b; border: 1px solid #475569; color: #f8fafc; border-radius: 6px; text-decoration: none; font-size: 0.82rem; font-weight: 600;">Sitemap &amp; Robots</a>
      <a href="/admin/seo_images" style="padding: 8px 14px; background: #1e293b; border: 1px solid #475569; color: #f8fafc; border-radius: 6px; text-decoration: none; font-size: 0.82rem; font-weight: 600;">Image Alt Tool</a>
      <a href="/admin/seo_bulk" style="padding: 8px 14px; background: #1e293b; border: 1px solid #475569; color: #f8fafc; border-radius: 6px; text-decoration: none; font-size: 0.82rem; font-weight: 600;">Bulk SEO Editor</a>
      <a href="/admin/seo_links" style="padding: 8px 14px; background: #1e293b; border: 1px solid #475569; color: #f8fafc; border-radius: 6px; text-decoration: none; font-size: 0.82rem; font-weight: 600;">Internal Links</a>
      <a href="/admin/seo_breadcrumbs" style="padding: 8px 14px; background: #1e293b; border: 1px solid #475569; color: #f8fafc; border-radius: 6px; text-decoration: none; font-size: 0.82rem; font-weight: 600;">Breadcrumbs</a>
    </div>
  </div>

  <!-- RECENT SEO AUDIT LOGS -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px;">
    <h3 style="font-size: 1.1rem; font-weight: 800; color: #f8fafc; margin: 0 0 16px 0; border-bottom: 1px solid #334155; padding-bottom: 12px;">
      Recent SEO Modifications &amp; Audit Trail
    </h3>
    <?php if (empty($auditLogs)): ?>
      <p style="color: #94a3b8; font-size: 0.88rem; margin: 0;">No audit events recorded yet. Changes will be logged here automatically.</p>
    <?php else: ?>
      <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.86rem; text-align: left;">
          <thead>
            <tr style="background: #0f172a; color: #94a3b8; border-bottom: 1px solid #334155;">
              <th style="padding: 10px 14px;">Date / Time</th>
              <th style="padding: 10px 14px;">Admin User</th>
              <th style="padding: 10px 14px;">Action</th>
              <th style="padding: 10px 14px;">Details</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($auditLogs as $log): ?>
              <tr style="border-bottom: 1px solid #334155;">
                <td style="padding: 10px 14px; color: #94a3b8; white-space: nowrap;">
                  <?= htmlspecialchars($log['created_at']) ?>
                </td>
                <td style="padding: 10px 14px; font-weight: 600; color: #f8fafc;">
                  <?= htmlspecialchars($log['admin_id'] ?? 'admin') ?>
                </td>
                <td style="padding: 10px 14px;">
                  <span style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 0.78rem;">
                    <?= htmlspecialchars($log['action']) ?>
                  </span>
                </td>
                <td style="padding: 10px 14px; color: #cbd5e1;">
                  <?= htmlspecialchars($log['details']) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>

