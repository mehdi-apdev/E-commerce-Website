<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\FabricModel;

class FabricsController extends BaseController
{
    private FabricModel $fabricModel;

    public function __construct()
    {
        parent::__construct();
        $this->fabricModel = new FabricModel($this->pdo);
    }

    public function getAllJson(): void
    {
        header('Content-Type: application/json');
        $data = $this->fabricModel->getAll();
        echo json_encode(['fabrics' => $data]);
    }
}
