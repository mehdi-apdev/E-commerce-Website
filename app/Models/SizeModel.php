<?php

namespace App\Models;

use App\Core\BaseModel;
use PDO;

class SizeModel extends BaseModel
{
    protected string $table = 'sizes';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    public function getAll(): array
    {
        // Suppose la table `sizes` a un champ `size_label`.
        // On ne stocke peut-être pas d’ID, c’est possible.
        // On ordonne par label par ex:
        $sql = "SELECT DISTINCT size_label FROM {$this->table} ORDER BY size_label";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
