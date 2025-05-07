<?php
namespace App\Core;

use PDO;

/**
 * BaseModel
 * 
 * Base class for all models with common CRUD operations.
 * This class provides standard database operations that all models can inherit.
 */
abstract class BaseModel {
    /**
     * PDO database connection instance
     */
    protected PDO $pdo;
    
    /**
     * Name of the table in the database
     */
    protected string $table;
    
    /**
     * Primary key column name
     */
    protected string $primaryKey = 'id';
    
    /**
     * Constructor - requires PDO connection
     * 
     * @param PDO $pdo Database connection
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        
        // If no table name is specified in the child class, 
        // generate it from the class name
        if (!isset($this->table)) {
            // Get the class name without namespace
            $className = (new \ReflectionClass($this))->getShortName();
            // Remove "Model" suffix if present and convert to snake_case
            $tableName = strtolower(preg_replace('/Model$/', '', $className));
            $tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $tableName)) . 's';
            $this->table = $tableName;
        }
    }

    /**
     * Get all records from the table
     * 
     * @param string $orderBy Column to order by
     * @param string $direction Order direction (ASC or DESC)
     * @return array All records
     */
    public function findAll($orderBy = null, $direction = 'ASC') {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$direction}";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create a new record
     * 
     * @param array $data Associative array of column => value
     * @return int|false The ID of the newly created record or false on failure
     */
    public function create(array $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        
        $success = $stmt->execute($data);
        
        return $success ? $this->pdo->lastInsertId() : false;
    }
    
    /**
     * Update an existing record
     * 
     * @param int $id Primary key value
     * @param array $data Associative array of column => value
     * @return bool Success or failure
     */
    public function update($id, array $data) {
        $setClause = [];
        foreach (array_keys($data) as $column) {
            $setClause[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setClause);
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id";
        $data['id'] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }
    
    /**
     * Delete a record
     * 
     * @param int $id Primary key value
     * @return bool Success or failure
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->pdo->prepare($sql);
        if (!$stmt->execute(['id' => $id])) {
            $errorInfo = $stmt->errorInfo();
            error_log("Erreur SQL lors de la suppression : " . $errorInfo[2]);
        }
        return $stmt->rowCount() > 0;
    }
    
    
    /**
     * Find records by a specific field
     * 
     * @param string $field The field name to search
     * @param mixed $value The value to search for
     * @return array Matching records
     */
    public function findBy($field, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE {$field} = :value";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['value' => $value]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Count total records
     * 
     * @param string $whereClause Optional WHERE clause
     * @param array $params Optional parameters for WHERE clause
     * @return int Number of records
     */
    public function count($whereClause = '', array $params = []) {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        
        if ($whereClause) {
            $sql .= " WHERE {$whereClause}";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Check if a record exists
     * 
     * @param string $field Field to check
     * @param mixed $value Value to check for
     * @return bool True if exists, false otherwise
     */
    public function exists($field, $value) {
        return $this->count("{$field} = :value", ['value' => $value]) > 0;
    }
}