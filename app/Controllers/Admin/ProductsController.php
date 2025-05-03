<?php
// app/Controllers/Admin/ProductsController.php

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Models\ProductModel;
use App\Middleware\Admin;
use App\Models\ProductImageModel;

class ProductsController extends BaseController
{
    private ProductModel $productModel;
    private ProductImageModel $imageModel;

    public function __construct()
    {
        parent::__construct();
        Admin::handle();

        $this->productModel = new ProductModel($this->pdo);
        $this->imageModel = new ProductImageModel($this->pdo);
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
            } catch (\Throwable $e) {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'Image invalide : ' . $e->getMessage()]);
                return;
            }

            echo json_encode(['success' => true, 'message' => 'Produit créé avec succès.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur lors de la création.']);
        }
    }

    public function updateJson($id)
    {
        header('Content-Type: application/json');
    
        // Compatibilité avec FormData POST + _method=PUT
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
            } catch (\Throwable $e) {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'Image invalide : ' . $e->getMessage()]);
                return;
            }
    
            echo json_encode(['success' => true, 'message' => 'Produit modifié avec succès.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur lors de la modification.']);
        }
    }    

    public function deleteJson($id)
    {
        header('Content-Type: application/json');

        $product = $this->productModel->getDetailedProductById($id);

        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Produit non trouvé.']);
            return;
        }

        if ($this->productModel->delete($id)) {
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
