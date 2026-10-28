<?php
// ==========================================================================
// TRUENORTH GROUP — API: SEARCH ENDPOINT
// ==========================================================================

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Controllers/ProductController.php';

ProductController::handleSearchApi();
