<?php
// www/index.php

// 🔐 Configuration des cookies de session AVANT session_start()
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    ini_set('session.cookie_secure', '0'); // pas de Secure en local
    ini_set('session.cookie_samesite', 'Lax'); // autorise fetch sans rejet
} else {
    ini_set('session.cookie_secure', '1'); // obligatoire en prod
    ini_set('session.cookie_samesite', 'None'); // accepte tous les cas cross-origin
}

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

    // 🌟 Gestion de la page produit générique
    if (preg_match('#^/product/(\d+)$#', $cleanUri, $matches)) {
        readfile(__DIR__ . '/product.html');
        exit;
    }

    // 🌟 Redirection vers la page 404
    http_response_code(302);
    header('Location: /404.html');
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Fatal error in index.php',
        'details' => $e->getMessage()
    ]);
}
