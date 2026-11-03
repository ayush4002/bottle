<?php
require_once __DIR__ . '/auth.php';
checkAdminAuth();

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Models/BlogModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /admin/blog");
    exit();
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    header("Location: /admin/blog?err=" . urlencode("Invalid security token. Please try again."));
    exit();
}

$title = trim($_POST['title'] ?? '');
if (empty($title)) {
    header("Location: /admin/blog_editor?err=" . urlencode("Article title is required."));
    exit();
}

// Handle file upload for featured image if present
$featuredImage = trim($_POST['featured_image'] ?? '');
if (isset($_FILES['featured_image_file']) && $_FILES['featured_image_file']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['featured_image_file']['tmp_name'];
    $fileName = $_FILES['featured_image_file']['name'];
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

    if (in_array($ext, $allowed)) {
        $uploadDir = defined('UPLOAD_DIR') ? UPLOAD_DIR . '/blog' : dirname(__DIR__) . '/public/uploads/blog';
        if (!file_exists($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }
        $newFileName = 'blog_' . time() . '_' . rand(100, 999) . '.' . $ext;
        $destPath = $uploadDir . '/' . $newFileName;
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $featuredImage = '/uploads/blog/' . $newFileName;
        }
    }
}

$postData = [
    'id' => !empty($_POST['id']) ? (int)$_POST['id'] : 0,
    'title' => $title,
    'slug' => trim($_POST['slug'] ?? ''),
    'excerpt' => trim($_POST['excerpt'] ?? ''),
    'content' => trim($_POST['content'] ?? ''),
    'featured_image' => $featuredImage,
    'category' => trim($_POST['category'] ?? 'Sustainable Packaging'),
    'tags' => trim($_POST['tags'] ?? ($_POST['meta_keywords'] ?? '')),
    'author_name' => trim($_POST['author_name'] ?? 'TrueNorth Technical Division'),
    'author_role' => trim($_POST['author_role'] ?? 'Senior Packaging Engineer'),
    'read_time' => trim($_POST['read_time'] ?? '5 min read'),
    'status' => in_array(trim($_POST['status'] ?? 'published'), ['published', 'draft']) ? trim($_POST['status']) : 'published',
    'published_at' => !empty($_POST['published_at']) ? $_POST['published_at'] : date('Y-m-d H:i:s'),
    
    // Complete SEO Suite Fields
    'meta_title' => trim($_POST['meta_title'] ?? ''),
    'meta_description' => trim($_POST['meta_description'] ?? ''),
    'meta_keywords' => trim($_POST['meta_keywords'] ?? ''),
    'canonical_url' => trim($_POST['canonical_url'] ?? ''),
    'og_title' => trim($_POST['og_title'] ?? ''),
    'og_description' => trim($_POST['og_description'] ?? ''),
    'og_image' => trim($_POST['og_image'] ?? $featuredImage),
    'twitter_title' => trim($_POST['twitter_title'] ?? ''),
    'twitter_description' => trim($_POST['twitter_description'] ?? ''),
    'twitter_image' => trim($_POST['twitter_image'] ?? $featuredImage),
    'robots_meta' => trim($_POST['robots_meta'] ?? 'index, follow'),
    'schema_type' => trim($_POST['schema_type'] ?? 'BlogPosting')
];

$saved = BlogModel::savePost($postData);

if ($saved) {
    $msg = "Article '" . htmlspecialchars($title) . "' saved and published successfully with all SEO parameters!";
    header("Location: /admin/blog?msg=" . urlencode($msg));
    exit();
} else {
    header("Location: /admin/blog_editor?err=" . urlencode("Failed to save article. Please check your database permissions."));
    exit();
}
