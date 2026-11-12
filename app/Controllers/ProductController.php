<?php
// ==========================================================================
// TRUENORTH GROUP — PRODUCT CONTROLLER
// Handles RESTful API requests for categories, series, SKUs, and search
// ==========================================================================

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__) . '/Models/ProductModel.php';

if (!function_exists('tn_json')) {
    /** JSON for API responses, with HTML-significant characters escaped. */
    function tn_json($data) {
        return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}

class ProductController {

    public static function handleProductsApi() {
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Origin: *");

        $action = $_GET['action'] ?? 'all';
        $categorySlug = $_GET['category'] ?? null;
        $seriesSlug = $_GET['series'] ?? null;
        $skuId = $_GET['id'] ?? null;

        if ($skuId) {
            $sku = ProductModel::getPublishedSkuById($skuId);
            if ($sku) {
                echo tn_json(["success" => true, "data" => $sku]);
            } else {
                http_response_code(404);
                echo tn_json(["success" => false, "message" => "SKU not found"]);
            }
            exit();
        }

        if ($seriesSlug) {
            $skus = ProductModel::getSkusBySeries($seriesSlug);
            $seriesInfo = ProductModel::getSeriesBySlug($seriesSlug);
            echo tn_json(["success" => true, "series" => $seriesInfo, "skus" => $skus]);
            exit();
        }

        if ($categorySlug) {
            $seriesList = ProductModel::getSeriesByCategory($categorySlug);
            $skus = ProductModel::getSkusByCategory($categorySlug);
            $categoryInfo = ProductModel::getCategoryBySlug($categorySlug);
            echo tn_json(["success" => true, "category" => $categoryInfo, "series" => $seriesList, "skus" => $skus]);
            exit();
        }

        echo tn_json([
            "success" => true,
            "categories" => ProductModel::getCategories(),
            "series" => ProductModel::getSeries(),
            "skus" => ProductModel::getSkus(),
            "skusCount" => count(ProductModel::getSkus())
        ]);
        exit();
    }

    public static function handleSearchApi() {
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Origin: *");

        $query = trim($_GET['q'] ?? '');
        $taxMatch = !empty($query) ? ProductModel::getSearchTaxonomyMatch($query) : null;
        $popular = ProductModel::getPopularSearches();

        if (empty($query)) {
            echo tn_json([
                "success" => true,
                "query" => "",
                "count" => 0,
                "popular" => $popular,
                "results" => []
            ]);
            exit();
        }

        $results = ProductModel::searchProducts($query);

        echo tn_json([
            "success" => true,
            "query" => $query,
            "taxonomyMatch" => $taxMatch,
            "count" => count($results),
            "popular" => $popular,
            "results" => array_slice($results, 0, 20) // Top 20 search results
        ]);
        exit();
    }
}
