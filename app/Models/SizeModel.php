<?php

namespace App\Models;

use App\Core\BaseModel;
use PDO;

class SizeModel extends BaseModel
{
    protected string $table = 'sizes';
    protected string $primaryKey = 'size_id';

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
    
    public function deleteByProductId(int $productId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM sizes WHERE product_id = ?");
        return $stmt->execute([$productId]);
    }

    public function createSize(int $productId, string $size, int $stockQty): bool
    {
        $stmt = $this->pdo->prepare("INSERT INTO sizes (product_id, size_label, stock_qty) VALUES (?, ?, ?)");
        return $stmt->execute([$productId, $size, $stockQty]);
    }

    public function updateSizes(int $productId, array $sizes): bool
    {
        $this->deleteByProductId($productId);
    
        foreach ($sizes as $size) {
            $this->createSize($productId, $size['size_label'], $size['stock_qty']);
        }
    
        return true;
    }
    

}
