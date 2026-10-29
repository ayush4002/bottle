<?php
// ==========================================================================
// TRUENORTH CMS — CLIENT INQUIRIES & WHOLESALE RFQ LEADS
// View, filter, reply via email, share on WhatsApp, and manage client leads
// ==========================================================================

require_once __DIR__ . '/auth.php';
checkAdminAuth();

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Models/InquiryModel.php';
require_once dirname(__DIR__) . '/app/Services/MailService.php';

$message = '';
$error = '';
$currentUser = $_SESSION['admin_user'] ?? 'TrueNorth Sales';

// Handle CSV Export
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    $all = InquiryModel::getAllInquiries();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=truenorth_inquiries_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Reference ID', 'Date', 'Status', 'Name', 'Company', 'Email', 'Phone', 'Country', 'Product/Subject', 'Inquiry Message', 'Replies Count']);
    foreach ($all as $r) {
        $repliesCount = isset($r['replies']) && is_array($r['replies']) ? count($r['replies']) : 0;
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
            str_replace(["\r", "\n"], ' ', $r['inquiry'] ?? ''),
            $repliesCount
        ]);
    }
    fclose($output);
    exit();
}

// Handle POST Actions (Reply, Status Update, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrf)) {
        $error = "Security validation failed. Please refresh the page and try again.";
    } elseif ($_POST['action'] === 'send_reply') {
        $targetId = trim($_POST['inquiry_id'] ?? '');
        $clientEmail = trim($_POST['client_email'] ?? '');
        $clientName = trim($_POST['client_name'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $replyMessage = trim($_POST['message'] ?? '');

        if (empty($clientEmail) || !filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
            $error = "A valid client email address is required to dispatch reply.";
        } elseif (empty($replyMessage)) {
            $error = "Reply message body cannot be empty.";
        } else {
            $sent = MailService::sendCustomReply($clientEmail, $clientName, $subject, $replyMessage, $targetId, $currentUser);

            InquiryModel::saveReply($targetId, [
                'subject' => $subject,
                'message' => $replyMessage,
                'admin'   => $currentUser,
                'to'      => $clientEmail
            ]);

            if ($sent['success']) {
                $message = "Reply successfully sent to " . htmlspecialchars($clientEmail) . " (" . htmlspecialchars($clientName) . ")! Inquiry marked as CONTACTED.";
            } else {
                $message = "Reply recorded in history and status updated to CONTACTED. (Server transport notice: " . htmlspecialchars($sent['message'] ?? 'Email queued') . ")";
            }
        }
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
  
  /* ACTION BUTTONS */
  .btn-reply-act {
    background: linear-gradient(135deg, #b8892e, #d4a34b);
    color: #ffffff;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.18s ease;
    box-shadow: 0 2px 6px rgba(184, 137, 46, 0.25);
  }
  .btn-reply-act:hover {
    filter: brightness(1.1);
    transform: translateY(-1px);
  }
  .btn-view-act {
    background: #0f172a;
    border: 1px solid #475569;
    color: #cbd5e1;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.15s;
  }
  .btn-view-act:hover {
    border-color: #38bdf8;
    color: #fff;
  }
  .btn-wa-act {
    background: rgba(34, 197, 94, 0.15);
    border: 1px solid rgba(34, 197, 94, 0.4);
    color: #4ade80;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }
  .btn-wa-act:hover {
    background: rgba(34, 197, 94, 0.25);
  }
  .badge-reply-pill {
    background: rgba(14, 165, 233, 0.2);
    border: 1px solid rgba(14, 165, 233, 0.4);
    color: #38bdf8;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 4px;
    margin-top: 4px;
    display: inline-block;
  }

  /* MODALS */
  .inq-modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(5px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    padding: 16px;
  }
  .inq-modal-overlay.open {
    display: flex;
  }
  .inq-modal-box {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 16px;
    width: 100%;
    max-width: 680px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 28px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    box-sizing: border-box;
  }
  .inq-tpl-btn {
    background: #0f172a;
    border: 1px solid #475569;
    color: #94a3b8;
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
  }
  .inq-tpl-btn:hover {
    border-color: #b8892e;
    color: #ffffff;
    background: rgba(184, 137, 46, 0.15);
  }
</style>

<div style="margin-bottom: 24px;">
  
  <!-- Header Title & Export -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
      <h1 style="font-size: 1.6rem; font-weight: 800; color: #f8fafc; margin: 0 0 4px;">Client Inquiries &amp; RFQ Leads</h1>
      <p style="color: #94a3b8; font-size: 0.88rem; margin: 0;">Manage quote requests, reply directly via email, and track customer communication</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <a href="/admin/inquiries?action=export_csv" class="tn-btn-gold" style="margin: 0; padding: 10px 18px; font-size: 0.85rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Export CSV
      </a>
      <a href="/admin/settings" style="background: #334155; color: #fff; text-decoration: none; padding: 10px 16px; border-radius: 6px; font-weight: 600; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        Email &amp; SMTP Settings
      </a>
    </div>
  </div>

  <?php if (!empty($message)): ?>
    <div style="background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); color: #4ade80; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      <span><?= htmlspecialchars($message) ?></span>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <span><?= htmlspecialchars($error) ?></span>
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
      <div style="font-size: 0.75rem; font-weight: 700; color: #eab308; text-transform: uppercase; margin-bottom: 6px;">Contacted / Replied</div>
      <div style="font-size: 1.9rem; font-weight: 800; color: #eab308; line-height: 1;"><?= $stats['contacted'] ?></div>
      <div style="font-size: 0.75rem; color: #64748b; margin-top: 6px;">Quote / email dispatched</div>
    </div>
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: 18px 20px;">
      <div style="font-size: 0.75rem; font-weight: 700; color: #4ade80; text-transform: uppercase; margin-bottom: 6px;">Completed / Closed</div>
      <div style="font-size: 1.9rem; font-weight: 800; color: #4ade80; line-height: 1;"><?= $stats['closed'] ?></div>
      <div style="font-size: 0.75rem; color: #64748b; margin-top: 6px;">Deal finalized</div>
    </div>
  </div>

  <!-- Filters & Search Bar -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
    
    <!-- Status Tabs -->
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
      <a href="/admin/inquiries?status=all<?= $searchQuery ? '&q=' . urlencode($searchQuery) : '' ?>" class="inq-filter-tab <?= $statusFilter === 'all' ? 'active' : '' ?>">
        All (<?= $stats['total'] ?>)
      </a>
      <a href="/admin/inquiries?status=new<?= $searchQuery ? '&q=' . urlencode($searchQuery) : '' ?>" class="inq-filter-tab <?= $statusFilter === 'new' ? 'active' : '' ?>">
        New (<?= $stats['new'] ?>)
      </a>
      <a href="/admin/inquiries?status=contacted<?= $searchQuery ? '&q=' . urlencode($searchQuery) : '' ?>" class="inq-filter-tab <?= $statusFilter === 'contacted' ? 'active' : '' ?>">
        Contacted (<?= $stats['contacted'] ?>)
      </a>
      <a href="/admin/inquiries?status=closed<?= $searchQuery ? '&q=' . urlencode($searchQuery) : '' ?>" class="inq-filter-tab <?= $statusFilter === 'closed' ? 'active' : '' ?>">
        Closed (<?= $stats['closed'] ?>)
      </a>
    </div>

    <!-- Keyword Search -->
    <form action="/admin/inquiries" method="GET" style="display: flex; gap: 8px;">
      <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>" />
      <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search name, email, company, ref..." style="background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 8px 14px; border-radius: 6px; font-size: 0.85rem; outline: none; width: 240px;" />
      <button type="submit" style="background: #334155; color: #fff; border: none; border-radius: 6px; padding: 8px 14px; font-size: 0.85rem; cursor: pointer; font-weight: 600;">Search</button>
      <?php if (!empty($searchQuery)): ?>
        <a href="/admin/inquiries?status=<?= urlencode($statusFilter) ?>" style="background: transparent; color: #94a3b8; padding: 8px; text-decoration: none; font-size: 0.85rem;">Clear</a>
      <?php endif; ?>
    </form>

  </div>

  <!-- Inquiries Table -->
  <div class="inq-table-wrap">
    <div style="overflow-x: auto;">
      <table class="inq-table">
        <thead>
          <tr>
            <th style="width: 150px;">Ref / Date</th>
            <th style="width: 200px;">Client &amp; Company</th>
            <th style="width: 180px;">Contact Details</th>
            <th>Subject &amp; Requirements</th>
            <th style="width: 130px;">Status</th>
            <th style="width: 210px; text-align: right;">Actions</th>
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
                $repliesList = $row['replies'] ?? [];
                $repliesCount = is_array($repliesList) ? count($repliesList) : 0;
                $phoneClean = preg_replace('/[^0-9]/', '', $row['phone'] ?? '');
              ?>
              <tr>
                <td>
                  <strong style="color: #b8892e; font-size: 0.88rem; cursor: pointer;" onclick="openDetailsModal(<?= htmlspecialchars(json_encode($row)) ?>)" title="Click to view details">
                    <?= htmlspecialchars($row['quote_id'] ?? 'INQ') ?>
                  </strong>
                  <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">
                    <?= date('d M Y, h:i A', strtotime($row['created_at'] ?? 'now')) ?>
                  </div>
                  <?php if ($repliesCount > 0): ?>
                    <span class="badge-reply-pill" style="cursor: pointer;" onclick="openDetailsModal(<?= htmlspecialchars(json_encode($row)) ?>)">
                      &bull; <?= $repliesCount ?> <?= $repliesCount === 1 ? 'reply sent' : 'replies sent' ?>
                    </span>
                  <?php endif; ?>
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
                      <a href="javascript:void(0)" onclick="openReplyModal(<?= htmlspecialchars(json_encode($row)) ?>)" style="color: #38bdf8; text-decoration: none; font-size: 0.82rem; word-break: break-all;">
                        <?= htmlspecialchars($row['email']) ?>
                      </a>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($row['phone'])): ?>
                    <div style="margin-top: 4px; display: flex; align-items: center; gap: 6px;">
                      <a href="tel:<?= htmlspecialchars($row['phone']) ?>" style="color: #cbd5e1; text-decoration: none; font-size: 0.8rem;">
                        <?= htmlspecialchars($row['phone']) ?>
                      </a>
                      <?php if (!empty($phoneClean)): ?>
                        <a href="https://wa.me/<?= htmlspecialchars($phoneClean) ?>?text=<?= urlencode("Hello " . ($row['name'] ?? 'Client') . ", this is TrueNorth Group regarding your inquiry " . ($row['quote_id'] ?? '')) ?>" target="_blank" rel="noopener noreferrer" title="Chat on WhatsApp" style="color: #4ade80;">
                          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9 L3 21l1.9-5.7 a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5 a8.48 8.48 0 0 1 8 8v.5z"/><path d="M9 9a5 5 0 0 0 6 6"/></svg>
                        </a>
                      <?php endif; ?>
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
                  <div style="color: #cbd5e1; font-size: 0.85rem; line-height: 1.5; max-height: 65px; overflow-y: auto;">
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
                  <div style="display: flex; justify-content: flex-end; gap: 6px; align-items: center; flex-wrap: wrap;">
                    
                    <!-- Working Interactive Reply Button -->
                    <button type="button" class="btn-reply-act" data-quote-id="<?= htmlspecialchars($quoteId) ?>" onclick="openReplyModal(<?= htmlspecialchars(json_encode($row)) ?>)" title="Write and send email reply to client">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>
                      Reply
                    </button>

                    <!-- View Details Button -->
                    <button type="button" class="btn-view-act" onclick="openDetailsModal(<?= htmlspecialchars(json_encode($row)) ?>)" title="View full inquiry & history">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>

                    <!-- Delete Button -->
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete inquiry <?= htmlspecialchars($row['quote_id'] ?? '') ?>?');" style="margin: 0;">
                      <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>" />
                      <input type="hidden" name="action" value="delete" />
                      <input type="hidden" name="inquiry_id" value="<?= htmlspecialchars($rowId) ?>" />
                      <button type="submit" style="background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #f87171; border-radius: 6px; padding: 6px 8px; font-size: 0.78rem; cursor: pointer;" title="Delete">
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

<!-- =========================================================================
     REPLY COMPOSER MODAL (WORKING EMAIL DISPATCH & WHATSAPP SHARING)
     ========================================================================= -->
<div class="inq-modal-overlay" id="replyModal">
  <div class="inq-modal-box">
    
    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #334155; padding-bottom: 16px; margin-bottom: 20px;">
      <div>
        <h3 style="margin: 0; font-size: 1.25rem; color: #f8fafc; font-weight: 800;">
          Reply to Client Inquiry
        </h3>
        <span style="font-size: 0.8rem; color: #b8892e; font-weight: 700;" id="replyRefTitle">Ref: INQ</span>
      </div>
      <button type="button" onclick="closeReplyModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 1.6rem; cursor: pointer; padding: 0 4px;">&times;</button>
    </div>

    <!-- Client Quick Summary Bar -->
    <div style="background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 12px 16px; margin-bottom: 18px; font-size: 0.84rem; display: flex; flex-direction: column; gap: 4px;">
      <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
        <div>Client: <strong style="color: #f8fafc;" id="replyClientName">Name</strong> <span style="color: #94a3b8;" id="replyClientCompany"></span></div>
        <div>Email: <strong style="color: #38bdf8;" id="replyClientEmail">email@example.com</strong></div>
      </div>
      <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
        <div id="replyClientPhoneWrap">Phone: <span style="color: #cbd5e1;" id="replyClientPhone">N/A</span></div>
        <div id="replyClientProductWrap">Product: <span style="color: #b8892e; font-weight: 700;" id="replyClientProduct">General</span></div>
      </div>
    </div>

    <!-- Client Original Message (Collapsible Reference) -->
    <div style="margin-bottom: 18px;">
      <label style="font-size: 0.76rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px;">Original Client Requirement:</label>
      <div id="replyOriginalInquiry" style="background: #0b1120; border-left: 3px solid #b8892e; padding: 10px 14px; font-size: 0.82rem; color: #cbd5e1; max-height: 90px; overflow-y: auto; border-radius: 0 6px 6px 0; line-height: 1.5;">
      </div>
    </div>

    <!-- Quick Template Selection Buttons -->
    <div style="margin-bottom: 16px;">
      <label style="font-size: 0.76rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Insert Quick Response Template:</label>
      <div style="display: flex; gap: 6px; flex-wrap: wrap;">
        <button type="button" class="inq-tpl-btn" onclick="applyTemplate('quote')">📋 Official Quote</button>
        <button type="button" class="inq-tpl-btn" onclick="applyTemplate('sample')">📦 Sample Dispatch</button>
        <button type="button" class="inq-tpl-btn" onclick="applyTemplate('tooling')">⚙️ Custom Moulding</button>
        <button type="button" class="inq-tpl-btn" onclick="applyTemplate('catalog')">📑 MOQ &amp; Lead Time</button>
      </div>
    </div>

    <!-- Reply Form -->
    <form method="POST" action="/admin/inquiries" id="replyForm" onsubmit="return handleReplySubmit(event)">
      <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>" />
      <input type="hidden" name="action" value="send_reply" />
      <input type="hidden" name="inquiry_id" id="replyInquiryId" value="" />
      <input type="hidden" name="client_email" id="replyFormClientEmail" value="" />
      <input type="hidden" name="client_name" id="replyFormClientName" value="" />

      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Email Subject *</label>
        <input type="text" name="subject" id="replySubject" required style="width: 100%; box-sizing: border-box; padding: 10px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" />
      </div>

      <div style="margin-bottom: 22px;">
        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">Reply Message Body *</label>
        <textarea name="message" id="replyMessage" rows="9" required style="width: 100%; box-sizing: border-box; padding: 12px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem; font-family: inherit; line-height: 1.6; resize: vertical;"></textarea>
      </div>

      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; border-top: 1px solid #334155; padding-top: 18px;">
        <div id="replyWhatsAppWrap">
          <a href="#" id="replyWhatsAppBtn" target="_blank" rel="noopener noreferrer" class="btn-wa-act" style="padding: 8px 14px; font-size: 0.82rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9 L3 21l1.9-5.7 a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5 a8.48 8.48 0 0 1 8 8v.5z"/><path d="M9 9a5 5 0 0 0 6 6"/></svg>
            Share via WhatsApp &nearr;
          </a>
        </div>

        <div style="display: flex; gap: 10px;">
          <button type="button" onclick="closeReplyModal()" style="background: transparent; border: 1px solid #475569; color: #cbd5e1; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.85rem;">Cancel</button>
          <button type="submit" id="replySubmitBtn" class="tn-btn-gold" style="margin: 0; padding: 10px 22px; font-size: 0.88rem; font-weight: 700; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Send Email Reply &rarr;
          </button>
        </div>
      </div>
    </form>

  </div>
</div>

<!-- =========================================================================
     INQUIRY DETAILS & REPLY HISTORY MODAL
     ========================================================================= -->
<div class="inq-modal-overlay" id="detailsModal">
  <div class="inq-modal-box">
    
    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #334155; padding-bottom: 16px; margin-bottom: 20px;">
      <div>
        <h3 style="margin: 0; font-size: 1.25rem; color: #f8fafc; font-weight: 800;">
          Inquiry &amp; Communication Dossier
        </h3>
        <span style="font-size: 0.82rem; color: #b8892e; font-weight: 700;" id="detRefTitle">Ref: INQ</span>
      </div>
      <button type="button" onclick="closeDetailsModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 1.6rem; cursor: pointer; padding: 0 4px;">&times;</button>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 16px; margin-bottom: 20px; font-size: 0.85rem;">
      <div><span style="color: #64748b;">Client Name:</span> <strong style="color: #fff; display: block;" id="detName"></strong></div>
      <div><span style="color: #64748b;">Company:</span> <strong style="color: #fff; display: block;" id="detCompany"></strong></div>
      <div><span style="color: #64748b;">Email Address:</span> <strong style="color: #38bdf8; display: block;" id="detEmail"></strong></div>
      <div><span style="color: #64748b;">Phone / WhatsApp:</span> <strong style="color: #cbd5e1; display: block;" id="detPhone"></strong></div>
      <div><span style="color: #64748b;">Country / Region:</span> <strong style="color: #fff; display: block;" id="detCountry"></strong></div>
      <div><span style="color: #64748b;">Associated Product:</span> <strong style="color: #b8892e; display: block;" id="detProduct"></strong></div>
    </div>

    <div style="margin-bottom: 24px;">
      <h4 style="margin: 0 0 8px; font-size: 0.85rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Client Submission Details:</h4>
      <div id="detMessage" style="background: #0b1120; border: 1px solid #334155; border-radius: 8px; padding: 14px 16px; color: #e2e8f0; font-size: 0.88rem; line-height: 1.6; max-height: 180px; overflow-y: auto;"></div>
    </div>

    <!-- Communication / Reply History -->
    <div>
      <h4 style="margin: 0 0 10px; font-size: 0.85rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Sent Replies &amp; Outgoing Activity:</h4>
      <div id="detRepliesList" style="display: flex; flex-direction: column; gap: 10px;">
        <!-- Filled dynamically -->
      </div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; border-top: 1px solid #334155; padding-top: 16px;">
      <button type="button" id="detReplyBtn" class="tn-btn-gold" style="margin: 0; padding: 10px 20px; font-size: 0.85rem;">Write New Reply &rarr;</button>
      <button type="button" onclick="closeDetailsModal()" style="background: #334155; color: #fff; border: none; padding: 10px 18px; border-radius: 6px; font-size: 0.85rem; cursor: pointer;">Close</button>
    </div>

  </div>
</div>

<script>
let currentActiveInquiry = null;

function openReplyModal(inquiry) {
  currentActiveInquiry = inquiry;

  const id = inquiry.id || inquiry.quote_id;
  const qid = inquiry.quote_id || ('INQ-' + id);
  const name = inquiry.name || 'Valued Client';
  const email = inquiry.email || '';
  const company = inquiry.company || '';
  const phone = inquiry.phone || '';
  const product = inquiry.product || 'Packaging Solutions';
  const text = inquiry.inquiry || 'No additional details provided.';

  document.getElementById('replyRefTitle').innerText = 'Reference: ' + qid;
  document.getElementById('replyClientName').innerText = name;
  document.getElementById('replyClientCompany').innerText = company ? '(' + company + ')' : '';
  document.getElementById('replyClientEmail').innerText = email;
  document.getElementById('replyClientPhone').innerText = phone || 'N/A';
  document.getElementById('replyClientProduct').innerText = product;
  document.getElementById('replyOriginalInquiry').innerText = text;

  document.getElementById('replyInquiryId').value = id;
  document.getElementById('replyFormClientEmail').value = email;
  document.getElementById('replyFormClientName').value = name;
  document.getElementById('replySubject').value = 'Re: Quotation & Specification Inquiry [' + qid + '] — TrueNorth Group';

  // Apply default template
  applyTemplate('quote');

  // WhatsApp Button
  const cleanPhone = (phone || '').replace(/[^0-9]/g, '');
  const waBtn = document.getElementById('replyWhatsAppBtn');
  const waWrap = document.getElementById('replyWhatsAppWrap');
  if (cleanPhone.length >= 7) {
    waWrap.style.display = 'block';
    updateWhatsAppUrl();
  } else {
    waWrap.style.display = 'none';
  }

  document.getElementById('replyModal').classList.add('open');
}

function closeReplyModal() {
  document.getElementById('replyModal').classList.remove('open');
}

function applyTemplate(type) {
  if (!currentActiveInquiry) return;

  const name = currentActiveInquiry.name || 'Valued Client';
  const product = currentActiveInquiry.product || 'primary packaging bottles & closures';
  const qid = currentActiveInquiry.quote_id || 'INQ';

  let body = '';

  if (type === 'quote') {
    body = `Dear ${name},\n\nThank you for reaching out to TrueNorth Group regarding your inquiry for ${product} (Ref: ${qid}).\n\nWe have reviewed your specifications and are pleased to provide the initial commercial framework:\n\n• Material & Finish: High-grade virgin PET / PCR polymer with precision finish\n• Standard Minimum Order Quantity (MOQ): 5,000 to 10,000 units per production run\n• Production Lead Time: 14 to 21 business days upon purchase order approval\n• Logistics & Shipping: Worldwide seaport exports from Daman, India & Ningbo, China\n\nPlease reply with your target order volumes and delivery seaport/location so our commercial team can forward an official itemized proforma invoice.\n\nWarm regards,\nSales & Technical Support Team\nTrueNorth Group\nPhone / WhatsApp: +91 98102 20761\nEmail: info@wetruenorthgroup.com\nWebsite: https://wetruenorthgroup.com`;
  } else if (type === 'sample') {
    body = `Dear ${name},\n\nThank you for your interest in TrueNorth Group's ${product}.\n\nWe would be delighted to dispatch pre-production physical samples for your laboratory testing and brand evaluation.\n\nCould you please confirm:\n1. The complete delivery address with postal code and recipient contact number\n2. Preferred neck closure / pump compatibility requirements\n3. Your corporate courier account number (DHL/FedEx) if applicable\n\nOnce confirmed, samples will be dispatched promptly within 48 hours.\n\nBest regards,\nSampling & Logistics Team\nTrueNorth Group\nPhone / WhatsApp: +91 98102 20761`;
  } else if (type === 'tooling') {
    body = `Dear ${name},\n\nThank you for inquiring about custom packaging development with TrueNorth Group.\n\nAs an integrated primary packaging manufacturer, we specialize in custom mould engineering, 3D CAD rapid prototyping, and proprietary blow/injection moulding tooling.\n\nTo proceed with your custom design, please share:\n• 2D/3D technical drawings (.step / .igs / .dwg) or reference physical samples\n• Target brimful capacity and required neck thread standard (e.g. 24/410, 28/410)\n• Estimated annual consumption volumes\n\nWe look forward to partnering on your custom bottle development.\n\nSincerely,\nEngineering & Design Team\nTrueNorth Group`;
  } else if (type === 'catalog') {
    body = `Dear ${name},\n\nThank you for contacting TrueNorth Group regarding ${product}.\n\nAttached to this thread is our updated packaging catalogue showcasing our standard PET bottles, jars, pumps, and precision closures.\n\nKey Highlights:\n• Cleanroom manufacturing compliant with Class 10,000 standards\n• ISO 9001:2015 & US FDA approved food and pharma grade polymers\n• Full custom colour matching and hot-stamping / silk-screen printing options\n\nPlease review and let us know which models best suit your brand requirements.\n\nWarm regards,\nTrueNorth Group Sales\nEmail: info@wetruenorthgroup.com`;
  }

  document.getElementById('replyMessage').value = body;
  updateWhatsAppUrl();
}

function updateWhatsAppUrl() {
  if (!currentActiveInquiry) return;
  const phone = (currentActiveInquiry.phone || '').replace(/[^0-9]/g, '');
  if (!phone) return;

  const msg = document.getElementById('replyMessage').value;
  const waUrl = 'https://wa.me/' + phone + '?text=' + encodeURIComponent(msg);
  const btn = document.getElementById('replyWhatsAppBtn');
  if (btn) btn.href = waUrl;
}

document.getElementById('replyMessage').addEventListener('input', updateWhatsAppUrl);

function handleReplySubmit(e) {
  const btn = document.getElementById('replySubmitBtn');
  btn.innerHTML = '<span style="display:inline-block;animation:spin 1s linear infinite;">⏳</span> Sending Email...';
  btn.disabled = true;
  return true;
}

function openDetailsModal(inquiry) {
  currentActiveInquiry = inquiry;

  const qid = inquiry.quote_id || 'INQ';
  document.getElementById('detRefTitle').innerText = 'Reference: ' + qid;
  document.getElementById('detName').innerText = inquiry.name || 'N/A';
  document.getElementById('detCompany').innerText = inquiry.company || 'N/A';
  document.getElementById('detEmail').innerText = inquiry.email || 'N/A';
  document.getElementById('detPhone').innerText = inquiry.phone || 'N/A';
  document.getElementById('detCountry').innerText = inquiry.country || 'N/A';
  document.getElementById('detProduct').innerText = inquiry.product || 'General Inquiry';
  document.getElementById('detMessage').innerText = inquiry.inquiry || 'No message content provided.';

  // Render Replies List
  const replies = inquiry.replies || [];
  const repList = document.getElementById('detRepliesList');
  if (Array.isArray(replies) && replies.length > 0) {
    let repHtml = '';
    replies.forEach(function(r) {
      repHtml += `
        <div style="background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 12px 14px; font-size: 0.82rem;">
          <div style="display: flex; justify-content: space-between; margin-bottom: 6px; border-bottom: 1px solid rgba(51, 65, 85, 0.4); padding-bottom: 4px;">
            <strong style="color: #38bdf8;">${escapeHtml(r.subject || 'Quotation Reply')}</strong>
            <span style="color: #64748b; font-size: 0.74rem;">${escapeHtml(r.date || '')} &bull; by ${escapeHtml(r.admin || 'Sales')}</span>
          </div>
          <div style="color: #cbd5e1; white-space: pre-line; line-height: 1.5;">${escapeHtml(r.message || '')}</div>
        </div>
      `;
    });
    repList.innerHTML = repHtml;
  } else {
    repList.innerHTML = '<div style="color: #64748b; font-size: 0.82rem; font-style: italic;">No replies sent yet to this inquiry.</div>';
  }

  // Hook reply button
  document.getElementById('detReplyBtn').onclick = function() {
    closeDetailsModal();
    openReplyModal(inquiry);
  };

  document.getElementById('detailsModal').classList.add('open');
}

function closeDetailsModal() {
  document.getElementById('detailsModal').classList.remove('open');
}

function escapeHtml(text) {
  return String(text || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// Auto-open reply modal from URL parameter e.g. ?reply=TN-REQ-1234
window.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);
  const replyQuoteId = urlParams.get('reply');
  if (replyQuoteId) {
    const targetBtn = document.querySelector(`button[data-quote-id="${replyQuoteId}"]`);
    if (targetBtn) {
      targetBtn.click();
    }
  }
});

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeReplyModal();
    closeDetailsModal();
  }
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
