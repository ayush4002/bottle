<?php
// ==============================================================================
// TRUENORTH CMS — REAL-TIME 404 ERROR LOG MONITOR
// Tracks broken URLs, hits, referrers, and provides 1-click 301 redirect creation
// ==============================================================================

require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/app/Models/SeoModel.php';

checkAdminAuth();
SeoModel::init();

$msg = '';
$err = '';
$adminUser = $_SESSION['admin_user'] ?? 'admin';
if (is_array($adminUser)) $adminUser = $adminUser['username'] ?? 'admin';

// Handle Dismiss 404
if (isset($_GET['action']) && $_GET['action'] === 'dismiss' && !empty($_GET['id'])) {
    SeoModel::dismiss404((int)$_GET['id']);
    $msg = "404 log entry dismissed.";
}

// Handle Clear All 404s
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear_all_404s') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $err = "Invalid security token.";
    } else {
        SeoModel::clearAll404s();
        $msg = "All 404 error logs have been cleared.";
    }
}

// Handle 1-Click Create Redirect from 404
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_redirect') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $err = "Invalid security token.";
    } else {
        $oldUrl = trim($_POST['old_url'] ?? '');
        $newUrl = trim($_POST['new_url'] ?? '');
        $code = (int)($_POST['status_code'] ?? 301);
        $active = isset($_POST['is_active']) ? 1 : 0;
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : 0;

        if (empty($oldUrl) || empty($newUrl)) {
            $err = "Both Old URL and Destination URL are required.";
        } elseif ($oldUrl === $newUrl) {
            $err = "Redirect loop prevented: Old URL and Destination URL cannot be identical.";
        } else {
            SeoModel::saveRedirect($oldUrl, $newUrl, $code, $active, $adminUser, $id);
            // Auto dismiss from 404 log if present
            if (!empty($_POST['dismiss_404_id'])) {
                SeoModel::dismiss404((int)$_POST['dismiss_404_id']);
            }
            $msg = "301 Redirect for '{$oldUrl}' successfully created!";
        }
    }
}

$logs404 = SeoModel::get404Logs(250);
$total404Urls = count($logs404);
$total404Hits = 0;
foreach ($logs404 as $l) {
    $total404Hits += (int)($l['hits'] ?? 1);
}
?>

<div class="cms-main-content">
  
  <!-- PAGE HEADER -->
  <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
      <div style="font-size: 0.82rem; color: #94a3b8; margin-bottom: 4px;">
        <a href="/admin" style="color: #94a3b8; text-decoration: none;">Dashboard</a> &rsaquo; 
        <a href="/admin/seo_redirects" style="color: #94a3b8; text-decoration: none;">Redirects</a> &rsaquo; 
        <span style="color: #38bdf8; font-weight: 600;">404 Monitor</span>
      </div>
      <h1 style="font-size: 1.6rem; color: #fff; margin: 0; font-weight: 800;">
        Real-Time 404 Error Log &amp; Broken URL Monitor
      </h1>
      <p style="font-size: 0.88rem; color: #94a3b8; margin: 4px 0 0 0;">
        Track broken or missing URLs requested by visitors and search crawlers. Convert them into permanent 301 redirects with 1 click.
      </p>
    </div>

    <div style="display: flex; gap: 10px; align-items: center;">
      <a href="/admin/seo_redirects" class="tn-btn-secondary" style="padding: 10px 18px; border-radius: 8px; text-decoration: none; font-size: 0.88rem; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
        &larr; View Redirect Manager
      </a>
      <?php if (!empty($logs404)): ?>
        <form method="POST" action="/admin/seo_404" style="margin: 0;" onsubmit="return confirm('Are you sure you want to clear all 404 error logs?');">
          <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>" />
          <input type="hidden" name="action" value="clear_all_404s" />
          <button type="submit" style="padding: 10px 18px; background: #334155; color: #f87171; border: 1px solid #475569; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.88rem;">
            Clear All 404 Logs
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!empty($msg)): ?>
    <div style="background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); color: #4ade80; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
      <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($err)): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
      <?= htmlspecialchars($err) ?>
    </div>
  <?php endif; ?>

  <!-- METRIC STATS -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 18px;">
      <div style="font-size: 0.78rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Unique 404 URLs</div>
      <div style="font-size: 1.8rem; font-weight: 900; color: #f87171; margin-top: 4px;"><?= $total404Urls ?></div>
      <div style="font-size: 0.74rem; color: #94a3b8; margin-top: 4px;">Distinct broken paths requested</div>
    </div>
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 18px;">
      <div style="font-size: 0.78rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Total 404 Hits</div>
      <div style="font-size: 1.8rem; font-weight: 900; color: #fbbf24; margin-top: 4px;"><?= $total404Hits ?></div>
      <div style="font-size: 0.74rem; color: #94a3b8; margin-top: 4px;">Cumulative error requests intercepted</div>
    </div>
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 18px;">
      <div style="font-size: 0.78rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Crawler Protection</div>
      <div style="font-size: 1.8rem; font-weight: 900; color: #4ade80; margin-top: 4px;">Active</div>
      <div style="font-size: 0.74rem; color: #94a3b8; margin-top: 4px;">Real-time logging via SeoModel</div>
    </div>
  </div>

  <!-- TABLE CONTAINER -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.25);">
    <div style="padding: 16px 20px; background: #0f172a; border-bottom: 1px solid #334155; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
      <div style="font-size: 1rem; font-weight: 800; color: #f8fafc;">
        Detected 404 Incidents
      </div>
      <div style="font-size: 0.78rem; color: #94a3b8;">
        Showing up to 250 most recent 404 events
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
              <td colspan="5" style="padding: 40px; text-align: center; color: #94a3b8;">
                No 404 errors logged. Your website crawl paths are completely clean!
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($logs404 as $l): ?>
              <tr style="border-bottom: 1px solid #334155;">
                <td style="padding: 12px 18px; font-family: monospace; color: #f87171; font-weight: 600;">
                  <?= htmlspecialchars($l['url']) ?>
                </td>
                <td style="padding: 12px 18px; color: #94a3b8; max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                  <?= htmlspecialchars($l['referrer'] ?: '(Direct / External Crawl)') ?>
                </td>
                <td style="padding: 12px 18px; text-align: center; font-weight: 800; color: #fbbf24;">
                  <?= (int)$l['hits'] ?>
                </td>
                <td style="padding: 12px 18px; color: #94a3b8; white-space: nowrap;">
                  <?= htmlspecialchars($l['last_seen']) ?>
                </td>
                <td style="padding: 12px 18px; text-align: right; white-space: nowrap;">
                  <button type="button" onclick="openCreateRedirectModal('<?= htmlspecialchars($l['url'], ENT_QUOTES) ?>', <?= (int)$l['id'] ?>)" class="tn-btn-gold" style="padding: 6px 14px; border: none; border-radius: 6px; font-size: 0.78rem; font-weight: 700; cursor: pointer; margin-right: 8px;">
                    + 301 Redirect
                  </button>
                  <a href="/admin/seo_404?action=dismiss&id=<?= $l['id'] ?>&csrf_token=<?= urlencode($csrfToken) ?>" style="color: #94a3b8; font-size: 0.8rem; text-decoration: none; padding: 4px 8px; border-radius: 4px; border: 1px solid #334155;">
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

<!-- 1-CLICK CREATE REDIRECT MODAL -->
<div id="createRedirectModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
  <div style="background: #1e293b; border: 1px solid #475569; border-radius: 14px; max-width: 520px; width: 100%; padding: 24px; box-sizing: border-box; box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
      <h3 style="margin: 0; font-size: 1.15rem; color: #f8fafc; font-weight: 800;">
        Create 301 Permanent Redirect
      </h3>
      <button type="button" onclick="closeCreateRedirectModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 1.4rem; cursor: pointer;">&times;</button>
    </div>

    <form method="POST" action="/admin/seo_404" style="margin: 0;">
      <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>" />
      <input type="hidden" name="action" value="save_redirect" />
      <input type="hidden" name="dismiss_404_id" id="mDismiss404Id" value="" />
      <input type="hidden" name="status_code" value="301" />
      <input type="hidden" name="is_active" value="1" />

      <div style="margin-bottom: 14px;">
        <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">
          Old / Broken URL (Source)
        </label>
        <input type="text" id="mOldUrl" name="old_url" required readonly style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #f87171; font-family: monospace; font-size: 0.86rem;" />
      </div>

      <div style="margin-bottom: 18px;">
        <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">
          Destination URL (Target) *
        </label>
        <input type="text" id="mNewUrl" name="new_url" required value="/" placeholder="/products or /product/slug" style="width: 100%; box-sizing: border-box; padding: 10px 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" />
        <div style="font-size: 0.74rem; color: #94a3b8; margin-top: 4px;">Enter relative path (e.g. /products) or full URL</div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #334155; padding-top: 16px;">
        <button type="button" onclick="closeCreateRedirectModal()" style="padding: 9px 16px; background: #334155; color: #cbd5e1; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">
          Cancel
        </button>
        <button type="submit" class="tn-btn-gold" style="padding: 9px 20px; border: none; border-radius: 6px; font-weight: 700; cursor: pointer;">
          Create 301 &amp; Dismiss
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openCreateRedirectModal(oldUrl, id) {
  document.getElementById('mOldUrl').value = oldUrl || '';
  document.getElementById('mDismiss404Id').value = id || '';
  document.getElementById('createRedirectModal').style.display = 'flex';
}

function closeCreateRedirectModal() {
  document.getElementById('createRedirectModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

