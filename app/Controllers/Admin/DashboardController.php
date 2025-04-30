<?php
namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Middleware\Admin;
use App\Models\ProductModel;
use App\Models\OrderModel;

/**
 * DashboardController
 * Fournit les statistiques globales pour l'administration (via JSON).
 * Données affichées dans le dashboard admin (total produits, catégories, fournisseurs, ventes, etc).
 */
class DashboardController extends BaseController
{
    private ProductModel $productModel;
    private OrderModel $orderModel;

    public function __construct()
    {
        parent::__construct();
        Admin::handle(); // Vérifie que l'utilisateur est un admin connecté
        $this->productModel = new ProductModel($this->pdo);
        $this->orderModel   = new OrderModel($this->pdo);
    }

    /**
     * GET /api/admin/dashboard
     * Renvoie un objet JSON avec les statistiques générales et mensuelles :
     * - total_products : nombre total de produits
     * - total_categories : nombre total de catégories
     * - total_suppliers : nombre total de fournisseurs
     * - total_orders : nombre total de commandes
     * - total_sales : somme totale des ventes (euros)
     * - monthly_sales : tableau [ ['mois', total], ... ]
     */
    public function getStatsJson()
    {
        header('Content-Type: application/json');

        try {
            echo json_encode([
                'total_products'    => $this->productModel->countAll(),
                'total_categories'  => $this->productModel->countCategories(),
                'total_suppliers'   => $this->productModel->countSuppliers(),
                'total_orders'      => $this->orderModel->countAll(),
                'total_sales'       => $this->orderModel->sumTotalSales(),
                'monthly_sales'     => $this->orderModel->getMonthlySales()
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Erreur serveur dans DashboardController',
                'details' => $e->getMessage()
            ]);
        }
    }
}
