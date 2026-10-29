<?php
require_once __DIR__ . '/auth.php';
checkAdminAuth();

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Models/ProductModel.php';
require_once dirname(__DIR__) . '/app/Services/MailService.php';

$settingsFile = defined('ROOT_PATH') ? ROOT_PATH . '/config/site_settings.json' : dirname(__DIR__) . '/config/site_settings.json';
$currentSettings = file_exists($settingsFile) ? (json_decode(file_get_contents($settingsFile), true) ?: []) : [];

$company = ProductModel::getCompanyInfo();

$siteName = $currentSettings['site_name'] ?? ($company['name'] ?? 'TrueNorth Group');
$tagline = $currentSettings['tagline'] ?? ($company['tagline'] ?? 'Primary Packaging Specialist — Engineered PET & Precision Closures');
$contactEmail = $currentSettings['contact_email'] ?? ($company['email'] ?? 'info@wetruenorthgroup.com');
$contactPhone = $currentSettings['contact_phone'] ?? ($company['phone'] ?? '+91 98102 20761');
$currency = $currentSettings['currency'] ?? '₹';
$hqAddress = $currentSettings['hq'] ?? ($company['hq'] ?? 'Global Manufacturing Operations | Audited Partner Network Serving Global Exports');
$sendClientConfirmation = isset($currentSettings['send_client_confirmation']) ? (int)$currentSettings['send_client_confirmation'] : 1;

// SMTP & Mail Settings
$mailMode = $currentSettings['mail_mode'] ?? 'smtp';
$smtpHost = $currentSettings['smtp_host'] ?? (defined('SMTP_HOST') ? SMTP_HOST : 'mail.wetruenorthgroup.com');
$smtpPort = $currentSettings['smtp_port'] ?? (defined('SMTP_PORT') ? SMTP_PORT : 465);
$smtpUser = $currentSettings['smtp_user'] ?? (defined('SMTP_USER') ? SMTP_USER : 'info@wetruenorthgroup.com');
$smtpPass = $currentSettings['smtp_pass'] ?? (defined('SMTP_PASS') ? SMTP_PASS : 'TrueNorth2026Group');
$fromName = $currentSettings['from_name'] ?? 'TrueNorth Group';
$fromEmail = $currentSettings['from_email'] ?? $contactEmail;
$replyTo = $currentSettings['reply_to'] ?? $contactEmail;

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    $action = $_POST['action'] ?? 'save_settings';

    if (!verifyCsrfToken($csrfToken)) {
        $error = "Invalid security token. Please try again.";
    } elseif ($action === 'test_email') {
        $testTarget = trim($_POST['test_email_address'] ?? $contactEmail);
        if (empty($testTarget) || !filter_var($testTarget, FILTER_VALIDATE_EMAIL)) {
            $error = "Please provide a valid destination email address for testing.";
        } else {
            $testSubject = "TrueNorth CMS — Outgoing Mail Delivery Verification";
            $testBody = "This is an automated verification email sent from your TrueNorth Group CMS administration panel.\n\nTime: " . date('Y-m-d H:i:s') . "\nHost: {$smtpHost}:{$smtpPort}\nFrom: {$fromName} <{$fromEmail}>\n\nIf you are reading this, outgoing email delivery and SMTP socket communications are operating at 100% efficiency.";
            
            $result = MailService::sendCustomReply($testTarget, 'Administrator', $testSubject, $testBody, 'TEST-' . rand(1000, 9999));
            if ($result['success']) {
                $message = "Test email successfully dispatched to " . htmlspecialchars($testTarget) . "! Check your inbox.";
            } else {
                $error = "Test email failed: " . htmlspecialchars($result['message'] ?? 'Connection error');
            }
        }
    } else {
        $siteName = trim($_POST['site_name'] ?? $siteName);
        $tagline = trim($_POST['tagline'] ?? $tagline);
        $contactEmail = trim($_POST['contact_email'] ?? $contactEmail);
        $contactPhone = trim($_POST['contact_phone'] ?? $contactPhone);
        $currency = trim($_POST['currency'] ?? $currency);
        $hqAddress = trim($_POST['hq'] ?? $hqAddress);
        $sendClientConfirmation = isset($_POST['send_client_confirmation']) ? 1 : 0;

        $mailMode = trim($_POST['mail_mode'] ?? 'smtp');
        $smtpHost = trim($_POST['smtp_host'] ?? $smtpHost);
        $smtpPort = (int)($_POST['smtp_port'] ?? $smtpPort);
        $smtpUser = trim($_POST['smtp_user'] ?? $smtpUser);
        $smtpPass = trim($_POST['smtp_pass'] ?? $smtpPass);
        $fromName = trim($_POST['from_name'] ?? $fromName);
        $fromEmail = trim($_POST['from_email'] ?? $fromEmail);
        $replyTo = trim($_POST['reply_to'] ?? $replyTo);

        $savedData = [
            'site_name' => $siteName,
            'tagline' => $tagline,
            'contact_email' => $contactEmail,
            'contact_phone' => $contactPhone,
            'currency' => $currency,
            'hq' => $hqAddress,
            'send_client_confirmation' => $sendClientConfirmation,
            'mail_mode' => $mailMode,
            'smtp_host' => $smtpHost,
            'smtp_port' => $smtpPort,
            'smtp_user' => $smtpUser,
            'smtp_pass' => $smtpPass,
            'from_name' => $fromName,
            'from_email' => $fromEmail,
            'reply_to' => $replyTo,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        file_put_contents($settingsFile, json_encode($savedData, JSON_PRETTY_PRINT));
        $message = "Website & Outgoing Email settings updated and saved successfully!";
    }
}

$csrfToken = generateCsrfToken();
require_once __DIR__ . '/header.php';
?>

<div style="max-width: 820px; margin: 0 auto;">
  
  <div style="margin-bottom: 24px;">
    <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0 0 6px 0; color: #f8fafc;">Website &amp; Email Settings</h1>
    <p style="color: #94a3b8; font-size: 0.9rem; margin: 0;">Manage global site identity, contact information, and outgoing SMTP email delivery.</p>
  </div>

  <?php if (!empty($message)): ?>
    <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      <span><?= htmlspecialchars($message) ?></span>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <span><?= htmlspecialchars($error) ?></span>
    </div>
  <?php endif; ?>

  <form method="POST" action="/admin/settings">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
    <input type="hidden" name="action" value="save_settings" />

    <!-- SECTION 1: GLOBAL BRAND IDENTITY -->
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 28px; margin-bottom: 24px;">
      <h3 style="margin: 0 0 16px 0; font-size: 1.15rem; color: #38bdf8; font-weight: 700; border-bottom: 1px solid #334155; padding-bottom: 10px;">
        1. Company &amp; Website Identity
      </h3>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 16px;">
        <div>
          <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Company / Site Name *</label>
          <input type="text" name="site_name" value="<?= htmlspecialchars($siteName) ?>" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" required />
        </div>
        <div>
          <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Currency Symbol</label>
          <input type="text" name="currency" value="<?= htmlspecialchars($currency) ?>" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
        </div>
      </div>

      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Brand Tagline / Slogan</label>
        <input type="text" name="tagline" value="<?= htmlspecialchars($tagline) ?>" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 16px;">
        <div>
          <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Public Contact Email *</label>
          <input type="email" name="contact_email" value="<?= htmlspecialchars($contactEmail) ?>" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" required />
        </div>
        <div>
          <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Public Contact Phone / WhatsApp</label>
          <input type="text" name="contact_phone" value="<?= htmlspecialchars($contactPhone) ?>" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
        </div>
      </div>

      <div>
        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">HQ &amp; Manufacturing Locations</label>
        <input type="text" name="hq" value="<?= htmlspecialchars($hqAddress) ?>" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
      </div>
    </div>

    <!-- SECTION 2: OUTGOING EMAIL & SMTP CONFIGURATION -->
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 28px; margin-bottom: 24px;">
      <h3 style="margin: 0 0 8px 0; font-size: 1.15rem; color: #b8892e; font-weight: 700; border-bottom: 1px solid #334155; padding-bottom: 10px;">
        2. Outgoing Email &amp; SMTP Server Configuration
      </h3>
      <p style="color: #94a3b8; font-size: 0.82rem; margin: 0 0 18px 0;">Used to send official quotation replies, inquiries, and customer confirmations.</p>

      <div style="margin-bottom: 18px;">
        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Email Delivery Mode</label>
        <select name="mail_mode" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;">
          <option value="smtp" <?= $mailMode === 'smtp' ? 'selected' : '' ?>>Authenticated SMTP Socket (Recommended for Webmail/Gmail/cPanel)</option>
          <option value="mail" <?= $mailMode === 'mail' ? 'selected' : '' ?>>Native PHP mail() Function (Server Default)</option>
        </select>
      </div>

      <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 18px; margin-bottom: 16px;">
        <div>
          <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">SMTP Host</label>
          <input type="text" name="smtp_host" value="<?= htmlspecialchars($smtpHost) ?>" placeholder="mail.wetruenorthgroup.com" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
        </div>
        <div>
          <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">SMTP Port</label>
          <input type="number" name="smtp_port" value="<?= (int)$smtpPort ?>" placeholder="465" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 16px;">
        <div>
          <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">SMTP Username / Email</label>
          <input type="text" name="smtp_user" value="<?= htmlspecialchars($smtpUser) ?>" placeholder="info@wetruenorthgroup.com" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
        </div>
        <div>
          <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">SMTP Password</label>
          <input type="password" name="smtp_pass" value="<?= htmlspecialchars($smtpPass) ?>" placeholder="********" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 16px;">
        <div>
          <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Outgoing Sender Name</label>
          <input type="text" name="from_name" value="<?= htmlspecialchars($fromName) ?>" placeholder="TrueNorth Group" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
        </div>
        <div>
          <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Reply-To Email Address</label>
          <input type="email" name="reply_to" value="<?= htmlspecialchars($replyTo) ?>" placeholder="info@wetruenorthgroup.com" style="width: 100%; box-sizing: border-box; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
        </div>
      </div>

      <!-- Auto Acknowledgement Switch -->
      <div style="background: #0f172a; border: 1px solid #334155; border-radius: 10px; padding: 16px 18px; margin-top: 14px;">
        <div style="display: flex; align-items: flex-start; gap: 12px;">
          <input type="checkbox" id="send_client_confirmation" name="send_client_confirmation" value="1" <?= $sendClientConfirmation ? 'checked' : '' ?> style="width: 20px; height: 20px; accent-color: #b8892e; cursor: pointer; margin-top: 2px;" />
          <div>
            <label for="send_client_confirmation" style="display: block; font-size: 0.92rem; font-weight: 700; color: #f8fafc; cursor: pointer;">
              Auto-send Receipt Acknowledgement to Inquiring Clients
            </label>
            <p style="color: #94a3b8; font-size: 0.8rem; margin: 4px 0 0; line-height: 1.4;">
              Clients receive an instant confirmation email with their reference number and product specifications.
            </p>
          </div>
        </div>
      </div>

    </div>

    <button type="submit" class="tn-btn-gold" style="width: 100%; padding: 14px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.95rem; margin-bottom: 28px;">
      SAVE ALL SETTINGS
    </button>
  </form>

  <!-- SECTION 3: TEST EMAIL DISPATCH -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 24px; margin-bottom: 36px;">
    <h3 style="margin: 0 0 8px; font-size: 1.05rem; color: #f8fafc; font-weight: 700;">
      Test Email Connectivity
    </h3>
    <p style="color: #94a3b8; font-size: 0.84rem; margin: 0 0 16px;">
      Send a live test message to verify your SMTP authentication and server socket transport.
    </p>

    <form method="POST" action="/admin/settings" style="display: flex; gap: 10px; flex-wrap: wrap;">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
      <input type="hidden" name="action" value="test_email" />
      <input type="email" name="test_email_address" value="<?= htmlspecialchars($contactEmail) ?>" placeholder="Enter email to receive test message..." required style="flex: 1; min-width: 260px; padding: 11px 14px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff; font-size: 0.88rem;" />
      <button type="submit" style="background: #0284c7; color: #fff; border: none; padding: 11px 22px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.88rem; min-height: 44px;">
        Send Test Email &rarr;
      </button>
    </form>
  </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
