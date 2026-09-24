<?php
// ==========================================================================
// TRUENORTH GROUP — API: PRODUCTS ENDPOINT
// ==========================================================================

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Controllers/ProductController.php';

ProductController::handleProductsApi();
