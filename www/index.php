<?php
// www/index.php

session_start();

try {
    // Configurations
    require_once __DIR__ . '/../config/config.php';
    require_once BASE_PATH . '/app/Core/helpers.php';
    require_once BASE_PATH . '/vendor/autoload.php';
    require_once BASE_PATH . '/config/database.php';

    // Analyse de l’URL
    $uri = $_SERVER['REQUEST_URI'];
    $scriptName = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $relativeUri = '/' . ltrim(str_replace($scriptName, '', $uri), '/');
    $cleanUri = strtok($relativeUri, '?');

    // Routage vers API
    if (preg_match('#^/api/#', $cleanUri)) {
        require_once BASE_PATH . '/routes/api.php';
        exit;
    }

    // Page d’accueil statique
    if ($cleanUri === '/' || $cleanUri === '/index.php') {
        readfile(__DIR__ . '/index.html');
        exit;
    }

    // Sinon 404
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Fatal error in index.php',
        'details' => $e->getMessage()
    ]);
}
