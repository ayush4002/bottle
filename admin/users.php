<?php
require_once __DIR__ . '/header.php';
checkRole(['Super Admin']);

$usersFile = defined('ROOT_PATH') ? ROOT_PATH . '/config/admin_users.json' : dirname(__DIR__) . '/config/admin_users.json';
$usersList = file_exists($usersFile) ? (json_decode(file_get_contents($usersFile), true) ?: []) : [];

// Ensure default super admin exists in memory list if file empty
if (empty($usersList)) {
    $usersList = [
        [
            'id' => 1,
            'username' => ADMIN_USER,
            'email' => 'info@wetruenorthgroup.com',
            'role' => 'Super Admin',
            'status' => 'Active',
            'created_at' => '2026-01-01 00:00:00'
        ]
    ];
    file_put_contents($usersFile, json_encode($usersList, JSON_PRETTY_PRINT));
}

$message = '';
$error = '';

// Handle Delete User
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $delUser = trim($_GET['user'] ?? '');
    if ($delUser === ADMIN_USER) {
        $error = "The primary Super Admin '{$delUser}' cannot be deleted.";
    } else {
        $usersList = array_values(array_filter($usersList, function($u) use ($delUser) {
            return ($u['username'] ?? '') !== $delUser;
        }));
        file_put_contents($usersFile, json_encode($usersList, JSON_PRETTY_PRINT));
        $message = "Admin user '{$delUser}' removed successfully.";
    }
}

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfTokenPost = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrfTokenPost)) {
        $error = "Invalid security token. Please try again.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role = trim($_POST['role'] ?? 'Editor');

        if (empty($username) || empty($password)) {
            $error = "Username and password are required.";
        } else {
            // Check if username exists
            $exists = false;
            foreach ($usersList as $u) {
                if (strcasecmp($u['username'] ?? '', $username) === 0) {
                    $exists = true;
                    break;
                }
            }

            if ($exists) {
                $error = "An administrator with username '{$username}' already exists.";
            } else {
                $newUser = [
                    'id' => time() . rand(10, 99),
                    'username' => $username,
                    'email' => $email,
                    'role' => $role,
                    'status' => 'Active',
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $usersList[] = $newUser;
                file_put_contents($usersFile, json_encode($usersList, JSON_PRETTY_PRINT));
                $message = "Admin user '{$username}' created successfully with role '{$role}'!";
            }
        }
    }
}
?>

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0; color: #f8fafc;">Admin Users &amp; Role Permissions</h1>
    <p style="color: #94a3b8; margin: 4px 0 0 0; font-size: 0.9rem;">Manage administrators, assign roles (Super Admin, Product Manager, Editor), and control access</p>
  </div>
</div>

<?php if (!empty($message)): ?>
  <div style="background: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px;"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 24px;"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 28px; align-items: start;">
  
  <!-- ADD ADMIN FORM -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 24px;">
    <h3 style="margin: 0 0 16px 0; font-size: 1.1rem; color: #f8fafc;">Add New Administrator</h3>
    
    <form method="POST" action="/admin/users.php">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />

      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Username *</label>
        <input type="text" name="username" required placeholder="e.g. manager1" style="width: 100%; box-sizing: border-box; padding: 10px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
      </div>

      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Email Address *</label>
        <input type="email" name="email" required placeholder="e.g. manager@wetruenorthgroup.com" style="width: 100%; box-sizing: border-box; padding: 10px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" />
      </div>

      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Initial Password *</label>
        <input type="password" name="password" required placeholder="Enter secure password" style="width: 100%; box-sizing: border-box; padding: 10px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;" minlength="6" />
      </div>

      <div style="margin-bottom: 20px;">
        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Role</label>
        <select name="role" style="width: 100%; box-sizing: border-box; padding: 10px; background: #0f172a; border: 1px solid #475569; border-radius: 8px; color: #fff;">
          <option value="Super Admin">Super Admin</option>
          <option value="Product Manager">Product Manager</option>
          <option value="Editor">Editor</option>
        </select>
      </div>

      <button type="submit" class="frapak-btn-gold" style="width: 100%; padding: 12px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; background: #eab308; color: #0f172a;">CREATE ADMIN USER</button>
    </form>
  </div>

  <!-- ADMIN USERS TABLE -->
  <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 24px;">
    <h3 style="margin: 0 0 16px 0; font-size: 1.1rem; color: #f8fafc;">Registered Admin Accounts (<?= count($usersList) ?>)</h3>

    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
      <table style="width: 100%; border-collapse: collapse;">
        <thead>
          <tr style="border-bottom: 1px solid #334155;">
            <th style="text-align: left; padding: 12px 10px; font-size: 0.78rem; color: #94a3b8; text-transform: uppercase;">Username</th>
            <th style="text-align: left; padding: 12px 10px; font-size: 0.78rem; color: #94a3b8; text-transform: uppercase;">Email</th>
            <th style="text-align: left; padding: 12px 10px; font-size: 0.78rem; color: #94a3b8; text-transform: uppercase;">Role</th>
            <th style="text-align: left; padding: 12px 10px; font-size: 0.78rem; color: #94a3b8; text-transform: uppercase;">Status</th>
            <th style="text-align: right; padding: 12px 10px; font-size: 0.78rem; color: #94a3b8; text-transform: uppercase;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($usersList as $u): ?>
            <?php 
              $uName = $u['username'] ?? '';
              $uRole = $u['role'] ?? 'Editor';
              $isPrimary = $uName === ADMIN_USER;
            ?>
            <tr style="border-bottom: 1px solid rgba(51, 65, 85, 0.5);">
              <td style="padding: 12px 10px;"><strong style="color: #f8fafc;"><?= htmlspecialchars($uName) ?></strong></td>
              <td style="padding: 12px 10px; color: #cbd5e1;"><?= htmlspecialchars($u['email'] ?? 'N/A') ?></td>
              <td style="padding: 12px 10px;">
                <span style="background: rgba(14, 165, 233, 0.15); color: #38bdf8; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; border: 1px solid rgba(14, 165, 233, 0.3);">
                  <?= htmlspecialchars($uRole) ?>
                </span>
              </td>
              <td style="padding: 12px 10px;">
                <span style="background: rgba(52, 211, 153, 0.15); color: #34d399; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                  <?= htmlspecialchars($u['status'] ?? 'Active') ?>
                </span>
              </td>
              <td style="padding: 12px 10px; text-align: right;">
                <?php if (!$isPrimary): ?>
                  <a href="/admin/users.php?action=delete&user=<?= urlencode($uName) ?>" onclick="return confirm('Delete admin user \'<?= htmlspecialchars(addslashes($uName)) ?>\'?');" style="color: #ef4444; font-size: 0.8rem; font-weight: 600; text-decoration: none; padding: 4px 8px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 4px;">
                    Delete
                  </a>
                <?php else: ?>
                  <span style="color: #64748b; font-size: 0.75rem;">System Admin</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
