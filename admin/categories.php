<?php
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/ProductModel.php';

$allOptions = ProductModel::getAllOptions();
$auditLogs = ProductModel::getOptionAuditLogs(30);

$message = $_GET['msg'] ?? '';
$error = $_GET['err'] ?? '';

// Handle Direct Form Posts if JS is disabled
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $group = $_POST['group'] ?? 'categories';
    $name = trim($_POST['name'] ?? ($_POST['display_label'] ?? ''));

    if ($action === 'save_option') {
        if (!empty($name)) {
            $id = (int)($_POST['id'] ?? 0);
            $data = [
                'id' => $id,
                'name' => $name,
                'status' => $_POST['status'] ?? 'active',
                'display_order' => (int)($_POST['display_order'] ?? 1)
            ];
            if (isset($_POST['short_label'])) $data['short_label'] = trim($_POST['short_label']);
            if (isset($_POST['display_label'])) $data['display_label'] = trim($_POST['display_label']);
            if (isset($_POST['value'])) $data['value'] = trim($_POST['value']);
            if (isset($_POST['unit'])) $data['unit'] = trim($_POST['unit']);
            if (isset($_POST['hex_code'])) $data['hex_code'] = trim($_POST['hex_code']);
            if (isset($_POST['slug'])) $data['slug'] = trim($_POST['slug']);
            if (isset($_POST['headline'])) $data['headline'] = trim($_POST['headline']);
            if (isset($_POST['desc'])) $data['desc'] = trim($_POST['desc']);
            if (isset($_POST['image'])) $data['image'] = trim($_POST['image']);
            if (isset($_POST['coverImage'])) $data['coverImage'] = trim($_POST['coverImage']);

            // Handle direct file upload
            if (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                $tmpFile = $_FILES['image_file']['tmp_name'];
                $origName = $_FILES['image_file']['name'];
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'])) {
                    $subDir = ($group === 'industries') ? 'industries' : 'categories';
                    $destFolder = dirname(__DIR__) . "/public/uploads/{$subDir}";
                    if (!is_dir($destFolder)) {
                        mkdir($destFolder, 0777, true);
                    }
                    $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($origName, PATHINFO_FILENAME));
                    $newFileName = time() . '_' . $safeName . '.' . $ext;
                    if (move_uploaded_file($tmpFile, "{$destFolder}/{$newFileName}")) {
                        $data['image'] = "/uploads/{$subDir}/{$newFileName}";
                        if (empty($data['coverImage'])) $data['coverImage'] = $data['image'];
                    }
                }
            }

            if (empty($data['slug'])) {
                $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            }

            ProductModel::saveOption($group, $data);
            header("Location: /admin/categories?msg=" . urlencode("Saved '{$name}' successfully!") . "&tab=" . urlencode($group));
            exit();
        }
    }
}

$activeTab = $_GET['tab'] ?? 'categories';
?>

<style>
  .admin-options-container {
    max-width: 1280px;
    margin: 0 auto;
  }
  .options-nav-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 12px;
    padding: 8px;
    margin-bottom: 24px;
  }
  .tab-btn {
    background: transparent;
    border: none;
    color: #94a3b8;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 0.88rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.18s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 44px;
  }
  .tab-btn:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.05);
  }
  .tab-btn.active {
    background: #0284c7;
    color: #ffffff;
    box-shadow: 0 0 12px rgba(2, 132, 199, 0.3);
  }
  .tab-badge {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    font-size: 0.72rem;
    padding: 2px 6px;
    border-radius: 10px;
    font-weight: 800;
  }
  .tab-content-panel {
    display: none;
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 16px;
    padding: 28px;
  }
  .tab-content-panel.active {
    display: block;
  }

  .panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #334155;
    flex-wrap: wrap;
    gap: 12px;
  }
  .panel-title {
    font-size: 1.3rem;
    font-weight: 800;
    color: #f8fafc;
    margin: 0;
  }
  .panel-search {
    background: #0f172a;
    border: 1px solid #475569;
    border-radius: 8px;
    padding: 8px 14px;
    color: #ffffff;
    font-size: 0.88rem;
    width: 220px;
  }
  .btn-add-option {
    background: #0284c7;
    color: #ffffff;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.88rem;
    cursor: pointer;
    transition: background 0.15s;
    min-height: 44px;
  }
  .btn-add-option:hover {
    background: #0369a1;
  }

  .options-table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
  .options-table {
    width: 100%;
    border-collapse: collapse;
  }
  .options-table th {
    text-align: left;
    padding: 12px 14px;
    font-size: 0.78rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    border-bottom: 1px solid #334155;
  }
  .options-table td {
    padding: 14px;
    font-size: 0.9rem;
    color: #cbd5e1;
    border-bottom: 1px solid rgba(51, 65, 85, 0.5);
    vertical-align: middle;
  }
  .status-badge-active {
    background: rgba(52, 211, 153, 0.15);
    color: #34d399;
    border: 1px solid rgba(52, 211, 153, 0.3);
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 700;
  }
  .status-badge-inactive {
    background: rgba(148, 163, 184, 0.15);
    color: #94a3b8;
    border: 1px solid rgba(148, 163, 184, 0.3);
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 700;
  }
  .btn-act {
    background: #0f172a;
    border: 1px solid #475569;
    color: #cbd5e1;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    min-height: 38px;
    display: inline-flex;
    align-items: center;
  }
  .btn-act:hover {
    border-color: #38bdf8;
    color: #ffffff;
  }
  .btn-act-danger {
    color: #ef4444;
    border-color: rgba(239, 68, 68, 0.4);
  }
  .btn-act-danger:hover {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
  }

  /* MODAL */
  .modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 12px;
  }
  .modal-overlay.open {
    display: flex;
  }
  .modal-box {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 16px;
    width: 100%;
    max-width: 540px;
    padding: 24px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
    box-sizing: border-box;
    max-height: 90vh;
    overflow-y: auto;
  }

  /* Preset thumbnails */
  .preset-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-top: 8px;
  }
  .preset-thumb {
    border: 1px solid #334155;
    border-radius: 6px;
    padding: 4px;
    background: #0f172a;
    cursor: pointer;
    transition: all 0.15s;
    text-align: center;
  }
  .preset-thumb:hover {
    border-color: #38bdf8;
    transform: translateY(-2px);
  }
  .preset-thumb img {
    width: 100%;
    height: 48px;
    object-fit: contain;
    display: block;
    border-radius: 4px;
  }
  .preset-thumb span {
    font-size: 0.68rem;
    color: #94a3b8;
    display: block;
    margin-top: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  @media (max-width: 768px) {
    .options-nav-tabs {
      overflow-x: auto;
      white-space: nowrap;
      flex-wrap: nowrap;
      padding: 6px;
      -webkit-overflow-scrolling: touch;
    }
    .tab-btn {
      flex-shrink: 0;
    }
    .tab-content-panel {
      padding: 16px 12px !important;
    }
    .panel-search {
      width: 100% !important;
    }
    .btn-add-option {
      width: 100% !important;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  }
</style>

<div class="admin-options-container">
  
  <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
      <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0; color: #f8fafc;">
        Category &amp; Product Options Management
      </h1>
      <p style="color: #94a3b8; margin: 4px 0 0 0; font-size: 0.88rem;">
        Single source of truth for Product Categories, Industry Categories, Series, Materials, Volumes, and Specifications
      </p>
    </div>
  </div>

  <?php if (!empty($message)): ?>
    <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 14px 18px; border-radius: 10px; font-size: 0.9rem; margin-bottom: 24px;">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <!-- TABS NAV -->
  <div class="options-nav-tabs">
    <button class="tab-btn <?= $activeTab === 'categories' ? 'active' : '' ?>" data-tab="categories" onclick="switchTab('categories', this)">
      Product Categories <span class="tab-badge"><?= count($allOptions['categories'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'industries' ? 'active' : '' ?>" data-tab="industries" onclick="switchTab('industries', this)">
      Industry Categories <span class="tab-badge"><?= count($allOptions['industries'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'subcategories' ? 'active' : '' ?>" data-tab="subcategories" onclick="switchTab('subcategories', this)">
      Series / Subcategories <span class="tab-badge"><?= count($allOptions['subcategories'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'materials' ? 'active' : '' ?>" data-tab="materials" onclick="switchTab('materials', this)">
      Materials <span class="tab-badge"><?= count($allOptions['materials'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'volumes' ? 'active' : '' ?>" data-tab="volumes" onclick="switchTab('volumes', this)">
      Volumes / Capacities <span class="tab-badge"><?= count($allOptions['volumes'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'necks' ? 'active' : '' ?>" data-tab="necks" onclick="switchTab('necks', this)">
      Neck Finishes <span class="tab-badge"><?= count($allOptions['necks'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'applications' ? 'active' : '' ?>" data-tab="applications" onclick="switchTab('applications', this)">
      Applications <span class="tab-badge"><?= count($allOptions['applications'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'features' ? 'active' : '' ?>" data-tab="features" onclick="switchTab('features', this)">
      Features <span class="tab-badge"><?= count($allOptions['features'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'shapes' ? 'active' : '' ?>" data-tab="shapes" onclick="switchTab('shapes', this)">
      Product Shapes <span class="tab-badge"><?= count($allOptions['shapes'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'colours' ? 'active' : '' ?>" data-tab="colours" onclick="switchTab('colours', this)">
      Colours <span class="tab-badge"><?= count($allOptions['colours'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'closures' ? 'active' : '' ?>" data-tab="closures" onclick="switchTab('closures', this)">
      Closures <span class="tab-badge"><?= count($allOptions['closures'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'audiences' ? 'active' : '' ?>" data-tab="audiences" onclick="switchTab('audiences', this)">
      Target Audience <span class="tab-badge"><?= count($allOptions['audiences'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'audit' ? 'active' : '' ?>" data-tab="audit" onclick="switchTab('audit', this)">
      Audit Logs <span class="tab-badge"><?= count($auditLogs) ?></span>
    </button>
  </div>

  <!-- TAB PANELS -->

  <!-- 1. PRODUCT CATEGORIES -->
  <div id="panel-categories" class="tab-content-panel <?= $activeTab === 'categories' ? 'active' : '' ?>">
    <div class="panel-header">
      <div>
        <h3 class="panel-title">Product Categories</h3>
        <span style="font-size: 0.8rem; color: #94a3b8;">Main packaging classification families with thumbnail cards</span>
      </div>
      <div style="display: flex; gap: 12px; align-items: center;">
        <input type="text" class="panel-search" placeholder="Search categories..." onkeyup="filterTable('table-categories', this.value)" />
        <button class="btn-add-option" onclick="openAddModal('categories', 'Category')">+ Add Category</button>
      </div>
    </div>
    <div class="options-table-wrapper">
      <table class="options-table" id="table-categories">
        <thead>
          <tr>
            <th style="width: 70px;">Image</th>
            <th>Category Name</th>
            <th>Slug</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($allOptions['categories'] ?? [] as $item): ?>
            <?php $status = $item['status'] ?? 'active'; ?>
            <tr>
              <td>
                <img src="<?= htmlspecialchars($item['image'] ?? '/logo_svg.svg') ?>" alt="" style="width: 48px; height: 48px; object-fit: contain; border-radius: 8px; background: #0f172a; border: 1px solid #334155; padding: 2px;" onerror="this.onerror=null;this.src='/logo_svg.svg';" />
              </td>
              <td>
                <strong><?= htmlspecialchars($item['name']) ?></strong>
                <?php if (!empty($item['desc'])): ?>
                  <div style="font-size: 0.78rem; color: #94a3b8; max-width: 320px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; margin-top: 2px;">
                    <?= htmlspecialchars($item['desc']) ?>
                  </div>
                <?php endif; ?>
              </td>
              <td><code><?= htmlspecialchars($item['slug'] ?? '') ?></code></td>
              <td>
                <span class="<?= $status === 'active' ? 'status-badge-active' : 'status-badge-inactive' ?>">
                  <?= ucfirst($status) ?>
                </span>
              </td>
              <td>
                <div style="display: flex; gap: 6px;">
                  <button class="btn-act" onclick="editOptionItem('categories', <?= htmlspecialchars(json_encode($item)) ?>)">Edit</button>
                  <button class="btn-act" onclick="toggleOptionStatus('categories', <?= (int)($item['id'] ?? 0) ?>, '<?= $status === 'active' ? 'inactive' : 'active' ?>')">
                    <?= $status === 'active' ? 'Deactivate' : 'Activate' ?>
                  </button>
                  <button class="btn-act btn-act-danger" onclick="deleteOptionItem('categories', <?= (int)($item['id'] ?? 0) ?>, '<?= htmlspecialchars(addslashes($item['name'])) ?>')">Delete</button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 2. INDUSTRY CATEGORIES -->
  <div id="panel-industries" class="tab-content-panel <?= $activeTab === 'industries' ? 'active' : '' ?>">
    <div class="panel-header">
      <div>
        <h3 class="panel-title">Industry Categories</h3>
        <span style="font-size: 0.8rem; color: #94a3b8;">Target industry markets and specialised packaging sectors</span>
      </div>
      <div style="display: flex; gap: 12px; align-items: center;">
        <input type="text" class="panel-search" placeholder="Search industries..." onkeyup="filterTable('table-industries', this.value)" />
        <button class="btn-add-option" onclick="openAddModal('industries', 'Industry')">+ Add Industry</button>
      </div>
    </div>
    <div class="options-table-wrapper">
      <table class="options-table" id="table-industries">
        <thead>
          <tr>
            <th style="width: 75px;">Image</th>
            <th>Industry Name &amp; Headline</th>
            <th>Slug</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($allOptions['industries'] ?? [] as $item): ?>
            <?php $status = $item['status'] ?? 'active'; ?>
            <tr>
              <td>
                <img src="<?= htmlspecialchars($item['image'] ?? '/logo_svg.svg') ?>" alt="" style="width: 56px; height: 56px; object-fit: cover; border-radius: 8px; border: 1px solid #334155;" onerror="this.onerror=null;this.src='/logo_svg.svg';" />
              </td>
              <td>
                <strong><?= htmlspecialchars($item['name']) ?></strong>
                <?php if (!empty($item['headline'])): ?>
                  <div style="font-size: 0.8rem; color: #38bdf8; margin-top: 1px;"><?= htmlspecialchars($item['headline']) ?></div>
                <?php endif; ?>
                <?php if (!empty($item['desc'])): ?>
                  <div style="font-size: 0.76rem; color: #94a3b8; max-width: 360px; margin-top: 3px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    <?= htmlspecialchars($item['desc']) ?>
                  </div>
                <?php endif; ?>
              </td>
              <td><code><?= htmlspecialchars($item['slug'] ?? '') ?></code></td>
              <td>
                <span class="<?= $status === 'active' ? 'status-badge-active' : 'status-badge-inactive' ?>">
                  <?= ucfirst($status) ?>
                </span>
              </td>
              <td>
                <div style="display: flex; gap: 6px;">
                  <button class="btn-act" onclick="editOptionItem('industries', <?= htmlspecialchars(json_encode($item)) ?>)">Edit</button>
                  <button class="btn-act" onclick="toggleOptionStatus('industries', <?= (int)($item['id'] ?? 0) ?>, '<?= $status === 'active' ? 'inactive' : 'active' ?>')">
                    <?= $status === 'active' ? 'Deactivate' : 'Activate' ?>
                  </button>
                  <button class="btn-act btn-act-danger" onclick="deleteOptionItem('industries', <?= (int)($item['id'] ?? 0) ?>, '<?= htmlspecialchars(addslashes($item['name'])) ?>')">Delete</button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 3. SUBCATEGORIES / SERIES -->
  <div id="panel-subcategories" class="tab-content-panel <?= $activeTab === 'subcategories' ? 'active' : '' ?>">
    <div class="panel-header">
      <div>
        <h3 class="panel-title">Series &amp; Subcategories</h3>
        <span style="font-size: 0.8rem; color: #94a3b8;">Hierarchical series under parent categories</span>
      </div>
      <div style="display: flex; gap: 12px; align-items: center;">
        <input type="text" class="panel-search" placeholder="Search series..." onkeyup="filterTable('table-subcategories', this.value)" />
        <button class="btn-add-option" onclick="openAddModal('subcategories', 'Series')">+ Add Series</button>
      </div>
    </div>
    <div class="options-table-wrapper">
      <table class="options-table" id="table-subcategories">
        <thead>
          <tr>
            <th>Series Name</th>
            <th>Parent Category</th>
            <th>Series Slug</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($allOptions['subcategories'] ?? [] as $item): ?>
            <tr>
              <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
              <td><span style="color: #38bdf8; font-size: 0.85rem;"><?= htmlspecialchars($item['categoryName'] ?? $item['categorySlug']) ?></span></td>
              <td><code><?= htmlspecialchars($item['slug'] ?? '') ?></code></td>
              <td>
                <button class="btn-act" onclick="editOptionItem('subcategories', <?= htmlspecialchars(json_encode($item)) ?>)">Edit</button>
                <button class="btn-act btn-act-danger" onclick="deleteOptionItem('subcategories', <?= (int)($item['id'] ?? 0) ?>, '<?= htmlspecialchars(addslashes($item['name'])) ?>')">Delete</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- GENERIC OPTIONS TABS -->
  <?php
  $simpleGroups = [
    'materials' => ['title' => 'Materials & Polymers', 'label' => 'Material'],
    'volumes' => ['title' => 'Volumes & Capacities', 'label' => 'Volume'],
    'necks' => ['title' => 'Neck Finishes & Thread Sizes', 'label' => 'Neck Finish'],
    'applications' => ['title' => 'Market Applications', 'label' => 'Application'],
    'features' => ['title' => 'Features & Compliance Badges', 'label' => 'Feature'],
    'shapes' => ['title' => 'Product Shapes & Silhouettes', 'label' => 'Shape'],
    'colours' => ['title' => 'Product Colours', 'label' => 'Colour'],
    'closures' => ['title' => 'Closure Types', 'label' => 'Closure'],
    'audiences' => ['title' => 'Target Audience & Gender', 'label' => 'Audience']
  ];
  ?>

  <?php foreach ($simpleGroups as $gKey => $gMeta): ?>
    <div id="panel-<?= $gKey ?>" class="tab-content-panel <?= $activeTab === $gKey ? 'active' : '' ?>">
      <div class="panel-header">
        <div>
          <h3 class="panel-title"><?= htmlspecialchars($gMeta['title']) ?></h3>
          <span style="font-size: 0.8rem; color: #94a3b8;">Managed options dynamically loaded into Add/Edit product forms</span>
        </div>
        <div style="display: flex; gap: 12px; align-items: center;">
          <input type="text" class="panel-search" placeholder="Search..." onkeyup="filterTable('table-<?= $gKey ?>', this.value)" />
          <button class="btn-add-option" onclick="openAddModal('<?= $gKey ?>', '<?= htmlspecialchars($gMeta['label']) ?>')">+ Add <?= htmlspecialchars($gMeta['label']) ?></button>
        </div>
      </div>
      <div class="options-table-wrapper">
        <table class="options-table" id="table-<?= $gKey ?>">
          <thead>
            <tr>
              <th style="width: 60px;">Order</th>
              <th>Name / Display Label</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($allOptions[$gKey] ?? [] as $item): ?>
              <?php $status = $item['status'] ?? 'active'; ?>
              <tr>
                <td style="width: 60px;"><code>#<?= $item['display_order'] ?? 1 ?></code></td>
                <td>
                  <strong><?= htmlspecialchars($item['name'] ?? ($item['display_label'] ?? '')) ?></strong>
                  <?php if (!empty($item['hex_code'])): ?>
                    <span style="display: inline-block; width: 12px; height: 12px; background: <?= htmlspecialchars($item['hex_code']) ?>; border-radius: 50%; margin-left: 6px; border: 1px solid #ffffff;"></span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="<?= $status === 'active' ? 'status-badge-active' : 'status-badge-inactive' ?>">
                    <?= ucfirst($status) ?>
                  </span>
                </td>
                <td>
                  <div style="display: flex; gap: 6px;">
                    <button class="btn-act" onclick="editOptionItem('<?= $gKey ?>', <?= htmlspecialchars(json_encode($item)) ?>)">Edit</button>
                    <button class="btn-act" onclick="toggleOptionStatus('<?= $gKey ?>', <?= (int)($item['id'] ?? 0) ?>, '<?= $status === 'active' ? 'inactive' : 'active' ?>')">
                      <?= $status === 'active' ? 'Deactivate' : 'Activate' ?>
                    </button>
                    <button class="btn-act btn-act-danger" onclick="deleteOptionItem('<?= $gKey ?>', <?= (int)($item['id'] ?? 0) ?>, '<?= htmlspecialchars(addslashes($item['name'] ?? ($item['display_label'] ?? ''))) ?>')">Delete</button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endforeach; ?>

  <!-- AUDIT LOG TAB -->
  <div id="panel-audit" class="tab-content-panel <?= $activeTab === 'audit' ? 'active' : '' ?>">
    <div class="panel-header">
      <div>
        <h3 class="panel-title">Option Audit Log</h3>
        <span style="font-size: 0.8rem; color: #94a3b8;">History of changes to option items</span>
      </div>
    </div>
    <div class="options-table-wrapper">
      <table class="options-table">
        <thead>
          <tr>
            <th>Date &amp; Time</th>
            <th>Admin User</th>
            <th>Action</th>
            <th>Group</th>
            <th>Item Name</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($auditLogs as $log): ?>
            <tr>
              <td><code><?= htmlspecialchars($log['created_at'] ?? '') ?></code></td>
              <td><strong><?= htmlspecialchars($log['user'] ?? 'Admin') ?></strong></td>
              <td><span style="color: #38bdf8; font-weight: 700;"><?= htmlspecialchars($log['action'] ?? '') ?></span></td>
              <td><code><?= htmlspecialchars($log['group'] ?? '') ?></code></td>
              <td><?= htmlspecialchars($log['item'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- ADD / EDIT MODAL -->
<div class="modal-overlay" id="optionModal">
  <div class="modal-box">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
      <h3 id="modalTitle" style="margin: 0; color: #38bdf8; font-size: 1.25rem;">Add Option</h3>
      <button type="button" onclick="closeModal()" style="background: none; border: none; color: #94a3b8; font-size: 1.3rem; cursor: pointer;">&times;</button>
    </div>
    
    <form id="optionForm" onsubmit="saveOptionForm(event)" enctype="multipart/form-data">
      <input type="hidden" id="optGroup" name="group" />
      <input type="hidden" id="optId" name="id" />
      <input type="hidden" id="optCoverImage" name="coverImage" />

      <!-- Name Field -->
      <div style="margin-bottom: 14px;">
        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Name / Label *</label>
        <input type="text" id="optName" name="name" required style="width: 100%; box-sizing: border-box; padding: 10px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
      </div>

      <!-- Slug Field (Categories & Industries) -->
      <div style="margin-bottom: 14px; display: none;" id="slugGroup">
        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Slug (URL Identifier)</label>
        <input type="text" id="optSlug" name="slug" placeholder="e.g. cosmetics-bottles or pharma" style="width: 100%; box-sizing: border-box; padding: 10px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
      </div>

      <!-- Headline Field (Industries) -->
      <div style="margin-bottom: 14px; display: none;" id="headlineGroup">
        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Industry Headline</label>
        <input type="text" id="optHeadline" name="headline" placeholder="e.g. Agrochemicals & Pesticides Packaging" style="width: 100%; box-sizing: border-box; padding: 10px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
      </div>

      <!-- Description Field (Categories & Industries) -->
      <div style="margin-bottom: 14px; display: none;" id="descGroup">
        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Description / Summary</label>
        <textarea id="optDesc" name="desc" rows="3" placeholder="Brief description of this packaging category or target sector..." style="width: 100%; box-sizing: border-box; padding: 10px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; resize: vertical;"></textarea>
      </div>

      <!-- IMAGE UPLOAD & SELECTOR BLOCK (Categories & Industries) -->
      <div style="margin-bottom: 16px; display: none;" id="imageGroup">
        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Category / Industry Image</label>
        
        <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 10px;">
          <!-- Live Preview Box -->
          <div style="width: 68px; height: 68px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <img id="optImgPreview" src="/logo_svg.svg" alt="Preview" style="max-width: 100%; max-height: 100%; object-fit: contain;" />
          </div>

          <!-- URL and File Inputs -->
          <div style="flex: 1;">
            <input type="text" id="optImage" name="image" placeholder="Image URL (e.g. /uploads/products/...)" oninput="updateImagePreview(this.value)" style="width: 100%; box-sizing: border-box; padding: 8px 10px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.85rem; margin-bottom: 6px;" />
            <input type="file" id="optImageFile" name="image_file" accept="image/*" onchange="previewUploadedImage(this)" style="font-size: 0.8rem; color: #94a3b8;" />
          </div>
        </div>

        <!-- Quick Preset Pickers -->
        <div id="categoryPresets" style="display: none;">
          <span style="font-size: 0.72rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">Quick Preset Thumbnails:</span>
          <div class="preset-grid">
            <div class="preset-thumb" onclick="selectPresetImage('/uploads/products/agro-bottles/agro-500ml-agro.webp')">
              <img src="/uploads/products/agro-bottles/agro-500ml-agro.webp" alt="Agro" />
              <span>Agro</span>
            </div>
            <div class="preset-thumb" onclick="selectPresetImage('/uploads/products/caps/cap-nb108-a.webp')">
              <img src="/uploads/products/caps/cap-nb108-a.webp" alt="Caps" />
              <span>Caps</span>
            </div>
            <div class="preset-thumb" onclick="selectPresetImage('/uploads/products/cosmetics-bottles/cosmetic-100ml-tulip.webp')">
              <img src="/uploads/products/cosmetics-bottles/cosmetic-100ml-tulip.webp" alt="Cosmetics" />
              <span>Cosmetics</span>
            </div>
            <div class="preset-thumb" onclick="selectPresetImage('/uploads/products/jars/jar-150cc-rd.webp')">
              <img src="/uploads/products/jars/jar-150cc-rd.webp" alt="Jars" />
              <span>Jars</span>
            </div>
            <div class="preset-thumb" onclick="selectPresetImage('/uploads/products/pharma-bottles/pharma-100ml-dome-pet.webp')">
              <img src="/uploads/products/pharma-bottles/pharma-100ml-dome-pet.webp" alt="Pharma" />
              <span>Pharma</span>
            </div>
            <div class="preset-thumb" onclick="selectPresetImage('/uploads/products/pumps/lotion-pump-nb101-a.webp')">
              <img src="/uploads/products/pumps/lotion-pump-nb101-a.webp" alt="Pumps" />
              <span>Pumps</span>
            </div>
            <div class="preset-thumb" onclick="selectPresetImage('/uploads/products/sprays/medical-sprayer-nb107-a.webp')">
              <img src="/uploads/products/sprays/medical-sprayer-nb107-a.webp" alt="Sprays" />
              <span>Sprays</span>
            </div>
            <div class="preset-thumb" onclick="selectPresetImage('/uploads/products/liquor-bottles/liq-180ml-kidney-10g.webp')">
              <img src="/uploads/products/liquor-bottles/liq-180ml-kidney-10g.webp" alt="Liquor" />
              <span>Liquor</span>
            </div>
          </div>
        </div>

        <div id="industryPresets" style="display: none;">
          <span style="font-size: 0.72rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">Quick Preset Market Banners:</span>
          <div class="preset-grid" style="grid-template-columns: repeat(5, 1fr);">
            <div class="preset-thumb" onclick="selectPresetImage('/vikaas_inputs/AGRO_PESTICIDES.webp')">
              <img src="/vikaas_inputs/AGRO_PESTICIDES.webp" alt="Agro" />
              <span>Agro</span>
            </div>
            <div class="preset-thumb" onclick="selectPresetImage('/vikaas_inputs/COSMETICS.webp')">
              <img src="/vikaas_inputs/COSMETICS.webp" alt="Cosmetics" />
              <span>Cosmetics</span>
            </div>
            <div class="preset-thumb" onclick="selectPresetImage('/vikaas_inputs/PHARMA_BOTTLES.webp')">
              <img src="/vikaas_inputs/PHARMA_BOTTLES.webp" alt="Pharma" />
              <span>Pharma</span>
            </div>
            <div class="preset-thumb" onclick="selectPresetImage('/vikaas_inputs/PET_nutraceuticle_jars.webp')">
              <img src="/vikaas_inputs/PET_nutraceuticle_jars.webp" alt="Nutra" />
              <span>Nutra</span>
            </div>
            <div class="preset-thumb" onclick="selectPresetImage('/vikaas_inputs/LIQUOR_Bottles.webp')">
              <img src="/vikaas_inputs/LIQUOR_Bottles.webp" alt="Liquor" />
              <span>Liquor</span>
            </div>
          </div>
        </div>

      </div>

      <!-- Hex Color (Colours only) -->
      <div style="margin-bottom: 14px; display: none;" id="hexGroup">
        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Hex Color Code (Optional)</label>
        <input type="text" id="optHex" name="hex_code" placeholder="#0284c7" style="width: 100%; box-sizing: border-box; padding: 10px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
      </div>

      <!-- Status Field -->
      <div style="margin-bottom: 20px;">
        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Status</label>
        <select id="optStatus" name="status" style="width: 100%; box-sizing: border-box; padding: 10px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;">
          <option value="active">Active (Visible across website &amp; forms)</option>
          <option value="inactive">Inactive (Hidden from public views)</option>
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #334155; padding-top: 16px;">
        <button type="button" class="btn-act" onclick="closeModal()">Cancel</button>
        <button type="button" id="btnSaveOption" class="btn-add-option" onclick="saveOptionForm(event)">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function switchTab(tabKey, btnElem) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-content-panel').forEach(p => p.classList.remove('active'));

  if (btnElem) {
    btnElem.classList.add('active');
  } else {
    const matchedBtn = document.querySelector('.tab-btn[data-tab="' + tabKey + '"]');
    if (matchedBtn) matchedBtn.classList.add('active');
  }

  const panel = document.getElementById('panel-' + tabKey);
  if (panel) panel.classList.add('active');

  try {
    const url = new URL(window.location);
    url.searchParams.set('tab', tabKey);
    window.history.replaceState({}, '', url);
  } catch(e) {}
}

function filterTable(tableId, query) {
  const q = query.toLowerCase();
  const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
  rows.forEach(r => {
    const text = r.innerText.toLowerCase();
    r.style.display = text.includes(q) ? '' : 'none';
  });
}

function updateImagePreview(src) {
  const preview = document.getElementById('optImgPreview');
  if (src && src.trim()) {
    preview.src = src;
    preview.onerror = function() { preview.src = '/logo_svg.svg'; };
  } else {
    preview.src = '/logo_svg.svg';
  }
}

function previewUploadedImage(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('optImgPreview').src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function selectPresetImage(url) {
  document.getElementById('optImage').value = url;
  updateImagePreview(url);
}

function openAddModal(group, labelName) {
  document.getElementById('modalTitle').innerText = 'Add New ' + labelName;
  document.getElementById('optGroup').value = group;
  document.getElementById('optId').value = '';
  document.getElementById('optName').value = '';
  document.getElementById('optSlug').value = '';
  document.getElementById('optHeadline').value = '';
  document.getElementById('optDesc').value = '';
  document.getElementById('optImage').value = '';
  document.getElementById('optCoverImage').value = '';
  document.getElementById('optHex').value = '';
  document.getElementById('optStatus').value = 'active';
  document.getElementById('optImageFile').value = '';
  updateImagePreview('/logo_svg.svg');

  const isCatOrInd = (group === 'categories' || group === 'industries');
  document.getElementById('slugGroup').style.display = isCatOrInd ? 'block' : 'none';
  document.getElementById('descGroup').style.display = isCatOrInd ? 'block' : 'none';
  document.getElementById('imageGroup').style.display = isCatOrInd ? 'block' : 'none';
  document.getElementById('headlineGroup').style.display = (group === 'industries') ? 'block' : 'none';
  document.getElementById('categoryPresets').style.display = (group === 'categories') ? 'block' : 'none';
  document.getElementById('industryPresets').style.display = (group === 'industries') ? 'block' : 'none';
  document.getElementById('hexGroup').style.display = (group === 'colours') ? 'block' : 'none';

  document.getElementById('optionModal').classList.add('open');
}

function editOptionItem(group, item) {
  document.getElementById('modalTitle').innerText = 'Edit ' + (group === 'categories' ? 'Category' : (group === 'industries' ? 'Industry' : 'Option'));
  document.getElementById('optGroup').value = group;
  document.getElementById('optId').value = item.id || '';
  document.getElementById('optName').value = item.name || item.display_label || '';
  document.getElementById('optSlug').value = item.slug || '';
  document.getElementById('optHeadline').value = item.headline || '';
  document.getElementById('optDesc').value = item.desc || item.description || '';
  document.getElementById('optImage').value = item.image || '';
  document.getElementById('optCoverImage').value = item.coverImage || item.image || '';
  document.getElementById('optHex').value = item.hex_code || '';
  document.getElementById('optStatus').value = item.status || 'active';
  document.getElementById('optImageFile').value = '';
  updateImagePreview(item.image || '/logo_svg.svg');

  const isCatOrInd = (group === 'categories' || group === 'industries');
  document.getElementById('slugGroup').style.display = isCatOrInd ? 'block' : 'none';
  document.getElementById('descGroup').style.display = isCatOrInd ? 'block' : 'none';
  document.getElementById('imageGroup').style.display = isCatOrInd ? 'block' : 'none';
  document.getElementById('headlineGroup').style.display = (group === 'industries') ? 'block' : 'none';
  document.getElementById('categoryPresets').style.display = (group === 'categories') ? 'block' : 'none';
  document.getElementById('industryPresets').style.display = (group === 'industries') ? 'block' : 'none';
  document.getElementById('hexGroup').style.display = (group === 'colours') ? 'block' : 'none';

  document.getElementById('optionModal').classList.add('open');
}

function closeModal() {
  document.getElementById('optionModal').classList.remove('open');
}

function saveOptionForm(e) {
  if (e && e.preventDefault) e.preventDefault();
  const btn = document.getElementById('btnSaveOption');
  const originalText = btn ? btn.innerText : 'Save Changes';
  if (btn) {
    btn.disabled = true;
    btn.innerText = 'Saving...';
  }

  const formData = new FormData(document.getElementById('optionForm'));
  formData.append('action', 'save_option');

  fetch('/admin/api_options', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(res => {
    btn.disabled = false;
    btn.innerText = originalText;
    if (res.success) {
      window.location.href = '/admin/categories?tab=' + document.getElementById('optGroup').value + '&msg=' + encodeURIComponent(res.message);
    } else {
      alert(res.message || 'Error saving option');
    }
  })
  .catch(err => {
    // If JSON fails, fallback to direct POST
    document.getElementById('optionForm').method = 'POST';
    document.getElementById('optionForm').action = '/admin/categories';
    const actInput = document.createElement('input');
    actInput.type = 'hidden';
    actInput.name = 'action';
    actInput.value = 'save_option';
    document.getElementById('optionForm').appendChild(actInput);
    document.getElementById('optionForm').submit();
  });
}

function toggleOptionStatus(group, id, newStatus) {
  const formData = new FormData();
  formData.append('action', 'toggle_status');
  formData.append('group', group);
  formData.append('id', id);
  formData.append('status', newStatus);

  fetch('/admin/api_options', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        window.location.href = '/admin/categories?tab=' + group + '&msg=' + encodeURIComponent(res.message);
      } else {
        alert(res.message || 'Failed to toggle status');
      }
    });
}

function deleteOptionItem(group, id, name) {
  if (!confirm('Are you sure you want to delete "' + name + '"?')) return;

  const formData = new FormData();
  formData.append('action', 'delete');
  formData.append('group', group);
  formData.append('id', id);

  fetch('/admin/api_options', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        window.location.href = '/admin/categories?tab=' + group + '&msg=' + encodeURIComponent(res.message);
      } else if (res.in_use) {
        if (confirm(res.message)) {
          const forceData = new FormData();
          forceData.append('action', 'delete');
          forceData.append('group', group);
          forceData.append('id', id);
          forceData.append('force_deactivate', '1');
          fetch('/admin/api_options', { method: 'POST', body: forceData })
            .then(r2 => r2.json())
            .then(res2 => {
              window.location.href = '/admin/categories?tab=' + group + '&msg=' + encodeURIComponent(res2.message);
            });
        }
      } else {
        alert(res.message || 'Error deleting option');
      }
    });
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
