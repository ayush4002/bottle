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


<section class="facts-section">
    <div class="facts-container">

        <div class="facts-header">
            <span>OUR STRENGTH</span>
            <h2 class=".tn-section-title">Facts &amp; Figures</h2>
        </div>

        <div class="facts-grid">

            <div class="facts-item">
                <h3>ISO TÜV 9001:2015</h3>
                <p>Certified Quality Management</p>
            </div>

            <div class="facts-item">
                <h3>US FDA Approved</h3>
                <p>Quality &amp; Regulatory Standards</p>
            </div>

            <div class="facts-item">
                <h3>DMF Bottles</h3>
                <p>Pharmaceutical Packaging</p>
            </div>

            <div class="facts-item">
                <h3>Class 10,000</h3>
                <p>Manufacturing Environment</p>
            </div>

            <div class="facts-item">
                <h3>25 Years</h3>
                <p>Manufacturing Experience</p>
            </div>

            <div class="facts-item">
                <h3>5 Continents</h3>
                <p>Happy Customers Worldwide</p>
            </div>

        </div>
    </div>
</section>

<style>
.facts-section {
    padding: 60px 24px;
    background: #ffffff;
    font-family: inherit;
    color: #17243b;
}

.facts-container {
    max-width: 1250px;
    margin: 0 auto;
}

/* Center-aligned heading */
.facts-header {
    margin-bottom: 35px;
    text-align: center;
}

.facts-header span {
    display: block;
    margin-bottom: 8px;
    color: #2446a8;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.8px;
}

.facts-header h2 {
    margin: 0;
    font-size: 32px;
    line-height: 1.3;
    font-weight: 700;
    color: #17243b;
}

/* Facts grid */
.facts-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    border-top: 1px solid #e5e8ee;
    border-left: 1px solid #e5e8ee;
}

.facts-item {
    padding: 27px 24px;
    border-right: 1px solid #e5e8ee;
    border-bottom: 1px solid #e5e8ee;
    transition: background 0.2s ease;
}

.facts-item:hover {
    background: #f8faff;
}

.facts-item h3 {
    margin: 0 0 8px;
    font-size: 19px;
    line-height: 1.4;
    font-weight: 700;
    color: #2446a8;
}

.facts-item p {
    margin: 0;
    font-size: 13px;
    line-height: 1.7;
    color: #6b7280;
}

/* Tablet */
@media (max-width: 900px) {
    .facts-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Mobile: two items per row */
@media (max-width: 600px) {
    .facts-section {
        padding: 42px 16px;
    }

    .facts-header {
        margin-bottom: 28px;
    }

    .facts-header h2 {
        font-size: 27px;
    }

    .facts-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .facts-item {
        padding: 22px 12px;
    }

    .facts-item h3 {
        font-size: 15px;
    }

    .facts-item p {
        font-size: 12px;
        line-height: 1.6;
    }
}
</style>



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
