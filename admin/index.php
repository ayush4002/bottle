<?php
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/ProductModel.php';

$allProducts = ProductModel::getSkus();
$customProducts = ProductModel::getCustomProducts();
$categories = ProductModel::getCategories();

$totalProductsCount = count($allProducts);
$customProductsCount = count($customProducts);
$categoriesCount = count($categories);

$publishedCount = 0;
$draftCount = 0;
$outOfStockCount = 0;
$featuredCount = 0;

foreach ($allProducts as $p) {
    $status = $p['status'] ?? 'published';
    $stockStatus = $p['stock_status'] ?? (($p['specs']['stock_qty'] ?? 10) <= 0 ? 'out_of_stock' : 'in_stock');
    
    if ($status === 'published') $publishedCount++;
    if ($status === 'draft') $draftCount++;
    if ($stockStatus === 'out_of_stock') $outOfStockCount++;
    if (!empty($p['is_featured'])) $featuredCount++;
}

// Inquiries / Quotes Count
$quoteCount = 0;
if (class_exists('Database')) {
    $db = Database::getInstance();
    if ($db && $db->isConnected()) {
        try {
            $pdo = $db->getConnection();
            $stmt = $pdo->query("SELECT COUNT(*) FROM quote_requests");
            $quoteCount = (int)$stmt->fetchColumn();
        } catch (Exception $e) {}
    }
}
?>

<style>
  .dash-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
  }
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 36px;
  }
  .stat-card {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 12px;
    padding: 20px 24px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  }
  .stat-card-title {
    font-size: 0.8rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 700;
    margin-bottom: 6px;
  }
  .stat-card-value {
    font-size: 2.2rem;
    font-weight: 800;
    color: #38bdf8;
    line-height: 1;
  }
  .stat-card-desc {
    font-size: 0.78rem;
    color: #64748b;
    margin-top: 6px;
  }
  .section-card {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 12px;
    padding: 24px;
  }
  .table-admin {
    width: 100%;
    border-collapse: collapse;
    margin-top: 14px;
  }
  .table-admin th {
    text-align: left;
    padding: 10px 12px;
    font-size: 0.78rem;
    color: #94a3b8;
    text-transform: uppercase;
    border-bottom: 1px solid #334155;
  }
  .table-admin td {
    padding: 12px;
    font-size: 0.88rem;
    border-bottom: 1px solid rgba(51, 65, 85, 0.5);
    color: #e2e8f0;
  }
  .product-thumb {
    width: 44px;
    height: 44px;
    object-fit: contain;
    background: #0f172a;
    border-radius: 6px;
    padding: 4px;
  }
  .badge-custom {
    background: rgba(14, 165, 233, 0.15);
    color: #38bdf8;
    border: 1px solid rgba(14, 165, 233, 0.3);
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 0.72rem;
    font-weight: 700;
  }
</style>

<div class="dash-header">
  <div>
    <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0;">CMS Business Dashboard</h1>
    <p style="color: #94a3b8; margin: 4px 0 0 0;">Catalogue overview, inventory statistics, and customer inquiries</p>
  </div>
  <a href="/admin/add_product.php" class="frapak-btn-gold" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: 700;">
    + Upload New Product
  </a>
</div>

<!-- METRICS CARDS -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-card-title">Total Products</div>
    <div class="stat-card-value"><?= number_format($totalProductsCount) ?></div>
    <div class="stat-card-desc">Active SKUs in system</div>
  </div>

  <div class="stat-card">
    <div class="stat-card-title">Published Live</div>
    <div class="stat-card-value" style="color: #34d399;"><?= number_format($publishedCount) ?></div>
    <div class="stat-card-desc">Visible on public site</div>
  </div>

  <div class="stat-card">
    <div class="stat-card-title">Drafts</div>
    <div class="stat-card-value" style="color: #f59e0b;"><?= number_format($draftCount) ?></div>
    <div class="stat-card-desc">Hidden from public</div>
  </div>

  <div class="stat-card">
    <div class="stat-card-title">Out of Stock</div>
    <div class="stat-card-value" style="color: #ef4444;"><?= number_format($outOfStockCount) ?></div>
    <div class="stat-card-desc">Inventory = 0</div>
  </div>

  <div class="stat-card">
    <div class="stat-card-title">Featured Products</div>
    <div class="stat-card-value" style="color: #a78bfa;"><?= number_format($featuredCount) ?></div>
    <div class="stat-card-desc">Showcased on homepage</div>
  </div>

  <div class="stat-card">
    <div class="stat-card-title">Quote Inquiries</div>
    <div class="stat-card-value" style="color: #38bdf8;"><?= number_format($quoteCount) ?></div>
    <div class="stat-card-desc">Customer submissions</div>
  </div>
</div>

<!-- CLIENT UPLOADED PRODUCTS -->
<div class="section-card">
  <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
    <h3 style="margin: 0; font-size: 1.1rem;">Client Uploaded Custom Products</h3>
    <a href="/admin/products.php" style="color: #38bdf8; text-decoration: none; font-size: 0.85rem; font-weight: 600;">View All Products &rarr;</a>
  </div>

  <?php if (empty($customProducts)): ?>
    <div style="text-align: center; padding: 36px 20px; color: #64748b;">
      <p style="margin: 0 0 12px 0;">No custom products uploaded yet.</p>
      <a href="/admin/add_product.php" style="color: #38bdf8; font-weight: 600; text-decoration: none;">Upload your first packaging product &rarr;</a>
    </div>
  <?php else: ?>
    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
      <table class="table-admin">
        <thead>
          <tr>
            <th>Image</th>
            <th>Title / Name</th>
            <th>Article No.</th>
            <th>Category</th>
            <th>Upload Date</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (array_slice($customProducts, 0, 8) as $p): ?>
            <tr>
              <td>
                <img src="<?= htmlspecialchars($p['image'] ?? '/logo_svg.svg') ?>" class="product-thumb" alt="Thumb" />
              </td>
              <td><strong><?= htmlspecialchars($p['name'] ?? 'Product') ?></strong></td>
              <td><code><?= htmlspecialchars($p['articleNo'] ?? 'TN-001') ?></code></td>
              <td><?= htmlspecialchars($p['categoryName'] ?? 'General') ?></td>
              <td><?= htmlspecialchars(date('M d, Y', strtotime($p['createdAt'] ?? 'now'))) ?></td>
              <td><span class="badge-custom">Live Custom</span></td>
              <td>
                <a href="/admin/delete_product.php?id=<?= urlencode($p['id']) ?>" onclick="return confirm('Are you sure you want to delete this product?');" style="color: #ef4444; font-size: 0.85rem; text-decoration: none; font-weight: 600;">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
