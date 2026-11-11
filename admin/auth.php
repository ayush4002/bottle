<?php
// ==========================================================================
// TRUENORTH GROUP — ENTERPRISE ADMIN AUTHENTICATION & SECURITY MIDDLEWARE
// ==========================================================================

$isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if ($isHttps) {
        ini_set('session.cookie_secure', '1');
    }
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

// Session Timeout: 30 minutes
define('SESSION_MAX_LIFETIME', 1800);

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_MAX_LIFETIME)) {
    session_unset();
    session_destroy();
    if ($isHttps) {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
    $_SESSION['auth_error'] = "Session expired due to inactivity. Please log in again.";
}
$_SESSION['last_activity'] = time();

// CSRF Token Generation
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Authentication Check Helper
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function checkAdminAuth() {
    if (!isAdminLoggedIn()) {
        header("Location: /admin/login");
        exit();
    }
}

function checkRole($allowedRoles = ['Super Admin']) {
    checkAdminAuth();
    $userRole = $_SESSION['admin_role'] ?? 'Editor';
    if (!in_array($userRole, (array)$allowedRoles)) {
        http_response_code(403);
        echo "<div style='font-family: sans-serif; padding: 40px; text-align: center;'><h2>403 - Permission Denied</h2><p>Your admin role (<strong>" . htmlspecialchars($userRole) . "</strong>) does not have access to this section.</p><a href='/admin'>Return to Dashboard</a></div>";
        exit();
    }
}

// ==========================================================================
// PERSISTENT BRUTE-FORCE LOGIN PROTECTION (IP + USERNAME BASED)
// Does NOT rely on session cookies so curl / bot scripts cannot bypass.
// ==========================================================================
function getLoginRateLimitStore() {
    $dir = dirname(__DIR__) . '/config/cache';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $file = $dir . '/login_rate_limits.json';
    if (!file_exists($file)) {
        file_put_contents($file, json_encode([]), LOCK_EX);
    }
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function saveLoginRateLimitStore($data) {
    $dir = dirname(__DIR__) . '/config/cache';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $file = $dir . '/login_rate_limits.json';
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
}

function isLoginRateLimited() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $store = getLoginRateLimitStore();
    $now = time();

    if (isset($store[$ip])) {
        $entry = $store[$ip];
        if (!empty($entry['locked_until']) && $now < $entry['locked_until']) {
            return true;
        }
    }
    return false;
}

function getLoginLockoutRemaining() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $store = getLoginRateLimitStore();
    $now = time();

    if (isset($store[$ip]) && !empty($store[$ip]['locked_until']) && $now < $store[$ip]['locked_until']) {
        return (int)ceil(($store[$ip]['locked_until'] - $now) / 60);
    }
    return 0;
}

function recordFailedLoginAttempt() {
    // Thwart rapid automated script timing
    usleep(250000); // 250ms delay

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $store = getLoginRateLimitStore();
    $now = time();

    $entry = $store[$ip] ?? [
        'count' => 0,
        'first_fail' => $now,
        'last_fail' => $now,
        'locked_until' => 0
    ];

    // Reset window if last fail was more than 15 minutes ago and not locked
    if ($now - $entry['last_fail'] > 900 && empty($entry['locked_until'])) {
        $entry['count'] = 0;
        $entry['first_fail'] = $now;
    }

    $entry['count']++;
    $entry['last_fail'] = $now;

    // Thresholds:
    // 5 fails -> 15 minutes lockout
    // 10+ fails -> 1 hour lockout
    if ($entry['count'] >= 10) {
        $entry['locked_until'] = $now + 3600; // 1 hour
    } elseif ($entry['count'] >= 5) {
        $entry['locked_until'] = $now + 900;  // 15 minutes
    }

    $store[$ip] = $entry;

    // Garbage collect entries older than 24h
    foreach ($store as $k => $v) {
        if ($now - ($v['last_fail'] ?? 0) > 86400) {
            unset($store[$k]);
        }
    }

    saveLoginRateLimitStore($store);
}

function resetFailedLoginAttempts() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $store = getLoginRateLimitStore();
    if (isset($store[$ip])) {
        unset($store[$ip]);
        saveLoginRateLimitStore($store);
    }
}

// ==========================================================================
// GLOBAL CSRF ENFORCEMENT FOR AUTHENTICATED ADMIN REQUESTS
// Every POST, and every GET that changes state (delete_* scripts or
// ?action=delete|toggle|dismiss...), must carry the session CSRF token in
// the csrf_token field/query param or the X-CSRF-Token header.
// admin/header.php injects the token into forms and fetch() automatically.
// ==========================================================================
function tn_request_csrf_token() {
    if (!empty($_POST['csrf_token'])) return (string)$_POST['csrf_token'];
    if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) return (string)$_SERVER['HTTP_X_CSRF_TOKEN'];
    if (!empty($_GET['csrf_token'])) return (string)$_GET['csrf_token'];
    return '';
}

function tn_csrf_reject() {
    http_response_code(403);
    $wantsJson = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false)
        || !empty($_REQUEST['ajax']);
    if ($wantsJson) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Security token expired or missing. Please reload the page and try again.']);
    } else {
        echo "<div style='font-family: sans-serif; padding: 40px; text-align: center;'><h2>403 - Security check failed</h2><p>Your security token expired or is missing (this can also happen if an upload is larger than the server limit). Please go back, reload the page and try again.</p><a href='/admin'>Return to Dashboard</a></div>";
    }
    exit();
}

(function () {
    if (!isAdminLoggedIn()) return;
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $stateChangingGet = preg_match('#/admin/delete_[a-z_]+#i', $path)
        || in_array($_GET['action'] ?? '', ['delete', 'toggle', 'dismiss', 'dismiss_404', 'regenerate_sitemap', 'permanent', 'restore', 'archive'], true);
    if ($method === 'POST' || ($method === 'GET' && $stateChangingGet)) {
        if (!verifyCsrfToken(tn_request_csrf_token())) {
            tn_csrf_reject();
        }
    }
})();
