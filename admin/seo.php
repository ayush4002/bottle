<?php
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/partials/seo_tabs.php';
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

  <!-- COMPLETE WEBSITE SEO SUITE — 11-MODULE COMMAND CENTER -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; border-bottom: 1px solid #334155; padding-bottom: 16px;">
      <div>
        <h2 style="font-size: 1.25rem; font-weight: 800; color: #f8fafc; margin: 0 0 4px 0;">
          Complete Website SEO Suite &amp; Technical Modules
        </h2>
        <p style="color: #94a3b8; font-size: 0.88rem; margin: 0;">
          All 11 technical SEO tools, metadata editors, schema builders, and crawl monitors for full website optimization.
        </p>
      </div>
      <div style="display: flex; gap: 8px;">
        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 6px; color: #38bdf8; font-size: 0.78rem; font-weight: 700;">
          11 Core Modules Active
        </span>
        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.3); border-radius: 6px; color: #34d399; font-size: 0.78rem; font-weight: 700;">
          311+ URLs Synced
        </span>
      </div>
    </div>

    <!-- 11-MODULE GRID -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 16px;">
      
      <!-- Module 1: Search Appearance -->
      <div style="background: #0f172a; border: 1px solid #334155; border-radius: 10px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
            <div style="font-size: 1rem; font-weight: 700; color: #f8fafc; display: flex; align-items: center; gap: 8px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
              Search Appearance
            </div>
            <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; background: #1e293b; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Core Settings</span>
          </div>
          <p style="font-size: 0.82rem; color: #cbd5e1; line-height: 1.45; margin: 0 0 14px 0;">
            Webmaster verification (Google, Bing, Baidu, Yandex, Pinterest), Knowledge Graph schema, organization branding, sitename separator, and default robots directives.
          </p>
        </div>
        <div style="border-top: 1px solid #1e293b; padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 0.75rem; color: #34d399; font-weight: 600;">Verified &amp; Configured</span>
          <a href="/admin/seo_settings" style="color: #38bdf8; text-decoration: none; font-size: 0.82rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">Configure &rarr;</a>
        </div>
      </div>

      <!-- Module 2: Titles & Meta Manager -->
      <div style="background: #0f172a; border: 1px solid #334155; border-radius: 10px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
            <div style="font-size: 1rem; font-weight: 700; color: #f8fafc; display: flex; align-items: center; gap: 8px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
              Titles &amp; Meta (311 URLs)
            </div>
            <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; background: rgba(56, 189, 248, 0.2); color: #38bdf8; font-weight: 700; text-transform: uppercase;">All Pages</span>
          </div>
          <p style="font-size: 0.82rem; color: #cbd5e1; line-height: 1.45; margin: 0 0 14px 0;">
            Granular per-URL SEO metadata editor for all 311+ website pages, category archives, blog posts, and catalogue items with full 5-tab Yoast snippet modal.
          </p>
        </div>
        <div style="border-top: 1px solid #1e293b; padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 600;">311 Live URLs Indexed</span>
          <a href="/admin/seo_pages" style="color: #38bdf8; text-decoration: none; font-size: 0.82rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">Manage Titles &rarr;</a>
        </div>
      </div>

      <!-- Module 3: Bulk Editor -->
      <div style="background: #0f172a; border: 1px solid #334155; border-radius: 10px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
            <div style="font-size: 1rem; font-weight: 700; color: #f8fafc; display: flex; align-items: center; gap: 8px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7m0-18H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7m0-18v18"/></svg>
              Bulk Meta Editor
            </div>
            <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; background: #1e293b; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Productivity</span>
          </div>
          <p style="font-size: 0.82rem; color: #cbd5e1; line-height: 1.45; margin: 0 0 14px 0;">
            Fast spreadsheet-style multi-row editor to rapidly modify SEO titles, meta descriptions, and keywords across multiple catalog entries without leaving the page.
          </p>
        </div>
        <div style="border-top: 1px solid #1e293b; padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 0.75rem; color: #fbbf24; font-weight: 600;">Multi-Row Quick Save</span>
          <a href="/admin/seo_bulk" style="color: #38bdf8; text-decoration: none; font-size: 0.82rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">Launch Editor &rarr;</a>
        </div>
      </div>

      <!-- Module 4: Meta Templates -->
      <div style="background: #0f172a; border: 1px solid #334155; border-radius: 10px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
            <div style="font-size: 1rem; font-weight: 700; color: #f8fafc; display: flex; align-items: center; gap: 8px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
              Title &amp; Meta Templates
            </div>
            <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; background: #1e293b; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Automation</span>
          </div>
          <p style="font-size: 0.82rem; color: #cbd5e1; line-height: 1.45; margin: 0 0 14px 0;">
            Define dynamic pattern variables (<code style="color:#38bdf8;">%%title%%</code>, <code style="color:#38bdf8;">%%sitename%%</code>, <code style="color:#38bdf8;">%%category%%</code>, <code style="color:#38bdf8;">%%sep%%</code>) for automated metadata across all items.
          </p>
        </div>
        <div style="border-top: 1px solid #1e293b; padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 0.75rem; color: #34d399; font-weight: 600;">Active Variables</span>
          <a href="/admin/seo_templates" style="color: #38bdf8; text-decoration: none; font-size: 0.82rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">Edit Templates &rarr;</a>
        </div>
      </div>

      <!-- Module 5: Internal Links -->
      <div style="background: #0f172a; border: 1px solid #334155; border-radius: 10px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
            <div style="font-size: 1rem; font-weight: 700; color: #f8fafc; display: flex; align-items: center; gap: 8px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
              Internal Links Optimizer
            </div>
            <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; background: #1e293b; color: #94a3b8; font-weight: 600; text-transform: uppercase;">PageRank Flow</span>
          </div>
          <p style="font-size: 0.82rem; color: #cbd5e1; line-height: 1.45; margin: 0 0 14px 0;">
            Track inbound and outbound internal links, detect orphaned articles and catalogue pages, and improve site crawl architecture and search engine link equity.
          </p>
        </div>
        <div style="border-top: 1px solid #1e293b; padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 600;">Link Count &amp; Anchors</span>
          <a href="/admin/seo_links" style="color: #38bdf8; text-decoration: none; font-size: 0.82rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">Audit Links &rarr;</a>
        </div>
      </div>

      <!-- Module 6: Breadcrumbs -->
      <div style="background: #0f172a; border: 1px solid #334155; border-radius: 10px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
            <div style="font-size: 1rem; font-weight: 700; color: #f8fafc; display: flex; align-items: center; gap: 8px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
              Breadcrumbs Architecture
            </div>
            <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; background: #1e293b; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Structured Data</span>
          </div>
          <p style="font-size: 0.82rem; color: #cbd5e1; line-height: 1.45; margin: 0 0 14px 0;">
            Configure breadcrumbs separator, home text prefix, taxonomy nesting, and auto-generated Google BreadcrumbList JSON-LD schema for rich SERP results.
          </p>
        </div>
        <div style="border-top: 1px solid #1e293b; padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 0.75rem; color: #34d399; font-weight: 600;">JSON-LD Schema Active</span>
          <a href="/admin/seo_breadcrumbs" style="color: #38bdf8; text-decoration: none; font-size: 0.82rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">Customize &rarr;</a>
        </div>
      </div>

      <!-- Module 7: Redirects Manager -->
      <div style="background: #0f172a; border: 1px solid #334155; border-radius: 10px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
            <div style="font-size: 1rem; font-weight: 700; color: #f8fafc; display: flex; align-items: center; gap: 8px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><polyline points="15 14 20 9 15 4"/><path d="M4 20v-7a4 4 0 0 1 4-4h12"/></svg>
              Redirects Manager (301/302)
            </div>
            <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; background: #1e293b; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Traffic Retention</span>
          </div>
          <p style="font-size: 0.82rem; color: #cbd5e1; line-height: 1.45; margin: 0 0 14px 0;">
            Manage permanent 301 and temporary 302 redirects with Regex pattern matching, pass-through query parameters, and prevent 404 dead ends during URL changes.
          </p>
        </div>
        <div style="border-top: 1px solid #1e293b; padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 0.75rem; color: #34d399; font-weight: 600;">Regex Engine Active</span>
          <a href="/admin/seo_redirects" style="color: #38bdf8; text-decoration: none; font-size: 0.82rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">Manage Redirects &rarr;</a>
        </div>
      </div>

      <!-- Module 8: 404 Monitor -->
      <div style="background: #0f172a; border: 1px solid #334155; border-radius: 10px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
            <div style="font-size: 1rem; font-weight: 700; color: #f8fafc; display: flex; align-items: center; gap: 8px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              Real-Time 404 Monitor
            </div>
            <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; background: rgba(248, 113, 113, 0.2); color: #f87171; font-weight: 700; text-transform: uppercase;">Crawl Diagnostics</span>
          </div>
          <p style="font-size: 0.82rem; color: #cbd5e1; line-height: 1.45; margin: 0 0 14px 0;">
            Capture broken URL visits, IPs, user agents, and referring sites in real-time. Turn any 404 error into a 301 redirect with a single click.
          </p>
        </div>
        <div style="border-top: 1px solid #1e293b; padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 600;">1-Click 301 Fix</span>
          <a href="/admin/seo_404" style="color: #38bdf8; text-decoration: none; font-size: 0.82rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">View 404 Logs &rarr;</a>
        </div>
      </div>

      <!-- Module 9: Sitemap & Robots -->
      <div style="background: #0f172a; border: 1px solid #334155; border-radius: 10px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
            <div style="font-size: 1rem; font-weight: 700; color: #f8fafc; display: flex; align-items: center; gap: 8px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
              Sitemap &amp; Robots.txt
            </div>
            <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; background: #1e293b; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Indexation</span>
          </div>
          <p style="font-size: 0.82rem; color: #cbd5e1; line-height: 1.45; margin: 0 0 14px 0;">
            Dynamic XML Sitemap partition for pages, products, categories, and blogs with priority scores, search engine pinging, and custom robots.txt editor.
          </p>
        </div>
        <div style="border-top: 1px solid #1e293b; padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 0.75rem; color: #34d399; font-weight: 600;">Valid sitemap.xml</span>
          <a href="/admin/seo_sitemap" style="color: #38bdf8; text-decoration: none; font-size: 0.82rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">Sitemap &amp; Robots &rarr;</a>
        </div>
      </div>

      <!-- Module 10: Image SEO & Alt Tags -->
      <div style="background: #0f172a; border: 1px solid #334155; border-radius: 10px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
            <div style="font-size: 1rem; font-weight: 700; color: #f8fafc; display: flex; align-items: center; gap: 8px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              Image SEO &amp; Media Alt
            </div>
            <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; background: #1e293b; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Accessibility</span>
          </div>
          <p style="font-size: 0.82rem; color: #cbd5e1; line-height: 1.45; margin: 0 0 14px 0;">
            Audit media uploads for missing image alt text, title tags, captions, and descriptions. Includes WordPress Attachment Details modal and Google Image search audit.
          </p>
        </div>
        <div style="border-top: 1px solid #1e293b; padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 0.75rem; color: #34d399; font-weight: 600;">Media Library Scanner</span>
          <a href="/admin/seo_images" style="color: #38bdf8; text-decoration: none; font-size: 0.82rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">Audit Images &rarr;</a>
        </div>
      </div>

      <!-- Module 11: Content & In-Page SEO Suites -->
      <div style="background: #0f172a; border: 1px solid #334155; border-radius: 10px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
            <div style="font-size: 1rem; font-weight: 700; color: #f8fafc; display: flex; align-items: center; gap: 8px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
              In-Editor Yoast SEO Suites
            </div>
            <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; background: rgba(52, 211, 153, 0.2); color: #34d399; font-weight: 700; text-transform: uppercase;">5-Tab Metabox</span>
          </div>
          <p style="font-size: 0.82rem; color: #cbd5e1; line-height: 1.45; margin: 0 0 14px 0;">
            Full 5-tab Yoast/Rank Math metabox integrated inside every Page, Blog post, and Product editor: SEO Analysis, Readability, Schema, Social, and Advanced robots.
          </p>
        </div>
        <div style="border-top: 1px solid #1e293b; padding-top: 12px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          <a href="/admin/pages" style="color: #38bdf8; text-decoration: none; font-size: 0.78rem; font-weight: 700;">Pages &rarr;</a>
          <span style="color: #475569;">|</span>
          <a href="/admin/blog" style="color: #38bdf8; text-decoration: none; font-size: 0.78rem; font-weight: 700;">Blog &rarr;</a>
          <span style="color: #475569;">|</span>
          <a href="/admin/products" style="color: #38bdf8; text-decoration: none; font-size: 0.78rem; font-weight: 700;">Products &rarr;</a>
        </div>
      </div>

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

