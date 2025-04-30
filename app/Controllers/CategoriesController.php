<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\CategoryModel;

/**
 * CategoriesController
 *
 * Expose /api/categories -> getAllJson()
 * Hérite de BaseController pour $this->pdo, etc.
 */
class CategoriesController extends BaseController
{
    private CategoryModel $categoryModel;

    public function __construct()
    {
        parent::__construct();
        $this->categoryModel = new CategoryModel($this->pdo);
    }

    /**
     * GET /api/categories
     * Renvoie un tableau JSON des catégories : [ {category_id, name}, ... ]
     */
    public function getAllJson(): void
    {
        header('Content-Type: application/json');
        $data = $this->categoryModel->getAll();
        echo json_encode(['categories' => $data]);
    }
}
