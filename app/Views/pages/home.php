  <!-- =======================================================================
       VIEW 1: HOME PAGE
       ======================================================================= -->
  <section class="page-view" id="view-home">
    
    <!-- 1. Hero Banner ("Our packaging experts") -->
    <section class="tn-hero-banner">
      <div class="tn-container">
        <div class="hero-content-box">
          <span class="hero-badge-tag">PRIMARY PACKAGING EXPERTS</span>
          <h1>Primary Packaging Specialists & Manufacturers</h1>
          <p class="hero-desc">
            TrueNorth Group is your integrated primary packaging partner and manufacturer, seamlessly blending precision cleanroom production and global distribution to deliver bottles, jars, pumps, and closures tailored to your specific needs.
          </p>
          <div class="hero-cta-wrap">
            <a href="/products" class="tn-btn-outline">
              VIEW OUR PACKAGING
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" style="margin-left: 6px;"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <button class="tn-btn-gold" onclick="window.openInquiry()">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" style="margin-right: 6px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              SEND AN INQUIRY
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- 2. Product Catalogue: Categories Grid (Server-Rendered PHP from Database) -->
    <section class="tn-section">
      <div class="tn-container">
        <div class="tn-section-title-wrap">
          <h2 class="tn-section-title">Choose from our catalogue of primary packaging products</h2>
        </div>

        <div class="tn-cat-tiles-grid" id="home-cat-tiles-grid">
          <?php foreach ($categories as $cat): ?>
            <a href="/products?category=<?= urlencode($cat['slug']) ?>" class="tn-tile-card" style="text-decoration: none;">
              <div class="tn-tile-image">
                <img src="<?= htmlspecialchars($cat['image'] ?? '/logo_svg.svg') ?>" alt="<?= htmlspecialchars($cat['name']) ?>" loading="lazy" />
              </div>
              <div class="tn-tile-bar">
                <span><?= htmlspecialchars($cat['name']) ?></span>
              </div>
            </a>
          <?php endforeach; ?>
        </div>

        <!-- Catalogue Direct Enquiry CTA Banner -->
        <div class="home-enquiry-banner">
          <div class="home-enquiry-content">
            <span class="home-enquiry-tag">DIRECT FACTORY WHOLESALE &amp; SAMPLING</span>
            <h3 class="home-enquiry-title">Need Custom Capacities, Specific Neck Finishes, or Bulk Pricing?</h3>
            <p class="home-enquiry-desc">
              Whether you require custom moulds, sterile cleanroom production, or fast stock dispatch, our packaging specialists provide itemized quotes, technical drawings, and free sample batches within 24 business hours.
            </p>
            <div class="home-enquiry-features">
              <span class="home-enquiry-feature">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#b8892e" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Free Physical Samples
              </span>
              <span class="home-enquiry-feature">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#b8892e" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                MOQs from 5,000 Units
              </span>
              <span class="home-enquiry-feature">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#b8892e" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                TÜV NORD ISO 9001 Certified
              </span>
              <span class="home-enquiry-feature">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#b8892e" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Global Seaport Logistics
              </span>
            </div>
          </div>
          <div class="home-enquiry-actions">
            <button type="button" class="tn-btn-gold home-enquiry-btn" onclick="window.openInquiry('Catalogue Wholesale RFQ & Samples')">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 6px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              REQUEST A QUOTE &amp; SAMPLES
            </button>
            <a href="tel:+919810220761" class="home-enquiry-phone-link">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
              Direct Sales: +91 98102 20761
            </a>
          </div>
        </div>

      </div>
    </section>

    <!-- 3. Markets Section -->
    <section class="tn-section-grey">
      <div class="tn-container">
        <div class="tn-section-title-wrap">
          <h2 class="tn-section-title">Target Industries &amp; Specialised Markets</h2>
          <p style="color: #64748B; font-size: 0.95rem; max-width: 750px; margin: 8px auto 0; text-align: center; line-height: 1.5;">
            Tailored primary packaging solutions engineered to meet rigorous compliance and aesthetic standards across 5 key industries.
          </p>
        </div>

        <div class="tn-markets-grid" id="home-markets-grid">
          <a href="/products?industry=cosmetics" class="tn-market-tile" style="text-decoration: none;">
            <div class="tn-market-img"><img src="/vikaas_inputs/COSMETICS.webp" alt="Cosmetics & Personal Care" loading="lazy" onerror="this.onerror=null;this.src='/vikaas_inputs/cosmetics.webp';" /></div>
            <div class="tn-tile-bar"><span>Cosmetics & Care</span></div>
          </a>
          <a href="/products?industry=pharma" class="tn-market-tile" style="text-decoration: none;">
            <div class="tn-market-img"><img src="/vikaas_inputs/PHARMA_BOTTLES.webp" alt="Pharmaceuticals & Healthcare" loading="lazy" onerror="this.onerror=null;this.src='/vikaas_inputs/pharma_bottles.webp';" /></div>
            <div class="tn-tile-bar"><span>Pharma & Health</span></div>
          </a>
          <a href="/products?industry=agro" class="tn-market-tile" style="text-decoration: none;">
            <div class="tn-market-img"><img src="/vikaas_inputs/AGRO_PESTICIDES.webp" alt="Agrochemicals & Pesticides" loading="lazy" onerror="this.onerror=null;this.src='/vikaas_inputs/agro_pesticides.webp';" /></div>
            <div class="tn-tile-bar"><span>Agro & Pesticides</span></div>
          </a>
          <a href="/products?industry=nutraceuticals" class="tn-market-tile" style="text-decoration: none;">
            <div class="tn-market-img"><img src="/vikaas_inputs/PET_nutraceuticle_jars.webp" alt="Nutraceuticals & Health Supplements" loading="lazy" onerror="this.onerror=null;this.src='/vikaas_inputs/pet_nutraceuticle_jars.webp';" /></div>
            <div class="tn-tile-bar"><span>Nutraceuticals & Health</span></div>
          </a>
          <a href="/products?industry=liquor" class="tn-market-tile" style="text-decoration: none;">
            <div class="tn-market-img"><img src="/vikaas_inputs/LIQUOR_Bottles.webp" alt="Liquor, Spirits & Beverages" loading="lazy" onerror="this.onerror=null;this.src='/vikaas_inputs/liquor_bottles.webp';" /></div>
            <div class="tn-tile-bar"><span>Liquor & Spirits</span></div>
          </a>
        </div>
      </div>
    </section>


    <!-- Facts & Figures: Our Strength Section (TrueNorth Luxury Brand Theme) -->
    <section class="tn-section tn-strength-section" id="home-strength-section">
      <div class="tn-container">
        
        <div class="tn-section-title-wrap">
          <span class="tn-section-eyebrow">OUR STRENGTH</span>
          <h2 class="tn-section-title">Facts &amp; Figures</h2>
          <p class="tn-section-lead">Pioneering cleanroom production, certified quality management, and trusted primary packaging supply across 5 continents.</p>
        </div>

        <div class="tn-strength-grid">
          
          <!-- 1. ISO TÜV 9001:2015 -->
          <div class="tn-strength-card">
            <div class="tn-strength-icon-wrap" aria-hidden="true">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <polyline points="9 12 11 14 15 10"/>
              </svg>
            </div>
            <div class="tn-strength-info">
              <h3 class="tn-strength-val">ISO TÜV 9001:2015</h3>
              <p class="tn-strength-label">Certified Quality Management</p>
            </div>
          </div>

          <!-- 2. US FDA Approved -->
          <div class="tn-strength-card">
            <div class="tn-strength-icon-wrap" aria-hidden="true">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="6"/>
                <path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>
              </svg>
            </div>
            <div class="tn-strength-info">
              <h3 class="tn-strength-val">US FDA Approved</h3>
              <p class="tn-strength-label">Quality &amp; Regulatory Standards</p>
            </div>
          </div>

          <!-- 3. DMF Bottles -->
          <div class="tn-strength-card">
            <div class="tn-strength-icon-wrap" aria-hidden="true">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="7" y="2" width="10" height="4" rx="1"/>
                <path d="M9 6v3a3 3 0 0 1-3 3H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-1a3 3 0 0 1-3-3V6"/>
                <line x1="12" y1="13" x2="12" y2="17"/>
                <line x1="10" y1="15" x2="14" y2="15"/>
              </svg>
            </div>
            <div class="tn-strength-info">
              <h3 class="tn-strength-val">DMF Bottles</h3>
              <p class="tn-strength-label">Pharmaceutical Packaging</p>
            </div>
          </div>

          <!-- 4. Class 10,000 -->
          <div class="tn-strength-card">
            <div class="tn-strength-icon-wrap" aria-hidden="true">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"/>
                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
              </svg>
            </div>
            <div class="tn-strength-info">
              <h3 class="tn-strength-val">Class 10,000</h3>
              <p class="tn-strength-label">Manufacturing Environment</p>
            </div>
          </div>

          <!-- 5. 25 Years -->
          <div class="tn-strength-card">
            <div class="tn-strength-icon-wrap" aria-hidden="true">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
              </svg>
            </div>
            <div class="tn-strength-info">
              <h3 class="tn-strength-val">25 Years</h3>
              <p class="tn-strength-label">Manufacturing Experience</p>
            </div>
          </div>

          <!-- 6. 5 Continents -->
          <div class="tn-strength-card">
            <div class="tn-strength-icon-wrap" aria-hidden="true">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="2" y1="12" x2="22" y2="12"/>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
              </svg>
            </div>
            <div class="tn-strength-info">
              <h3 class="tn-strength-val">5 Continents</h3>
              <p class="tn-strength-label">Happy Customers Worldwide</p>
            </div>
          </div>

        </div>
      </div>
    </section>



    <!-- 4. Sustainable Products Section -->
    <section class="tn-section">
      <div class="tn-container">
        <div class="tn-section-title-wrap">
          <h2 class="tn-section-title">Sustainable rPET & Post-Consumer Recycled Packaging</h2>
        </div>

        <div class="rpet-layout">
          <!-- Left 2 Product Tiles -->
          <div class="rpet-cards-stack">
            <a href="/products?category=cosmetics-bottles" class="rpet-card" style="text-decoration: none;">
              <div class="tn-tile-image">
                <img src="/vikaas_inputs/thumbnails/thumbnail_pet_bottles.webp" alt="rPET bottles" loading="lazy" />
              </div>
              <div class="rpet-card-bar">rPET bottles</div>
            </a>
            <a href="/products?category=jars" class="rpet-card" style="text-decoration: none;">
              <div class="tn-tile-image">
                <img src="/vikaas_inputs/thumbnails/thumbnail_nutraceuticle_jars.webp" alt="rPET jars" loading="lazy" />
              </div>
              <div class="rpet-card-bar">rPET jars</div>
            </a>
          </div>

          <!-- Right Content -->
          <div class="rpet-text-content">
            <h3>More Choice. Less Virgin Plastic.</h3>
            <p class="pcr-summary-lead">
              Every product has different packaging needs. That’s why we keep adding bottle and jar sizes, neck options and recycled materials to the TrueNorth Group range—giving you more flexibility when choosing your packaging.
            </p>
            
            <div class="pcr-expandable-content" id="pcr-expandable-content">
              <p>
                For businesses looking to use recycled plastic, we offer bottles and jars made from 100% recycled PET (rPET), with minimum orders starting at 30,000 units. Standard options are also held in stock for immediate requirements. From selecting a ready-made bottle to developing a customised design, we work with you to find a solution that suits your product and supports your sustainability goals.
              </p>
              <p>
                We also have <a href="/products?category=cosmetics-bottles">rPET bottles in stock</a> and <a href="/products?category=jars">rPET jars in stock</a>.
              </p>
            </div>

            <button type="button" class="tn-readmore-btn" onclick="window.togglePcrContent(this)">
              <span>Read More</span>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
          </div>
        </div>
      </div>
    </section>

  </section>
