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
