<?php
// =======================================================================
// VIEW: PRODUCTS CATALOGUE (4-Tier Drilldown with Dynamic Smart Filters)
// Level 1: Categories Catalogue (Image 2)
// Level 2: Series within Category (Image 3)
// Level 3: SKUs within Series / Search Results (Image 4)
// =======================================================================

$selectedCategory = $_GET['category'] ?? null;
$selectedSeries = $_GET['series'] ?? null;
$selectedIndustry = $_GET['industry'] ?? null;
$searchQuery = $_GET['q'] ?? null;

$allCategories = ProductModel::getCategories();
$allIndustries = ProductModel::getIndustries();

// Determine drill-down level
$currentLevel = 'categories';
$industryInfo = null;

if ($searchQuery) {
    $currentLevel = 'search';
    $searchTaxMatch = ProductModel::getSearchTaxonomyMatch($searchQuery);
    $skusList = ProductModel::searchProducts($searchQuery);
    if (!empty($searchTaxMatch['name'])) {
        $viewTitle = "Search: " . $searchTaxMatch['name'] . " (\"" . $searchQuery . "\")";
        $viewSubtitle = "Showing " . count($skusList) . " products matching \"" . $searchQuery . "\" across the " . $searchTaxMatch['name'] . " category";
    } else {
        $viewTitle = "Search Results for \"" . $searchQuery . "\"";
        $viewSubtitle = "Showing " . count($skusList) . " matches across the primary packaging catalogue";
    }
    $categoryInfo = null;
    $seriesInfo = null;
} elseif ($selectedIndustry) {
    $currentLevel = 'industry';
    $industryInfo = ProductModel::getIndustryBySlug($selectedIndustry);
    if ($industryInfo) {
        $skusList = ProductModel::getSkusByIndustry($selectedIndustry);
        $viewTitle = $industryInfo['name'];
        $viewSubtitle = $industryInfo['desc'];
    } else {
        $skusList = ProductModel::getSkus();
        $viewTitle = "Industry Packaging Catalogue";
        $viewSubtitle = "Showing packaging solutions across all primary industry markets";
    }
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
    $currentLevel = 'skus';
    $categoryInfo = ProductModel::getCategoryBySlug($selectedCategory);
    $skusList = ProductModel::getSkusByCategory($selectedCategory);
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
      <div class="tn-container">
        <div class="about-hero-badge">TRUENORTH PRODUCTS</div>
        <h1 class="about-hero-title">Products of TrueNorth Group</h1>
        <p class="about-hero-subtitle">Primary Packaging Solutions</p>
      </div>
    </div>

    <section class="tn-section" style="padding: 36px 0;">
      <div class="tn-container">
        
        <!-- Search & Quick Navigation Bar -->
        <div style="max-width: 680px; margin: 0 auto 36px;">
          <form action="/products" method="GET" style="display: flex; gap: 8px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); border-radius: 8px; overflow: hidden; background: #fff; border: 1px solid #cbd5e1; padding: 4px;">
            <input type="text" name="q" placeholder="Search 290+ products..." style="flex: 1; border: none; padding: 12px 16px; font-size: 0.95rem; outline: none;" />
            <button type="submit" class="tn-btn-gold" style="margin: 0; padding: 12px 24px; border-radius: 6px; font-weight: 700;">
              SEARCH
            </button>
          </form>
        </div>

        <!-- Industry Markets Section -->
        <div class="tn-section-title-wrap" style="text-align: center; margin-bottom: 24px;">
          <div style="display: inline-block; font-size: 0.75rem; font-weight: 800; color: #b8892e; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 6px; background: rgba(184, 137, 46, 0.08); padding: 4px 12px; border-radius: 4px;">
            TARGET SECTOR SELECTION
          </div>
          <h2 class="tn-section-title" style="font-size: 1.65rem; font-weight: 800; color: var(--color-navy); margin-bottom: 8px;">
            Packaging by Industry & Market
          </h2>
          <p style="color: #64748B; font-size: 0.92rem; max-width: 750px; margin: 0 auto; line-height: 1.5;">
            Explore packaging systems curated for regulatory compliance, durability, and aesthetics across our 5 primary markets.
          </p>
        </div>

        <div class="tn-markets-grid" style="margin-bottom: 48px;">
          <?php foreach ($allIndustries as $indSlug => $ind): ?>
            <a href="/products?industry=<?= urlencode($indSlug) ?>" class="tn-market-tile" style="text-decoration: none;">
              <div class="tn-market-img">
                <img src="<?= htmlspecialchars($ind['image']) ?>" alt="<?= htmlspecialchars($ind['name']) ?>" loading="lazy" onerror="this.onerror=null;this.src='/logo_svg.svg';" />
              </div>
              <div class="tn-tile-bar">
                <span><?= htmlspecialchars($ind['name']) ?></span>
              </div>
            </a>
          <?php endforeach; ?>
        </div>

        <div class="tn-section-title-wrap" style="text-align: center; margin-bottom: 32px;">
          <div style="display: inline-block; font-size: 0.75rem; font-weight: 800; color: var(--color-navy); letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 6px; background: #e2e8f0; padding: 4px 12px; border-radius: 4px;">
            COMPONENT CATALOGUE
          </div>
          <h2 class="tn-section-title" style="font-size: 1.8rem; font-weight: 800; color: var(--color-navy); margin-bottom: 8px;">
            Packaging Categories
          </h2>
          <p style="color: #64748B; font-size: 0.95rem; max-width: 750px; margin: 0 auto; line-height: 1.5;">
            Explore our comprehensive catalogue of PET and HDPE bottles, jars, closures, dispensing pumps, and spray systems.
          </p>
        </div>

        <div class="tn-level1-grid">
          <?php foreach ($allCategories as $cat): ?>
            <a href="/products?category=<?= urlencode($cat['slug']) ?>" class="tn-tile-card" style="text-decoration: none;">
              <div class="tn-tile-image">
                <img src="<?= htmlspecialchars($cat['image'] ?? '/logo_svg.svg') ?>" alt="<?= htmlspecialchars($cat['name']) ?>" loading="lazy" />
              </div>
              <div class="tn-tile-bar"><?= htmlspecialchars($cat['name']) ?></div>
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
      <div class="tn-container">
        <div class="about-hero-badge">TRUENORTH PRODUCTS</div>
        <h1 class="about-hero-title"><?= htmlspecialchars($categoryInfo['name'] ?? 'Category') ?></h1>
        <p class="about-hero-subtitle">Premium Primary Packaging Solutions</p>
      </div>
    </div>

    <section class="tn-section" style="padding: 32px 0;">
      <div class="tn-container">
        
        <!-- Breadcrumbs & Back -->
        <div class="tn-breadcrumbs">
          <a href="/products">Products</a>
          <span class="separator">&gt;</span>
          <span class="current"><?= htmlspecialchars($categoryInfo['name'] ?? 'Category') ?></span>
        </div>

        <a href="/products" class="tn-back-btn">&larr; Back to Categories</a>

        <div class="tn-catalogue-layout">
          
          <!-- Left Sidebar Column -->
          <div class="tn-sidebar-column">
            <aside class="tn-filter-sidebar">
              
              <!-- Quick Keyword Filter -->
              <div class="tn-filter-group" style="padding: 12px 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                  <label style="font-size: 0.8rem; font-weight: 700; color: var(--color-navy); margin: 0;">Filter Series</label>
                  <button type="button" onclick="window.resetCatalogueFilters()" style="background: none; border: none; color: #b8892e; font-size: 0.75rem; font-weight: 700; cursor: pointer; padding: 0;">Reset</button>
                </div>
                <input type="text" id="catalogue-instant-filter" placeholder="Type name, size, type..." oninput="window.filterCatalogueItems()" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; outline: none; box-sizing: border-box;" />
              </div>

              <!-- Material Filter -->
              <?php if (!empty($seriesMaterials)): ?>
                <div class="tn-filter-group">
                  <div class="tn-filter-header" onclick="window.toggleFilterAccordion(this)">
                    Material <span class="toggle-icon">&#9662;</span>
                  </div>
                  <div class="tn-filter-list">
                    <?php foreach ($seriesMaterials as $mat => $cnt): ?>
                      <label class="tn-filter-item">
                        <input type="checkbox" name="filter-material" value="<?= htmlspecialchars($mat) ?>" onchange="window.filterCatalogueItems()">
                        <?= htmlspecialchars($mat) ?> (<?= $cnt ?>)
                      </label>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>

              <!-- Action Button -->
              <div style="margin-top: 10px;">
                <button type="button" onclick="window.resetCatalogueFilters()" class="tn-btn-outline" style="width: 100%; padding: 8px; font-size: 0.82rem; margin: 0; text-align: center;">
                  Clear Active Filters
                </button>
              </div>

            </aside>
          </div>

          <!-- Main Series Grid -->
          <div class="tn-products-main-column">
            <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 8px;">
              <h2 style="font-size: 1.55rem; font-weight: 800; color: var(--color-navy); margin: 0;">
                <?= htmlspecialchars($categoryInfo['name'] ?? 'Category') ?>
              </h2>
              <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;" id="catalogue-counter">
                Showing <?= count($seriesList) ?> series
              </span>
            </div>

            <div class="tn-series-grid" id="catalogue-items-grid">
              <?php foreach ($seriesList as $s): ?>
                <a href="/products?category=<?= urlencode($selectedCategory) ?>&series=<?= urlencode($s['slug']) ?>" 
                   class="tn-tile-card filterable-item"
                   data-name="<?= strtolower(htmlspecialchars($s['name'] ?? '')) ?>"
                   data-material="<?= strtoupper(htmlspecialchars($s['material'] ?? 'PET')) ?>"
                   data-volume="<?= htmlspecialchars($s['volumeRange'] ?? '') ?>"
                   style="text-decoration: none;">
                  <div class="tn-tile-image">
                    <img src="<?= htmlspecialchars($s['image'] ?? '/logo_svg.svg') ?>" alt="<?= htmlspecialchars($s['name']) ?>" loading="lazy" />
                  </div>
                  <div class="tn-tile-bar"><?= htmlspecialchars($s['name']) ?></div>
                </a>
              <?php endforeach; ?>
            </div>

            <!-- Empty filtered state -->
            <div id="no-filter-results" style="display: none; text-align: center; padding: 40px 20px; background: #fff; border-radius: 8px; border: 1px dashed #cbd5e1; margin-top: 16px;">
              <h3 style="font-size: 1.1rem; color: #16273f; margin-bottom: 6px;">No packaging series match your filters</h3>
              <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 16px;">Try adjusting your keyword or clearing checked options.</p>
              <button type="button" onclick="window.resetCatalogueFilters()" class="tn-btn-gold" style="margin: 0; padding: 8px 18px; font-size: 0.85rem;">Reset Filters</button>
            </div>

          </div>

        </div>

      </div>
    </section>

  <!-- =====================================================================
       LEVEL 3: SKUs WITHIN SERIES / SEARCH RESULTS / INDUSTRY (Matching Image 4)
       ===================================================================== -->
  <?php elseif ($currentLevel === 'skus' || $currentLevel === 'search' || $currentLevel === 'industry'): ?>

    <?php
      // Sort SKUs ascending by numerical size
      if (!empty($skusList) && is_array($skusList)) {
          usort($skusList, ['ProductModel', 'compareProductSizes']);
      }

      // =====================================================================
      // SMART FACET EXTRACTION (Purely Product-Centric Attributes)
      // =====================================================================
      $skuIndustries = [];
      $allIndustriesMap = ProductModel::getIndustries();
      $skuSeries = [];
      $skuCategories = [];
      $skuMaterials = [];
      $skuCapacities = [];
      $skuNecks = [];
      $skuShapes = [];
      $skuColors = [];

      foreach ($skusList as $sku) {
          // Industry mapping
          foreach ($sku['industries'] ?? [] as $indKey) {
              $skuIndustries[$indKey] = ($skuIndustries[$indKey] ?? 0) + 1;
          }

          // Category (relevant especially in search)
          $catSlug = strtolower(trim($sku['categorySlug'] ?? ''));
          $catName = trim($sku['categoryName'] ?? ucfirst(str_replace('-', ' ', $catSlug)));
          if ($catSlug) {
              $skuCategories[$catSlug] = [
                  'name' => $catName,
                  'count' => ($skuCategories[$catSlug]['count'] ?? 0) + 1
              ];
          }

          // Subcategory / Series
          $ser = trim($sku['series'] ?? $sku['serie'] ?? '');
          if ($ser && strcasecmp($ser, 'custom-series') !== 0 && strcasecmp($ser, $catSlug) !== 0) {
              $skuSeries[$ser] = ($skuSeries[$ser] ?? 0) + 1;
          }

          // Material
          $mat = trim($sku['specs']['material'] ?? $sku['material'] ?? 'PET');
          if ($mat) {
              $skuMaterials[$mat] = ($skuMaterials[$mat] ?? 0) + 1;
          }

          // Capacity / Volume (normalized with standard spacing: e.g. "30 ml")
          $cap = trim($sku['specs']['capacity'] ?? $sku['volume'] ?? '');
          if ($cap && strcasecmp($cap, 'N/A') !== 0) {
              $cap = preg_replace('/^(\d+(?:\.\d+)?)\s*(ml|cc|gm|g|l)$/i', '$1 $2', $cap);
              $skuCapacities[$cap] = ($skuCapacities[$cap] ?? 0) + 1;
          }

          // Neck Finish (Parse individual recognized standards)
          $rawNeck = trim($sku['specs']['neck'] ?? $sku['neck'] ?? '');
          if ($rawNeck && strcasecmp($rawNeck, 'Standard') !== 0 && strcasecmp($rawNeck, 'Complete pump assembly') !== 0) {
              preg_match_all('/(\d+\/\d+|\d+(?:\.\d+)?\s*mm)/i', $rawNeck, $matches);
              if (!empty($matches[0])) {
                  $uniqueMatches = array_unique(array_map(function($m) {
                      return strtolower(str_replace(' ', '', $m));
                  }, $matches[0]));
                  foreach ($uniqueMatches as $um) {
                      $displayUm = preg_match('/^\d+mm$/', $um) ? substr($um, 0, -2) . ' mm' : $um;
                      $skuNecks[$displayUm] = ($skuNecks[$displayUm] ?? 0) + 1;
                  }
              } else {
                  $skuNecks[$rawNeck] = ($skuNecks[$rawNeck] ?? 0) + 1;
              }
          }

          // Shape / Profile (Includes Standard)
          $shp = trim($sku['specs']['shape'] ?? $sku['shape'] ?? '');
          if ($shp) {
              $skuShapes[$shp] = ($skuShapes[$shp] ?? 0) + 1;
          }

          // Color / Coating
          $col = trim($sku['specs']['color'] ?? $sku['color'] ?? '');
          if ($col) {
              $skuColors[$col] = ($skuColors[$col] ?? 0) + 1;
          }
      }

      // Sort series alphabetically
      ksort($skuSeries);

      // Sort colors by count
      arsort($skuColors);

      // Sort materials by count
      arsort($skuMaterials);

      // Sort capacities numerically
      uksort($skuCapacities, function($a, $b) {
          preg_match('/(\d+(?:\.\d+)?)/', $a, $mA);
          preg_match('/(\d+(?:\.\d+)?)/', $b, $mB);
          $numA = isset($mA[1]) ? (float)$mA[1] : 0;
          $numB = isset($mB[1]) ? (float)$mB[1] : 0;
          if ($numA === $numB) return strcmp($a, $b);
          return ($numA < $numB) ? -1 : 1;
      });

      // Sort necks naturally
      uksort($skuNecks, function($a, $b) {
          preg_match('/(\d+(?:\.\d+)?)/', $a, $mA);
          preg_match('/(\d+(?:\.\d+)?)/', $b, $mB);
          $numA = isset($mA[1]) ? (float)$mA[1] : 0;
          $numB = isset($mB[1]) ? (float)$mB[1] : 0;
          if ($numA === $numB) return strcmp($a, $b);
          return ($numA < $numB) ? -1 : 1;
      });

      // Sort shapes by count
      arsort($skuShapes);
    ?>

    <!-- Page Title / Banner -->
    <div class="products-page-title-banner about-hero-banner" style="padding: 40px 0;">
      <div class="tn-container">
        <div class="about-hero-badge"><?= $currentLevel === 'search' ? 'SEARCH RESULTS' : ($currentLevel === 'industry' ? 'INDUSTRY SELECTION' : 'TRUENORTH PRODUCTS') ?></div>
        <h1 class="about-hero-title"><?= htmlspecialchars($viewTitle) ?></h1>
        <p class="about-hero-subtitle">
          <?= htmlspecialchars($viewSubtitle ?? ($currentLevel === 'search' ? 'Showing matches across the full primary packaging catalogue' : 'Premium Primary Packaging Solutions')) ?>
        </p>

        <?php if ($currentLevel === 'industry' && !empty($industryInfo['image'])): ?>
          <div style="display: flex; align-items: center; justify-content: center; gap: 20px; max-width: 820px; margin: 18px auto 0; background: rgba(255,255,255,0.06); border-radius: 10px; padding: 12px 18px; border: 1px solid rgba(255,255,255,0.12);">
            <img src="<?= htmlspecialchars($industryInfo['image']) ?>" alt="<?= htmlspecialchars($industryInfo['name']) ?>" style="height: 60px; width: 90px; object-fit: cover; border-radius: 6px; box-shadow: 0 4px 10px rgba(0,0,0,0.25);" onerror="this.style.display='none';" />
            <span style="color: #cbd5e1; font-size: 0.88rem; line-height: 1.5; text-align: left;">
              <?= htmlspecialchars($industryInfo['desc']) ?>
            </span>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <section class="tn-section" style="padding: 32px 0;">
      <div class="tn-container">
        
        <!-- Breadcrumbs & Back -->
        <div class="tn-breadcrumbs">
          <a href="/products">Products</a>
          <?php if (!empty($categoryInfo)): ?>
            <span class="separator">&gt;</span>
            <a href="/products?category=<?= urlencode($categoryInfo['slug']) ?>"><?= htmlspecialchars($categoryInfo['name']) ?></a>
          <?php endif; ?>
          <?php if (!empty($seriesInfo)): ?>
            <span class="separator">&gt;</span>
            <span class="current"><?= htmlspecialchars($seriesInfo['name']) ?></span>
          <?php endif; ?>
          <?php if (!empty($industryInfo)): ?>
            <span class="separator">&gt;</span>
            <span class="current">Industry: <?= htmlspecialchars($industryInfo['name']) ?></span>
          <?php endif; ?>
          <?php if ($currentLevel === 'search'): ?>
            <span class="separator">&gt;</span>
            <span class="current">Search: <?= htmlspecialchars($searchQuery) ?></span>
          <?php endif; ?>
        </div>

        <?php 
          if ($currentLevel === 'search' || $currentLevel === 'industry' || empty($selectedSeries)) {
              $backUrl = '/products';
              $backLabel = 'Back to All Products';
          } else {
              $backUrl = !empty($categoryInfo) ? '/products?category=' . urlencode($categoryInfo['slug']) : '/products';
              $backLabel = 'Back to ' . (!empty($categoryInfo['name']) ? htmlspecialchars($categoryInfo['name']) : 'Products');
          }
        ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
          <a href="<?= $backUrl ?>" class="tn-back-btn" style="margin: 0;">&larr; <?= $backLabel ?></a>
          
          <?php if ($currentLevel === 'search' || $currentLevel === 'industry'): ?>
            <a href="/products" style="color: #b8892e; font-weight: 700; text-decoration: none; font-size: 0.85rem;">Clear Active View &times;</a>
          <?php endif; ?>
        </div>

        <!-- Mobile Filter Bar Trigger (Shown only on Mobile/Tablet) -->
        <div class="tn-mobile-filter-bar">
          <button type="button" class="tn-mobile-filter-btn" onclick="window.toggleMobileFilters()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            <span>Filters</span>
            <span class="tn-filter-count-badge" id="mobile-filter-badge" style="display: none;">0</span>
          </button>
          <span class="tn-mobile-results-count" id="mobile-products-count"><?= !empty($skusList) ? count($skusList) : (!empty($skus) ? count($skus) : 0) ?> items</span>
        </div>

        <div class="tn-catalogue-layout">
          
          <!-- Left Sidebar Column (Stretches full height of products grid) -->
          <div class="tn-sidebar-column">
            <!-- =================================================================
                 SMART FLOATING SIDEBAR FILTERS (Sticky as product grid scrolls)
                 ================================================================= -->
            <aside class="tn-filter-sidebar" id="catalogue-smart-sidebar">
              
              <!-- Mobile Drawer Header -->
              <div class="tn-filter-mobile-header">
                <span>Filter Packaging</span>
                <button type="button" class="tn-filter-close-btn" onclick="window.closeMobileFilters()" aria-label="Close filters">&times;</button>
              </div>
              
              <!-- 1. Quick Keyword Filter -->
              <div class="tn-filter-group" style="padding: 12px 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                  <label style="font-size: 0.8rem; font-weight: 700; color: var(--color-navy); margin: 0;">Quick Search</label>
                  <button type="button" onclick="window.resetCatalogueFilters()" style="background: none; border: none; color: #b8892e; font-size: 0.75rem; font-weight: 700; cursor: pointer; padding: 0;">Reset</button>
                </div>
                <input type="text" id="catalogue-instant-filter" placeholder="Filter code, size, type..." oninput="window.filterCatalogueItems()" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.82rem; outline: none; box-sizing: border-box;" />
              </div>

              <!-- 2. Industry / Market Filter -->
              <div class="tn-filter-group" data-filter-group="industry">
                <div class="tn-filter-header" onclick="window.toggleFilterAccordion(this)">
                  Industry / Market <span class="toggle-icon">&#9662;</span>
                </div>
                <div class="tn-filter-list">
                  <?php foreach ($allIndustriesMap as $indSlug => $indData): ?>
                    <?php 
                      $cnt = $skuIndustries[$indSlug] ?? 0;
                      $isChecked = ($selectedIndustry === $indSlug);
                    ?>
                    <label class="tn-filter-item <?= ($cnt === 0 && !$isChecked) ? 'is-disabled' : '' ?>" data-facet-group="industry" data-facet-val="<?= htmlspecialchars($indSlug) ?>">
                      <span class="tn-filter-item-left">
                        <input type="checkbox" name="filter-industry" value="<?= htmlspecialchars($indSlug) ?>" <?= $isChecked ? 'checked' : '' ?> onchange="window.filterCatalogueItems()">
                        <span class="filter-label-text"><?= htmlspecialchars($indData['name']) ?></span>
                      </span>
                      <span class="facet-count">(<?= $cnt ?>)</span>
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>

              <!-- 3. Category Filter (for Search & Multi-category views) -->
              <?php if (count($skuCategories) > 1): ?>
                <div class="tn-filter-group" data-filter-group="category">
                  <div class="tn-filter-header" onclick="window.toggleFilterAccordion(this)">
                    Packaging Category <span class="toggle-icon">&#9662;</span>
                  </div>
                  <div class="tn-filter-list">
                    <?php foreach ($skuCategories as $cSlug => $cData): ?>
                      <label class="tn-filter-item" data-facet-group="category" data-facet-val="<?= htmlspecialchars($cSlug) ?>">
                        <span class="tn-filter-item-left">
                          <input type="checkbox" name="filter-category" value="<?= htmlspecialchars($cSlug) ?>" onchange="window.filterCatalogueItems()">
                          <span class="filter-label-text"><?= htmlspecialchars($cData['name']) ?></span>
                        </span>
                        <span class="facet-count">(<?= $cData['count'] ?>)</span>
                      </label>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>

              <!-- 3. Size Filter -->
              <?php if (!empty($skuCapacities) && count($skuCapacities) > 1): ?>
                <div class="tn-filter-group" data-filter-group="volume">
                  <div class="tn-filter-header" onclick="window.toggleFilterAccordion(this)">
                    Size <span class="toggle-icon">&#9662;</span>
                  </div>
                  <div class="tn-filter-list">
                    <?php foreach ($skuCapacities as $cap => $cnt): ?>
                      <label class="tn-filter-item" data-facet-group="volume" data-facet-val="<?= htmlspecialchars(strtolower($cap)) ?>">
                        <span class="tn-filter-item-left">
                          <input type="checkbox" name="filter-volume" value="<?= htmlspecialchars($cap) ?>" onchange="window.filterCatalogueItems()">
                          <span class="filter-label-text"><?= htmlspecialchars($cap) ?></span>
                        </span>
                        <span class="facet-count">(<?= $cnt ?>)</span>
                      </label>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>

              <!-- 5. Neck Finish Filter -->
              <?php if (!empty($skuNecks) && count($skuNecks) > 1): ?>
                <div class="tn-filter-group" data-filter-group="neck">
                  <div class="tn-filter-header" onclick="window.toggleFilterAccordion(this)">
                    Neck Finish <span class="toggle-icon">&#9662;</span>
                  </div>
                  <div class="tn-filter-list">
                    <?php foreach ($skuNecks as $n => $cnt): ?>
                      <label class="tn-filter-item" data-facet-group="neck" data-facet-val="<?= htmlspecialchars(strtolower($n)) ?>">
                        <span class="tn-filter-item-left">
                          <input type="checkbox" name="filter-neck" value="<?= htmlspecialchars($n) ?>" onchange="window.filterCatalogueItems()">
                          <span class="filter-label-text"><?= htmlspecialchars($n) ?></span>
                        </span>
                        <span class="facet-count">(<?= $cnt ?>)</span>
                      </label>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>

              <!-- 6. Material Filter -->
              <?php if (!empty($skuMaterials) && count($skuMaterials) > 1): ?>
                <div class="tn-filter-group" data-filter-group="material">
                  <div class="tn-filter-header" onclick="window.toggleFilterAccordion(this)">
                    Material <span class="toggle-icon">&#9662;</span>
                  </div>
                  <div class="tn-filter-list">
                    <?php foreach ($skuMaterials as $mat => $cnt): ?>
                      <label class="tn-filter-item" data-facet-group="material" data-facet-val="<?= htmlspecialchars(strtoupper($mat)) ?>">
                        <span class="tn-filter-item-left">
                          <input type="checkbox" name="filter-material" value="<?= htmlspecialchars($mat) ?>" onchange="window.filterCatalogueItems()">
                          <span class="filter-label-text"><?= htmlspecialchars($mat) ?></span>
                        </span>
                        <span class="facet-count">(<?= $cnt ?>)</span>
                      </label>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>

              <!-- 7. Shape Filter (if > 1 distinct shape) -->
              <?php if (!empty($skuShapes) && count($skuShapes) > 1): ?>
                <div class="tn-filter-group" data-filter-group="shape">
                  <div class="tn-filter-header" onclick="window.toggleFilterAccordion(this)">
                    Bottle Shape <span class="toggle-icon">&#9662;</span>
                  </div>
                  <div class="tn-filter-list">
                    <?php foreach ($skuShapes as $shp => $cnt): ?>
                      <label class="tn-filter-item" data-facet-group="shape" data-facet-val="<?= htmlspecialchars(strtolower($shp)) ?>">
                        <span class="tn-filter-item-left">
                          <input type="checkbox" name="filter-shape" value="<?= htmlspecialchars($shp) ?>" onchange="window.filterCatalogueItems()">
                          <span class="filter-label-text"><?= htmlspecialchars($shp) ?></span>
                        </span>
                        <span class="facet-count">(<?= $cnt ?>)</span>
                      </label>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>

              <!-- 8. Color Filter (Agro, Jars, Pharma) -->
              <?php if (!empty($skuColors)): ?>
                <div class="tn-filter-group" data-filter-group="color">
                  <div class="tn-filter-header" onclick="window.toggleFilterAccordion(this)">
                    Color <span class="toggle-icon">&#9662;</span>
                  </div>
                  <div class="tn-filter-list">
                    <?php foreach ($skuColors as $col => $cnt): ?>
                      <label class="tn-filter-item" data-facet-group="color" data-facet-val="<?= htmlspecialchars(strtolower($col)) ?>">
                        <span class="tn-filter-item-left">
                          <input type="checkbox" name="filter-color" value="<?= htmlspecialchars($col) ?>" onchange="window.filterCatalogueItems()">
                          <span class="filter-label-text"><?= htmlspecialchars($col) ?></span>
                        </span>
                        <span class="facet-count">(<?= $cnt ?>)</span>
                      </label>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>

              <!-- Reset All Action Button -->
              <div style="margin-top: 6px;">
                <button type="button" onclick="window.resetCatalogueFilters()" class="tn-btn-outline" style="width: 100%; padding: 8px; font-size: 0.82rem; margin: 0; text-align: center;">
                  Clear Active Filters
                </button>
              </div>

            </aside>
          </div>

          <!-- =================================================================
               MAIN PRODUCTS GRID & ACTIVE FILTER CHIPS (Stretches Full Height)
               ================================================================= -->
          <div class="tn-products-main-column">
            
            <!-- Title Bar & Live Counter -->
            <div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 8px;">
              <h2 style="font-size: 1.55rem; font-weight: 800; color: var(--color-navy); margin: 0;">
                <?= htmlspecialchars($viewTitle) ?>
              </h2>
              <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;" id="catalogue-counter">
                Showing <strong id="catalogue-count-val" style="color: var(--color-navy);"><?= count($skusList) ?></strong> of <?= count($skusList) ?> items
              </span>
            </div>

            <!-- Active Filters Chips Bar (Dynamic badges with 1-click 'x') -->
            <div id="active-filters-bar" class="tn-active-filters-bar" style="display: none;">
              <span class="active-filters-label">Active:</span>
              <div id="active-filters-chips" class="active-filters-chips"></div>
              <button type="button" class="clear-all-filters-btn" onclick="window.resetCatalogueFilters()">Clear all</button>
            </div>

            <?php if (empty($skusList)): ?>
              <div style="text-align: center; padding: 48px 20px; background: #fff; border-radius: 12px; border: 1px dashed #cbd5e1;">
                <h3 style="color: #64748b; margin-bottom: 8px;">No packaging items found matching "<?= htmlspecialchars($searchQuery ?? '') ?>".</h3>
                <a href="/products" style="color: var(--color-gold); font-weight: 700; text-decoration: none;">View All Catalogue Categories &rarr;</a>
              </div>
            <?php else: ?>
              <div class="tn-skus-grid" id="catalogue-items-grid">
                <?php foreach ($skusList as $sku): ?>
                  <?php 
                    $skuId = $sku['id'] ?? $sku['articleNo'] ?? 1;
                    $catSlug = strtolower(trim($sku['categorySlug'] ?? ''));
                    $seriesVal = trim($sku['series'] ?? $sku['serie'] ?? '');
                    $materialVal = trim($sku['specs']['material'] ?? $sku['material'] ?? 'PET');
                    $volumeVal = trim($sku['specs']['capacity'] ?? $sku['volume'] ?? '');
                    $neckVal = trim($sku['specs']['neck'] ?? $sku['neck'] ?? '');
                    $shapeVal = trim($sku['specs']['shape'] ?? $sku['shape'] ?? 'Cylindrical');
                    $colorVal = trim($sku['specs']['color'] ?? $sku['color'] ?? 'White / Natural');
                    $artNo = trim($sku['articleNo'] ?? $sku['code'] ?? 'TN-001');
                  ?>
                  <a href="/product?id=<?= urlencode($skuId) ?>" 
                     class="tn-sku-card filterable-item"
                     data-name="<?= strtolower(htmlspecialchars($sku['name'] ?? '')) ?>"
                     data-code="<?= strtolower(htmlspecialchars($artNo)) ?>"
                     data-category="<?= htmlspecialchars($catSlug) ?>"
                     data-series="<?= strtolower(htmlspecialchars($seriesVal)) ?>"
                     data-material="<?= strtoupper(htmlspecialchars($materialVal)) ?>"
                     data-volume="<?= strtolower(htmlspecialchars($volumeVal)) ?>"
                     data-neck="<?= strtolower(htmlspecialchars($neckVal)) ?>"
                     data-shape="<?= strtolower(htmlspecialchars($shapeVal)) ?>"
                     data-color="<?= strtolower(htmlspecialchars($colorVal)) ?>"
                     data-industries="<?= htmlspecialchars(implode(',', $sku['industries'] ?? [])) ?>"
                     style="text-decoration: none;">
                    
                    <div class="tn-sku-img">
                      <img src="<?= htmlspecialchars($sku['image'] ?? '/logo_svg.svg') ?>" alt="<?= htmlspecialchars($sku['name'] ?? '') ?>" loading="lazy" onerror="this.onerror=null;this.src='/logo_svg.svg';" />
                    </div>
                    <div class="tn-sku-bar"><?= htmlspecialchars($sku['name'] ?? 'Product SKU') ?></div>

                    <div class="tn-sku-specs-table">
                      <div class="tn-sku-spec-row">
                        <span class="tn-sku-spec-icon">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 5v14M8 5v14M12 5v14M17 5v14M21 5v14"/></svg>
                        </span>
                        <span class="tn-sku-spec-val"><?= htmlspecialchars($artNo) ?></span>
                      </div>
                      <div class="tn-sku-spec-row">
                        <span class="tn-sku-spec-icon">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 19H4.815a1.83 1.83 0 0 1-1.57-.881 1.785 1.785 0 0 1-.004-1.784L7.196 9.5M11 19h8.2a1.8 1.8 0 0 0 1.583-.935 1.777 1.777 0 0 0-.015-1.778L16.8 9.5M12 5l3.89 6H8.11L12 5z"/></svg>
                        </span>
                        <span class="tn-sku-spec-val"><?= htmlspecialchars($materialVal) ?></span>
                      </div>
                      <div class="tn-sku-spec-row">
                        <span class="tn-sku-spec-icon">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 3h6v3H9zM10 6v3a4 4 0 0 1-2 3.46V20a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-7.54A4 4 0 0 1 14 9V6"/></svg>
                        </span>
                        <span class="tn-sku-spec-val"><?= htmlspecialchars($volumeVal ?: $shapeVal) ?></span>
                      </div>
                      <div class="tn-sku-spec-row">
                        <span class="tn-sku-spec-icon">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                        </span>
                        <span class="tn-sku-spec-val"><?= htmlspecialchars($neckVal ?: $colorVal) ?></span>
                      </div>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>

              <!-- Empty filtered state -->
              <div id="no-filter-results" style="display: none; text-align: center; padding: 48px 24px; background: #fff; border-radius: 8px; border: 1px dashed #cbd5e1; margin-top: 16px;">
                <h3 style="font-size: 1.15rem; color: #16273f; margin-bottom: 6px;">No packaging items match your filters</h3>
                <p style="color: #64748b; font-size: 0.88rem; margin-bottom: 18px;">Try clearing selected filters or broadening your search keyword.</p>
                <button type="button" onclick="window.resetCatalogueFilters()" class="tn-btn-gold" style="margin: 0; padding: 10px 22px; font-size: 0.85rem; font-weight: 700;">Reset All Filters</button>
              </div>

            <?php endif; ?>
          </div>

        </div>

      </div>
    </section>

  <?php endif; ?>

</section>

<!-- =========================================================================
     SMART FACETED FILTER ENGINE (Live Counts, Active Badges & Multi-Facet Logic)
     ========================================================================= -->
<script>
window.toggleMobileFilters = function() {
  const sidebar = document.getElementById('catalogue-smart-sidebar') || document.querySelector('.tn-filter-sidebar');
  let backdrop = document.getElementById('tn-filter-backdrop');
  if (!backdrop) {
    backdrop = document.createElement('div');
    backdrop.id = 'tn-filter-backdrop';
    backdrop.className = 'tn-filter-backdrop';
    backdrop.onclick = window.closeMobileFilters;
    document.body.appendChild(backdrop);
  }
  if (sidebar) {
    const isOpen = sidebar.classList.contains('mobile-open');
    if (isOpen) {
      window.closeMobileFilters();
    } else {
      sidebar.classList.add('mobile-open');
      backdrop.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
  }
};

window.closeMobileFilters = function() {
  const sidebar = document.getElementById('catalogue-smart-sidebar') || document.querySelector('.tn-filter-sidebar');
  const backdrop = document.getElementById('tn-filter-backdrop');
  if (sidebar) sidebar.classList.remove('mobile-open');
  if (backdrop) backdrop.classList.remove('open');
  document.body.style.overflow = '';
};

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

  document.querySelectorAll('.tn-filter-sidebar input[type="checkbox"]').forEach(function(cb) {
    cb.checked = false;
  });

  window.filterCatalogueItems();
};

window.removeSingleFilter = function(filterName, filterValue) {
  const cbs = document.querySelectorAll('.tn-filter-sidebar input[name="' + filterName + '"]');
  cbs.forEach(function(cb) {
    if (cb.value.toLowerCase() === filterValue.toLowerCase()) {
      cb.checked = false;
    }
  });
  window.filterCatalogueItems();
};

window.removeSearchQueryFilter = function() {
  const instantInput = document.getElementById('catalogue-instant-filter');
  if (instantInput) instantInput.value = '';
  window.filterCatalogueItems();
};

window.GENERIC_TAXONOMY = <?= json_encode(ProductModel::getGenericSearchTaxonomy()) ?>;

// Item match evaluator with optional facet group exclusion (for calculating disjunctive facet counts)
function itemMatchesCriteria(item, activeFilters, excludeGroup) {
  const query = activeFilters.query;
  const industries = excludeGroup === 'industry' ? [] : activeFilters.industry;
  const cats = excludeGroup === 'category' ? [] : activeFilters.category;
  const series = excludeGroup === 'series' ? [] : activeFilters.series;
  const materials = excludeGroup === 'material' ? [] : activeFilters.material;
  const volumes = excludeGroup === 'volume' ? [] : activeFilters.volume;
  const necks = excludeGroup === 'neck' ? [] : activeFilters.neck;
  const shapes = excludeGroup === 'shape' ? [] : activeFilters.shape;
  const colors = excludeGroup === 'color' ? [] : activeFilters.color;

  const itemIndustries = (item.getAttribute('data-industries') || '').toLowerCase().split(',').map(s => s.trim()).filter(Boolean);
  const itemCat = (item.getAttribute('data-category') || '').toLowerCase();
  const itemName = (item.getAttribute('data-name') || '').toLowerCase();
  const itemCode = (item.getAttribute('data-code') || '').toLowerCase();
  const itemSeries = (item.getAttribute('data-series') || '').toLowerCase();
  const itemMat = (item.getAttribute('data-material') || '').toUpperCase();
  const itemVol = (item.getAttribute('data-volume') || '').toLowerCase();
  const itemNeck = (item.getAttribute('data-neck') || '').toLowerCase();
  const itemShape = (item.getAttribute('data-shape') || '').toLowerCase();
  const itemColor = (item.getAttribute('data-color') || '').toLowerCase();

  // 1. Text Query with Generic Packaging Taxonomy Expansion
  if (query) {
    let isGenericMatch = false;
    if (window.GENERIC_TAXONOMY) {
      for (const [key, tax] of Object.entries(window.GENERIC_TAXONOMY)) {
        if (tax.terms && (tax.terms.includes(query) || tax.terms.includes(query + 's') || (query.endsWith('s') && tax.terms.includes(query.slice(0, -1))))) {
          if (tax.categorySlugs && tax.categorySlugs.includes(itemCat)) {
            isGenericMatch = true;
            break;
          }
        }
      }
    }

    const normQ = query.replace(/\s+/g, '');
    const normVol = itemVol.replace(/\s+/g, '');
    const normNeck = itemNeck.replace(/\s+/g, '');

    const matchQ = isGenericMatch ||
                   itemIndustries.some(ind => ind.includes(query)) ||
                   itemCat.includes(query) ||
                   itemName.includes(query) || 
                   itemCode.includes(query) || 
                   itemSeries.includes(query) || 
                   itemMat.toLowerCase().includes(query) || 
                   itemVol.includes(query) || 
                   normVol.includes(normQ) ||
                   itemNeck.includes(query) || 
                   normNeck.includes(normQ) ||
                   itemShape.includes(query);
    if (!matchQ) return false;
  }

  // 2. Industry Filter
  if (industries.length > 0) {
    const indMatch = industries.some(ind => itemIndustries.includes(ind.toLowerCase()));
    if (!indMatch) return false;
  }

  // 3. Category
  if (cats.length > 0) {
    if (!cats.includes(itemCat)) return false;
  }

  // 4. Series
  if (series.length > 0) {
    const sMatch = series.some(s => itemSeries.includes(s.toLowerCase()));
    if (!sMatch) return false;
  }

  // 5. Material
  if (materials.length > 0) {
    const mMatch = materials.some(m => itemMat.includes(m.toUpperCase()));
    if (!mMatch) return false;
  }

  // 6. Volume / Capacity
  if (volumes.length > 0) {
    const normItemVol = itemVol.replace(/\s+/g, '');
    const vMatch = volumes.some(v => {
      const normV = v.toLowerCase().replace(/\s+/g, '');
      return normItemVol === normV || itemVol.includes(v.toLowerCase());
    });
    if (!vMatch) return false;
  }

  // 7. Neck Finish (Smart standard matching)
  if (necks.length > 0) {
    const normItemNeck = itemNeck.replace(/\s+/g, '');
    const nMatch = necks.some(n => {
      const normN = n.toLowerCase().replace(/\s+/g, '');
      return normItemNeck.includes(normN) || itemNeck.includes(n.toLowerCase());
    });
    if (!nMatch) return false;
  }

  // 8. Shape
  if (shapes.length > 0) {
    const shpMatch = shapes.some(shp => itemShape.includes(shp.toLowerCase()));
    if (!shpMatch) return false;
  }

  // 9. Color
  if (colors.length > 0) {
    const cMatch = colors.some(c => itemColor.includes(c.toLowerCase()));
    if (!cMatch) return false;
  }

  return true;
}

window.filterCatalogueItems = function() {
  const instantInput = document.getElementById('catalogue-instant-filter');
  const query = instantInput ? instantInput.value.trim().toLowerCase() : '';

  const activeFilters = {
    query: query,
    industry: Array.from(document.querySelectorAll('.tn-filter-sidebar input[name="filter-industry"]:checked')).map(i => i.value.toLowerCase()),
    category: Array.from(document.querySelectorAll('.tn-filter-sidebar input[name="filter-category"]:checked')).map(i => i.value.toLowerCase()),
    series: Array.from(document.querySelectorAll('.tn-filter-sidebar input[name="filter-series"]:checked')).map(i => i.value.toLowerCase()),
    material: Array.from(document.querySelectorAll('.tn-filter-sidebar input[name="filter-material"]:checked')).map(i => i.value.toUpperCase()),
    volume: Array.from(document.querySelectorAll('.tn-filter-sidebar input[name="filter-volume"]:checked')).map(i => i.value.toLowerCase()),
    neck: Array.from(document.querySelectorAll('.tn-filter-sidebar input[name="filter-neck"]:checked')).map(i => i.value.toLowerCase()),
    shape: Array.from(document.querySelectorAll('.tn-filter-sidebar input[name="filter-shape"]:checked')).map(i => i.value.toLowerCase()),
    color: Array.from(document.querySelectorAll('.tn-filter-sidebar input[name="filter-color"]:checked')).map(i => i.value.toLowerCase())
  };

  const allItems = Array.from(document.querySelectorAll('.filterable-item'));
  let visibleCount = 0;

  // 1. Determine visibility for each item
  allItems.forEach(function(item) {
    const show = itemMatchesCriteria(item, activeFilters, null);
    item.style.display = show ? '' : 'none';
    if (show) visibleCount++;
  });

  // 2. Update Header Counter
  const countVal = document.getElementById('catalogue-count-val');
  if (countVal) {
    countVal.innerText = visibleCount;
  }
  const counterWrap = document.getElementById('catalogue-counter');
  if (counterWrap && !countVal) {
    counterWrap.innerText = 'Showing ' + visibleCount + ' item' + (visibleCount === 1 ? '' : 's');
  }
  const mobileCount = document.getElementById('mobile-products-count');
  if (mobileCount) {
    mobileCount.innerText = visibleCount + ' items';
  }

  // 3. Dynamic Faceted Counts & Smart Disabling
  const facetLabels = document.querySelectorAll('.tn-filter-sidebar .tn-filter-item');
  facetLabels.forEach(function(label) {
    const group = label.getAttribute('data-facet-group');
    const val = label.getAttribute('data-facet-val');
    const cb = label.querySelector('input[type="checkbox"]');
    const countSpan = label.querySelector('.facet-count');

    if (!group || !val || !cb) return;

    // Count how many items match the criteria when this specific facet is chosen
    let facetCount = 0;
    allItems.forEach(function(item) {
      if (itemMatchesCriteria(item, activeFilters, group)) {
        // Test if this item matches the specific facet value
        const itemIndustries = (item.getAttribute('data-industries') || '').toLowerCase().split(',').map(s => s.trim()).filter(Boolean);
        const itemCat = (item.getAttribute('data-category') || '').toLowerCase();
        const itemSeries = (item.getAttribute('data-series') || '').toLowerCase();
        const itemMat = (item.getAttribute('data-material') || '').toUpperCase();
        const itemVol = (item.getAttribute('data-volume') || '').toLowerCase();
        const itemNeck = (item.getAttribute('data-neck') || '').toLowerCase();
        const itemShape = (item.getAttribute('data-shape') || '').toLowerCase();
        const itemColor = (item.getAttribute('data-color') || '').toLowerCase();

        let matchesVal = false;
        if (group === 'industry') {
          matchesVal = itemIndustries.includes(val.toLowerCase());
        } else if (group === 'category') {
          matchesVal = itemCat === val;
        } else if (group === 'series') {
          matchesVal = itemSeries.includes(val);
        } else if (group === 'material') {
          matchesVal = itemMat.includes(val.toUpperCase());
        } else if (group === 'volume') {
          const normItemVol = itemVol.replace(/\s+/g, '');
          const normV = val.replace(/\s+/g, '');
          matchesVal = normItemVol === normV || itemVol.includes(val);
        } else if (group === 'neck') {
          const normItemNeck = itemNeck.replace(/\s+/g, '');
          const normN = val.replace(/\s+/g, '');
          matchesVal = normItemNeck.includes(normN) || itemNeck.includes(val);
        } else if (group === 'shape') {
          matchesVal = itemShape.includes(val);
        } else if (group === 'color') {
          matchesVal = itemColor.includes(val.toLowerCase());
        }

        if (matchesVal) facetCount++;
      }
    });

    if (countSpan) {
      countSpan.innerText = '(' + facetCount + ')';
    }

    if (facetCount === 0 && !cb.checked) {
      label.classList.add('is-disabled');
      cb.disabled = true;
    } else {
      label.classList.remove('is-disabled');
      cb.disabled = false;
    }
  });

  // 4. Update Active Filters Badges Bar
  const chipsBar = document.getElementById('active-filters-bar');
  const chipsContainer = document.getElementById('active-filters-chips');
  if (chipsBar && chipsContainer) {
    chipsContainer.innerHTML = '';
    let hasActive = false;

    if (query) {
      hasActive = true;
      const chip = document.createElement('span');
      chip.className = 'active-filter-chip';
      chip.innerHTML = 'Search: "' + escapeHtml(query) + '" <span class="chip-remove" onclick="window.removeSearchQueryFilter()">&times;</span>';
      chipsContainer.appendChild(chip);
    }

    const groupNames = {
      'filter-industry': 'Industry',
      'filter-category': 'Category',
      'filter-volume': 'Size',
      'filter-neck': 'Neck',
      'filter-material': 'Material',
      'filter-shape': 'Shape',
      'filter-color': 'Color'
    };

    document.querySelectorAll('.tn-filter-sidebar input[type="checkbox"]:checked').forEach(function(checkedCb) {
      hasActive = true;
      const filterName = checkedCb.name;
      const filterVal = checkedCb.value;
      const prefix = groupNames[filterName] ? groupNames[filterName] + ': ' : '';

      const labelElem = checkedCb.closest('.tn-filter-item');
      const labelText = labelElem ? (labelElem.querySelector('.filter-label-text')?.textContent || filterVal) : filterVal;

      const chip = document.createElement('span');
      chip.className = 'active-filter-chip';
      chip.innerHTML = prefix + escapeHtml(labelText) + ' <span class="chip-remove" onclick="window.removeSingleFilter(\'' + filterName + '\', \'' + escapeHtmlAttr(filterVal) + '\')">&times;</span>';
      chipsContainer.appendChild(chip);
    });

    chipsBar.style.display = hasActive ? 'flex' : 'none';
  }

  // 5. Zero Results State
  const noResultsBox = document.getElementById('no-filter-results');
  if (noResultsBox) {
    noResultsBox.style.display = (visibleCount === 0 && allItems.length > 0) ? 'block' : 'none';
  }

  // 6. Keep sidebar column height matched to product grid so sticky never drops off
  syncSidebarHeight();
};

function syncSidebarHeight() {
  const sidebarCols = document.querySelectorAll('.tn-sidebar-column');
  if (window.innerWidth <= 900) {
    sidebarCols.forEach(function(sCol) {
      sCol.style.minHeight = '0px';
      sCol.style.height = '0px';
    });
    return;
  }
  const mainCols = document.querySelectorAll('.tn-products-main-column');
  sidebarCols.forEach(function(sCol, idx) {
    const mCol = mainCols[idx];
    if (sCol && mCol) {
      sCol.style.minHeight = Math.max(mCol.offsetHeight, 600) + 'px';
    }
  });
}

function escapeHtml(str) {
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
function escapeHtmlAttr(str) {
  return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

// Initial smart facet calculation & height synchronization
window.addEventListener('resize', syncSidebarHeight);
document.addEventListener('DOMContentLoaded', function() {
  if (typeof window.filterCatalogueItems === 'function') {
    window.filterCatalogueItems();
  }
  syncSidebarHeight();
  setTimeout(syncSidebarHeight, 250);
});

// =========================================================================
// SMART TWO-WAY DYNAMIC SIDEBAR SCROLL FOLLOWER
// Moves with the list of products down and up smoothly without cutting off
// =========================================================================
(function initDynamicSidebarScroll() {
  const sidebar = document.getElementById('catalogue-smart-sidebar');
  if (!sidebar) return;

  let lastScrollY = window.pageYOffset || document.documentElement.scrollTop;
  let ticking = false;

  function updateSidebarPosition() {
    if (window.innerWidth <= 900) {
      sidebar.style.position = '';
      sidebar.style.top = '';
      sidebar.style.bottom = '';
      ticking = false;
      return;
    }

    const currentScrollY = window.pageYOffset || document.documentElement.scrollTop;
    const scrollDelta = currentScrollY - lastScrollY;
    const viewportHeight = window.innerHeight;
    const topOffset = 96;
    const bottomOffset = 24;

    const sidebarHeight = sidebar.offsetHeight;
    const column = sidebar.closest('.tn-sidebar-column') || sidebar.parentElement;
    if (!column) return;
    const columnRect = column.getBoundingClientRect();
    const columnTop = columnRect.top + currentScrollY;
    const columnHeight = column.offsetHeight;

    // Case 1: Sidebar fits within viewport -> standard top sticky
    if (sidebarHeight + topOffset + bottomOffset <= viewportHeight) {
      sidebar.style.position = 'sticky';
      sidebar.style.top = topOffset + 'px';
      sidebar.style.bottom = 'auto';
      lastScrollY = currentScrollY;
      ticking = false;
      return;
    }

    // Case 2: Sidebar is taller than viewport -> moves naturally with the list!
    const sidebarRect = sidebar.getBoundingClientRect();

    if (scrollDelta > 0) {
      // Scrolling DOWN with product list
      if (sidebarRect.bottom <= viewportHeight - bottomOffset) {
        // Reached bottom of sidebar -> stick to bottom of viewport
        sidebar.style.position = 'sticky';
        sidebar.style.top = (viewportHeight - sidebarHeight - bottomOffset) + 'px';
        sidebar.style.bottom = 'auto';
      } else if (sidebar.style.position === 'sticky' && parseFloat(sidebar.style.top) === topOffset) {
        // Unstick from top and move down with the products
        sidebar.style.position = 'relative';
        sidebar.style.top = Math.max(0, currentScrollY - columnTop + sidebarRect.top) + 'px';
      }
    } else if (scrollDelta < 0) {
      // Scrolling UP with product list
      if (sidebarRect.top >= topOffset) {
        // Reached top of sidebar -> stick to top of viewport
        sidebar.style.position = 'sticky';
        sidebar.style.top = topOffset + 'px';
        sidebar.style.bottom = 'auto';
      } else if (sidebar.style.position === 'sticky' && parseFloat(sidebar.style.top) < topOffset) {
        // Unstick from bottom and move up with the products
        sidebar.style.position = 'relative';
        sidebar.style.top = Math.max(0, currentScrollY - columnTop + sidebarRect.top) + 'px';
      }
    }

    lastScrollY = currentScrollY;
    ticking = false;
  }

  function onScroll() {
    if (!ticking) {
      window.requestAnimationFrame(updateSidebarPosition);
      ticking = true;
    }
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', function() {
    syncSidebarHeight();
    onScroll();
  }, { passive: true });

  updateSidebarPosition();
})();
</script>

