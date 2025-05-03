<?php
// routes/api.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Gestion de _method pour simuler PUT/DELETE via POST + FormData
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method'])) {
    $_SERVER['REQUEST_METHOD'] = strtoupper($_POST['_method']);
}

// Chargement des classes nécessaires
use App\Core\Router;
use App\Controllers\ProductsController;
use App\Controllers\CategoriesController;
use App\Controllers\ColorsController;
use App\Controllers\FabricsController;
use App\Controllers\SizesController;
use App\Controllers\RegionsController;
use App\Controllers\SuppliersController;
use App\Controllers\AuthController;
use App\Controllers\ProfileController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\ProductsController as AdminProductsController; // <-- aliasé proprement
use App\Controllers\CheckoutController;



require_once BASE_PATH . '/app/Core/Router.php';

header('Content-Type: application/json');

$router = new Router();

// === ROUTES ADMIN ===

// Dashboard admin (statistiques)
$router->get('/api/admin/dashboard', [DashboardController::class, 'getStatsJson']);

// CRUD produits admin
$router->post('/api/admin/products', [AdminProductsController::class, 'storeJson']);
$router->put('/api/admin/products/{id}', [AdminProductsController::class, 'updateJson']);
$router->delete('/api/admin/products/{id}', [AdminProductsController::class, 'deleteJson']);

// === ROUTES UTILISATEUR PUBLIC ===

// Produits
$router->get('/api/products', [ProductsController::class, 'getAllJson']);
$router->get('/api/products/{id}', [ProductsController::class, 'getOneJson']);

// Commande (checkout)
$router->post('/api/checkout', [CheckoutController::class, 'create']);

// Filtres
$router->get('/api/categories', [CategoriesController::class, 'getAllJson']);
$router->get('/api/colors', [ColorsController::class, 'getAllJson']);
$router->get('/api/fabrics', [FabricsController::class, 'getAllJson']);
$router->get('/api/sizes', [SizesController::class, 'getAllJson']);
$router->get('/api/regions', [RegionsController::class, 'getAllJson']);
$router->get('/api/suppliers', [SuppliersController::class, 'getAllJson']);

// Auth
$router->post('/api/auth/registerPost', [AuthController::class, 'registerPost']);
$router->post('/api/auth/loginPost', [AuthController::class, 'loginPost']);
$router->get('/api/auth/me', [AuthController::class, 'me']);
$router->post('/api/auth/logout', [AuthController::class, 'logout']);
$router->get('/api/auth/getProfile', [ProfileController::class, 'getProfile']);
$router->post('/api/auth/updateProfile', [ProfileController::class, 'updateProfile']);

// Dispatch
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
