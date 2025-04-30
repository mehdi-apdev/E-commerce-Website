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
}
