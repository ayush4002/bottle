// Catalogue smart faceted filter engine (moved out of products.php so browsers can cache it)
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
