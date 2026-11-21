<?php
// =============================================================================
// GENERIC CONTENT PAGE VIEW
// Renders "Default Template" pages (custom slugs with a body, created in
// admin/pages or admin/edit_page). The PageModel record is passed in as
// $pageRecord by PageController::renderPage().
// =============================================================================
$cp = $pageRecord ?? [];
$cpTitle   = $cp['share_title'] ?? ($cp['title'] ?? '');
$cpBanner  = $cp['banner_image'] ?? '';
$cpBannerV = $cp['banner_video'] ?? '';
$cpSub     = $cp['banner_subtitle'] ?? '';
$cpContent = $cp['content'] ?? '';
?>
<section class="page-view" id="view-default">

  <!-- Banner -->
  <section class="tn-hero-banner" style="position: relative; padding: 90px 0; background: #0f172a; overflow: hidden;">
    <?php if (!empty($cpBanner)): ?>
      <img src="<?= htmlspecialchars($cpBanner) ?>" alt=""<?= tn_img_dims($cpBanner) ?>
           style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:.28;" />
    <?php endif; ?>
    <div class="tn-container" style="position:relative;">
      <h1 style="font-size:2.4rem; font-weight:800; color:#f8fafc; margin:0 0 12px; line-height:1.15;">
        <?= htmlspecialchars($cpTitle) ?>
      </h1>
      <?php if (!empty($cpSub)): ?>
        <p style="color:#94a3b8; font-size:1.05rem; max-width:780px; margin:0; line-height:1.6;">
          <?= htmlspecialchars($cpSub) ?>
        </p>
      <?php endif; ?>
    </div>
  </section>

  <!-- Body: admin-authored content (plain paragraphs or template HTML overrides) -->
  <?php if (!empty($cpContent)): ?>
    <section style="padding:64px 0; background:#ffffff; color:#1e293b;">
      <div class="tn-container" style="max-width:880px;">
        <div style="font-size:1.05rem; line-height:1.8; color:#334155;"><?php echo $cpContent; ?></div>
      </div>
    </section>
  <?php endif; ?>

</section>
