<?php
// config/database.php

define('DB_HOST', 'amarnac927.mysql.db');
define('DB_NAME', 'amarnac927');
define('DB_USER', 'amarnac927');
define('DB_PASS', 'Lolmdr100'); 
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    

    if (class_exists('App\\Core\\Database')) {
        \App\Core\Database::setInstance($pdo);
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}