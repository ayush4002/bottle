<?php
// Public Blog Listing View
$categorySlug = $_GET['category'] ?? 'all';
$searchQuery = trim($_GET['q'] ?? '');
?>

<div class="tn-blog-page">
  
  <!-- HERO BANNER -->
  <section class="tn-hero tn-hero-sm" style="background: linear-gradient(135deg, #0e1b2c 0%, #16273f 60%, #1e3a5f 100%); padding: 60px 0 50px; border-bottom: 1px solid rgba(184, 137, 46, 0.25);">
    <div class="tn-container">
      <div style="max-width: 820px; margin: 0 auto; text-align: center;">
        <span class="tn-badge-gold" style="display: inline-block; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: #d4a754; background: rgba(184, 137, 46, 0.15); border: 1px solid rgba(184, 137, 46, 0.35); padding: 5px 12px; border-radius: 4px; margin-bottom: 14px;">
          TECHNICAL WHITEPAPERS &amp; PACKAGING INTELLIGENCE
        </span>
        <h1 style="color: #ffffff; font-size: clamp(1.8rem, 3.5vw, 2.6rem); font-weight: 800; line-height: 1.25; margin: 0 0 14px; letter-spacing: -0.01em;">
          Packaging Insights &amp; Engineering Standards
        </h1>
        <p style="color: #cbd5e1; font-size: 1.02rem; line-height: 1.6; margin: 0 0 24px; max-width: 720px; margin-left: auto; margin-right: auto;">
          Authoritative technical guides on food-grade rPET sustainability, Class 10,000 cleanroom blow-moulding protocols, and precision dispensing closures engineered under TÜV NORD ISO 9001 certification.
        </p>

        <!-- Search Bar -->
        <form action="/blog" method="GET" style="max-width: 540px; margin: 0 auto; display: flex; gap: 8px;">
          <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search packaging articles, materials, or neck finishes..." style="flex: 1; padding: 12px 16px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.25); background: rgba(255,255,255,0.1); color: #fff; font-size: 0.92rem; outline: none; backdrop-filter: blur(4px);" />
          <button type="submit" class="tn-btn-gold" style="padding: 12px 22px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; white-space: nowrap;">
            Search
          </button>
        </form>
      </div>
    </div>
  </section>

  <!-- MAIN BLOG SECTION -->
  <section class="tn-section" style="padding: 48px 0 70px;">
    <div class="tn-container">
      
      <!-- CATEGORY FILTER PILLS -->
      <div style="display: flex; gap: 10px; overflow-x: auto; padding-bottom: 16px; margin-bottom: 36px; -webkit-overflow-scrolling: touch;">
        <a href="/blog" class="tn-category-chip <?= $categorySlug === 'all' ? 'active' : '' ?>" style="text-decoration: none; padding: 9px 18px; border-radius: 999px; font-size: 0.84rem; font-weight: 700; white-space: nowrap; transition: all 0.2s; <?= $categorySlug === 'all' ? 'background: #16273f; color: #fff; border: 1px solid #16273f;' : 'background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;' ?>">
          All Articles (<?= count(BlogModel::getAllPosts(true)) ?>)
        </a>
        <?php foreach ($categories as $cat): ?>
          <?php $isActive = ($categorySlug === $cat['slug']); ?>
          <a href="/blog?category=<?= htmlspecialchars($cat['slug']) ?>" class="tn-category-chip <?= $isActive ? 'active' : '' ?>" style="text-decoration: none; padding: 9px 18px; border-radius: 999px; font-size: 0.84rem; font-weight: 700; white-space: nowrap; transition: all 0.2s; <?= $isActive ? 'background: #16273f; color: #fff; border: 1px solid #16273f;' : 'background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;' ?>">
            <?= htmlspecialchars($cat['name']) ?> (<?= $cat['count'] ?>)
          </a>
        <?php endforeach; ?>
      </div>

      <?php if (!empty($searchQuery)): ?>
        <div style="margin-bottom: 24px; font-size: 0.95rem; color: #64748b;">
          Showing search results for "<strong><?= htmlspecialchars($searchQuery) ?></strong>" (<a href="/blog" style="color: #0284c7;">Clear search</a>):
        </div>
      <?php endif; ?>

      <?php if (empty($posts)): ?>
        <div style="text-align: center; padding: 60px 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 40px;">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" style="margin-bottom: 12px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <h3 style="color: #1e293b; font-size: 1.25rem; font-weight: 700; margin: 0 0 6px;">No articles found</h3>
          <p style="color: #64748b; font-size: 0.9rem; margin: 0 0 16px;">Try adjusting your search keywords or browsing all categories.</p>
          <a href="/blog" class="tn-btn-gold" style="display: inline-block; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 700;">View All Articles</a>
        </div>
      <?php else: ?>

        <!-- FEATURED HERO POST (FIRST ARTICLE IF NO SEARCH QUERY) -->
        <?php if (empty($searchQuery) && $categorySlug === 'all' && !empty($posts[0])): ?>
          <?php
            $featured = $posts[0];
            $otherPosts = array_slice($posts, 1);
          ?>
          <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px -8px rgba(15, 27, 44, 0.08); margin-bottom: 44px; display: grid; grid-template-columns: 1.1fr 1fr; align-items: center;">
            <a href="/blog/<?= htmlspecialchars($featured['slug']) ?>" style="display: block; overflow: hidden; height: 100%; min-height: 320px; text-decoration: none;">
              <img src="<?= htmlspecialchars($featured['featured_image']) ?>" alt="<?= htmlspecialchars($featured['title']) ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'" onerror="this.src='/logo_svg.svg'" />
            </a>
            <div style="padding: 36px 32px;">
              <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; flex-wrap: wrap;">
                <span style="background: rgba(184, 137, 46, 0.12); color: #b8892e; border: 1px solid rgba(184, 137, 46, 0.3); padding: 4px 10px; border-radius: 4px; font-size: 0.74rem; font-weight: 700; text-transform: uppercase;">
                  <?= htmlspecialchars($featured['category']) ?>
                </span>
                <span style="font-size: 0.8rem; color: #64748b;"><?= htmlspecialchars($featured['read_time']) ?></span>
                <span style="font-size: 0.8rem; color: #cbd5e1;">&bull;</span>
                <span style="font-size: 0.8rem; color: #64748b;"><?= date('M d, Y', strtotime($featured['published_at'])) ?></span>
              </div>
              <h2 style="font-size: clamp(1.3rem, 2.2vw, 1.7rem); font-weight: 800; color: #16273f; line-height: 1.3; margin: 0 0 12px;">
                <a href="/blog/<?= htmlspecialchars($featured['slug']) ?>" style="color: #16273f; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#b8892e'" onmouseout="this.style.color='#16273f'">
                  <?= htmlspecialchars($featured['title']) ?>
                </a>
              </h2>
              <p style="color: #475569; font-size: 0.94rem; line-height: 1.6; margin: 0 0 20px;">
                <?= htmlspecialchars($featured['excerpt']) ?>
              </p>
              <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                <div style="font-size: 0.82rem; color: #64748b;">
                  By <strong style="color: #1e293b;"><?= htmlspecialchars($featured['author_name']) ?></strong>
                </div>
                <a href="/blog/<?= htmlspecialchars($featured['slug']) ?>" class="tn-btn-gold" style="display: inline-flex; align-items: center; gap: 6px; text-decoration: none; padding: 10px 18px; border-radius: 6px; font-size: 0.84rem; font-weight: 700;">
                  Read Technical Guide &rarr;
                </a>
              </div>
            </div>
          </div>
        <?php else: ?>
          <?php $otherPosts = $posts; ?>
        <?php endif; ?>

        <!-- ARTICLES GRID -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 28px;">
          <?php foreach ($otherPosts as $post): ?>
            <article style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s ease, box-shadow 0.2s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.04);" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 25px -4px rgba(15,27,44,0.1)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.04)';">
              <a href="/blog/<?= htmlspecialchars($post['slug']) ?>" style="display: block; position: relative; height: 200px; overflow: hidden; background: #0f172a; text-decoration: none;">
                <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" onerror="this.src='/logo_svg.svg'" />
                <span style="position: absolute; top: 12px; left: 12px; background: rgba(14, 27, 44, 0.85); color: #d4a754; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; padding: 4px 8px; border-radius: 4px; backdrop-filter: blur(4px);">
                  <?= htmlspecialchars($post['category']) ?>
                </span>
              </a>
              <div style="padding: 22px 20px; display: flex; flex-direction: column; flex: 1;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.78rem; color: #64748b; margin-bottom: 8px;">
                  <span><?= htmlspecialchars($post['read_time']) ?></span>
                  <span>&bull;</span>
                  <span><?= date('M d, Y', strtotime($post['published_at'])) ?></span>
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 700; line-height: 1.35; margin: 0 0 10px;">
                  <a href="/blog/<?= htmlspecialchars($post['slug']) ?>" style="color: #16273f; text-decoration: none;" onmouseover="this.style.color='#b8892e'" onmouseout="this.style.color='#16273f'">
                    <?= htmlspecialchars($post['title']) ?>
                  </a>
                </h3>
                <p style="color: #475569; font-size: 0.88rem; line-height: 1.55; margin: 0 0 18px; flex: 1;">
                  <?= htmlspecialchars(mb_strimwidth($post['excerpt'], 0, 140, '...')) ?>
                </p>
                <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 14px; border-top: 1px solid #f1f5f9; font-size: 0.82rem;">
                  <span style="color: #64748b;"><?= htmlspecialchars($post['author_name']) ?></span>
                  <a href="/blog/<?= htmlspecialchars($post['slug']) ?>" style="color: #b8892e; font-weight: 700; text-decoration: none;">
                    Read Guide &rarr;
                  </a>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

      <?php endif; ?>

      <!-- BOTTOM WHITEPAPER & PACKAGING INQUIRY CTA BANNER -->
      <div style="margin-top: 60px; padding: 36px 40px; background: linear-gradient(135deg, #0e1b2c 0%, #16273f 55%, #1e3a5f 100%); border-left: 5px solid #b8892e; border-radius: 12px; color: #fff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 24px;">
        <div style="flex: 1 1 500px;">
          <span style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.1em; color: #d4a754; text-transform: uppercase;">CUSTOM PACKAGING ENGINEERING</span>
          <h3 style="font-size: 1.4rem; font-weight: 800; color: #fff; margin: 6px 0 10px;">Require Custom Polymer Specifications or Cleanroom Production?</h3>
          <p style="color: #cbd5e1; font-size: 0.92rem; line-height: 1.55; margin: 0;">
            Our packaging engineering team assists international brands with custom mould development, barrier testing, and free physical sample batches dispatched within 24 business hours.
          </p>
        </div>
        <div>
          <button type="button" class="tn-btn-gold" onclick="window.openInquiry('Blog Technical Inquiry')" style="padding: 13px 24px; border: none; border-radius: 6px; font-weight: 700; font-size: 0.88rem; cursor: pointer; white-space: nowrap;">
            REQUEST SAMPLES &amp; RFQ
          </button>
        </div>
      </div>

    </div>
  </section>

</div>
