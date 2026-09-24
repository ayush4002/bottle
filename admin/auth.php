<?php
// ==========================================================================
// TRUENORTH GROUP — ENTERPRISE ADMIN AUTHENTICATION & SECURITY MIDDLEWARE
// ==========================================================================

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

// Session Timeout: 30 minutes
define('SESSION_MAX_LIFETIME', 1800);

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_MAX_LIFETIME)) {
    session_unset();
    session_destroy();
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
        header("Location: /admin/login.php");
        exit();
    }
}

function checkRole($allowedRoles = ['Super Admin']) {
    checkAdminAuth();
    $userRole = $_SESSION['admin_role'] ?? 'Editor';
    if (!in_array($userRole, (array)$allowedRoles)) {
        http_response_code(403);
        echo "<div style='font-family: sans-serif; padding: 40px; text-align: center;'><h2>403 - Permission Denied</h2><p>Your admin role (<strong>" . htmlspecialchars($userRole) . "</strong>) does not have access to this section.</p><a href='/admin/index.php'>Return to Dashboard</a></div>";
        exit();
    }
}

// Login Rate Limiting (Max 5 attempts in 15 minutes)
function isLoginRateLimited() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $attemptsKey = 'login_attempts_' . md5($ip);
    $lockoutKey = 'login_lockout_' . md5($ip);

    if (isset($_SESSION[$lockoutKey]) && time() < $_SESSION[$lockoutKey]) {
        return true;
    }

    return false;
}

function recordFailedLoginAttempt() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $attemptsKey = 'login_attempts_' . md5($ip);
    $lockoutKey = 'login_lockout_' . md5($ip);

    $_SESSION[$attemptsKey] = ($_SESSION[$attemptsKey] ?? 0) + 1;

    if ($_SESSION[$attemptsKey] >= 5) {
        $_SESSION[$lockoutKey] = time() + 900; // 15 minutes lockout
        $_SESSION[$attemptsKey] = 0;
    }
}

function resetFailedLoginAttempts() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $attemptsKey = 'login_attempts_' . md5($ip);
    $lockoutKey = 'login_lockout_' . md5($ip);
    unset($_SESSION[$attemptsKey], $_SESSION[$lockoutKey]);
}
