<?php
// ==========================================================================
// TRUENORTH GROUP — CENTRAL SYSTEM CONFIGURATION
// ==========================================================================

define('APP_NAME', 'TrueNorth Group');
define('APP_TAGLINE', 'Primary Packaging Specialist — PET & Glass Solutions');
define('APP_ENV', 'production'); // 'development' or 'production'

// Base URL detection
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', $protocol . $host);

// Directory Paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// SMTP Mail Configuration
define('SMTP_HOST', process_env('SMTP_HOST', 'mail.wetruenorthgroup.com'));
define('SMTP_PORT', (int)process_env('SMTP_PORT', 465));
define('SMTP_USER', process_env('SMTP_USER', 'info@wetruenorthgroup.com'));
define('SMTP_PASS', process_env('SMTP_PASS', 'TrueNorth2026Group'));
define('EMAIL_TO', process_env('EMAIL_TO', 'info@wetruenorthgroup.com'));

// Database Credentials
define('DB_HOST', process_env('DB_HOST', 'localhost'));
define('DB_NAME', process_env('DB_NAME', 'truenorth_db'));
define('DB_USER', process_env('DB_USER', 'root'));
define('DB_PASS', process_env('DB_PASS', ''));

// Admin Credentials & Custom Upload Storage
define('ADMIN_USER', process_env('ADMIN_USER', 'admin'));
define('ADMIN_PASS', process_env('ADMIN_PASS', 'admin123'));
define('CUSTOM_PRODUCTS_FILE', ROOT_PATH . '/config/custom_products.json');
define('UPLOAD_DIR', PUBLIC_PATH . '/uploads/products');
define('UPLOAD_URL', '/public/uploads/products');

// Environment Helper
function process_env($key, $default = '') {
    return getenv($key) !== false ? getenv($key) : ($_ENV[$key] ?? $default);
}

// Error reporting configuration
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
