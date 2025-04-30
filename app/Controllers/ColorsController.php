<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\ColorModel;

class ColorsController extends BaseController
{
    private ColorModel $colorModel;

    public function __construct()
    {
        parent::__construct();
        $this->colorModel = new ColorModel($this->pdo);
    }

    public function getAllJson(): void
    {
        header('Content-Type: application/json');
        $data = $this->colorModel->getAll();
        echo json_encode(['colors' => $data]);
    }
}
