<?php
// Vercel Serverless Entrypoint Router for TrueNorth CMS
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$baseDir = dirname(__DIR__);

// Serve static assets directly if requested
$filePath = $baseDir . $uri;
if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mimes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'svg'  => 'image/svg+xml',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'ico'  => 'image/x-icon',
        'json' => 'application/json'
    ];
    if (isset($mimes[$ext])) {
        header("Content-Type: " . $mimes[$ext]);
        readfile($filePath);
        exit();
    }
}

// Handle Admin Panel routing
if (strpos($uri, '/admin') === 0) {
    $adminScript = $baseDir . ($uri === '/admin' || $uri === '/admin/' ? '/admin/index.php' : $uri);
    if (file_exists($adminScript) && !is_dir($adminScript)) {
        require $adminScript;
        exit();
    }
}

// Handle Quote Page
if ($uri === '/quote' || $uri === '/quote.php') {
    require $baseDir . '/quote.php';
    exit();
}

// Default Homepage
require $baseDir . '/index.php';
