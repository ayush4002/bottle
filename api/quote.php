<?php
// ==========================================================================
// TRUENORTH GROUP — API: QUOTE / CONTACT SUBMISSION ENDPOINT
// ==========================================================================

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Controllers/QuoteController.php';

QuoteController::handleInquiry();
