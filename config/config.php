<?php
// config/config.php

// Définit le chemin absolu à la racine du projet
define('BASE_PATH', dirname(__DIR__)); // /home/amarnac

// Base URL pour générer les liens
define('BASE_URL', '/');

// Chemin absolu vers le dossier app
define('APP_ROOT', BASE_PATH . '/app');

// Chemin vers les fichiers publics
define('PUBLIC_PATH', BASE_PATH . '/www');

// Chemin vers les uploads
define('UPLOADS_DIR', 'uploads/products');

// Nom du site
define('SITE_NAME', 'Amarna');

// Sécurité des mots de passe
define('PASSWORD_MIN_LENGTH', 8);
define('BCRYPT_COST', 12);
