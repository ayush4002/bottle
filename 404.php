<?php
// ==============================================================================
// TRUENORTH GROUP — STANDALONE 404 NOT FOUND HANDLER
// Returns real 404 HTTP status code and renders the branded error template.
// ==============================================================================

http_response_code(404);

if (file_exists(__DIR__ . '/config/config.php')) {
    require_once __DIR__ . '/config/config.php';
} elseif (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}
require_once __DIR__ . '/app/Models/ProductModel.php';
require_once __DIR__ . '/app/Models/SeoModel.php';
require_once __DIR__ . '/app/Controllers/PageController.php';

$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/404', PHP_URL_PATH);
SeoModel::log404($requestUri, $_SERVER['HTTP_REFERER'] ?? '');

PageController::renderPage('404');
exit();

