<?php
// app/Controllers/Admin/ProductsController.php

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Models\ProductModel;
use App\Middleware\Admin;
use App\Models\ProductImageModel;

/**
 * AdminProductController handles the product management in the admin panel.
 */
class ProductsController extends BaseController
{
    private ProductModel $productModel;
    private ProductImageModel $imageModel;

    public function __construct()
    {
        parent::__construct();

        // Checks if the user is admin via middleware
        Admin::handle();

        $this->productModel = new ProductModel($this->pdo);
        $this->imageModel = new ProductImageModel($this->pdo);
    }

    /**
     * Display the list of products in admin panel
     */
    public function index()
    {
        // Optional sorting parameters (default: creation date descending)
        $orderBy = $_GET['orderBy'] ?? 'p.created_at';
        $direction = $_GET['direction'] ?? 'DESC';

        // Call to the flexible method
        $products = $this->productModel->getDetailedProducts([
            'orderBy' => $orderBy,
            'direction' => $direction
        ]);

        $this->render('admin/products/index', [
            'pageTitle' => 'Gestion des produits - Admin',
            'products' => $products
        ]);
    }

    /**
     * Fetch all data needed to populate select dropdowns in the form
     *
     * @return array
     */
    private function getFormOptions(): array
    {
        return [
            'categories' => $this->productModel->getCategories(),
            'colors' => $this->productModel->getColors(),
            'fabrics' => $this->productModel->getFabrics(),
            'regions' => $this->productModel->getCulturalRegions(),
            'suppliers' => $this->productModel->getSuppliers(),
        ];
    }
    

    /**
     * Show form to create a product
     */
    public function create()
    {
        $options = $this->getFormOptions();
    
        $this->render('admin/products/form', [
            'pageTitle' => 'Ajouter un produit',
            'isEdit' => false,
            ...$options
        ]);
    }
    


    /**
     * Show form to edit a product
     */
    public function edit($id)
    {
        $product = $this->productModel->getDetailedProductById($id);
        ;
        if (!$product) {
            setFlashMessage('danger', 'Produit introuvable.');
            redirect('admin/products');
        }
    
        $image = $this->imageModel->getMainImageFilename($id);
        if (!empty($image)) {
            $product['main_image'] = $image;
        }        
    
        $options = $this->getFormOptions();
    
        $this->render('admin/products/form', [
            'pageTitle' => 'Modifier un produit',
            'product' => $product,
            'isEdit' => true,
            ...$options
        ]);
    }
    
    
    /**
     * Validate the product form data
     *
     * @return array An array containing validation errors and sanitized data
     */
    private function validateProductForm(): array {
        $name     = sanitize($_POST['name'] ?? '');
        $short    = sanitize($_POST['short_description'] ?? '');
        $desc     = sanitize($_POST['description'] ?? '');
        $price    = floatval($_POST['price'] ?? 0);
        $category = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
        $color    = !empty($_POST['color_id']) ? intval($_POST['color_id']) : null;
        $fabric   = !empty($_POST['fabric_id']) ? intval($_POST['fabric_id']) : null;
        $region   = !empty($_POST['cultural_region_id']) ? intval($_POST['cultural_region_id']) : null;
        $supplier = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
    
        $errors = [];
    
        if (!$name) $errors[] = 'Le nom est requis';
        if ($price <= 0) $errors[] = 'Le prix doit être supérieur à 0';
        if (!$category) $errors[] = 'La catégorie est requise';
    
        return [
            'errors' => $errors,
            'data'   => compact('name', 'short', 'desc', 'price', 'category', 'color', 'fabric', 'region', 'supplier')
        ];
    }
    
    /**
     * Handles the upload of a product's main image
     * 
     * @param int $productId The ID of the product to associate with the image
     * @return void
     */
    private function handleMainImageUpload(int $productId): void
    {
        if (
            isset($_FILES['main_image']) &&
            is_uploaded_file($_FILES['main_image']['tmp_name']) &&
            $_FILES['main_image']['error'] === UPLOAD_ERR_OK
        ) {
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $maxSize = 2 * 1024 * 1024; // 2 MB
    
            if ($_FILES['main_image']['size'] > $maxSize) {
                setFlashMessage('danger', 'Image trop grande (max 2 MB)');
                return;
            }
    
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $_FILES['main_image']['tmp_name']);
            finfo_close($fileInfo);
    
            if (!in_array($mimeType, $allowedMimeTypes)) {
                setFlashMessage('danger', 'Type de fichier non autorisé (jpg, png, webp)');
                return;
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
                // Ici on délègue à ProductImageModel :
                $this->imageModel->setMainImage($productId, $filename);
            } else {
                setFlashMessage('danger', 'Erreur lors du téléchargement de l’image.');
            }
        }
    }    

    /**
     * Store a new product in the database
     * 
     * @return void
     */
    public function store()
    {
        $validation = $this->validateProductForm();

        if (!empty($validation['errors'])) {
            setFlashMessage('danger', implode('<br>', $validation['errors']));
            redirect('admin/products/create');
        }

        $data = $validation['data'];

        $id = $this->productModel->create([
            'name' => $data['name'],
            'short_description' => $data['short'],
            'description' => $data['desc'],
            'price' => $data['price'],
            'category_id' => $data['category'],
            'color_id' => $data['color'],
            'fabric_id' => $data['fabric'],
            'cultural_region_id' => $data['region'],
            'supplier_id' => $data['supplier']
        ]);

        if ($id) {
            $this->handleMainImageUpload($id);
            setFlashMessage('success', 'Produit ajouté avec succès');
            redirect('admin/products');
        } else {
            setFlashMessage('danger', 'Erreur lors de l\'ajout');
            redirect('admin/products/create');
        }
    }

    /**
     * Update an existing product in the database
     *
     * @param int $id The ID of the product to update
     * @return void
     */
    public function update($id)
    {
        $product = $this->productModel->getDetailedProductById($id);
        if (!$product) {
            setFlashMessage('danger', 'Produit introuvable.');
            redirect('admin/products');
        }
    
        $validation = $this->validateProductForm();
    
        if (!empty($validation['errors'])) {
            setFlashMessage('danger', implode('<br>', $validation['errors']));
            redirect('admin/products/edit/' . $id);
        }
    
        $data = $validation['data'];
    
        $updated = $this->productModel->update($id, [
            'name' => $data['name'],
            'short_description' => $data['short'],
            'description' => $data['desc'],
            'price' => $data['price'],
            'category_id' => $data['category'],
            'color_id' => $data['color'],
            'fabric_id' => $data['fabric'],
            'cultural_region_id' => $data['region'],
            'supplier_id' => $data['supplier']
        ]);
    
        if ($updated) {
            $this->handleMainImageUpload($id);
            setFlashMessage('success', 'Produit mis à jour.');
            redirect('admin/products');
        } else {
            setFlashMessage('warning', 'Aucune modification effectuée.');
            redirect('admin/products/edit/' . $id);
        }
    }    

    /**
     * Delete a product from the database
     *
     * @param int $id The ID of the product to delete
     * @return void
     */
    public function delete($id)
    {
        $product = $this->productModel->getDetailedProductById($id);
        if (!$product) {
            setFlashMessage('danger', 'Produit introuvable.');
            redirect('admin/products');
        }
    
        if ($this->productModel->delete($id)) {
    
            $uploadDir = PUBLIC_PATH . '/uploads/products/' . $id;
            if (is_dir($uploadDir)) {
                $files = glob($uploadDir . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                rmdir($uploadDir);
            }
    
            setFlashMessage('success', 'Produit supprimé avec succès.');
        } else {
            setFlashMessage('danger', 'Erreur lors de la suppression.');
        }
    
        redirect('admin/products');
    }
    
}