<?php
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/TestimonialModel.php';

checkAdminAuth();

TestimonialModel::initStorage();
$rows = TestimonialModel::getAll(false);
$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';
$publishedCount = count(array_filter($rows, function ($t) { return ($t['status'] ?? 'published') === 'published'; }));
?>

<div style="max-width: 1200px; margin: 0 auto;">

  <!-- PAGE HEADER & ACTION -->
  <div class="dash-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
      <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0 0 6px 0; color: #f8fafc;">Testimonials &amp; Client Logos</h1>
      <p style="color: #94a3b8; font-size: 0.9rem; margin: 0;">Manage client quotes and the logo wall shown on the home page. Replace placeholder entries with genuine, verifiable client endorsements.</p>
    </div>
    <a href="/admin/testimonial_editor" class="tn-btn-gold" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; padding: 12px 22px; border-radius: 8px; font-weight: 700; font-size: 0.9rem; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35);">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add Testimonial / Logo
    </a>
  </div>

  <?php if (!empty($msg)): ?>
    <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      <span><?= htmlspecialchars($msg) ?></span>
    </div>
  <?php endif; ?>

  <?php if (!empty($err)): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <span><?= htmlspecialchars($err) ?></span>
    </div>
  <?php endif; ?>

  <!-- STATS CARDS -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
      <div style="font-size: 0.8rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Total Entries</div>
      <div style="font-size: 2rem; font-weight: 800; color: #f8fafc;"><?= count($rows) ?></div>
    </div>
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
      <div style="font-size: 0.8rem; font-weight: 700; color: #34d399; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Published</div>
      <div style="font-size: 2rem; font-weight: 800; color: #34d399;"><?= $publishedCount ?></div>
    </div>
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
      <div style="font-size: 0.8rem; font-weight: 700; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Quotes / Logos</div>
      <div style="font-size: 2rem; font-weight: 800; color: #38bdf8;"><?= count(array_filter($rows, function($t){ return empty($t['is_logo']); })) ?> / <?= count(array_filter($rows, function($t){ return !empty($t['is_logo']); })) ?></div>
    </div>
  </div>

  <!-- ENTRIES TABLE -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
        <thead>
          <tr style="background: #0f172a; border-bottom: 1px solid #334155; color: #94a3b8; font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.06em;">
            <th style="padding: 14px 18px;">Preview</th>
            <th style="padding: 14px 18px;">Client / Company</th>
            <th style="padding: 14px 18px;">Type</th>
            <th style="padding: 14px 18px;">Status</th>
            <th style="padding: 14px 18px; text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rows)): ?>
            <tr><td colspan="5" style="padding: 48px; text-align: center; color: #94a3b8;">
              <p style="font-size: 1.1rem; margin: 0 0 10px 0;">No entries yet.</p>
              <a href="/admin/testimonial_editor" class="tn-btn-gold" style="display: inline-block; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 700;">+ Add First Entry</a>
            </td></tr>
          <?php else: ?>
            <?php foreach ($rows as $t):
                $tId = $t['id'] ?? 0;
                $isLogo = !empty($t['is_logo']);
                $status = $t['status'] ?? 'published';
                $label = $isLogo ? ($t['company'] ?: 'Logo entry') : (($t['client_name'] ?: 'Quote') . ($t['company'] ? ' — ' . $t['company'] : ''));
            ?>
              <tr style="border-bottom: 1px solid #334155;">
                <td style="padding: 14px 18px; vertical-align: top;">
                  <?php if ($isLogo && !empty($t['logo_image'])): ?>
                    <img src="<?= htmlspecialchars($t['logo_image']) ?>" alt="" style="width: 52px; height: 52px; object-fit: contain; border-radius: 8px; border: 1px solid #475569; background: #0f172a;" onerror="this.src='/logo_svg.svg'" />
                  <?php else: ?>
                    <div style="width: 52px; height: 52px; border-radius: 8px; background: #0f172a; border: 1px solid #475569; display: flex; align-items: center; justify-content: center; color: #d4a754; font-weight: 800;">
                      <?= htmlspecialchars(strtoupper(substr($t['client_name'] ?: '?', 0, 1))) ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td style="padding: 14px 18px; vertical-align: top; max-width: 420px;">
                  <div style="font-weight: 700; color: #f8fafc; margin-bottom: 3px;"><?= htmlspecialchars($label) ?></div>
                  <?php if (!$isLogo && !empty($t['quote'])): ?>
                    <div style="font-size: 0.8rem; color: #94a3b8; line-height: 1.45;">&ldquo;<?= htmlspecialchars(mb_strimwidth($t['quote'], 0, 90, '&hellip;')) ?>&rdquo;</div>
                  <?php endif; ?>
                </td>
                <td style="padding: 14px 18px; vertical-align: top; white-space: nowrap;">
                  <span style="background: rgba(14, 165, 233, 0.12); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.3); padding: 4px 9px; border-radius: 6px; font-size: 0.76rem; font-weight: 600;">
                    <?= $isLogo ? 'Client Logo' : 'Testimonial' ?>
                  </span>
                </td>
                <td style="padding: 14px 18px; vertical-align: top; white-space: nowrap;">
                  <?php if ($status === 'published'): ?>
                    <span style="background: rgba(52, 211, 153, 0.15); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.3); padding: 4px 8px; border-radius: 6px; font-size: 0.74rem; font-weight: 700; text-transform: uppercase;">Live</span>
                  <?php else: ?>
                    <span style="background: rgba(148, 163, 184, 0.15); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.3); padding: 4px 8px; border-radius: 6px; font-size: 0.74rem; font-weight: 700; text-transform: uppercase;">Draft</span>
                  <?php endif; ?>
                </td>
                <td style="padding: 14px 18px; vertical-align: top; text-align: right; white-space: nowrap;">
                  <div style="display: inline-flex; align-items: center; gap: 8px;">
                    <a href="/admin/testimonial_editor?id=<?= $tId ?>" style="color: #f8fafc; padding: 6px 12px; background: #334155; border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: 600;">Edit</a>
                    <a href="/admin/delete_testimonial?id=<?= $tId ?>&csrf_token=<?= $csrfToken ?>" onclick="return confirm('Delete this entry permanently?')" style="color: #f87171; padding: 6px 10px; background: rgba(239, 68, 68, 0.1); border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: 600;">Delete</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <p style="color: #94a3b8; font-size: 0.8rem; margin-top: 14px;">
    Note: Review/AggregateRating schema is only emitted when genuine reviews exist. Placeholder entries are safe to display but should be replaced with verifiable client endorsements.
  </p>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
