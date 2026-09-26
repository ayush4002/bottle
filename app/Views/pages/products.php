<?php
// =======================================================================
// VIEW: PRODUCTS CATALOGUE (4-Tier Drilldown with Dynamic Smart Filters)
// Level 1: Categories Catalogue (Image 2)
// Level 2: Series within Category (Image 3)
// Level 3: SKUs within Series / Search Results (Image 4)
// =======================================================================

$selectedCategory = $_GET['category'] ?? null;
$selectedSeries = $_GET['series'] ?? null;
$searchQuery = $_GET['q'] ?? null;

$allCategories = ProductModel::getCategories();

// Determine drill-down level
$currentLevel = 'categories';

if ($searchQuery) {
    $currentLevel = 'search';
    $skusList = ProductModel::searchProducts($searchQuery);
    $viewTitle = "Search Results for \"" . htmlspecialchars($searchQuery) . "\"";
    $categoryInfo = null;
    $seriesInfo = null;
} elseif ($selectedSeries) {
    $currentLevel = 'skus'; // Level 3 (Image 4)
    $seriesInfo = ProductModel::getSeriesBySlug($selectedSeries);
    $categorySlug = $seriesInfo['categorySlug'] ?? $selectedCategory ?? 'pet-bottles';
    $categoryInfo = ProductModel::getCategoryBySlug($categorySlug);
    $skusList = ProductModel::getSkusBySeries($selectedSeries);
    $viewTitle = $seriesInfo['name'] ?? 'Series Catalogue';
} elseif ($selectedCategory) {
    $currentLevel = 'series'; // Level 2 (Image 3)
    $categoryInfo = ProductModel::getCategoryBySlug($selectedCategory);
    $seriesList = ProductModel::getSeriesByCategory($selectedCategory);
    if (empty($seriesList)) {
        $skusList = ProductModel::getSkusByCategory($selectedCategory);
        $currentLevel = 'skus';
    }
    $viewTitle = $categoryInfo['name'] ?? 'Category Catalogue';
    $seriesInfo = null;
} else {
    $currentLevel = 'categories'; // Level 1 (Image 2)
    $viewTitle = "Products of TrueNorth Group";
}
?>

<section class="page-view" id="view-products">

  <!-- =====================================================================
       LEVEL 1: CATEGORY CATALOGUE GRID (Matching Image 2)
       ===================================================================== -->
  <?php if ($currentLevel === 'categories'): ?>
    
    <!-- Hero Banner -->
    <div class="products-page-title-banner about-hero-banner" style="padding: 40px 0;">
      <div class="frapak-container">
        <div class="about-hero-badge">TRUENORTH PRODUCTS</div>
        <h1 class="about-hero-title">Products of TrueNorth Group</h1>
        <p class="about-hero-subtitle">Primary Packaging Solutions</p>
      </div>
    </div>

    <section class="frapak-section" style="padding: 36px 0;">
      <div class="frapak-container">
        
        <!-- Search & Quick Navigation Bar -->
        <div style="max-width: 680px; margin: 0 auto 36px;">
          <form action="/products" method="GET" style="display: flex; gap: 8px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); border-radius: 8px; overflow: hidden; background: #fff; border: 1px solid #cbd5e1; padding: 4px;">
            <input type="text" name="q" placeholder="Search 470+ bottles, jars, pumps, caps by name or size..." style="flex: 1; border: none; padding: 12px 16px; font-size: 0.95rem; outline: none;" />
            <button type="submit" class="frapak-btn-gold" style="margin: 0; padding: 12px 24px; border-radius: 6px; font-weight: 700;">
              SEARCH
            </button>
          </form>
        </div>

        <div class="frapak-section-title-wrap" style="text-align: center; margin-bottom: 32px;">
          <h2 class="frapak-section-title" style="font-size: 1.8rem; font-weight: 800; color: var(--color-navy); margin-bottom: 8px;">
            Packaging Categories
          </h2>
          <p style="color: #64748B; font-size: 0.95rem; max-width: 750px; margin: 0 auto; line-height: 1.5;">
            Explore our comprehensive catalogue of PET and HDPE bottles, jars, closures, dispensing pumps, and spray systems.
          </p>
        </div>

        <div class="frapak-level1-grid">
          <?php foreach ($allCategories as $cat): ?>
            <a href="/products?category=<?= urlencode($cat['slug']) ?>" class="frapak-tile-card" style="text-decoration: none;">
              <div class="frapak-tile-image">
                <img src="<?= htmlspecialchars($cat['image'] ?? '/logo_svg.svg') ?>" alt="<?= htmlspecialchars($cat['name']) ?>" loading="lazy" />
              </div>
              <div class="frapak-tile-bar"><?= htmlspecialchars($cat['name']) ?></div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

  <!-- =====================================================================
       LEVEL 2: SERIES WITHIN CATEGORY (Matching Image 3)
       ===================================================================== -->
  <?php elseif ($currentLevel === 'series'): ?>

    <?php
      // Dynamically extract real filter options from current series
      $seriesMaterials = [];
      foreach ($seriesList as $s) {
          $m = strtoupper(trim($s['material'] ?? 'PET'));
          if ($m) $seriesMaterials[$m] = ($seriesMaterials[$m] ?? 0) + 1;
      }
      arsort($seriesMaterials);
    ?>

    <!-- Category Banner -->
    <div class="products-page-title-banner about-hero-banner" style="padding: 40px 0;">
      <div class="frapak-container">
        <div class="about-hero-badge">TRUENORTH PRODUCTS</div>
        <h1 class="about-hero-title"><?= htmlspecialchars($categoryInfo['name'] ?? 'Category') ?></h1>
        <p class="about-hero-subtitle">Premium Primary Packaging Solutions</p>
      </div>
    </div>

    <section class="frapak-section" style="padding: 32px 0;">
      <div class="frapak-container">
        
        <!-- Breadcrumbs & Back -->
        <div class="frapak-breadcrumbs">
          <a href="/products">Products</a>
          <span class="separator">&gt;</span>
          <span class="current"><?= htmlspecialchars($categoryInfo['name'] ?? 'Category') ?></span>
        </div>

        <a href="/products" class="frapak-back-btn">&larr; Back to Categories</a>

        <div class="frapak-catalogue-layout">
          
          <!-- Left Sidebar Filters -->
          <aside class="frapak-filter-sidebar">
            
            <!-- Quick Keyword Filter -->
            <div class="frapak-filter-group" style="padding: 12px 14px;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <label style="font-size: 0.8rem; font-weight: 700; color: var(--color-navy); margin: 0;">Filter Series</label>
                <button type="button" onclick="window.resetCatalogueFilters()" style="background: none; border: none; color: #b8892e; font-size: 0.75rem; font-weight: 700; cursor: pointer; padding: 0;">Reset</button>
              </div>
              <input type="text" id="catalogue-instant-filter" placeholder="Type name, size, type..." oninput="window.filterCatalogueItems()" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; outline: none; box-sizing: border-box;" />
            </div>

            <!-- Material Filter -->
            <?php if (!empty($seriesMaterials)): ?>
              <div class="frapak-filter-group">
                <div class="frapak-filter-header" onclick="window.toggleFilterAccordion(this)">
                  Material <span class="toggle-icon">&#9662;</span>
                </div>
                <div class="frapak-filter-list">
                  <?php foreach ($seriesMaterials as $mat => $cnt): ?>
                    <label class="frapak-filter-item">
                      <input type="checkbox" name="filter-material" value="<?= htmlspecialchars($mat) ?>" onchange="window.filterCatalogueItems()">
                      <?= htmlspecialchars($mat) ?> (<?= $cnt ?>)
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- Action Button -->
            <div style="margin-top: 10px;">
              <button type="button" onclick="window.resetCatalogueFilters()" class="frapak-btn-outline" style="width: 100%; padding: 8px; font-size: 0.82rem; margin: 0; text-align: center;">
                Clear Active Filters
              </button>
            </div>

          </aside>

          <!-- Main Series Grid -->
          <div>
            <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 8px;">
              <h2 style="font-size: 1.55rem; font-weight: 800; color: var(--color-navy); margin: 0;">
                <?= htmlspecialchars($categoryInfo['name'] ?? 'Category') ?>
              </h2>
              <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;" id="catalogue-counter">
                Showing <?= count($seriesList) ?> series
              </span>
            </div>

            <div class="frapak-series-grid" id="catalogue-items-grid">
              <?php foreach ($seriesList as $s): ?>
                <a href="/products?category=<?= urlencode($selectedCategory) ?>&series=<?= urlencode($s['slug']) ?>" 
                   class="frapak-tile-card filterable-item"
                   data-name="<?= strtolower(htmlspecialchars($s['name'] ?? '')) ?>"
                   data-material="<?= strtoupper(htmlspecialchars($s['material'] ?? 'PET')) ?>"
                   data-volume="<?= htmlspecialchars($s['volumeRange'] ?? '') ?>"
                   style="text-decoration: none;">
                  <div class="frapak-tile-image">
                    <img src="<?= htmlspecialchars($s['image'] ?? '/logo_svg.svg') ?>" alt="<?= htmlspecialchars($s['name']) ?>" loading="lazy" />
                  </div>
                  <div class="frapak-tile-bar"><?= htmlspecialchars($s['name']) ?></div>
                </a>
              <?php endforeach; ?>
            </div>

            <!-- Empty filtered state -->
            <div id="no-filter-results" style="display: none; text-align: center; padding: 40px 20px; background: #fff; border-radius: 8px; border: 1px dashed #cbd5e1; margin-top: 16px;">
              <h3 style="font-size: 1.1rem; color: #16273f; margin-bottom: 6px;">No packaging series match your filters</h3>
              <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 16px;">Try adjusting your keyword or clearing checked options.</p>
              <button type="button" onclick="window.resetCatalogueFilters()" class="frapak-btn-gold" style="margin: 0; padding: 8px 18px; font-size: 0.85rem;">Reset Filters</button>
            </div>

          </div>

        </div>

      </div>
    </section>

  <!-- =====================================================================
       LEVEL 3: SKUs WITHIN SERIES / SEARCH RESULTS (Matching Image 4)
       ===================================================================== -->
  <?php elseif ($currentLevel === 'skus' || $currentLevel === 'search'): ?>

    <?php
      // Dynamically extract real filter options from current SKUs
      $skuMaterials = [];
      $skuCapacities = [];
      $skuNecks = [];
      $stockYesCount = 0;
      $stockNoCount = 0;

      foreach ($skusList as $sku) {
          $isStk = !empty($sku['isStock']) || ($sku['stockStatus'] ?? '') === 'in_stock';
          if ($isStk) { $stockYesCount++; } else { $stockNoCount++; }

          $mat = strtoupper(trim($sku['specs']['material'] ?? $sku['material'] ?? 'PET'));
          if ($mat) $skuMaterials[$mat] = ($skuMaterials[$mat] ?? 0) + 1;

          $cap = trim($sku['specs']['capacity'] ?? $sku['volume'] ?? '');
          if ($cap) $skuCapacities[$cap] = ($skuCapacities[$cap] ?? 0) + 1;

          $neck = trim($sku['specs']['neck'] ?? $sku['neck'] ?? '');
          if ($neck) $skuNecks[$neck] = ($skuNecks[$neck] ?? 0) + 1;
      }
      arsort($skuMaterials);
      arsort($skuCapacities);
      arsort($skuNecks);
    ?>

    <!-- Series Banner -->
    <div class="products-page-title-banner about-hero-banner" style="padding: 40px 0;">
      <div class="frapak-container">
        <div class="about-hero-badge"><?= $currentLevel === 'search' ? 'SEARCH RESULTS' : 'TRUENORTH PRODUCTS' ?></div>
        <h1 class="about-hero-title"><?= htmlspecialchars($viewTitle) ?></h1>
        <p class="about-hero-subtitle">
          <?= $currentLevel === 'search' ? 'Showing matches across the full primary packaging catalogue' : 'Premium Primary Packaging Solutions' ?>
        </p>
      </div>
    </div>

    <section class="frapak-section" style="padding: 32px 0;">
      <div class="frapak-container">
        
        <!-- Breadcrumbs & Back -->
        <div class="frapak-breadcrumbs">
          <a href="/products">Products</a>
          <?php if (!empty($categoryInfo)): ?>
            <span class="separator">&gt;</span>
            <a href="/products?category=<?= urlencode($categoryInfo['slug']) ?>"><?= htmlspecialchars($categoryInfo['name']) ?></a>
          <?php endif; ?>
          <?php if (!empty($seriesInfo)): ?>
            <span class="separator">&gt;</span>
            <span class="current"><?= htmlspecialchars($seriesInfo['name']) ?></span>
          <?php endif; ?>
          <?php if ($currentLevel === 'search'): ?>
            <span class="separator">&gt;</span>
            <span class="current">Search: <?= htmlspecialchars($searchQuery) ?></span>
          <?php endif; ?>
        </div>

        <?php 
          $backUrl = !empty($categoryInfo) ? '/products?category=' . urlencode($categoryInfo['slug']) : '/products';
        ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
          <a href="<?= $backUrl ?>" class="frapak-back-btn" style="margin: 0;">&larr; <?= $currentLevel === 'search' ? 'All Categories' : 'Back to Series' ?></a>
          
          <?php if ($currentLevel === 'search'): ?>
            <a href="/products" style="color: #b8892e; font-weight: 700; text-decoration: none; font-size: 0.85rem;">Clear Search &times;</a>
          <?php endif; ?>
        </div>

        <div class="frapak-catalogue-layout">
          
          <!-- Left Sidebar Filters -->
          <aside class="frapak-filter-sidebar">
            
            <!-- Quick Search in Results -->
            <div class="frapak-filter-group" style="padding: 12px 14px;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <label style="font-size: 0.8rem; font-weight: 700; color: var(--color-navy); margin: 0;">Quick Filter</label>
                <button type="button" onclick="window.resetCatalogueFilters()" style="background: none; border: none; color: #b8892e; font-size: 0.75rem; font-weight: 700; cursor: pointer; padding: 0;">Reset</button>
              </div>
              <input type="text" id="catalogue-instant-filter" placeholder="Filter code, capacity, neck..." oninput="window.filterCatalogueItems()" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; outline: none; box-sizing: border-box;" />
            </div>

            <!-- Stock Item Filter -->
            <?php if ($stockYesCount > 0 || $stockNoCount > 0): ?>
              <div class="frapak-filter-group">
                <div class="frapak-filter-header" onclick="window.toggleFilterAccordion(this)">
                  Stock item <span class="toggle-icon">&#9662;</span>
                </div>
                <div class="frapak-filter-list">
                  <?php if ($stockYesCount > 0): ?>
                    <label class="frapak-filter-item">
                      <input type="checkbox" name="filter-stock" value="yes" onchange="window.filterCatalogueItems()">
                      Available Stock (<?= $stockYesCount ?>)
                    </label>
                  <?php endif; ?>
                  <?php if ($stockNoCount > 0): ?>
                    <label class="frapak-filter-item">
                      <input type="checkbox" name="filter-stock" value="no" onchange="window.filterCatalogueItems()">
                      Production Run / Custom (<?= $stockNoCount ?>)
                    </label>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- Material Filter -->
            <?php if (!empty($skuMaterials)): ?>
              <div class="frapak-filter-group">
                <div class="frapak-filter-header" onclick="window.toggleFilterAccordion(this)">
                  Material <span class="toggle-icon">&#9662;</span>
                </div>
                <div class="frapak-filter-list">
                  <?php foreach ($skuMaterials as $mat => $cnt): ?>
                    <label class="frapak-filter-item">
                      <input type="checkbox" name="filter-material" value="<?= htmlspecialchars($mat) ?>" onchange="window.filterCatalogueItems()">
                      <?= htmlspecialchars($mat) ?> (<?= $cnt ?>)
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- Capacity / Volume Filter -->
            <?php if (!empty($skuCapacities) && count($skuCapacities) > 1): ?>
              <div class="frapak-filter-group">
                <div class="frapak-filter-header" onclick="window.toggleFilterAccordion(this)">
                  Capacity / Volume <span class="toggle-icon">&#9662;</span>
                </div>
                <div class="frapak-filter-list">
                  <?php $capSlice = array_slice($skuCapacities, 0, 15, true); ?>
                  <?php foreach ($capSlice as $cap => $cnt): ?>
                    <label class="frapak-filter-item">
                      <input type="checkbox" name="filter-volume" value="<?= htmlspecialchars($cap) ?>" onchange="window.filterCatalogueItems()">
                      <?= htmlspecialchars($cap) ?> (<?= $cnt ?>)
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- Neck Finish Filter -->
            <?php if (!empty($skuNecks) && count($skuNecks) > 1): ?>
              <div class="frapak-filter-group">
                <div class="frapak-filter-header" onclick="window.toggleFilterAccordion(this)">
                  Neck Finish <span class="toggle-icon">&#9662;</span>
                </div>
                <div class="frapak-filter-list">
                  <?php $neckSlice = array_slice($skuNecks, 0, 12, true); ?>
                  <?php foreach ($neckSlice as $n => $cnt): ?>
                    <label class="frapak-filter-item">
                      <input type="checkbox" name="filter-neck" value="<?= htmlspecialchars($n) ?>" onchange="window.filterCatalogueItems()">
                      <?= htmlspecialchars($n) ?> (<?= $cnt ?>)
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- Action Button -->
            <div style="margin-top: 10px;">
              <button type="button" onclick="window.resetCatalogueFilters()" class="frapak-btn-outline" style="width: 100%; padding: 8px; font-size: 0.82rem; margin: 0; text-align: center;">
                Clear Active Filters
              </button>
            </div>

          </aside>

          <!-- Main SKUs Grid (Matches Image 4) -->
          <div>
            <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 8px;">
              <h2 style="font-size: 1.55rem; font-weight: 800; color: var(--color-navy); margin: 0;">
                <?= htmlspecialchars($viewTitle) ?>
              </h2>
              <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;" id="catalogue-counter">
                Showing <?= count($skusList) ?> items
              </span>
            </div>

            <?php if (empty($skusList)): ?>
              <div style="text-align: center; padding: 48px 20px; background: #fff; border-radius: 12px; border: 1px dashed #cbd5e1;">
                <h3 style="color: #64748b; margin-bottom: 8px;">No packaging items found matching "<?= htmlspecialchars($searchQuery ?? '') ?>".</h3>
                <a href="/products" style="color: var(--color-gold); font-weight: 700; text-decoration: none;">View All Catalogue Categories &rarr;</a>
              </div>
            <?php else: ?>
              <div class="frapak-skus-grid" id="catalogue-items-grid">
                <?php foreach ($skusList as $sku): ?>
                  <?php 
                    $skuId = $sku['id'] ?? $sku['articleNo'] ?? 1;
                    $isStock = !empty($sku['isStock']) || ($sku['stockStatus'] ?? '') === 'in_stock';
                    $materialVal = $sku['specs']['material'] ?? $sku['material'] ?? 'PET';
                    $volumeVal = $sku['specs']['capacity'] ?? $sku['volume'] ?? '';
                    $neckVal = $sku['specs']['neck'] ?? $sku['neck'] ?? '';
                    $shapeVal = $sku['specs']['shape'] ?? $sku['shape'] ?? 'Cylindrical';
                    $colorVal = $sku['specs']['color'] ?? $sku['color'] ?? 'White / Natural';
                    $moqVal = $sku['specs']['moq'] ?? $sku['moq'] ?? '10,000';
                    $artNo = $sku['articleNo'] ?? $sku['code'] ?? 'TN-001';
                  ?>
                  <a href="/product?id=<?= urlencode($skuId) ?>" 
                     class="frapak-sku-card filterable-item"
                     data-name="<?= strtolower(htmlspecialchars($sku['name'] ?? '')) ?>"
                     data-code="<?= strtolower(htmlspecialchars($artNo)) ?>"
                     data-stock="<?= $isStock ? 'yes' : 'no' ?>"
                     data-material="<?= strtoupper(htmlspecialchars($materialVal)) ?>"
                     data-volume="<?= htmlspecialchars($volumeVal) ?>"
                     data-neck="<?= htmlspecialchars($neckVal) ?>"
                     style="text-decoration: none;">
                    
                    <?php if ($isStock): ?>
                      <div class="frapak-sku-stock-indicator" title="Stock Available"></div>
                    <?php endif; ?>

                    <div class="frapak-sku-img">
                      <img src="<?= htmlspecialchars($sku['image'] ?? '/logo_svg.svg') ?>" alt="<?= htmlspecialchars($sku['name'] ?? '') ?>" loading="lazy" />
                    </div>
                    <div class="frapak-sku-bar"><?= htmlspecialchars($sku['name'] ?? 'Product SKU') ?></div>

                    <div class="frapak-sku-specs-table">
                      <div class="frapak-sku-spec-row">
                        <span class="frapak-sku-spec-icon">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 5v14M8 5v14M12 5v14M17 5v14M21 5v14"/></svg>
                        </span>
                        <span class="frapak-sku-spec-val"><?= htmlspecialchars($artNo) ?></span>
                      </div>
                      <div class="frapak-sku-spec-row">
                        <span class="frapak-sku-spec-icon">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 19H4.815a1.83 1.83 0 0 1-1.57-.881 1.785 1.785 0 0 1-.004-1.784L7.196 9.5M11 19h8.2a1.8 1.8 0 0 0 1.583-.935 1.777 1.777 0 0 0-.015-1.778L16.8 9.5M12 5l3.89 6H8.11L12 5z"/></svg>
                        </span>
                        <span class="frapak-sku-spec-val"><?= htmlspecialchars($materialVal) ?></span>
                      </div>
                      <div class="frapak-sku-spec-row">
                        <span class="frapak-sku-spec-icon">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 3h6v3H9zM10 6v3a4 4 0 0 1-2 3.46V20a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-7.54A4 4 0 0 1 14 9V6"/></svg>
                        </span>
                        <span class="frapak-sku-spec-val"><?= htmlspecialchars($volumeVal ?: $shapeVal) ?></span>
                      </div>
                      <div class="frapak-sku-spec-row">
                        <span class="frapak-sku-spec-icon">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                        </span>
                        <span class="frapak-sku-spec-val"><?= htmlspecialchars($neckVal ?: $colorVal) ?></span>
                      </div>
                      <div class="frapak-sku-spec-row">
                        <span class="frapak-sku-spec-icon">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                        </span>
                        <span class="frapak-sku-spec-val">MOQ <?= htmlspecialchars($moqVal) ?></span>
                      </div>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>

              <!-- Empty filtered state -->
              <div id="no-filter-results" style="display: none; text-align: center; padding: 40px 20px; background: #fff; border-radius: 8px; border: 1px dashed #cbd5e1; margin-top: 16px;">
                <h3 style="font-size: 1.1rem; color: #16273f; margin-bottom: 6px;">No packaging items match your filters</h3>
                <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 16px;">Try adjusting your search keyword or clearing checked filters.</p>
                <button type="button" onclick="window.resetCatalogueFilters()" class="frapak-btn-gold" style="margin: 0; padding: 8px 18px; font-size: 0.85rem;">Reset Filters</button>
              </div>

            <?php endif; ?>
          </div>

        </div>

      </div>
    </section>

  <?php endif; ?>

</section>

<!-- =========================================================================
     SMART DYNAMIC CATALOGUE FILTER ENGINE
     ========================================================================= -->
<script>
window.toggleFilterAccordion = function(header) {
  const list = header.nextElementSibling;
  const icon = header.querySelector('.toggle-icon');
  if (list) {
    if (list.style.display === 'none') {
      list.style.display = 'flex';
      if (icon) icon.innerHTML = '&#9662;';
    } else {
      list.style.display = 'none';
      if (icon) icon.innerHTML = '&#9656;';
    }
  }
};

window.resetCatalogueFilters = function() {
  const instantInput = document.getElementById('catalogue-instant-filter');
  if (instantInput) instantInput.value = '';

  document.querySelectorAll('.frapak-filter-sidebar input[type="checkbox"]').forEach(function(cb) {
    cb.checked = false;
  });

  window.filterCatalogueItems();
};

window.filterCatalogueItems = function() {
  const instantInput = document.getElementById('catalogue-instant-filter');
  const query = instantInput ? instantInput.value.trim().toLowerCase() : '';

  const stockChecked = Array.from(document.querySelectorAll('.frapak-filter-sidebar input[name="filter-stock"]:checked')).map(i => i.value.toLowerCase());
  const materialChecked = Array.from(document.querySelectorAll('.frapak-filter-sidebar input[name="filter-material"]:checked')).map(i => i.value.toUpperCase());
  const volumeChecked = Array.from(document.querySelectorAll('.frapak-filter-sidebar input[name="filter-volume"]:checked')).map(i => i.value.toLowerCase());
  const neckChecked = Array.from(document.querySelectorAll('.frapak-filter-sidebar input[name="filter-neck"]:checked')).map(i => i.value.toLowerCase());

  const items = document.querySelectorAll('.filterable-item');
  let visibleCount = 0;

  items.forEach(function(item) {
    let show = true;

    const name = item.getAttribute('data-name') || '';
    const code = item.getAttribute('data-code') || '';
    const stock = (item.getAttribute('data-stock') || '').toLowerCase();
    const mat = (item.getAttribute('data-material') || '').toUpperCase();
    const vol = (item.getAttribute('data-volume') || '').toLowerCase();
    const neck = (item.getAttribute('data-neck') || '').toLowerCase();

    // 1. Text Search Filter
    if (query) {
      const matchQuery = name.includes(query) || code.includes(query) || mat.toLowerCase().includes(query) || vol.includes(query) || neck.includes(query);
      if (!matchQuery) show = false;
    }

    // 2. Stock Filter
    if (show && stockChecked.length > 0) {
      if (!stockChecked.includes(stock)) show = false;
    }

    // 3. Material Filter
    if (show && materialChecked.length > 0) {
      const matMatch = materialChecked.some(m => mat.includes(m));
      if (!matMatch) show = false;
    }

    // 4. Volume / Capacity Filter
    if (show && volumeChecked.length > 0) {
      const volMatch = volumeChecked.some(v => vol.includes(v) || v.includes(vol));
      if (!volMatch) show = false;
    }

    // 5. Neck Finish Filter
    if (show && neckChecked.length > 0) {
      const neckMatch = neckChecked.some(n => neck.includes(n));
      if (!neckMatch) show = false;
    }

    item.style.display = show ? '' : 'none';
    if (show) visibleCount++;
  });

  const counter = document.getElementById('catalogue-counter');
  if (counter) {
    counter.innerText = 'Showing ' + visibleCount + ' item' + (visibleCount === 1 ? '' : 's');
  }

  const noResultsBox = document.getElementById('no-filter-results');
  if (noResultsBox) {
    noResultsBox.style.display = (visibleCount === 0 && items.length > 0) ? 'block' : 'none';
  }
};
</script>
