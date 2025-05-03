<?php
// app/Models/ProductModel.php
namespace App\Models;

use App\Core\BaseModel;
use PDO;

/**
 * ProductModel
 *
 * Handles operations related to products.
 * Inherits common CRUD operations from BaseModel.
 */
class ProductModel extends BaseModel {
    protected string $table = 'products';
    protected string $primaryKey = 'product_id';

    public function __construct(PDO $pdo) {
        parent::__construct($pdo);
    }

    public function getDetailedProducts(
        array $filters = [],
        string $orderBy = 'created_at',
        string $direction = 'DESC',
        ?int $limit = null,
        ?int $offset = null
    ): array {
        // Champs autorisés pour le tri
        $allowedFields = ['created_at','price','name','sales_count'];
        if (!in_array($orderBy, $allowedFields)) {
            $orderBy = 'created_at';
        }
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
    
        // Requête SQL
        $sql = "
          SELECT
            p.*,
            c.name AS category_name,
            col.name AS color_name,
            f.name AS fabric_name,
            r.name AS region_name,
            (
              SELECT SUM(oi.quantity)
              FROM order_items oi
              WHERE oi.product_id = p.product_id
            ) AS sales_count,
            (
              SELECT filename
              FROM product_images
              WHERE product_id = p.product_id
                AND is_main = 1
              LIMIT 1
            ) AS main_image
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.category_id
          LEFT JOIN colors col ON p.color_id = col.color_id
          LEFT JOIN fabrics f ON p.fabric_id = f.fabric_id
          LEFT JOIN cultural_regions r ON p.cultural_region_id = r.region_id
          WHERE 1=1
        ";
    
        // On prépare un tableau de paramètres pour le bind
        $params = [];
    
        // 1) Filtres
        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }
        if (!empty($filters['color_id'])) {
            $sql .= " AND p.color_id = :color_id";
            $params['color_id'] = $filters['color_id'];
        }
        if (!empty($filters['fabric_id'])) {
            $sql .= " AND p.fabric_id = :fabric_id";
            $params['fabric_id'] = $filters['fabric_id'];
        }
        if (!empty($filters['cultural_region_id'])) {
            $sql .= " AND p.cultural_region_id = :region";
            $params['region'] = $filters['cultural_region_id'];
        }
        // Exemple si tu gères la taille dans une table `sizes` : tu ferais un JOIN ou un WHERE
        // if (!empty($filters['size_label'])) { ... }
    
        // 2) Tri
        $sql .= " ORDER BY p.$orderBy $direction";
    
        // 3) Pagination
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
    
        // Préparation
        $stmt = $this->pdo->prepare($sql);
    
        // Bind des filtres
        foreach ($params as $key => $val) {
            $paramType = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(":$key", $val, $paramType);
        }
        // Bind pagination
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }
    
        // Exécution
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Compte le nombre total de produits correspondant aux filtres, 
     * pour calculer la pagination (totalPages).
     */
    public function countFiltered(array $filters): int
    {
        $sql = "
          SELECT COUNT(*) 
          FROM products p
          WHERE 1=1
        ";
        $params = [];
    
        // Filtres identiques
        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }
        if (!empty($filters['color_id'])) {
            $sql .= " AND p.color_id = :color_id";
            $params['color_id'] = $filters['color_id'];
        }
        if (!empty($filters['fabric_id'])) {
            $sql .= " AND p.fabric_id = :fabric_id";
            $params['fabric_id'] = $filters['fabric_id'];
        }
        if (!empty($filters['cultural_region_id'])) {
            $sql .= " AND p.cultural_region_id = :region";
            $params['region'] = $filters['cultural_region_id'];
        }
        // Idem pour la taille etc.
    
        // Préparation
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $paramType = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(":$key", $val, $paramType);
        }
    
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
    

    public function getDetailedProductById(int $id): ?array
    {
        $sql = "SELECT 
                    p.*, 
                    c.name AS category_name, 
                    col.name AS color_name,
                    f.name AS fabric_name,
                    r.name AS region_name,
                    s.name AS supplier_name,
                    (
                        SELECT filename 
                        FROM product_images 
                        WHERE product_id = p.product_id AND is_main = 1 
                        LIMIT 1
                    ) AS main_image
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id
                LEFT JOIN colors col ON p.color_id = col.color_id
                LEFT JOIN fabrics f ON p.fabric_id = f.fabric_id
                LEFT JOIN cultural_regions r ON p.cultural_region_id = r.region_id
                LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
                WHERE p.product_id = :id
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        return $product ?: null;
    }   

    public function countAll(): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        return (int) $this->pdo->query($sql)->fetchColumn();
    }

    public function getValidProduct($productId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    public function decrementStock($productId, $qty): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE products
            SET stock = stock - :qty
            WHERE product_id = :id AND stock >= :qty
        ");
        $stmt->execute([
            'id' => $productId,
            'qty' => $qty
        ]);
        return $stmt->rowCount() > 0;
    }
    


    public function getCategories(): array {
        $stmt = $this->pdo->query("SELECT * FROM categories ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getColors(): array {
        $stmt = $this->pdo->query("SELECT * FROM colors ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getFabrics(): array {
        $stmt = $this->pdo->query("SELECT * FROM fabrics ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getCulturalRegions(): array {
        $stmt = $this->pdo->query("SELECT * FROM cultural_regions ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getSuppliers(): array {
        $stmt = $this->pdo->query("SELECT * FROM suppliers ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    

    public function countCategories(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM categories");
        return (int) $stmt->fetchColumn();
    }
    
    public function countSuppliers(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM suppliers");
        return (int) $stmt->fetchColumn();
    }
    
}
