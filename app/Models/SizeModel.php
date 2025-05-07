<?php

namespace App\Models;

use App\Core\BaseModel;
use PDO;

class SizeModel extends BaseModel
{
    protected string $table = 'sizes';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }
    public function getAll(): array
    {
        $sql = "SELECT * FROM sizes";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByProductId(int $productId): array
    {
        $sql = "SELECT size_label, stock_qty 
                FROM sizes 
                WHERE product_id = :product_id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['product_id' => $productId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
