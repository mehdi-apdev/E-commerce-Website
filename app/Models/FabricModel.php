<?php

namespace App\Models;

use App\Core\BaseModel;
use PDO;

class FabricModel extends BaseModel
{
    protected string $table = 'fabrics';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    public function getAll(): array
    {
        $sql = "SELECT fabric_id, name FROM {$this->table} ORDER BY name";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
