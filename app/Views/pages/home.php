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
            <a href="#products" class="frapak-btn-outline" onclick="window.navigateToPage('products')">
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

    <!-- 2. Product Catalogue: Exact 5 Categories (PET Bottles, PET Jars, Pumps, Caps, Sprays) -->
    <section class="frapak-section">
      <div class="frapak-container">
        <div class="frapak-section-title-wrap">
          <h2 class="frapak-section-title">Choose from our catalogue of primary packaging products</h2>
        </div>

        <div class="frapak-cat-tiles-grid" id="home-cat-tiles-grid">
          <!-- Rendered by app.js: Bottles PET, Bottles PE, Caps, Pumps, Sprayers, Triggers, Foamers, Airless, PET Jars, PP Jars -->
        </div>
      </div>
    </section>

    <!-- 3. Markets Section: Exact 5 Markets (Cosmetics, Pharma, Agro, Beverages, Liquor) -->
    <section class="frapak-section-grey">
      <div class="frapak-container">
        <div class="frapak-section-title-wrap">
          <h2 class="frapak-section-title">Markets of TrueNorth Group</h2>
        </div>

        <div class="frapak-markets-grid" id="home-markets-grid">
          <!-- Rendered by app.js: Cosmetics, Pharma, Agro, Beverages, Liquor -->
        </div>
      </div>
    </section>

    <!-- 4. Sustainable Products Section (Exact Client Content: PCR plastics) -->
    <section class="frapak-section">
      <div class="frapak-container">
        <div class="frapak-section-title-wrap">
          <h2 class="frapak-section-title">A large selection of sustainable rPET products</h2>
        </div>

        <div class="rpet-layout">
          <!-- Left 2 Product Tiles -->
          <div class="rpet-cards-stack">
            <div class="rpet-card" onclick="window.goToCategory('Bottles PET')">
              <div class="frapak-tile-image">
                <img src="/vikaas_inputs/thumbnails/THUMBNAIL_PET_BOTTLES.png" alt="rPET bottles" loading="lazy" />
              </div>
              <div class="rpet-card-bar">rPET bottles</div>
            </div>
            <div class="rpet-card" onclick="window.goToCategory('PET Jars')">
              <div class="frapak-tile-image">
                <img src="/vikaas_inputs/thumbnails/thumbnail_NUTRACEUTICLE_JARS.jpg" alt="rPET jars" loading="lazy" />
              </div>
              <div class="rpet-card-bar">rPET jars</div>
            </div>
          </div>

          <!-- Right Exact Client Content with Mobile-Friendly Read More Architecture -->
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
                We also have <a href="#products" onclick="window.goToCategory('PET Bottles')">rPET bottles on stock</a> and <a href="#products" onclick="window.goToCategory('PET Jars')">rPET jars on stock</a>.
              </p>
            </div>

            <button class="frapak-readmore-btn" id="pcr-readmore-toggle" onclick="window.togglePcrContent()" aria-expanded="false">
              <span>Read more +</span>
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- 5. Editorial Section (Exact Client Content & Headings in Mobile-First Cards) -->
    <section class="frapak-section-grey">
      <div class="frapak-container">
        <div class="frapak-section-title-wrap">
          <h2 class="frapak-section-title">Our Packaging Experience</h2>
        </div>

        <div class="editorial-two-col">
          <!-- Left Column: 6 Structured Mobile Cards / Accordions -->
          <div class="editorial-left-col">
            
            <div class="frapak-exp-card">
              <h4>Primary Packaging Expertise & Global Distribution</h4>
              <p class="exp-summary">TrueNorth Group is your ideal supplier and partner for premium quality primary packaging. As a hybrid packaging specialist, we seamlessly blend manufacturing and distribution to provide comprehensive packaging tailored to meet your specific needs.</p>
              <div class="exp-details">
                <p>With our audited partner facilities and controlled cleanroom operations, we are well positioned to serve domestic requirements as well as direct seaport global exports.</p>
              </div>
              <button class="frapak-exp-btn" onclick="window.toggleExpCard(this)" aria-expanded="false">
                <span>Read more &rarr;</span>
              </button>
            </div>

            <div class="frapak-exp-card">
              <h4>Three Decades of Excellence and Innovation</h4>
              <p class="exp-summary">For over 25 years, our partners have focused on the precision production of polyethylene terephthalate (PET) bottles, while also acting as a leading distributor for cosmetic and trigger pumps.</p>
              <div class="exp-details">
                <p>This dual competency ensures that we have a broad and deep understanding of the packaging market, making us the go-to partner for primary packaging across domestic and global markets.</p>
              </div>
              <button class="frapak-exp-btn" onclick="window.toggleExpCard(this)" aria-expanded="false">
                <span>Read more &rarr;</span>
              </button>
            </div>

            <div class="frapak-exp-card">
              <h4>Unparalleled Product Range</h4>
              <p class="exp-summary">Our comprehensive product portfolio encompasses a diverse range of standard and customised packaging solutions, including bottles, jars, pumps and closures.</p>
              <div class="exp-details">
                <p>We can accommodate specific requirements in terms of shape, size and colour, offering our clients endless possibilities for effectively promoting their products.</p>
              </div>
              <button class="frapak-exp-btn" onclick="window.toggleExpCard(this)" aria-expanded="false">
                <span>Read more &rarr;</span>
              </button>
            </div>

            <div class="frapak-exp-card">
              <h4>Quality Assurance Through In-House R&D</h4>
              <p class="exp-summary">Quality is a non-negotiable priority at TrueNorth Group. Our in-house Research and Development (R&D) team meticulously checks each manufactured product for quality and leak issues.</p>
              <div class="exp-details">
                <p>Through rigorous testing, which includes over 1,000 leak-free trials, we ensure that our closures and finger sprayers work seamlessly with our bottles and jars.</p>
              </div>
              <button class="frapak-exp-btn" onclick="window.toggleExpCard(this)" aria-expanded="false">
                <span>Read more &rarr;</span>
              </button>
            </div>

            <div class="frapak-exp-card">
              <h4>Food-grade certified</h4>
              <p class="exp-summary">Safety is our top priority. All our PET packaging, including bottles and jars, is certified as food-grade, making it ideal for the food and beverage industry, among others.</p>
              <div class="exp-details">
                <p>Manufactured strictly adhering to international food-approved ISO standards and cleanroom production protocols.</p>
              </div>
              <button class="frapak-exp-btn" onclick="window.toggleExpCard(this)" aria-expanded="false">
                <span>Read more &rarr;</span>
              </button>
            </div>

            <div class="frapak-exp-card">
              <h4>Sustainability as a core principle</h4>
              <p class="exp-summary">In an era increasingly defined by environmental concerns, we take our responsibility seriously. Our PET bottles and jars are available in 100% rPET and are made of post-consumer resin (PCR).</p>
              <div class="exp-details">
                <p>We are committed to the principles of reducing waste, reusing materials, refilling containers, and recycling, aligning our operations with sustainable practices.</p>
              </div>
              <button class="frapak-exp-btn" onclick="window.toggleExpCard(this)" aria-expanded="false">
                <span>Read more &rarr;</span>
              </button>
            </div>

          </div>

          <!-- Right Column: Factory Image, Logistics Image & Quality Capabilities Card -->
          <div class="editorial-right-col">
            <!-- Image 1: Manufacturing Facility -->
            <div class="editorial-img-box">
              <img src="/factory.webp" alt="Precision Cleanroom Manufacturing Operations" class="editorial-factory-img" loading="lazy" />
              <div class="editorial-img-caption">
                <span>Controlled Cleanroom & Automated Blow Molding Facility</span>
              </div>
            </div>

            <!-- Image 2: Global Supply Chain & Warehousing (Desktop Only) -->
            <div class="editorial-img-box desktop-only-feature">
              <img src="/benefit_supply.webp" alt="Global Supply Chain & Audited Warehousing" class="editorial-factory-img" loading="lazy" />
              <div class="editorial-img-caption">
                <span>Audited Warehousing, Barcode QA & Direct Seaport Exports</span>
              </div>
            </div>

            <!-- Executive Quality & Technical Showcase Card (Desktop Only) -->
            <div class="editorial-quality-card desktop-only-feature">
              <div class="quality-card-header">
                <div class="quality-badge">Certified Standards</div>
                <h4>Precision Quality & Global Assurance</h4>
              </div>
              <ul class="quality-features-list">
                <li>
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--color-gold)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  <div>
                    <strong>ISO 15593 Certified:</strong>
                    <span>Audited Class 10,000 cleanroom packaging manufacturing.</span>
                  </div>
                </li>
                <li>
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--color-gold)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  <div>
                    <strong>1,000+ Cycle Leak-Free QA:</strong>
                    <span>Rigorous vacuum seal & torque testing across all neck finishes.</span>
                  </div>
                </li>
                <li>
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--color-gold)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  <div>
                    <strong>Custom Mould Engineering:</strong>
                    <span>In-house 3D prototyping, bespoke tooling, and short lead times.</span>
                  </div>
                </li>
                <li>
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--color-gold)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  <div>
                    <strong>Sustainable PCR & rPET:</strong>
                    <span>Low 20,000 MOQ for recycled polymers with stock ready for dispatch.</span>
                  </div>
                </li>
              </ul>
              <button class="frapak-btn-gold quality-cta-btn" onclick="window.openInquiry(null, true)">
                REQUEST TECHNICAL SAMPLE KIT
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 6. Digital Tool: Interactive Component Matcher Callout (Mobile Stacked Layout) -->
    <section class="frapak-section frapak-matcher-section">
      <div class="frapak-container">
        <div class="frapak-matcher-card">
          <div class="frapak-matcher-left">
            <div class="frapak-matcher-eyebrow">Digital Packaging Tool</div>
            <h3 class="frapak-matcher-title">Interactive Component Matching Tool</h3>
            <p class="frapak-matcher-desc">
              Cross-match bottles, jars, pumps, and closures in real-time. Verify neck finish compatibility across 18mm, 20mm, 24mm, 28mm, and 38mm formats with 1,000+ leak-tested configurations.
            </p>
            <button class="frapak-btn-gold frapak-matcher-btn" onclick="window.openMatcherTool()">
              LAUNCH COMPONENT MATCHER
            </button>
          </div>
          
          <div class="frapak-matcher-right">
            <div class="matcher-spec-box">
              <div class="matcher-spec-header">Instant Compatibility Verification</div>
              <ul class="matcher-spec-list">
                <li>
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--color-gold)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  <span><strong>Container Selection:</strong> Boston, Dome, Flat, Brute & Jars</span>
                </li>
                <li>
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--color-gold)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  <span><strong>Neck Finishes:</strong> 15mm – 38mm PCO & ROPP</span>
                </li>
                <li>
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--color-gold)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  <span><strong>Closures:</strong> Pumps, Sprayers, Triggers, CRC, Disc Tops</span>
                </li>
                <li>
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--color-gold)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  <span><strong>Leak Assurance:</strong> 1,000+ cycle verified seals</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

  </section>
