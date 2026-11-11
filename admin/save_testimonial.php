<?php
require_once dirname(__DIR__) . '/includes/upload_guard.php';
require_once __DIR__ . '/auth.php';
checkAdminAuth();

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Models/TestimonialModel.php';
require_once dirname(__DIR__) . '/app/Models/SeoModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /admin/testimonials");
    exit();
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    header("Location: /admin/testimonials?err=" . urlencode("Invalid security token. Please try again."));
    exit();
}

$editId = !empty($_POST['id']) ? (int)$_POST['id'] : 0;
$entryType = ($_POST['type'] ?? 'quote') === 'logo' ? 'logo' : 'quote';
$isLogo = ($entryType === 'logo') ? 1 : 0;

// Logo image: prefer upload, else keep existing/typed path
$logoImage = trim($_POST['logo_image'] ?? '');
if ($isLogo && isset($_FILES['logo_image_file']) && $_FILES['logo_image_file']['error'] === UPLOAD_ERR_OK) {
    $ext = tn_upload_safe_image_ext($_FILES['logo_image_file']['tmp_name'], $_FILES['logo_image_file']['name']);
    if ($ext !== false) {
        $uploadDir = (defined('UPLOAD_DIR') ? UPLOAD_DIR : dirname(__DIR__) . '/public/uploads/products') . '/clients';
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
        $newName = 'client_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
        if (move_uploaded_file($_FILES['logo_image_file']['tmp_name'], $uploadDir . '/' . $newName)) {
            $logoImage = '/uploads/products/clients/' . $newName;
        }
    }
}

$data = [
    'id' => $editId,
    'client_name' => $isLogo ? '' : trim($_POST['client_name'] ?? ''),
    'company' => trim($_POST['company'] ?? ''),
    'role' => $isLogo ? '' : trim($_POST['role'] ?? ''),
    'quote' => $isLogo ? '' : trim($_POST['quote'] ?? ''),
    'rating' => $isLogo ? 0 : (int)($_POST['rating'] ?? 5),
    'logo_image' => $isLogo ? $logoImage : '',
    'is_logo' => $isLogo,
    'status' => trim($_POST['status'] ?? 'published'),
    'sort_order' => (int)($_POST['sort_order'] ?? 0)
];

// Identity rule: logos need a label or image; quotes need a client or company name
if ($isLogo && empty($data['company']) && empty($data['logo_image'])) {
    header("Location: /admin/testimonial_editor?id=" . $editId . "&err=" . urlencode("Client logo needs a label or an image."));
    exit();
}
if (!$isLogo && empty($data['client_name']) && empty($data['company'])) {
    header("Location: /admin/testimonial_editor?id=" . $editId . "&err=" . urlencode("A testimonial needs a client name or company."));
    exit();
}

$saved = TestimonialModel::save($data);

if ($saved) {
    header("Location: /admin/testimonials?msg=" . urlencode($isLogo ? "Client logo saved successfully." : "Testimonial saved successfully."));
    exit();
} else {
    header("Location: /admin/testimonial_editor?id=" . $editId . "&err=" . urlencode("Failed to save entry. Check database permissions."));
    exit();
}
