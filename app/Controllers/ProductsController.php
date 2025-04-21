<?php
// app/Controllers/ProductsController.php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\ProductModel;

/**
 * ProductsController
 *
 * - Interagit avec ProductModel
 * - Gère les vues (index, view) en HTML
 * - Fournit une API JSON (getAllJson, getOneJson) avec pagination, tri, etc.
 */
class ProductsController extends BaseController
{
    private $productModel;

    public function __construct()
    {
        file_put_contents(__DIR__ . '/../../www/debug.log', "🔧 ProductsController instancié\n", FILE_APPEND);

        parent::__construct();
        $this->productModel = new ProductModel($this->pdo);
    }

    /**
     * Affichage HTML (MVC traditionnel)
     * /products?page=...
     */
    public function index()
    {
        $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
        $perPage = 12;

        $orderBy = $_GET['orderBy'] ?? 'p.created_at';
        $direction = $_GET['direction'] ?? 'DESC';

        // getDetailedProducts() minimaliste, toi tu peux adapter
        $products = $this->productModel->getDetailedProducts([
            'orderBy' => $orderBy,
            'direction' => $direction,
            'limit' => $perPage,
            'offset' => ($page - 1) * $perPage
        ]);

        // totalCount
        $totalCount = $this->productModel->countAll();
        $totalPages = ceil($totalCount / $perPage);

        $this->render('products/index', [
            'pageTitle' => 'Nos produits - ' . SITE_NAME,
            'products' => $products,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'orderBy' => $orderBy,
            'direction' => $direction
        ]);
    }

    /**
     * API JSON (GET /api/products)
     * Renvoyer un objet JSON avec:
     * {
     *   "products": [...],
     *   "totalPages": int,
     *   "currentPage": int
     * }
     */
    public function getAllJson(): void
    {
        header('Content-Type: application/json');
    
        file_put_contents('/home/amarnac/www/debug.log', "[getAllJson] Début fonction\n", FILE_APPEND);

        try {
            // Log d'entrée
            file_put_contents(__DIR__ . '/../../www/debug.log', "➡️ getAllJson() triggered\n", FILE_APPEND);
    
            // 1) Récup params
            $category = $_GET['category'] ?? '';
            $color    = $_GET['color']    ?? '';
            $fabric   = $_GET['fabric']   ?? '';
            $size     = $_GET['size']     ?? '';
            $region   = $_GET['region']   ?? '';
    
            $orderBy   = $_GET['orderBy']   ?? 'created_at';
            $direction = $_GET['direction'] ?? 'DESC';
    
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $limit = 12;
            $offset = ($page - 1) * $limit;
    
            // 2) Construire les filtres
            $filters = [];
            if (!empty($category)) $filters['category_id'] = (int)$category;
            if (!empty($color))    $filters['color_id'] = (int)$color;
            if (!empty($fabric))   $filters['fabric_id'] = (int)$fabric;
            if (!empty($region))   $filters['cultural_region_id'] = (int)$region;
            if (!empty($size))     $filters['size_label'] = $size;
    
            // 3) Requête
            $products = $this->productModel->getDetailedProducts(
                $filters,
                $orderBy,
                $direction,
                $limit,
                $offset
            );
    
            $totalCount = $this->productModel->countFiltered($filters);
            $totalPages = ceil($totalCount / $limit);
    
            // 4) Réponse JSON
            echo json_encode([
                'products'    => $products,
                'totalPages'  => $totalPages,
                'currentPage' => $page,
            ]);
    
            // Log de fin
            file_put_contents(__DIR__ . '/../../www/debug.log', "✅ getAllJson terminé sans erreur\n", FILE_APPEND);
        } catch (\Throwable $e) {
            http_response_code(500);
            file_put_contents(__DIR__ . '/../../www/debug.log', "❌ Erreur dans getAllJson: " . $e->getMessage() . "\n", FILE_APPEND);
            echo json_encode([
                'error' => 'Server error in getAllJson',
                'details' => $e->getMessage()
            ]);
        }
    }
    

    /**
     * API JSON (GET /api/products/{id})
     * Renvoyer un produit unique en JSON
     */
    public function getOneJson(int $id): void
    {
        header('Content-Type: application/json');

        $product = $this->productModel->getDetailedProductById($id);
        if ($product) {
            echo json_encode($product);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
        }
    }
}
