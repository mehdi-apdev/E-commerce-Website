<?php

namespace App\Models;

use App\Core\BaseModel;
use PDO;

/**
 * CategoryModel
 *
 * Gère la table `categories`.
 * Hérite de BaseModel (qui te fournit $this->pdo)
 */
class CategoryModel extends BaseModel
{
    protected string $table = 'categories';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * getAll()
     * Récupère toutes les catégories (id + name).
     */
    public function getAll(): array
    {
        $sql = "SELECT category_id, name FROM {$this->table} ORDER BY name";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
