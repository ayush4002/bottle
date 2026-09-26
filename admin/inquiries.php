<?php
// ==========================================================================
// TRUENORTH CMS — CLIENT INQUIRIES & WHOLESALE RFQ LEADS
// View, filter, update status, and export client inquiries
// ==========================================================================

require_once __DIR__ . '/auth.php';
checkAdminAuth();

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Models/InquiryModel.php';

$message = '';
$error = '';

// Handle CSV Export
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    $all = InquiryModel::getAllInquiries();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=truenorth_inquiries_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Reference ID', 'Date', 'Status', 'Name', 'Company', 'Email', 'Phone', 'Country', 'Product/Subject', 'Inquiry Message']);
    foreach ($all as $r) {
        fputcsv($output, [
            $r['quote_id'] ?? '',
            $r['created_at'] ?? '',
            $r['status'] ?? 'new',
            $r['name'] ?? '',
            $r['company'] ?? '',
            $r['email'] ?? '',
            $r['phone'] ?? '',
            $r['country'] ?? '',
            $r['product'] ?? '',
            str_replace(["\r", "\n"], ' ', $r['inquiry'] ?? '')
        ]);
    }
    fclose($output);
    exit();
}

// Handle Status Update via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrf)) {
        $error = "Security validation failed. Please refresh.";
    } elseif ($_POST['action'] === 'update_status') {
        $targetId = $_POST['inquiry_id'] ?? '';
        $newStatus = $_POST['status'] ?? 'new';
        if (InquiryModel::updateStatus($targetId, $newStatus)) {
            $message = "Inquiry status updated to " . strtoupper($newStatus) . " successfully!";
        } else {
            $error = "Failed to update inquiry status.";
        }
    } elseif ($_POST['action'] === 'delete') {
        $targetId = $_POST['inquiry_id'] ?? '';
        if (InquiryModel::deleteInquiry($targetId)) {
            $message = "Inquiry deleted successfully.";
        } else {
            $error = "Failed to delete inquiry.";
        }
    }
}

$statusFilter = $_GET['status'] ?? 'all';
$searchQuery = trim($_GET['q'] ?? '');

$inquiries = InquiryModel::getAllInquiries($statusFilter, $searchQuery);
$stats = InquiryModel::getStats();

$currentPage = 'inquiries';
$crumbTitle = 'Client Inquiries';
require_once __DIR__ . '/header.php';
?>

<style>
  .inq-stat-badge {
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 5px;
  }
  .inq-status-new {
    background: rgba(56, 189, 248, 0.15);
    color: #38bdf8;
    border: 1px solid rgba(56, 189, 248, 0.35);
  }
  .inq-status-contacted {
    background: rgba(184, 137, 46, 0.18);
    color: #eab308;
    border: 1px solid rgba(184, 137, 46, 0.35);
  }
  .inq-status-closed {
    background: rgba(34, 197, 94, 0.15);
    color: #4ade80;
    border: 1px solid rgba(34, 197, 94, 0.35);
  }
  .inq-table-wrap {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  }
  .inq-table {
    width: 100%;
    border-collapse: collapse;
    color: #e2e8f0;
    font-size: 0.88rem;
    text-align: left;
  }
  .inq-table th {
    background: #0f172a;
    padding: 14px 16px;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #94a3b8;
    border-bottom: 1px solid #334155;
    font-weight: 700;
  }
  .inq-table td {
    padding: 14px 16px;
    border-bottom: 1px solid #334155;
    vertical-align: top;
  }
  .inq-table tr:hover td {
    background: rgba(255,255,255,0.02);
  }
  .inq-filter-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 700;
    color: #94a3b8;
    text-decoration: none;
    transition: all 0.15s ease;
    border: 1px solid transparent;
  }
  .inq-filter-tab:hover {
    color: #fff;
    background: #334155;
  }
  .inq-filter-tab.active {
    background: #0284c7;
    color: #fff;
  }
</style>

<div style="margin-bottom: 24px;">
  
  <!-- Header Title & Export -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
      <h1 style="font-size: 1.6rem; font-weight: 800; color: #f8fafc; margin: 0 0 4px;">Client Inquiries &amp; RFQ Leads</h1>
      <p style="color: #94a3b8; font-size: 0.88rem; margin: 0;">Direct factory quote requests and sample inquiries from wetruenorthgroup.com</p>
    </div>
    <div style="display: flex; gap: 10px;">
      <a href="/admin/inquiries.php?action=export_csv" class="frapak-btn-gold" style="margin: 0; padding: 10px 18px; font-size: 0.85rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Export CSV
      </a>
      <a href="/admin/settings.php" style="background: #334155; color: #fff; text-decoration: none; padding: 10px 16px; border-radius: 6px; font-weight: 600; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px;">
        Email Settings &rarr;
      </a>
    </div>
  </div>

  <?php if (!empty($message)): ?>
    <div style="background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); color: #4ade80; padding: 12px 18px; border-radius: 8px; font-size: 0.88rem; margin-bottom: 20px;">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; padding: 12px 18px; border-radius: 8px; font-size: 0.88rem; margin-bottom: 20px;">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <!-- Stats Grid -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: 18px 20px;">
      <div style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Total Inquiries</div>
      <div style="font-size: 1.9rem; font-weight: 800; color: #f8fafc; line-height: 1;"><?= $stats['total'] ?></div>
      <div style="font-size: 0.75rem; color: #64748b; margin-top: 6px;">All submitted quotes</div>
    </div>
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: 18px 20px;">
      <div style="font-size: 0.75rem; font-weight: 700; color: #38bdf8; text-transform: uppercase; margin-bottom: 6px;">New Leads</div>
      <div style="font-size: 1.9rem; font-weight: 800; color: #38bdf8; line-height: 1;"><?= $stats['new'] ?></div>
      <div style="font-size: 0.75rem; color: #64748b; margin-top: 6px;">Pending sales review</div>
    </div>
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: 18px 20px;">
      <div style="font-size: 0.75rem; font-weight: 700; color: #eab308; text-transform: uppercase; margin-bottom: 6px;">Contacted / In Progress</div>
      <div style="font-size: 1.9rem; font-weight: 800; color: #eab308; line-height: 1;"><?= $stats['contacted'] ?></div>
      <div style="font-size: 0.75rem; color: #64748b; margin-top: 6px;">Samples / quote sent</div>
    </div>
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: 18px 20px;">
      <div style="font-size: 0.75rem; font-weight: 700; color: #4ade80; text-transform: uppercase; margin-bottom: 6px;">Completed / Closed</div>
      <div style="font-size: 1.9rem; font-weight: 800; color: #4ade80; line-height: 1;"><?= $stats['closed'] ?></div>
      <div style="font-size: 0.75rem; color: #64748b; margin-top: 6px;">Deal closed or fulfilled</div>
    </div>
  </div>

  <!-- Filters & Search Bar -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
    
    <!-- Status Tabs -->
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
      <a href="/admin/inquiries.php?status=all<?= $searchQuery ? '&q=' . urlencode($searchQuery) : '' ?>" class="inq-filter-tab <?= $statusFilter === 'all' ? 'active' : '' ?>">
        All (<?= $stats['total'] ?>)
      </a>
      <a href="/admin/inquiries.php?status=new<?= $searchQuery ? '&q=' . urlencode($searchQuery) : '' ?>" class="inq-filter-tab <?= $statusFilter === 'new' ? 'active' : '' ?>">
        New (<?= $stats['new'] ?>)
      </a>
      <a href="/admin/inquiries.php?status=contacted<?= $searchQuery ? '&q=' . urlencode($searchQuery) : '' ?>" class="inq-filter-tab <?= $statusFilter === 'contacted' ? 'active' : '' ?>">
        Contacted (<?= $stats['contacted'] ?>)
      </a>
      <a href="/admin/inquiries.php?status=closed<?= $searchQuery ? '&q=' . urlencode($searchQuery) : '' ?>" class="inq-filter-tab <?= $statusFilter === 'closed' ? 'active' : '' ?>">
        Closed (<?= $stats['closed'] ?>)
      </a>
    </div>

    <!-- Keyword Search -->
    <form action="/admin/inquiries.php" method="GET" style="display: flex; gap: 8px;">
      <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>" />
      <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search name, email, company, ref..." style="background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 8px 14px; border-radius: 6px; font-size: 0.85rem; outline: none; width: 240px;" />
      <button type="submit" style="background: #334155; color: #fff; border: none; border-radius: 6px; padding: 8px 14px; font-size: 0.85rem; cursor: pointer; font-weight: 600;">Search</button>
      <?php if (!empty($searchQuery)): ?>
        <a href="/admin/inquiries.php?status=<?= urlencode($statusFilter) ?>" style="background: transparent; color: #94a3b8; padding: 8px; text-decoration: none; font-size: 0.85rem;">Clear</a>
      <?php endif; ?>
    </form>

  </div>

  <!-- Inquiries Table -->
  <div class="inq-table-wrap">
    <div style="overflow-x: auto;">
      <table class="inq-table">
        <thead>
          <tr>
            <th style="width: 140px;">Reference / Date</th>
            <th style="width: 200px;">Client &amp; Company</th>
            <th style="width: 180px;">Contact Details</th>
            <th>Subject &amp; Requirements</th>
            <th style="width: 130px;">Status</th>
            <th style="width: 150px; text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($inquiries)): ?>
            <tr>
              <td colspan="6" style="text-align: center; padding: 48px 20px; color: #64748b;">
                <div style="font-size: 1.1rem; font-weight: 700; color: #94a3b8; margin-bottom: 6px;">No inquiries found</div>
                <p style="font-size: 0.85rem; margin: 0;">Inquiries submitted through client quote forms will automatically show up here.</p>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($inquiries as $row): ?>
              <?php
                $st = $row['status'] ?? 'new';
                $badgeClass = ($st === 'closed') ? 'inq-status-closed' : (($st === 'contacted') ? 'inq-status-contacted' : 'inq-status-new');
                $rowId = $row['id'] ?? $row['quote_id'];
              ?>
              <tr>
                <td>
                  <strong style="color: #b8892e; font-size: 0.88rem;"><?= htmlspecialchars($row['quote_id'] ?? 'INQ') ?></strong>
                  <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">
                    <?= date('d M Y, h:i A', strtotime($row['created_at'] ?? 'now')) ?>
                  </div>
                </td>

                <td>
                  <strong style="color: #f8fafc; font-size: 0.92rem;"><?= htmlspecialchars($row['name'] ?? 'N/A') ?></strong>
                  <?php if (!empty($row['company'])): ?>
                    <div style="color: #94a3b8; font-size: 0.8rem; margin-top: 2px;">
                      <?= htmlspecialchars($row['company']) ?>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($row['country'])): ?>
                    <div style="font-size: 0.75rem; color: #38bdf8; margin-top: 4px;">
                      📍 <?= htmlspecialchars($row['country']) ?>
                    </div>
                  <?php endif; ?>
                </td>

                <td>
                  <?php if (!empty($row['email'])): ?>
                    <div>
                      <a href="mailto:<?= htmlspecialchars($row['email']) ?>" style="color: #38bdf8; text-decoration: none; font-size: 0.82rem; word-break: break-all;">
                        <?= htmlspecialchars($row['email']) ?>
                      </a>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($row['phone'])): ?>
                    <div style="margin-top: 4px;">
                      <a href="tel:<?= htmlspecialchars($row['phone']) ?>" style="color: #cbd5e1; text-decoration: none; font-size: 0.8rem;">
                        <?= htmlspecialchars($row['phone']) ?>
                      </a>
                    </div>
                  <?php endif; ?>
                </td>

                <td>
                  <?php if (!empty($row['product'])): ?>
                    <div style="margin-bottom: 6px;">
                      <span style="background: rgba(184, 137, 46, 0.15); border: 1px solid #b8892e; color: #b8892e; font-weight: 700; font-size: 0.75rem; padding: 2px 8px; border-radius: 4px;">
                        <?= htmlspecialchars($row['product']) ?>
                      </span>
                    </div>
                  <?php endif; ?>
                  <div style="color: #cbd5e1; font-size: 0.85rem; line-height: 1.5; max-height: 80px; overflow-y: auto;">
                    <?= nl2br(htmlspecialchars($row['inquiry'] ?? '')) ?>
                  </div>
                </td>

                <td>
                  <form method="POST" style="margin: 0;">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>" />
                    <input type="hidden" name="action" value="update_status" />
                    <input type="hidden" name="inquiry_id" value="<?= htmlspecialchars($rowId) ?>" />
                    <select name="status" onchange="this.form.submit()" style="background: #0f172a; border: 1px solid #334155; color: #f8fafc; font-size: 0.78rem; font-weight: 700; border-radius: 6px; padding: 5px 8px; cursor: pointer; width: 100%;">
                      <option value="new" <?= $st === 'new' ? 'selected' : '' ?>>🔵 New</option>
                      <option value="contacted" <?= $st === 'contacted' ? 'selected' : '' ?>>🟡 Contacted</option>
                      <option value="closed" <?= $st === 'closed' ? 'selected' : '' ?>>🟢 Closed</option>
                    </select>
                  </form>
                </td>

                <td style="text-align: right;">
                  <div style="display: flex; justify-content: flex-end; gap: 6px; align-items: center;">
                    <?php if (!empty($row['email'])): ?>
                      <a href="mailto:<?= htmlspecialchars($row['email']) ?>?subject=Regarding%20your%20inquiry%20<?= urlencode($row['quote_id'] ?? '') ?>%20-%20TrueNorth%20Group" class="frapak-btn-gold" style="margin: 0; padding: 6px 10px; font-size: 0.75rem; text-decoration: none;" title="Reply via Email">
                        Reply
                      </a>
                    <?php endif; ?>

                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete inquiry <?= htmlspecialchars($row['quote_id'] ?? '') ?>?');" style="margin: 0;">
                      <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>" />
                      <input type="hidden" name="action" value="delete" />
                      <input type="hidden" name="inquiry_id" value="<?= htmlspecialchars($rowId) ?>" />
                      <button type="submit" style="background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #f87171; border-radius: 6px; padding: 6px 8px; font-size: 0.75rem; cursor: pointer;" title="Delete">
                        &times;
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
