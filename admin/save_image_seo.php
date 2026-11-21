<?php
// ==============================================================================
// TRUENORTH CMS — AJAX IMAGE SEO SAVE HANDLER
// Persists Alternative Text, Title, Caption, and Description for any media asset
// ==============================================================================

require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/app/Models/SeoModel.php';

checkAdminAuth();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
if (!verifyCsrfToken($csrfToken)) {
    echo json_encode(['success' => false, 'error' => 'Invalid security token']);
    exit;
}

$url = trim($_POST['url'] ?? '');
if (empty($url)) {
    echo json_encode(['success' => false, 'error' => 'Missing image URL']);
    exit;
}

$adminUser = $_SESSION['admin_user'] ?? 'admin';

$saveData = [
    'alt_text' => trim($_POST['alt_text'] ?? ''),
    'title_text' => trim($_POST['title_text'] ?? ''),
    'caption' => trim($_POST['caption'] ?? ''),
    'description' => trim($_POST['description'] ?? ''),
    'uploaded_by' => trim($_POST['uploaded_by'] ?? $adminUser),
    'uploaded_to' => trim($_POST['uploaded_to'] ?? ''),
    'uploaded_to_url' => trim($_POST['uploaded_to_url'] ?? '')
];

$saved = SeoModel::saveImageSeo($url, $saveData, $adminUser);

echo json_encode([
    'success' => true,
    'message' => 'Saved.',
    'item' => $saved
]);
exit;

