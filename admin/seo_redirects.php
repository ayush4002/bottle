<?php
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/SeoModel.php';

checkAdminAuth();
SeoModel::init();

$msg = '';
$err = '';
$adminUser = $_SESSION['admin_user'] ?? 'admin';

// Handle Add/Edit Redirect
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_redirect') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $err = "Invalid security token.";
    } else {
        $oldUrl = trim($_POST['old_url'] ?? '');
        $newUrl = trim($_POST['new_url'] ?? '');
        $code = (int)($_POST['status_code'] ?? 301);
        $active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($oldUrl) || ($code !== 410 && empty($newUrl))) {
            $err = "Old URL and Destination URL are required (unless status is 410 Gone).";
        } else {
            $res = SeoModel::saveRedirect($oldUrl, $newUrl ?: '/', $code, $active, $adminUser);
            if (!empty($res['success'])) {
                $msg = "Redirect from '{$oldUrl}' successfully saved!";
            } else {
                $err = $res['error'] ?? "Failed to save redirect.";
            }
        }
    }
}

// Handle Delete Redirect
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    SeoModel::deleteRedirect((int)$_GET['id'], $adminUser);
    $msg = "Redirect deleted.";
}

// Handle Toggle Redirect
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && !empty($_GET['id'])) {
    SeoModel::toggleRedirect((int)$_GET['id'], $adminUser);
    $msg = "Redirect status updated.";
}

// Handle Dismiss 404
if (isset($_GET['action']) && $_GET['action'] === 'dismiss_404' && !empty($_GET['id'])) {
    SeoModel::dismiss404((int)$_GET['id']);
    $msg = "404 log entry dismissed.";
}

// Handle Export Redirects CSV
if (isset($_GET['action']) && $_GET['action'] === 'export_redirects_csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=redirects_' . date('Y-m-d') . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Old URL', 'New URL', 'Status Code', 'Hits', 'Active']);
    $list = SeoModel::getRedirects();
    foreach ($list as $r) {
        fputcsv($out, [$r['old_url'], $r['new_url'], $r['status_code'], $r['hits'], $r['is_active']]);
    }
    fclose($out);
    exit();
}

// Search
$search = trim($_GET['search'] ?? '');
$redirects = SeoModel::getRedirects($search);
$logs404 = SeoModel::get404Logs(60);

$csrfToken = generateCsrfToken();
?>

<div style="max-width: 1320px; margin: 0 auto;">

  <!-- PAGE HEADER -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
      <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 0.88rem; color: #94a3b8;">
        <a href="/admin/seo" style="color: #94a3b8; text-decoration: none;">&larr; SEO Overview</a>
        <span>/</span>
        <span style="color: #38bdf8; font-weight: 600;">Redirects &amp; 404 Monitor</span>
      </div>
      <h1 style="font-size: 1.8rem; font-weight: 800; color: #f8fafc; margin: 0;">
        URL Redirect Manager &amp; Real-Time 404 Monitor
      </h1>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <a href="/admin/seo_redirects?action=export_redirects_csv" class="tn-btn-outline" style="padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: 0.86rem; background: #1e293b; color: #38bdf8; border: 1px solid #334155; text-decoration: none;">
        Export Redirects CSV
      </a>
      <button type="button" onclick="openAddRedirectModal('', '', 301, 1)" class="tn-btn-gold" style="padding: 10px 18px; border: none; border-radius: 8px; font-weight: 700; font-size: 0.88rem; cursor: pointer; box-shadow: 0 4px 14px rgba(184, 137, 46, 0.35);">
        + Add New Redirect
      </button>
    </div>
  </div>

  <?php if (!empty($msg)): ?>
    <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 12px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 20px;">
      <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($err)): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 12px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 20px;">
      <?= htmlspecialchars($err) ?>
    </div>
  <?php endif; ?>

  <!-- REDIRECTS TABLE CARD -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; overflow: hidden; margin-bottom: 30px;">
    
    <div style="padding: 16px 20px; background: #0f172a; border-bottom: 1px solid #334155; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
      <div style="font-size: 1.05rem; font-weight: 800; color: #f8fafc;">
        Active URL Redirects (<?= count($redirects) ?> rules)
      </div>
      <form method="GET" action="/admin/seo_redirects" style="display: flex; gap: 8px;">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search URLs..." style="padding: 7px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.82rem;" />
        <button type="submit" style="padding: 7px 14px; background: #334155; color: #fff; border: none; border-radius: 6px; font-size: 0.82rem; cursor: pointer;">Search</button>
      </form>
    </div>

    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.86rem;">
        <thead>
          <tr style="color: #94a3b8; border-bottom: 1px solid #334155; background: #162032;">
            <th style="padding: 12px 18px;">Source URL (From)</th>
            <th style="padding: 12px 18px;">Target URL (To)</th>
            <th style="padding: 12px 18px; text-align: center;">Status</th>
            <th style="padding: 12px 18px; text-align: center;">Hits</th>
            <th style="padding: 12px 18px; text-align: center;">Active</th>
            <th style="padding: 12px 18px; text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($redirects)): ?>
            <tr>
              <td colspan="6" style="padding: 30px; text-align: center; color: #94a3b8;">
                No redirects configured. Click "+ Add New Redirect" above.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($redirects as $r): ?>
              <tr style="border-bottom: 1px solid #334155;">
                <td style="padding: 12px 18px; font-family: monospace; color: #38bdf8; font-weight: 600;">
                  <?= htmlspecialchars($r['old_url']) ?>
                  <?php if (!empty($r['has_loop'])): ?>
                    <span style="display: inline-block; background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; padding: 1px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; margin-left: 6px;">
                      LOOP WARNING
                    </span>
                  <?php elseif (!empty($r['has_chain'])): ?>
                    <span style="display: inline-block; background: rgba(251, 191, 36, 0.2); color: #fbbf24; border: 1px solid #f59e0b; padding: 1px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; margin-left: 6px;">
                      CHAIN WARNING
                    </span>
                  <?php endif; ?>
                </td>
                <td style="padding: 12px 18px; font-family: monospace; color: #cbd5e1;">
                  <?= htmlspecialchars($r['new_url']) ?>
                </td>
                <td style="padding: 12px 18px; text-align: center;">
                  <span style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; padding: 2px 8px; border-radius: 4px; font-weight: 700; font-size: 0.78rem;">
                    <?= $r['status_code'] ?>
                  </span>
                </td>
                <td style="padding: 12px 18px; text-align: center; color: #94a3b8; font-weight: 600;">
                  <?= (int)$r['hits'] ?>
                </td>
                <td style="padding: 12px 18px; text-align: center;">
                  <a href="/admin/seo_redirects?action=toggle&id=<?= $r['id'] ?>&csrf_token=<?= urlencode($csrfToken) ?>" style="text-decoration: none;">
                    <?php if (!empty($r['is_active'])): ?>
                      <span style="color: #34d399; font-weight: 700; font-size: 0.8rem; background: rgba(52, 211, 153, 0.15); padding: 2px 8px; border-radius: 4px;">Active</span>
                    <?php else: ?>
                      <span style="color: #94a3b8; font-weight: 600; font-size: 0.8rem; background: rgba(148, 163, 184, 0.15); padding: 2px 8px; border-radius: 4px;">Disabled</span>
                    <?php endif; ?>
                  </a>
                </td>
                <td style="padding: 12px 18px; text-align: right; white-space: nowrap;">
                  <button type="button" onclick="openAddRedirectModal('<?= htmlspecialchars($r['old_url'], ENT_QUOTES) ?>', '<?= htmlspecialchars($r['new_url'], ENT_QUOTES) ?>', <?= (int)$r['status_code'] ?>, <?= (int)$r['is_active'] ?>)" style="padding: 4px 10px; background: #334155; color: #fff; border: none; border-radius: 4px; font-size: 0.78rem; cursor: pointer; margin-right: 6px;">
                    Edit
                  </button>
                  <a href="/admin/seo_redirects?action=delete&id=<?= $r['id'] ?>&csrf_token=<?= urlencode($csrfToken) ?>" onclick="return confirm('Delete this redirect permanently?');" style="padding: 4px 10px; background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 4px; font-size: 0.78rem; text-decoration: none;">
                    Delete
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>

  <!-- 404 ERROR LOG MONITOR -->
  <div id="tab-404" style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; overflow: hidden;">
    
    <div style="padding: 16px 20px; background: #0f172a; border-bottom: 1px solid #334155; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
      <div>
        <div style="font-size: 1.05rem; font-weight: 800; color: #f8fafc;">
          Real-Time 404 Error Log
        </div>
        <p style="font-size: 0.8rem; color: #94a3b8; margin: 2px 0 0 0;">
          Broken URLs requested by visitors or search crawlers. Convert them into 301 redirects with 1-click.
        </p>
      </div>
    </div>

    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.86rem;">
        <thead>
          <tr style="color: #94a3b8; border-bottom: 1px solid #334155; background: #162032;">
            <th style="padding: 12px 18px;">Broken Requested URL</th>
            <th style="padding: 12px 18px;">Referrer</th>
            <th style="padding: 12px 18px; text-align: center;">Hits</th>
            <th style="padding: 12px 18px;">Last Seen</th>
            <th style="padding: 12px 18px; text-align: right;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($logs404)): ?>
            <tr>
              <td colspan="5" style="padding: 30px; text-align: center; color: #94a3b8;">
                No 404 errors logged. Your website crawl paths are clean!
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($logs404 as $l): ?>
              <tr style="border-bottom: 1px solid #334155;">
                <td style="padding: 12px 18px; font-family: monospace; color: #f87171; font-weight: 600;">
                  <?= htmlspecialchars($l['url']) ?>
                </td>
                <td style="padding: 12px 18px; color: #94a3b8; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                  <?= htmlspecialchars($l['referrer'] ?: '(Direct / External Crawl)') ?>
                </td>
                <td style="padding: 12px 18px; text-align: center; font-weight: 800; color: #fbbf24;">
                  <?= (int)$l['hits'] ?>
                </td>
                <td style="padding: 12px 18px; color: #94a3b8; white-space: nowrap;">
                  <?= htmlspecialchars($l['last_seen']) ?>
                </td>
                <td style="padding: 12px 18px; text-align: right; white-space: nowrap;">
                  <button type="button" onclick="openAddRedirectModal('<?= htmlspecialchars($l['url'], ENT_QUOTES) ?>', '/', 301, 1)" class="tn-btn-gold" style="padding: 5px 12px; border: none; border-radius: 4px; font-size: 0.78rem; font-weight: 700; cursor: pointer; margin-right: 6px;">
                    + Create 301 Redirect
                  </button>
                  <a href="/admin/seo_redirects?action=dismiss_404&id=<?= $l['id'] ?>&csrf_token=<?= urlencode($csrfToken) ?>" style="color: #94a3b8; font-size: 0.78rem; text-decoration: none;">
                    Dismiss
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>

</div>

<!-- ADD / EDIT REDIRECT MODAL -->
<div id="redirectModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
  <div style="background: #1e293b; border: 1px solid #475569; border-radius: 12px; max-width: 550px; width: 100%; padding: 24px; box-sizing: border-box;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; padding-bottom: 12px; margin-bottom: 18px;">
      <h3 style="font-size: 1.15rem; font-weight: 800; color: #f8fafc; margin: 0;">Add / Edit URL Redirect</h3>
      <button type="button" onclick="document.getElementById('redirectModal').style.display='none';" style="background: transparent; border: none; color: #94a3b8; font-size: 1.5rem; cursor: pointer;">&times;</button>
    </div>

    <form method="POST" action="/admin/seo_redirects">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
      <input type="hidden" name="action" value="save_redirect" />

      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Old Source URL (From) *</label>
        <input type="text" name="old_url" id="modalOldUrl" required placeholder="/old-bottle-page" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #38bdf8; font-size: 0.88rem; font-family: monospace;" />
      </div>

      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">New Target URL (To) *</label>
        <input type="text" name="new_url" id="modalNewUrl" required placeholder="/products?category=pharma-bottles" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #34d399; font-size: 0.88rem; font-family: monospace;" />
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px;">
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">HTTP Status Code</label>
          <select name="status_code" id="modalStatusCode" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.88rem;">
            <option value="301">301 — Permanent Redirect (SEO Equity Pass)</option>
            <option value="302">302 — Temporary Redirect</option>
            <option value="410">410 — Gone (Permanently Removed)</option>
          </select>
        </div>
        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Activation State</label>
          <select name="is_active" id="modalIsActive" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 6px; color: #fff; font-size: 0.88rem;">
            <option value="1">Enabled (Active)</option>
            <option value="0">Disabled (Inactive)</option>
          </select>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #334155; padding-top: 14px;">
        <button type="button" onclick="document.getElementById('redirectModal').style.display='none';" style="padding: 8px 16px; background: #334155; color: #fff; border: none; border-radius: 6px; cursor: pointer;">Cancel</button>
        <button type="submit" class="tn-btn-gold" style="padding: 8px 20px; border: none; border-radius: 6px; font-weight: 700; cursor: pointer;">Save Redirect</button>
      </div>
    </form>

  </div>
</div>

<script>
function openAddRedirectModal(oldUrl, newUrl, statusCode, isActive) {
  document.getElementById('modalOldUrl').value = oldUrl || '';
  document.getElementById('modalNewUrl').value = newUrl || '';
  document.getElementById('modalStatusCode').value = statusCode || 301;
  document.getElementById('modalIsActive').value = (isActive !== undefined) ? isActive : 1;
  document.getElementById('redirectModal').style.display = 'flex';
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

