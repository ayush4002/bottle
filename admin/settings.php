<?php
require_once __DIR__ . '/header.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siteName = trim($_POST['site_name'] ?? '');
    $contactEmail = trim($_POST['contact_email'] ?? '');
    $contactPhone = trim($_POST['contact_phone'] ?? '');

    $message = "Website settings updated successfully!";
}
?>

<div style="max-width: 650px; margin: 0 auto; background: #1e293b; border: 1px solid #334155; border-radius: 16px; padding: 36px;">
  <h2 style="font-size: 1.6rem; margin: 0 0 6px 0;">Website Settings</h2>
  <p style="color: #94a3b8; font-size: 0.9rem; margin: 0 0 28px 0;">Manage global website identity, contact info, and business credentials.</p>

  <?php if (!empty($message)): ?>
    <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px;"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <form method="POST" action="settings.php">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />

    <div style="margin-bottom: 20px;">
      <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Company / Site Name *</label>
      <input type="text" name="site_name" value="TrueNorth Group" class="form-input-admin" style="width: 100%; box-sizing: border-box; padding: 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" required />
    </div>

    <div style="margin-bottom: 20px;">
      <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Contact Email Address *</label>
      <input type="email" name="contact_email" value="info@wetruenorthgroup.com" class="form-input-admin" style="width: 100%; box-sizing: border-box; padding: 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" required />
    </div>

    <div style="margin-bottom: 20px;">
      <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Contact Phone Number</label>
      <input type="text" name="contact_phone" value="+91 98765 43210" class="form-input-admin" style="width: 100%; box-sizing: border-box; padding: 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
    </div>

    <div style="margin-bottom: 24px;">
      <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Global Currency Symbol</label>
      <input type="text" name="currency" value="₹" class="form-input-admin" style="width: 100%; box-sizing: border-box; padding: 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
    </div>

    <button type="submit" class="frapak-btn-gold" style="width: 100%; padding: 14px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">SAVE WEBSITE SETTINGS</button>
  </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
