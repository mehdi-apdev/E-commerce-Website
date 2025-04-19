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
        // Latest products
        $latestProducts = $this->productModel->getDetailedProducts([
            'orderBy' => 'p.created_at',
            'direction' => 'DESC',
            'limit' => 5
        ]);

        // Most sold products (assumes we have LEFT JOIN + sales_count calculated in the model)
        $topSellingProducts = $this->productModel->getDetailedProducts([
            'orderBy' => 'sales_count',
            'direction' => 'DESC',
            'limit' => 5,
            'includeSales' => true
        ]);

        $this->render('home/index', [
            'latestProducts' => $latestProducts,
            'topSellingProducts' => $topSellingProducts,
            'pageTitle' => 'Accueil - ' . SITE_NAME
        ]);
    }
}
