<?php
// app/Controllers/Admin/ProductsController.php

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Models\ProductModel;
use App\Middleware\Admin;
use App\Models\ProductImageModel;
use App\Models\SizeModel;

class ProductsController extends BaseController
{
    private ProductModel $productModel;
    private ProductImageModel $imageModel;
    private SizeModel $sizeModel;

    public function __construct()
    {
        parent::__construct();
        Admin::handle();

        $this->productModel = new ProductModel($this->pdo);
        $this->imageModel = new ProductImageModel($this->pdo);
        $this->sizeModel = new SizeModel($this->pdo);
    }

    private function validateProductForm(): array
    {
        // Compatibilité PUT : lecture manuelle si vide (cas PUT natif ou _method=PUT avec FormData)
        if ($_SERVER['REQUEST_METHOD'] === 'PUT' && empty($_POST)) {
            parse_str(file_get_contents("php://input"), $_PUT);
            $_POST = $_PUT;
        }
    
        $name     = sanitize($_POST['name'] ?? '');
        $short    = sanitize($_POST['short_description'] ?? '');
        $desc     = sanitize($_POST['description'] ?? '');
        $price    = floatval($_POST['price'] ?? 0);
        $stock    = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
        $category = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
        $color    = !empty($_POST['color_id']) ? intval($_POST['color_id']) : null;
        $fabric   = !empty($_POST['fabric_id']) ? intval($_POST['fabric_id']) : null;
        $region   = !empty($_POST['cultural_region_id']) ? intval($_POST['cultural_region_id']) : null;
        $supplier = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
    
        $errors = [];
    
        if (!$name) $errors[] = 'Le nom est requis';
        if ($price <= 0) $errors[] = 'Le prix doit être supérieur à 0';
        if ($stock < 0) $errors[] = 'Le stock ne peut pas être négatif';
        if (!$category) $errors[] = 'La catégorie est requise';
    
        return [
            'errors' => $errors,
            'data'   => compact('name', 'short', 'desc', 'price', 'stock', 'category', 'color', 'fabric', 'region', 'supplier')
        ];
    }
     

    private function handleMainImageUpload(int $productId): void
    {
        if (
            isset($_FILES['main_image']) &&
            is_uploaded_file($_FILES['main_image']['tmp_name']) &&
            $_FILES['main_image']['error'] === UPLOAD_ERR_OK
        ) {
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $maxSize = 2 * 1024 * 1024;

            if ($_FILES['main_image']['size'] > $maxSize) {
                throw new \Exception('Image trop grande (max 2 MB)');
            }

            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $_FILES['main_image']['tmp_name']);
            finfo_close($fileInfo);

            if (!in_array($mimeType, $allowedMimeTypes)) {
                throw new \Exception('Type de fichier non autorisé (jpg, png, webp)');
            }

            $uploadDir = PUBLIC_PATH . '/uploads/products/' . $productId;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $ext = match ($mimeType) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'jpg'
            };

            $filename = 'img_' . time() . '.' . $ext;
            $targetPath = $uploadDir . '/' . $filename;

            if (move_uploaded_file($_FILES['main_image']['tmp_name'], $targetPath)) {
                $this->imageModel->setMainImage($productId, $filename);
            } else {
                throw new \Exception('Erreur lors du téléchargement de l’image.');
            }
        }
    }


    /**
     * Création d'un produit + tailles associées
     */
    public function storeJson()
    {
        header('Content-Type: application/json');
    
        $validation = $this->validateProductForm();
    
        if (!empty($validation['errors'])) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => implode(', ', $validation['errors'])]);
            return;
        }
    
        $data = $validation['data'];
        
        // ✅ On récupère correctement les tailles
        $sizes = json_decode($_POST['sizes'], true);
    
        // Vérification si le décodage a réussi
        if ($sizes === null) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Le format des tailles est invalide.']);
            return;
        }
    
        // ✅ Création du produit
        $id = $this->productModel->create([
            'name' => $data['name'],
            'short_description' => $data['short'],
            'description' => $data['desc'],
            'price' => $data['price'],
            'stock' => $data['stock'],
            'category_id' => $data['category'],
            'color_id' => $data['color'],
            'fabric_id' => $data['fabric'],
            'cultural_region_id' => $data['region'],
            'supplier_id' => $data['supplier']
        ]);
    
        if ($id) {
            try {
                $this->handleMainImageUpload($id);
    
                // ✅ Insertion des tailles dans la base
                if (!empty($sizes)) {
                    foreach ($sizes as $size) {
                        if (isset($size['size_label']) && isset($size['stock_qty'])) {
                            $this->sizeModel->createSize($id, $size['size_label'], $size['stock_qty']);
                        } else {
                            throw new \Exception("Les tailles sont mal formatées.");
                        }
                    }
                }
            } catch (\Throwable $e) {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout : ' . $e->getMessage()]);
                return;
            }
    
            echo json_encode(['success' => true, 'message' => 'Produit créé avec succès.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur lors de la création.']);
        }
    }
    
    /**
     * Mise à jour d'un produit + tailles associées
     */
    public function updateJson($id)
    {
        header('Content-Type: application/json');
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_method'] ?? '') === 'PUT') {
            $_SERVER['REQUEST_METHOD'] = 'PUT';
        }
    
        $product = $this->productModel->getDetailedProductById($id);
    
        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Produit non trouvé.']);
            return;
        }
    
        $validation = $this->validateProductForm();
    
        if (!empty($validation['errors'])) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => implode(', ', $validation['errors'])]);
            return;
        }
    
        $data = $validation['data'];
        $sizes = json_decode($_POST['sizes'], true);
    
        $updated = $this->productModel->update($id, [
            'name' => $data['name'],
            'short_description' => $data['short'],
            'description' => $data['desc'],
            'price' => $data['price'],
            'stock' => $data['stock'],
            'category_id' => $data['category'],
            'color_id' => $data['color'],
            'fabric_id' => $data['fabric'],
            'cultural_region_id' => $data['region'],
            'supplier_id' => $data['supplier']
        ]);
    
        if ($updated) {
            try {
                $this->handleMainImageUpload($id);
    
                // ✅ Mise à jour des tailles via le SizeModel
                if (!empty($sizes)) {
                    $formattedSizes = array_map(function ($size) {
                        return [
                            'size_label' => $size['size_label'],
                            'stock_qty' => $size['stock_qty']
                        ];
                    }, $sizes);
    
                    $this->sizeModel->updateSizes($id, $formattedSizes);
                }
            } catch (\Throwable $e) {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()]);
                return;
            }
    
            echo json_encode(['success' => true, 'message' => 'Produit modifié avec succès.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur lors de la modification.']);
        }
    }
    

    /**
     * Suppression d'un produit + ses tailles associées
     */
    public function deleteJson($id)
    {
        header('Content-Type: application/json');
    
        // Vérification si le produit existe
        $product = $this->productModel->getDetailedProductById($id);
    
        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Produit non trouvé.']);
            return;
        }
    
        // ➡️ Appel au modèle pour supprimer le produit et ses dépendances
        if ($this->productModel->deleteWithDependencies($id)) {
            // ➡️ Suppression des fichiers uploadés
            $uploadDir = PUBLIC_PATH . '/uploads/products/' . $id;
            if (is_dir($uploadDir)) {
                $files = glob($uploadDir . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) unlink($file);
                }
                rmdir($uploadDir);
            }
    
            echo json_encode(['success' => true, 'message' => 'Produit supprimé.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur lors de la suppression.']);
        }
    }
    
    
}
