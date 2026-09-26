<?php
require_once __DIR__ . '/auth.php';
checkAdminAuth();

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Models/ProductModel.php';

$settingsFile = defined('ROOT_PATH') ? ROOT_PATH . '/config/site_settings.json' : dirname(__DIR__) . '/config/site_settings.json';
$currentSettings = file_exists($settingsFile) ? (json_decode(file_get_contents($settingsFile), true) ?: []) : [];

$company = ProductModel::getCompanyInfo();

$siteName = $currentSettings['site_name'] ?? ($company['name'] ?? 'TrueNorth Group');
$tagline = $currentSettings['tagline'] ?? ($company['tagline'] ?? 'Primary Packaging Specialist — PET & Glass Solutions');
$contactEmail = $currentSettings['contact_email'] ?? ($company['email'] ?? 'info@wetruenorthgroup.com');
$contactPhone = $currentSettings['contact_phone'] ?? ($company['phone'] ?? '+91 98765 43210');
$currency = $currentSettings['currency'] ?? '₹';
$hqAddress = $currentSettings['hq'] ?? ($company['hq'] ?? 'Global Manufacturing Operations | Audited Partner Network Serving Global Exports');
$sendClientConfirmation = isset($currentSettings['send_client_confirmation']) ? (int)$currentSettings['send_client_confirmation'] : 1;

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrfToken)) {
        $error = "Invalid security token. Please try again.";
    } else {
        $siteName = trim($_POST['site_name'] ?? $siteName);
        $tagline = trim($_POST['tagline'] ?? $tagline);
        $contactEmail = trim($_POST['contact_email'] ?? $contactEmail);
        $contactPhone = trim($_POST['contact_phone'] ?? $contactPhone);
        $currency = trim($_POST['currency'] ?? $currency);
        $hqAddress = trim($_POST['hq'] ?? $hqAddress);
        $sendClientConfirmation = isset($_POST['send_client_confirmation']) ? 1 : 0;

        $savedData = [
            'site_name' => $siteName,
            'tagline' => $tagline,
            'contact_email' => $contactEmail,
            'contact_phone' => $contactPhone,
            'currency' => $currency,
            'hq' => $hqAddress,
            'send_client_confirmation' => $sendClientConfirmation,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        file_put_contents($settingsFile, json_encode($savedData, JSON_PRETTY_PRINT));
        $message = "Website settings updated and saved successfully!";
    }
}

require_once __DIR__ . '/header.php';
?>

<div style="max-width: 680px; margin: 0 auto; background: #1e293b; border: 1px solid #334155; border-radius: 16px; padding: 36px;">
  <h2 style="font-size: 1.6rem; margin: 0 0 6px 0; color: #f8fafc;">Website Settings</h2>
  <p style="color: #94a3b8; font-size: 0.9rem; margin: 0 0 28px 0;">Manage global website identity, contact info, and business credentials.</p>

  <?php if (!empty($message)): ?>
    <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px;"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px;"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="/admin/settings.php">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />

    <div style="margin-bottom: 20px;">
      <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Company / Site Name *</label>
      <input type="text" name="site_name" value="<?= htmlspecialchars($siteName) ?>" class="form-input-admin" style="width: 100%; box-sizing: border-box; padding: 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" required />
    </div>

    <div style="margin-bottom: 20px;">
      <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Tagline / Subtitle</label>
      <input type="text" name="tagline" value="<?= htmlspecialchars($tagline) ?>" class="form-input-admin" style="width: 100%; box-sizing: border-box; padding: 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
    </div>

    <div style="margin-bottom: 20px;">
      <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Contact Email Address *</label>
      <input type="email" name="contact_email" value="<?= htmlspecialchars($contactEmail) ?>" class="form-input-admin" style="width: 100%; box-sizing: border-box; padding: 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" required />
    </div>

    <div style="margin-bottom: 20px;">
      <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Contact Phone Number</label>
      <input type="text" name="contact_phone" value="<?= htmlspecialchars($contactPhone) ?>" class="form-input-admin" style="width: 100%; box-sizing: border-box; padding: 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
    </div>

    <div style="margin-bottom: 20px;">
      <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">HQ Operations / Address</label>
      <input type="text" name="hq" value="<?= htmlspecialchars($hqAddress) ?>" class="form-input-admin" style="width: 100%; box-sizing: border-box; padding: 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
    </div>

    <div style="margin-bottom: 24px;">
      <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Global Currency Symbol</label>
      <input type="text" name="currency" value="<?= htmlspecialchars($currency) ?>" class="form-input-admin" style="width: 100%; box-sizing: border-box; padding: 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
    </div>

    <!-- Client Email Auto-Reply Toggle -->
    <div style="background: #0f172a; border: 1px solid #334155; border-radius: 10px; padding: 18px 20px; margin-bottom: 28px;">
      <div style="display: flex; align-items: flex-start; gap: 14px;">
        <input type="checkbox" id="send_client_confirmation" name="send_client_confirmation" value="1" <?= $sendClientConfirmation ? 'checked' : '' ?> style="width: 22px; height: 22px; accent-color: #eab308; cursor: pointer; margin-top: 2px;" />
        <div>
          <label for="send_client_confirmation" style="display: block; font-size: 0.95rem; font-weight: 700; color: #f8fafc; cursor: pointer; margin-bottom: 4px;">
            Send Inquiry Confirmation Copy to Client
          </label>
          <p style="color: #94a3b8; font-size: 0.82rem; margin: 0; line-height: 1.5;">
            When checked, the client who submitted the quote request will automatically receive a branded confirmation email copy with their reference number, product specs, and inquiry message.
          </p>
        </div>
      </div>
    </div>

    <button type="submit" class="frapak-btn-gold" style="width: 100%; padding: 14px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; background: #eab308; color: #0f172a;">SAVE WEBSITE SETTINGS</button>
  </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
