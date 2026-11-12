  <!-- =======================================================================
       VIEW 1: HOME PAGE
       ======================================================================= -->
  <div id="main-content" tabindex="-1" aria-hidden="true" style="position:absolute; left:0; top:0; width:0; height:0;"></div>
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

        <!-- Executive Primary Packaging Overview Banner (Before Catalogue Heading) -->
        <div class="home-catalogue-pre-banner">
          <div class="home-pre-banner-content">
            <span class="home-pre-banner-tag">PRECISION MOULDING &amp; DISPENSING SYSTEMS</span>
            <h2 class="home-pre-banner-title">Factory-Direct Primary Packaging Across 8 Certified Product Categories</h2>
            <p class="home-pre-banner-desc">
              From 10ml mini-droppers to 1,000ml bulk containers, TrueNorth delivers high-precision PET bottles, HDPE containers, cosmetic jars, and fine-mist dispensing systems engineered under strict TÜV NORD ISO 9001 protocols.
            </p>
            <div class="home-pre-banner-specs">
              <span class="home-pre-banner-spec">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#b8892e" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                10ml – 1,000ml Spectrum
              </span>
              <span class="home-pre-banner-spec">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#b8892e" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Class 10,000 Cleanroom
              </span>
              <span class="home-pre-banner-spec">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#b8892e" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                18/410 to 28/410 Neck Finishes
              </span>
              <span class="home-pre-banner-spec">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#b8892e" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                290+ Active Mould Designs
              </span>
            </div>
          </div>
          <div class="home-pre-banner-actions">
            <button type="button" class="tn-btn-gold home-pre-banner-btn" onclick="document.getElementById('home-cat-tiles-grid').scrollIntoView({behavior: 'smooth'})">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 6px;"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
              BROWSE CATALOGUE
            </button>
            <button type="button" class="home-pre-banner-secondary-btn" onclick="window.openInquiry('Pre-Catalogue Custom RFQ')">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 5px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              Request Custom RFQ
            </button>
          </div>
        </div>

        <div class="tn-section-title-wrap">
          <h2 class="tn-section-title">Choose from our catalogue of primary packaging products</h2>
        </div>

        <div class="tn-cat-tiles-grid" id="home-cat-tiles-grid">
          <?php foreach ($categories as $cat): ?>
            <a href="/products?category=<?= urlencode($cat['slug']) ?>" class="tn-tile-card" style="text-decoration: none;">
              <div class="tn-tile-image">
                <img src="<?= htmlspecialchars($cat['image'] ?? '/logo_svg.svg') ?>" alt="<?= htmlspecialchars($cat['name']) ?>" loading="lazy" width="280" height="280" />
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

        <?php $homeIndustries = class_exists('ProductModel') ? ProductModel::getIndustries(true) : []; ?>
        <div class="tn-markets-grid" id="home-markets-grid">
          <?php foreach ($homeIndustries as $indSlug => $ind): ?>
            <a href="/products?industry=<?= urlencode($ind['slug'] ?? $indSlug) ?>" class="tn-market-tile" style="text-decoration: none;">
              <div class="tn-market-img">
                <img src="<?= htmlspecialchars($ind['image'] ?? '/logo_svg.svg') ?>" alt="<?= htmlspecialchars($ind['name']) ?>" loading="lazy" width="300" height="200" onerror="this.onerror=null;this.src='/logo_svg.svg';" />
              </div>
              <div class="tn-tile-bar"><span><?= htmlspecialchars($ind['name']) ?></span></div>
            </a>
          <?php endforeach; ?>
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



    <!-- 3b. Social Proof: Client Testimonials & Logo Wall (Checklist §Trust / §12) -->
    <?php
    if (!class_exists('TestimonialModel')) {
        require_once APP_PATH . '/Models/TestimonialModel.php';
    }
    $tnTestimonials = TestimonialModel::getTestimonials(3);
    $tnClientLogos = TestimonialModel::getClientLogos(8);
    if (!empty($tnTestimonials) || !empty($tnClientLogos)):
    ?>
    <section class="tn-section-grey" style="padding: 48px 0;">
      <div class="tn-container">
        <div class="tn-section-title-wrap" style="text-align: center; margin-bottom: 24px;">
          <span class="home-pre-banner-tag" style="display: inline-block; margin-bottom: 8px;">TRUSTED BY FORMULATORS &amp; BRANDS</span>
          <h2 class="tn-section-title">What Our Clients Say</h2>
        </div>

        <?php if (!empty($tnClientLogos)): ?>
          <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 20px 32px; margin-bottom: 36px;">
            <?php foreach ($tnClientLogos as $tnLogo): ?>
              <div style="display: flex; align-items: center; gap: 8px; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 16px;">
                <img src="<?= htmlspecialchars($tnLogo['logo_image']) ?>" alt="<?= htmlspecialchars($tnLogo['company']) ?>"<?= tn_img_dims($tnLogo['logo_image']) ?> loading="lazy" decoding="async" style="width: 40px; height: 40px; object-fit: contain; display: block;" onerror="this.style.display='none'" />
                <span style="font-weight: 700; color: #475569; font-size: 0.9rem;"><?= htmlspecialchars($tnLogo['company']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($tnTestimonials)): ?>
          <div class="tn-grid-3" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 260px), 1fr)); gap: 20px;">
            <?php foreach ($tnTestimonials as $tnT): ?>
              <figure style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; margin: 0; display: flex; flex-direction: column; gap: 12px;">
                <?php if (!empty($tnT['rating'])): ?>
                  <div style="color: #8A6516; font-size: 0.9rem; letter-spacing: 1px;" aria-label="Rated <?= (int)$tnT['rating'] ?> out of 5">
                    <?= str_repeat('★', (int)$tnT['rating']) . str_repeat('☆', 5 - (int)$tnT['rating']) ?>
                  </div>
                <?php endif; ?>
                <blockquote style="margin: 0; color: #334155; font-size: 0.92rem; line-height: 1.6;">
                  &ldquo;<?= htmlspecialchars($tnT['quote']) ?>&rdquo;
                </blockquote>
                <figcaption style="border-top: 1px solid #e2e8f0; padding-top: 12px;">
                  <div style="font-weight: 800; color: #16273f; font-size: 0.92rem;"><?= htmlspecialchars($tnT['client_name'] ?: 'Client') ?></div>
                  <div style="color: #64748b; font-size: 0.8rem; margin-top: 2px;"><?= htmlspecialchars(trim(($tnT['company'] ?? '') . ($tnT['role'] ? ' · ' . $tnT['role'] : ''))) ?></div>
                </figcaption>
              </figure>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
    <?php endif; ?>

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
                <img src="/vikaas_inputs/thumbnails/thumbnail_pet_bottles.webp" alt="rPET bottles" loading="lazy" width="260" height="260" />
              </div>
              <div class="rpet-card-bar">rPET bottles</div>
            </a>
            <a href="/products?category=jars" class="rpet-card" style="text-decoration: none;">
              <div class="tn-tile-image">
                <img src="/vikaas_inputs/thumbnails/thumbnail_nutraceuticle_jars.webp" alt="rPET jars" loading="lazy" width="260" height="260" />
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

    <!-- 5. Latest Technical Guides (Blog) + Buying FAQs (Checklist: featured posts + FAQ on home) -->
    <?php
    if (!class_exists('BlogModel')) {
        require_once APP_PATH . '/Models/BlogModel.php';
    }
    $tnHomePosts = BlogModel::getRecentPosts(3);
    $tnHomeFaqs = SeoModel::getFaqs('page', 'faq');
    ?>
    <?php if (!empty($tnHomePosts)): ?>
    <section class="tn-section">
      <div class="tn-container">
        <div class="tn-section-title-wrap">
          <span class="home-pre-banner-tag">TECHNICAL GUIDES</span>
          <h2 class="tn-section-title">Latest From The Packaging Blog</h2>
        </div>
        <div class="tn-grid-3" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 260px), 1fr)); gap: 24px;">
          <?php foreach ($tnHomePosts as $tnP): ?>
            <a href="/blog/<?= htmlspecialchars($tnP['slug']) ?>" class="home-blog-card" style="display: block; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff; text-decoration: none; transition: box-shadow 0.25s ease;">
              <?php if (!empty($tnP['featured_image'])): ?>
                <div style="overflow: hidden; aspect-ratio: 16 / 9; background: #f1f5f9;">
                  <img src="<?= htmlspecialchars($tnP['featured_image']) ?>" alt="<?= htmlspecialchars($tnP['title']) ?>"<?= tn_img_dims($tnP['featured_image']) ?> loading="lazy" decoding="async" style="width: 100%; height: 100%; object-fit: cover; display: block;" onerror="this.onerror=null;this.style.display='none';" />
                </div>
              <?php endif; ?>
              <div style="padding: 18px 18px 20px;">
                <?php if (!empty($tnP['category'])): ?><span style="font-size: 0.7rem; font-weight: 800; letter-spacing: 0.06em; color: #8A6516; text-transform: uppercase;"><?= htmlspecialchars($tnP['category']) ?></span><?php endif; ?>
                <h3 style="font-size: 1.05rem; font-weight: 800; color: #16273f; margin: 8px 0 6px; line-height: 1.35;"><?= htmlspecialchars($tnP['title']) ?></h3>
                <p style="color: #475569; font-size: 0.87rem; line-height: 1.55; margin: 0 0 10px;"><?= htmlspecialchars(mb_strimwidth($tnP['excerpt'] ?? strip_tags($tnP['content'] ?? ''), 0, 140, '…')) ?></p>
                <span style="font-size: 0.78rem; font-weight: 700; color: #16273f;">Read Guide →</span>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
        <div style="text-align: center; margin-top: 26px;">
          <a href="/blog" class="tn-btn-outline tn-btn-outline-dark">VIEW ALL GUIDES</a>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($tnHomeFaqs)): ?>
    <section class="tn-section tn-section-grey" id="home-faq-section">
      <div class="tn-container" style="max-width: 900px;">
        <div class="tn-section-title-wrap">
          <span class="home-pre-banner-tag">BUYER GUIDANCE</span>
          <h2 class="tn-section-title">Frequently Asked Questions</h2>
          <p style="color: #475569; max-width: 560px; margin: 0 auto;">Quick answers on minimum orders, samples, custom moulding and rPET.</p>
        </div>
        <div style="margin-top: 22px;">
          <?php foreach (array_slice($tnHomeFaqs, 0, 4) as $tnF): ?>
            <details class="tn-faq-item" style="border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 12px; background: #fff; overflow: hidden;">
              <summary style="cursor: pointer; padding: 16px 18px; font-weight: 700; color: #16273f; font-size: 0.98rem; list-style: none; display: flex; justify-content: space-between; gap: 10px;">
                <span><?= htmlspecialchars($tnF['question']) ?></span>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#b8892e" stroke-width="2.5" style="flex-shrink:0;"><polyline points="6 9 12 15 18 9"/></svg>
              </summary>
              <div style="padding: 0 18px 16px; color: #475569; line-height: 1.6; font-size: 0.9rem;"><?= nl2br(htmlspecialchars($tnF['answer'])) ?></div>
            </details>
          <?php endforeach; ?>
        </div>
        <div style="text-align: center; margin-top: 22px;">
          <a href="/faq" class="tn-btn-gold" style="text-decoration:none;">SEE ALL QUESTIONS</a>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <!-- Final Homepage Direct Factory Wholesale CTA Banner (At the Last of Home Page) -->
    <section class="tn-section-grey tn-home-final-cta-section" style="padding: 48px 0; border-top: 1px solid #e2e8f0;">
      <div class="tn-container">
        <div class="home-enquiry-banner" style="margin: 0;">
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
            <button type="button" class="tn-btn-gold home-enquiry-btn" onclick="window.openInquiry('Homepage Bottom Wholesale RFQ &amp; Samples')">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 6px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              REQUEST A QUOTE &amp; SAMPLES
            </button>
            <a href="tel:<?= htmlspecialchars($company['phone_tel'] ?? '+919810220761') ?>" class="home-enquiry-phone-link">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
              Direct Sales: <?= htmlspecialchars($company['phone'] ?? '+91 98102 20761') ?>
            </a>
          </div>
        </div>
      </div>
    </section>

  </section>
