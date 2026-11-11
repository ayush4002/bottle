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

<div id="main-content" tabindex="-1" aria-hidden="true" style="position:absolute; left:0; top:0; width:0; height:0;"></div>
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
            <input type="text" name="q" aria-label="Search products and articles" placeholder="Search 290+ products..." style="flex: 1; border: none; padding: 12px 16px; font-size: 0.95rem; outline: none;" />
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
                <img src="<?= htmlspecialchars($ind['image']) ?>" alt="<?= htmlspecialchars($ind['name']) ?>"<?= tn_img_dims($ind['image']) ?> loading="lazy" onerror="this.onerror=null;this.src='/logo_svg.svg';" />
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
                <img src="<?= htmlspecialchars($cat['image'] ?? '/logo_svg.svg') ?>" alt="<?= htmlspecialchars($cat['name']) ?>"<?= tn_img_dims($cat['image'] ?? '/logo_svg.svg') ?> loading="lazy" />
              </div>
              <div class="tn-tile-bar"><?= htmlspecialchars($cat['name']) ?></div>
            </a>
          <?php endforeach; ?>
        </div>

        <!-- =================================================================
             EXECUTIVE PACKAGING ENQUIRY & SAMPLE REQUEST FORM AT DOWN
             ================================================================= -->
        <div class="products-down-inquiry-section" id="products-down-inquiry">
          <div class="products-inquiry-container">
            
            <!-- Left Info Column -->
            <div class="products-inquiry-info">
              <div>
                <span class="products-inquiry-tag">DIRECT FACTORY WHOLESALE &amp; SAMPLING</span>
                <h3 class="products-inquiry-title">Request a Custom Quote &amp; Free Packaging Samples</h3>
                <p class="products-inquiry-desc">
                  Need bulk production pricing, custom mould tooling, sterile cleanroom production, or physical samples dispatched to your facility? Fill out the form and our technical packaging specialists will provide detailed specs and an itemized quote within 24 business hours.
                </p>

                <div class="products-inquiry-perks">
                  <div class="products-inquiry-perk">
                    <div class="products-perk-icon">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div>
                      <strong>Free Physical Samples</strong>
                      <p>Dispatched worldwide for line testing &amp; torque calibration.</p>
                    </div>
                  </div>

                  <div class="products-inquiry-perk">
                    <div class="products-perk-icon">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <div>
                      <strong>TÜV NORD ISO 9001:2015 Certified</strong>
                      <p>Accredited cleanroom moulding, 1,000+ vacuum leak cycles tested.</p>
                    </div>
                  </div>

                  <div class="products-inquiry-perk">
                    <div class="products-perk-icon">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    </div>
                    <div>
                      <strong>MOQs from 5,000 Units &amp; Seaport Logistics</strong>
                      <p>Export packaging with full batch traceability (COA, TDS, MSDS).</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Direct Contact Box -->
              <div class="products-inquiry-direct">
                <div class="direct-contact-item">
                  <span class="direct-label">Direct Factory Desk:</span>
                  <a href="tel:<?= htmlspecialchars($company['phone_tel'] ?? '+919810220761') ?>" class="direct-val"><?= htmlspecialchars($company['phone'] ?? '+91 98102 20761') ?></a>
                </div>
                <div class="direct-contact-item">
                  <span class="direct-label">Technical RFQs:</span>
                  <a href="mailto:<?= htmlspecialchars($company['email'] ?? 'info@wetruenorthgroup.com') ?>" class="direct-val"><?= htmlspecialchars($company['email'] ?? 'info@wetruenorthgroup.com') ?></a>
                </div>
              </div>
            </div>

            <!-- Right Form Column -->
            <div class="products-inquiry-form-card">
              <form id="products-inquiry-form" class="inquiry-ajax-form">
                <input type="hidden" name="source_page" value="Products Catalogue Bottom Form" />
                <input type="hidden" name="product" value="Catalogue Wholesale RFQ &amp; Samples" />

                <div class="products-form-grid">
                  <div class="tn-form-field">
                    <label>1. Full Name *</label>
                    <input type="text" name="name" required placeholder="Enter your full name" autocomplete="name" />
                  </div>
                  <div class="tn-form-field">
                    <label>2. Company Name *</label>
                    <input type="text" name="company" required placeholder="Enter company name" autocomplete="organization" />
                  </div>
                  <div class="tn-form-field">
                    <label>3. Work Email *</label>
                    <input type="email" name="email" required placeholder="name@company.com" autocomplete="email" />
                  </div>
                  <div class="tn-form-field">
                    <label>4. Phone Number *</label>
                    <input type="tel" name="phone" required placeholder="Country code + phone number" autocomplete="tel" />
                  </div>
                  <div class="tn-form-field">
                    <label>5. Country *</label>
                    <select name="country" class="country-select" required>
                      <option value="" disabled selected>Select country...</option>
                      <option value="United States">United States</option>
                      <option value="United Kingdom">United Kingdom</option>
                      <option value="Germany">Germany</option>
                      <option value="United Arab Emirates">United Arab Emirates</option>
                      <option value="Saudi Arabia">Saudi Arabia</option>
                      <option value="France">France</option>
                      <option value="Netherlands">Netherlands</option>
                      <option value="Australia">Australia</option>
                      <option value="Canada">Canada</option>
                      <option value="Singapore">Singapore</option>
                      <option value="South Africa">South Africa</option>
                      <option value="Malaysia">Malaysia</option>
                      <option value="India">India</option>
                      <option value="Italy">Italy</option>
                      <option value="Spain">Spain</option>
                      <option value="Switzerland">Switzerland</option>
                      <option value="Japan">Japan</option>
                      <option value="China">China</option>
                      <option value="Brazil">Brazil</option>
                      <option value="Mexico">Mexico</option>
                      <option value="Poland">Poland</option>
                      <option value="Belgium">Belgium</option>
                      <option value="Sweden">Sweden</option>
                      <option value="Other Country">Other Country</option>
                    </select>
                  </div>
                  <div class="tn-form-field">
                    <label>6. Category of Interest *</label>
                    <select name="category_interest" required>
                      <option value="All Categories / Custom Requirement" selected>All Categories / Custom Requirement</option>
                      <option value="Pharma Bottles">Pharma Bottles</option>
                      <option value="Cosmetics Bottles">Cosmetics Bottles</option>
                      <option value="Agro Bottles">Agro Bottles</option>
                      <option value="Jars">Jars</option>
                      <option value="Pumps">Pumps</option>
                      <option value="Sprays">Sprays</option>
                      <option value="Caps & Closures">Caps &amp; Closures</option>
                      <option value="Liquor & Spirits Bottles">Liquor &amp; Spirits Bottles</option>
                      <option value="Custom Mould Development">Custom Mould Development</option>
                    </select>
                  </div>
                </div>

                <div class="tn-form-field" style="margin-top: 12px;">
                  <label>7. Specifications &amp; Estimated Quantity *</label>
                  <textarea name="inquiry" required placeholder="Specify your desired volume (e.g. 50,000 units), neck finish (e.g. 28/410), target bottle weight, cap requirements, or sample delivery address..."></textarea>
                </div>

                <button type="submit" class="tn-btn-gold products-form-submit-btn">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 8px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                  SUBMIT RFQ &amp; SAMPLE REQUEST
                </button>
              </form>
            </div>

          </div>
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
                    <img src="<?= htmlspecialchars($s['image'] ?? '/logo_svg.svg') ?>" alt="<?= htmlspecialchars($s['name']) ?>"<?= tn_img_dims($s['image'] ?? '/logo_svg.svg') ?> loading="lazy" />
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
            <img src="<?= htmlspecialchars($industryInfo['image']) ?>" alt="<?= htmlspecialchars($industryInfo['name']) ?>"<?= tn_img_dims($industryInfo['image']) ?> style="height: 60px; width: 90px; object-fit: cover; border-radius: 6px; box-shadow: 0 4px 10px rgba(0,0,0,0.25);" onerror="this.style.display='none';" />
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
                      <img src="<?= htmlspecialchars($sku['image'] ?? '/logo_svg.svg') ?>" alt="<?= htmlspecialchars($sku['name'] ?? '') ?>"<?= tn_img_dims($sku['image'] ?? '/logo_svg.svg') ?> loading="lazy" onerror="this.onerror=null;this.src='/logo_svg.svg';" />
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
window.GENERIC_TAXONOMY = <?= json_encode(ProductModel::getGenericSearchTaxonomy(), JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>
<script src="/js/catalogue.js?v=<?= @filemtime(dirname(__DIR__, 3) . '/public/js/catalogue.js') ?: 1 ?>" defer></script>

