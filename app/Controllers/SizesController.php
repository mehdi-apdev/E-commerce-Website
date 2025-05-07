<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\SizeModel;

class SizesController extends BaseController
{
    private SizeModel $sizeModel;

    public function __construct()
    {
        parent::__construct();
        $this->sizeModel = new SizeModel($this->pdo);
    }

    public function getAllJson(): void
    {
        header('Content-Type: application/json');
        $data = $this->sizeModel->getAll();
        echo json_encode(['sizes' => $data]);
    }

    public function getByProductIdJson(int $productId): void
    {
        header('Content-Type: application/json');
        $data = $this->sizeModel->getByProductId($productId);

        if ($data) {
            echo json_encode(['sizes' => $data]);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Aucune taille trouvée pour ce produit.']);
        }
    }
}
