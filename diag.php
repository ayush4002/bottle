<?php
// ==============================================================================
// TRUENORTH GROUP — DETAILED PATH & FILE LOCATOR
// Open in browser: https://wetruenorthgroup.com/diag.php
// ==============================================================================

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

?>
<!DOCTYPE html>
<html>
<head>
  <title>TrueNorth Path Diagnostic</title>
  <style>
    body { font-family: monospace; background: #0f172a; color: #f8fafc; padding: 30px; font-size: 14px; }
    .box { background: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 20px; max-width: 900px; margin: 0 auto; }
    h2 { color: #38bdf8; margin-top: 0; }
    .ok { color: #4ade80; font-weight: bold; }
    .err { color: #f87171; font-weight: bold; }
    .warn { color: #facc15; font-weight: bold; }
    pre { background: #020617; padding: 12px; border-radius: 6px; overflow-x: auto; color: #cbd5e1; }
  </style>
</head>
<body>
<div class="box">
  <h2>🔍 TrueNorth Files &amp; Folders Diagnostic</h2>

  <div><strong>Current Script Path:</strong> <?= htmlspecialchars(__FILE__) ?></div>
  <div><strong>Current Directory:</strong> <?= htmlspecialchars(__DIR__) ?></div>
  <hr style="border-color: #334155; margin: 15px 0;">

  <!-- 1. Check Root Contents -->
  <div>
    <strong>Files &amp; Folders inside <?= htmlspecialchars(__DIR__) ?>:</strong>
    <pre><?php
      $rootItems = scandir(__DIR__);
      $folders = [];
      $files = [];
      foreach ($rootItems as $item) {
        if ($item === '.' || $item === '..') continue;
        if (is_dir(__DIR__ . '/' . $item)) {
          $folders[] = "[DIR]  " . $item;
        } else {
          $files[] = "[FILE] " . $item . " (" . filesize(__DIR__ . '/' . $item) . " bytes)";
        }
      }
      echo implode("\n", array_merge($folders, $files));
    ?></pre>
  </div>

  <!-- 2. Locate app/ directory -->
  <div>
    <strong>Checking for `app` folder:</strong><br>
    <?php
      $appFound = false;
      $possibleAppPaths = [
        __DIR__ . '/app',
        __DIR__ . '/App',
        __DIR__ . '/CPANEL_PUBLIC_HTML/app',
        __DIR__ . '/cpanel_public_html/app',
      ];

      foreach ($possibleAppPaths as $p) {
        if (is_dir($p)) {
          echo "<span class='ok'>✓ FOUND folder at: " . htmlspecialchars($p) . "</span><br>";
          $appFound = $p;
          break;
        }
      }

      if (!$appFound) {
        echo "<span class='err'>✗ FAILED: 'app' folder is completely missing from " . htmlspecialchars(__DIR__) . "!</span><br>";
        echo "<div style='background:rgba(239,68,68,0.2); padding:10px; border-radius:6px; margin:10px 0;'><strong>FIX:</strong> You need to upload the <code>app/</code> folder from your computer into <code>public_html/</code> in cPanel File Manager!</div>";
      }
    ?>
  </div>

  <!-- 3. Locate ProductModel.php -->
  <?php if ($appFound): ?>
  <div style="margin-top: 15px;">
    <strong>Checking for `ProductModel.php` inside `<?= htmlspecialchars($appFound) ?>`:</strong><br>
    <?php
      $modelPaths = [
        $appFound . '/Models/ProductModel.php',
        $appFound . '/models/ProductModel.php',
        $appFound . '/Models/productmodel.php',
        $appFound . '/models/productmodel.php',
      ];

      $modelFound = false;
      foreach ($modelPaths as $mp) {
        if (file_exists($mp)) {
          echo "<span class='ok'>✓ FOUND ProductModel at: " . htmlspecialchars($mp) . " (" . filesize($mp) . " bytes)</span><br>";
          $modelFound = $mp;
          break;
        }
      }

      if (!$modelFound) {
        echo "<span class='err'>✗ FAILED: ProductModel.php was not found inside " . htmlspecialchars($appFound) . "!</span><br>";
        echo "Folders inside " . htmlspecialchars($appFound) . ":<br>";
        echo "<pre>" . implode("\n", scandir($appFound)) . "</pre>";
        if (is_dir($appFound . '/Models')) {
          echo "Files inside Models:<br><pre>" . implode("\n", scandir($appFound . '/Models')) . "</pre>";
        } elseif (is_dir($appFound . '/models')) {
          echo "Files inside models:<br><pre>" . implode("\n", scandir($appFound . '/models')) . "</pre>";
        }
      } else {
        echo "<br><strong>Testing ProductModel execution:</strong> ";
        try {
          require_once $modelFound;
          echo "<span class='ok'>✓ LOADED SUCCESSFULLY!</span><br>";
        } catch (Throwable $e) {
          echo "<span class='err'>✗ Error loading: " . htmlspecialchars($e->getMessage()) . "</span><br>";
        }
      }
    ?>
  </div>
  <?php endif; ?>

  <!-- 4. Image Casing & Path Check -->
  <div style="margin-top: 15px;">
    <strong>Checking Image Assets &amp; Linux Case Sensitivity:</strong><br>
    <?php
      $testImages = [
        'Caps Image' => 'brand_assets/caps/nb108-a.png',
        'Pumps Image' => 'brand_assets/pumps/nb101-a.png',
        'Sprays Image' => 'brand_assets/sprays/nb304-a.png',
        'Vikaas Thumbnails' => 'vikaas_inputs/thumbnails/thumbnail_pet_bottles.png',
        'Logo SVG' => 'logo_svg.svg',
      ];

      foreach ($testImages as $label => $relPath) {
        $exact = file_exists(__DIR__ . '/' . $relPath) || file_exists(__DIR__ . '/public/' . $relPath);
        if ($exact) {
          echo "<span class='ok'>✓ " . htmlspecialchars($label) . ": EXACT MATCH FOUND</span><br>";
        } else {
          $dir = dirname($relPath);
          $file = basename($relPath);
          $searchDir = __DIR__ . ($dir !== '.' ? '/' . $dir : '');
          $foundCi = false;
          if (is_dir($searchDir)) {
            $sc = @scandir($searchDir);
            if ($sc) {
              foreach ($sc as $f) {
                if (strcasecmp($f, $file) === 0) {
                  $foundCi = $f;
                  break;
                }
              }
            }
          }
          if ($foundCi) {
            echo "<span class='warn'>⚠ " . htmlspecialchars($label) . ": Case mismatch! Requested <code>" . htmlspecialchars($file) . "</code>, but file on Linux is <code>" . htmlspecialchars($foundCi) . "</code>. (Our new index.php automatically fixes this!).</span><br>";
          } else {
            echo "<span class='err'>✗ " . htmlspecialchars($label) . ": File completely missing in folder " . htmlspecialchars($dir) . "/!</span><br>";
          }
        }
      }
    ?>
  </div>

  <!-- 5. Solution Summary -->
  <hr style="border-color: #334155; margin: 15px 0;">
  <div style="background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 8px; padding: 16px;">
    <strong>💡 Quick Explanation:</strong><br>
    To fix all images immediately, simply replace <code>index.php</code> in your cPanel <code>public_html/</code> with the updated <code>index.php</code> from your <code>CPANEL_PUBLIC_HTML</code> folder. It automatically resolves all uppercase/lowercase image names on Linux!
  </div>

</div>
</body>
</html>
