<?php
// ==========================================================================
// TRUENORTH GROUP — CENTRAL SYSTEM CONFIGURATION
// All settings are configured directly in this file (NO .env FILE NEEDED).
// ==========================================================================

// Directory Paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// --------------------------------------------------------------------------
// 1. DATABASE CONFIGURATION (EDIT FOR YOUR CPANEL DATABASE)
// --------------------------------------------------------------------------
// In cPanel, go to "MySQL Databases", create a database & user, and enter them below:
define('DB_HOST', 'localhost');                  // Usually 'localhost' on cPanel
define('DB_NAME', 'truenorth_db');               // Enter your cPanel database name
define('DB_USER', 'root');                      // Enter your cPanel database username
define('DB_PASS', '');                          // Enter your cPanel database password

// --------------------------------------------------------------------------
// 2. WEBSITE DOMAIN & ENVIRONMENT
// --------------------------------------------------------------------------
define('APP_NAME', 'TrueNorth Group');
define('APP_TAGLINE', 'Primary Packaging Specialist — PET & Glass Solutions');
define('APP_ENV', 'production'); // 'development' or 'production'

// Auto-detect live domain (e.g., https://wetruenorthgroup.com)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'wetruenorthgroup.com';
define('BASE_URL', $protocol . $host);

// --------------------------------------------------------------------------
// 3. ADMIN PANEL CREDENTIALS
// --------------------------------------------------------------------------
// Used for logging in at /admin/login.php
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'admin123'); // Change to your desired secure password

// --------------------------------------------------------------------------
// 4. SMTP MAIL SETTINGS (FOR QUOTE INQUIRIES & CONTACT FORMS)
// --------------------------------------------------------------------------
define('SMTP_HOST', 'mail.wetruenorthgroup.com');
define('SMTP_PORT', 465);
define('SMTP_USER', 'info@wetruenorthgroup.com');
define('SMTP_PASS', 'TrueNorth2026Group');
define('EMAIL_TO', 'info@wetruenorthgroup.com');

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
if (isset($_GET['debug']) || APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    // Production mode: log errors securely, do not leak raw stack traces to visitors
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}
