<?php
// www/index.php

// 1. Démarrer la session
session_start();

// === DEBUG ===
file_put_contents(__DIR__ . '/debug.log', "🟢 Début index.php\n", FILE_APPEND);

try {
    // 2. Chargement config et dépendances
    require_once BASE_PATH . '/config/config.php';
    file_put_contents(__DIR__ . '/debug.log', "✅ config.php chargé\n", FILE_APPEND);

    require_once BASE_PATH . '/app/core/helpers.php';
    file_put_contents(__DIR__ . '/debug.log', "✅ helpers.php chargé\n", FILE_APPEND);

    require_once BASE_PATH . '/vendor/autoload.php';
    file_put_contents(__DIR__ . '/debug.log', "✅ autoload.php chargé\n", FILE_APPEND);

    require_once BASE_PATH . '/config/database.php';
    file_put_contents(__DIR__ . '/debug.log', "✅ database.php chargé\n", FILE_APPEND);

    // 3. Parsing de l’URL
    $uri = $_SERVER['REQUEST_URI'];
    $scriptName = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $relativeUri = '/' . ltrim(str_replace($scriptName, '', $uri), '/');
    $cleanUri = strtok($relativeUri, '?');

    file_put_contents(__DIR__ . '/debug.log', "🧪 index.php atteint - URI: $cleanUri\n", FILE_APPEND);

    // 4. Routage API
    if (preg_match('#^/api/#', $cleanUri)) {
        file_put_contents(__DIR__ . '/debug.log', "🔁 Redirection vers api.php\n", FILE_APPEND);
        require_once BASE_PATH . '/routes/api.php';
        exit;
    }

    // 5. Page d’accueil statique
    if ($cleanUri === '/' || $cleanUri === '/index.php') {
        readfile(__DIR__ . '/index.html');
        exit;
    }

    // 6. Sinon → 404
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    file_put_contents(__DIR__ . '/debug.log', "🛑 Exception attrapée : " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['error' => 'Fatal error in index.php', 'details' => $e->getMessage()]);
}
