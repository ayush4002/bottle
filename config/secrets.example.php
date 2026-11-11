<?php
// ==========================================================================
// TRUENORTH GROUP — SECRETS TEMPLATE
// Copy this file to config/secrets.php and fill in real values.
// config/secrets.php is git-ignored and blocked from web access.
// ==========================================================================

// Database (cPanel → MySQL Databases)
define('DB_HOST', 'localhost');
define('DB_NAME', 'truenorth_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Bootstrap admin login (used only when the user is not in the DB / admin_users.json).
// Generate the hash with:  php scripts/set-admin-password.php
define('ADMIN_USER', 'admin');
define('ADMIN_PASS_HASH', '');

// Outgoing mail
define('SMTP_HOST', 'mail.wetruenorthgroup.com');
define('SMTP_PORT', 465);              // 465 = implicit SSL, 587 = STARTTLS
define('SMTP_USER', 'info@wetruenorthgroup.com');
define('SMTP_PASS', '');
define('SMTP_VERIFY_SSL', true);       // set false only if your mail host has an invalid certificate
define('EMAIL_TO', 'info@wetruenorthgroup.com');
