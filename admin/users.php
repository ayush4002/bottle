<?php
require_once __DIR__ . '/header.php';
checkRole(['Super Admin']);

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? 'Editor');

    $message = "Admin user '{$username}' created successfully with role '{$role}'!";
}
?>

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px;">
  <div>
    <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0;">Admin Users & Role Permissions</h1>
    <p style="color: #94a3b8; margin: 4px 0 0 0;">Manage administrators, assign roles (Super Admin, Product Manager, Editor), and control permissions</p>
  </div>
</div>

<?php if (!empty($message)): ?>
  <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px;"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 28px;">
  
  <!-- ADD ADMIN FORM -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 24px; height: fit-content;">
    <h3 style="margin: 0 0 16px 0; font-size: 1.1rem;">Add New Administrator</h3>
    
    <form method="POST" action="users.php">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />

      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Username *</label>
        <input type="text" name="username" required placeholder="e.g. manager1" style="width: 100%; box-sizing: border-box; padding: 10px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
      </div>

      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Email Address *</label>
        <input type="email" name="email" required placeholder="e.g. admin@wetruenorthgroup.com" style="width: 100%; box-sizing: border-box; padding: 10px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
      </div>

      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Initial Password *</label>
        <input type="password" name="password" required placeholder="Enter password" style="width: 100%; box-sizing: border-box; padding: 10px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
      </div>

      <div style="margin-bottom: 20px;">
        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Role</label>
        <select name="role" style="width: 100%; box-sizing: border-box; padding: 10px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;">
          <option value="Super Admin">Super Admin</option>
          <option value="Product Manager">Product Manager</option>
          <option value="Editor">Editor</option>
        </select>
      </div>

      <button type="submit" class="frapak-btn-gold" style="width: 100%; padding: 12px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">CREATE ADMIN USER</button>
    </form>
  </div>

  <!-- ADMIN USERS TABLE -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 24px;">
    <h3 style="margin: 0 0 16px 0; font-size: 1.1rem;">Registered Admin Accounts</h3>

    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
      <table style="width: 100%; border-collapse: collapse;">
        <thead>
          <tr style="border-bottom: 1px solid #334155;">
            <th style="text-align: left; padding: 10px; font-size: 0.78rem; color: #94a3b8;">Username</th>
            <th style="text-align: left; padding: 10px; font-size: 0.78rem; color: #94a3b8;">Email</th>
            <th style="text-align: left; padding: 10px; font-size: 0.78rem; color: #94a3b8;">Role</th>
            <th style="text-align: left; padding: 10px; font-size: 0.78rem; color: #94a3b8;">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr style="border-bottom: 1px solid rgba(51, 65, 85, 0.5);">
            <td style="padding: 10px;"><strong>admin</strong></td>
            <td style="padding: 10px;">info@wetruenorthgroup.com</td>
            <td style="padding: 10px;"><span style="background: rgba(14, 165, 233, 0.15); color: #38bdf8; padding: 3px 6px; border-radius: 4px; font-size: 0.72rem; font-weight: 700;">Super Admin</span></td>
            <td style="padding: 10px;"><span style="background: rgba(52, 211, 153, 0.15); color: #34d399; padding: 3px 6px; border-radius: 4px; font-size: 0.72rem; font-weight: 700;">Active</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
