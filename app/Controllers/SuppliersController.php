<?php
// app/Controllers/SuppliersController.php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\SupplierModel;

class SuppliersController extends BaseController
{
    private SupplierModel $supplierModel;

    public function __construct()
    {
        parent::__construct();
        $this->supplierModel = new SupplierModel($this->pdo);
    }

    /**
     * GET /api/suppliers
     * Renvoie un tableau JSON des fournisseurs : [ {supplier_id, name}, ... ]
     */
    public function getAllJson(): void
    {
        header('Content-Type: application/json');
        $suppliers = $this->supplierModel->getAll();
        echo json_encode(['suppliers' => $suppliers]);
    }
}