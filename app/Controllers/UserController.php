<?php
//app/Controllers/UserController.php

/**
 * UserController
 * 
 * This controller handles user-related actions such as displaying the user account page.
 */

namespace App\Controllers;
use App\Core\BaseController;

class UserController extends BaseController
{
    public function account()
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Vous devez être connecté pour accéder à cette page.'];
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $user = $_SESSION['user'];
        $this->render('users/account', [
            'pageTitle' => 'Mon compte - ' . SITE_NAME,
            'user' => $_SESSION['user'],
        ]);
    }

    public function edit()
    {
        if (!isset($_SESSION['user'])) {
            redirect('auth/login');
        }

        $this->render('users/edit', [
            'pageTitle' => 'Modifier mon profil - ' . SITE_NAME,
            'user' => $_SESSION['user']
        ]);
    }

    public function editPost()
    {
        if (!isset($_SESSION['user'])) {
            redirect('auth/login');
        }

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];

        if (empty($firstName)) $errors['first_name'] = 'Le prénom est requis';
        if (empty($lastName)) $errors['last_name'] = 'Le nom est requis';

        if (!empty($password)) {
            if (strlen($password) < 8) {
                $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères';
            } elseif ($password !== $confirmPassword) {
                $errors['confirm_password'] = 'Les mots de passe ne correspondent pas';
            }
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            redirect('user/edit');
        }

        // Update user profile
        $userModel = new \App\Models\UserModel($this->pdo);
        $userId = $_SESSION['user']['user_id'];

        $userModel->updateProfile($userId, $firstName, $lastName, $phone, $password);

        // Refresh user session data
        $_SESSION['user'] = $userModel->getUserById($userId);
        setFlashMessage('success', 'Profil mis à jour avec succès');
        redirect('user/account');
    }

}
