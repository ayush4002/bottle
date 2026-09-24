<?php
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/ProductModel.php';

checkAdminAuth();

$id = trim($_GET['id'] ?? '');
if (empty($id)) {
    header("Location: /admin/products.php?err=" . urlencode("No product ID specified for editing."));
    exit();
}

$product = ProductModel::getSkuById($id);
if (!$product) {
    header("Location: /admin/products.php?err=" . urlencode("Product '{$id}' not found."));
    exit();
}

$categories = ProductModel::getCategories();
$series = ProductModel::getSeries();
$dbOptions = ProductModel::getAllOptions(true);

$materialsList = $dbOptions['materials'] ?? [];
$volumesList = $dbOptions['volumes'] ?? [];
$necksList = $dbOptions['necks'] ?? [];
$appsList = $dbOptions['applications'] ?? [];
$featsList = $dbOptions['features'] ?? [];
$audiencesList = $dbOptions['audiences'] ?? [];
$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// Extract Images 1 to 4
$existingImages = $product['images'] ?? (!empty($product['image']) ? [$product['image']] : []);
if (!is_array($existingImages)) $existingImages = [$existingImages];
$img1 = $existingImages[0] ?? ($product['image'] ?? '/logo_svg.svg');
$img2 = $existingImages[1] ?? '/logo_svg.svg';
$img3 = $existingImages[2] ?? '/logo_svg.svg';
$img4 = $existingImages[3] ?? '/logo_svg.svg';

// Technical Specs
$specs = $product['specs'] ?? [];
$capacity = $specs['capacity'] ?? ($product['volume'] ?? ($product['capacity'] ?? '250ml'));
$neck = $specs['neck'] ?? ($product['neckSize'] ?? ($product['neck'] ?? '24/410'));
$material = $specs['material'] ?? ($product['material'] ?? 'PET');
$weight = $specs['weight'] ?? ($product['weight'] ?? '23 g');
$moq = $specs['moq'] ?? ($product['moq'] ?? '5,000 pcs');

// Commercial
$regularPrice = isset($product['regularPrice']) ? $product['regularPrice'] : ($product['price'] ?? 0);
$salePrice = isset($product['salePrice']) ? $product['salePrice'] : '';
$stockQty = isset($product['stockQty']) ? $product['stockQty'] : (isset($product['isStock']) && $product['isStock'] ? 1000 : 0);
$stockStatus = $product['stockStatus'] ?? ($stockQty > 0 ? 'in_stock' : 'out_of_stock');
$status = $product['status'] ?? 'published';
$isFeatured = !empty($product['isFeatured']);
$gender = $product['gender'] ?? 'Unisex';
?>

<style>
  .smart-form-container {
    max-width: 920px;
    margin: 0 auto;
  }
  .form-card {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
  }
  .card-header-smart {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 1.15rem;
    font-weight: 700;
    color: #38bdf8;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid #334155;
  }
  
  /* CHIP SELECTION GRID */
  .chip-group {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 8px;
    margin-bottom: 16px;
  }
  .select-chip {
    background: #0f172a;
    border: 1px solid #475569;
    color: #cbd5e1;
    padding: 9px 16px;
    border-radius: 8px;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    user-select: none;
    transition: all 0.18s ease;
  }
  .select-chip:hover {
    border-color: #38bdf8;
    color: #ffffff;
    background: rgba(14, 165, 233, 0.1);
  }
  .select-chip.selected {
    background: #0284c7;
    border-color: #38bdf8;
    color: #ffffff;
    box-shadow: 0 0 10px rgba(56, 189, 248, 0.25);
  }

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 18px;
  }
  .form-row-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 16px;
    margin-bottom: 18px;
  }
  .form-group {
    margin-bottom: 18px;
  }
  .form-label {
    display: block;
    font-size: 0.85rem;
    font-weight: 700;
    color: #cbd5e1;
    margin-bottom: 8px;
  }
  .form-control-smart {
    width: 100%;
    box-sizing: border-box;
    padding: 12px 14px;
    background: #0f172a;
    border: 1px solid #475569;
    border-radius: 8px;
    color: #ffffff;
    font-size: 0.92rem;
  }
  .form-control-smart:focus {
    outline: none;
    border-color: #38bdf8;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15);
  }

  .media-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
  }
  .media-slot {
    background: #0f172a;
    border: 2px dashed #475569;
    border-radius: 12px;
    padding: 14px;
    text-align: center;
    position: relative;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .media-slot:hover {
    border-color: #38bdf8;
    background: rgba(14, 165, 233, 0.05);
  }
  .media-slot-preview {
    width: 100%;
    height: 100px;
    object-fit: contain;
    background: #ffffff;
    border-radius: 6px;
    margin-bottom: 8px;
    padding: 4px;
  }
  .btn-auto-gen {
    background: rgba(14, 165, 233, 0.15);
    color: #38bdf8;
    border: 1px solid rgba(14, 165, 233, 0.3);
    padding: 8px 14px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
  }
  .btn-submit-publish {
    background: linear-gradient(135deg, #0284c7, #1e3a8a);
    color: #ffffff;
    border: none;
    padding: 16px 36px;
    font-size: 1.05rem;
    font-weight: 800;
    border-radius: 10px;
    cursor: pointer;
  }
  .btn-cancel {
    background: #334155;
    color: #cbd5e1;
    border: none;
    padding: 16px 24px;
    font-size: 0.95rem;
    font-weight: 700;
    border-radius: 10px;
    text-decoration: none;
  }
  .alert-success {
    background: rgba(52, 211, 153, 0.15);
    border: 1px solid rgba(52, 211, 153, 0.4);
    color: #6ee7b7;
    padding: 14px 18px;
    border-radius: 10px;
    font-size: 0.9rem;
    margin-bottom: 24px;
  }
  .alert-danger {
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.4);
    color: #fca5a5;
    padding: 14px 18px;
    border-radius: 10px;
    font-size: 0.9rem;
    margin-bottom: 24px;
  }
</style>

<div class="smart-form-container">
  
  <!-- HEADER BAR -->
  <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
      <a href="/admin/products.php" style="color: #38bdf8; text-decoration: none; font-size: 0.88rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 6px;">
        &larr; Back to Catalogue Management
      </a>
      <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0; color: #f8fafc;">
        Edit Product: <span style="color: #38bdf8;"><?= htmlspecialchars($product['name'] ?? 'Product') ?></span>
      </h1>
      <p style="color: #94a3b8; margin: 4px 0 0 0; font-size: 0.88rem;">
        SKU Article: <code><?= htmlspecialchars($product['articleNo'] ?? $product['id']) ?></code>
      </p>
    </div>
    <a href="/#products" target="_blank" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.3); padding: 10px 18px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.85rem;">
      View Live on Website &nearr;
    </a>
  </div>

  <?php if (!empty($msg)): ?>
    <div class="alert-success"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <?php if (!empty($err)): ?>
    <div class="alert-danger"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <form id="smartProductForm" action="save_product.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>" />
    <input type="hidden" name="is_edit" value="1" />
    <input type="hidden" name="id" value="<?= htmlspecialchars($product['id']) ?>" />

    <!-- 1. PRODUCT BASICS -->
    <div class="form-card">
      <div class="card-header-smart">
        <span>① Product Basics</span>
        <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 400;">Product Title &amp; SKU</span>
      </div>

      <div class="form-group">
        <label for="name" class="form-label">Product Name / Title *</label>
        <input type="text" id="name" name="name" class="form-control-smart" value="<?= htmlspecialchars($product['name'] ?? '') ?>" required />
      </div>

      <div class="form-row">
        <div>
          <label for="articleNo" class="form-label">SKU / Article Number *</label>
          <input type="text" id="articleNo" name="articleNo" class="form-control-smart" value="<?= htmlspecialchars($product['articleNo'] ?? '') ?>" required />
        </div>
        <div>
          <label for="gender" class="form-label">Target Audience / Gender</label>
          <select id="gender" name="gender" class="form-control-smart">
            <option value="Unisex" <?= $gender === 'Unisex' ? 'selected' : '' ?>>Unisex / General</option>
            <option value="Men" <?= $gender === 'Men' ? 'selected' : '' ?>>Men</option>
            <option value="Women" <?= $gender === 'Women' ? 'selected' : '' ?>>Women</option>
          </select>
        </div>
      </div>
    </div>

    <!-- 2. CLASSIFICATION & SPECIFICATIONS (CHIPS) -->
    <div class="form-card">
      <div class="card-header-smart">
        <span>② Classification &amp; Specifications</span>
        <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 400;">Category, Material &amp; Volume</span>
      </div>

      <div class="form-row">
        <div>
          <label for="categorySlug" class="form-label">Packaging Category *</label>
          <select id="categorySlug" name="categorySlug" class="form-control-smart" required onchange="updateSubcategories()">
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= ($product['categorySlug'] ?? '') === $cat['slug'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label for="serie" class="form-label">Series / Subcategory</label>
          <select id="serie" name="serie" class="form-control-smart">
            <!-- Dynamically populated -->
          </select>
        </div>
      </div>

      <!-- MATERIAL CHIPS (DATABASE BACKED) -->
      <div class="form-group">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <label class="form-label">Material Polymer</label>
          <a href="/admin/categories.php?tab=materials" target="_blank" style="color: #38bdf8; font-size: 0.78rem; text-decoration: none; font-weight: 600;">Manage Materials &nearr;</a>
        </div>
        <input type="hidden" id="material" name="material" value="<?= htmlspecialchars($material) ?>" />
        <div class="chip-group" id="material-chips">
          <?php foreach ($materialsList as $m): ?>
            <?php $mVal = $m['name'] ?? $m['short_label']; ?>
            <div class="select-chip <?= strtolower($material) === strtolower($mVal) ? 'selected' : '' ?>" onclick="selectChip('material-chips', this, 'material', '<?= htmlspecialchars($mVal) ?>')">
              <?= htmlspecialchars($mVal) ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- VOLUME CHIPS (DATABASE BACKED) -->
      <div class="form-group">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <label class="form-label">Volume / Capacity</label>
          <a href="/admin/categories.php?tab=volumes" target="_blank" style="color: #38bdf8; font-size: 0.78rem; text-decoration: none; font-weight: 600;">Manage Volumes &nearr;</a>
        </div>
        <input type="hidden" id="capacity" name="capacity" value="<?= htmlspecialchars($capacity) ?>" />
        <div class="chip-group" id="capacity-chips">
          <?php foreach ($volumesList as $v): ?>
            <?php $vVal = $v['display_label'] ?? ($v['name'] ?? $v['value']); ?>
            <div class="select-chip <?= strtolower($capacity) === strtolower($vVal) ? 'selected' : '' ?>" onclick="selectChip('capacity-chips', this, 'capacity', '<?= htmlspecialchars($vVal) ?>')">
              <?= htmlspecialchars($vVal) ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- NECK CHIPS (DATABASE BACKED) -->
      <div class="form-group">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <label class="form-label">Neck Finish / Thread Size</label>
          <a href="/admin/categories.php?tab=necks" target="_blank" style="color: #38bdf8; font-size: 0.78rem; text-decoration: none; font-weight: 600;">Manage Necks &nearr;</a>
        </div>
        <input type="hidden" id="neck" name="neck" value="<?= htmlspecialchars($neck) ?>" />
        <div class="chip-group" id="neck-chips">
          <?php foreach ($necksList as $nk): ?>
            <?php $nkVal = $nk['name']; ?>
            <div class="select-chip <?= strtolower($neck) === strtolower($nkVal) ? 'selected' : '' ?>" onclick="selectChip('neck-chips', this, 'neck', '<?= htmlspecialchars($nkVal) ?>')">
              <?= htmlspecialchars($nkVal) ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="form-row">
        <div>
          <label for="weight" class="form-label">Gram Weight</label>
          <input type="text" id="weight" name="weight" class="form-control-smart" value="<?= htmlspecialchars($weight) ?>" />
        </div>
        <div>
          <label for="moq" class="form-label">Minimum Order Quantity (MOQ)</label>
          <input type="text" id="moq" name="moq" class="form-control-smart" value="<?= htmlspecialchars($moq) ?>" />
        </div>
      </div>
    </div>

    <!-- 3. PRICING & STOCK -->
    <div class="form-card">
      <div class="card-header-smart">
        <span>③ Pricing &amp; Stock</span>
        <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 400;">Commercial Attributes</span>
      </div>

      <div class="form-row-3">
        <div>
          <label for="regularPrice" class="form-label">Regular Price (₹)</label>
          <input type="number" step="0.01" min="0" id="regularPrice" name="regularPrice" class="form-control-smart" value="<?= htmlspecialchars($regularPrice) ?>" />
        </div>
        <div>
          <label for="salePrice" class="form-label">Sale Price (₹ Optional)</label>
          <input type="number" step="0.01" min="0" id="salePrice" name="salePrice" class="form-control-smart" value="<?= htmlspecialchars($salePrice) ?>" />
        </div>
        <div>
          <label for="stockQty" class="form-label">Stock Quantity (pcs) *</label>
          <input type="number" min="0" id="stockQty" name="stockQty" class="form-control-smart" value="<?= htmlspecialchars($stockQty) ?>" required />
        </div>
      </div>

      <div class="form-row-3">
        <div>
          <label for="stockStatus" class="form-label">Stock Status</label>
          <select id="stockStatus" name="stockStatus" class="form-control-smart">
            <option value="in_stock" <?= $stockStatus === 'in_stock' ? 'selected' : '' ?>>In Stock</option>
            <option value="out_of_stock" <?= $stockStatus === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
            <option value="backorder" <?= $stockStatus === 'backorder' ? 'selected' : '' ?>>Backorder</option>
          </select>
        </div>

        <div>
          <label for="status" class="form-label">Publication Status *</label>
          <select id="status" name="status" class="form-control-smart" required>
            <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Archived</option>
          </select>
        </div>

        <div>
          <label for="isFeatured" class="form-label">Featured Showcase</label>
          <select id="isFeatured" name="isFeatured" class="form-control-smart">
            <option value="0" <?= !$isFeatured ? 'selected' : '' ?>>OFF - Standard</option>
            <option value="1" <?= $isFeatured ? 'selected' : '' ?>>ON - Showcase on Homepage</option>
          </select>
        </div>
      </div>
    </div>

    <!-- 4. MEDIA GALLERY -->
    <div class="form-card">
      <div class="card-header-smart">
        <span>④ Product Images (Slots 1–4)</span>
        <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 400;">Visual Upload Slots</span>
      </div>

      <div class="media-grid">
        <!-- SLOT 1 -->
        <div class="media-slot" onclick="document.getElementById('file_img1').click()">
          <input type="hidden" name="existing_img1" value="<?= htmlspecialchars($img1) ?>" />
          <input type="file" name="product_img1" id="file_img1" accept="image/*" style="opacity: 0; position: absolute; top:0; left:0; width:100%; height:100%; cursor: pointer;" onchange="previewSlot(this, 'prev_img1')" />
          <span style="font-size: 0.75rem; font-weight: 700; color: #38bdf8; display: block; margin-bottom: 6px;">IMAGE 1 (PRIMARY)</span>
          <img id="prev_img1" src="<?= htmlspecialchars($img1) ?>" class="media-slot-preview" alt="Slot 1" />
          <span style="font-size: 0.75rem; color: #94a3b8;">Click to Replace</span>
        </div>

        <!-- SLOT 2 -->
        <div class="media-slot" onclick="document.getElementById('file_img2').click()">
          <input type="hidden" name="existing_img2" value="<?= htmlspecialchars($img2) ?>" />
          <input type="file" name="product_img2" id="file_img2" accept="image/*" style="opacity: 0; position: absolute; top:0; left:0; width:100%; height:100%; cursor: pointer;" onchange="previewSlot(this, 'prev_img2')" />
          <span style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; display: block; margin-bottom: 6px;">IMAGE 2</span>
          <img id="prev_img2" src="<?= htmlspecialchars($img2) ?>" class="media-slot-preview" alt="Slot 2" />
          <span style="font-size: 0.75rem; color: #94a3b8;">Click to Replace</span>
        </div>

        <!-- SLOT 3 -->
        <div class="media-slot" onclick="document.getElementById('file_img3').click()">
          <input type="hidden" name="existing_img3" value="<?= htmlspecialchars($img3) ?>" />
          <input type="file" name="product_img3" id="file_img3" accept="image/*" style="opacity: 0; position: absolute; top:0; left:0; width:100%; height:100%; cursor: pointer;" onchange="previewSlot(this, 'prev_img3')" />
          <span style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; display: block; margin-bottom: 6px;">IMAGE 3</span>
          <img id="prev_img3" src="<?= htmlspecialchars($img3) ?>" class="media-slot-preview" alt="Slot 3" />
          <span style="font-size: 0.75rem; color: #94a3b8;">Click to Replace</span>
        </div>

        <!-- SLOT 4 -->
        <div class="media-slot" onclick="document.getElementById('file_img4').click()">
          <input type="hidden" name="existing_img4" value="<?= htmlspecialchars($img4) ?>" />
          <input type="file" name="product_img4" id="file_img4" accept="image/*" style="opacity: 0; position: absolute; top:0; left:0; width:100%; height:100%; cursor: pointer;" onchange="previewSlot(this, 'prev_img4')" />
          <span style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; display: block; margin-bottom: 6px;">IMAGE 4</span>
          <img id="prev_img4" src="<?= htmlspecialchars($img4) ?>" class="media-slot-preview" alt="Slot 4" />
          <span style="font-size: 0.75rem; color: #94a3b8;">Click to Replace</span>
        </div>
      </div>
    </div>

    <!-- 5. DESCRIPTIONS & AUTO GENERATOR -->
    <div class="form-card">
      <div class="card-header-smart">
        <span>⑤ Description &amp; Content</span>
        <button type="button" class="btn-auto-gen" onclick="autoGenerateDescription()">✨ Auto Generate Description</button>
      </div>

      <div class="form-group">
        <label for="shortDescription" class="form-label">Short Summary Description</label>
        <textarea id="shortDescription" name="shortDescription" rows="2" class="form-control-smart"><?= htmlspecialchars($product['shortDescription'] ?? ($product['description'] ?? '')) ?></textarea>
      </div>

      <div class="form-group">
        <label for="description" class="form-label">Full Technical Description &amp; Features</label>
        <textarea id="description" name="description" rows="5" class="form-control-smart"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
      </div>
    </div>

    <!-- SUBMIT BAR -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 24px; padding-top: 18px; border-top: 1px solid #334155; flex-wrap: wrap; gap: 12px;">
      <a href="/admin/products.php" class="btn-cancel" style="min-height: 48px; display: inline-flex; align-items: center; justify-content: center; flex: 1; min-width: 140px;">Cancel &amp; Return</a>

      <button type="submit" class="btn-submit-publish" onclick="document.getElementById('status').value = 'published';" style="min-height: 48px; flex: 2; min-width: 200px;">
        SAVE CHANGES &amp; PUBLISH &rarr;
      </button>
    </div>

  </form>
</div>

<script>
const seriesData = <?= json_encode($series) ?>;
const currentSerie = <?= json_encode($product['serie'] ?? '') ?>;

function updateSubcategories() {
  const catSlug = document.getElementById('categorySlug').value;
  const serieSelect = document.getElementById('serie');
  serieSelect.innerHTML = '';

  const filtered = seriesData.filter(s => s.categorySlug === catSlug);
  if (filtered.length > 0) {
    filtered.forEach(s => {
      const opt = document.createElement('option');
      const val = s.serie || s.name;
      opt.value = val;
      opt.textContent = val;
      if (val === currentSerie) opt.selected = true;
      serieSelect.appendChild(opt);
    });
  } else {
    const opt = document.createElement('option');
    opt.value = 'General Series';
    opt.textContent = 'General Series';
    serieSelect.appendChild(opt);
  }
}

function selectChip(groupId, chipElem, inputId, val) {
  const group = document.getElementById(groupId);
  group.querySelectorAll('.select-chip').forEach(c => c.classList.remove('selected'));
  chipElem.classList.add('selected');
  document.getElementById(inputId).value = val;
}

function autoGenerateDescription() {
  const name = document.getElementById('name').value || 'Primary Packaging Component';
  const mat = document.getElementById('material').value || 'PET';
  const cap = document.getElementById('capacity').value || '250ml';
  const neck = document.getElementById('neck').value || '24/410';

  const shortDesc = `Precision engineered ${cap} ${name} manufactured in high-grade ${mat} with ${neck} neck finish.`;
  const fullDesc = `The ${name} is a premium ${cap} packaging solution engineered for exceptional durability, chemical compatibility, and shelf presence.

Key Specifications & Attributes:
- Volume Capacity: ${cap}
- Material Polymer: ${mat} (100% Recyclable)
- Neck Finish: ${neck} tamper-evident closure standard
- Quality Standard: Manufactured under Class 10,000 cleanroom conditions with ISO 9001 quality compliance.
- Ideal Applications: Cosmetics, personal care serums, pharmaceutical formulations, and specialized liquids.`;

  document.getElementById('shortDescription').value = shortDesc;
  document.getElementById('description').value = fullDesc;
}

function previewSlot(input, imgId) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById(imgId).src = e.target.result;
    }
    reader.readAsDataURL(input.files[0]);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  updateSubcategories();
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
