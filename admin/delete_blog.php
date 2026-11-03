<?php
require_once __DIR__ . '/auth.php';
checkAdminAuth();

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Models/BlogModel.php';

$csrfToken = $_GET['csrf_token'] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    header("Location: /admin/blog?err=" . urlencode("Security token validation failed."));
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /admin/blog?err=" . urlencode("Missing article ID."));
    exit();
}

$deleted = BlogModel::deletePost($id);

if ($deleted) {
    header("Location: /admin/blog?msg=" . urlencode("Article deleted successfully."));
    exit();
} else {
    header("Location: /admin/blog?err=" . urlencode("Failed to delete article."));
    exit();
}

