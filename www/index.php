<?php
// www/index.php

// 1. Démarrer la session
session_start();

// 2. Configs + autoload + helpers
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

// 3. Nettoyage et parsing de l’URL
$uri = $_SERVER['REQUEST_URI'];
$scriptName = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); // souvent vide en prod
$relativeUri = '/' . ltrim(str_replace($scriptName, '', $uri), '/');
$cleanUri = strtok($relativeUri, '?');

// 4. Routage API
if (preg_match('#^/api/#', $cleanUri)) {
    require_once __DIR__ . '/../routes/api.php';
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
