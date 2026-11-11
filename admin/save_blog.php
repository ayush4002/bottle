<?php
require_once dirname(__DIR__) . '/includes/html_sanitizer.php';
require_once dirname(__DIR__) . '/includes/upload_guard.php';
require_once __DIR__ . '/auth.php';
checkAdminAuth();

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Models/BlogModel.php';
require_once dirname(__DIR__) . '/app/Models/SeoModel.php';

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

$slug = trim($_POST['slug'] ?? '');
if (empty($slug)) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
}

// Handle file upload for featured image if present
$featuredImage = trim($_POST['featured_image'] ?? '');
if (isset($_FILES['featured_image_file']) && $_FILES['featured_image_file']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['featured_image_file']['tmp_name'];
    $fileName = $_FILES['featured_image_file']['name'];
    $ext = tn_upload_safe_image_ext($fileTmpPath, $fileName);

    if ($ext !== false) {
        $uploadDir = defined('UPLOAD_DIR') ? UPLOAD_DIR . '/blog' : dirname(__DIR__) . '/public/uploads/blog';
        if (!file_exists($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }
        $newFileName = 'blog_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
        $destPath = $uploadDir . '/' . $newFileName;
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $featuredImage = '/uploads/blog/' . $newFileName;
        }
    }
}

$robotsIndex = isset($_POST['robots_index']) ? (int)$_POST['robots_index'] : 1;
$robotsFollow = isset($_POST['robots_follow']) ? (int)$_POST['robots_follow'] : 1;
if (isset($_POST['robots_meta'])) {
    if (strpos($_POST['robots_meta'], 'noindex') !== false) $robotsIndex = 0;
    if (strpos($_POST['robots_meta'], 'nofollow') !== false) $robotsFollow = 0;
}

$postData = [
    'id' => !empty($_POST['id']) ? (int)$_POST['id'] : 0,
    'title' => $title,
    'slug' => $slug,
    'excerpt' => trim($_POST['excerpt'] ?? ''),
    'content' => tn_sanitize_html(trim($_POST['content'] ?? '')), // strips scripts/handlers (stored-XSS)
    'featured_image' => $featuredImage,
    'category' => trim($_POST['category'] ?? 'Sustainable Packaging'),
    'tags' => trim($_POST['tags'] ?? ($_POST['meta_keywords'] ?? '')),
    'author_name' => trim($_POST['author_name'] ?? 'TrueNorth Technical Division'),
    'author_role' => trim($_POST['author_role'] ?? 'Senior Packaging Engineer'),
    'read_time' => trim($_POST['read_time'] ?? '5 min read'),
    'status' => in_array(trim($_POST['status'] ?? 'published'), ['published', 'draft']) ? trim($_POST['status']) : 'published',
    'published_at' => !empty($_POST['published_at']) ? $_POST['published_at'] : date('Y-m-d H:i:s'),
    
    // Complete WordPress SEO Suite Fields
    'meta_title' => trim($_POST['meta_title'] ?? ''),
    'meta_description' => trim($_POST['meta_description'] ?? ''),
    'meta_keywords' => trim($_POST['meta_keywords'] ?? ''),
    'focus_keyword' => trim($_POST['focus_keyword'] ?? ''),
    'h1_override' => trim($_POST['h1_override'] ?? ''),
    'canonical_url' => trim($_POST['canonical_url'] ?? ''),
    'robots_index' => $robotsIndex,
    'robots_follow' => $robotsFollow,
    'robots_meta' => ($robotsIndex ? 'index' : 'noindex') . ', ' . ($robotsFollow ? 'follow' : 'nofollow'),
    'og_title' => trim($_POST['og_title'] ?? ''),
    'og_description' => trim($_POST['og_description'] ?? ''),
    'og_image' => trim($_POST['og_image'] ?? $featuredImage),
    'twitter_title' => trim($_POST['twitter_title'] ?? ''),
    'twitter_description' => trim($_POST['twitter_description'] ?? ''),
    'twitter_image' => trim($_POST['twitter_image'] ?? $featuredImage),
    'twitter_card' => trim($_POST['twitter_card'] ?? 'summary_large_image'),
    'schema_type' => trim($_POST['schema_type'] ?? 'BlogPosting'),
    'custom_schema_json' => trim($_POST['custom_schema_json'] ?? ''),
    'cornerstone_content' => !empty($_POST['cornerstone_content']) ? 1 : 0,
    'breadcrumbs_title' => trim($_POST['breadcrumbs_title'] ?? ''),
    'advanced_robots' => trim($_POST['advanced_robots'] ?? '')
];

$saved = BlogModel::savePost($postData);

if ($saved) {
    $adminUser = $_SESSION['admin_user'] ?? 'admin';
    if (is_array($adminUser)) $adminUser = $adminUser['username'] ?? 'admin';

    // Synchronize to unified SeoModel seo_pages table
    $seoData = [
        'page_type' => 'blog',
        'page_ref_id' => $postData['id'] ?: null,
        'url_slug' => '/blog/' . $slug,
        'old_url_slug' => !empty($_POST['old_slug']) ? ('/blog/' . trim($_POST['old_slug'])) : null,
        'meta_title' => $postData['meta_title'],
        'meta_description' => $postData['meta_description'],
        'meta_keywords' => $postData['meta_keywords'],
        'focus_keyword' => $postData['focus_keyword'],
        'canonical_url' => $postData['canonical_url'],
        'robots_index' => $robotsIndex,
        'robots_follow' => $robotsFollow,
        'og_title' => $postData['og_title'],
        'og_description' => $postData['og_description'],
        'og_image' => $postData['og_image'],
        'h1_override' => $postData['h1_override'],
        'schema_type' => $postData['schema_type'],
        'custom_schema_json' => $postData['custom_schema_json'],
        'cornerstone_content' => $postData['cornerstone_content'],
        'breadcrumbs_title' => $postData['breadcrumbs_title'],
        'twitter_title' => $postData['twitter_title'],
        'twitter_description' => $postData['twitter_description'],
        'twitter_image' => $postData['twitter_image'],
        'twitter_card' => $postData['twitter_card'],
        'advanced_robots' => $postData['advanced_robots']
    ];
    SeoModel::savePageSeo($seoData, $adminUser);

    // Save FAQ repeater if present
    $faqQuestions = $_POST['faq_questions'] ?? [];
    $faqAnswers = $_POST['faq_answers'] ?? [];
    $faqsToSave = [];
    if (is_array($faqQuestions)) {
        foreach ($faqQuestions as $i => $q) {
            $a = $faqAnswers[$i] ?? '';
            if (!empty(trim($q)) && !empty(trim($a))) {
                $faqsToSave[] = [
                    'question' => trim($q),
                    'answer' => trim($a)
                ];
            }
        }
    }
    SeoModel::saveFaqs('blog', '/blog/' . $slug, $faqsToSave, $adminUser);

    $msg = "Article '" . htmlspecialchars($title) . "' saved and published successfully with all WordPress SEO parameters!";
    header("Location: /admin/blog?msg=" . urlencode($msg));
    exit();
} else {
    header("Location: /admin/blog_editor?err=" . urlencode("Failed to save article. Please check your database permissions."));
    exit();
}
