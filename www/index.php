<?php
// www/index.php

// 1. Démarrer la session
session_start();

// 1. Configs
require_once dirname(__DIR__) . '/config/config.php';

// 2. Charge les helpers AVANT database (car contient env())
require_once dirname(__DIR__) . '/app/core/helpers.php';

// 3. Autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// 4. Connexion DB
require_once dirname(__DIR__) . '/config/database.php';

// 3. Nettoyage et parsing de l’URL
$uri = $_SERVER['REQUEST_URI'];
$scriptName = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); // souvent vide en prod
$relativeUri = '/' . ltrim(str_replace($scriptName, '', $uri), '/');
$cleanUri = strtok($relativeUri, '?');

// Debug (à enlever en prod)
file_put_contents(dirname(__DIR__) . '/debug.log', "Entrée dans index.php\nURI nettoyée : $cleanUri\n", FILE_APPEND);

// 4. Routage API
if (preg_match('#^/api/#', $cleanUri)) {
    require_once dirname(__DIR__) . '/routes/api.php';
    exit;
}

// 5. Page d’accueil statique
if ($cleanUri === '/' || $cleanUri === '/index.php') {
    readfile(__DIR__ . '/index.html');
    exit;
}

// 6. Sinon 404
http_response_code(404);
echo json_encode(['error' => 'Not Found']);
exit;
