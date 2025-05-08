<?php
// app/Models/ProductModel.php
namespace App\Models;

use App\Core\BaseModel;
use PDOException;
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
        ?int $offset = null,
        ?string $sizeLabel = null
    ): array {
        $allowedFields = ['created_at', 'price', 'name', 'sales_count'];
        if (!in_array($orderBy, $allowedFields)) {
            $orderBy = 'created_at';
        }
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
    
        $sql = "
            SELECT DISTINCT p.*, 
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
            LEFT JOIN sizes s ON s.product_id = p.product_id
            WHERE 1=1
        ";
    
        $params = [];
    
        // ✅ Application des filtres
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
    
        // ✅ Ajout du filtre de taille
        if (!empty($sizeLabel)) {
            $sql .= " AND s.size_label = :size_label";
            $params['size_label'] = $sizeLabel;
        }
    
        $sql .= " ORDER BY p.$orderBy $direction";
    
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
    
        // ✅ Préparation de la requête
        $stmt = $this->pdo->prepare($sql);
    
        // ✅ Bind des filtres
        foreach ($params as $key => $val) {
            $paramType = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(":$key", $val, $paramType);
        }
    
        // ✅ Bind pagination
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }
    
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
        // 🔎 Filtrage par taille
        if (!empty($filters['size_label'])) {
            $sql .= " AND s.size_label = :size_label";
            $params['size_label'] = $filters['size_label'];
        }

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
    
        // ✅ Récupération de toutes les images associées
        if ($product) {
            $imageSql = "SELECT filename, is_main FROM product_images WHERE product_id = :id";
            $imageStmt = $this->pdo->prepare($imageSql);
            $imageStmt->execute(['id' => $id]);
            $product['images'] = $imageStmt->fetchAll(PDO::FETCH_ASSOC);
    
            // ✅ Récupération des tailles disponibles
            $sizeSql = "SELECT size_label, size_description, stock_qty 
                        FROM sizes 
                        WHERE product_id = :id AND stock_qty > 0";
            $sizeStmt = $this->pdo->prepare($sizeSql);
            $sizeStmt->execute(['id' => $id]);
            $product['sizes'] = $sizeStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    
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
    
    public function decrementStock($productId, $sizeId, $qty): bool
    {
        // Vérifier d'abord s'il y a suffisamment de stock pour la taille demandée
        $stmt = $this->pdo->prepare("
            UPDATE sizes
            SET stock_qty = stock_qty - :qty
            WHERE product_id = :product_id AND size_id = :size_id AND stock_qty >= :qty
        ");
        $stmt->execute([
            'product_id' => $productId,
            'size_id' => $sizeId,
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
/**
 * Supprime le produit ainsi que toutes ses dépendances
 */
public function deleteWithDependencies(int $productId): bool
{
    // ⚠️ Utiliser le bon PDO, c'est $this->pdo et non $this->db
    try {
        // ➡️ Début de la transaction SQL
        $this->pdo->beginTransaction();

        // ➡️ Suppression des tables dépendantes (ordre important)
        $this->deleteFromTable('order_items', $productId);       // Lié aux commandes
        $this->deleteFromTable('product_images', $productId);     // Images du produit
        $this->deleteFromTable('shopping_cart', $productId);      // Panier
        $this->deleteFromTable('wishlists', $productId);          // Liste de souhaits
        $this->deleteFromTable('inventory_logs', $productId);     // Logs d'inventaire
        $this->deleteFromTable('sizes', $productId);              // Tailles
        $this->deleteFromTable('accessories', $productId);        // Accessoires
        $this->deleteFromTable('bottoms', $productId);            // Bas
        $this->deleteFromTable('tops', $productId);               // Hauts

        // ➡️ Suppression du produit principal
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$productId]);

        // ➡️ Validation de la transaction
        $this->pdo->commit();

        return true;
    } catch (PDOException $e) {
        // ➡️ Annulation de la transaction en cas d'erreur
        $this->pdo->rollBack();
        error_log("Erreur lors de la suppression du produit ID $productId : " . $e->getMessage());
        return false;
    }
}

/**
 * Supprime toutes les entrées liées au produit dans une table donnée
 */
private function deleteFromTable(string $table, int $productId): void
{
    $stmt = $this->pdo->prepare("DELETE FROM $table WHERE product_id = ?");
    $stmt->execute([$productId]);
}


}
