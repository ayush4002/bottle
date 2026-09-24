<?php
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/ProductModel.php';

$allOptions = ProductModel::getAllOptions();
$auditLogs = ProductModel::getOptionAuditLogs(30);

$message = $_GET['msg'] ?? '';
$error = $_GET['err'] ?? '';

// Handle Direct Form Posts if JS disabled
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

            ProductModel::saveOption($group, $data);
            header("Location: categories.php?msg=" . urlencode("Option '{$name}' saved successfully!") . "&tab=" . urlencode($group));
            exit();
        }
    }
}

$activeTab = $_GET['tab'] ?? 'categories';
?>

<style>
  .admin-options-container {
    max-width: 1200px;
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
    min-height: 40px;
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
    max-width: 480px;
    padding: 24px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
    box-sizing: border-box;
    max-height: 90vh;
    overflow-y: auto;
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
        Centralized Single Source of Truth for all product selection dropdowns and chips
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
    <button class="tab-btn <?= $activeTab === 'categories' ? 'active' : '' ?>" onclick="switchTab('categories')">
      Categories <span class="tab-badge"><?= count($allOptions['categories'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'subcategories' ? 'active' : '' ?>" onclick="switchTab('subcategories')">
      Series / Subcategories <span class="tab-badge"><?= count($allOptions['subcategories'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'materials' ? 'active' : '' ?>" onclick="switchTab('materials')">
      Materials <span class="tab-badge"><?= count($allOptions['materials'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'volumes' ? 'active' : '' ?>" onclick="switchTab('volumes')">
      Volumes / Capacities <span class="tab-badge"><?= count($allOptions['volumes'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'necks' ? 'active' : '' ?>" onclick="switchTab('necks')">
      Neck Finishes <span class="tab-badge"><?= count($allOptions['necks'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'applications' ? 'active' : '' ?>" onclick="switchTab('applications')">
      Applications <span class="tab-badge"><?= count($allOptions['applications'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'features' ? 'active' : '' ?>" onclick="switchTab('features')">
      Features <span class="tab-badge"><?= count($allOptions['features'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'shapes' ? 'active' : '' ?>" onclick="switchTab('shapes')">
      Product Shapes <span class="tab-badge"><?= count($allOptions['shapes'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'colours' ? 'active' : '' ?>" onclick="switchTab('colours')">
      Colours <span class="tab-badge"><?= count($allOptions['colours'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'closures' ? 'active' : '' ?>" onclick="switchTab('closures')">
      Closures <span class="tab-badge"><?= count($allOptions['closures'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'audiences' ? 'active' : '' ?>" onclick="switchTab('audiences')">
      Target Audience <span class="tab-badge"><?= count($allOptions['audiences'] ?? []) ?></span>
    </button>
    <button class="tab-btn <?= $activeTab === 'audit' ? 'active' : '' ?>" onclick="switchTab('audit')">
      Audit Logs <span class="tab-badge"><?= count($auditLogs) ?></span>
    </button>
  </div>

  <!-- TAB PANELS -->

  <!-- 1. CATEGORIES -->
  <div id="panel-categories" class="tab-content-panel <?= $activeTab === 'categories' ? 'active' : '' ?>">
    <div class="panel-header">
      <div>
        <h3 class="panel-title">Product Categories</h3>
        <span style="font-size: 0.8rem; color: #94a3b8;">Main product classification families</span>
      </div>
      <div style="display: flex; gap: 12px; align-items: center;">
        <input type="text" class="panel-search" placeholder="Search categories..." onkeyup="filterTable('table-categories', this.value)" />
        <button class="btn-add-option" onclick="openAddModal('categories', 'Category')">+ Add Category</button>
      </div>
    </div>
    <table class="options-table" id="table-categories">
      <thead>
        <tr>
          <th>Category Name</th>
          <th>Slug</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($allOptions['categories'] ?? [] as $item): ?>
          <tr>
            <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
            <td><code><?= htmlspecialchars($item['slug']) ?></code></td>
            <td><span class="status-badge-active">Active</span></td>
            <td>
              <button class="btn-act" onclick="editOptionItem('categories', <?= htmlspecialchars(json_encode($item)) ?>)">Edit</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- 2. SUBCATEGORIES / SERIES -->
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
            <td><code><?= htmlspecialchars($item['slug']) ?></code></td>
            <td>
              <button class="btn-act" onclick="editOptionItem('subcategories', <?= htmlspecialchars(json_encode($item)) ?>)">Edit</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- GENERIC OPTIONS TABS (Materials, Volumes, Necks, Applications, Features, Shapes, Colours, Closures, Audiences) -->
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
      <table class="options-table" id="table-<?= $gKey ?>">
        <thead>
          <tr>
            <th>Order</th>
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
                <button class="btn-act" onclick="editOptionItem('<?= $gKey ?>', <?= htmlspecialchars(json_encode($item)) ?>)">Edit</button>
                <button class="btn-act" onclick="toggleOptionStatus('<?= $gKey ?>', <?= $item['id'] ?>, '<?= $status === 'active' ? 'inactive' : 'active' ?>')">
                  <?= $status === 'active' ? 'Deactivate' : 'Activate' ?>
                </button>
                <button class="btn-act btn-act-danger" onclick="deleteOptionItem('<?= $gKey ?>', <?= $item['id'] ?>, '<?= htmlspecialchars($item['name'] ?? ($item['display_label'] ?? '')) ?>')">Delete</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
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

<!-- ADD / EDIT MODAL -->
<div class="modal-overlay" id="optionModal">
  <div class="modal-box">
    <h3 id="modalTitle" style="margin-top: 0; color: #38bdf8;">Add Option</h3>
    
    <form id="optionForm" onsubmit="saveOptionForm(event)">
      <input type="hidden" id="optGroup" name="group" />
      <input type="hidden" id="optId" name="id" />

      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Name / Label *</label>
        <input type="text" id="optName" name="name" class="form-control-smart" required style="width: 100%; box-sizing: border-box; padding: 10px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
      </div>

      <div style="margin-bottom: 16px;" id="hexGroup">
        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Hex Color Code (Optional)</label>
        <input type="text" id="optHex" name="hex_code" placeholder="#0284c7" style="width: 100%; box-sizing: border-box; padding: 10px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
      </div>

      <div style="margin-bottom: 20px;">
        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Status</label>
        <select id="optStatus" name="status" style="width: 100%; box-sizing: border-box; padding: 10px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;">
          <option value="active">Active (Visible in Add Product)</option>
          <option value="inactive">Inactive (Disabled for New Products)</option>
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 12px;">
        <button type="button" class="btn-act" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn-add-option">Save Option</button>
      </div>
    </form>
  </div>
</div>

<script>
function switchTab(tabKey) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-content-panel').forEach(p => p.classList.remove('active'));

  const btn = Array.from(document.querySelectorAll('.tab-btn')).find(b => b.getAttribute('onclick').includes(tabKey));
  if (btn) btn.classList.add('active');

  const panel = document.getElementById('panel-' + tabKey);
  if (panel) panel.classList.add('active');
}

function filterTable(tableId, query) {
  const q = query.toLowerCase();
  const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
  rows.forEach(r => {
    const text = r.innerText.toLowerCase();
    r.style.display = text.includes(q) ? '' : 'none';
  });
}

function openAddModal(group, labelName) {
  document.getElementById('modalTitle').innerText = 'Add New ' + labelName;
  document.getElementById('optGroup').value = group;
  document.getElementById('optId').value = '';
  document.getElementById('optName').value = '';
  document.getElementById('optHex').value = '';
  document.getElementById('optStatus').value = 'active';

  document.getElementById('hexGroup').style.display = group === 'colours' ? 'block' : 'none';
  document.getElementById('optionModal').classList.add('open');
}

function editOptionItem(group, item) {
  document.getElementById('modalTitle').innerText = 'Edit Option';
  document.getElementById('optGroup').value = group;
  document.getElementById('optId').value = item.id;
  document.getElementById('optName').value = item.name || item.display_label || '';
  document.getElementById('optHex').value = item.hex_code || '';
  document.getElementById('optStatus').value = item.status || 'active';

  document.getElementById('hexGroup').style.display = group === 'colours' ? 'block' : 'none';
  document.getElementById('optionModal').classList.add('open');
}

function closeModal() {
  document.getElementById('optionModal').classList.remove('open');
}

function saveOptionForm(e) {
  e.preventDefault();
  const formData = new FormData(document.getElementById('optionForm'));
  formData.append('action', 'save_option');

  fetch('categories.php', {
    method: 'POST',
    body: formData
  }).then(() => {
    window.location.href = 'categories.php?tab=' + document.getElementById('optGroup').value + '&msg=Option+saved+successfully';
  });
}

function toggleOptionStatus(group, id, newStatus) {
  const formData = new FormData();
  formData.append('action', 'toggle_status');
  formData.append('group', group);
  formData.append('id', id);
  formData.append('status', newStatus);

  fetch('api_options.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        window.location.href = 'categories.php?tab=' + group + '&msg=' + encodeURIComponent(res.message);
      }
    });
}

function deleteOptionItem(group, id, name) {
  if (!confirm('Are you sure you want to delete "' + name + '"?')) return;

  const formData = new FormData();
  formData.append('action', 'delete');
  formData.append('group', group);
  formData.append('id', id);

  fetch('api_options.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        window.location.href = 'categories.php?tab=' + group + '&msg=' + encodeURIComponent(res.message);
      } else if (res.in_use) {
        if (confirm(res.message)) {
          const forceData = new FormData();
          forceData.append('action', 'delete');
          forceData.append('group', group);
          forceData.append('id', id);
          forceData.append('force_deactivate', '1');
          fetch('api_options.php', { method: 'POST', body: forceData })
            .then(r2 => r2.json())
            .then(res2 => {
              window.location.href = 'categories.php?tab=' + group + '&msg=' + encodeURIComponent(res2.message);
            });
        }
      } else {
        alert(res.message || 'Error deleting option');
      }
    });
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
