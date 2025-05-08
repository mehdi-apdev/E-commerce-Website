<?php
// app/Models/OrderModel.php

namespace App\Models;

use App\Core\BaseModel;
use PDO;

class OrderModel extends BaseModel
{
    protected string $table = 'orders';
    protected string $primaryKey = 'order_id';

    public function countAll(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM orders");
        return (int)$stmt->fetchColumn();
    }

    public function sumTotalSales(): float
    {
        $stmt = $this->pdo->query("SELECT SUM(total_amount) FROM orders");
        return (float)$stmt->fetchColumn();
    }

    public function getMonthlySales(): array
    {
        $stmt = $this->pdo->query("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') AS month,
                SUM(total_amount) AS total
            FROM orders
            GROUP BY month
            ORDER BY month ASC
        ");
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

       /**
     * Récupère les commandes d'un utilisateur avec les détails associés.
     */
    public function getOrdersByUserId(int $userId): array
    {
        $sql = "
            SELECT 
                o.order_id,
                o.created_at,
                o.total_amount,
                o.status,
                p.product_id,              -- ✅ On récupère explicitement le product_id
                p.name AS product_name,
                p.price,
                oi.quantity,
                s.size_label,
                pi.filename AS product_image
            FROM orders o
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            LEFT JOIN products p ON oi.product_id = p.product_id
            LEFT JOIN sizes s ON oi.size_id = s.size_id
            LEFT JOIN product_images pi ON pi.product_id = p.product_id AND pi.is_main = 1
            WHERE o.user_id = :user_id
            ORDER BY o.created_at DESC
        ";
    
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        $groupedOrders = [];
    
        foreach ($orders as $order) {
            $orderId = $order['order_id'];
    
            // 🔄 Initialiser le groupe s'il n'existe pas encore
            if (!isset($groupedOrders[$orderId])) {
                $groupedOrders[$orderId] = [
                    'order_id' => $orderId,
                    'created_at' => $order['created_at'],
                    'total_amount' => $order['total_amount'],
                    'status' => $order['status'],
                    'items' => []
                ];
            }
    
            // 🔎 Vérification de l'image, sinon image par défaut
            $productImage = $order['product_image'] ?? 'default.png';
    
            // ✅ Ajout du product_id dans le tableau d'items
            $groupedOrders[$orderId]['items'][] = [
                'product_id' => $order['product_id'],
                'product_name' => $order['product_name'],
                'price' => $order['price'],
                'quantity' => $order['quantity'],
                'size_label' => $order['size_label'],
                'product_image' => $productImage
            ];
        }
    
        return array_values($groupedOrders);
    }
    
    
}
