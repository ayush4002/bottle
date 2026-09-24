import {
  FRAPAK_CATEGORIES_12,
  FRAPAK_MARKETS_5,
  SERIES_CATALOGUE,
  SKUS_CATALOGUE,
  ALL_PRODUCTS_DATA,
  COUNTRIES
} from './data.js';

// Clean vector SVG icons
const ICONS = {
  chevronUp: `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m18 15-6-6-6 6"/></svg>`,
  chevronLeft: `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>`,
  chevronRight: `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>`,
  barcode: `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 5v14M8 5v14M12 5v14M17 5v14M21 5v14"/></svg>`,
  recycle: `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 19H4.8a1.8 1.8 0 0 1-1.6-.9 1.8 1.8 0 0 1 0-1.8L7.2 9.5"/><path d="M11 19h8.2a1.8 1.8 0 0 0 1.6-.9 1.8 1.8 0 0 0 0-1.8L17.5 11"/><path d="m14 2-3.8 6.5a1.8 1.8 0 0 0 0 1.8 1.8 1.8 0 0 0 1.6.9H19"/></svg>`,
  bottle: `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="6" y="2" width="12" height="20" rx="3"/></svg>`,
  palette: `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="7" r="1.5" fill="currentColor"/><circle cx="8" cy="12" r="1.5" fill="currentColor"/><circle cx="16" cy="12" r="1.5" fill="currentColor"/></svg>`,
  box: `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>`,
  zoom: `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>`,
  info: `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>`
};

// Application State
const state = {
  activePage: 'home',
  // 4-Tier Drilldown State: 1 = Categories, 2 = Series, 3 = SKUs, 4 = PDP
  productLevel: 1,
  activeCatSlug: null,
  activeSeriesSlug: null,
  activeSkuId: null,
  activePdpImgIdx: 0,
  selectedProduct: null,
  // Sidebar Filters
  filters: {
    stock: [], // 'yes', 'no'
    material: [], // 'PET', 'RPET', 'HDPE', 'PP'
    volume: [] // '50', '100', '200', '250', '500', '1000'
  }
};

// Global image error handler to guarantee zero broken image icons
window.addEventListener('error', function(e) {
  if (e.target && e.target.tagName === 'IMG') {
    if (!e.target.dataset.hasFallback) {
      e.target.dataset.hasFallback = 'true';
      e.target.src = '/brand_assets/pet_bottles/100ml_boston_white.jpeg';
    }
  }
}, true);

// Initialize on DOM Ready
document.addEventListener('DOMContentLoaded', () => {
  initRouter();
  initSearch();
  initForms();
  renderHomeCatalogueTiles();
  renderHomeMarketTiles();
  populateCountries();
});

// ==========================================================================
// 1. ROUTER & HASH NAVIGATION
// ==========================================================================
function initRouter() {
  const handleHash = () => {
    const raw = window.location.hash.replace('#', '') || 'home';
    const parts = raw.split('/').filter(Boolean);
    const mainPage = parts[0] || 'home';

    const validPages = ['home', 'about', 'products', 'market', 'custom', 'contact'];
    const pageId = validPages.includes(mainPage) ? mainPage : 'home';

    if (pageId === 'products') {
      // Parse 4-Tier drilldown params
      // #products
      // #products/category/:catSlug
      // #products/category/:catSlug/series/:seriesSlug
      // #products/category/:catSlug/series/:seriesSlug/sku/:skuId
      if (parts.length >= 7 && parts[1] === 'category' && parts[3] === 'series' && parts[5] === 'sku') {
        state.productLevel = 4;
        state.activeCatSlug = parts[2];
        state.activeSeriesSlug = parts[4];
        state.activeSkuId = parts[6];
        state.activePdpImgIdx = 0;
      } else if (parts.length >= 5 && parts[1] === 'category' && parts[3] === 'series') {
        state.productLevel = 3;
        state.activeCatSlug = parts[2];
        state.activeSeriesSlug = parts[4];
        state.activeSkuId = null;
      } else if (parts.length >= 3 && parts[1] === 'category') {
        state.productLevel = 2;
        state.activeCatSlug = parts[2];
        state.activeSeriesSlug = null;
        state.activeSkuId = null;
      } else {
        state.productLevel = 1;
        state.activeCatSlug = null;
        state.activeSeriesSlug = null;
        state.activeSkuId = null;
      }
    }

    navigateToPage(pageId, false);
  };

  window.addEventListener('hashchange', handleHash);
  handleHash();
}

export function navigateToPage(pageId, updateHash = true) {
  state.activePage = pageId;

  if (updateHash) {
    if (pageId === 'products') {
      syncProductsHash();
    } else {
      window.location.hash = pageId;
    }
  }

  // Toggle page visibility
  document.querySelectorAll('.page-view').forEach(view => {
    view.style.display = 'none';
  });

  const targetId = pageId === 'market' ? 'home' : pageId;
  const activeView = document.getElementById(`view-${targetId}`);
  if (activeView) {
    activeView.style.display = 'block';
  }

  // Update top nav links
  document.querySelectorAll('.frapak-nav-link').forEach(link => {
    link.classList.toggle('active', link.dataset.page === pageId);
  });

  // Update mobile nav links
  document.querySelectorAll('.mobile-nav-item').forEach(link => {
    link.classList.toggle('active', link.dataset.page === pageId);
  });

  if (pageId === 'products') {
    renderProductsDrilldown();
  }

  if (pageId === 'market') {
    const marketGrid = document.getElementById('home-market-tiles-grid');
    if (marketGrid) {
      marketGrid.scrollIntoView({ behavior: 'smooth' });
    }
  } else {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
}

window.navigateToPage = navigateToPage;

// ==========================================================================
// MOBILE MENU CONTROLLERS
// ==========================================================================
window.toggleMobileMenu = () => {
  const drawer = document.getElementById('mobile-nav-drawer');
  const isOpen = drawer && drawer.classList.contains('open');
  if (isOpen) {
    window.closeMobileMenu();
  } else {
    window.openMobileMenu();
  }
};

window.openMobileMenu = () => {
  const drawer = document.getElementById('mobile-nav-drawer');
  const backdrop = document.getElementById('mobile-nav-backdrop');
  const btn = document.getElementById('frapak-mobile-menu-btn');
  if (drawer) drawer.classList.add('open');
  if (backdrop) backdrop.classList.add('open');
  if (btn) {
    btn.classList.add('active');
    btn.setAttribute('aria-expanded', 'true');
  }
  document.body.classList.add('menu-open');
};

window.closeMobileMenu = () => {
  const drawer = document.getElementById('mobile-nav-drawer');
  const backdrop = document.getElementById('mobile-nav-backdrop');
  const btn = document.getElementById('frapak-mobile-menu-btn');
  if (drawer) drawer.classList.remove('open');
  if (backdrop) backdrop.classList.remove('open');
  if (btn) {
    btn.classList.remove('active');
    btn.setAttribute('aria-expanded', 'false');
  }
  document.body.classList.remove('menu-open');
  const popover = document.getElementById('mobile-search-popover');
  if (popover) popover.classList.remove('open');
};

// Mobile Filter Drawer Controllers (for Catalogue Level 2 & 3)
window.toggleMobileFilters = () => {
  const sidebar = document.querySelector('.frapak-filter-sidebar');
  const backdrop = document.getElementById('filter-drawer-backdrop');
  if (sidebar) {
    sidebar.classList.toggle('mobile-open');
    const isOpen = sidebar.classList.contains('mobile-open');
    if (backdrop) backdrop.classList.toggle('open', isOpen);
    document.body.classList.toggle('filters-open', isOpen);
  }
};

window.closeMobileFilters = () => {
  const sidebar = document.querySelector('.frapak-filter-sidebar');
  const backdrop = document.getElementById('filter-drawer-backdrop');
  if (sidebar) sidebar.classList.remove('mobile-open');
  if (backdrop) backdrop.classList.remove('open');
  document.body.classList.remove('filters-open');
};

// Global Escape listener
window.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    window.closeMobileMenu();
    window.closeMobileFilters();
    window.closeInquiry();
    window.closeProductModal();
  }
});

// ==========================================================================
// MOBILE ACCORDION & READ MORE CONTROLLERS
// ==========================================================================

// PCR Content Read More Toggle
window.togglePcrContent = () => {
  const content = document.getElementById('pcr-expandable-content');
  const btn = document.getElementById('pcr-readmore-toggle');
  if (!content || !btn) return;
  const isExpanded = content.classList.contains('open');
  if (isExpanded) {
    content.classList.remove('open');
    btn.setAttribute('aria-expanded', 'false');
    btn.innerHTML = '<span>Read more +</span>';
  } else {
    content.classList.add('open');
    btn.setAttribute('aria-expanded', 'true');
    btn.innerHTML = '<span>Show less &minus;</span>';
  }
};

// Packaging Experience Cards Read More Toggle
window.toggleExpCard = (btn) => {
  const card = btn.closest('.frapak-exp-card');
  if (!card) return;
  const isExpanded = card.classList.contains('open');
  if (isExpanded) {
    card.classList.remove('open');
    btn.setAttribute('aria-expanded', 'false');
    btn.innerHTML = '<span>Read more &rarr;</span>';
  } else {
    card.classList.add('open');
    btn.setAttribute('aria-expanded', 'true');
    btn.innerHTML = '<span>Show less &uarr;</span>';
  }
};

function syncProductsHash() {
  if (state.productLevel === 4 && state.activeCatSlug && state.activeSeriesSlug && state.activeSkuId) {
    window.location.hash = `products/category/${state.activeCatSlug}/series/${state.activeSeriesSlug}/sku/${state.activeSkuId}`;
  } else if (state.productLevel === 3 && state.activeCatSlug && state.activeSeriesSlug) {
    window.location.hash = `products/category/${state.activeCatSlug}/series/${state.activeSeriesSlug}`;
  } else if (state.productLevel === 2 && state.activeCatSlug) {
    window.location.hash = `products/category/${state.activeCatSlug}`;
  } else {
    window.location.hash = 'products';
  }
}

// ==========================================================================
// 2. LEVEL NAVIGATION CONTROLLERS
// ==========================================================================

// Select Category (Level 1 -> Level 2, or back to Level 1)
window.selectCategory = (catSlug) => {
  if (!catSlug) {
    state.productLevel = 1;
    state.activeCatSlug = null;
    state.activeSeriesSlug = null;
    state.activeSkuId = null;
  } else {
    state.productLevel = 2;
    state.activeCatSlug = catSlug;
    state.activeSeriesSlug = null;
    state.activeSkuId = null;
  }
  // Reset filters
  state.filters = { stock: [], material: [], volume: [] };
  syncProductsHash();
  renderProductsDrilldown();
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Select Series (Level 2 -> Level 3)
window.selectSeries = (catSlug, seriesSlug) => {
  state.productLevel = 3;
  state.activeCatSlug = catSlug;
  state.activeSeriesSlug = seriesSlug;
  state.activeSkuId = null;
  state.filters = { stock: [], material: [], volume: [] };
  syncProductsHash();
  renderProductsDrilldown();
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Select SKU (Level 3 -> Level 4 PDP)
window.selectSku = (skuId) => {
  const sku = SKUS_CATALOGUE.find(s => s.id === skuId);
  if (!sku) return;

  state.productLevel = 4;
  state.activeCatSlug = sku.categorySlug;
  state.activeSeriesSlug = sku.seriesSlug;
  state.activeSkuId = sku.id;
  state.activePdpImgIdx = 0;
  syncProductsHash();
  renderProductsDrilldown();
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Back Button Navigation (Matches Screenshot "[ Back ]" button)
window.goBackProductLevel = () => {
  if (state.productLevel === 4) {
    // Return to Level 3
    state.productLevel = 3;
    state.activeSkuId = null;
  } else if (state.productLevel === 3) {
    // Return to Level 2
    state.productLevel = 2;
    state.activeSeriesSlug = null;
  } else if (state.productLevel === 2) {
    // Return to Level 1
    state.productLevel = 1;
    state.activeCatSlug = null;
  }
  syncProductsHash();
  renderProductsDrilldown();
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Quick navigation from Homepage tiles or footer
window.goToCategory = (identifier) => {
  let cat = FRAPAK_CATEGORIES_12.find(c => c.slug === identifier || c.name.toLowerCase() === identifier.toLowerCase());
  if (!cat) {
    cat = FRAPAK_CATEGORIES_12[0];
  }
  state.productLevel = 2;
  state.activeCatSlug = cat.slug;
  state.activeSeriesSlug = null;
  state.activeSkuId = null;
  state.filters = { stock: [], material: [], volume: [] };
  navigateToPage('products', true);
};

// ==========================================================================
// 3. PRODUCTS 4-TIER DRILLDOWN RENDERER
// ==========================================================================
function renderProductsDrilldown() {
  const container = document.getElementById('frapak-drilldown-container');
  if (!container) return;

  if (state.productLevel === 1) {
    renderLevel1(container);
  } else if (state.productLevel === 2) {
    renderLevel2(container);
  } else if (state.productLevel === 3) {
    renderLevel3(container);
  } else if (state.productLevel === 4) {
    renderLevel4(container);
  }
}

// --------------------------------------------------------------------------
// LEVEL 1: CATEGORIES OVERVIEW (Matching Image 1)
// --------------------------------------------------------------------------
function renderLevel1(container) {
  const titleElem = document.getElementById('products-view-main-title');
  const badgeElem = document.getElementById('products-hero-badge');
  const subElem = document.getElementById('products-hero-subtitle');

  if (titleElem) titleElem.innerText = 'Products of TrueNorth Group';
  if (badgeElem) badgeElem.innerText = 'TRUENORTH PRODUCTS';
  if (subElem) subElem.innerText = 'Premium Primary Packaging Solutions';

  container.innerHTML = `
    <div class="products-intro-wrap" style="margin-bottom: 26px !important; padding-bottom: 0 !important;">
      <h2 class="products-intro-title" style="margin-bottom: 8px !important; font-size: 1.6rem; font-weight: 700; color: var(--color-navy);">Products of TrueNorth Group</h2>
      <p class="products-intro-desc" style="margin-bottom: 0 !important; color: #475569 !important; font-size: 1rem !important; line-height: 1.6 !important;">
        Explore our complete catalogue of primary packaging containers, closures, dispensing pumps, and sprayers.
      </p>
    </div>

    <!-- 12 Category Tiles Grid -->
    <div class="frapak-level1-grid" style="margin-top: 0 !important;">
      ${FRAPAK_CATEGORIES_12.map(cat => `
        <div class="frapak-tile-card" onclick="window.selectCategory('${cat.slug}')">
          <div class="frapak-tile-image">
            <img src="${cat.image}" alt="${cat.name}" loading="lazy" />
          </div>
          <div class="frapak-tile-bar">${cat.name}</div>
        </div>
      `).join('')}
    </div>
  `;
}

// --------------------------------------------------------------------------
// LEVEL 2: SERIES WITHIN CATEGORY (Matching Image 2)
// --------------------------------------------------------------------------
function renderLevel2(container) {
  const currentCat = FRAPAK_CATEGORIES_12.find(c => c.slug === state.activeCatSlug) || FRAPAK_CATEGORIES_12[0];
  const titleElem = document.getElementById('products-view-main-title');
  if (titleElem) titleElem.innerText = currentCat.name;

  // Filter series belonging to this category
  let seriesList = SERIES_CATALOGUE.filter(s => s.categorySlug === currentCat.slug);
  if (seriesList.length === 0) {
    // Fallback if series not mapped yet
    seriesList = SERIES_CATALOGUE.filter(s => s.categorySlug === 'pet-bottles');
  }

  // Check filter criteria on series
  const filteredSeries = seriesList.filter(s => {
    if (state.filters.material.length > 0) {
      const match = state.filters.material.some(m => s.material.toUpperCase().includes(m.toUpperCase()));
      if (!match) return false;
    }
    return true;
  });

  container.innerHTML = `
    <!-- Breadcrumb Bar -->
    <div class="frapak-breadcrumbs">
      <a href="#products" onclick="window.selectCategory(null)">Products</a>
      <span class="separator">&gt;</span>
      <span class="current">${currentCat.name}</span>
    </div>

    <button class="frapak-back-btn" onclick="window.goBackProductLevel()">
      Back
    </button>

    <!-- Mobile Filter Bar Button (Visible on <= 768px) -->
    <div class="frapak-mobile-filter-bar">
      <button class="frapak-mobile-filter-btn" onclick="window.toggleMobileFilters()" aria-label="Filter Products">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
        <span>Filter Products</span>
        ${(state.filters.stock.length + state.filters.material.length + state.filters.volume.length) > 0 ? `<span class="frapak-filter-count-badge">${state.filters.stock.length + state.filters.material.length + state.filters.volume.length}</span>` : ''}
      </button>
      <span class="frapak-mobile-results-count">${filteredSeries.length} series</span>
    </div>

    <!-- Backdrop for Mobile Filters -->
    <div class="frapak-filter-backdrop" id="filter-drawer-backdrop" onclick="window.closeMobileFilters()"></div>

    <div class="frapak-catalogue-layout">
      <!-- Left Sidebar Filters Matching Screenshot 2 -->
      <aside class="frapak-filter-sidebar">
        <div class="frapak-filter-mobile-header">
          <span>Filter Products ${(state.filters.stock.length + state.filters.material.length + state.filters.volume.length) > 0 ? `(${state.filters.stock.length + state.filters.material.length + state.filters.volume.length})` : ''}</span>
          <button class="frapak-filter-close-btn" onclick="window.closeMobileFilters()" aria-label="Close Filters">&times;</button>
        </div>
        <!-- Stock item filter -->
        <div class="frapak-filter-group">
          <div class="frapak-filter-header">
            Stock item <span class="toggle-icon">${ICONS.chevronUp}</span>
          </div>
          <div class="frapak-filter-list">
            <label class="frapak-filter-item">
              <input type="checkbox" value="no" ${state.filters.stock.includes('no') ? 'checked' : ''} onchange="window.toggleFilter('stock', 'no')">
              No (755)
            </label>
            <label class="frapak-filter-item">
              <input type="checkbox" value="yes" ${state.filters.stock.includes('yes') ? 'checked' : ''} onchange="window.toggleFilter('stock', 'yes')">
              Yes (70)
            </label>
          </div>
        </div>

        <!-- Material filter -->
        <div class="frapak-filter-group">
          <div class="frapak-filter-header">
            Material <span class="toggle-icon">${ICONS.chevronUp}</span>
          </div>
          <div class="frapak-filter-list">
            <label class="frapak-filter-item">
              <input type="checkbox" value="PET" ${state.filters.material.includes('PET') ? 'checked' : ''} onchange="window.toggleFilter('material', 'PET')">
              PET (454)
            </label>
            <label class="frapak-filter-item">
              <input type="checkbox" value="RPET" ${state.filters.material.includes('RPET') ? 'checked' : ''} onchange="window.toggleFilter('material', 'RPET')">
              RPET (371)
            </label>
          </div>
        </div>

        <!-- Volume filter -->
        <div class="frapak-filter-group">
          <div class="frapak-filter-header">
            Volume <span class="toggle-icon">${ICONS.chevronUp}</span>
          </div>
          <div class="frapak-filter-list">
            <label class="frapak-filter-item">
              <input type="checkbox" value="50" ${state.filters.volume.includes('50') ? 'checked' : ''} onchange="window.toggleFilter('volume', '50')">
              50 ml (12)
            </label>
            <label class="frapak-filter-item">
              <input type="checkbox" value="100" ${state.filters.volume.includes('100') ? 'checked' : ''} onchange="window.toggleFilter('volume', '100')">
              100 ml (24)
            </label>
            <label class="frapak-filter-item">
              <input type="checkbox" value="200" ${state.filters.volume.includes('200') ? 'checked' : ''} onchange="window.toggleFilter('volume', '200')">
              200 ml (18)
            </label>
            <label class="frapak-filter-item">
              <input type="checkbox" value="250" ${state.filters.volume.includes('250') ? 'checked' : ''} onchange="window.toggleFilter('volume', '250')">
              250 ml (16)
            </label>
            <label class="frapak-filter-item">
              <input type="checkbox" value="500" ${state.filters.volume.includes('500') ? 'checked' : ''} onchange="window.toggleFilter('volume', '500')">
              500 ml (14)
            </label>
            <label class="frapak-filter-item">
              <input type="checkbox" value="1000" ${state.filters.volume.includes('1000') ? 'checked' : ''} onchange="window.toggleFilter('volume', '1000')">
              1000 ml (8)
            </label>
          </div>
        </div>

        <div class="frapak-filter-mobile-actions">
          <button class="frapak-btn-gold" style="width: 100%; margin: 0; padding: 12px;" onclick="window.closeMobileFilters()">Show ${filteredSeries.length} Results</button>
        </div>
      </aside>

      <!-- Main Series Grid -->
      <div>
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: baseline;">
          <h2 style="font-size: 1.55rem; font-weight: 800; color: var(--color-navy); margin: 0;">
            ${currentCat.name}
          </h2>
          <span style="font-size: 0.8125rem; color: #777;">
            Showing ${filteredSeries.length} ${currentCat.slug === 'caps' ? 'cap & closure series' : (currentCat.slug === 'pumps' ? 'dispenser pump series' : (currentCat.slug.includes('sprayer') ? 'sprayer series' : (currentCat.slug.includes('bottle') ? 'bottle series' : 'series')))}
          </span>
        </div>

        <div class="frapak-series-grid">
          ${filteredSeries.map(s => `
            <div class="frapak-tile-card" onclick="window.selectSeries('${currentCat.slug}', '${s.slug}')">
              <div class="frapak-tile-image">
                <img src="${s.image}" alt="${s.name}" loading="lazy" />
              </div>
              <div class="frapak-tile-bar">${s.name}</div>
            </div>
          `).join('')}
        </div>
      </div>
    </div>
  `;
}

// --------------------------------------------------------------------------
// LEVEL 3: SIZES / SKUS WITHIN SERIES (Matching Image 3)
// --------------------------------------------------------------------------
function renderLevel3(container) {
  const currentCat = FRAPAK_CATEGORIES_12.find(c => c.slug === state.activeCatSlug) || FRAPAK_CATEGORIES_12[0];
  const currentSeries = SERIES_CATALOGUE.find(s => s.slug === state.activeSeriesSlug) || SERIES_CATALOGUE[0];

  const titleElem = document.getElementById('products-view-main-title');
  if (titleElem) titleElem.innerText = `${currentSeries.name} bottles`;

  // Find SKUs for this series
  let skus = SKUS_CATALOGUE.filter(s => {
    if (!s) return false;
    if (s.seriesSlug === currentSeries.slug) return true;
    if (s.serie && currentSeries.name && s.serie.toLowerCase() === currentSeries.name.toLowerCase()) return true;
    if (s.seriesSlug && currentSeries.slug && (s.seriesSlug.includes(currentSeries.slug) || currentSeries.slug.includes(s.seriesSlug))) return true;
    return false;
  });
  if (skus.length === 0) {
    skus = SKUS_CATALOGUE.filter(s => s && s.categorySlug === currentCat.slug);
  }

  // Apply filters
  const filteredSkus = skus.filter(sku => {
    if (state.filters.stock.length > 0) {
      if (state.filters.stock.includes('yes') && !sku.isStock) return false;
      if (state.filters.stock.includes('no') && sku.isStock) return false;
    }
    if (state.filters.material.length > 0) {
      const match = state.filters.material.some(m => sku.material.toUpperCase().includes(m.toUpperCase()));
      if (!match) return false;
    }
    if (state.filters.volume.length > 0) {
      const match = state.filters.volume.some(v => sku.volume.includes(v));
      if (!match) return false;
    }
    return true;
  });

  const stockYesCount = skus.filter(s => s.isStock).length;
  const stockNoCount = skus.filter(s => !s.isStock).length;

  container.innerHTML = `
    <!-- Top Title and Back Button Matching Screenshot 3 -->
    <h2 style="font-size: 1.65rem; font-weight: 800; color: var(--color-navy); margin-bottom: 12px;">
      ${currentSeries.name}${currentCat.slug.includes('bottle') && !currentSeries.name.toLowerCase().includes('bottle') ? ' bottles' : ''}
    </h2>

    <button class="frapak-back-btn" onclick="window.goBackProductLevel()">
      Back
    </button>

    <!-- Mobile Filter Bar Button (Visible on <= 768px) -->
    <div class="frapak-mobile-filter-bar">
      <button class="frapak-mobile-filter-btn" onclick="window.toggleMobileFilters()" aria-label="Filter Products">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
        <span>Filter Products</span>
        ${(state.filters.stock.length + state.filters.material.length + state.filters.volume.length) > 0 ? `<span class="frapak-filter-count-badge">${state.filters.stock.length + state.filters.material.length + state.filters.volume.length}</span>` : ''}
      </button>
      <span class="frapak-mobile-results-count">${filteredSkus.length} SKUs</span>
    </div>

    <!-- Backdrop for Mobile Filters -->
    <div class="frapak-filter-backdrop" id="filter-drawer-backdrop" onclick="window.closeMobileFilters()"></div>

    <div class="frapak-catalogue-layout">
      <!-- Left Filter Sidebar Matching Screenshot 3 -->
      <aside class="frapak-filter-sidebar">
        <div class="frapak-filter-mobile-header">
          <span>Filter Products ${(state.filters.stock.length + state.filters.material.length + state.filters.volume.length) > 0 ? `(${state.filters.stock.length + state.filters.material.length + state.filters.volume.length})` : ''}</span>
          <button class="frapak-filter-close-btn" onclick="window.closeMobileFilters()" aria-label="Close Filters">&times;</button>
        </div>
        <!-- Stock item -->
        <div class="frapak-filter-group">
          <div class="frapak-filter-header">
            Stock item <span class="toggle-icon">${ICONS.chevronUp}</span>
          </div>
          <div class="frapak-filter-list">
            <label class="frapak-filter-item">
              <input type="checkbox" value="no" ${state.filters.stock.includes('no') ? 'checked' : ''} onchange="window.toggleFilter('stock', 'no')">
              No (${stockNoCount || 83})
            </label>
            <label class="frapak-filter-item">
              <input type="checkbox" value="yes" ${state.filters.stock.includes('yes') ? 'checked' : ''} onchange="window.toggleFilter('stock', 'yes')">
              Yes (${stockYesCount || 3})
            </label>
          </div>
        </div>

        <!-- Material -->
        <div class="frapak-filter-group">
          <div class="frapak-filter-header">
            Material <span class="toggle-icon">${ICONS.chevronUp}</span>
          </div>
          <div class="frapak-filter-list">
            <label class="frapak-filter-item">
              <input type="checkbox" value="PET" ${state.filters.material.includes('PET') ? 'checked' : ''} onchange="window.toggleFilter('material', 'PET')">
              PET (45)
            </label>
            <label class="frapak-filter-item">
              <input type="checkbox" value="RPET" ${state.filters.material.includes('RPET') ? 'checked' : ''} onchange="window.toggleFilter('material', 'RPET')">
              RPET (41)
            </label>
          </div>
        </div>

        <!-- Volume -->
        <div class="frapak-filter-group">
          <div class="frapak-filter-header">
            Volume <span class="toggle-icon">${ICONS.chevronUp}</span>
          </div>
          <div class="frapak-filter-list">
            <label class="frapak-filter-item">
              <input type="checkbox" value="50" ${state.filters.volume.includes('50') ? 'checked' : ''} onchange="window.toggleFilter('volume', '50')">
              50 ml
            </label>
            <label class="frapak-filter-item">
              <input type="checkbox" value="100" ${state.filters.volume.includes('100') ? 'checked' : ''} onchange="window.toggleFilter('volume', '100')">
              100 ml
            </label>
            <label class="frapak-filter-item">
              <input type="checkbox" value="200" ${state.filters.volume.includes('200') ? 'checked' : ''} onchange="window.toggleFilter('volume', '200')">
              200 ml
            </label>
            <label class="frapak-filter-item">
              <input type="checkbox" value="250" ${state.filters.volume.includes('250') ? 'checked' : ''} onchange="window.toggleFilter('volume', '250')">
              250 ml
            </label>
            <label class="frapak-filter-item">
              <input type="checkbox" value="500" ${state.filters.volume.includes('500') ? 'checked' : ''} onchange="window.toggleFilter('volume', '500')">
              500 ml
            </label>
            <label class="frapak-filter-item">
              <input type="checkbox" value="1000" ${state.filters.volume.includes('1000') ? 'checked' : ''} onchange="window.toggleFilter('volume', '1000')">
              1000 ml
            </label>
          </div>
        </div>

        <div class="frapak-filter-mobile-actions">
          <button class="frapak-btn-gold" style="width: 100%; margin: 0; padding: 12px;" onclick="window.closeMobileFilters()">Show ${filteredSkus.length} Results</button>
        </div>
      </aside>

      <!-- Main SKUs Grid Matching Screenshot 3 -->
      <div>
        <div class="frapak-skus-grid">
          ${filteredSkus.map(sku => `
            <div class="frapak-sku-card" onclick="window.selectSku('${sku.id}')">
              ${sku.isStock ? `<div class="frapak-sku-stock-indicator" title="Stock Available"></div>` : ''}
              <div class="frapak-sku-img">
                <img src="${sku.image}" alt="${sku.name}" loading="lazy" />
              </div>
              <div class="frapak-sku-bar">${sku.name}</div>
              
              <!-- Attribute Rows with Icons Matching Screenshot 3 -->
              <div class="frapak-sku-specs-table">
                <div class="frapak-sku-spec-row">
                  <span class="frapak-sku-spec-icon">${ICONS.barcode}</span>
                  <span class="frapak-sku-spec-val">${sku.articleNo}</span>
                </div>
                <div class="frapak-sku-spec-row">
                  <span class="frapak-sku-spec-icon">${ICONS.recycle}</span>
                  <span class="frapak-sku-spec-val">${sku.material}</span>
                </div>
                <div class="frapak-sku-spec-row">
                  <span class="frapak-sku-spec-icon">${ICONS.bottle}</span>
                  <span class="frapak-sku-spec-val">${sku.shape}</span>
                </div>
                <div class="frapak-sku-spec-row">
                  <span class="frapak-sku-spec-icon">${ICONS.palette}</span>
                  <span class="frapak-sku-spec-val">${sku.color}</span>
                </div>
                <div class="frapak-sku-spec-row">
                  <span class="frapak-sku-spec-icon">${ICONS.box}</span>
                  <span class="frapak-sku-spec-val">${sku.moq}</span>
                </div>
              </div>
            </div>
          `).join('')}
        </div>
      </div>
    </div>
  `;
}

// --------------------------------------------------------------------------
// LEVEL 4: BOTTLE MAIN DETAIL PAGE (Matching Image 4)
// --------------------------------------------------------------------------
function renderLevel4(container) {
  const sku = SKUS_CATALOGUE.find(s => s.id === state.activeSkuId) || SKUS_CATALOGUE[0];
  const gallery = sku.gallery && sku.gallery.length > 0 ? sku.gallery : [sku.image];
  const activeImg = gallery[state.activePdpImgIdx] || gallery[0];

  const titleElem = document.getElementById('products-view-main-title');
  if (titleElem) titleElem.innerText = sku.fullTitle || sku.name;

  container.innerHTML = `
    <!-- Top Heading Matching Screenshot 4 -->
    <h2 style="font-size: 1.55rem; font-weight: 800; color: var(--color-navy); margin-bottom: 12px; line-height: 1.35;">
      ${sku.fullTitle || sku.name}
    </h2>

    <button class="frapak-back-btn" onclick="window.goBackProductLevel()">
      Back
    </button>

    <!-- Breadcrumbs -->
    <div class="frapak-breadcrumbs">
      <a href="#products" onclick="window.selectCategory(null)">Products</a>
      <span class="separator">&gt;</span>
      <a href="#products/category/${sku.categorySlug}" onclick="window.selectCategory('${sku.categorySlug}')">${sku.categorySlug}</a>
      <span class="separator">&gt;</span>
      <a href="#products/category/${sku.categorySlug}/series/${sku.seriesSlug}" onclick="window.selectSeries('${sku.categorySlug}', '${sku.seriesSlug}')">${sku.serie}</a>
      <span class="separator">&gt;</span>
      <span class="current">${sku.name}</span>
    </div>

    <!-- 2-Column Product Detail Layout Matching Screenshot 4 -->
    <div class="frapak-pdp-layout">
      
      <!-- Left Column: Image Stage & Vertical Thumbnails -->
      <div class="frapak-pdp-gallery-container">
        
        <!-- Main Large Image Viewport -->
        <div class="frapak-pdp-main-stage">
          <button class="frapak-pdp-zoom-btn" onclick="window.zoomPdpImage()" title="Enlarge image" style="display: inline-flex; align-items: center; justify-content: center;">${ICONS.zoom}</button>
          ${gallery.length > 1 ? `
            <button class="frapak-pdp-nav-prev" onclick="window.prevPdpImage()" aria-label="Previous image" style="display: inline-flex; align-items: center; justify-content: center;">${ICONS.chevronLeft}</button>
            <button class="frapak-pdp-nav-next" onclick="window.nextPdpImage()" aria-label="Next image" style="display: inline-flex; align-items: center; justify-content: center;">${ICONS.chevronRight}</button>
          ` : ''}
          <img id="pdp-active-display-img" src="${activeImg}" alt="${sku.name}" />
        </div>

        <!-- Vertical Thumbnails Column -->
        <div class="frapak-pdp-thumbs-col">
          ${gallery.map((g, idx) => `
            <div class="frapak-pdp-thumb-item ${state.activePdpImgIdx === idx ? 'active' : ''}" onclick="window.switchPdpImage(${idx})">
              <img src="${g}" alt="${sku.name} view ${idx + 1}" />
            </div>
          `).join('')}
        </div>

      </div>

      <!-- Right Column: Specs Table Matching Screenshot 4 -->
      <div class="frapak-pdp-specs-card">
        <table>
          <tbody>
            <tr><td>Material</td><td>${sku.material}</td></tr>
            <tr><td>Serie</td><td>${sku.serie}</td></tr>
            <tr><td>Article no.</td><td>${sku.articleNo}</td></tr>
            <tr><td>Neck size</td><td>${sku.neckSize}</td></tr>
            <tr><td>Shape</td><td>${sku.shape}</td></tr>
            <tr><td>Volume</td><td>${sku.volume}</td></tr>
            <tr><td>Weight</td><td>${sku.weight}</td></tr>
            <tr><td>Color</td><td>${sku.color}</td></tr>
            <tr><td>Stock</td><td>${sku.isStock ? 'Yes' : 'No'}</td></tr>
            <tr><td>Website no.</td><td>${sku.websiteNo}</td></tr>
            <tr><td>MOQ <span style="display: inline-flex; vertical-align: middle; cursor: help;" title="Minimum Order Quantity">${ICONS.info}</span></td><td>${sku.moq}</td></tr>
          </tbody>
        </table>

        <!-- Action Buttons -->
        <div class="frapak-pdp-cta-wrap">
          <button class="frapak-btn-gold" style="margin: 0; padding: 12px 28px; background: #15803d; border-color: #15803d;" onclick="window.openInquiryForSku('${sku.id}')">
            REQUEST A QUOTE
          </button>
          <button class="frapak-btn-outline" style="color: var(--color-navy); border-color: var(--color-navy); padding: 12px 22px;" onclick="window.openSampleInquiry('${sku.id}')">
            REQUEST A SAMPLE
          </button>
        </div>

        <!-- Detailed Description & Compatible Closures -->
        <div style="margin-top: 28px; border-top: 1px solid #E5E7EB; padding-top: 20px;">
          <h4 style="font-size: 0.9375rem; font-weight: 700; color: var(--color-navy); margin-bottom: 6px;">Product Overview</h4>
          <p style="font-size: 0.8125rem; color: #555; line-height: 1.6; margin-bottom: 14px;">
            ${sku.description}
          </p>

          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 14px; border-radius: var(--radius-sm);">
            <strong style="font-size: 0.8125rem; color: var(--color-navy); display: block; margin-bottom: 4px;">
              Compatible Closures & Pumps:
            </strong>
            <span style="font-size: 0.8125rem; color: #475569;">
              ${sku.compatibleClosures || 'Standard matching closures available.'}
            </span>
          </div>
        </div>

      </div>

    </div>
  `;
}

// PDP Image Gallery Interactions
window.switchPdpImage = (idx) => {
  state.activePdpImgIdx = idx;
  const sku = SKUS_CATALOGUE.find(s => s.id === state.activeSkuId) || SKUS_CATALOGUE[0];
  const gallery = sku.gallery && sku.gallery.length > 0 ? sku.gallery : [sku.image];
  const displayImg = document.getElementById('pdp-active-display-img');
  if (displayImg && gallery[idx]) {
    displayImg.src = gallery[idx];
  }
  document.querySelectorAll('.frapak-pdp-thumb-item').forEach((t, i) => {
    t.classList.toggle('active', i === idx);
  });
};

window.prevPdpImage = () => {
  const sku = SKUS_CATALOGUE.find(s => s.id === state.activeSkuId) || SKUS_CATALOGUE[0];
  const gallery = sku.gallery && sku.gallery.length > 0 ? sku.gallery : [sku.image];
  let nextIdx = state.activePdpImgIdx - 1;
  if (nextIdx < 0) nextIdx = gallery.length - 1;
  window.switchPdpImage(nextIdx);
};

window.nextPdpImage = () => {
  const sku = SKUS_CATALOGUE.find(s => s.id === state.activeSkuId) || SKUS_CATALOGUE[0];
  const gallery = sku.gallery && sku.gallery.length > 0 ? sku.gallery : [sku.image];
  let nextIdx = (state.activePdpImgIdx + 1) % gallery.length;
  window.switchPdpImage(nextIdx);
};

window.zoomPdpImage = () => {
  const sku = SKUS_CATALOGUE.find(s => s.id === state.activeSkuId) || SKUS_CATALOGUE[0];
  const gallery = sku.gallery && sku.gallery.length > 0 ? sku.gallery : [sku.image];
  const currentImg = gallery[state.activePdpImgIdx] || sku.image;
  window.open(currentImg, '_blank');
};

// Sidebar Filters Handler
window.toggleFilter = (filterType, value) => {
  const list = state.filters[filterType];
  const idx = list.indexOf(value);
  if (idx > -1) {
    list.splice(idx, 1);
  } else {
    list.push(value);
  }
  renderProductsDrilldown();
};

// ==========================================================================
// 4. HOMEPAGE TILES (Matches Screenshot 1 Exactly)
// ==========================================================================
function renderHomeCatalogueTiles() {
  const grid = document.getElementById('home-cat-tiles-grid');
  if (!grid) return;

  grid.innerHTML = FRAPAK_CATEGORIES_12.map(cat => `
    <div class="frapak-tile-card" onclick="window.goToCategory('${cat.slug}')">
      <div class="frapak-tile-image">
        <img src="${cat.image}" alt="${cat.name}" loading="lazy" />
      </div>
      <div class="frapak-tile-bar">${cat.name}</div>
    </div>
  `).join('');
}

function renderHomeMarketTiles() {
  const grid = document.getElementById('home-markets-grid');
  if (!grid) return;

  grid.innerHTML = FRAPAK_MARKETS_5.map(m => `
    <div class="frapak-market-tile" onclick="window.goToCategory('pet-bottles')">
      <div class="frapak-market-img">
        <img src="${m.image}" alt="${m.title}" loading="lazy" />
      </div>
      <div class="frapak-tile-bar">${m.title}</div>
    </div>
  `).join('');
}

function populateCountries() {
  const selects = document.querySelectorAll('.country-select');
  selects.forEach(select => {
    select.innerHTML = `<option value="">Select Country</option>` +
      COUNTRIES.map(c => `<option value="${c}">${c}</option>`).join('');
  });
}

// ==========================================================================
// 5. INQUIRY & SAMPLE REQUEST DRAWER
// ==========================================================================
window.openInquiry = (productId = null, isSample = false) => {
  const drawer = document.getElementById('inquiry-drawer');
  const overlay = document.getElementById('inquiry-drawer-overlay');
  const badge = document.getElementById('inquiry-product-badge');
  const textarea = document.getElementById('inquiry-notes-field');

  if (productId) {
    const p = SKUS_CATALOGUE.find(i => i.id === productId) || ALL_PRODUCTS_DATA.find(i => i.id === productId);
    if (p) {
      state.selectedProduct = p;
      if (badge) {
        badge.style.display = 'block';
        badge.innerHTML = `
          <div style="background: var(--color-off-white); border: 1px solid var(--color-border); border-left: 3px solid var(--color-gold); border-radius: var(--radius-sm); padding: 8px 12px; margin-bottom: 14px; font-size: 0.8125rem;">
            <strong>Selected Product:</strong> ${p.fullTitle || p.name} (Art: ${p.articleNo || 'N/A'}, MOQ: ${p.moq || 'N/A'})
          </div>
        `;
      }
      if (textarea && !textarea.value) {
        textarea.value = isSample 
          ? `Requesting technical sample kit for: ${p.name} (${p.volume || p.capacity}, Art No: ${p.articleNo || ''}). Target application and delivery location:` 
          : `Inquiry for bulk pricing on: ${p.name} (${p.volume || p.capacity}, Art No: ${p.articleNo || ''}). Target order volume: ${p.moq || '5,000 units'}.`;
      }
    } else {
      // General Inquiry Topic (e.g. Facility Audit or Custom Packaging)
      state.selectedProduct = null;
      if (badge) {
        badge.style.display = 'block';
        badge.innerHTML = `
          <div style="background: var(--color-off-white); border: 1px solid var(--color-border); border-left: 3px solid var(--color-gold); border-radius: var(--radius-sm); padding: 8px 12px; margin-bottom: 14px; font-size: 0.8125rem;">
            <strong>Inquiry Topic:</strong> ${productId}
          </div>
        `;
      }
      if (textarea) {
        textarea.value = `Inquiry regarding: ${productId}.\nPlease specify required documentation (e.g. TÜV NORD ISO 9001, US FDA DMF, COA, TDS, MSDS) or custom packaging specifications:`;
      }
    }
  } else {
    state.selectedProduct = null;
    if (badge) badge.style.display = 'none';
  }

  if (drawer) drawer.classList.add('open');
  if (overlay) overlay.classList.add('open');
};

window.openInquiryForSku = (skuId) => {
  window.openInquiry(skuId, false);
};

window.openSampleInquiry = (skuId) => {
  window.openInquiry(skuId, true);
};

window.closeInquiry = () => {
  const drawer = document.getElementById('inquiry-drawer');
  const overlay = document.getElementById('inquiry-drawer-overlay');
  if (drawer) drawer.classList.remove('open');
  if (overlay) overlay.classList.remove('open');
};

// ==========================================================================
// ABOUT PAGE HANDLERS (Progressive Disclosure & Gallery Interaction)
// ==========================================================================
window.toggleAboutStory = (index) => {
  const card = document.getElementById(`about-story-${index}`);
  if (!card) return;
  const wasOpen = card.classList.contains('open');
  card.classList.toggle('open', !wasOpen);
  const btnText = card.querySelector('.about-btn-text');
  if (btnText) {
    btnText.textContent = wasOpen ? 'Read More' : 'Show Less';
  }
};

window.switchAboutGallery = (imgUrl, caption, thumbIdx) => {
  const mainImg = document.getElementById('about-main-gallery-img');
  const captionEl = document.getElementById('about-gallery-caption');
  const thumbs = document.querySelectorAll('.about-thumb-btn');

  if (mainImg) {
    mainImg.style.opacity = '0.3';
    setTimeout(() => {
      mainImg.src = imgUrl;
      mainImg.alt = caption;
      mainImg.style.opacity = '1';
    }, 150);
  }

  if (captionEl) {
    captionEl.innerHTML = `
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
      <span>${caption}</span>
    `;
  }

  thumbs.forEach((btn, idx) => {
    btn.classList.toggle('active', idx === thumbIdx);
  });
};

// ==========================================================================
// FOOTER ACCORDION & MARKET PAGE HANDLERS
// ==========================================================================
window.toggleFooterAccordion = (colId) => {
  const col = document.getElementById(colId);
  if (!col) return;
  const isMobile = window.innerWidth <= 768;
  if (!isMobile) return; // Always open on desktop

  const header = col.querySelector('.footer-accordion-header');
  const wasOpen = col.classList.contains('footer-col--open');

  // Single open accordion behavior: close other columns
  const allCols = document.querySelectorAll('.frapak-footer .footer-col');
  allCols.forEach(otherCol => {
    if (otherCol !== col) {
      otherCol.classList.remove('footer-col--open');
      const otherHeader = otherCol.querySelector('.footer-accordion-header');
      if (otherHeader) otherHeader.setAttribute('aria-expanded', 'false');
    }
  });

  if (wasOpen) {
    col.classList.remove('footer-col--open');
    if (header) header.setAttribute('aria-expanded', 'false');
  } else {
    col.classList.add('footer-col--open');
    if (header) header.setAttribute('aria-expanded', 'true');
  }
};

window.scrollToMarketSector = (sectorId) => {
  const el = document.getElementById(sectorId);
  if (el) {
    const yOffset = -80;
    const y = el.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({ top: y, behavior: 'smooth' });
  }
};

window.toggleMarketIntro = () => {
  const card = document.getElementById('mkt-intro-card');
  if (!card) return;
  const isExpanded = card.classList.contains('expanded');
  card.classList.toggle('expanded', !isExpanded);
  const btn = document.getElementById('mkt-intro-readmore');
  if (btn) {
    const span = btn.querySelector('span');
    if (span) span.textContent = isExpanded ? 'Read more' : 'Show less';
  }
};

window.initCustomSolutionsCarousel = () => {
  const carousel = document.getElementById('cst-stage-carousel');
  if (!carousel) return;
  const dots = document.querySelectorAll('.cst-carousel-dots .cst-dot');
  if (!dots.length) return;

  carousel.addEventListener('scroll', () => {
    const firstCard = carousel.firstElementChild;
    const cardWidth = firstCard ? firstCard.offsetWidth + 12 : 300;
    const scrollPos = carousel.scrollLeft;
    const activeIndex = Math.min(dots.length - 1, Math.round(scrollPos / cardWidth));
    dots.forEach((dot, idx) => {
      dot.classList.toggle('active', idx === activeIndex);
    });
  }, { passive: true });
};

// ==========================================================================
// 6. FORMS SUBMISSION
// ==========================================================================
function initForms() {
  const form1 = document.getElementById('drawer-inquiry-form');
  if (form1) form1.addEventListener('submit', handleFormSubmit);

  const form2 = document.getElementById('contact-page-form');
  if (form2) form2.addEventListener('submit', handleFormSubmit);
}

async function handleFormSubmit(e) {
  e.preventDefault();
  const form = e.target;
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalBtnText = submitBtn ? submitBtn.innerText : 'SEND INQUIRY';

  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerText = 'SENDING INQUIRY...';
  }

  const formData = new FormData(form);
  const object = Object.fromEntries(formData);
  
  if (state.selectedProduct) {
    object.product = `${state.selectedProduct.name} (${state.selectedProduct.volume || state.selectedProduct.capacity}, Art: ${state.selectedProduct.articleNo || 'N/A'})`;
  }

  let quoteId = '';
  try {
    const res = await fetch('/quote.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(object)
    });
    const result = await res.json();
    if (result.success) {
      quoteId = result.quote_id;
    }
  } catch (err) {
    console.log('Using generated reference ID for offline environment');
    quoteId = 'TN-' + Math.floor(100000 + Math.random() * 900000);
  }

  const successBox = document.getElementById('inquiry-success-message');
  const refDisplay = document.getElementById('inquiry-reference-id');
  
  if (refDisplay && quoteId) {
    refDisplay.innerText = quoteId;
  }
  if (successBox) {
    successBox.style.display = 'block';
  }

  form.reset();
  if (submitBtn) {
    submitBtn.disabled = false;
    submitBtn.innerText = originalBtnText;
  }

  setTimeout(() => {
    window.closeInquiry();
    if (successBox) successBox.style.display = 'none';
  }, 4000);
}

// 7. AUTOCOMPLETE SEARCH BOX (Searches Categories, Series, & SKUs)
// ==========================================================================
function initSearch() {
  const searchPairs = [
    { inputId: 'top-search-input', popoverId: 'top-search-popover' },
    { inputId: 'mobile-search-input', popoverId: 'mobile-search-popover' }
  ];

  searchPairs.forEach(({ inputId, popoverId }) => {
    const input = document.getElementById(inputId);
    const popover = document.getElementById(popoverId);
    if (!input || !popover) return;

    input.addEventListener('input', (e) => {
      const query = e.target.value.trim().toLowerCase();
      if (!query) {
        popover.classList.remove('open');
        popover.innerHTML = '';
        return;
      }

      // Match categories
      const matchingCats = FRAPAK_CATEGORIES_12.filter(c => c.name.toLowerCase().includes(query)).slice(0, 3);
      // Match series
      const matchingSeries = SERIES_CATALOGUE.filter(s => s.name.toLowerCase().includes(query) || s.categoryName.toLowerCase().includes(query)).slice(0, 4);
      // Match SKUs
      const matchingSkus = SKUS_CATALOGUE.filter(s => 
        s.name.toLowerCase().includes(query) || 
        (s.articleNo && s.articleNo.toLowerCase().includes(query)) ||
        (s.fullTitle && s.fullTitle.toLowerCase().includes(query))
      ).slice(0, 5);

      if (matchingCats.length === 0 && matchingSeries.length === 0 && matchingSkus.length === 0) {
        popover.innerHTML = `<div style="padding: 14px; color: #888; font-size: 0.8125rem;">No matching packaging specifications found.</div>`;
        popover.classList.add('open');
        return;
      }

      let html = '';

      if (matchingCats.length > 0) {
        html += `<div style="padding: 6px 12px; background: #EEF2F6; font-size: 0.6875rem; font-weight: 700; color: var(--color-navy); text-transform: uppercase;">Categories</div>`;
        html += matchingCats.map(c => `
          <div style="padding: 8px 12px; display: flex; align-items: center; gap: 10px; cursor: pointer; border-bottom: 1px solid #f0f0f0;" onclick="window.selectCategory('${c.slug}'); document.getElementById('${popoverId}').classList.remove('open'); if (window.closeMobileMenu) window.closeMobileMenu();">
            <img src="${c.image}" style="width: 28px; height: 28px; object-fit: contain;" />
            <div style="font-size: 0.8125rem; font-weight: 600; color: var(--color-navy);">${c.name}</div>
          </div>
        `).join('');
      }

      if (matchingSeries.length > 0) {
        html += `<div style="padding: 6px 12px; background: #EEF2F6; font-size: 0.6875rem; font-weight: 700; color: var(--color-navy); text-transform: uppercase;">Series / Models</div>`;
        html += matchingSeries.map(s => `
          <div style="padding: 8px 12px; display: flex; align-items: center; gap: 10px; cursor: pointer; border-bottom: 1px solid #f0f0f0;" onclick="window.selectSeries('${s.categorySlug}', '${s.slug}'); document.getElementById('${popoverId}').classList.remove('open'); if (window.closeMobileMenu) window.closeMobileMenu();">
            <img src="${s.image}" style="width: 28px; height: 28px; object-fit: contain;" />
            <div style="font-size: 0.8125rem; font-weight: 600; color: var(--color-navy);">${s.name} <span style="font-size: 0.75rem; color: #888;">(${s.categoryName})</span></div>
          </div>
        `).join('');
      }

      if (matchingSkus.length > 0) {
        html += `<div style="padding: 6px 12px; background: #EEF2F6; font-size: 0.6875rem; font-weight: 700; color: var(--color-navy); text-transform: uppercase;">SKUs & Sizes</div>`;
        html += matchingSkus.map(sku => `
          <div style="padding: 8px 12px; display: flex; align-items: center; gap: 10px; cursor: pointer; border-bottom: 1px solid #f0f0f0;" onclick="window.selectSku('${sku.id}'); document.getElementById('${popoverId}').classList.remove('open'); if (window.closeMobileMenu) window.closeMobileMenu();">
            <img src="${sku.image}" style="width: 28px; height: 28px; object-fit: contain;" />
            <div>
              <div style="font-size: 0.8125rem; font-weight: 600; color: var(--color-navy);">${sku.name}</div>
              <div style="font-size: 0.6875rem; color: #666;">Art: ${sku.articleNo} • ${sku.volume} • ${sku.material}</div>
            </div>
          </div>
        `).join('');
      }

      popover.innerHTML = html;
      popover.classList.add('open');
    });

    document.addEventListener('click', (e) => {
      if (!input.contains(e.target) && !popover.contains(e.target)) {
        popover.classList.remove('open');
      }
    });
  });
}

window.executeMobileSearch = () => {
  const input = document.getElementById('mobile-search-input');
  if (input && input.value.trim()) {
    window.navigateToPage('products');
    window.closeMobileMenu();
  }
};

document.addEventListener('DOMContentLoaded', () => {
  if (window.initCustomSolutionsCarousel) {
    window.initCustomSolutionsCarousel();
  }

  // Fetch published products from PHP API & sync into live frontend catalogue
  fetch('/api/products')
    .then(res => res.json())
    .then(data => {
      const items = data.skus || data.results;
      if (data && data.success && Array.isArray(items)) {
        items.forEach(item => {
          const itemId = item.id || item.articleNo;
          const existingIdx = SKUS_CATALOGUE.findIndex(s => s.id === itemId || s.articleNo === item.articleNo);
          if (existingIdx !== -1) {
            SKUS_CATALOGUE[existingIdx] = Object.assign({}, SKUS_CATALOGUE[existingIdx], item);
          } else {
            SKUS_CATALOGUE.unshift(item);
          }
        });
        if (typeof renderProductsDrilldown === 'function') {
          renderProductsDrilldown();
        }
      }
    })
    .catch(() => {});
});

// ============================================================
// TERMS & PRIVACY MODALS
// ============================================================

window.openTermsModal = () => {
  const el = document.getElementById('terms-modal');
  if (el) el.classList.add('is-open');
  document.body.style.overflow = 'hidden';
};

window.closeTermsModal = () => {
  const el = document.getElementById('terms-modal');
  if (el) el.classList.remove('is-open');
  document.body.style.overflow = '';
};

window.openPrivacyModal = () => {
  const el = document.getElementById('privacy-modal');
  if (el) el.classList.add('is-open');
  document.body.style.overflow = 'hidden';
};

window.closePrivacyModal = () => {
  const el = document.getElementById('privacy-modal');
  if (el) el.classList.remove('is-open');
  document.body.style.overflow = '';
};

// Close modals on Escape key
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    window.closeTermsModal();
    window.closePrivacyModal();
  }
});
