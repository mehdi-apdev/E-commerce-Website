<?php
// routes/api.php

// DEBUG + SESSION
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Controllers\ProductsController;
use App\Controllers\CategoriesController;
use App\Controllers\ColorsController;
use App\Controllers\FabricsController;
use App\Controllers\SizesController;
use App\Controllers\RegionsController;
use App\Controllers\AuthController;

// Nettoyer l’URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

$basePath = '/api'; // Chemin de base de l'API
$route = str_replace($basePath, '', $path);

// Réponse JSON par défaut
header('Content-Type: application/json');

try {
    // ROUTEUR API
    switch (true) {

        // 0) HOME
        case $method === 'GET' && $route === '/home':
            (new \App\Controllers\HomeController())->index();
            break;    

        // 1) PRODUITS
        case $method === 'GET' && $route === '/products':
            (new ProductsController())->getAllJson();
            break;

        case $method === 'GET' && preg_match('#^/products/(\d+)$#', $route, $matches):
            (new ProductsController())->getOneJson($matches[1]);
            break;

        // 2) FILTRES
        case $method === 'GET' && $route === '/categories':
            (new CategoriesController())->getAllJson();
            break;

        case $method === 'GET' && $route === '/colors':
            (new ColorsController())->getAllJson();
            break;

        case $method === 'GET' && $route === '/fabrics':
            (new FabricsController())->getAllJson();
            break;

        case $method === 'GET' && $route === '/sizes':
            (new SizesController())->getAllJson();
            break;

        case $method === 'GET' && $route === '/regions':
            (new RegionsController())->getAllJson();
            break;

        // 3) AUTH
        case $method === 'POST' && $route === '/auth/registerPost':
            (new AuthController())->registerPost();
            break;

        case $method === 'POST' && $route === '/auth/loginPost':
            (new AuthController())->loginPost();
            break;

        case $method === 'GET' && $route === '/auth/me':
            (new AuthController())->me();
            break;

        case $method === 'POST' && $route === '/auth/logout':
            (new AuthController())->logout();
            break;

        // 4) ADMIN
        case $method === 'GET' && $route === '/admin/dashboard':
            (new \App\Controllers\Admin\DashboardController())->getStatsJson();
            break;

        case $method === 'DELETE' && preg_match('#^/products/(\d+)$#', $route, $matches):
            (new \App\Controllers\Admin\ProductsController())->delete($matches[1]);
            break;

        // Route non trouvée
        default:
            http_response_code(404);
            echo json_encode(['error' => 'API endpoint not found']);
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
}