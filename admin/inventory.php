<?php
require_once __DIR__ . '/auth.php';
checkAdminAuth();

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Models/ProductModel.php';

$message = '';
$error = '';

// Handle POST updates (Standard POST or AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skuId = trim($_POST['sku_id'] ?? '');
    $stockQty = max(0, (int)($_POST['stock_qty'] ?? 0));
    $regularPrice = max(0, (float)($_POST['regular_price'] ?? 0.00));
    $isAjax = !empty($_POST['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

    if (empty($skuId)) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'SKU ID is required.']);
            exit();
        }
        $error = "SKU ID is required.";
    } else {
        $product = ProductModel::getSkuById($skuId);
        if ($product) {
            $product['stockQty'] = $stockQty;
            $product['stockStatus'] = $stockQty > 0 ? 'in_stock' : 'out_of_stock';
            $product['regularPrice'] = $regularPrice;
            ProductModel::saveProduct($product);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => "Updated {$product['name']} (Stock: {$stockQty}, Price: ₹" . number_format($regularPrice, 2) . ")",
                    'stockStatus' => $product['stockStatus'],
                    'stockQty' => $stockQty,
                    'price' => $regularPrice
                ]);
                exit();
            }
            $message = "Inventory & Pricing updated for {$product['name']} ({$skuId}).";
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => "Product '{$skuId}' not found."]);
                exit();
            }
            $error = "Product '{$skuId}' not found.";
        }
    }
}

require_once __DIR__ . '/header.php';

$search = trim($_GET['search'] ?? '');
if (!empty($search)) {
    $products = ProductModel::searchProducts($search, true);
} else {
    $products = ProductModel::getSkus(true);
}

// Filter out archived unless searched
if (empty($search)) {
    $products = array_values(array_filter($products, function($p) {
        return ($p['status'] ?? 'published') !== 'archived';
    }));
}
?>

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0; color: #f8fafc;">Inventory & Pricing Management</h1>
    <p style="color: #94a3b8; margin: 4px 0 0 0; font-size: 0.9rem;">Quickly update stock levels, prices, and availability with instant saving</p>
  </div>
</div>

<?php if (!empty($message)): ?>
  <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px;"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px;"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Quick Search Form -->
<form method="GET" action="/admin/inventory.php" style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 14px 18px; display: flex; gap: 12px; margin-bottom: 24px; align-items: center; flex-wrap: wrap;">
  <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search SKU or product name to quickly update..." style="flex: 1; min-width: 220px; background: #0f172a; border: 1px solid #475569; color: #fff; padding: 10px 14px; border-radius: 8px; font-size: 0.9rem;" />
  <button type="submit" style="background: #0284c7; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer;">Filter</button>
  <?php if (!empty($search)): ?>
    <a href="/admin/inventory.php" style="color: #ef4444; font-size: 0.85rem; font-weight: 600; text-decoration: none;">Clear Search</a>
  <?php endif; ?>
  <span style="color: #94a3b8; font-size: 0.85rem; margin-left: auto;">Showing <?= min(100, count($products)) ?> of <?= count($products) ?> items</span>
</form>

<style>
  @media (min-width: 768px) {
    .mobile-inventory-container { display: none !important; }
  }
  @media (max-width: 767px) {
    .desktop-inventory-table { display: none !important; }
    .mobile-inventory-container { display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px; }
    .mobile-inv-card {
      background: #1e293b;
      border: 1px solid #334155;
      border-radius: 12px;
      padding: 14px;
    }
    .mic-header {
      display: flex;
      gap: 10px;
      align-items: center;
      margin-bottom: 12px;
    }
    .mic-thumb {
      width: 44px;
      height: 44px;
      object-fit: contain;
      background: #0f172a;
      border-radius: 6px;
      padding: 3px;
      border: 1px solid #334155;
    }
    .mic-title {
      font-size: 0.9rem;
      font-weight: 700;
      color: #f8fafc;
    }
    .mic-sku {
      font-size: 0.75rem;
      color: #38bdf8;
    }
    .mic-form {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin-bottom: 10px;
    }
    .mic-field label {
      display: block;
      font-size: 0.72rem;
      color: #94a3b8;
      font-weight: 700;
      margin-bottom: 4px;
      text-transform: uppercase;
    }
    .mic-field input {
      width: 100% !important;
      min-height: 44px !important;
      padding: 8px 10px !important;
      background: #0f172a !important;
      border: 1px solid #475569 !important;
      border-radius: 6px !important;
      color: #fff !important;
      font-size: 0.9rem !important;
      box-sizing: border-box !important;
    }
    .mic-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
    }
    .mic-btn-save {
      background: #0284c7;
      color: #fff;
      border: none;
      padding: 10px 18px;
      border-radius: 6px;
      font-weight: 700;
      font-size: 0.85rem;
      min-height: 44px;
      width: 100%;
      cursor: pointer;
    }
  }
</style>

<!-- MOBILE INVENTORY CARDS (< 768px) -->
<div class="mobile-inventory-container">
  <?php foreach (array_slice($products, 0, 100) as $p): ?>
    <?php 
      $pId = $p['id'] ?? $p['articleNo'];
      $stockQty = (int)($p['stockQty'] ?? ($p['stock'] ?? 1000));
      $stockStatus = $stockQty <= 0 ? 'out_of_stock' : ($p['stockStatus'] ?? 'in_stock');
      $price = (float)($p['regularPrice'] ?? ($p['price'] ?? 0.00));
    ?>
    <div class="mobile-inv-card" id="mic-card-<?= htmlspecialchars($pId) ?>">
      <div class="mic-header">
        <img src="<?= htmlspecialchars($p['image'] ?? '/logo_svg.svg') ?>" class="mic-thumb" alt="Thumb" />
        <div>
          <div class="mic-title"><?= htmlspecialchars($p['name'] ?? $p['fullTitle'] ?? 'Product') ?></div>
          <div class="mic-sku">Article: <code><?= htmlspecialchars($p['articleNo'] ?? $pId) ?></code></div>
        </div>
      </div>

      <div class="mic-form">
        <div class="mic-field">
          <label>Price (₹)</label>
          <input type="number" step="0.01" id="m-price-<?= htmlspecialchars($pId) ?>" value="<?= sprintf('%.2f', $price) ?>" required />
        </div>
        <div class="mic-field">
          <label>Stock Qty (pcs)</label>
          <input type="number" id="m-stock-<?= htmlspecialchars($pId) ?>" value="<?= (int)$stockQty ?>" required />
        </div>
      </div>

      <div class="mic-footer">
        <div id="m-status-<?= htmlspecialchars($pId) ?>" style="flex: 1;">
          <?php if ($stockStatus === 'in_stock'): ?>
            <span style="background: rgba(52, 211, 153, 0.15); color: #34d399; padding: 4px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; display: inline-block;">In Stock</span>
          <?php else: ?>
            <span style="background: rgba(239, 68, 68, 0.15); color: #fca5a5; padding: 4px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; display: inline-block;">Out of Stock</span>
          <?php endif; ?>
        </div>
        <button type="button" class="mic-btn-save" style="flex: 1;" onclick="saveInventoryQuick('<?= htmlspecialchars(addslashes($pId)) ?>', 'm')">Quick Save</button>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- DESKTOP INVENTORY TABLE (>= 768px) -->
<div class="desktop-inventory-table" style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 24px; overflow-x: auto;">
  <table style="width: 100%; border-collapse: collapse;">
    <thead>
      <tr style="border-bottom: 1px solid #334155;">
        <th style="text-align: left; padding: 12px 10px; font-size: 0.78rem; color: #94a3b8; text-transform: uppercase;">Image</th>
        <th style="text-align: left; padding: 12px 10px; font-size: 0.78rem; color: #94a3b8; text-transform: uppercase;">Product Name</th>
        <th style="text-align: left; padding: 12px 10px; font-size: 0.78rem; color: #94a3b8; text-transform: uppercase;">Article / SKU</th>
        <th style="text-align: left; padding: 12px 10px; font-size: 0.78rem; color: #94a3b8; text-transform: uppercase;">Price (₹)</th>
        <th style="text-align: left; padding: 12px 10px; font-size: 0.78rem; color: #94a3b8; text-transform: uppercase;">Stock Qty (pcs)</th>
        <th style="text-align: left; padding: 12px 10px; font-size: 0.78rem; color: #94a3b8; text-transform: uppercase;">Status</th>
        <th style="text-align: right; padding: 12px 10px; font-size: 0.78rem; color: #94a3b8; text-transform: uppercase;">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($products)): ?>
        <tr>
          <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">No products found matching your search.</td>
        </tr>
      <?php else: ?>
        <?php foreach (array_slice($products, 0, 100) as $p): ?>
          <?php 
            $pId = $p['id'] ?? $p['articleNo'];
            $stockQty = (int)($p['stockQty'] ?? ($p['stock'] ?? 1000));
            $stockStatus = $stockQty <= 0 ? 'out_of_stock' : ($p['stockStatus'] ?? 'in_stock');
            $price = (float)($p['regularPrice'] ?? ($p['price'] ?? 0.00));
          ?>
          <tr id="row-<?= htmlspecialchars($pId) ?>" style="border-bottom: 1px solid rgba(51, 65, 85, 0.5);">
            <td style="padding: 12px 10px;">
              <img src="<?= htmlspecialchars($p['image'] ?? '/logo_svg.svg') ?>" style="width: 40px; height: 40px; object-fit: contain; background: #0f172a; border-radius: 6px; padding: 3px; border: 1px solid #334155;" alt="Thumb" />
            </td>
            <td style="padding: 12px 10px;">
              <strong style="color: #f8fafc; font-size: 0.92rem; display: block;"><?= htmlspecialchars($p['name'] ?? $p['fullTitle'] ?? 'Product') ?></strong>
              <span style="color: #64748b; font-size: 0.78rem;"><?= htmlspecialchars($p['categoryName'] ?? 'Packaging') ?></span>
            </td>
            <td style="padding: 12px 10px;">
              <code style="background: #0f172a; border: 1px solid #334155; padding: 3px 6px; border-radius: 4px; color: #38bdf8; font-size: 0.8rem; font-weight: 700;">
                <?= htmlspecialchars($p['articleNo'] ?? $pId) ?>
              </code>
            </td>
            <td style="padding: 12px 10px;">
              <input type="number" step="0.01" id="d-price-<?= htmlspecialchars($pId) ?>" value="<?= sprintf('%.2f', $price) ?>" style="width: 100px; padding: 8px 10px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.9rem;" />
            </td>
            <td style="padding: 12px 10px;">
              <input type="number" id="d-stock-<?= htmlspecialchars($pId) ?>" value="<?= (int)$stockQty ?>" style="width: 100px; padding: 8px 10px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.9rem;" />
            </td>
            <td style="padding: 12px 10px;" id="d-status-<?= htmlspecialchars($pId) ?>">
              <?php if ($stockStatus === 'in_stock'): ?>
                <span style="background: rgba(52, 211, 153, 0.15); color: #34d399; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">In Stock</span>
              <?php else: ?>
                <span style="background: rgba(239, 68, 68, 0.15); color: #fca5a5; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">Out of Stock</span>
              <?php endif; ?>
            </td>
            <td style="padding: 12px 10px; text-align: right;">
              <button type="button" id="btn-save-<?= htmlspecialchars($pId) ?>" onclick="saveInventoryQuick('<?= htmlspecialchars(addslashes($pId)) ?>', 'd')" style="background: #0284c7; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 0.82rem; transition: all 0.2s;">
                Quick Save
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<script>
function saveInventoryQuick(skuId, prefix) {
  const priceInput = document.getElementById(prefix + '-price-' + skuId);
  const stockInput = document.getElementById(prefix + '-stock-' + skuId);
  const btn = prefix === 'd' ? document.getElementById('btn-save-' + skuId) : event.target;
  const statusEl = document.getElementById(prefix + '-status-' + skuId);

  const priceVal = priceInput ? priceInput.value : 0;
  const stockVal = stockInput ? stockInput.value : 0;

  const origText = btn.innerText;
  btn.innerText = 'Saving...';
  btn.disabled = true;

  const data = new FormData();
  data.append('sku_id', skuId);
  data.append('regular_price', priceVal);
  data.append('stock_qty', stockVal);
  data.append('ajax', '1');

  fetch('/admin/inventory.php', {
    method: 'POST',
    body: data
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      btn.innerText = '✓ Saved!';
      btn.style.background = '#15803d';
      
      // Update badge
      if (statusEl) {
        if (res.stockStatus === 'in_stock') {
          statusEl.innerHTML = '<span style="background: rgba(52, 211, 153, 0.15); color: #34d399; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">In Stock</span>';
        } else {
          statusEl.innerHTML = '<span style="background: rgba(239, 68, 68, 0.15); color: #fca5a5; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">Out of Stock</span>';
        }
      }

      setTimeout(() => {
        btn.innerText = origText;
        btn.style.background = '#0284c7';
        btn.disabled = false;
      }, 2000);
    } else {
      alert(res.message || 'Error updating product');
      btn.innerText = origText;
      btn.disabled = false;
    }
  })
  .catch(err => {
    alert('Network error while saving inventory.');
    btn.innerText = origText;
    btn.disabled = false;
  });
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
