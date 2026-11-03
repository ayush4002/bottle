<?php
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/BlogModel.php';

checkAdminAuth();

BlogModel::initStorage();

$categoryFilter = $_GET['category'] ?? 'all';
$statusFilter = $_GET['status'] ?? 'all';
$searchQuery = trim($_GET['q'] ?? '');

$allPosts = BlogModel::getAllPosts(false, $categoryFilter === 'all' ? null : $categoryFilter, null, $searchQuery);

// Filter by status if requested
if ($statusFilter !== 'all') {
    $allPosts = array_filter($allPosts, function($p) use ($statusFilter) {
        return ($p['status'] ?? 'published') === $statusFilter;
    });
}

// Compute statistics
$totalPosts = count(BlogModel::getAllPosts(false));
$publishedPosts = count(BlogModel::getAllPosts(true));
$draftPosts = $totalPosts - $publishedPosts;
$totalViews = array_sum(array_column($allPosts, 'views_count'));

$categories = BlogModel::getCategories();
$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';
?>

<div style="max-width: 1200px; margin: 0 auto;">
  
  <!-- PAGE HEADER & ACTION -->
  <div class="dash-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
      <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0 0 6px 0; color: #f8fafc;">Blog &amp; Technical Articles</h1>
      <p style="color: #94a3b8; font-size: 0.9rem; margin: 0;">Publish high-ranking packaging industry guides, whitepapers, and technical case studies with enterprise SEO controls.</p>
    </div>
    <div style="display: flex; gap: 10px;">
      <a href="/blog" target="_blank" style="display: inline-flex; align-items: center; gap: 7px; background: #334155; color: #fff; text-decoration: none; padding: 12px 18px; border-radius: 8px; font-weight: 700; font-size: 0.88rem; transition: background 0.15s;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        View Public Blog &nearr;
      </a>
      <a href="/admin/blog_editor" class="tn-btn-gold" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; padding: 12px 22px; border-radius: 8px; font-weight: 700; font-size: 0.9rem; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35);">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Write New Article
      </a>
    </div>
  </div>

  <?php if (!empty($msg)): ?>
    <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      <span><?= htmlspecialchars($msg) ?></span>
    </div>
  <?php endif; ?>

  <?php if (!empty($err)): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <span><?= htmlspecialchars($err) ?></span>
    </div>
  <?php endif; ?>

  <!-- STATS CARDS -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
      <div style="font-size: 0.8rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Total Articles</div>
      <div style="font-size: 2rem; font-weight: 800; color: #f8fafc;"><?= $totalPosts ?></div>
    </div>
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
      <div style="font-size: 0.8rem; font-weight: 700; color: #34d399; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Published Live</div>
      <div style="font-size: 2rem; font-weight: 800; color: #34d399;"><?= $publishedPosts ?></div>
    </div>
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
      <div style="font-size: 0.8rem; font-weight: 700; color: #fbbf24; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Drafts</div>
      <div style="font-size: 2rem; font-weight: 800; color: #fbbf24;"><?= $draftPosts ?></div>
    </div>
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px;">
      <div style="font-size: 0.8rem; font-weight: 700; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Total Reads / Views</div>
      <div style="font-size: 2rem; font-weight: 800; color: #38bdf8;"><?= number_format($totalViews) ?></div>
    </div>
  </div>

  <!-- SEARCH & FILTER BAR -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 18px 20px; margin-bottom: 24px;">
    <form method="GET" action="/admin/blog" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center;">
      <div style="flex: 1; min-width: 240px; position: relative;">
        <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search by title, keyword, or excerpt..." style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" />
      </div>
      <div style="min-width: 180px;">
        <select name="category" onchange="this.form.submit()" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;">
          <option value="all">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= $categoryFilter === $cat['slug'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['name']) ?> (<?= $cat['count'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="min-width: 150px;">
        <select name="status" onchange="this.form.submit()" style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;">
          <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Statuses</option>
          <option value="published" <?= $statusFilter === 'published' ? 'selected' : '' ?>>Published</option>
          <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
        </select>
      </div>
      <button type="submit" style="background: #0284c7; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.88rem;">
        Filter
      </button>
      <?php if (!empty($searchQuery) || $categoryFilter !== 'all' || $statusFilter !== 'all'): ?>
        <a href="/admin/blog" style="color: #94a3b8; font-size: 0.85rem; text-decoration: none; margin-left: 6px;">Reset</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- ARTICLES TABLE -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
        <thead>
          <tr style="background: #0f172a; border-bottom: 1px solid #334155; color: #94a3b8; font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.06em;">
            <th style="padding: 14px 18px; width: 64px;">Image</th>
            <th style="padding: 14px 18px;">Article Title &amp; Details</th>
            <th style="padding: 14px 18px;">Category</th>
            <th style="padding: 14px 18px;">SEO Health</th>
            <th style="padding: 14px 18px;">Status</th>
            <th style="padding: 14px 18px;">Reads</th>
            <th style="padding: 14px 18px; text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($allPosts)): ?>
            <tr>
              <td colspan="7" style="padding: 48px; text-align: center; color: #94a3b8;">
                <p style="font-size: 1.1rem; margin: 0 0 10px 0;">No blog articles found matching your criteria.</p>
                <a href="/admin/blog_editor" class="tn-btn-gold" style="display: inline-block; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 700;">+ Write First Article</a>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($allPosts as $post): ?>
              <?php
                $pId = $post['id'] ?? 0;
                $pSlug = $post['slug'] ?? '';
                $pTitle = $post['title'] ?? 'Untitled Article';
                $pImg = $post['featured_image'] ?? '/vikaas_inputs/thumbnails/thumbnail_pet_bottles.webp';
                $pCat = $post['category'] ?? 'General';
                $pStatus = $post['status'] ?? 'published';
                $pViews = (int)($post['views_count'] ?? 0);
                $pDate = !empty($post['published_at']) ? date('M d, Y', strtotime($post['published_at'])) : '';
                $pRead = $post['read_time'] ?? '5 min';
                
                // SEO Health Assessment
                $mTitleLen = mb_strlen($post['meta_title'] ?? '');
                $mDescLen = mb_strlen($post['meta_description'] ?? '');
                $hasKeywords = !empty($post['meta_keywords']);
                $seoScore = 100;
                if ($mTitleLen < 30 || $mTitleLen > 65) $seoScore -= 25;
                if ($mDescLen < 70 || $mDescLen > 165) $seoScore -= 25;
                if (!$hasKeywords) $seoScore -= 20;
                if (empty($post['featured_image'])) $seoScore -= 15;

                $seoLabel = 'Optimal (90+)';
                $seoBadgeBg = 'rgba(52, 211, 153, 0.15)';
                $seoBadgeCol = '#6ee7b7';
                if ($seoScore < 60) {
                    $seoLabel = 'Needs Work';
                    $seoBadgeBg = 'rgba(239, 68, 68, 0.15)';
                    $seoBadgeCol = '#fca5a5';
                } elseif ($seoScore < 85) {
                    $seoLabel = 'Good (75+)';
                    $seoBadgeBg = 'rgba(251, 191, 36, 0.15)';
                    $seoBadgeCol = '#fcd34d';
                }
              ?>
              <tr style="border-bottom: 1px solid #334155; transition: background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                <td style="padding: 14px 18px; vertical-align: top;">
                  <img src="<?= htmlspecialchars($pImg) ?>" alt="<?= htmlspecialchars($pTitle) ?>" style="width: 52px; height: 52px; object-fit: cover; border-radius: 8px; border: 1px solid #475569; background: #0f172a;" onerror="this.src='/logo_svg.svg'" />
                </td>
                <td style="padding: 14px 18px; vertical-align: top;">
                  <div style="font-weight: 700; color: #f8fafc; font-size: 0.96rem; margin-bottom: 4px; line-height: 1.35;">
                    <a href="/admin/blog_editor?id=<?= $pId ?>" style="color: #f8fafc; text-decoration: none;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='#f8fafc'">
                      <?= htmlspecialchars($pTitle) ?>
                    </a>
                  </div>
                  <div style="font-size: 0.78rem; color: #94a3b8; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <span>/blog/<?= htmlspecialchars($pSlug) ?></span>
                    <span>&bull;</span>
                    <span><?= htmlspecialchars($pRead) ?></span>
                    <span>&bull;</span>
                    <span><?= $pDate ?></span>
                  </div>
                </td>
                <td style="padding: 14px 18px; vertical-align: top; white-space: nowrap;">
                  <span style="background: rgba(14, 165, 233, 0.12); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.3); padding: 4px 9px; border-radius: 6px; font-size: 0.76rem; font-weight: 600;">
                    <?= htmlspecialchars($pCat) ?>
                  </span>
                </td>
                <td style="padding: 14px 18px; vertical-align: top; white-space: nowrap;">
                  <span style="background: <?= $seoBadgeBg ?>; color: <?= $seoBadgeCol ?>; border: 1px solid <?= $seoBadgeCol ?>40; padding: 4px 9px; border-radius: 6px; font-size: 0.74rem; font-weight: 700;">
                    SEO <?= $seoLabel ?>
                  </span>
                </td>
                <td style="padding: 14px 18px; vertical-align: top; white-space: nowrap;">
                  <?php if ($pStatus === 'published'): ?>
                    <span style="background: rgba(52, 211, 153, 0.15); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.3); padding: 4px 8px; border-radius: 6px; font-size: 0.74rem; font-weight: 700; text-transform: uppercase;">
                      Live
                    </span>
                  <?php else: ?>
                    <span style="background: rgba(148, 163, 184, 0.15); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.3); padding: 4px 8px; border-radius: 6px; font-size: 0.74rem; font-weight: 700; text-transform: uppercase;">
                      Draft
                    </span>
                  <?php endif; ?>
                </td>
                <td style="padding: 14px 18px; vertical-align: top; font-weight: 600; color: #cbd5e1; white-space: nowrap;">
                  <?= number_format($pViews) ?>
                </td>
                <td style="padding: 14px 18px; vertical-align: top; text-align: right; white-space: nowrap;">
                  <div style="display: inline-flex; align-items: center; gap: 8px;">
                    <?php if ($pStatus === 'published'): ?>
                      <a href="/blog/<?= htmlspecialchars($pSlug) ?>" target="_blank" title="View live article on public website" style="color: #38bdf8; padding: 6px 10px; background: rgba(56, 189, 248, 0.1); border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: 600;">
                        Live &nearr;
                      </a>
                    <?php endif; ?>
                    <a href="/admin/blog_editor?id=<?= $pId ?>" title="Edit post and SEO metadata" style="color: #f8fafc; padding: 6px 12px; background: #334155; border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: 600;">
                      Edit
                    </a>
                    <a href="/admin/delete_blog?id=<?= $pId ?>&csrf_token=<?= $csrfToken ?>" onclick="return confirm('Are you sure you want to permanently delete this article: \'<?= addslashes($pTitle) ?>\'? This action cannot be undone.');" title="Delete article" style="color: #f87171; padding: 6px 10px; background: rgba(239, 68, 68, 0.1); border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: 600;">
                      Delete
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>

