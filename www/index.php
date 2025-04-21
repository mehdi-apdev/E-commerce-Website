<?php
// www/index.php

session_start();

try {
    file_put_contents(__DIR__ . '/debug.log', "🟢 Début index.php\n", FILE_APPEND);

    // Configurations
    require_once __DIR__ . '/../config/config.php';
    file_put_contents(__DIR__ . '/debug.log', "✅ config.php chargé\n", FILE_APPEND);

    require_once BASE_PATH . '/app/Core/helpers.php';
    file_put_contents(__DIR__ . '/debug.log', "✅ helpers.php chargé\n", FILE_APPEND);

    require_once BASE_PATH . '/vendor/autoload.php';
    file_put_contents(__DIR__ . '/debug.log', "✅ autoload chargé\n", FILE_APPEND);

    require_once BASE_PATH . '/config/database.php';
    file_put_contents(__DIR__ . '/debug.log', "✅ database.php chargé\n", FILE_APPEND);

    // Analyse de l’URL
    $uri = $_SERVER['REQUEST_URI'];
    $scriptName = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $relativeUri = '/' . ltrim(str_replace($scriptName, '', $uri), '/');
    $cleanUri = strtok($relativeUri, '?');

    file_put_contents(__DIR__ . '/debug.log', "🧪 URI analysée: $cleanUri\n", FILE_APPEND);

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
    file_put_contents(__DIR__ . '/debug.log', "🛑 Exception attrapée : " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode([
        'error' => 'Fatal error in index.php',
        'details' => $e->getMessage()
    ]);
}
