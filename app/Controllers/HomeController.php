<?php
// app/Controllers/HomeController.php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\ProductModel;

class HomeController extends BaseController
{
    private $productModel;

    public function __construct()
    {
        parent::__construct(); 
        $this->productModel = new ProductModel($this->pdo);
    }

    public function index()
    {
        // Récupère les produits récents
        $latestProducts = $this->productModel->getDetailedProducts([
            'orderBy' => 'p.created_at',
            'direction' => 'DESC',
            'limit' => 5
        ]);

        // Récupère les produits les plus vendus
        $topSellingProducts = $this->productModel->getDetailedProducts([
            'orderBy' => 'sales_count',
            'direction' => 'DESC',
            'limit' => 5,
            'includeSales' => true
        ]);

        // Répond en JSON
        header('Content-Type: application/json');
        echo json_encode([
            'latestProducts' => $latestProducts,
            'topSellingProducts' => $topSellingProducts
        ]);
    }
}
