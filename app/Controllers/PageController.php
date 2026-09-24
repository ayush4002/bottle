<?php
// ==========================================================================
// TRUENORTH GROUP — PAGE CONTROLLER
// Handles page rendering & view assembly for seamless single-page & multi-route navigation
// ==========================================================================

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__) . '/Models/ProductModel.php';

class PageController {

    public static function renderPage($pageName = 'home') {
        $allowedPages = ['home', 'products', 'about', 'contact', 'custom', 'sustainability', 'market'];
        
        if (!in_array($pageName, $allowedPages)) {
            $pageName = 'home';
        }

        $company = ProductModel::getCompanyInfo();
        $categories = ProductModel::getCategories();
        $series = ProductModel::getSeries();
        $skus = ProductModel::getSkus();

        // Pass initial active page to views
        $activePage = $pageName;

        // Render header layout
        require_once APP_PATH . '/Views/layouts/header.php';
        
        // Render ALL view sections so client-side SPA router & hash navigation work 100% seamlessly
        $views = ['home', 'products', 'about', 'custom', 'sustainability', 'contact'];
        foreach ($views as $view) {
            $file = APP_PATH . "/Views/pages/{$view}.php";
            if (file_exists($file)) {
                require_once $file;
            }
        }

        // Render modals and footer layout
        require_once APP_PATH . '/Views/layouts/modals.php';
        require_once APP_PATH . '/Views/layouts/footer.php';
    }
}
