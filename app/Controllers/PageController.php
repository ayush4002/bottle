<?php
// ==========================================================================
// TRUENORTH GROUP — PAGE CONTROLLER
// Handles clean server-side PHP page rendering & dynamic database data loading
// ==========================================================================

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__) . '/Models/ProductModel.php';

class PageController {

    public static function renderPage($pageName = 'home', $params = []) {
        $allowedPages = ['home', 'products', 'product_detail', 'about', 'contact', 'custom', 'sustainability'];
        
        if (!in_array($pageName, $allowedPages)) {
            $pageName = 'home';
        }

        $company = ProductModel::getCompanyInfo();
        $categories = ProductModel::getCategories();
        $series = ProductModel::getSeries();

        $activePage = $pageName === 'product_detail' ? 'products' : $pageName;

        // Render header layout
        require_once APP_PATH . '/Views/layouts/header.php';
        
        // Render ONLY the specific page view requested via server-side PHP
        $file = APP_PATH . "/Views/pages/{$pageName}.php";
        if (file_exists($file)) {
            require_once $file;
        } else {
            require_once APP_PATH . "/Views/pages/home.php";
        }

        // Render modals and footer layout
        require_once APP_PATH . '/Views/layouts/modals.php';
        require_once APP_PATH . '/Views/layouts/footer.php';
    }

    public static function renderProductDetail($skuId) {
        $sku = ProductModel::getSkuById($skuId);
        if (!$sku) {
            header("Location: /products");
            exit();
        }

        $seriesSlug = $sku['seriesSlug'] ?? null;
        $categorySlug = $sku['categorySlug'] ?? null;
        $seriesInfo = $seriesSlug ? ProductModel::getSeriesBySlug($seriesSlug) : null;
        $categoryInfo = $categorySlug ? ProductModel::getCategoryBySlug($categorySlug) : null;

        $company = ProductModel::getCompanyInfo();
        $categories = ProductModel::getCategories();
        $activePage = 'products';

        require_once APP_PATH . '/Views/layouts/header.php';
        require_once APP_PATH . '/Views/pages/product_detail.php';
        require_once APP_PATH . '/Views/layouts/modals.php';
        require_once APP_PATH . '/Views/layouts/footer.php';
    }
}
