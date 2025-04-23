<?php
// routes/api.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Core\Router;
use App\Controllers\ProductsController;
use App\Controllers\CategoriesController;
use App\Controllers\ColorsController;
use App\Controllers\FabricsController;
use App\Controllers\SizesController;
use App\Controllers\RegionsController;
use App\Controllers\AuthController;
use App\Controllers\ProfileController;

require_once BASE_PATH . '/app/Core/Router.php';

header('Content-Type: application/json');

$router = new Router();

// Produits
$router->get('/api/products', [ProductsController::class, 'getAllJson']);
$router->get('/api/products/{id}', [ProductsController::class, 'getOneJson']);

// Filtres
$router->get('/api/categories', [CategoriesController::class, 'getAllJson']);
$router->get('/api/colors', [ColorsController::class, 'getAllJson']);
$router->get('/api/fabrics', [FabricsController::class, 'getAllJson']);
$router->get('/api/sizes', [SizesController::class, 'getAllJson']);
$router->get('/api/regions', [RegionsController::class, 'getAllJson']);

// Auth
$router->post('/api/auth/registerPost', [AuthController::class, 'registerPost']);
$router->post('/api/auth/loginPost', [AuthController::class, 'loginPost']);
$router->get('/api/auth/me', [AuthController::class, 'me']);
$router->post('/api/auth/logout', [AuthController::class, 'logout']);
$router->get('/api/auth/getProfile', [ProfileController::class, 'getProfile']);
$router->post('/api/auth/updateProfile', [ProfileController::class, 'updateProfile']);

// Dispatch
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
