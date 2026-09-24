<?php
// ==========================================================================
// VIEW: PRODUCT DETAIL PAGE (Server-rendered from Database)
// ==========================================================================
?>

<div class="products-page-title-banner about-hero-banner" style="padding: 40px 0;">
  <div class="frapak-container">
    <div class="about-hero-badge">PRODUCT CATALOGUE</div>
    <h1 class="about-hero-title" style="font-size: 2.2rem;"><?= htmlspecialchars($sku['name'] ?? 'Packaging Product') ?></h1>
    <p class="about-hero-subtitle">Article No: <code><?= htmlspecialchars($sku['articleNo'] ?? $sku['code'] ?? 'TN-001') ?></code></p>
  </div>
</div>

<section class="frapak-section" style="padding: 48px 0; background: #fff;">
  <div class="frapak-container">
    <div style="margin-bottom: 24px;">
      <a href="/products" style="color: var(--color-gold); text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
        &larr; Back to Products Catalogue
      </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 48px; align-items: start;">
      
      <!-- Product Image -->
      <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; text-align: center;">
        <img src="<?= htmlspecialchars($sku['image'] ?? '/logo_svg.svg') ?>" 
             alt="<?= htmlspecialchars($sku['name'] ?? 'Product') ?>" 
             style="max-width: 100%; max-height: 420px; object-fit: contain; border-radius: 8px;" />
      </div>

      <!-- Product Information & Database Specs -->
      <div>
        <span style="display: inline-block; background: rgba(204,160,82,0.15); color: var(--color-navy); font-weight: 700; font-size: 0.8rem; padding: 4px 12px; border-radius: 20px; margin-bottom: 12px;">
          <?= htmlspecialchars($sku['categoryName'] ?? 'Primary Packaging') ?>
        </span>
        <h2 style="font-size: 1.8rem; font-weight: 800; color: var(--color-navy); margin: 0 0 16px 0;"><?= htmlspecialchars($sku['name'] ?? '') ?></h2>
        
        <p style="color: #475569; font-size: 1rem; line-height: 1.6; margin-bottom: 24px;">
          <?= htmlspecialchars($sku['desc'] ?? $sku['specs']['desc'] ?? 'High quality primary packaging component manufactured under strict cleanroom conditions.') ?>
        </p>

        <!-- Technical Specifications Table -->
        <div style="background: #f1f5f9; border-radius: 8px; padding: 20px; margin-bottom: 28px;">
          <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-navy); margin-top: 0; margin-bottom: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Technical Specifications</h3>
          
          <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
            <tr>
              <td style="padding: 8px 0; border-bottom: 1px solid #cbd5e1; color: #64748b; font-weight: 600;">Material:</td>
              <td style="padding: 8px 0; border-bottom: 1px solid #cbd5e1; color: #0f172a; font-weight: 700; text-align: right;"><?= htmlspecialchars($sku['specs']['material'] ?? $sku['material'] ?? 'PET / HDPE') ?></td>
            </tr>
            <tr>
              <td style="padding: 8px 0; border-bottom: 1px solid #cbd5e1; color: #64748b; font-weight: 600;">Capacity / Volume:</td>
              <td style="padding: 8px 0; border-bottom: 1px solid #cbd5e1; color: #0f172a; font-weight: 700; text-align: right;"><?= htmlspecialchars($sku['specs']['capacity'] ?? $sku['volume'] ?? '100ml - 1000ml') ?></td>
            </tr>
            <tr>
              <td style="padding: 8px 0; border-bottom: 1px solid #cbd5e1; color: #64748b; font-weight: 600;">Neck Finish / Size:</td>
              <td style="padding: 8px 0; border-bottom: 1px solid #cbd5e1; color: #0f172a; font-weight: 700; text-align: right;"><?= htmlspecialchars($sku['specs']['neck'] ?? $sku['neckRange'] ?? '24/410, 28/410') ?></td>
            </tr>
            <tr>
              <td style="padding: 8px 0; color: #64748b; font-weight: 600;">Shape / Profile:</td>
              <td style="padding: 8px 0; color: #0f172a; font-weight: 700; text-align: right;"><?= htmlspecialchars($sku['specs']['shape'] ?? $sku['shape'] ?? 'Cylindrical / Round') ?></td>
            </tr>
          </table>
        </div>

        <!-- Call to Action -->
        <button onclick="window.openInquiry('<?= htmlspecialchars($sku['name'] ?? '', ENT_QUOTES) ?>')" class="frapak-btn-gold" style="padding: 14px 28px; font-size: 1rem; border-radius: 8px; width: 100%;">
          REQUEST BULK QUOTE FOR THIS PRODUCT &rarr;
        </button>
      </div>

    </div>
  </div>
</section>
