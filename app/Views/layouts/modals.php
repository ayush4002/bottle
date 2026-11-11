  <!-- =========================================================================
       INQUIRY DRAWER & MODAL STYLES (Self-contained for immediate live rendering)
       ========================================================================= -->
  <style>
    .tn-drawer-overlay {
      position: fixed;
      inset: 0;
      background: rgba(15, 27, 44, 0.65);
      z-index: 9998;
      display: none;
      backdrop-filter: blur(3px);
      transition: opacity 0.25s ease;
    }
    .tn-drawer-overlay.open {
      display: block !important;
    }
    .tn-drawer {
      position: fixed;
      top: 0;
      right: 0;
      bottom: 0;
      width: 480px;
      max-width: 95vw;
      background: #ffffff;
      z-index: 9999;
      display: flex;
      flex-direction: column;
      transform: translateX(100%);
      transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      box-shadow: -6px 0 30px rgba(0, 0, 0, 0.2);
      border-top-left-radius: 12px;
      border-bottom-left-radius: 12px;
    }
    .tn-drawer.open {
      transform: translateX(0) !important;
    }
    .tn-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(15, 27, 44, 0.65);
      z-index: 10000;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
      backdrop-filter: blur(4px);
    }
    .tn-modal-overlay.open {
      display: flex !important;
    }
    .tn-modal-box {
      background: #ffffff;
      border-radius: 12px;
      width: 100%;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
      animation: truenorthModalIn 0.25s ease-out;
    }
    @keyframes truenorthModalIn {
      from { opacity: 0; transform: translateY(16px) scale(0.98); }
      to { opacity: 1; transform: translateY(0) scale(1); }
    }
  </style>

  <!-- =========================================================================
       INQUIRY DRAWER
       ========================================================================= -->
  <div class="tn-drawer-overlay" id="inquiry-drawer-overlay" onclick="window.closeInquiry()"></div>
  <aside class="tn-drawer" id="inquiry-drawer" onclick="event.stopPropagation()">
    <div class="drawer-header" style="background: var(--color-navy); color: #fff; padding: 18px 22px; display: flex; justify-content: space-between; align-items: center;">
      <div>
        <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0; color: #fff;">Wholesale Packaging Inquiry</h3>
        <p style="font-size: 0.8rem; color: #b8892e; margin: 3px 0 0; font-weight: 600;">Direct Factory RFQ &amp; Sample Request</p>
      </div>
      <button onclick="window.closeInquiry()" style="color: #fff; border: none; background: rgba(255,255,255,0.1); border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; padding: 0;" aria-label="Close">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <div class="drawer-body" style="padding: 22px; overflow-y: auto; flex: 1;">
      <div id="inquiry-product-badge" style="display: none;"></div>

      <form id="drawer-inquiry-form">
        <input type="hidden" name="product" id="inquiry-hidden-product" value="" />
        <div class="tn-form-field">
          <label>1. Name *</label>
          <input type="text" name="name" required placeholder="Enter your full name" autocomplete="name" />
        </div>
        <div class="tn-form-field">
          <label>2. Company Name *</label>
          <input type="text" name="company" required placeholder="Enter company name" autocomplete="organization" />
        </div>
        <div class="tn-form-field">
          <label>3. Work Email *</label>
          <input type="email" name="email" required placeholder="Enter work email address" autocomplete="email" />
        </div>
        <div class="tn-form-field">
          <label>4. Country *</label>
          <select name="country" class="country-select" required>
            <option value="" disabled selected>Select your country...</option>
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
            <option value="Austria">Austria</option>
            <option value="Denmark">Denmark</option>
            <option value="Norway">Norway</option>
            <option value="Ireland">Ireland</option>
            <option value="New Zealand">New Zealand</option>
            <option value="Other Country">Other Country</option>
          </select>
        </div>
        <div class="tn-form-field">
          <label>5. Phone Number *</label>
          <input type="tel" name="phone" required placeholder="Country code + phone number" autocomplete="tel" />
        </div>
        <div class="tn-form-field">
          <label>6. Inquiry Details *</label>
          <textarea id="inquiry-notes-field" name="inquiry" required placeholder="Tell us about your required volumes, neck sizes, caps, or target delivery timeline" style="min-height: 90px;"></textarea>
        </div>

        <button type="submit" class="tn-btn-gold" style="margin-left: 0; width: 100%; margin-top: 10px; padding: 13px; font-weight: 700; font-size: 0.95rem; border-radius: 6px;">
          SEND INQUIRY
        </button>
      </form>
    </div>
  </aside>

  <!-- =========================================================================
       CONFIRMATION MODAL
       ========================================================================= -->
  <div class="tn-modal-overlay" id="success-modal" onclick="window.closeModal('success-modal')">
    <div class="tn-modal-box" style="max-width: 460px; padding: 24px;" onclick="event.stopPropagation()">
      <div id="success-modal-content"></div>
    </div>
  </div>

  <!-- =========================================================================
       TERMS & CONDITIONS MODAL
       ========================================================================= -->
  <div class="tn-modal-overlay" id="terms-modal" onclick="window.closeTermsModal()">
    <div class="tn-modal-box" style="max-width: 640px; padding: 28px;" onclick="event.stopPropagation()">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px;">
        <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--color-navy); margin: 0;">General Terms &amp; Conditions</h3>
        <button onclick="window.closeTermsModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-navy); line-height: 1;">&times;</button>
      </div>
      <div style="font-size: 0.84rem; color: #4B5563; line-height: 1.6; max-height: 60vh; overflow-y: auto; padding-right: 8px;">
        <h4 style="font-size: 0.95rem; color: var(--color-navy); margin: 0 0 6px;">1. Quality Assurance &amp; Manufacturing Standards</h4>
        <p style="margin-bottom: 12px;">All PET bottles, jars, closures, and pumps produced by TrueNorth Group comply strictly with TÜV NORD ISO 9001 quality management protocols and food-grade safety standards. Production is conducted under cleanroom protocols for medical, cosmetic, and food packaging applications.</p>
        <h4 style="font-size: 0.95rem; color: var(--color-navy); margin: 0 0 6px;">2. Minimum Order Quantities (MOQ) &amp; Tooling</h4>
        <p style="margin-bottom: 12px;">Standard catalogue items feature accessible MOQs starting from 5,000 units per batch. Custom tooling and 3D prototype sampling terms are agreed upon project specifications prior to mould fabrication.</p>
        <h4 style="font-size: 0.95rem; color: var(--color-navy); margin: 0 0 6px;">3. Leak-Free Guarantee &amp; Inspection</h4>
        <p style="margin-bottom: 12px;">Every production batch undergoes 1,000+ cycle vacuum chamber leak tests and neck-finish torque calibration. Technical samples dispatched for customer testing must be verified before mass production sign-off.</p>
        <h4 style="font-size: 0.95rem; color: var(--color-navy); margin: 0 0 6px;">4. Export Shipping &amp; Delivery Terms</h4>
        <p style="margin-bottom: 12px;">Shipments are packaged in export-grade corrugated cartons, stretch-wrapped pallets, and assigned full batch traceability for maximum transit protection and export durability across domestic and global logistics destinations.</p>
        <p style="margin-bottom: 0; font-size: 0.82rem; font-weight: 700;"><a href="/terms" style="color: var(--color-navy); text-decoration: underline;">Read Full Terms &amp; Conditions of Sale &rarr;</a></p>
      </div>
      <button onclick="window.closeTermsModal()" class="tn-btn-gold" style="width: 100%; margin-top: 20px; text-align: center; margin-left: 0;">CLOSE TERMS</button>
    </div>
  </div>

  <!-- =========================================================================
       PRIVACY STATEMENT MODAL
       ========================================================================= -->
  <div class="tn-modal-overlay" id="privacy-modal" onclick="window.closePrivacyModal()">
    <div class="tn-modal-box" style="max-width: 640px; padding: 28px;" onclick="event.stopPropagation()">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px;">
        <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--color-navy); margin: 0;">Privacy Statement</h3>
        <button onclick="window.closePrivacyModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-navy); line-height: 1;">&times;</button>
      </div>
      <div style="font-size: 0.84rem; color: #4B5563; line-height: 1.6; max-height: 60vh; overflow-y: auto; padding-right: 8px;">
        <h4 style="font-size: 0.95rem; color: var(--color-navy); margin: 0 0 6px;">1. Client Data Protection</h4>
        <p style="margin-bottom: 12px;">TrueNorth Group values your business privacy. All technical drawings, CAD files, sample requests, and procurement inquiries submitted through our portal are stored securely under encrypted SSL protocols.</p>
        <h4 style="font-size: 0.95rem; color: var(--color-navy); margin: 0 0 6px;">2. Confidentiality &amp; Non-Disclosure (NDA)</h4>
        <p style="margin-bottom: 12px;">Proprietary bottle designs and custom mould specifications remain 100% confidential. We strictly execute Non-Disclosure Agreements (NDAs) for custom tooling and private-label packaging developments.</p>
        <h4 style="font-size: 0.95rem; color: var(--color-navy); margin: 0 0 6px;">3. Information Usage &amp; Communication</h4>
        <p style="margin-bottom: 12px;">Contact details provided during RFQ submissions are exclusively utilized by our engineering team to provide quotations, technical specifications, and shipment status updates. We do not sell or share data with third parties.</p>
        <p style="margin-bottom: 0; font-size: 0.82rem; font-weight: 700;"><a href="/privacy" style="color: var(--color-navy); text-decoration: underline;">Read Full Privacy &amp; Data Protection Policy &rarr;</a></p>
      </div>
      <button onclick="window.closePrivacyModal()" class="tn-btn-gold" style="width: 100%; margin-top: 20px; text-align: center; margin-left: 0;">CLOSE PRIVACY STATEMENT</button>
    </div>
  </div>


  <!-- Google Translate Hidden Engine (L3: Gated behind interaction / active translation) -->
  <div id="google_translate_element" style="display:none;position:absolute;top:-9999px;"></div>
  <script type="text/javascript">
    var gtLoaded = false;
    function loadGoogleTranslate() {
      if (gtLoaded) return;
      gtLoaded = true;
      var s = document.createElement('script');
      s.type = 'text/javascript';
      s.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
      document.body.appendChild(s);
    }
    function googleTranslateElementInit() {
      new google.translate.TranslateElement({
        pageLanguage: 'en',
        includedLanguages: 'en,es,fr,de,ar,ru,zh-CN,ja,pt,it,hi,nl,tr,ko,vi,id,pl,th',
        autoDisplay: false
      }, 'google_translate_element');
    }
    // Auto-load only if user previously active translation cookie exists
    if (document.cookie.indexOf('googtrans') !== -1) {
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadGoogleTranslate);
      } else {
        loadGoogleTranslate();
      }
    }
  </script>

  <script>
    // =========================================================================
    // COMPACT LANGUAGE DROPDOWN CONTROLLER (No Pop-up)
    // =========================================================================
    window.toggleLanguageDropdown = function(e) {
      if (e) {
        e.preventDefault();
        e.stopPropagation();
      }
      var menu = document.getElementById('tn-lang-dropdown-menu');
      var btn = document.getElementById('top-bar-lang-btn');
      if (!menu) return;

      var isOpen = menu.classList.contains('open');
      if (isOpen) {
        menu.classList.remove('open');
        if (btn) {
          btn.classList.remove('open');
          btn.setAttribute('aria-expanded', 'false');
        }
      } else {
        if (typeof loadGoogleTranslate === 'function') loadGoogleTranslate();
        // Close mobile dropdown if open
        window.closeMobileLangDropdown();
        menu.classList.add('open');
        if (btn) {
          btn.classList.add('open');
          btn.setAttribute('aria-expanded', 'true');
        }
      }
    };

    window.closeLanguageDropdown = function() {
      var menu = document.getElementById('tn-lang-dropdown-menu');
      var btn = document.getElementById('top-bar-lang-btn');
      if (menu) menu.classList.remove('open');
      if (btn) {
        btn.classList.remove('open');
        btn.setAttribute('aria-expanded', 'false');
      }
    };

    window.toggleMobileLangDropdown = function(e) {
      if (e) {
        e.preventDefault();
        e.stopPropagation();
      }
      var mMenu = document.getElementById('mobile-lang-dropdown-menu');
      var mBtn = document.getElementById('mobile-lang-btn');
      if (!mMenu) return;

      var isOpen = mMenu.classList.contains('open') || mMenu.style.display === 'block';
      if (isOpen) {
        mMenu.classList.remove('open');
        mMenu.style.display = 'none';
        if (mBtn) mBtn.classList.remove('open');
      } else {
        if (typeof loadGoogleTranslate === 'function') loadGoogleTranslate();
        window.closeLanguageDropdown();
        mMenu.classList.add('open');
        mMenu.style.display = 'block';
        if (mBtn) mBtn.classList.add('open');
      }
    };

    window.closeMobileLangDropdown = function() {
      var mMenu = document.getElementById('mobile-lang-dropdown-menu');
      var mBtn = document.getElementById('mobile-lang-btn');
      if (mMenu) {
        mMenu.classList.remove('open');
        mMenu.style.display = 'none';
      }
      if (mBtn) mBtn.classList.remove('open');
    };

    // Close dropdowns on outside click
    document.addEventListener('click', function(e) {
      var wrapper = document.getElementById('top-bar-lang-wrapper');
      if (wrapper && !wrapper.contains(e.target)) {
        window.closeLanguageDropdown();
      }
      var mSection = document.querySelector('.mobile-nav-lang-section');
      if (mSection && !mSection.contains(e.target)) {
        window.closeMobileLangDropdown();
      }
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        window.closeLanguageDropdown();
        window.closeMobileLangDropdown();
      }
    });

    // Language Selection Handler
    window.selectLanguage = function(langCode, langName, shortCode) {
      try {
        shortCode = shortCode || (langCode || 'EN').toUpperCase().split('-')[0];

        localStorage.setItem('tn_selected_lang', langCode);
        localStorage.setItem('tn_selected_lang_name', langName);
        localStorage.setItem('tn_selected_lang_code', shortCode);

        // Google Translate Cookie management
        var host = window.location.hostname;
        var hostParts = host.split('.');
        var cookieDomain = (hostParts.length > 1 && host !== '127.0.0.1' && host !== 'localhost') 
          ? ('.' + hostParts.slice(-2).join('.')) 
          : host;

        if (langCode === 'en') {
          document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
          document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=" + cookieDomain;
          document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=." + host;
        } else {
          var cookieVal = "/en/" + langCode;
          document.cookie = "googtrans=" + cookieVal + "; path=/;";
          document.cookie = "googtrans=" + cookieVal + "; path=/; domain=" + cookieDomain;
          document.cookie = "googtrans=" + cookieVal + "; path=/; domain=." + host;
        }

        // Update Button UI
        var nameEl = document.getElementById('top-lang-name');
        if (nameEl) nameEl.textContent = shortCode;

        var btnEl = document.getElementById('top-bar-lang-btn');
        if (btnEl) btnEl.title = 'Language: ' + langName + ' (' + shortCode + ')';

        var mNameEl = document.getElementById('mobile-lang-name');
        if (mNameEl) mNameEl.textContent = 'Language: ' + langName + ' (' + shortCode + ')';

        // Update active class on items
        document.querySelectorAll('.tn-lang-dropdown-item').forEach(function(item) {
          if (item.getAttribute('data-lang') === langCode) {
            item.classList.add('active');
          } else {
            item.classList.remove('active');
          }
        });

        // Close dropdowns
        window.closeLanguageDropdown();
        window.closeMobileLangDropdown();

        // Trigger Google Translate engine
        var combo = document.querySelector('.goog-te-combo');
        if (combo) {
          combo.value = langCode;
          combo.dispatchEvent(new Event('change'));
        } else {
          setTimeout(function() {
            window.location.reload();
          }, 150);
        }
      } catch (err) {
        console.error("Language selector exception:", err);
      }
    };

    // Backward compatibility aliases so legacy calls do not error
    window.openLanguageModal = function() { window.toggleLanguageDropdown(); };
    window.closeLanguageModal = function() { window.closeLanguageDropdown(); };

    // Initialize Language Selection on page load
    (function initLang() {
      var savedLang = localStorage.getItem('tn_selected_lang') || 'en';
      var savedName = localStorage.getItem('tn_selected_lang_name') || 'English';
      var savedCode = localStorage.getItem('tn_selected_lang_code') || 'EN';

      // Detect existing googtrans cookie
      var cookieMatch = document.cookie.match(/googtrans=\/en\/([a-zA-Z\-]+)/);
      if (cookieMatch && cookieMatch[1]) {
        savedLang = cookieMatch[1];
      }

      var item = document.querySelector('.tn-lang-dropdown-item[data-lang="' + savedLang + '"]');
      if (item) {
        var codeEl = item.querySelector('.tn-lang-item-code');
        if (codeEl) savedCode = codeEl.textContent.trim();
        var nameEl = item.querySelector('.tn-lang-item-name');
        if (nameEl) savedName = nameEl.textContent.trim();
      }

      var topName = document.getElementById('top-lang-name');
      if (topName) topName.textContent = savedCode;

      var btnEl = document.getElementById('top-bar-lang-btn');
      if (btnEl) btnEl.title = 'Language: ' + savedName + ' (' + savedCode + ')';

      var mNameEl = document.getElementById('mobile-lang-name');
      if (mNameEl) mNameEl.textContent = 'Language: ' + savedName + ' (' + savedCode + ')';

      document.querySelectorAll('.tn-lang-dropdown-item').forEach(function(el) {
        if (el.getAttribute('data-lang') === savedLang) {
          el.classList.add('active');
        } else {
          el.classList.remove('active');
        }
      });
    })();
  </script>
