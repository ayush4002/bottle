<?php
// Single Blog Post View
$author = $post['author_name'] ?? 'TrueNorth Technical Division';
$role = $post['author_role'] ?? 'Senior Packaging Engineer';
$publishedDate = !empty($post['published_at']) ? date('F d, Y', strtotime($post['published_at'])) : '';
$postTags = !empty($post['tags']) ? array_filter(array_map('trim', explode(',', $post['tags']))) : [];
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>

<div class="tn-blog-detail-page">
  
  <!-- BREADCRUMBS -->
  <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 14px 0;">
    <div class="tn-container">
      <nav aria-label="Breadcrumb" style="font-size: 0.84rem; color: #64748b; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
        <a href="/" style="color: #64748b; text-decoration: none;">Home</a>
        <span>/</span>
        <a href="/blog" style="color: #64748b; text-decoration: none;">Blog &amp; Insights</a>
        <span>/</span>
        <a href="/blog?category=<?= urlencode(strtolower(str_replace(' ', '-', $post['category'] ?? ''))) ?>" style="color: #64748b; text-decoration: none;"><?= htmlspecialchars($post['category'] ?? '') ?></a>
        <span>/</span>
        <span style="color: #1e293b; font-weight: 600;"><?= htmlspecialchars(mb_strimwidth($post['title'], 0, 40, '...')) ?></span>
      </nav>
    </div>
  </div>

  <!-- ARTICLE HEADER -->
  <article class="tn-section" style="padding: 48px 0 70px;">
    <div class="tn-container">
      <div style="max-width: 860px; margin: 0 auto;">
        
        <!-- META HEADER -->
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
          <a href="/blog?category=<?= urlencode(strtolower(str_replace(' ', '-', $post['category'] ?? ''))) ?>" style="text-decoration: none; background: rgba(184, 137, 46, 0.12); color: #b8892e; border: 1px solid rgba(184, 137, 46, 0.3); padding: 5px 12px; border-radius: 4px; font-size: 0.76rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
            <?= htmlspecialchars($post['category']) ?>
          </a>
          <span style="font-size: 0.84rem; color: #64748b; display: inline-flex; align-items: center; gap: 4px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <?= htmlspecialchars($post['read_time']) ?>
          </span>
          <span style="color: #cbd5e1;">&bull;</span>
          <span style="font-size: 0.84rem; color: #64748b;">
            Published: <?= $publishedDate ?>
          </span>
        </div>

        <!-- TITLE -->
        <h1 style="font-size: clamp(1.8rem, 3.2vw, 2.7rem); font-weight: 800; color: #16273f; line-height: 1.25; margin: 0 0 20px; letter-spacing: -0.015em;">
          <?= htmlspecialchars($post['title']) ?>
        </h1>

        <!-- EXCERPT LEAD -->
        <?php if (!empty($post['excerpt'])): ?>
          <p style="font-size: 1.12rem; color: #475569; line-height: 1.6; margin: 0 0 28px; font-weight: 500; border-left: 3px solid #b8892e; padding-left: 16px;">
            <?= htmlspecialchars($post['excerpt']) ?>
          </p>
        <?php endif; ?>

        <!-- AUTHOR BAR -->
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 16px 0; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 44px; height: 44px; border-radius: 50%; background: #16273f; color: #d4a754; font-weight: 800; font-size: 1rem; display: flex; align-items: center; justify-content: center; border: 2px solid #b8892e;">
              TN
            </div>
            <div>
              <div style="font-weight: 700; color: #16273f; font-size: 0.94rem;"><?= htmlspecialchars($author) ?></div>
              <div style="font-size: 0.8rem; color: #64748b;"><?= htmlspecialchars($role) ?></div>
            </div>
          </div>

          <!-- Social Share Buttons -->
          <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 0.8rem; color: #64748b; font-weight: 600;">Share:</span>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($currentUrl) ?>" target="_blank" rel="noopener" title="Share on LinkedIn" style="width: 34px; height: 34px; border-radius: 6px; background: #f1f5f9; color: #0a66c2; display: flex; align-items: center; justify-content: center; text-decoration: none; border: 1px solid #e2e8f0;">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
            </a>
            <a href="https://twitter.com/intent/tweet?text=<?= urlencode($post['title']) ?>&url=<?= urlencode($currentUrl) ?>" target="_blank" rel="noopener" title="Share on X" style="width: 34px; height: 34px; border-radius: 6px; background: #f1f5f9; color: #000; display: flex; align-items: center; justify-content: center; text-decoration: none; border: 1px solid #e2e8f0;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </a>
            <a href="https://api.whatsapp.com/send?text=<?= urlencode($post['title'] . ' ' . $currentUrl) ?>" target="_blank" rel="noopener" title="Share on WhatsApp" style="width: 34px; height: 34px; border-radius: 6px; background: #f1f5f9; color: #25d366; display: flex; align-items: center; justify-content: center; text-decoration: none; border: 1px solid #e2e8f0;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </a>
          </div>
        </div>

        <!-- FEATURED HERO IMAGE -->
        <?php if (!empty($post['featured_image'])): ?>
          <div style="margin-bottom: 36px; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 8px 24px rgba(0,0,0,0.06);">
            <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" style="width: 100%; max-height: 460px; object-fit: cover; display: block;" onerror="this.src='/logo_svg.svg'" />
          </div>
        <?php endif; ?>

        <!-- BODY ARTICLE CONTENT -->
        <div class="tn-blog-article-body" style="font-size: 1.05rem; line-height: 1.75; color: #334155;">
          <?= $post['content'] ?>
        </div>

        <!-- TAGS -->
        <?php if (!empty($postTags)): ?>
          <div style="margin-top: 40px; padding-top: 24px; border-top: 1px solid #e2e8f0; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-size: 0.82rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Tags:</span>
            <?php foreach ($postTags as $tag): ?>
              <span style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600;">
                #<?= htmlspecialchars($tag) ?>
              </span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- AUTHOR BIO BOX -->
        <div style="margin-top: 44px; padding: 28px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
          <div style="width: 56px; height: 56px; border-radius: 50%; background: #16273f; color: #d4a754; font-weight: 800; font-size: 1.3rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 2px solid #b8892e;">
            TN
          </div>
          <div style="flex: 1 1 300px;">
            <div style="font-weight: 800; color: #16273f; font-size: 1.05rem; margin-bottom: 4px;"><?= htmlspecialchars($author) ?></div>
            <div style="font-size: 0.82rem; color: #b8892e; font-weight: 700; margin-bottom: 8px; text-transform: uppercase;"><?= htmlspecialchars($role) ?> &bull; TrueNorth Packaging Group</div>
            <p style="font-size: 0.88rem; color: #64748b; line-height: 1.55; margin: 0;">
              TrueNorth Group is an ISO 9001:2015 certified primary packaging manufacturer operating Class 10,000 cleanrooms. We engineer precision PET bottles, jars, closures, and custom tooling serving pharmaceutical, cosmetic, and personal care brands globally.
            </p>
          </div>
        </div>

        <!-- DIRECT RFQ IN-ARTICLE CTA BANNER -->
        <div style="margin-top: 44px; padding: 32px; background: linear-gradient(135deg, #0e1b2c 0%, #16273f 55%, #1e3a5f 100%); border-left: 5px solid #b8892e; border-radius: 12px; color: #fff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
          <div style="flex: 1 1 450px;">
            <span style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.1em; color: #d4a754; text-transform: uppercase;">Direct Factory Packaging &amp; Sampling</span>
            <h3 style="font-size: 1.35rem; font-weight: 800; color: #fff; margin: 6px 0 8px;">Need Samples or Itemized Technical Quotations?</h3>
            <p style="color: #cbd5e1; font-size: 0.9rem; line-height: 1.5; margin: 0;">
              Speak directly with our packaging engineers. We provide technical 2D/3D CAD drawings, material safety data sheets (MSDS), and physical sample batches.
            </p>
          </div>
          <div>
            <button type="button" class="tn-btn-gold" onclick="window.openInquiry('Blog Post RFQ: <?= addslashes($post['title']) ?>')" style="padding: 12px 24px; border: none; border-radius: 6px; font-weight: 700; font-size: 0.88rem; cursor: pointer; white-space: nowrap;">
              REQUEST A QUOTE &amp; SAMPLES
            </button>
          </div>
        </div>

      </div>
    </div>
  </article>

  <!-- RELATED ARTICLES SECTION -->
  <?php if (!empty($recentPosts)): ?>
    <section class="tn-section-grey" style="padding: 50px 0 60px; border-top: 1px solid #e2e8f0;">
      <div class="tn-container">
        <div style="max-width: 860px; margin: 0 auto;">
          <h3 style="font-size: 1.4rem; font-weight: 800; color: #16273f; margin: 0 0 24px;">Recommended Technical Guides</h3>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
            <?php foreach ($recentPosts as $rp): ?>
              <a href="/blog/<?= htmlspecialchars($rp['slug']) ?>" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; text-decoration: none; display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.06)';" onmouseout="this.style.transform='none'; this.style.boxShadow='none';">
                <img src="<?= htmlspecialchars($rp['featured_image']) ?>" alt="<?= htmlspecialchars($rp['title']) ?>" style="width: 100%; height: 140px; object-fit: cover;" onerror="this.src='/logo_svg.svg'" />
                <div style="padding: 16px; flex: 1; display: flex; flex-direction: column;">
                  <span style="font-size: 0.7rem; font-weight: 700; color: #b8892e; text-transform: uppercase; margin-bottom: 6px;"><?= htmlspecialchars($rp['category']) ?></span>
                  <h4 style="font-size: 0.96rem; font-weight: 700; color: #16273f; line-height: 1.35; margin: 0 0 8px; flex: 1;">
                    <?= htmlspecialchars($rp['title']) ?>
                  </h4>
                  <span style="font-size: 0.78rem; color: #64748b;"><?= htmlspecialchars($rp['read_time']) ?></span>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>

</div>

<style>
/* Blog Article Typography Enhancement */
.tn-blog-article-body h2 {
  font-size: 1.55rem;
  font-weight: 800;
  color: #16273f;
  margin: 34px 0 14px;
  line-height: 1.3;
}
.tn-blog-article-body h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin: 28px 0 12px;
  line-height: 1.35;
}
.tn-blog-article-body p {
  margin-bottom: 20px;
}
.tn-blog-article-body ul, .tn-blog-article-body ol {
  margin: 0 0 24px 20px;
  padding-left: 10px;
}
.tn-blog-article-body li {
  margin-bottom: 8px;
}
.blog-callout-box {
  background: #f8fafc;
  border-left: 4px solid #b8892e;
  padding: 20px 24px;
  border-radius: 0 8px 8px 0;
  margin: 28px 0;
}
.blog-callout-box h4 {
  margin: 0 0 10px 0;
  color: #16273f;
  font-size: 1.05rem;
  font-weight: 700;
}
.blog-callout-box p:last-child, .blog-callout-box ul:last-child {
  margin-bottom: 0;
}
</style>

