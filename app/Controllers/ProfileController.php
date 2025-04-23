<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\UserModel;

class ProfileController extends BaseController
{
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel($this->pdo);
    }

    public function getProfile()
    {
        header('Content-Type: application/json');

        $userSession = $_SESSION['user'] ?? $this->getUserFromToken();

        if (!$userSession || empty($userSession['id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Non autorisé']);
            return;
        }

        $user = $this->userModel->getUserById($userSession['id']);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'Utilisateur introuvable']);
            return;
        }

        echo json_encode(['success' => true, 'user' => [
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'email'      => $user['email'],
            'phone'      => $user['phone']
        ]]);
    }

    public function updateProfile()
    {
        header('Content-Type: application/json');

        $userSession = $_SESSION['user'] ?? $this->getUserFromToken();
        if (!$userSession || empty($userSession['id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Non autorisé']);
            return;
        }

        $firstName = htmlspecialchars(trim($_POST['first_name'] ?? ''));
        $lastName  = htmlspecialchars(trim($_POST['last_name'] ?? ''));
        $phone     = htmlspecialchars(trim($_POST['phone'] ?? ''));
        $password  = trim($_POST['password'] ?? '');

        $errors = [];

        if (!$firstName) $errors['first_name'] = 'Prénom requis';
        if (!$lastName) $errors['last_name'] = 'Nom requis';

        if (!empty($password) && strlen($password) < 8) {
            $errors['password'] = 'Mot de passe trop court (min. 8 caractères)';
        }

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        $this->userModel->updateProfile(
            $userSession['id'],
            $firstName,
            $lastName,
            $phone,
            $password
        );

        // Mettre à jour la session
        $_SESSION['user']['first_name'] = $firstName;
        $_SESSION['user']['last_name']  = $lastName;

        echo json_encode(['success' => true, 'message' => 'Profil mis à jour avec succès.']);
    }
}
