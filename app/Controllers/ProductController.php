<?php
// ==========================================================================
// TRUENORTH GROUP — PRODUCT CONTROLLER
// Handles RESTful API requests for categories, series, SKUs, and search
// ==========================================================================

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__) . '/Models/ProductModel.php';

class ProductController {

    public static function handleProductsApi() {
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Origin: *");

        $action = $_GET['action'] ?? 'all';
        $categorySlug = $_GET['category'] ?? null;
        $seriesSlug = $_GET['series'] ?? null;
        $skuId = $_GET['id'] ?? null;

        if ($skuId) {
            $sku = ProductModel::getSkuById($skuId);
            if ($sku) {
                echo json_encode(["success" => true, "data" => $sku]);
            } else {
                http_response_code(404);
                echo json_encode(["success" => false, "message" => "SKU not found"]);
            }
            exit();
        }

        if ($seriesSlug) {
            $skus = ProductModel::getSkusBySeries($seriesSlug);
            $seriesInfo = ProductModel::getSeriesBySlug($seriesSlug);
            echo json_encode(["success" => true, "series" => $seriesInfo, "skus" => $skus]);
            exit();
        }

        if ($categorySlug) {
            $seriesList = ProductModel::getSeriesByCategory($categorySlug);
            $skus = ProductModel::getSkusByCategory($categorySlug);
            $categoryInfo = ProductModel::getCategoryBySlug($categorySlug);
            echo json_encode(["success" => true, "category" => $categoryInfo, "series" => $seriesList, "skus" => $skus]);
            exit();
        }

        echo json_encode([
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

        $query = $_GET['q'] ?? '';
        $results = ProductModel::searchProducts($query);

        echo json_encode([
            "success" => true,
            "query" => $query,
            "count" => count($results),
            "results" => array_slice($results, 0, 20) // Limit to top 20 search results
        ]);
        exit();
    }
}
