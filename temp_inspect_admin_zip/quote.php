<?php
// ==============================================================================
// TRUENORTH GROUP — QUOTE INQUIRY ADAPTER
// Bridges legacy /quote.php requests directly to QuoteController & InquiryModel
// ==============================================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Controllers/QuoteController.php';

QuoteController::handleInquiry();
