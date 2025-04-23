<?php
// app/Controllers/AuthController.php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\UserModel;

/**
 * AuthController
 * 
 * Handles user authentication operations including registration,
 * login, logout, and other account-related functionality.
 */
class AuthController extends BaseController
{
    private $userModel;
    private $minPasswordLength = 8;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel($this->pdo);
    }

    public function login()
    {
        if (!isset($_SESSION['user'])) {
            $rememberedUser = $this->getUserFromToken();
            if ($rememberedUser) {
                $_SESSION['user'] = $rememberedUser;
            }
        }
        
        if (isset($_SESSION['user'])) {
            redirect('index.html');
        }        
    }

    public function loginPost()
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
    
        $errors = [];
    
        if (!$email) $errors['email'] = 'Adresse email invalide';
        if (empty($password)) $errors['password'] = 'Le mot de passe est requis';
    
        if (empty($errors)) {
            $user = $this->userModel->login($email, $password);
    
            if ($user) {
                if (!isset($user['is_active']) || !$user['is_active']) {
                    $errors['auth'] = 'Votre compte est désactivé.';
                } else {
                    // Normaliser les données de session
                    $_SESSION['user'] = [
                        'id'         => $user['user_id'],
                        'first_name' => $user['first_name'],
                        'last_name'  => $user['last_name'],
                        'email'      => $user['email'],
                        'role'       => $user['role'] ?? 'customer',
                    ];
    
                    // Gestion du cookie de session "remember me"
                    if (!empty($_POST['remember'])) {
                        // Génère un token aléatoire sécurisé
                        $token = bin2hex(random_bytes(32));
                    
                        // Sauvegarde le token dans la BDD
                        $this->userModel->updateRememberToken($user['user_id'], $token);
                    
                        // Stocke le token dans un cookie sécurisé, HTTP only
                        setcookie('remember_token', $token, time() + (30 * 86400), '/', '', false, true);
                    }
                    
    
                    $redirectUrl = url('index.html');
    
                    if ($isAjax) {
                        echo json_encode([
                            'success' => true,
                            'redirect' => $redirectUrl,
                            'user' => $_SESSION['user']
                        ]);
                    } else {
                        header("Location: $redirectUrl");
                    }
                    exit;
                }
            } else {
                $errors['auth'] = 'Email ou mot de passe incorrect';
            }
        }
    
        if ($isAjax) {
            echo json_encode(['success' => false, 'errors' => $errors]);
        } else {
            $_SESSION['errors'] = $errors;
            redirect('auth/login');
        }
    }

    public function registerPost()
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
        $firstName = htmlspecialchars(trim($_POST['first_name'] ?? ''));
        $lastName = htmlspecialchars(trim($_POST['last_name'] ?? ''));
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
    
        $errors = [];
    
        if (!$firstName) $errors['first_name'] = 'Le prénom est requis';
        if (!$lastName) $errors['last_name'] = 'Le nom est requis';
        if (!$email) {
            $errors['email'] = 'Email invalide';
        } elseif ($this->userModel->emailExists($email)) {
            $errors['email'] = 'Email déjà utilisé';
        }
    
        if (strlen($password) < $this->minPasswordLength) {
            $errors['password'] = "Mot de passe trop court (min {$this->minPasswordLength} caractères)";
        } elseif ($password !== $passwordConfirm) {
            $errors['password_confirm'] = 'Les mots de passe ne correspondent pas';
        }
    
        if (empty($errors)) {
            $userId = $this->userModel->register($firstName, $lastName, $email, $password, $phone);
            if ($userId) {
                $user = $this->userModel->getUserById($userId);
    
                // Uniformiser les données de session
                $_SESSION['user'] = [
                    'id'         => $user['user_id'],
                    'first_name' => $user['first_name'],
                    'last_name'  => $user['last_name'],
                    'email'      => $user['email'],
                    'role'       => $user['role'] ?? 'customer',
                ];
    
                setFlashMessage('success', 'Votre compte a bien été créé. Bienvenue !');
    
                $redirectUrl = url('index.html');
    
                if ($isAjax) {
                    echo json_encode([
                        'success' => true,
                        'redirect' => $redirectUrl,
                        'user' => $_SESSION['user']
                    ]);
                } else {
                    header("Location: $redirectUrl");
                }
                exit;
            }
        }
    
        if ($isAjax) {
            echo json_encode(['success' => false, 'errors' => $errors]);
        } else {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = compact('firstName', 'lastName', 'email', 'phone');
            redirect('auth/register');
        }
    }
    
    

    public function logout()
    {
        if (isset($_COOKIE['remember_user'])) {
            setcookie('remember_user', '', time() - 3600, '/', '', false, true);
        }

        session_unset();
        session_destroy();

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode(['success' => true, 'message' => 'Déconnexion réussie']);
        } else {
            redirect('index.html');
        }
    }

    public function me()
    {
        $user = $_SESSION['user'] ?? $this->getUserFromToken();
        header('Content-Type: application/json');
    
        if ($user) {
            echo json_encode([
                'success' => true,
                'user' => [
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email'],
                    'role' => $user['role'] ?? 'customer'
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
        }
    }
    

}
