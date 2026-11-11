<?php
// ==========================================================================
// TRUENORTH GROUP — CENTRAL SYSTEM CONFIGURATION
// All settings are configured directly in this file (NO .env FILE NEEDED).
// ==========================================================================

// Directory Paths (defined individually so external ROOT_PATH pre-definitions still work)
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', is_dir(__DIR__ . '/app') ? __DIR__ : dirname(__DIR__));
}
if (!defined('APP_PATH')) {
    define('APP_PATH', ROOT_PATH . '/app');
}
if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', ROOT_PATH . '/config');
}
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', ROOT_PATH . '/public');
}

// --------------------------------------------------------------------------
// 1. DATABASE CONFIGURATION (EDIT FOR YOUR CPANEL DATABASE)
// --------------------------------------------------------------------------
// Real credentials live in config/secrets.php (NOT committed to git).
// Copy config/secrets.example.php to config/secrets.php and fill it in.
if (file_exists(__DIR__ . '/secrets.php')) {
    require_once __DIR__ . '/secrets.php';
}
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'truenorth_db');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

// --------------------------------------------------------------------------
// 2. WEBSITE DOMAIN & ENVIRONMENT
// --------------------------------------------------------------------------
define('APP_NAME', 'TrueNorth Group');
define('APP_TAGLINE', 'Primary Packaging Specialist — PET & Glass Solutions');
define('APP_ENV', 'production'); // 'development' or 'production'

// Auto-detect live domain (e.g., https://wetruenorthgroup.com)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'wetruenorthgroup.com';
if (!preg_match('/^(localhost|127\.0\.0\.1|(www\.)?wetruenorthgroup\.com)(:\d+)?$/i', $host)) {
    $host = 'wetruenorthgroup.com'; // ignore spoofed Host headers
}
define('BASE_URL', $protocol . $host);

// --------------------------------------------------------------------------
// 3. ADMIN PANEL BOOTSTRAP ACCOUNT
// --------------------------------------------------------------------------
// Set ADMIN_USER / ADMIN_PASS_HASH in config/secrets.php.
// Generate a hash with:  php scripts/set-admin-password.php
// There is deliberately NO default password.
if (!defined('ADMIN_USER')) define('ADMIN_USER', 'admin');
if (!defined('ADMIN_PASS_HASH')) define('ADMIN_PASS_HASH', '');

// --------------------------------------------------------------------------
// 4. SMTP MAIL SETTINGS (FOR QUOTE INQUIRIES & CONTACT FORMS)
// --------------------------------------------------------------------------
// SMTP_PASS must be set in config/secrets.php (never in this file).
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'mail.wetruenorthgroup.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 465);
if (!defined('SMTP_USER')) define('SMTP_USER', 'info@wetruenorthgroup.com');
if (!defined('SMTP_PASS')) define('SMTP_PASS', '');
if (!defined('EMAIL_TO')) define('EMAIL_TO', 'info@wetruenorthgroup.com');

// --------------------------------------------------------------------------
// 5. STORAGE & UPLOAD PATHS
// --------------------------------------------------------------------------
define('CUSTOM_PRODUCTS_FILE', ROOT_PATH . '/config/custom_products.json');
define('UPLOAD_DIR', PUBLIC_PATH . '/uploads/products');
define('UPLOAD_URL', '/public/uploads/products');

// Environment Helper for backward compatibility
function process_env($key, $default = '') {
    $val = getenv($key);
    return ($val !== false && $val !== '') ? $val : $default;
}

// --------------------------------------------------------------------------
// 6. ERROR REPORTING & LOGGING
// --------------------------------------------------------------------------
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    // Production mode: log errors securely, do not leak raw stack traces to visitors
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}
