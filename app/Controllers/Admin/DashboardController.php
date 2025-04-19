<?php
// app/Controllers/Admin/DashboardController.php

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Middleware\Admin;
use App\Models\ProductModel;

class DashboardController extends BaseController
{
    private ProductModel $productModel;

    public function __construct()
    {
        parent::__construct();
        Admin::handle(); // vérifie que l'utilisateur est admin
        $this->productModel = new ProductModel($this->pdo);
    }

    /**
     * Endpoint API dynamique (AJAX)
     */
    public function getStatsJson()
    {
        header('Content-Type: application/json');

        echo json_encode([
            'total_products' => $this->productModel->countAll(),
            'total_categories' => $this->count('categories'),
            'total_suppliers' => $this->count('suppliers'),
            'total_sales' => $this->countSales()
        ]);
    }

    private function count(string $table): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM {$table}");
        return (int) $stmt->fetchColumn();
    }

    private function countSales(): int
    {
        $stmt = $this->pdo->query("SELECT SUM(quantity) FROM order_items");
        return (int) $stmt->fetchColumn();
    }
}
