<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Database
 *
 * Singleton class for PDO database connection
 */
class Database {
    private static ?PDO $instance = null;

    /**
     * Prevent direct instantiation
     */
    private function __construct() {}

    /**
     * Set the PDO instance (useful for testing or reusing an existing connection)
     * 
     * @param PDO $pdo Existing PDO connection
     */
    public static function setInstance(PDO $pdo): void {
        self::$instance = $pdo;
    }

    /**
     * Get PDO instance (singleton)
     *
     * @return PDO
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $host = DB_HOST;
            $dbname = DB_NAME;
            $user = DB_USER;
            $pass = DB_PASS;

            try {
                self::$instance = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}