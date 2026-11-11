<?php
// Usage: php scripts/set-admin-password.php [username]
// Prompts for a new password and writes ADMIN_USER / ADMIN_PASS_HASH into config/secrets.php.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

$user = $argv[1] ?? 'admin';
echo "New password for '{$user}' (min 12 chars): ";
$pass = trim((string)fgets(STDIN));
if (strlen($pass) < 12) { fwrite(STDERR, "Password too short.\n"); exit(1); }

$file = dirname(__DIR__) . '/config/secrets.php';
if (!file_exists($file)) {
    copy(dirname(__DIR__) . '/config/secrets.example.php', $file);
}
$src = file_get_contents($file);
$hash = password_hash($pass, PASSWORD_DEFAULT);
$set = function ($src, $name, $value) {
    $line = "define('{$name}', " . var_export($value, true) . ");";
    $re = "/define\(\s*'" . $name . "'\s*,.*?\);/";
    return preg_match($re, $src) ? preg_replace_callback($re, fn() => $line, $src, 1) : rtrim($src) . "\n" . $line . "\n";
};
$src = $set($src, 'ADMIN_USER', $user);
$src = $set($src, 'ADMIN_PASS_HASH', $hash);
file_put_contents($file, $src, LOCK_EX);
echo "Saved to config/secrets.php\n";
