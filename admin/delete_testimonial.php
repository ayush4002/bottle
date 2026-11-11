<?php
require_once __DIR__ . '/auth.php';
checkAdminAuth();

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Models/TestimonialModel.php';
require_once dirname(__DIR__) . '/app/Models/SeoModel.php';

$csrfToken = $_GET['csrf_token'] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    header("Location: /admin/testimonials?err=" . urlencode("Security token validation failed."));
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /admin/testimonials?err=" . urlencode("Missing entry ID."));
    exit();
}

if (TestimonialModel::delete($id)) {
    header("Location: /admin/testimonials?msg=" . urlencode("Entry deleted successfully."));
} else {
    header("Location: /admin/testimonials?err=" . urlencode("Failed to delete entry."));
}
exit();
