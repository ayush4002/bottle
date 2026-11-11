<?php
if (empty($company) && class_exists('ProductModel')) {
    $company = ProductModel::getCompanyInfo();
}
?>
  <!-- =========================================================================
       FOOTER (Clean 4-Column Responsive Architecture)
       ========================================================================= -->
  <footer class="tn-footer">
    <div class="tn-container">
      
      <!-- Footer Brand Header (Logo & Description above accordions) -->
      <div class="footer-brand-header">
        <a href="/" class="footer-brand-logo-link">
          <img src="/logo_svg.svg" alt="TrueNorth Group Logo" class="footer-brand-logo" width="160" height="40" />
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
            
            <!-- By Category Section -->
            <div class="footer-section-group" id="footer-group-category">
              <button type="button" class="footer-subheading-btn" onclick="window.toggleFooterSection('footer-group-category')" aria-expanded="true">
                <span>By Category:</span>
                <svg class="footer-sub-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
              </button>
              <div class="footer-sub-content">
                <ul class="footer-link-grid">
                  <?php 
                    $footerCategories = !empty($categories) ? $categories : (class_exists('ProductModel') ? ProductModel::getCategories() : []);
                    foreach ($footerCategories as $cat): 
                  ?>
                    <li><a href="/products?category=<?= urlencode($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>

            <!-- By Industry Market Section -->
            <div class="footer-section-group" id="footer-group-industry" style="margin-top: 14px;">
              <button type="button" class="footer-subheading-btn" onclick="window.toggleFooterSection('footer-group-industry')" aria-expanded="true">
                <span>By Industry Market:</span>
                <svg class="footer-sub-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
              </button>
              <div class="footer-sub-content">
                <ul class="footer-link-grid">
                  <li><a href="/products?industry=cosmetics">Cosmetics &amp; Personal Care</a></li>
                  <li><a href="/products?industry=pharma">Pharmaceuticals &amp; Healthcare</a></li>
                  <li><a href="/products?industry=agro">Agrochemicals &amp; Pesticides</a></li>
                  <li><a href="/products?industry=nutraceuticals">Nutraceuticals &amp; Supplements</a></li>
                  <li><a href="/products?industry=liquor">Liquor, Spirits &amp; Beverages</a></li>
                </ul>
              </div>
            </div>

            <!-- Direct View All Link -->
            <a href="/products" class="footer-view-all-link">
              <span>View All Products</span>
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>

            <!-- Line After All Product Button -->
            <div class="footer-view-all-divider"></div>

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
              <strong><?= htmlspecialchars($company['name'] ?? 'TrueNorth Group') ?></strong><br />
              <?= nl2br(htmlspecialchars($company['hq'] ?? '#201, Vardhaman Trade Towers, Rohit Kunj, LSC Pitampura, Delhi-110034')) ?>
            </p>
            <div class="footer-contact-block">
              <a href="tel:<?= htmlspecialchars($company['phone_tel'] ?? '+919810220761') ?>" class="footer-phone-link" style="color: var(--color-gold) !important; font-weight: 700;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--color-gold)" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                <?= htmlspecialchars($company['phone'] ?? '+91 98102 20761') ?>
              </a>
              <a href="https://wa.me/<?= htmlspecialchars($company['whatsapp'] ?? '919810220761') ?>" class="footer-whatsapp-link" target="_blank" rel="noopener noreferrer" style="color: var(--color-gold) !important; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--color-gold)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9 L3 21l1.9-5.7 a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5 a8.48 8.48 0 0 1 8 8v.5z"/>
                  <path d="M9 9a5 5 0 0 0 6 6"/>
                </svg>
                <?= htmlspecialchars($company['phone'] ?? '+91 98102 20761') ?>
              </a>
              <a href="mailto:<?= htmlspecialchars($company['email'] ?? 'info@wetruenorthgroup.com') ?>" class="footer-email-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                <?= htmlspecialchars($company['email'] ?? 'info@wetruenorthgroup.com') ?>
              </a>
            </div>
            <p class="footer-closures-line" style="margin-top: 8px; margin-bottom: 0; font-size: 0.8125rem; color: #64748B;">
              <span>Closures: Ningbo, China</span>
            </p>
            <?php
            $tnLinkedin = class_exists('SeoModel') ? (SeoModel::getSetting('org_social_linkedin') ?: '') : '';
            if (!empty($tnLinkedin)):
            ?>
              <a href="<?= htmlspecialchars($tnLinkedin) ?>" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; gap: 6px; margin-top: 10px; font-size: 0.8125rem; font-weight: 700; color: #94a3b8; text-decoration: none; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 10px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.86 0-2.14 1.45-2.14 2.95v5.66H9.35V9h3.41v1.56h.05c.47-.9 1.63-1.85 3.36-1.85 3.6 0 4.27 2.37 4.27 5.45v6.29zM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12zM7.12 20.45H3.56V9h3.56v11.45zM22.22 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.73V1.73C24 .77 23.2 0 22.22 0z"/></svg>
                LinkedIn
              </a>
            <?php endif; ?>
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
              <li><a href="/blog">Packaging Blog &amp; Insights</a></li>
            </ul>
          </div>
        </div>

      </div>

      <div class="footer-copyright-bar">
        <span>© 2000 - <?= date('Y') ?> <?= htmlspecialchars($company['name'] ?? 'TrueNorth Group') ?>. Over 25 Years of Industry Excellence. All Rights Reserved.</span>
        <span class="footer-legal-links">
          <a href="/terms">General Terms &amp; Conditions</a>
          <span class="footer-legal-sep">|</span>
          <a href="/privacy">Privacy Statement</a>
        </span>
      </div>
    </div>
  </footer>

  <!-- Site JS extracted to a cacheable, deferred file (CWV: removes 27KB of
       render-blocking inline script; parsed once, cached for a year) -->
    <script src="/js/site-main.js?v=<?= @filemtime(dirname(__DIR__, 3) . '/public/js/site-main.js') ?: 1 ?>" defer></script>
  <?php if (function_exists('seo_footer_scripts')) { echo seo_footer_scripts(); } ?>

  <!-- Sticky Mobile Call / WhatsApp Bar (Mobile UX & conversion) -->
  <div class="tn-sticky-mob-bar" style="position: fixed; left: 0; right: 0; bottom: 0; z-index: 90; display: none; background: #16273f; border-top: 1px solid rgba(255,255,255,0.08);">
    <a href="tel:<?= htmlspecialchars($company['phone_tel'] ?? '+919810220761') ?>" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 8px; color: #fff; text-decoration: none; font-weight: 700; font-size: 0.82rem;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
      CALL US
    </a>
    <a href="https://wa.me/<?= htmlspecialchars($company['whatsapp'] ?? '919810220761') ?>?text=<?= urlencode('Hi TrueNorth Group — I would like to request a quote & samples.') ?>" target="_blank" rel="noopener noreferrer" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 8px; color: #fff; background: #16a34a; text-decoration: none; font-weight: 700; font-size: 0.82rem;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8z"/></svg>
      WHATSAPP
    </a>
  </div>
  <script>
    // Show sticky Call/WhatsApp bar on mobile viewports only
    (function () {
      var bar = document.querySelector('.tn-sticky-mob-bar');
      if (!bar) return;
      function sync() {
        bar.style.display = window.innerWidth < 768 ? 'flex' : 'none';
      }
      window.addEventListener('resize', sync, { passive: true });
      sync();
    })();
  </script>
</body>
</html>
