<?php
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/ProductModel.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skuId = $_POST['sku_id'] ?? '';
    $stockQty = (int)($_POST['stock_qty'] ?? 0);
    $regularPrice = (float)($_POST['regular_price'] ?? 0.00);

    $message = "Inventory & Pricing updated for SKU #{$skuId}.";
}

$products = ProductModel::getSkus();
?>

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px;">
  <div>
    <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0;">Inventory & Pricing Management</h1>
    <p style="color: #94a3b8; margin: 4px 0 0 0;">Manage stock quantities, out of stock status, regular prices, and sale prices</p>
  </div>
</div>

<?php if (!empty($message)): ?>
  <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px;"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- MOBILE INVENTORY CARDS (< 768px) -->
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

<div class="mobile-inventory-container">
  <?php foreach (array_slice($products, 0, 50) as $p): ?>
    <?php 
      $stockQty = $p['specs']['stock_qty'] ?? $p['stock_qty'] ?? 5000;
      $stockStatus = $stockQty <= 0 ? 'out_of_stock' : 'in_stock';
      $price = $p['regular_price'] ?? 12.50;
    ?>
    <div class="mobile-inv-card">
      <form method="POST" action="inventory.php">
        <input type="hidden" name="sku_id" value="<?= htmlspecialchars($p['id']) ?>" />
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />

        <div class="mic-header">
          <img src="<?= htmlspecialchars($p['image'] ?? '/logo_svg.svg') ?>" class="mic-thumb" alt="Thumb" />
          <div>
            <div class="mic-title"><?= htmlspecialchars($p['name'] ?? $p['fullTitle'] ?? 'Product') ?></div>
            <div class="mic-sku">Article: <code><?= htmlspecialchars($p['articleNo'] ?? 'N/A') ?></code></div>
          </div>
        </div>

        <div class="mic-form">
          <div class="mic-field">
            <label>Price (₹)</label>
            <input type="number" step="0.01" name="regular_price" value="<?= sprintf('%.2f', $price) ?>" required />
          </div>
          <div class="mic-field">
            <label>Stock Qty (pcs)</label>
            <input type="number" name="stock_qty" value="<?= (int)$stockQty ?>" required />
          </div>
        </div>

        <div class="mic-footer">
          <div style="flex: 1;">
            <?php if ($stockStatus === 'in_stock'): ?>
              <span style="background: rgba(52, 211, 153, 0.15); color: #34d399; padding: 4px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; display: inline-block;">In Stock</span>
            <?php else: ?>
              <span style="background: rgba(239, 68, 68, 0.15); color: #fca5a5; padding: 4px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; display: inline-block;">Out of Stock</span>
            <?php endif; ?>
          </div>
          <button type="submit" class="mic-btn-save" style="flex: 1;">Quick Save</button>
        </div>
      </form>
    </div>
  <?php endforeach; ?>
</div>

<div class="desktop-inventory-table" style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 24px; overflow-x: auto;">
  <table style="width: 100%; border-collapse: collapse;">
    <thead>
      <tr style="border-bottom: 1px solid #334155;">
        <th style="text-align: left; padding: 10px; font-size: 0.78rem; color: #94a3b8;">Image</th>
        <th style="text-align: left; padding: 10px; font-size: 0.78rem; color: #94a3b8;">Product Name</th>
        <th style="text-align: left; padding: 10px; font-size: 0.78rem; color: #94a3b8;">Article No.</th>
        <th style="text-align: left; padding: 10px; font-size: 0.78rem; color: #94a3b8;">Price (₹)</th>
        <th style="text-align: left; padding: 10px; font-size: 0.78rem; color: #94a3b8;">Stock Qty</th>
        <th style="text-align: left; padding: 10px; font-size: 0.78rem; color: #94a3b8;">Stock Availability</th>
        <th style="text-align: left; padding: 10px; font-size: 0.78rem; color: #94a3b8;">Quick Save</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach (array_slice($products, 0, 50) as $p): ?>
        <?php 
          $stockQty = $p['specs']['stock_qty'] ?? $p['stock_qty'] ?? 5000;
          $stockStatus = $stockQty <= 0 ? 'out_of_stock' : 'in_stock';
          $price = $p['regular_price'] ?? 12.50;
        ?>
        <tr style="border-bottom: 1px solid rgba(51, 65, 85, 0.5);">
          <form method="POST" action="inventory.php">
            <input type="hidden" name="sku_id" value="<?= htmlspecialchars($p['id']) ?>" />
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />

            <td style="padding: 10px;">
              <img src="<?= htmlspecialchars($p['image'] ?? '/logo_svg.svg') ?>" style="width: 36px; height: 36px; object-fit: contain; background: #0f172a; border-radius: 6px; padding: 2px;" alt="Thumb" />
            </td>
            <td style="padding: 10px;"><strong><?= htmlspecialchars($p['name'] ?? $p['fullTitle'] ?? 'Product') ?></strong></td>
            <td style="padding: 10px;"><code><?= htmlspecialchars($p['articleNo'] ?? 'N/A') ?></code></td>
            <td style="padding: 10px;">
              <input type="number" step="0.01" name="regular_price" value="<?= sprintf('%.2f', $price) ?>" style="width: 80px; padding: 6px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #fff;" />
            </td>
            <td style="padding: 10px;">
              <input type="number" name="stock_qty" value="<?= (int)$stockQty ?>" style="width: 80px; padding: 6px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #fff;" />
            </td>
            <td style="padding: 10px;">
              <?php if ($stockStatus === 'in_stock'): ?>
                <span style="background: rgba(52, 211, 153, 0.15); color: #34d399; padding: 3px 6px; border-radius: 4px; font-size: 0.72rem; font-weight: 700;">In Stock (Available)</span>
              <?php else: ?>
                <span style="background: rgba(239, 68, 68, 0.15); color: #fca5a5; padding: 3px 6px; border-radius: 4px; font-size: 0.72rem; font-weight: 700;">Out of Stock</span>
              <?php endif; ?>
            </td>
            <td style="padding: 10px;">
              <button type="submit" style="background: #0284c7; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.8rem;">Save</button>
            </td>
          </form>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
