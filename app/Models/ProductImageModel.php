<?php
// app/Models/ProductImageModel.php
namespace App\Models;

use App\Core\BaseModel;
use PDO;

class ProductImageModel extends BaseModel   
{

    public function __construct(PDO $pdo) {
        parent::__construct($pdo);
    }

    public function setMainImage(int $productId, string $filename): void
    {
        // Retirer l’ancien main
        $this->pdo->prepare("UPDATE product_images SET is_main = 0 WHERE product_id = :id AND is_main = 1")
                  ->execute(['id' => $productId]);

        // Insérer la nouvelle image principale
        $stmt = $this->pdo->prepare("INSERT INTO product_images (product_id, filename, is_main) VALUES (:product_id, :filename, 1)");
        $stmt->execute([
            'product_id' => $productId,
            'filename' => $filename
        ]);
    }

    public function getMainImageFilename(int $productId): ?string
    {
        $stmt = $this->pdo->prepare("SELECT filename FROM product_images WHERE product_id = :id AND is_main = 1 LIMIT 1");
        $stmt->execute(['id' => $productId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['filename'] ?? null;
    }


    // ajouter :
    // - getAllImagesForProduct()
    // - deleteImage()
    // - reorderImages()
}
