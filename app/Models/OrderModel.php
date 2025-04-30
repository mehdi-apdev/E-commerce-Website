<?php
// app/Models/OrderModel.php

namespace App\Models;

use App\Core\BaseModel;
use PDO;

class OrderModel extends BaseModel
{
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
    
}
