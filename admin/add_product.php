<?php
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/ProductModel.php';

checkAdminAuth();

$categories = ProductModel::getCategories();
$series = ProductModel::getSeries();
$dbOptions = ProductModel::getAllOptions(true);

$materialsList = $dbOptions['materials'] ?? [];
$volumesList = $dbOptions['volumes'] ?? [];
$necksList = $dbOptions['necks'] ?? [];
$appsList = $dbOptions['applications'] ?? [];
$featsList = $dbOptions['features'] ?? [];
$audiencesList = $dbOptions['audiences'] ?? [];

$message = $_GET['msg'] ?? '';
$error = $_GET['err'] ?? '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
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
  
  /* FORM INPUTS */
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

  /* MEDIA GALLERY SLOTS */
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
    transition: background 0.15s;
  }
  .btn-auto-gen:hover {
    background: rgba(14, 165, 233, 0.3);
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
    transition: transform 0.15s, opacity 0.15s;
  }
  .btn-submit-publish:hover {
    opacity: 0.92;
    transform: translateY(-1px);
  }
  .btn-save-draft {
    background: #334155;
    color: #cbd5e1;
    border: none;
    padding: 16px 24px;
    font-size: 0.95rem;
    font-weight: 700;
    border-radius: 10px;
    cursor: pointer;
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
        Smart Add Product — <span style="color: #eab308;">Select More, Type Less</span>
      </h1>
      <p style="color: #94a3b8; margin: 4px 0 0 0; font-size: 0.88rem;">
        Fast 1-minute product uploader for client catalogue management
      </p>
    </div>
  </div>

  <?php if (!empty($message)): ?>
    <div class="alert-success"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form id="smartProductForm" action="save_product.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>" />

    <!-- 1. PRODUCT BASICS -->
    <div class="form-card">
      <div class="card-header-smart">
        <span>① Product Basics</span>
        <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 400;">Essential Name &amp; Auto-SKU</span>
      </div>

      <div class="form-group">
        <label for="name" class="form-label">Product Name / Title *</label>
        <input type="text" id="name" name="name" class="form-control-smart" placeholder="e.g. 250ml Boston Round PET Bottle" required oninput="autoGenerateFields()" />
      </div>

      <div class="form-row">
        <div>
          <label for="articleNo" class="form-label">SKU / Article Number</label>
          <div style="display: flex; gap: 8px;">
            <input type="text" id="articleNo" name="articleNo" class="form-control-smart" placeholder="Auto generated..." />
            <button type="button" class="btn-auto-gen" onclick="generateSKU()">Auto SKU</button>
          </div>
        </div>
        <div>
          <label for="gender" class="form-label">Target Audience / Gender</label>
          <select id="gender" name="gender" class="form-control-smart">
            <option value="Unisex">Unisex / General</option>
            <option value="Men">Men</option>
            <option value="Women">Women</option>
          </select>
        </div>
      </div>
    </div>

    <!-- 2. CLASSIFICATION & SPECIFICATIONS (CHIPS) -->
    <div class="form-card">
      <div class="card-header-smart">
        <span>② Classification &amp; Specifications</span>
        <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 400;">Clickable Category, Material &amp; Volume</span>
      </div>

      <div class="form-row">
        <div>
          <label for="categorySlug" class="form-label">Packaging Category *</label>
          <select id="categorySlug" name="categorySlug" class="form-control-smart" required onchange="updateSubcategories()">
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label for="serie" class="form-label">Series / Subcategory</label>
          <select id="serie" name="serie" class="form-control-smart">
            <!-- Dynamically populated by JS -->
          </select>
        </div>
      </div>

      <!-- MATERIAL CHIPS (DATABASE BACKED) -->
      <div class="form-group">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <label class="form-label">Material Polymer (Click to select)</label>
          <a href="/admin/categories.php?tab=materials" target="_blank" style="color: #38bdf8; font-size: 0.78rem; text-decoration: none; font-weight: 600;">Manage Materials &nearr;</a>
        </div>
        <input type="hidden" id="material" name="material" value="PET" />
        <div class="chip-group" id="material-chips">
          <?php foreach ($materialsList as $idx => $m): ?>
            <?php $mVal = $m['name'] ?? $m['short_label']; ?>
            <div class="select-chip <?= $idx === 0 ? 'selected' : '' ?>" onclick="selectChip('material-chips', this, 'material', '<?= htmlspecialchars($mVal) ?>')">
              <?= htmlspecialchars($mVal) ?>
            </div>
          <?php endforeach; ?>
          <div class="select-chip" onclick="selectChipCustom('material-chips', this, 'material')">+ Custom</div>
        </div>
        <input type="text" id="material_custom" class="form-control-smart" placeholder="Specify custom material..." style="display: none; margin-top: 8px;" oninput="document.getElementById('material').value = this.value" />
      </div>

      <!-- VOLUME / CAPACITY CHIPS (DATABASE BACKED) -->
      <div class="form-group">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <label class="form-label">Volume / Capacity (Click to select)</label>
          <a href="/admin/categories.php?tab=volumes" target="_blank" style="color: #38bdf8; font-size: 0.78rem; text-decoration: none; font-weight: 600;">Manage Volumes &nearr;</a>
        </div>
        <input type="hidden" id="capacity" name="capacity" value="250ml" />
        <div class="chip-group" id="capacity-chips">
          <?php foreach ($volumesList as $v): ?>
            <?php $vVal = $v['display_label'] ?? ($v['name'] ?? $v['value']); ?>
            <div class="select-chip <?= $vVal === '250ml' ? 'selected' : '' ?>" onclick="selectChip('capacity-chips', this, 'capacity', '<?= htmlspecialchars($vVal) ?>')">
              <?= htmlspecialchars($vVal) ?>
            </div>
          <?php endforeach; ?>
          <div class="select-chip" onclick="selectChipCustom('capacity-chips', this, 'capacity')">+ Custom</div>
        </div>
        <input type="text" id="capacity_custom" class="form-control-smart" placeholder="Specify custom volume (e.g. 150ml)..." style="display: none; margin-top: 8px;" oninput="document.getElementById('capacity').value = this.value" />
      </div>

      <!-- NECK FINISH CHIPS (DATABASE BACKED) -->
      <div class="form-group">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <label class="form-label">Neck Finish / Thread Size</label>
          <a href="/admin/categories.php?tab=necks" target="_blank" style="color: #38bdf8; font-size: 0.78rem; text-decoration: none; font-weight: 600;">Manage Necks &nearr;</a>
        </div>
        <input type="hidden" id="neck" name="neck" value="24/410" />
        <div class="chip-group" id="neck-chips">
          <?php foreach ($necksList as $nk): ?>
            <?php $nkVal = $nk['name']; ?>
            <div class="select-chip <?= $nkVal === '24/410' ? 'selected' : '' ?>" onclick="selectChip('neck-chips', this, 'neck', '<?= htmlspecialchars($nkVal) ?>')">
              <?= htmlspecialchars($nkVal) ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- APPLICATION CHECKBOXES (DATABASE BACKED) -->
      <div class="form-group">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <label class="form-label">Suitable Market Applications (Click all that apply)</label>
          <a href="/admin/categories.php?tab=applications" target="_blank" style="color: #38bdf8; font-size: 0.78rem; text-decoration: none; font-weight: 600;">Manage Applications &nearr;</a>
        </div>
        <div class="chip-group" id="application-chips">
          <?php foreach ($appsList as $idx => $app): ?>
            <div class="select-chip <?= $idx === 0 ? 'selected' : '' ?>" onclick="toggleMultiChip(this)">
              <?= htmlspecialchars($app['name']) ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- FEATURES CHECKBOXES (DATABASE BACKED) -->
      <div class="form-group">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <label class="form-label">Key Features &amp; Compliance</label>
          <a href="/admin/categories.php?tab=features" target="_blank" style="color: #38bdf8; font-size: 0.78rem; text-decoration: none; font-weight: 600;">Manage Features &nearr;</a>
        </div>
        <div class="chip-group" id="features-chips">
          <?php foreach ($featsList as $idx => $feat): ?>
            <div class="select-chip <?= $idx < 2 ? 'selected' : '' ?>" onclick="toggleMultiChip(this)">
              <?= htmlspecialchars($feat['name']) ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="form-row">
        <div>
          <label for="weight" class="form-label">Gram Weight</label>
          <input type="text" id="weight" name="weight" class="form-control-smart" placeholder="e.g. 23 g" value="23 g" />
        </div>
        <div>
          <label for="moq" class="form-label">Minimum Order Quantity (MOQ)</label>
          <input type="text" id="moq" name="moq" class="form-control-smart" placeholder="e.g. 5,000 pcs" value="5,000 pcs" />
        </div>
      </div>
    </div>

    <!-- 3. PRICING & STOCK -->
    <div class="form-card">
      <div class="card-header-smart">
        <span>③ Pricing &amp; Stock</span>
        <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 400;">Unit Pricing &amp; Inventory</span>
      </div>

      <div class="form-row-3">
        <div>
          <label for="regularPrice" class="form-label">Regular Price (₹)</label>
          <input type="number" step="0.01" min="0" id="regularPrice" name="regularPrice" class="form-control-smart" placeholder="e.g. 12.50" value="12.50" />
        </div>
        <div>
          <label for="salePrice" class="form-label">Sale Price (₹ Optional)</label>
          <input type="number" step="0.01" min="0" id="salePrice" name="salePrice" class="form-control-smart" placeholder="Discount price" />
        </div>
        <div>
          <label for="stockQty" class="form-label">Stock Quantity (pcs) *</label>
          <input type="number" min="0" id="stockQty" name="stockQty" class="form-control-smart" value="1000" required />
        </div>
      </div>

      <div class="form-row-3">
        <div>
          <label for="stockStatus" class="form-label">Stock Status</label>
          <select id="stockStatus" name="stockStatus" class="form-control-smart">
            <option value="in_stock">In Stock</option>
            <option value="out_of_stock">Out of Stock</option>
            <option value="backorder">Backorder</option>
          </select>
        </div>

        <div>
          <label for="status" class="form-label">Publication Status *</label>
          <select id="status" name="status" class="form-control-smart" required>
            <option value="published">Published (Live on Website)</option>
            <option value="draft">Draft (Hidden)</option>
            <option value="archived">Archived</option>
          </select>
        </div>

        <div>
          <label for="isFeatured" class="form-label">Featured Showcase</label>
          <select id="isFeatured" name="isFeatured" class="form-control-smart">
            <option value="0">OFF - Standard</option>
            <option value="1">ON - Showcase on Homepage</option>
          </select>
        </div>
      </div>
    </div>

    <!-- 4. MEDIA GALLERY (SLOTS 1-4) -->
    <div class="form-card">
      <div class="card-header-smart">
        <span>④ Product Images (Slots 1–4)</span>
        <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 400;">Visual Upload Slots</span>
      </div>

      <div class="media-grid">
        <!-- SLOT 1 -->
        <div class="media-slot" onclick="document.getElementById('file_img1').click()">
          <input type="file" name="product_img1" id="file_img1" accept="image/*" style="opacity: 0; position: absolute; top:0; left:0; width:100%; height:100%; cursor: pointer;" onchange="previewSlot(this, 'prev_img1')" />
          <span style="font-size: 0.75rem; font-weight: 700; color: #38bdf8; display: block; margin-bottom: 6px;">IMAGE 1 (PRIMARY)</span>
          <img id="prev_img1" src="/brand_assets/pet_bottles/100ml_boston_white.jpeg" class="media-slot-preview" alt="Slot 1" />
          <span style="font-size: 0.75rem; color: #94a3b8;">Click to Upload</span>
        </div>

        <!-- SLOT 2 -->
        <div class="media-slot" onclick="document.getElementById('file_img2').click()">
          <input type="file" name="product_img2" id="file_img2" accept="image/*" style="opacity: 0; position: absolute; top:0; left:0; width:100%; height:100%; cursor: pointer;" onchange="previewSlot(this, 'prev_img2')" />
          <span style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; display: block; margin-bottom: 6px;">IMAGE 2</span>
          <img id="prev_img2" src="/logo_svg.svg" class="media-slot-preview" alt="Slot 2" />
          <span style="font-size: 0.75rem; color: #94a3b8;">Click to Upload</span>
        </div>

        <!-- SLOT 3 -->
        <div class="media-slot" onclick="document.getElementById('file_img3').click()">
          <input type="file" name="product_img3" id="file_img3" accept="image/*" style="opacity: 0; position: absolute; top:0; left:0; width:100%; height:100%; cursor: pointer;" onchange="previewSlot(this, 'prev_img3')" />
          <span style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; display: block; margin-bottom: 6px;">IMAGE 3</span>
          <img id="prev_img3" src="/logo_svg.svg" class="media-slot-preview" alt="Slot 3" />
          <span style="font-size: 0.75rem; color: #94a3b8;">Click to Upload</span>
        </div>

        <!-- SLOT 4 -->
        <div class="media-slot" onclick="document.getElementById('file_img4').click()">
          <input type="file" name="product_img4" id="file_img4" accept="image/*" style="opacity: 0; position: absolute; top:0; left:0; width:100%; height:100%; cursor: pointer;" onchange="previewSlot(this, 'prev_img4')" />
          <span style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; display: block; margin-bottom: 6px;">IMAGE 4</span>
          <img id="prev_img4" src="/logo_svg.svg" class="media-slot-preview" alt="Slot 4" />
          <span style="font-size: 0.75rem; color: #94a3b8;">Click to Upload</span>
        </div>
      </div>
    </div>

    <!-- 5. DESCRIPTIONS & AUTO GENERATOR -->
    <div class="form-card">
      <div class="card-header-smart">
        <span>⑤ Description &amp; Content Generator</span>
        <button type="button" class="btn-auto-gen" onclick="autoGenerateDescription()">✨ Auto Generate Description</button>
      </div>

      <div class="form-group">
        <label for="shortDescription" class="form-label">Short Summary Description</label>
        <textarea id="shortDescription" name="shortDescription" rows="2" class="form-control-smart" placeholder="Auto generated or manual summary..."></textarea>
      </div>

      <div class="form-group">
        <label for="description" class="form-label">Full Technical Description &amp; Features</label>
        <textarea id="description" name="description" rows="5" class="form-control-smart" placeholder="Detailed product specifications..."></textarea>
      </div>
    </div>

    <!-- SUBMIT BAR -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 24px; padding-top: 18px; border-top: 1px solid #334155; flex-wrap: wrap; gap: 12px;">
      <button type="submit" name="submit_draft" class="btn-save-draft" onclick="document.getElementById('status').value = 'draft';" style="min-height: 48px; flex: 1; min-width: 140px;">Save as Draft</button>

      <button type="submit" class="btn-submit-publish" onclick="document.getElementById('status').value = 'published';" style="min-height: 48px; flex: 2; min-width: 200px;">
        PUBLISH PRODUCT TO LIVE CATALOGUE &rarr;
      </button>
    </div>

  </form>
</div>

<script>
const seriesData = <?= json_encode($series) ?>;

function updateSubcategories() {
  const catSlug = document.getElementById('categorySlug').value;
  const serieSelect = document.getElementById('serie');
  serieSelect.innerHTML = '';

  const filtered = seriesData.filter(s => s.categorySlug === catSlug);
  if (filtered.length > 0) {
    filtered.forEach(s => {
      const opt = document.createElement('option');
      opt.value = s.serie || s.name;
      opt.textContent = s.serie || s.name;
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

  const customInput = document.getElementById(inputId + '_custom');
  if (customInput) customInput.style.display = 'none';
}

function selectChipCustom(groupId, chipElem, inputId) {
  const group = document.getElementById(groupId);
  group.querySelectorAll('.select-chip').forEach(c => c.classList.remove('selected'));
  chipElem.classList.add('selected');

  const customInput = document.getElementById(inputId + '_custom');
  if (customInput) {
    customInput.style.display = 'block';
    customInput.focus();
  }
}

function toggleMultiChip(chipElem) {
  chipElem.classList.toggle('selected');
}

function generateSKU() {
  const catSlug = document.getElementById('categorySlug').value;
  const prefix = 'TN-' + catSlug.substring(0, 3).toUpperCase();
  const randNum = Math.floor(1000 + Math.random() * 9000);
  document.getElementById('articleNo').value = prefix + '-' + randNum;
}

function autoGenerateFields() {
  const name = document.getElementById('name').value;
  if (!document.getElementById('articleNo').value && name.length > 3) {
    generateSKU();
  }
}

function autoGenerateDescription() {
  const name = document.getElementById('name').value || 'Primary Packaging Component';
  const catName = document.getElementById('categorySlug').selectedOptions[0].text;
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

// Init subcategories on page load
document.addEventListener('DOMContentLoaded', () => {
  updateSubcategories();
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
