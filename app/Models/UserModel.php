<?php
// app/Models/UserModel.php
namespace App\Models;

use App\Core\BaseModel;
use PDO;

/**
 * UserModel
 *
 * Handles all database operations related to users.
 */
class UserModel extends BaseModel {
    protected string $table = 'users';
    protected string $primaryKey = 'user_id';

    public function __construct(PDO $pdo) {
        parent::__construct($pdo);
    }

    /**
     * Register a new user.
     *
     * @return int|false
     */
    public function register(string $firstName, string $lastName, string $email, string $password, ?string $phone = null): int|false {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);

        $data = [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'password'   => $hashedPassword,
            'phone'      => $phone,
            'created_at' => date('Y-m-d H:i:s'),
            'last_login' => date('Y-m-d H:i:s'),
        ];

        return $this->create($data);
    }

    /**
     * Check if email already exists.
     */
    public function emailExists(string $email): bool {
        return $this->exists('email', $email);
    }

    /**
     * Authenticate user.
     */
    public function login(string $email, string $password): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $this->updateLastLogin($user['user_id']);
            return $user;
        }

        return null;
    }

    /**
     * Get a user by ID.
     */
    public function getUserById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_id = :id LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    

    private function updateLastLogin(int $userId): void {
        $stmt = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = :id");
        $stmt->bindValue(':id', $userId);
        $stmt->execute();
    }
    public function updateProfile($userId, $firstName, $lastName, $phone, $password = '')
    {
    
        if (!empty($password)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
            $sql = "UPDATE users SET first_name = :first_name, last_name = :last_name, phone = :phone, password = :password WHERE user_id = :id";
            $stmt = $this->pdo->prepare($sql);
    

            $stmt->execute([
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':phone' => $phone,
                ':password' => $passwordHash,
                ':id' => $userId
            ]);
        } else {
    
            $sql = "UPDATE users SET first_name = :first_name, last_name = :last_name, phone = :phone WHERE user_id = :id";
            $stmt = $this->pdo->prepare($sql);
    
            $stmt->execute([
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':phone' => $phone,
                ':id' => $userId
            ]);
        }
    }
    
    

    public function updateRememberToken(int $userId, string $token): void {
        $stmt = $this->pdo->prepare("UPDATE users SET remember_token = :token WHERE user_id = :id");
        $stmt->execute([
            ':token' => $token,
            ':id' => $userId
        ]);
    }
    
    public function getUserByRememberToken(string $token): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE remember_token = :token LIMIT 1");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        return $user;
    }
       
}
