<?php
// ==========================================================================
// VIEW: PRODUCT DETAIL PAGE (Matching Image 5)
// ==========================================================================

$gallery = !empty($sku['gallery']) && is_array($sku['gallery']) ? $sku['gallery'] : (!empty($sku['image']) ? [$sku['image']] : ['/logo_svg.svg']);
$primaryImg = $gallery[0] ?? ($sku['image'] ?? '/logo_svg.svg');
$catName = $categoryInfo['name'] ?? ($sku['categoryName'] ?? 'Packaging');
$catSlug = $categoryInfo['slug'] ?? ($sku['categorySlug'] ?? 'pet-bottles');
$seriesName = $seriesInfo['name'] ?? ($sku['serie'] ?? 'Series');
$seriesSlug = $seriesInfo['slug'] ?? ($sku['seriesSlug'] ?? '');
?>

<!-- Product Banner -->
<div class="products-page-title-banner about-hero-banner" style="padding: 40px 0;">
  <div class="tn-container">
    <div class="about-hero-badge">TRUENORTH PRODUCTS</div>
    <h1 class="about-hero-title"><?= htmlspecialchars($sku['fullTitle'] ?? $sku['name'] ?? 'Product Detail') ?></h1>
    <p class="about-hero-subtitle">Premium Primary Packaging Solutions</p>
  </div>
</div>

<section class="tn-section" style="padding: 32px 0; background: #ffffff;">
  <div class="tn-container">

    <!-- Top Heading Matching Image 5 -->
    <h2 style="font-size: 1.6rem; font-weight: 800; color: var(--color-navy); margin-bottom: 12px; line-height: 1.35;">
      <?= htmlspecialchars($sku['fullTitle'] ?? $sku['name'] ?? 'Product Detail') ?>
    </h2>

    <!-- Back Button -->
    <a href="/products?category=<?= urlencode($catSlug) ?>" class="tn-back-btn">
      Back
    </a>

    <!-- Breadcrumbs -->
    <div class="tn-breadcrumbs">
      <a href="/products">Products</a>
      <span class="separator">&gt;</span>
      <a href="/products?category=<?= urlencode($catSlug) ?>"><?= htmlspecialchars($catName) ?></a>
      <span class="separator">&gt;</span>
      <span class="current"><?= htmlspecialchars($sku['name'] ?? 'Product') ?></span>
    </div>

    <!-- 2-Column Product Detail Layout (Matching Image 5) -->
    <div class="tn-pdp-layout">
      
      <!-- Left Column: Image Stage & Vertical Thumbnails -->
      <div class="tn-pdp-gallery-container">
        
        <!-- Main Large Image Viewport -->
        <div class="tn-pdp-main-stage">
          <button class="tn-pdp-zoom-btn" onclick="window.open(document.getElementById('pdp-active-display-img').src, '_blank')" title="Enlarge image" style="display: inline-flex; align-items: center; justify-content: center; gap: 4px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><path d="M11 8v6M8 11h6"/></svg>
          </button>
          
          <img id="pdp-active-display-img" src="<?= htmlspecialchars($primaryImg) ?>" alt="<?= htmlspecialchars($sku['name'] ?? 'Product') ?>" onerror="this.onerror=null;this.src='/logo_svg.svg';" />
        </div>

        <!-- Vertical Thumbnails Column -->
        <div class="tn-pdp-thumbs-col">
          <?php foreach ($gallery as $idx => $gUrl): ?>
            <div class="tn-pdp-thumb-item <?= $idx === 0 ? 'active' : '' ?>" onclick="window.switchPdpImage(this, '<?= htmlspecialchars($gUrl, ENT_QUOTES) ?>')">
              <img src="<?= htmlspecialchars($gUrl) ?>" alt="Thumb <?= $idx + 1 ?>" onerror="this.onerror=null;this.src='/logo_svg.svg';" />
            </div>
          <?php endforeach; ?>
        </div>

      </div>

      <!-- Right Column: Specs Table (Matching Image 5) -->
      <div class="tn-pdp-specs-card">
        <table>
          <tbody>
            <tr>
              <td>Material</td>
              <td><?= htmlspecialchars($sku['specs']['material'] ?? $sku['material'] ?? 'PET') ?></td>
            </tr>
            <tr>
              <td>Article no.</td>
              <td><?= htmlspecialchars($sku['articleNo'] ?? $sku['code'] ?? 'TN-001') ?></td>
            </tr>
            <tr>
              <td>Neck size</td>
              <td><?= htmlspecialchars($sku['specs']['neck'] ?? $sku['neckRange'] ?? $sku['neckSize'] ?? 'Standard') ?></td>
            </tr>
            <tr>
              <td>Shape</td>
              <td><?= htmlspecialchars($sku['specs']['shape'] ?? $sku['shape'] ?? 'Cylindrical') ?></td>
            </tr>
            <tr>
              <td>Size</td>
              <td><?= htmlspecialchars($sku['specs']['capacity'] ?? $sku['volume'] ?? 'Standard') ?></td>
            </tr>
            <?php 
              $rawWeight = trim($sku['specs']['weight'] ?? $sku['weight'] ?? '');
              $hasWeight = !empty($rawWeight) && !in_array(strtoupper($rawWeight), ['N/A', 'NA', 'STANDARD', 'NONE', '-', 'NULL']);
            ?>
            <?php if ($hasWeight): ?>
            <tr>
              <td>Weight</td>
              <td><?= htmlspecialchars($rawWeight) ?></td>
            </tr>
            <?php endif; ?>
            <?php
              $defaultColor = 'Clear / White / Custom';
              if ($catSlug === 'jars') $defaultColor = 'Clear / Amber / Custom';
              elseif ($catSlug === 'pharma-bottles') $defaultColor = 'Amber / Clear / Custom';
              elseif ($catSlug === 'agro-bottles') $defaultColor = 'White / Amber / Custom';
              $displayColor = !empty($sku['specs']['color']) ? $sku['specs']['color'] : (!empty($sku['color']) ? $sku['color'] : $defaultColor);
            ?>
            <tr>
              <td>Colour</td>
              <td><?= htmlspecialchars($displayColor) ?></td>
            </tr>
          </tbody>
        </table>

        <!-- Action Buttons -->
        <div class="tn-pdp-cta-wrap">
          <button class="tn-btn-gold" style="margin: 0; padding: 12px 28px; background: #15803d; border-color: #15803d; color: #fff; font-weight: 700; border-radius: 6px; cursor: pointer;" onclick="window.openInquiry('<?= addslashes($sku['name'] ?? '') ?>')">
            REQUEST A QUOTE
          </button>
          <button class="tn-btn-outline" style="margin: 0; padding: 12px 24px; background: #B8892E; border-color: #B8892E; color: #fff; font-weight: 700; border-radius: 6px; cursor: pointer;" onclick="window.openInquiry('Sample Request: <?= addslashes($sku['name'] ?? '') ?>')">
            REQUEST A SAMPLE
          </button>
        </div>

        <!-- Detailed Description & Compatible Closures -->
        <div style="margin-top: 28px; border-top: 1px solid #E5E7EB; padding-top: 20px;">
          <h4 style="font-size: 0.9375rem; font-weight: 700; color: var(--color-navy); margin-bottom: 6px;">Product Overview</h4>
          <p style="font-size: 0.8125rem; color: #555; line-height: 1.6; margin-bottom: 14px;">
            <?= htmlspecialchars($sku['description'] ?? $sku['shortDescription'] ?? 'High quality primary packaging component manufactured under strict cleanroom conditions.') ?>
          </p>

          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 14px; border-radius: 8px;">
            <strong style="font-size: 0.8125rem; color: var(--color-navy); display: block; margin-bottom: 4px;">
              Compatible Closures & Pumps:
            </strong>
            <?php
              $neckVal = htmlspecialchars($sku['specs']['neck'] ?? $sku['neckRange'] ?? $sku['neckSize'] ?? 'standard');
              $rawClosures = trim($sku['compatibleClosures'] ?? '');
              $isGenericFiller = empty($rawClosures) || stripos($rawClosures, 'Standard matching closures and dispensing pumps available') !== false;
              
              if (!$isGenericFiller) {
                  $displayClosures = $rawClosures;
              } else {
                  if ($catSlug === 'pumps' || $catSlug === 'sprays') {
                      $displayClosures = "Engineered for direct mounting onto {$neckVal} finish PET and HDPE bottles. Available with smooth or ribbed collars and protective dust caps.";
                  } elseif ($catSlug === 'caps') {
                      $displayClosures = "Compatible with {$neckVal} neck bottles and containers. Sealing liner options include EPE foam, induction heat seal (IHS), and pressure-sensitive liners.";
                  } elseif ($catSlug === 'jars') {
                      $displayClosures = "Pairs with {$neckVal} continuous thread (CT) wide-mouth closures, wad seal liners, and cosmetic induction foil seals.";
                  } else {
                      $displayClosures = "Precision {$neckVal} neck finish compatible with lotion pumps, fine-mist sprayers, flip-top dispensing caps, and tamper-evident screw closures.";
                  }
              }
            ?>
            <span style="font-size: 0.8125rem; color: #475569;">
              <?= htmlspecialchars($displayClosures) ?>
            </span>
          </div>
        </div>

      </div>

    </div>

  </div>
</section>

<script>
window.switchPdpImage = function(thumbElem, imgUrl) {
  const displayImg = document.getElementById('pdp-active-display-img');
  if (displayImg && imgUrl) {
    displayImg.src = imgUrl;
  }
  document.querySelectorAll('.tn-pdp-thumb-item').forEach(t => t.classList.remove('active'));
  if (thumbElem) thumbElem.classList.add('active');
};
</script>
