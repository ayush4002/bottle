// ==========================================================================
// TRUENORTH GROUP — UI INTERACTION HANDLERS (PHP FRONTEND)
// Lightweight client-side DOM handlers for PHP rendered views
// ==========================================================================

document.addEventListener('DOMContentLoaded', () => {
  // Mobile Navigation Menu Toggles
  window.toggleMobileMenu = function() {
    const drawer = document.getElementById('mobile-nav-drawer');
    const backdrop = document.getElementById('mobile-nav-backdrop');
    const btn = document.getElementById('frapak-mobile-menu-btn');
    if (drawer && backdrop) {
      drawer.classList.toggle('active');
      backdrop.classList.toggle('active');
      const expanded = drawer.classList.contains('active');
      if (btn) btn.setAttribute('aria-expanded', expanded);
    }
  };

  window.closeMobileMenu = function() {
    const drawer = document.getElementById('mobile-nav-drawer');
    const backdrop = document.getElementById('mobile-nav-backdrop');
    const btn = document.getElementById('frapak-mobile-menu-btn');
    if (drawer) drawer.classList.remove('active');
    if (backdrop) backdrop.classList.remove('active');
    if (btn) btn.setAttribute('aria-expanded', 'false');
  };

  // Footer Accordion Toggles for Mobile
  window.toggleFooterAccordion = function(colId) {
    const col = document.getElementById(colId);
    if (!col) return;
    const headerBtn = col.querySelector('.footer-accordion-header');
    const panel = col.querySelector('.footer-accordion-panel');
    const isExpanded = headerBtn ? headerBtn.getAttribute('aria-expanded') === 'true' : false;

    if (headerBtn) headerBtn.setAttribute('aria-expanded', !isExpanded);
    if (panel) {
      if (isExpanded) {
        panel.style.maxHeight = null;
      } else {
        panel.style.maxHeight = panel.scrollHeight + 'px';
      }
    }
  };

  // Inquiry Modal Controls
  window.openInquiry = function(productName = '') {
    const modal = document.getElementById('inquiryModal');
    if (modal) {
      modal.style.display = 'flex';
      if (productName) {
        const input = modal.querySelector('input[name="product_name"]');
        if (input) input.value = productName;
      }
    }
  };

  window.closeInquiry = function() {
    const modal = document.getElementById('inquiryModal');
    if (modal) modal.style.display = 'none';
  };

  // Legal Modals
  window.openTermsModal = function() {
    const modal = document.getElementById('termsModal');
    if (modal) modal.style.display = 'flex';
  };

  window.openPrivacyModal = function() {
    const modal = document.getElementById('privacyModal');
    if (modal) modal.style.display = 'flex';
  };

  window.closeModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'none';
  };

  // Page Navigation / Routing Helper
  window.navigateToPage = function(page) {
    if (page === 'home') {
      window.location.href = '/';
    } else {
      window.location.href = '/' + page;
    }
  };

  // Category Filtering Helper
  window.goToCategory = function(category) {
    window.location.href = '/products?category=' + encodeURIComponent(category);
  };
});
