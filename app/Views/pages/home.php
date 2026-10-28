  <!-- =======================================================================
       VIEW 1: HOME PAGE
       ======================================================================= -->
  <section class="page-view" id="view-home">
    
    <!-- 1. Hero Banner ("Our packaging experts") -->
    <section class="frapak-hero-banner">
      <div class="frapak-container">
        <div class="hero-content-box">
          <span class="hero-badge-tag">YOUR PACKAGING EXPERIENCE & EXPERTISE</span>
          <h1>Primary Packaging Specialists & Manufacturers</h1>
          <p class="hero-desc">
            TrueNorth Group is your hybrid packaging partner, seamlessly blending precision manufacturing and global distribution to provide comprehensive bottles, jars, pumps, and closures tailored to your specific needs.
          </p>
          <div class="hero-cta-wrap">
            <a href="/products" class="frapak-btn-outline">
              VIEW OUR PACKAGING
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" style="margin-left: 6px;"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <button class="frapak-btn-gold" onclick="window.openInquiry()">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" style="margin-right: 6px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              SEND AN INQUIRY
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- 2. Product Catalogue: Categories Grid (Server-Rendered PHP from Database) -->
    <section class="frapak-section">
      <div class="frapak-container">
        <div class="frapak-section-title-wrap">
          <h2 class="frapak-section-title">Choose from our catalogue of primary packaging products</h2>
        </div>

        <div class="frapak-cat-tiles-grid" id="home-cat-tiles-grid">
          <?php foreach ($categories as $cat): ?>
            <a href="/products?category=<?= urlencode($cat['slug']) ?>" class="frapak-tile-card" style="text-decoration: none;">
              <div class="frapak-tile-image">
                <img src="<?= htmlspecialchars($cat['image'] ?? '/logo_svg.svg') ?>" alt="<?= htmlspecialchars($cat['name']) ?>" loading="lazy" />
              </div>
              <div class="frapak-tile-bar">
                <span><?= htmlspecialchars($cat['name']) ?></span>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- 3. Markets Section -->
    <section class="frapak-section-grey">
      <div class="frapak-container">
        <div class="frapak-section-title-wrap">
          <h2 class="frapak-section-title">Markets of TrueNorth Group</h2>
          <p style="color: #64748B; font-size: 0.95rem; max-width: 750px; margin: 8px auto 0; text-align: center; line-height: 1.5;">
            Tailored primary packaging solutions engineered to meet rigorous compliance and aesthetic standards across 5 key industries.
          </p>
        </div>

        <div class="frapak-markets-grid" id="home-markets-grid">
          <a href="/products?industry=cosmetics" class="frapak-market-tile" style="text-decoration: none;">
            <div class="frapak-market-img"><img src="/vikaas_inputs/COSMETICS.webp" alt="Cosmetics & Personal Care" loading="lazy" onerror="this.onerror=null;this.src='/vikaas_inputs/cosmetics.webp';" /></div>
            <div class="frapak-tile-bar"><span>Cosmetics & Care</span></div>
          </a>
          <a href="/products?industry=pharma" class="frapak-market-tile" style="text-decoration: none;">
            <div class="frapak-market-img"><img src="/vikaas_inputs/PHARMA_BOTTLES.webp" alt="Pharmaceuticals & Healthcare" loading="lazy" onerror="this.onerror=null;this.src='/vikaas_inputs/pharma_bottles.webp';" /></div>
            <div class="frapak-tile-bar"><span>Pharma & Health</span></div>
          </a>
          <a href="/products?industry=agro" class="frapak-market-tile" style="text-decoration: none;">
            <div class="frapak-market-img"><img src="/vikaas_inputs/AGRO_PESTICIDES.webp" alt="Agrochemicals & Pesticides" loading="lazy" onerror="this.onerror=null;this.src='/vikaas_inputs/agro_pesticides.webp';" /></div>
            <div class="frapak-tile-bar"><span>Agro & Pesticides</span></div>
          </a>
          <a href="/products?industry=nutraceuticals" class="frapak-market-tile" style="text-decoration: none;">
            <div class="frapak-market-img"><img src="/vikaas_inputs/PET_nutraceuticle_jars.webp" alt="Nutraceuticals & Health Supplements" loading="lazy" onerror="this.onerror=null;this.src='/vikaas_inputs/pet_nutraceuticle_jars.webp';" /></div>
            <div class="frapak-tile-bar"><span>Nutraceutical Jars</span></div>
          </a>
          <a href="/products?industry=liquor" class="frapak-market-tile" style="text-decoration: none;">
            <div class="frapak-market-img"><img src="/vikaas_inputs/LIQUOR_Bottles.webp" alt="Liquor, Spirits & Beverages" loading="lazy" onerror="this.onerror=null;this.src='/vikaas_inputs/liquor_bottles.webp';" /></div>
            <div class="frapak-tile-bar"><span>Liquor & Spirits</span></div>
          </a>
        </div>
      </div>
    </section>

    <!-- 4. Sustainable Products Section -->
    <section class="frapak-section">
      <div class="frapak-container">
        <div class="frapak-section-title-wrap">
          <h2 class="frapak-section-title">A large selection of sustainable rPET products</h2>
        </div>

        <div class="rpet-layout">
          <!-- Left 2 Product Tiles -->
          <div class="rpet-cards-stack">
            <a href="/products?category=cosmetics-bottles" class="rpet-card" style="text-decoration: none;">
              <div class="frapak-tile-image">
                <img src="/vikaas_inputs/thumbnails/thumbnail_pet_bottles.webp" alt="rPET bottles" loading="lazy" />
              </div>
              <div class="rpet-card-bar">rPET bottles</div>
            </a>
            <a href="/products?category=jars" class="rpet-card" style="text-decoration: none;">
              <div class="frapak-tile-image">
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
                We also have <a href="/products?category=cosmetics-bottles">rPET bottles on stock</a> and <a href="/products?category=jars">rPET jars on stock</a>.
              </p>
            </div>

            <button type="button" class="frapak-readmore-btn" onclick="window.togglePcrContent(this)">
              <span>Read More</span>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
          </div>
        </div>
      </div>
    </section>

  </section>
