<?php
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/ProductModel.php';

// Auth Check
checkAdminAuth();

// Query Filters
$search = trim($_GET['search'] ?? '');
$filterCategory = trim($_GET['category'] ?? '');
$filterStatus = trim($_GET['status'] ?? '');
$filterStock = trim($_GET['stock'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 50;

// Fetch All Products (including archived for admin view)
if (!empty($search)) {
    $allProducts = ProductModel::searchProducts($search, true);
} else {
    $allProducts = ProductModel::getSkus(true);
}

// Apply Category Filter
if (!empty($filterCategory)) {
    $allProducts = array_values(array_filter($allProducts, function($p) use ($filterCategory) {
        return ($p['categorySlug'] ?? '') === $filterCategory;
    }));
}

// Apply Status Filter
if (!empty($filterStatus)) {
    if ($filterStatus !== 'all') {
        $allProducts = array_values(array_filter($allProducts, function($p) use ($filterStatus) {
            $st = $p['status'] ?? 'published';
            return $st === $filterStatus;
        }));
    }
} else {
    // Default view: Show active products (published & draft), hide archived
    $allProducts = array_values(array_filter($allProducts, function($p) {
        $st = $p['status'] ?? 'published';
        return $st !== 'archived';
    }));
}

// Apply Stock Filter
if (!empty($filterStock)) {
    $allProducts = array_values(array_filter($allProducts, function($p) use ($filterStock) {
        $qty = (int)($p['stockQty'] ?? ($p['stock'] ?? 1000));
        if ($filterStock === 'in_stock') return $qty > 0;
        if ($filterStock === 'out_of_stock') return $qty <= 0;
        return true;
    }));
}

$totalProducts = count($allProducts);
$totalPages = max(1, ceil($totalProducts / $perPage));
if ($page > $totalPages) $page = $totalPages;

$offset = ($page - 1) * $perPage;
$pagedProducts = array_slice($allProducts, $offset, $perPage);

$categories = ProductModel::getCategories();
$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';
?>

<style>
  .dash-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
  }
  .filter-card {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 14px;
    padding: 18px 22px;
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 24px;
    flex-wrap: wrap;
  }
  .filter-input {
    background: #0f172a;
    border: 1px solid #475569;
    color: #ffffff;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 0.9rem;
    flex: 1;
    min-width: 220px;
  }
  .filter-select {
    background: #0f172a;
    border: 1px solid #475569;
    color: #ffffff;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 0.9rem;
  }
  .table-container {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 14px;
    padding: 20px;
    overflow-x: auto;
  }
  .table-admin {
    width: 100%;
    border-collapse: collapse;
  }
  .table-admin th {
    text-align: left;
    padding: 14px 12px;
    font-size: 0.78rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #334155;
  }
  .table-admin td {
    padding: 14px 12px;
    font-size: 0.88rem;
    border-bottom: 1px solid rgba(51, 65, 85, 0.4);
    color: #e2e8f0;
    vertical-align: middle;
  }
  .product-thumb {
    width: 48px;
    height: 48px;
    object-fit: contain;
    background: #0f172a;
    border-radius: 8px;
    padding: 4px;
    border: 1px solid #334155;
  }
  .badge-published {
    background: rgba(34, 197, 94, 0.15);
    color: #4ade80;
    border: 1px solid rgba(34, 197, 94, 0.3);
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 700;
  }
  .badge-draft {
    background: rgba(234, 179, 8, 0.15);
    color: #facc15;
    border: 1px solid rgba(234, 179, 8, 0.3);
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 700;
  }
  .badge-archived {
    background: rgba(239, 68, 68, 0.15);
    color: #fca5a5;
    border: 1px solid rgba(239, 68, 68, 0.3);
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 700;
  }
  .btn-action-edit {
    background: linear-gradient(135deg, #1e3a8a, #0284c7);
    color: #ffffff;
    padding: 7px 14px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.82rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: transform 0.15s, opacity 0.15s;
  }
  .btn-action-edit:hover {
    opacity: 0.9;
    transform: translateY(-1px);
  }
  .btn-action-delete {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.3);
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.82rem;
    transition: background 0.15s;
  }
  .btn-action-delete:hover {
    background: rgba(239, 68, 68, 0.25);
  }
  .pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid #334155;
    flex-wrap: wrap;
    gap: 12px;
  }
  .page-link {
    background: #0f172a;
    border: 1px solid #475569;
    color: #cbd5e1;
    padding: 8px 14px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 600;
  }
  .page-link.active {
    background: #0284c7;
    border-color: #38bdf8;
    color: #ffffff;
  }
  .alert-box-success {
    background: rgba(52, 211, 153, 0.15);
    border: 1px solid rgba(52, 211, 153, 0.4);
    color: #6ee7b7;
    padding: 14px 18px;
    border-radius: 8px;
    font-size: 0.9rem;
    margin-bottom: 20px;
  }
  .alert-box-error {
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.4);
    color: #fca5a5;
    padding: 14px 18px;
    border-radius: 8px;
    font-size: 0.9rem;
    margin-bottom: 20px;
  }
</style>

<div class="dash-header">
  <div>
    <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0; color: #f8fafc;">Product Catalogue Management</h1>
    <p style="color: #94a3b8; margin: 4px 0 0 0; font-size: 0.9rem;">
      Full CRUD Product Management — Showing <?= count($pagedProducts) ?> of <?= $totalProducts ?> live packaging SKUs
    </p>
  </div>
  <a href="/admin/add_product.php" class="frapak-btn-gold" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; padding: 12px 22px; border-radius: 8px; font-weight: 700; background: #eab308; color: #0f172a;">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Upload New Product
  </a>
</div>

<?php if (!empty($msg)): ?>
  <div class="alert-box-success"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<?php if (!empty($err)): ?>
  <div class="alert-box-error"><?= htmlspecialchars($err) ?></div>
<?php endif; ?>
<!-- SEARCH & MULTI-FILTER FORM -->
<form method="GET" action="/admin/products.php" class="filter-card">
  <input type="text" name="search" class="filter-input" placeholder="Search product name, SKU, or material..." value="<?= htmlspecialchars($search) ?>" />
  
  <select name="category" class="filter-select" onchange="this.form.submit()">
    <option value="">All Categories (12)</option>
    <?php foreach ($categories as $cat): ?>
      <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= $filterCategory === $cat['slug'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($cat['name']) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <select name="status" class="filter-select" onchange="this.form.submit()">
    <option value="" <?= $filterStatus === '' ? 'selected' : '' ?>>Active (Published &amp; Draft)</option>
    <option value="published" <?= $filterStatus === 'published' ? 'selected' : '' ?>>Published Only</option>
    <option value="draft" <?= $filterStatus === 'draft' ? 'selected' : '' ?>>Drafts Only</option>
    <option value="archived" <?= $filterStatus === 'archived' ? 'selected' : '' ?>>Archived</option>
    <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>All Statuses</option>
  </select>

  <select name="stock" class="filter-select" onchange="this.form.submit()">
    <option value="">All Stock</option>
    <option value="in_stock" <?= $filterStock === 'in_stock' ? 'selected' : '' ?>>In Stock</option>
    <option value="out_of_stock" <?= $filterStock === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
  </select>

  <button type="submit" style="background: #0284c7; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; min-height: 46px;">Search</button>

  <?php if (!empty($search) || !empty($filterCategory) || !empty($filterStatus) || !empty($filterStock)): ?>
    <a href="/admin/products.php" style="color: #ef4444; font-size: 0.85rem; font-weight: 600; text-decoration: none; margin-left: 6px; display: inline-flex; align-items: center; min-height: 44px;">Reset All</a>
  <?php endif; ?>
</form>

<!-- MOBILE PRODUCT CARDS GRID (< 768px) -->
<style>
  @media (min-width: 768px) {
    .mobile-products-container { display: none !important; }
  }
  @media (max-width: 767px) {
    .table-container { display: none !important; }
    .mobile-products-container { display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px; }
    .mobile-product-card {
      background: #1e293b;
      border: 1px solid #334155;
      border-radius: 12px;
      padding: 14px;
    }
    .mpc-top {
      display: flex;
      gap: 12px;
      margin-bottom: 12px;
    }
    .mpc-thumb {
      width: 56px;
      height: 56px;
      object-fit: contain;
      background: #0f172a;
      border-radius: 8px;
      padding: 4px;
      border: 1px solid #334155;
      flex-shrink: 0;
    }
    .mpc-info { flex: 1; min-width: 0; }
    .mpc-name {
      font-size: 0.95rem;
      font-weight: 700;
      color: #f8fafc;
      margin: 0 0 4px 0;
    }
    .mpc-meta {
      font-size: 0.78rem;
      color: #94a3b8;
      margin-bottom: 4px;
    }
    .mpc-sku code {
      background: #0f172a;
      border: 1px solid #334155;
      padding: 2px 6px;
      border-radius: 4px;
      color: #38bdf8;
      font-size: 0.75rem;
      font-weight: 700;
    }
    .mpc-details {
      background: #0f172a;
      border-radius: 8px;
      padding: 10px 12px;
      margin-bottom: 12px;
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .mpc-detail-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.82rem;
    }
    .mpc-label { color: #64748b; font-weight: 600; }
    .mpc-val { color: #e2e8f0; font-weight: 600; }
    .mpc-actions {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
    }
    .mpc-btns { display: flex; gap: 8px; }
    .mpc-btns a {
      min-height: 40px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 6px 14px;
    }
  }
</style>

<div class="mobile-products-container">
  <?php if (empty($pagedProducts)): ?>
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 30px; text-align: center; color: #94a3b8;">
      No products found matching your search filters.
    </div>
  <?php else: ?>
    <?php foreach ($pagedProducts as $p): ?>
      <?php 
        $pId = $p['id'] ?? $p['articleNo'];
        $pStatus = $p['status'] ?? 'published';
        $pQty = (int)($p['stockQty'] ?? ($p['stock'] ?? 1000));
        $pPrice = (float)($p['regularPrice'] ?? ($p['price'] ?? 0));
        $pSale = isset($p['salePrice']) && $p['salePrice'] !== null ? (float)$p['salePrice'] : null;
      ?>
      <div class="mobile-product-card" id="prod-card-<?= htmlspecialchars($pId) ?>" data-sku="<?= htmlspecialchars($pId) ?>">
        <div class="mpc-top">
          <img src="<?= htmlspecialchars($p['image'] ?? ($p['images'][0] ?? '/logo_svg.svg')) ?>" class="mpc-thumb" alt="Thumb" />
          <div class="mpc-info">
            <h4 class="mpc-name"><?= htmlspecialchars($p['name'] ?? $p['fullTitle'] ?? 'Product Item') ?></h4>
            <div class="mpc-meta">
              <?= htmlspecialchars($p['specs']['capacity'] ?? ($p['volume'] ?? ($p['capacity'] ?? 'N/A'))) ?> &bull; <?= htmlspecialchars($p['specs']['material'] ?? ($p['material'] ?? 'PET')) ?>
            </div>
            <div class="mpc-sku">SKU: <code><?= htmlspecialchars($p['articleNo'] ?? $p['id'] ?? 'SKU') ?></code></div>
          </div>
        </div>
        
        <div class="mpc-details">
          <div class="mpc-detail-item">
            <span class="mpc-label">Category &amp; Series</span>
            <span class="mpc-val"><?= htmlspecialchars($p['categoryName'] ?? 'Packaging') ?> (<?= htmlspecialchars($p['serie'] ?? 'General') ?>)</span>
          </div>
          <div class="mpc-detail-item">
            <span class="mpc-label">Price &amp; Stock</span>
            <span class="mpc-val">
              <?php if ($pSale !== null && $pSale < $pPrice): ?>
                <strong style="color: #4ade80;">₹<?= number_format($pSale, 2) ?></strong>
              <?php else: ?>
                <strong style="color: #e2e8f0;">₹<?= number_format($pPrice, 2) ?></strong>
              <?php endif; ?>
              <span style="font-size: 0.75rem; margin-left: 4px; color: <?= $pQty > 0 ? '#94a3b8' : '#ef4444' ?>;">(<?= number_format($pQty) ?> pcs)</span>
            </span>
          </div>
        </div>

        <div class="mpc-actions">
          <div class="mpc-status">
            <?php if ($pStatus === 'published'): ?>
              <span class="badge-published">Published</span>
            <?php elseif ($pStatus === 'draft'): ?>
              <span class="badge-draft">Draft</span>
            <?php else: ?>
              <span class="badge-archived">Archived</span>
            <?php endif; ?>
          </div>
          <div class="mpc-btns">
            <?php if ($pStatus === 'archived'): ?>
              <button type="button" class="btn-action-edit" style="background: #15803d; border:none; cursor:pointer;" onclick="ajaxProductAction('<?= htmlspecialchars($pId) ?>', 'restore', '<?= htmlspecialchars(addslashes($p['name'] ?? 'Product')) ?>', this)">Restore</button>
              <button type="button" class="btn-action-delete" style="background: #991b1b; color: #fff; border:none; cursor:pointer;" onclick="ajaxProductAction('<?= htmlspecialchars($pId) ?>', 'permanent', '<?= htmlspecialchars(addslashes($p['name'] ?? 'Product')) ?>', this)">Delete Forever</button>
            <?php else: ?>
              <a href="/admin/edit_product.php?id=<?= urlencode($pId) ?>" class="btn-action-edit">Edit</a>
              <button type="button" class="btn-action-delete" style="background: rgba(234, 179, 8, 0.15); color: #facc15; border: 1px solid rgba(234, 179, 8, 0.4); cursor:pointer;" onclick="ajaxProductAction('<?= htmlspecialchars($pId) ?>', 'archive', '<?= htmlspecialchars(addslashes($p['name'] ?? 'Product')) ?>', this)">Archive</button>
              <button type="button" class="btn-action-delete" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); cursor:pointer;" onclick="ajaxProductAction('<?= htmlspecialchars($pId) ?>', 'permanent', '<?= htmlspecialchars(addslashes($p['name'] ?? 'Product')) ?>', this)">Delete</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- CATALOGUE DATA TABLE (DESKTOP >= 768px) -->
<div class="table-container">
  <table class="table-admin">
    <thead>
      <tr>
        <th style="width: 60px;">Image</th>
        <th>Product Name &amp; Title</th>
        <th>Article / SKU</th>
        <th>Category &amp; Series</th>
        <th>Price &amp; Stock</th>
        <th>Status</th>
        <th style="text-align: right;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($pagedProducts)): ?>
        <tr>
          <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
            No products found matching your search filters.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($pagedProducts as $p): ?>
          <?php 
            $pId = $p['id'] ?? $p['articleNo'];
            $pStatus = $p['status'] ?? 'published';
            $pQty = (int)($p['stockQty'] ?? ($p['stock'] ?? 1000));
            $pPrice = (float)($p['regularPrice'] ?? ($p['price'] ?? 0));
            $pSale = isset($p['salePrice']) && $p['salePrice'] !== null ? (float)$p['salePrice'] : null;
          ?>
          <tr id="prod-row-<?= htmlspecialchars($pId) ?>" data-sku="<?= htmlspecialchars($pId) ?>">
            <td>
              <img src="<?= htmlspecialchars($p['image'] ?? ($p['images'][0] ?? '/logo_svg.svg')) ?>" class="product-thumb" alt="Thumb" />
            </td>
            <td>
              <strong style="color: #f8fafc; font-size: 0.92rem; display: block; margin-bottom: 2px;">
                <?= htmlspecialchars($p['name'] ?? $p['fullTitle'] ?? 'Product Item') ?>
              </strong>
              <span style="font-size: 0.78rem; color: #94a3b8;">
                <?= htmlspecialchars($p['specs']['capacity'] ?? ($p['volume'] ?? ($p['capacity'] ?? 'N/A'))) ?> &bull; <?= htmlspecialchars($p['specs']['material'] ?? ($p['material'] ?? 'PET')) ?>
              </span>
            </td>
            <td>
              <code style="background: #0f172a; border: 1px solid #334155; padding: 3px 6px; border-radius: 4px; color: #38bdf8; font-size: 0.8rem; font-weight: 700;">
                <?= htmlspecialchars($p['articleNo'] ?? $p['id'] ?? 'SKU') ?>
              </code>
            </td>
            <td>
              <span style="display: block; font-weight: 600; font-size: 0.85rem; color: #e2e8f0;"><?= htmlspecialchars($p['categoryName'] ?? 'Packaging') ?></span>
              <span style="font-size: 0.78rem; color: #64748b;"><?= htmlspecialchars($p['serie'] ?? 'General Series') ?></span>
            </td>
            <td>
              <?php if ($pSale !== null && $pSale < $pPrice): ?>
                <span style="text-decoration: line-through; color: #64748b; font-size: 0.8rem;">₹<?= number_format($pPrice, 2) ?></span>
                <strong style="color: #4ade80; font-size: 0.88rem; display: block;">₹<?= number_format($pSale, 2) ?></strong>
              <?php else: ?>
                <strong style="color: #e2e8f0; font-size: 0.88rem;">₹<?= number_format($pPrice, 2) ?></strong>
              <?php endif; ?>

              <div style="font-size: 0.75rem; margin-top: 2px; color: <?= $pQty > 0 ? '#94a3b8' : '#ef4444' ?>;">
                Stock: <strong><?= number_format($pQty) ?> pcs</strong>
              </div>
            </td>
            <td>
              <?php if ($pStatus === 'published'): ?>
                <span class="badge-published">Published</span>
              <?php elseif ($pStatus === 'draft'): ?>
                <span class="badge-draft">Draft</span>
              <?php else: ?>
                <span class="badge-archived">Archived</span>
              <?php endif; ?>

              <?php if (!empty($p['isFeatured'])): ?>
                <span style="display: inline-block; margin-left: 4px; background: rgba(234, 179, 8, 0.2); color: #facc15; padding: 2px 5px; border-radius: 4px; font-size: 0.7rem; font-weight: 700;">★ Featured</span>
              <?php endif; ?>
            </td>
            <td style="text-align: right; white-space: nowrap;">
              <?php if ($pStatus === 'archived'): ?>
                <button type="button" class="btn-action-edit" style="background: #15803d; padding: 6px 12px; border:none; cursor:pointer;" title="Restore to active catalogue" onclick="ajaxProductAction('<?= htmlspecialchars($pId) ?>', 'restore', '<?= htmlspecialchars(addslashes($p['name'] ?? 'Product')) ?>', this)">
                  Restore
                </button>
                <button type="button" class="btn-action-delete" style="background: rgba(239, 68, 68, 0.2); color: #f87171; border-color: rgba(239, 68, 68, 0.5); margin-left: 6px; cursor:pointer;" title="Permanently delete from database" onclick="ajaxProductAction('<?= htmlspecialchars($pId) ?>', 'permanent', '<?= htmlspecialchars(addslashes($p['name'] ?? 'Product')) ?>', this)">
                  Delete Forever
                </button>
              <?php else: ?>
                <a href="/admin/edit_product.php?id=<?= urlencode($pId) ?>" class="btn-action-edit" title="Edit product details">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  Edit
                </a>

                <button type="button" class="btn-action-delete" style="background: rgba(234, 179, 8, 0.15); color: #facc15; border: 1px solid rgba(234, 179, 8, 0.4); margin-left: 6px; cursor:pointer;" title="Archive product" onclick="ajaxProductAction('<?= htmlspecialchars($pId) ?>', 'archive', '<?= htmlspecialchars(addslashes($p['name'] ?? 'Product')) ?>', this)">
                  Archive
                </button>

                <button type="button" class="btn-action-delete" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); margin-left: 6px; cursor:pointer;" title="Delete forever" onclick="ajaxProductAction('<?= htmlspecialchars($pId) ?>', 'permanent', '<?= htmlspecialchars(addslashes($p['name'] ?? 'Product')) ?>', this)">
                  Delete
                </button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- PAGINATION CONTROLS -->
<?php if ($totalPages > 1): ?>
  <div class="pagination">
    <div style="color: #94a3b8; font-size: 0.85rem;">
      Page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong> (Total <span id="totalProductCount"><?= $totalProducts ?></span> SKUs)
    </div>
    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
      <?php if ($page > 1): ?>
        <a href="/admin/products.php?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="page-link">&laquo; Prev</a>
      <?php endif; ?>

      <?php 
        $startP = max(1, $page - 3);
        $endP = min($totalPages, $page + 3);
        for ($i = $startP; $i <= $endP; $i++): 
      ?>
        <a href="/admin/products.php?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="page-link <?= $i === $page ? 'active' : '' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>

      <?php if ($page < $totalPages): ?>
        <a href="/admin/products.php?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="page-link">Next &raquo;</a>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<!-- TOAST ALERT NOTIFICATION CONTAINER -->
<div id="toastNotification" style="position: fixed; bottom: 28px; right: 28px; z-index: 9999; display: none; background: #1e293b; color: #fff; padding: 14px 22px; border-radius: 12px; border: 1px solid #38bdf8; box-shadow: 0 10px 30px rgba(0,0,0,0.5); font-size: 0.9rem; font-weight: 600; align-items: center; gap: 12px; transition: all 0.3s ease;"></div>

<script>
function showToast(msg, isErr = false) {
  const t = document.getElementById('toastNotification');
  if (!t) return;
  t.style.display = 'flex';
  t.style.borderColor = isErr ? '#ef4444' : '#38bdf8';
  t.innerHTML = (isErr ? '❌ ' : '✓ ') + msg;
  setTimeout(() => { t.style.display = 'none'; }, 4500);
}

function ajaxProductAction(sku, action, name, btnElem) {
  let confirmMsg = '';
  if (action === 'archive') {
    confirmMsg = "Archive product '" + name + "'? It will be hidden from the live website catalogue.";
  } else if (action === 'permanent') {
    confirmMsg = "PERMANENTLY DELETE product '" + name + "'? This action cannot be undone.";
  } else if (action === 'restore') {
    confirmMsg = "Restore product '" + name + "' to active catalogue?";
  }

  if (confirmMsg && !confirm(confirmMsg)) {
    return false;
  }

  btnElem.disabled = true;
  btnElem.style.opacity = '0.5';

  fetch('/admin/delete_product.php?id=' + encodeURIComponent(sku) + '&action=' + encodeURIComponent(action) + '&ajax=1', {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showToast(data.message);
      // Remove row and/or mobile card smoothly
      const row = document.getElementById('prod-row-' + sku) || btnElem.closest('tr');
      const card = document.getElementById('prod-card-' + sku) || btnElem.closest('.mobile-product-card');

      [row, card].forEach(elem => {
        if (elem) {
          elem.style.transition = 'all 0.35s ease';
          elem.style.opacity = '0';
          elem.style.transform = 'translateX(40px)';
          setTimeout(() => { elem.remove(); }, 350);
        }
      });

      // Update counter if present
      const counter = document.getElementById('totalProductCount');
      if (counter) {
        const cur = parseInt(counter.textContent) || 0;
        if (cur > 0) counter.textContent = cur - 1;
      }
    } else {
      showToast(data.message || 'Action failed', true);
      btnElem.disabled = false;
      btnElem.style.opacity = '1';
    }
  })
  .catch(err => {
    // Fallback to normal navigation
    window.location.href = '/admin/delete_product.php?id=' + encodeURIComponent(sku) + '&action=' + encodeURIComponent(action);
  });
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
