<?php
// ==============================================================================
// TRUENORTH GROUP — BLOG & SEO CONTROLLER
// Handles public blog index, category filtering, search, dynamic single article views,
// and enterprise-grade SEO schema injection.
// ==============================================================================

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__) . '/Models/ProductModel.php';
require_once dirname(__DIR__) . '/Models/BlogModel.php';

class BlogController {

    /**
     * Render the public blog index & search page
     */
    public static function index() {
        BlogModel::initStorage();

        $categorySlug = $_GET['category'] ?? 'all';
        $searchQuery = trim($_GET['q'] ?? '');

        $posts = BlogModel::getAllPosts(true, $categorySlug === 'all' ? null : $categorySlug, null, $searchQuery);
        $categories = BlogModel::getCategories();
        $recentPosts = BlogModel::getRecentPosts(3);
        $company = ProductModel::getCompanyInfo();

        $activePage = 'blog';
        $pageTitle = 'Blog & Packaging Insights';

        // SEO Configuration for Blog Index
        $seoBaseUrl = 'https://wetruenorthgroup.com';
        $seoTitle = 'Packaging Industry Insights & Technical Guides | TrueNorth Group';
        $seoDesc = 'Explore expert engineering insights, sustainable rPET packaging developments, cleanroom compliance guides, and dispensing closure specifications from TrueNorth Group.';
        $seoKeywords = 'PET bottles blog, sustainable packaging articles, cleanroom bottle molding, cosmetic packaging guide, rPET whitepaper, packaging manufacturer news';
        $canonicalUrl = $seoBaseUrl . '/blog';
        $ogType = 'website';
        $ogImage = $seoBaseUrl . '/vikaas_inputs/thumbnails/thumbnail_pet_bottles.webp';

        $jsonLdList = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'Blog',
                'name' => 'TrueNorth Group Primary Packaging Insights',
                'description' => $seoDesc,
                'url' => $canonicalUrl,
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'TrueNorth Group',
                    'url' => $seoBaseUrl,
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => $seoBaseUrl . '/logo.webp'
                    ]
                ]
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $seoBaseUrl . '/'],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog & Insights', 'item' => $canonicalUrl]
                ]
            ]
        ];

        require_once APP_PATH . '/Views/layouts/header.php';
        require_once APP_PATH . '/Views/pages/blog.php';
        require_once APP_PATH . '/Views/layouts/modals.php';
        require_once APP_PATH . '/Views/layouts/footer.php';
    }

    /**
     * Render single blog article with complete SEO and Schema.org injection
     */
    public static function view($slug) {
        BlogModel::initStorage();
        $post = BlogModel::getPostBySlug($slug);

        if (!$post) {
            http_response_code(404);
            require_once APP_PATH . '/Controllers/PageController.php';
            PageController::renderPage('404');
            return;
        }

        // If draft and not logged in as admin, 404 for security
        if (($post['status'] ?? 'published') !== 'published') {
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (empty($_SESSION['admin_logged_in'])) {
                http_response_code(404);
                require_once APP_PATH . '/Controllers/PageController.php';
                PageController::renderPage('404');
                return;
            }
        }

        // Increment views
        BlogModel::incrementViews($slug);

        $company = ProductModel::getCompanyInfo();
        $recentPosts = BlogModel::getRecentPosts(3, $slug);
        $categories = BlogModel::getCategories();
        $activePage = 'blog';

        // SEO Configuration for Single Article
        $seoBaseUrl = 'https://wetruenorthgroup.com';
        $seoTitle = !empty($post['meta_title']) ? $post['meta_title'] : ($post['title'] . ' | TrueNorth Group');
        $seoDesc = !empty($post['meta_description']) ? $post['meta_description'] : ($post['excerpt'] ?: substr(strip_tags($post['content']), 0, 155));
        $seoKeywords = !empty($post['meta_keywords']) ? $post['meta_keywords'] : 'PET bottles, primary packaging, cleanroom molding, ISO 9001';
        $canonicalUrl = !empty($post['canonical_url']) ? $post['canonical_url'] : ($seoBaseUrl . '/blog/' . urlencode($slug));
        
        $ogType = 'article';
        $ogTitle = !empty($post['og_title']) ? $post['og_title'] : $seoTitle;
        $ogDesc = !empty($post['og_description']) ? $post['og_description'] : $seoDesc;
        
        $featuredImg = $post['featured_image'] ?? '/vikaas_inputs/thumbnails/thumbnail_pet_bottles.webp';
        $ogImage = (strpos($featuredImg, 'http') === 0) ? $featuredImg : ($seoBaseUrl . '/' . ltrim($featuredImg, '/'));

        $schemaType = !empty($post['schema_type']) ? $post['schema_type'] : 'BlogPosting';

        $jsonLdList = [
            [
                '@context' => 'https://schema.org',
                '@type' => $schemaType,
                'headline' => $post['title'],
                'description' => strip_tags($seoDesc),
                'image' => [$ogImage],
                'datePublished' => date('c', strtotime($post['published_at'] ?? 'now')),
                'dateModified' => date('c', strtotime($post['updated_at'] ?? 'now')),
                'mainEntityOfPage' => [
                    '@type' => 'WebPage',
                    '@id' => $canonicalUrl
                ],
                'author' => [
                    '@type' => 'Person',
                    'name' => $post['author_name'] ?? 'TrueNorth Technical Division',
                    'jobTitle' => $post['author_role'] ?? 'Senior Packaging Engineer'
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'TrueNorth Group',
                    'url' => $seoBaseUrl,
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => $seoBaseUrl . '/logo.webp'
                    ]
                ]
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $seoBaseUrl . '/'],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog & Insights', 'item' => $seoBaseUrl . '/blog'],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $post['title'], 'item' => $canonicalUrl]
                ]
            ]
        ];

        require_once APP_PATH . '/Views/layouts/header.php';
        require_once APP_PATH . '/Views/pages/blog_detail.php';
        require_once APP_PATH . '/Views/layouts/modals.php';
        require_once APP_PATH . '/Views/layouts/footer.php';
    }
}
