  <!-- =========================================================================
       FOOTER (Clean 4-Column Responsive Architecture)
       ========================================================================= -->
  <footer class="tn-footer">
    <div class="tn-container">
      
      <!-- Footer Brand Header (Logo & Description above accordions) -->
      <div class="footer-brand-header">
        <a href="/" class="footer-brand-logo-link">
          <img src="/logo_svg.svg" alt="TrueNorth Group Logo" class="footer-brand-logo" />
        </a>
        <p class="footer-brand-desc">
          Global Primary Packaging Specialists & Precision Manufacturers
        </p>
      </div>

      <div class="tn-footer-grid">
        
        <!-- Section 1: Products -->
        <div class="footer-col" id="footer-col-products">
          <button type="button" class="footer-accordion-header" aria-expanded="false" aria-controls="footer-panel-products" id="footer-header-products" onclick="window.toggleFooterAccordion('footer-col-products')">
            <span class="footer-title">PRODUCTS</span>
            <span class="footer-accordion-icon" aria-hidden="true">
              <svg class="footer-icon-plus" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              <svg class="footer-icon-minus" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </span>
          </button>
          <div class="footer-accordion-panel" id="footer-panel-products" role="region" aria-labelledby="footer-header-products">
            <span style="display: block; font-size: 0.72rem; font-weight: 800; color: #b8892e; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 6px;">By Category:</span>
            <ul class="footer-link-list" style="margin-bottom: 12px;">
              <?php 
                $footerCategories = !empty($categories) ? $categories : (class_exists('ProductModel') ? ProductModel::getCategories() : []);
                foreach ($footerCategories as $cat): 
              ?>
                <li><a href="/products?category=<?= urlencode($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
              <?php endforeach; ?>
            </ul>
            <span style="display: block; font-size: 0.72rem; font-weight: 800; color: #b8892e; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 6px;">By Industry Market:</span>
            <ul class="footer-link-list">
              <li><a href="/products?industry=cosmetics">Cosmetics & Personal Care</a></li>
              <li><a href="/products?industry=pharma">Pharmaceuticals & Healthcare</a></li>
              <li><a href="/products?industry=agro">Agrochemicals & Pesticides</a></li>
              <li><a href="/products?industry=nutraceuticals">Nutraceuticals & Supplements</a></li>
              <li><a href="/products?industry=liquor">Liquor, Spirits & Beverages</a></li>
            </ul>
          </div>
        </div>

        <!-- Section 2: Company Details HQ -->
        <div class="footer-col" id="footer-col-company">
          <button type="button" class="footer-accordion-header" aria-expanded="false" aria-controls="footer-panel-company" id="footer-header-company" onclick="window.toggleFooterAccordion('footer-col-company')">
            <span class="footer-title">COMPANY DETAILS HQ</span>
            <span class="footer-accordion-icon" aria-hidden="true">
              <svg class="footer-icon-plus" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              <svg class="footer-icon-minus" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </span>
          </button>
          <div class="footer-accordion-panel" id="footer-panel-company" role="region" aria-labelledby="footer-header-company">
            <p class="footer-address-block">
              <strong>TrueNorth Group</strong><br />
              #201, Vardhaman Trade Towers, Rohit Kunj, LSC Pitampura, Delhi-110034<br />
              <span>Manufacturing: Daman, India <br/>Closures: Ningbo, China</span>
            </p>
            <div class="footer-contact-block">
              <a href="tel:+919810220761" class="footer-phone-link" style="color: var(--color-gold) !important; font-weight: 700;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--color-gold)" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                +91 98102 20761
              </a>
              <!--Sandeep Added-->
              
<div class="footer-contact-block">
  <a href="https://wa.me/919810220761"
     class="footer-whatsapp-link"
     target="_blank"
     rel="noopener noreferrer"
     style="color: var(--color-gold) !important; font-weight: 700;">

    <svg width="14" height="14"
         viewBox="0 0 24 24"
         fill="none"
         stroke="var(--color-gold)"
         stroke-width="2"
         stroke-linecap="round"
         stroke-linejoin="round"
         aria-hidden="true">
      <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8
               8.5 8.5 0 0 1-7.6 4.7
               8.38 8.38 0 0 1-3.8-.9
               L3 21l1.9-5.7
               a8.38 8.38 0 0 1-.9-3.8
               8.5 8.5 0 0 1 4.7-7.6
               8.38 8.38 0 0 1 3.8-.9h.5
               a8.48 8.48 0 0 1 8 8v.5z"/>
      <path d="M9 9a5 5 0 0 0 6 6"/>
    </svg>

    +91 98102 20761
  </a>
</div>

              
              <!--Sandeep Added End-->
              <a href="mailto:info@wetruenorthgroup.com" class="footer-email-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                info@wetruenorthgroup.com
              </a>
            </div>
          </div>
        </div>

        <!-- Section 3: Mission -->
        <div class="footer-col" id="footer-col-mission">
          <button type="button" class="footer-accordion-header" aria-expanded="false" aria-controls="footer-panel-mission" id="footer-header-mission" onclick="window.toggleFooterAccordion('footer-col-mission')">
            <span class="footer-title">MISSION</span>
            <span class="footer-accordion-icon" aria-hidden="true">
              <svg class="footer-icon-plus" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              <svg class="footer-icon-minus" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </span>
          </button>
          <div class="footer-accordion-panel" id="footer-panel-mission" role="region" aria-labelledby="footer-header-mission">
            <p class="footer-mission-text">
              TrueNorth Group is your premier partner and manufacturer of high-grade primary packaging. As an integrated packaging specialist, we seamlessly combine precision manufacturing and global supply chain expertise to deliver tailored bottles, jars, and closures.
            </p>
          </div>
        </div>

        <!-- Section 4: Extra Information -->
        <div class="footer-col" id="footer-col-extra">
          <button type="button" class="footer-accordion-header" aria-expanded="false" aria-controls="footer-panel-extra" id="footer-header-extra" onclick="window.toggleFooterAccordion('footer-col-extra')">
            <span class="footer-title">EXTRA INFORMATION</span>
            <span class="footer-accordion-icon" aria-hidden="true">
              <svg class="footer-icon-plus" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              <svg class="footer-icon-minus" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </span>
          </button>
          <div class="footer-accordion-panel" id="footer-panel-extra" role="region" aria-labelledby="footer-header-extra">
            <ul class="footer-link-list">
              <li><a href="/custom">Custom Mould Development</a></li>
              <li><a href="/sustainability">PCR Plastics &amp; rPET Stock</a></li>
            </ul>
          </div>
        </div>

      </div>

      <div class="footer-copyright-bar">
        <span>© 2000 - 2026 TrueNorth Group. Over 25 Years of Industry Excellence. All Rights Reserved.</span>
        <span class="footer-legal-links">
          <a href="/terms">General Terms &amp; Conditions</a>
          <span class="footer-legal-sep">|</span>
          <a href="/privacy">Privacy Statement</a>
        </span>
      </div>
    </div>
  </footer>

  <script>
    // Mobile Drawer Navigation (Explicit window attachment for bulletproof mobile support)
    window.toggleMobileMenu = function() {
      const drawer = document.getElementById('mobile-nav-drawer');
      const backdrop = document.getElementById('mobile-nav-backdrop');
      const btn = document.getElementById('tn-mobile-menu-btn');
      if (drawer && backdrop) {
        const isOpen = drawer.classList.contains('open') || drawer.classList.contains('active');
        if (isOpen) {
          window.closeMobileMenu();
        } else {
          drawer.classList.add('open', 'active');
          backdrop.classList.add('open', 'active');
          if (btn) {
            btn.classList.add('active');
            btn.setAttribute('aria-expanded', 'true');
          }
          document.body.style.overflow = 'hidden';
        }
      }
    };

    window.closeMobileMenu = function() {
      const drawer = document.getElementById('mobile-nav-drawer');
      const backdrop = document.getElementById('mobile-nav-backdrop');
      const btn = document.getElementById('tn-mobile-menu-btn');
      const mobilePopover = document.getElementById('mobile-search-popover');
      if (drawer) drawer.classList.remove('open', 'active');
      if (backdrop) backdrop.classList.remove('open', 'active');
      if (mobilePopover) {
        mobilePopover.classList.remove('open', 'active');
        mobilePopover.style.display = 'none';
      }
      if (btn) {
        btn.classList.remove('active');
        btn.setAttribute('aria-expanded', 'false');
      }
      document.body.style.overflow = '';
    };

    // Auto-close drawer on Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        window.closeMobileMenu();
      }
    });

    // Footer Accordions on Mobile (Bulletproof toggle supporting all mobile devices)
    window.toggleFooterAccordion = function(colId) {
      const col = typeof colId === 'string' ? document.getElementById(colId) : colId;
      if (!col) return;
      const headerBtn = col.querySelector('.footer-accordion-header');
      const panel = col.querySelector('.footer-accordion-panel');
      const isCurrentlyOpen = col.classList.contains('footer-col--open');
      
      if (isCurrentlyOpen) {
        col.classList.remove('footer-col--open');
        if (headerBtn) headerBtn.setAttribute('aria-expanded', 'false');
        if (panel) {
          panel.style.maxHeight = '';
        }
      } else {
        col.classList.add('footer-col--open');
        if (headerBtn) headerBtn.setAttribute('aria-expanded', 'true');
        if (panel) {
          panel.style.maxHeight = Math.max(panel.scrollHeight + 40, 800) + 'px';
        }
      }
    };
    function toggleFooterAccordion(colId) {
      return window.toggleFooterAccordion(colId);
    }

    // Modal & Inquiry Controls
    window.openInquiry = function(productName) {
      const drawer = document.getElementById('inquiry-drawer');
      const overlay = document.getElementById('inquiry-drawer-overlay');
      if (drawer) {
        drawer.classList.add('open');
        drawer.style.transform = 'translateX(0)';
      }
      if (overlay) {
        overlay.classList.add('open');
        overlay.style.display = 'block';
      }
      document.body.style.overflow = 'hidden';

      const badge = document.getElementById('inquiry-product-badge');
      const hiddenProduct = document.getElementById('inquiry-hidden-product');
      const notesField = document.getElementById('inquiry-notes-field');

      if (productName && typeof productName === 'string' && productName.trim() !== '') {
        const cleanName = productName.trim();
        if (badge) {
          badge.style.display = 'block';
          badge.innerHTML = '<span style="background: rgba(184,137,46,0.15); border: 1px solid #b8892e; color: #b8892e; padding: 6px 12px; border-radius: 4px; font-weight: 700; font-size: 0.85rem; display: inline-block; margin-bottom: 14px;">Product: ' + cleanName + '</span>';
        }
        if (hiddenProduct) {
          hiddenProduct.value = cleanName;
        }
        if (notesField && (!notesField.value || notesField.value.trim() === '')) {
          notesField.value = 'Inquiry regarding: ' + cleanName;
        }
      } else {
        if (badge) badge.style.display = 'none';
        if (hiddenProduct) hiddenProduct.value = '';
      }

      if (typeof window.initCountrySelects === 'function') {
        window.initCountrySelects();
      }
    };

    window.closeInquiry = function() {
      const drawer = document.getElementById('inquiry-drawer');
      const overlay = document.getElementById('inquiry-drawer-overlay');
      if (drawer) {
        drawer.classList.remove('open');
        drawer.style.transform = '';
      }
      if (overlay) {
        overlay.classList.remove('open');
        overlay.style.display = 'none';
      }
      document.body.style.overflow = '';
    };

    window.openTermsModal = function() {
      const modal = document.getElementById('terms-modal');
      if (modal) {
        modal.classList.add('open');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
      }
    };

    window.closeTermsModal = function() {
      const modal = document.getElementById('terms-modal');
      if (modal) {
        modal.classList.remove('open');
        modal.style.display = 'none';
        document.body.style.overflow = '';
      }
    };

    window.openPrivacyModal = function() {
      const modal = document.getElementById('privacy-modal');
      if (modal) {
        modal.classList.add('open');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
      }
    };

    window.closePrivacyModal = function() {
      const modal = document.getElementById('privacy-modal');
      if (modal) {
        modal.classList.remove('open');
        modal.style.display = 'none';
        document.body.style.overflow = '';
      }
    };

    window.closeModal = function(modalId) {
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.classList.remove('open');
        modal.style.display = 'none';
        document.body.style.overflow = '';
      }
    };

    // Close on Escape Key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' || e.keyCode === 27) {
        window.closeInquiry();
        window.closeTermsModal();
        window.closePrivacyModal();
        window.closeModal('success-modal');
      }
    });

    // Populate country dropdowns everywhere
    const countryList = [
      "United States",
      "United Kingdom",
      "Germany",
      "United Arab Emirates",
      "Saudi Arabia",
      "France",
      "Netherlands",
      "Australia",
      "Canada",
      "Singapore",
      "India",
      "Afghanistan",
      "Albania",
      "Algeria",
      "Andorra",
      "Angola",
      "Antigua and Barbuda",
      "Argentina",
      "Armenia",
      "Austria",
      "Azerbaijan",
      "Bahamas",
      "Bahrain",
      "Bangladesh",
      "Barbados",
      "Belarus",
      "Belgium",
      "Belize",
      "Benin",
      "Bhutan",
      "Bolivia",
      "Bosnia and Herzegovina",
      "Botswana",
      "Brazil",
      "Brunei",
      "Bulgaria",
      "Burkina Faso",
      "Burundi",
      "Cabo Verde",
      "Cambodia",
      "Cameroon",
      "Central African Republic",
      "Chad",
      "Chile",
      "China",
      "Colombia",
      "Comoros",
      "Congo",
      "Costa Rica",
      "Croatia",
      "Cuba",
      "Cyprus",
      "Czech Republic",
      "Denmark",
      "Djibouti",
      "Dominica",
      "Dominican Republic",
      "Ecuador",
      "Egypt",
      "El Salvador",
      "Equatorial Guinea",
      "Eritrea",
      "Estonia",
      "Eswatini",
      "Ethiopia",
      "Fiji",
      "Finland",
      "Gabon",
      "Gambia",
      "Georgia",
      "Ghana",
      "Greece",
      "Grenada",
      "Guatemala",
      "Guinea",
      "Guinea-Bissau",
      "Guyana",
      "Haiti",
      "Honduras",
      "Hungary",
      "Iceland",
      "Indonesia",
      "Iran",
      "Iraq",
      "Ireland",
      "Israel",
      "Italy",
      "Ivory Coast",
      "Jamaica",
      "Japan",
      "Jordan",
      "Kazakhstan",
      "Kenya",
      "Kiribati",
      "Kuwait",
      "Kyrgyzstan",
      "Laos",
      "Latvia",
      "Lebanon",
      "Lesotho",
      "Liberia",
      "Libya",
      "Liechtenstein",
      "Lithuania",
      "Luxembourg",
      "Madagascar",
      "Malawi",
      "Malaysia",
      "Maldives",
      "Mali",
      "Malta",
      "Marshall Islands",
      "Mauritania",
      "Mauritius",
      "Mexico",
      "Micronesia",
      "Moldova",
      "Monaco",
      "Mongolia",
      "Montenegro",
      "Morocco",
      "Mozambique",
      "Myanmar",
      "Namibia",
      "Nauru",
      "Nepal",
      "New Zealand",
      "Nicaragua",
      "Niger",
      "Nigeria",
      "North Korea",
      "North Macedonia",
      "Norway",
      "Oman",
      "Pakistan",
      "Palau",
      "Palestine",
      "Panama",
      "Papua New Guinea",
      "Paraguay",
      "Peru",
      "Philippines",
      "Poland",
      "Portugal",
      "Qatar",
      "Romania",
      "Russia",
      "Rwanda",
      "Saint Kitts and Nevis",
      "Saint Lucia",
      "Saint Vincent and the Grenadines",
      "Samoa",
      "San Marino",
      "Sao Tome and Principe",
      "Senegal",
      "Serbia",
      "Seychelles",
      "Sierra Leone",
      "Slovakia",
      "Slovenia",
      "Solomon Islands",
      "Somalia",
      "South Africa",
      "South Korea",
      "South Sudan",
      "Spain",
      "Sri Lanka",
      "Sudan",
      "Suriname",
      "Sweden",
      "Switzerland",
      "Syria",
      "Taiwan",
      "Tajikistan",
      "Tanzania",
      "Thailand",
      "Timor-Leste",
      "Togo",
      "Tonga",
      "Trinidad and Tobago",
      "Tunisia",
      "Turkey",
      "Turkmenistan",
      "Tuvalu",
      "Uganda",
      "Ukraine",
      "Uruguay",
      "Uzbekistan",
      "Vanuatu",
      "Vatican City",
      "Venezuela",
      "Vietnam",
      "Yemen",
      "Zambia",
      "Zimbabwe",
      "Other Country"
];

    window.initCountrySelects = function() {
      document.querySelectorAll('select.country-select, select[name="country"]').forEach(function(sel) {
        if (sel.options.length <= 1) {
          sel.innerHTML = '<option value="" disabled selected>Select your country...</option>';
          countryList.forEach(function(c) {
            const opt = document.createElement('option');
            opt.value = c;
            opt.textContent = c;
            sel.appendChild(opt);
          });
        }
      });
    };

    // Helper for AJAX inquiry submission
    function bindInquirySubmit(formElement, isDrawer) {
      if (!formElement || formElement.dataset.bound === 'true') return;
      formElement.dataset.bound = 'true';

      formElement.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = formElement.querySelector('button[type="submit"]');
        const origText = btn ? btn.innerHTML : 'SEND INQUIRY';
        if (btn) {
          btn.disabled = true;
          btn.innerHTML = 'Sending...';
        }

        const fd = new FormData(formElement);
        const payload = {};
        fd.forEach(function(v, k) { payload[k] = v; });

        fetch('/api/quote', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        })
        .then(function(r) { 
          if (!r.ok) {
            return r.json().then(function(errData) {
              throw new Error(errData.message || ('Server error: ' + r.status));
            }).catch(function() {
              throw new Error('Server returned error status ' + r.status);
            });
          }
          return r.json(); 
        })
        .then(function(res) {
          if (btn) {
            btn.disabled = false;
            btn.innerHTML = origText;
          }
          if (!res || !res.success) {
            alert(res && res.message ? res.message : 'We could not submit your inquiry at this moment. Please try again or email info@wetruenorthgroup.com.');
            return;
          }
          if (isDrawer) {
            window.closeInquiry();
          }

          const successModal = document.getElementById('success-modal');
          const successContent = document.getElementById('success-modal-content');
          if (successModal && successContent) {
            successContent.innerHTML = 
              '<div style="text-align: center; padding: 20px 10px;">' +
                '<div style="width: 54px; height: 54px; background: rgba(34, 197, 94, 0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; color: #16a34a; font-size: 26px; font-weight: bold;">✓</div>' +
                '<h3 style="font-size: 1.25rem; font-weight: 800; color: #16273f; margin-bottom: 8px;">Inquiry Received!</h3>' +
                '<p style="color: #64748b; font-size: 0.95rem; margin-bottom: 16px; line-height: 1.5;">' + (res.message || 'Thank you! Our packaging specialists will contact you within 24 hours.') + '</p>' +
                '<p style="font-size: 0.82rem; color: #94a3b8; margin-bottom: 22px;">Reference: <strong style="color: #b8892e;">' + (res.quoteId || ('INQ-' + Date.now().toString().slice(-6))) + '</strong></p>' +
                '<button onclick="window.closeModal(\'success-modal\')" class="tn-btn-gold" style="width: 100%; margin: 0; padding: 12px; font-weight: 700; border-radius: 6px;">CONTINUE BROWSING</button>' +
              '</div>';
            successModal.classList.add('open');
            successModal.style.display = 'flex';
          } else {
            alert(res.message || 'Thank you! Your inquiry has been submitted.');
          }
          formElement.reset();
        })
        .catch(function(err) {
          if (btn) {
            btn.disabled = false;
            btn.innerHTML = origText;
          }
          // Do not reset form on error so user does not lose their typed message
          alert('Submission error: ' + (err.message || 'Unable to connect to server') + '. Please try again or email info@wetruenorthgroup.com directly.');
        });
      });
    }

    function initInquiryForms() {
      bindInquirySubmit(document.getElementById('drawer-inquiry-form'), true);
      bindInquirySubmit(document.getElementById('contact-page-form'), false);
    }

    // Global navigation fallbacks
    window.navigateToPage = function(page) {
      if (!page || page === 'home') {
        window.location.href = '/';
      } else {
        window.location.href = '/' + page;
      }
    };

    window.executeMobileSearch = function() {
      const input = document.getElementById('mobile-search-input');
      const q = input ? input.value.trim() : '';
      if (q) {
        window.location.href = '/products?q=' + encodeURIComponent(q);
      }
    };

    // Live Search Autocomplete Engine with Generic Packaging Searches
    function initLiveSearch() {
      const popularSearches = [
        { label: 'Caps & Closures', q: 'caps' },
        { label: 'Bottles (All)', q: 'bottles' },
        { label: 'Jars', q: 'jars' },
        { label: 'Lotion Pumps', q: 'lotion pump' },
        { label: 'Foam Pumps', q: 'foam pump' },
        { label: 'Trigger Sprayers', q: 'trigger sprayer' },
        { label: 'Mist Sprayers', q: 'mist sprayer' },
        { label: 'Agro Bottles', q: 'agro' },
        { label: 'Cosmetic Bottles', q: 'cosmetics' },
        { label: 'Pharma Bottles', q: 'pharma' },
        { label: 'HDPE', q: 'hdpe' },
        { label: 'PET', q: 'pet' },
        { label: '500ml', q: '500ml' }
      ];

      function renderPopularPanel(popoverEl, inputEl) {
        let html = '<div class="search-popular-panel" style="padding: 14px; background: #fff; box-sizing: border-box; width: 100%;">';
        html += '<div style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">';
        html += '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#b8892e" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
        html += 'Popular Generic Searches';
        html += '</div>';
        html += '<div style="display: flex; flex-wrap: wrap; gap: 6px; box-sizing: border-box; width: 100%;">';
        popularSearches.forEach(function(item) {
          html += '<button type="button" class="generic-search-tag-chip" data-search-q="' + item.q + '" style="background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 20px; padding: 5px 10px; font-size: 0.78rem; font-weight: 600; color: #1e293b; cursor: pointer; transition: all 0.15s ease; white-space: normal; text-align: center; max-width: 100%; box-sizing: border-box; line-height: 1.25;" onmouseover="this.style.background=\'#e0f2fe\'; this.style.borderColor=\'#38bdf8\'; this.style.color=\'#0369a1\';" onmouseout="this.style.background=\'#f1f5f9\'; this.style.borderColor=\'#e2e8f0\'; this.style.color=\'#1e293b\';">' + item.label + '</button>';
        });
        html += '</div>';
        html += '</div>';

        popoverEl.innerHTML = html;
        popoverEl.classList.add('open', 'active');
        popoverEl.style.display = 'block';

        // Bind clicks on tag chips
        popoverEl.querySelectorAll('.generic-search-tag-chip').forEach(function(chip) {
          chip.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const q = this.getAttribute('data-search-q');
            if (q) {
              inputEl.value = q;
              window.location.href = '/products?q=' + encodeURIComponent(q);
            }
          });
        });
      }

      function bindSearch(inputEl, popoverEl) {
        if (!inputEl || !popoverEl) return;
        let debounceTimer = null;

        // On Focus: show popular packaging tags if empty
        inputEl.addEventListener('focus', function() {
          const val = inputEl.value.trim();
          if (val.length < 2) {
            renderPopularPanel(popoverEl, inputEl);
          }
        });

        inputEl.addEventListener('input', function() {
          const val = inputEl.value.trim();
          clearTimeout(debounceTimer);

          if (val.length < 2) {
            renderPopularPanel(popoverEl, inputEl);
            return;
          }

          debounceTimer = setTimeout(function() {
            fetch('/api/search?q=' + encodeURIComponent(val))
              .then(function(r) { return r.json(); })
              .then(function(data) {
                if (!data || !data.results || data.results.length === 0) {
                  popoverEl.innerHTML = '<div style="padding: 18px 14px; text-align: center; color: #64748b; font-size: 0.85rem;">No packaging found for "<strong>' + escapeSearchHtml(val) + '</strong>"</div>';
                  popoverEl.classList.add('open', 'active');
                  popoverEl.style.display = 'block';
                  return;
                }

                let html = '<div style="padding: 0; max-height: 380px; overflow-y: auto;">';

                // Generic Taxonomy Match Header Banner
                if (data.taxonomyMatch && data.taxonomyMatch.name) {
                  html += 
                    '<div style="background: linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 100%); border-bottom: 1px solid #cbd5e1; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between;">' +
                      '<div>' +
                        '<span style="display: inline-block; background: #0284c7; color: #fff; font-size: 0.65rem; font-weight: 800; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; margin-bottom: 2px;">Generic Category Match</span>' +
                        '<div style="font-weight: 800; font-size: 0.86rem; color: #0f172a;">' + escapeSearchHtml(data.taxonomyMatch.name) + ' (' + data.count + ' Products)</div>' +
                      '</div>' +
                      '<a href="/products?q=' + encodeURIComponent(val) + '" style="font-size: 0.78rem; font-weight: 700; color: #b8892e; text-decoration: none; padding: 4px 8px; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px;">View All &rarr;</a>' +
                    '</div>';
                }

                const items = data.results.slice(0, 6);
                items.forEach(function(item) {
                  const img = item.image || '/logo_svg.svg';
                  const title = item.name || 'Product';
                  const meta = item.series || item.serie || item.categoryName || '';
                  const neck = (item.specs && item.specs.neck) ? item.specs.neck : '';
                  const cap = (item.specs && item.specs.capacity && item.specs.capacity !== 'N/A') ? item.specs.capacity : '';
                  const specsStr = [cap, neck].filter(Boolean).join(' • ');

                  html += 
                    '<a href="/product?id=' + encodeURIComponent(item.id) + '" style="display: flex; align-items: center; gap: 12px; padding: 10px 14px; text-decoration: none; border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background=\'#f8fafc\'" onmouseout="this.style.background=\'transparent\'">' +
                      '<img src="' + img + '" alt="' + escapeSearchHtml(title) + '" style="width: 40px; height: 40px; object-fit: contain; border-radius: 4px; border: 1px solid #e2e8f0; background: #fff; padding: 2px; flex-shrink: 0;" />' +
                      '<div style="min-width: 0; flex: 1;">' +
                        '<div style="font-size: 0.84rem; font-weight: 700; color: #16273f; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">' + escapeSearchHtml(title) + '</div>' +
                        '<div style="font-size: 0.74rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">' + escapeSearchHtml(meta) + (specsStr ? ' &bull; <span style="color:#0284c7; font-weight:600;">' + escapeSearchHtml(specsStr) + '</span>' : '') + '</div>' +
                      '</div>' +
                    '</a>';
                });

                html += 
                  '<a href="/products?q=' + encodeURIComponent(val) + '" style="display: block; text-align: center; padding: 11px; font-size: 0.83rem; font-weight: 700; color: #b8892e; background: #f8fafc; text-decoration: none; border-top: 1px solid #e2e8f0;">' +
                    'View all ' + data.count + ' results for "' + escapeSearchHtml(val) + '" &rarr;' +
                  '</a>';
                html += '</div>';

                popoverEl.innerHTML = html;
                popoverEl.classList.add('open', 'active');
                popoverEl.style.display = 'block';
              })
              .catch(function(err) {
                console.warn('Search autocomplete error:', err);
              });
          }, 180);
        });

        // Close on click outside
        document.addEventListener('click', function(e) {
          if (!inputEl.contains(e.target) && !popoverEl.contains(e.target)) {
            popoverEl.classList.remove('open', 'active');
            popoverEl.style.display = 'none';
          }
        });
      }

      function escapeSearchHtml(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
      }

      bindSearch(document.getElementById('top-search-input'), document.getElementById('top-search-popover'));
      bindSearch(document.getElementById('mobile-search-input'), document.getElementById('mobile-search-popover'));
    }

    // About Page Accordion Story Toggle
    window.toggleAboutStory = function(idx) {
      const card = document.getElementById('about-story-' + idx);
      if (!card) return;
      const isOpen = card.classList.contains('open');
      if (isOpen) {
        card.classList.remove('open');
      } else {
        card.classList.add('open');
      }
      const btnText = card.querySelector('.about-btn-text');
      if (btnText) {
        btnText.textContent = card.classList.contains('open') ? 'Read Less' : 'Read More';
      }
    };

    // Home Page PCR Content Toggle
    window.togglePcrContent = function(btn) {
      const content = document.getElementById('pcr-expandable-content');
      if (!content) return;
      const isOpen = content.classList.contains('open');
      content.classList.toggle('open');
      if (btn) {
        const span = btn.querySelector('span');
        if (span) span.textContent = content.classList.contains('open') ? 'Read Less' : 'Read More';
        const svg = btn.querySelector('svg');
        if (svg) svg.style.transform = content.classList.contains('open') ? 'rotate(180deg)' : 'rotate(0deg)';
      }
    };

    // Initialize on page ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function() {
        window.initCountrySelects();
        initInquiryForms();
        initLiveSearch();
      });
    } else {
      window.initCountrySelects();
      initInquiryForms();
      initLiveSearch();
    }
  </script>
</body>
</html>
