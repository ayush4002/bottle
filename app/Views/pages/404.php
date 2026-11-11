<?php
// ==============================================================================
// TRUENORTH GROUP — 404 NOT FOUND VIEW
// Includes live search box, popular packaging category links, and 404 HTTP response.
// ==============================================================================
http_response_code(404);
$popularCats = [
    ['slug' => 'pet-bottles', 'name' => 'PET Bottles'],
    ['slug' => 'jars', 'name' => 'Cosmetic Jars'],
    ['slug' => 'pumps', 'name' => 'Lotion Pumps'],
    ['slug' => 'caps', 'name' => 'Closures & Caps'],
    ['slug' => 'agro-bottles', 'name' => 'Agro & Chemical Bottles']
];
?>
<div id="main-content" tabindex="-1" aria-hidden="true" style="position:absolute; left:0; top:0; width:0; height:0;"></div>
<section class="page-view" id="view-404">
  
  <div class="products-page-title-banner about-hero-banner" style="padding: 48px 0 36px;">
    <div class="tn-container">
      <div class="about-hero-badge" style="background: rgba(220, 38, 38, 0.12); color: #dc2626; border-color: rgba(220, 38, 38, 0.3);">404 ERROR</div>
      <h1 class="about-hero-title"><?= function_exists('seo_h1') ? seo_h1('404 — Page Not Found') : '404 — Page Not Found' ?></h1>
      <p class="about-hero-subtitle">The packaging catalogue page, resource, or document you requested could not be located.</p>
    </div>
  </div>

  <section class="tn-section" style="padding: 60px 0; min-height: 420px; display: flex; align-items: center;">
    <div class="tn-container" style="text-align: center; max-width: 720px; margin: 0 auto;">
      <div style="width: 72px; height: 72px; background: rgba(184, 137, 46, 0.12); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: var(--color-gold);">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      </div>
      <h2 style="font-size: 1.6rem; font-weight: 800; color: var(--color-navy); margin-bottom: 12px;">Looking for Primary Packaging Solutions?</h2>
      <p style="color: var(--color-text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 24px;">
        The requested URL may have moved or expired. Use our catalogue search below, or explore our primary packaging lines:
      </p>

      <!-- Search bar on 404 -->
      <form action="/products" method="GET" style="display: flex; max-width: 520px; margin: 0 auto 28px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); border-radius: 8px; overflow: hidden; border: 1px solid #cbd5e1;">
        <input type="text" name="q" placeholder="Search 290+ bottles, jars, pumps, caps..." style="flex: 1; padding: 12px 16px; border: none; font-size: 0.92rem; outline: none;" required />
        <button type="submit" style="background: var(--color-navy); color: #fff; border: none; padding: 0 22px; font-weight: 700; font-size: 0.88rem; cursor: pointer;">
          Search
        </button>
      </form>

      <!-- Popular Categories -->
      <div style="margin-bottom: 32px;">
        <span style="font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; display: block; margin-bottom: 10px;">Popular Catalogue Categories:</span>
        <div style="display: flex; justify-content: center; gap: 8px; flex-wrap: wrap;">
          <?php foreach ($popularCats as $cat): ?>
            <a href="/products?category=<?= urlencode($cat['slug']) ?>" style="display: inline-block; padding: 6px 14px; background: #f1f5f9; color: var(--color-navy); border: 1px solid #e2e8f0; border-radius: 20px; font-size: 0.84rem; font-weight: 600; text-decoration: none;">
              <?= htmlspecialchars($cat['name']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap;">
        <a href="/products" class="tn-btn-gold" style="margin: 0; padding: 12px 24px;">
          EXPLORE COMPLETE CATALOGUE
        </a>
        <a href="/contact" class="tn-btn-outline" style="margin: 0; padding: 12px 24px; color: var(--color-navy); border-color: var(--color-navy);">
          CONTACT SALES TEAM
        </a>
      </div>
    </div>
  </section>

</section>
