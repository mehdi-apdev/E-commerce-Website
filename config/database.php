<?php
// config/database.php

// Inclure les fonctions utilitaires (dont env())
require_once __DIR__ . '/../app/core/helpers.php';

// Charge les variables d’environnement depuis le fichier .env
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$name, $value] = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
} else {
    die("Fichier .env introuvable !");
}

// Définitions des constantes à partir de $_ENV
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'eshop_clothing'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Enregistre l’instance dans la classe Database si elle existe
    if (class_exists('App\\Core\\Database')) {
        \App\Core\Database::setInstance($pdo);
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
