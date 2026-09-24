<?php
// =======================================================================
// VIEW: PRODUCTS CATALOGUE (Server-rendered PHP from Database)
// =======================================================================

$selectedCategory = $_GET['category'] ?? null;
$selectedSeries = $_GET['series'] ?? null;
$searchQuery = $_GET['q'] ?? null;

$allCategories = ProductModel::getCategories();

if ($searchQuery) {
    $skusList = ProductModel::searchProducts($searchQuery);
    $viewTitle = "Search Results for \"" . htmlspecialchars($searchQuery) . "\"";
} elseif ($selectedCategory) {
    $skusList = ProductModel::getSkusByCategory($selectedCategory);
    $catInfo = ProductModel::getCategoryBySlug($selectedCategory);
    $viewTitle = ($catInfo['name'] ?? 'Category') . " Catalogue";
} elseif ($selectedSeries) {
    $skusList = ProductModel::getSkusBySeries($selectedSeries);
    $seriesInfo = ProductModel::getSeriesBySlug($selectedSeries);
    $viewTitle = ($seriesInfo['name'] ?? 'Series') . " Catalogue";
} else {
    $skusList = ProductModel::getSkus();
    $viewTitle = "All Packaging Products";
}
?>

<section class="page-view" id="view-products">
  
  <!-- Products Hero Banner -->
  <div class="products-page-title-banner about-hero-banner" style="padding: 48px 0;">
    <div class="frapak-container">
      <div class="about-hero-badge">TRUENORTH PRODUCTS</div>
      <h1 class="about-hero-title"><?= htmlspecialchars($viewTitle) ?></h1>
      <p class="about-hero-subtitle">Database-Driven Primary Packaging Catalogue (<?= count($skusList) ?> Active Products)</p>
    </div>
  </div>

  <section class="frapak-section" style="padding: 36px 0; background: #f8fafc;">
    <div class="frapak-container">
      
      <!-- Category Filter Tabs -->
      <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 32px; border-bottom: 2px solid #e2e8f0; padding-bottom: 16px;">
        <a href="/products" class="frapak-btn-secondary" style="font-size: 0.85rem; padding: 6px 14px; text-decoration: none; border-radius: 20px; <?= !$selectedCategory && !$selectedSeries && !$searchQuery ? 'background: var(--color-navy); color: #fff;' : '' ?>">
          All Products
        </a>
        <?php foreach ($allCategories as $cat): ?>
          <a href="/products?category=<?= urlencode($cat['slug']) ?>" 
             class="frapak-btn-secondary" 
             style="font-size: 0.85rem; padding: 6px 14px; text-decoration: none; border-radius: 20px; <?= $selectedCategory === $cat['slug'] ? 'background: var(--color-navy); color: #fff;' : '' ?>">
            <?= htmlspecialchars($cat['name']) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- Product Cards Grid -->
      <?php if (empty($skusList)): ?>
        <div style="text-align: center; padding: 48px 20px; background: #fff; border-radius: 12px; border: 1px dashed #cbd5e1;">
          <h3 style="color: #64748b; margin-bottom: 8px;">No products found in database matching your selection.</h3>
          <a href="/products" style="color: var(--color-gold); font-weight: 700; text-decoration: none;">View All Catalogue Products &rarr;</a>
        </div>
      <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 24px;">
          <?php foreach ($skusList as $sku): ?>
            <?php $skuId = $sku['id'] ?? $sku['code'] ?? $sku['articleNo'] ?? 1; ?>
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
              <a href="/product?id=<?= urlencode($skuId) ?>" style="text-decoration: none; color: inherit;">
                <div style="background: #f1f5f9; padding: 20px; text-align: center; position: relative;">
                  <img src="<?= htmlspecialchars($sku['image'] ?? '/logo_svg.svg') ?>" 
                       alt="<?= htmlspecialchars($sku['name'] ?? '') ?>" 
                       style="max-width: 100%; height: 180px; object-fit: contain;" />
                </div>
              </a>

              <div style="padding: 16px; flex-grow: 1; display: flex; flex-direction: column;">
                <span style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px;">
                  <?= htmlspecialchars($sku['categoryName'] ?? 'Packaging') ?>
                </span>
                <a href="/product?id=<?= urlencode($skuId) ?>" style="text-decoration: none; color: var(--color-navy);">
                  <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0 0 8px 0; line-height: 1.3; min-height: 2.6em;">
                    <?= htmlspecialchars($sku['name'] ?? 'Product SKU') ?>
                  </h3>
                </a>

                <p style="font-size: 0.82rem; color: #64748b; margin: 0 0 16px 0; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                  <?= htmlspecialchars($sku['specs']['material'] ?? $sku['material'] ?? 'PET') ?> • <?= htmlspecialchars($sku['specs']['capacity'] ?? $sku['volume'] ?? 'Standard') ?>
                </p>

                <div style="margin-top: auto; display: flex; gap: 8px;">
                  <a href="/product?id=<?= urlencode($skuId) ?>" 
                     style="flex: 1; text-align: center; background: var(--color-navy); color: #fff; font-size: 0.82rem; font-weight: 700; padding: 8px 12px; border-radius: 6px; text-decoration: none;">
                    View Specs &rarr;
                  </a>
                  <button onclick="window.openInquiry('<?= htmlspecialchars($sku['name'] ?? '', ENT_QUOTES) ?>')" 
                          style="background: rgba(204,160,82,0.15); color: var(--color-navy); border: 1px solid var(--color-gold); font-size: 0.82rem; font-weight: 700; padding: 8px 12px; border-radius: 6px; cursor: pointer;">
                    Quote
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </section>

</section>
