<?php
// public/index.php

// Important: Start session first, before any output
session_start();

// 1. Load configuration constants
require_once dirname(__DIR__) . '/config/config.php';

// 2. Load PSR-4 autoloader first
require_once dirname(__DIR__) . '/vendor/autoload.php';

// 3. Now that autoloader is registered, load database config
// This will also initialize the Database singleton
require_once dirname(__DIR__) . '/config/database.php';

// 4. Load global helper functions
require_once APP_ROOT . '/core/helpers.php';

// 5 Load the routes
$uri = $_SERVER['REQUEST_URI'];
$scriptName = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); // Ex: '' en prod
$relativeUri = '/' . ltrim(str_replace($scriptName, '', $uri), '/');
$cleanUri = strtok($relativeUri, '?');

if (preg_match('#^/api/#', $cleanUri)) {
    require_once dirname(__DIR__) . '/routes/api.php';
    exit;
}


// 6. Start the router
use App\Core\Router;
new Router();

