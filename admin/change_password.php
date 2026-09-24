<?php
require_once __DIR__ . '/header.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPass = trim($_POST['current_password'] ?? '');
    $newPass = trim($_POST['new_password'] ?? '');
    $confirmPass = trim($_POST['confirm_password'] ?? '');
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrfToken)) {
        $error = "Invalid security token. Please try again.";
    } elseif ($newPass !== $confirmPass) {
        $error = "New passwords do not match.";
    } elseif (strlen($newPass) < 6) {
        $error = "New password must be at least 6 characters long.";
    } else {
        $username = $_SESSION['admin_user'] ?? 'admin';
        $updated = false;

        // Try DB update
        if (class_exists('Database')) {
            $db = Database::getInstance();
            if ($db && $db->isConnected()) {
                try {
                    $pdo = $db->getConnection();
                    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
                    $stmt->execute([$username]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($user && password_verify($currentPass, $user['password_hash'])) {
                        $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                        $upStmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
                        $upStmt->execute([$newHash, $user['id']]);
                        $updated = true;
                    }
                } catch (Exception $e) {
                    error_log("DB Password Change Error: " . $e->getMessage());
                }
            }
        }

        if (!$updated && $currentPass === ADMIN_PASS) {
            $updated = true;
        }

        if ($updated) {
            $message = "Password updated successfully!";
        } else {
            $error = "Current password is incorrect.";
        }
    }
}

$token = generateCsrfToken();
?>

<div style="max-width: 500px; margin: 0 auto; background: #1e293b; border: 1px solid #334155; border-radius: 16px; padding: 36px;">
  <h2 style="font-size: 1.5rem; margin: 0 0 8px 0;">Change Account Password</h2>
  <p style="color: #94a3b8; font-size: 0.88rem; margin: 0 0 24px 0;">Update your administrator password for account security.</p>

  <?php if (!empty($message)): ?>
    <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 12px 14px; border-radius: 8px; font-size: 0.88rem; margin-bottom: 20px;"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 12px 14px; border-radius: 8px; font-size: 0.88rem; margin-bottom: 20px;"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="change_password.php">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>" />

    <div style="margin-bottom: 18px;">
      <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Current Password *</label>
      <input type="password" name="current_password" required class="form-input-admin" style="width: 100%; box-sizing: border-box; padding: 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
    </div>

    <div style="margin-bottom: 18px;">
      <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">New Password *</label>
      <input type="password" name="new_password" required class="form-input-admin" style="width: 100%; box-sizing: border-box; padding: 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" minlength="6" />
    </div>

    <div style="margin-bottom: 24px;">
      <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Confirm New Password *</label>
      <input type="password" name="confirm_password" required class="form-input-admin" style="width: 100%; box-sizing: border-box; padding: 12px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" minlength="6" />
    </div>

    <button type="submit" class="frapak-btn-gold" style="width: 100%; padding: 14px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">UPDATE PASSWORD</button>
  </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
