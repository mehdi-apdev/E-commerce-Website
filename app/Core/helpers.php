<?php
// app/core/helpers.php

/**
 * helpers.php
 *
 * Contains general-purpose utility functions used throughout the app.
 */

/**
 * Generate full URL from relative path.
 *
 * @param string $path Relative path (ex: 'products', 'assets/css/style.css')
 * @return string Full URL (ex: '/my-eshop/public/products')
 */

 function env(string $key, mixed $default = null): mixed {
    return $_ENV[$key] ?? $default;
}
function url(string $path = ''): string {
    $cleanPath = trim($path, '/');
    
    // Redirection par défaut vers index.html si aucun chemin
    if ($cleanPath === '') {
        $cleanPath = 'html/index.html';
    }

    return rtrim(BASE_URL, '/') . '/' . $cleanPath;
}

/**
 * Redirect to a given relative path using BASE_URL
 *
 * @param string $path Relative path (ex: 'products', 'auth/login')
 */
function redirect(string $path = ''): void {
    $location = url($path);
    header("Location: $location");
    exit;
}

/**
 * Check if user is logged in
 *
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user']);
}

/**
 * Check if current user is admin
 *
 * @return bool True if user is admin, false otherwise
 */
function isAdmin(): bool {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

/**
 * Check if current user is staff
 *
 * @return bool True if user is staff, false otherwise
 */
function isStaff(): bool {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'staff';
}

/**
 * Get current logged in user data
 *
 * @return array|null User data if logged in, null otherwise
 */
function getCurrentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Get user ID of current logged in user
 *
 * @return int|null User ID if logged in, null otherwise
 */
function getCurrentUserId(): ?int {
    return isset($_SESSION['user']) ? (int)$_SESSION['user']['user_id'] : null;
}

/**
 * Format price with currency symbol
 *
 * @param float $price Price to format
 * @param string $currency Currency symbol
 * @return string Formatted price
 */
function formatPrice(float $price, string $currency = '€'): string {
    return number_format($price, 2, ',', ' ') . ' ' . $currency;
}

/**
 * Set a flash message to be displayed on the next page load
 *
 * @param string $type Message type (success, danger, warning, info)
 * @param string $message The message content
 */
function setFlashMessage(string $type, string $message): void {
    $_SESSION['flash_message_rendered'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Sanitize user input
 *
 * @param string $input Input to sanitize
 * @return string Sanitized input
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Get the base name of a product image path
 *
 * @param string $path Path to image
 * @return string Base name of image
 */
function getImageBaseName(string $path): string {
    return basename($path);
}

/**
 * Get first paragraph from text
 *
 * @param string $text Text to extract first paragraph from
 * @param int $maxLength Maximum length of extracted text
 * @return string First paragraph
 */
function getFirstParagraph(string $text, int $maxLength = 150): string {
    $text = strip_tags($text);
    if (strpos($text, '.') !== false) {
        $text = substr($text, 0, strpos($text, '.') + 1);
    }
    
    if (strlen($text) > $maxLength) {
        $text = substr($text, 0, $maxLength) . '...';
    }
    
    return $text;
}

/**
 * Generate a random string
 *
 * @param int $length Length of random string
 * @return string Random string
 */
function generateRandomString(int $length = 10): string {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    
    return $randomString;
}