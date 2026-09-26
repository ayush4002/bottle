<?php
// ==============================================================================
// TRUENORTH GROUP — CPANEL SERVER HEALTH & DIAGNOSTIC CHECKER
// Access this in your browser: https://yourdomain.com/test_server.php
// (Delete this file once your website is live and working)
// ==============================================================================

ini_set('display_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Server Health Check — TrueNorth Group</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 40px 20px; line-height: 1.6; }
    .card { max-width: 750px; margin: 0 auto; background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.4); }
    h1 { margin-top: 0; font-size: 1.5rem; color: #38bdf8; border-bottom: 1px solid #334155; padding-bottom: 15px; }
    .item { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.06); }
    .badge { font-weight: 700; font-size: 0.85rem; padding: 4px 10px; border-radius: 6px; }
    .badge-ok { background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.4); }
    .badge-warn { background: rgba(234, 179, 8, 0.2); color: #facc15; border: 1px solid rgba(234, 179, 8, 0.4); }
    .badge-err { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); }
    .detail { font-size: 0.85rem; color: #94a3b8; margin-top: 4px; }
    .tip-box { background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 8px; padding: 16px; margin-top: 25px; font-size: 0.9rem; }
  </style>
</head>
<body>
<div class="card">
  <h1>Server Health &amp; Environment Diagnostics</h1>

  <!-- 1. PHP Version -->
  <div class="item">
    <div>
      <strong>PHP Version</strong>
      <div class="detail">Required: PHP 7.4 or higher (PHP 8.0 - 8.3 recommended)</div>
    </div>
    <div>
      <?php if (version_compare(PHP_VERSION, '7.4.0', '>=')): ?>
        <span class="badge badge-ok">OK (<?= PHP_VERSION ?>)</span>
      <?php else: ?>
        <span class="badge badge-err">OUTDATED (<?= PHP_VERSION ?>)</span>
      <?php endif; ?>
    </div>
  </div>

  <!-- 2. PDO MySQL Extension -->
  <div class="item">
    <div>
      <strong>PDO MySQL Extension</strong>
      <div class="detail">Required for database communication</div>
    </div>
    <div>
      <?php if (extension_loaded('pdo_mysql')): ?>
        <span class="badge badge-ok">ENABLED</span>
      <?php else: ?>
        <span class="badge badge-err">MISSING (Enable in cPanel &gt; Select PHP Version)</span>
      <?php endif; ?>
    </div>
  </div>

  <!-- 3. Config File Status -->
  <div class="item">
    <div>
      <strong>Configuration File</strong>
      <div class="detail">config/config.php</div>
    </div>
    <div>
      <?php 
        $configFile = __DIR__ . '/config/config.php';
        if (file_exists($configFile)):
          require_once $configFile;
      ?>
        <span class="badge badge-ok">FOUND</span>
      <?php else: ?>
        <span class="badge badge-err">NOT FOUND</span>
      <?php endif; ?>
    </div>
  </div>

  <!-- 4. MySQL Database Connection -->
  <div class="item">
    <div>
      <strong>Database Connection</strong>
      <div class="detail">Host: <?= defined('DB_HOST') ? htmlspecialchars(DB_HOST) : 'N/A' ?> | DB: <?= defined('DB_NAME') ? htmlspecialchars(DB_NAME) : 'N/A' ?> | User: <?= defined('DB_USER') ? htmlspecialchars(DB_USER) : 'N/A' ?></div>
    </div>
    <div>
      <?php
        $dbOk = false;
        $dbErr = '';
        if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER')) {
          try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_TIMEOUT => 5, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
            $productCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
            $dbOk = true;
          } catch (Exception $e) {
            $dbErr = $e->getMessage();
          }
        }
      ?>
      <?php if ($dbOk): ?>
        <span class="badge badge-ok">CONNECTED (<?= (int)$productCount ?> Products Found)</span>
      <?php else: ?>
        <span class="badge badge-err">FAILED</span>
        <div class="detail" style="color: #f87171; text-align: right;"><?= htmlspecialchars($dbErr) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- 5. Folder Permissions -->
  <div class="item">
    <div>
      <strong>Upload Folder Writable</strong>
      <div class="detail">public/uploads/products/</div>
    </div>
    <div>
      <?php
        $uploadDir = __DIR__ . '/public/uploads/products';
        $uploadOk = is_dir($uploadDir) && is_writable($uploadDir);
      ?>
      <?php if ($uploadOk): ?>
        <span class="badge badge-ok">WRITABLE (755)</span>
      <?php else: ?>
        <span class="badge badge-warn">NOT WRITABLE (Set permissions to 755 in cPanel)</span>
      <?php endif; ?>
    </div>
  </div>

  <!-- 6. Homepage Render Test -->
  <div class="item">
    <div>
      <strong>Homepage Render Test</strong>
      <div class="detail">Simulating PageController::renderPage('home')</div>
    </div>
    <div>
      <?php
        $renderOk = false;
        $renderErr = '';
        try {
          require_once __DIR__ . '/app/Models/ProductModel.php';
          require_once __DIR__ . '/app/Controllers/PageController.php';
          require_once __DIR__ . '/app/Controllers/ProductController.php';
          require_once __DIR__ . '/app/Controllers/QuoteController.php';
          ob_start();
          PageController::renderPage('home');
          $htmlOutput = ob_get_clean();
          if (strlen($htmlOutput) > 100) {
            $renderOk = true;
          } else {
            $renderErr = "Empty output generated";
          }
        } catch (Throwable $e) {
          if (ob_get_level()) ob_end_clean();
          $renderErr = $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
        }
      ?>
      <?php if ($renderOk): ?>
        <span class="badge badge-ok">SUCCESS (<?= strlen($htmlOutput) ?> bytes)</span>
      <?php else: ?>
        <span class="badge badge-err">FAILED</span>
        <div class="detail" style="color: #f87171; text-align: right;"><?= htmlspecialchars($renderErr) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- 7. Recent Server Error Log -->
  <?php if (file_exists(__DIR__ . '/error_log')): ?>
  <div style="margin-top: 20px;">
    <strong>Recent Server PHP Errors (from public_html/error_log):</strong>
    <?php
      $errLines = array_slice(file(__DIR__ . '/error_log'), -15);
      echo "<pre style='background:#020617; border:1px solid #ef4444; color:#fca5a5; padding:12px; border-radius:8px; font-size:0.75rem; overflow-x:auto; white-space:pre-wrap; margin-top:8px;'>" . htmlspecialchars(implode("", $errLines)) . "</pre>";
    ?>
  </div>
  <?php endif; ?>

  <!-- Suggested Action -->
  <div class="tip-box">
    <?php if (!$dbOk): ?>
      <strong>⚠️ Next Step:</strong> Open <code>config/config.php</code> in cPanel File Manager and enter your actual cPanel Database Name, Database Username, and Database Password.<br>
      <em>Error details: <?= htmlspecialchars($dbErr) ?></em>
    <?php elseif (!$renderOk): ?>
      <strong>⚠️ Homepage Render Issue:</strong> <?= htmlspecialchars($renderErr) ?>
    <?php else: ?>
      <strong>✅ Everything looks good!</strong> Your server environment and database are ready. You can now access your homepage at <a href="/" style="color: #38bdf8;">Homepage &rarr;</a> or CMS login at <a href="/admin/login.php" style="color: #38bdf8;">/admin/login.php &rarr;</a>.
    <?php endif; ?>
  </div>

</div>
</body>
</html>
