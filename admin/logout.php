<?php
// Logout must be a POST carrying the CSRF token (auth.php enforces the token for
// every authenticated POST), so third-party pages cannot log admins out.
require_once __DIR__ . '/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    if (!isAdminLoggedIn()) {
        header("Location: /admin/login");
        exit();
    }
    $token = generateCsrfToken();
    ?><!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" /><title>Log out — TrueNorth Group CMS</title>
<style>body{background:#0f172a;color:#f8fafc;font-family:system-ui,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;margin:0}
.card{background:#1e293b;border:1px solid #334155;border-radius:14px;padding:32px;text-align:center;max-width:360px}
button{background:#0284c7;color:#fff;border:0;border-radius:8px;padding:12px 22px;font-weight:700;cursor:pointer}a{color:#94a3b8;display:block;margin-top:14px}</style></head>
<body><div class="card"><h2 style="margin-top:0">Log out?</h2>
<form method="post" action="/admin/logout"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>" /><button type="submit">Log out</button></form>
<a href="/admin">Cancel</a></div></body></html><?php
    exit();
}

$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
header("Location: /admin/login");
exit();
