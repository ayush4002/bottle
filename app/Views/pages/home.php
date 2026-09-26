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
        </div>

        <div class="frapak-markets-grid" id="home-markets-grid">
          <a href="/products?category=cosmetic-bottles" class="frapak-market-tile" style="text-decoration: none;">
            <div class="frapak-market-img"><img src="/vikaas_inputs/cosmetics.webp" alt="Cosmetics" loading="lazy" /></div>
            <div class="frapak-tile-bar"><span>Cosmetics</span></div>
          </a>
          <a href="/products?category=pet-bottles" class="frapak-market-tile" style="text-decoration: none;">
            <div class="frapak-market-img"><img src="/vikaas_inputs/pharma_bottles.webp" alt="Pharma" loading="lazy" /></div>
            <div class="frapak-tile-bar"><span>Pharma</span></div>
          </a>
          <a href="/products?category=agro-bottles" class="frapak-market-tile" style="text-decoration: none;">
            <div class="frapak-market-img"><img src="/vikaas_inputs/agro_pesticides.webp" alt="Agro" loading="lazy" /></div>
            <div class="frapak-tile-bar"><span>Agro</span></div>
          </a>
          <a href="/products?category=pet-bottles" class="frapak-market-tile" style="text-decoration: none;">
            <div class="frapak-market-img"><img src="/vikaas_inputs/LIQUOR_Bottles.webp" alt="Beverages" loading="lazy" /></div>
            <div class="frapak-tile-bar"><span>Beverages</span></div>
          </a>
          <a href="/products?category=pet-bottles" class="frapak-market-tile" style="text-decoration: none;">
            <div class="frapak-market-img"><img src="/brand_assets/glass_bottles/7G3A8712.webp" alt="Liquor" loading="lazy" /></div>
            <div class="frapak-tile-bar"><span>Liquor</span></div>
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
            <a href="/products?category=pet-bottles" class="rpet-card" style="text-decoration: none;">
              <div class="frapak-tile-image">
                <img src="/vikaas_inputs/thumbnails/thumbnail_pet_bottles.webp" alt="rPET bottles" loading="lazy" />
              </div>
              <div class="rpet-card-bar">rPET bottles</div>
            </a>
            <a href="/products?category=pet-jars" class="rpet-card" style="text-decoration: none;">
              <div class="frapak-tile-image">
                <img src="/vikaas_inputs/thumbnails/thumbnail_nutraceuticle_jars.webp" alt="rPET jars" loading="lazy" />
              </div>
              <div class="rpet-card-bar">rPET jars</div>
            </a>
          </div>

          <!-- Right Content -->
          <div class="rpet-text-content">
            <h3>Content: PCR plastics</h3>
            <p class="pcr-summary-lead">
              TrueNorth Group is dedicated to continuous innovation, frequently expanding its diverse array of products by introducing a variety of neck sizes and capacities to meet evolving market demands. Furthermore, we're enhancing our product range by integrating sustainable, eco-friendly options, demonstrating our commitment to environmental responsibility.
            </p>
            
            <div class="pcr-expandable-content" id="pcr-expandable-content">
              <p>
                Our selection of PET bottles and jars, now available in 100% recycled PET (rPET), reflects this commitment. These eco-conscious choices are offered with a manageable minimum order quantity (MOQ) of just 20,000 units, making sustainability accessible to a wider range of customers. In addition, we ensure ready availability of rPET bottles and jars, keeping them in stock to meet immediate needs and demands. This approach allows us to cater to both bespoke and standard requirements, aligning with our mission to provide high-quality, sustainable packaging solutions.
              </p>
              <p>
                We also have <a href="/products?category=pet-bottles">rPET bottles on stock</a> and <a href="/products?category=pet-jars">rPET jars on stock</a>.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

  </section>
