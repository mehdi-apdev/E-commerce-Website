<?php
// app/middleware/Admin.php

namespace App\Middleware;

/**
 * Admin Middleware
 * Ensure the user is an admin.
 */
class Admin {
    public static function handle() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            http_response_code(403);
            echo "403 Forbidden - You do not have permission to access this page.";
            exit;
        }
    }
}
