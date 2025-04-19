<?php
// config/config.php

/**
 * config.php
 * 
 * Configuration file for the e-commerce application
 */

// Base URL for generating links
define('BASE_URL', '/my-eshop/public');

// Absolute path to the app folder
define('APP_ROOT', dirname(__DIR__) . '/app');

// Absolute path to the public folder
define('PUBLIC_PATH', dirname(__DIR__) . '/public');

// Absolute path to the uploads folder
define('UPLOADS_DIR', 'uploads/products');

// Site information
define('SITE_NAME', 'Amarna');

// Password security settings
define('PASSWORD_MIN_LENGTH', 8);
define('BCRYPT_COST', 12); // Cost factor for password_hash

// Database settings are in database.php