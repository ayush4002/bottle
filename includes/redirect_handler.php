<?php
// ==============================================================================
// TRUENORTH GROUP — EARLY REDIRECT & 404 INTERCEPTOR
// Executes early in request lifecycle before any HTML output.
// Matches requested URI against seo_redirects and triggers status codes 301/302/410.
// ==============================================================================

require_once dirname(__DIR__) . '/app/Models/SeoModel.php';

function handle_seo_redirects() {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    // Skip static assets, admin post requests, or api calls
    $ext = strtolower(pathinfo(parse_url($uri, PHP_URL_PATH), PATHINFO_EXTENSION));
    if (in_array($ext, ['css', 'js', 'png', 'jpg', 'jpeg', 'webp', 'svg', 'gif', 'ico', 'woff', 'woff2', 'ttf'])) {
        return;
    }
    SeoModel::handleEarlyRedirect($uri);
}

// Run immediately upon include
handle_seo_redirects();

