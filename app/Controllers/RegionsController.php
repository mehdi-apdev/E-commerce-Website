<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\RegionModel;

class RegionsController extends BaseController
{
    private RegionModel $regionModel;

    public function __construct()
    {
        parent::__construct();
        $this->regionModel = new RegionModel($this->pdo);
    }

    public function getAllJson(): void
    {
        header('Content-Type: application/json');
        $data = $this->regionModel->getAll();
        echo json_encode($data);
    }
}
