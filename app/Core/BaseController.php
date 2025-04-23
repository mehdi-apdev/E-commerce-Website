<?php
// app/Core/BaseController.php

namespace App\Core;

use App\Core\Database;

/**
 * BaseController
 *
 * Parent class for all controllers to inherit shared functionality.
 */
abstract class BaseController
{
    protected $pdo;

    public function __construct()
    {
        // Automatically assign PDO connection from the Database singleton
        $this->pdo = Database::getInstance();
    }

    /**
     * Render a view
     *
     * @param string $viewPath Relative path from the views folder (e.g. 'home/index')
     * @param array $data Data to pass to the view
     */
    protected function render(string $viewPath, array $data = []): void
    {
        if (isset($_SESSION['flash_message']) && is_array($_SESSION['flash_message'])) {
            // Nettoyage préventif si jamais la vue ne l'appelle pas
            $_SESSION['flash_message_rendered'] = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
        }

        extract($data);
        $viewFile = APP_ROOT . '/views/' . $viewPath . '.php';

        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            http_response_code(404);
            die("View '{$viewPath}' not found.");
        }
    }

    public function getUserFromToken(): ?array
    {
        if (!empty($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            $userModel = new \App\Models\UserModel($this->pdo);
            return $userModel->getUserByRememberToken($token);            
        }
        return null;
    }

}