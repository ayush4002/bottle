<?php
require_once __DIR__ . '/auth.php';

$error = $_SESSION['auth_error'] ?? '';
unset($_SESSION['auth_error']);

if (isLoginRateLimited()) {
    $error = "Too many failed login attempts. Account temporarily locked for 15 minutes for security.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (isLoginRateLimited()) {
        $error = "Too many failed login attempts. Please wait 15 minutes before trying again.";
    } elseif (!verifyCsrfToken($csrfToken)) {
        $error = "Invalid security token. Please refresh the page and try again.";
    } elseif (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $authenticated = false;
        $userRole = 'Super Admin';
        $userEmail = 'info@wetruenorthgroup.com';

        // 1. Try MySQL Database Authentication
        if (class_exists('Database')) {
            $db = Database::getInstance();
            if ($db && $db->isConnected()) {
                try {
                    $pdo = $db->getConnection();
                    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? OR email = ? LIMIT 1");
                    $stmt->execute([$username, $username]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($user && password_verify($password, $user['password_hash'])) {
                        $authenticated = true;
                        $username = $user['username'];
                        $userRole = $user['role'];
                        $userEmail = $user['email'];

                        // Update last_login
                        $upStmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
                        $upStmt->execute([$user['id']]);
                    }
                } catch (Exception $e) {
                    error_log("DB Admin Login Error: " . $e->getMessage());
                }
            }
        }

        // 2. Fallback to Config Default Admin
        if (!$authenticated) {
            if ($username === ADMIN_USER && ($password === ADMIN_PASS || password_verify($password, password_hash(ADMIN_PASS, PASSWORD_DEFAULT)))) {
                $authenticated = true;
                $userRole = 'Super Admin';
            }
        }

        // 3. Fallback to JSON Admin Users
        if (!$authenticated) {
            $usersFile = defined('ROOT_PATH') ? ROOT_PATH . '/config/admin_users.json' : dirname(__DIR__) . '/config/admin_users.json';
            if (file_exists($usersFile)) {
                $users = json_decode(file_get_contents($usersFile), true) ?: [];
                foreach ($users as $u) {
                    if (strcasecmp($u['username'] ?? '', $username) === 0 || strcasecmp($u['email'] ?? '', $username) === 0) {
                        if (!empty($u['password_hash']) && password_verify($password, $u['password_hash'])) {
                            $authenticated = true;
                            $username = $u['username'];
                            $userRole = $u['role'] ?? 'Editor';
                            $userEmail = $u['email'] ?? '';
                            break;
                        }
                    }
                }
            }
        }

        if ($authenticated) {
            session_regenerate_id(true);
            resetFailedLoginAttempts();

            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $username;
            $_SESSION['admin_role'] = $userRole;
            $_SESSION['admin_email'] = $userEmail;
            $_SESSION['last_activity'] = time();

            header("Location: /admin/index.php");
            exit();
        } else {
            recordFailedLoginAttempt();
            $error = "Invalid username or password. Please check your credentials.";
        }
    }
}

if (isAdminLoggedIn()) {
    header("Location: /admin/index.php");
    exit();
}

$token = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login — TrueNorth Group CMS</title>
  <link rel="icon" type="image/svg+xml" href="/logo_svg.svg" />
  <link rel="stylesheet" href="/styles.css?v=1.0.7" />
  <style>
    body {
      background: #0f172a;
      color: #f8fafc;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
      padding: 20px;
    }
    .admin-login-card {
      background: #1e293b;
      border: 1px solid #334155;
      border-radius: 16px;
      padding: 40px 32px;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }
    .login-brand {
      text-align: center;
      margin-bottom: 28px;
    }
    .login-brand img {
      height: 48px;
      margin-bottom: 12px;
      filter: brightness(0) invert(1);
    }
    .login-brand h2 {
      font-size: 1.4rem;
      font-weight: 700;
      color: #ffffff;
      margin: 0 0 6px 0;
    }
    .login-brand p {
      color: #94a3b8;
      font-size: 0.85rem;
      margin: 0;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      font-size: 0.85rem;
      font-weight: 600;
      color: #cbd5e1;
      margin-bottom: 8px;
    }
    .form-control {
      width: 100%;
      box-sizing: border-box;
      padding: 12px 14px;
      background: #0f172a;
      border: 1px solid #475569;
      border-radius: 8px;
      color: #ffffff;
      font-size: 0.95rem;
      transition: border-color 0.2s;
    }
    .form-control:focus {
      outline: none;
      border-color: #38bdf8;
    }
    .btn-login {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #1e3a8a, #0284c7);
      border: none;
      border-radius: 8px;
      color: #ffffff;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: opacity 0.2s;
      margin-top: 10px;
    }
    .btn-login:hover {
      opacity: 0.92;
    }
    .alert-danger {
      background: rgba(239, 68, 68, 0.15);
      border: 1px solid rgba(239, 68, 68, 0.4);
      color: #fca5a5;
      padding: 12px 14px;
      border-radius: 8px;
      font-size: 0.85rem;
      margin-bottom: 20px;
      text-align: center;
    }
    .login-footer {
      text-align: center;
      margin-top: 24px;
      font-size: 0.8rem;
      color: #64748b;
    }
  </style>
</head>
<body>

  <div class="admin-login-card">
    <div class="login-brand">
      <img src="/logo_svg.svg" alt="TrueNorth Group Logo" />
      <h2>Enterprise CMS Portal</h2>
      <p>TrueNorth Group Packaging Management</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>" />

      <div class="form-group">
        <label for="username">Username or Email</label>
        <input type="text" id="username" name="username" class="form-control" required placeholder="Enter admin username" autofocus />
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control" required placeholder="Enter password" />
      </div>

      <button type="submit" class="btn-login">SECURE SIGN IN &rarr;</button>
    </form>

    <div class="login-footer">
      &copy; <?= date('Y') ?> TrueNorth Group. Protected by SSL & Session Security.
    </div>
  </div>

</body>
</html>
