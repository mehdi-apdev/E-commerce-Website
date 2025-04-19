<?php
// app/middleware/Auth.php

namespace App\Middleware;

/**
 * Auth Middleware
 * Ensure the user is authenticated.
 */
class Auth {
    public static function check(): void
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'Utilisateur non connecté',
            ]);
            exit;
        }
    }
}
