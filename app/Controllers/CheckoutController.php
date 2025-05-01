<?php

namespace App\Controllers;

use App\Core\BaseController;
use PDO;

class CheckoutController extends BaseController
{
    public function create(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['user']) || !isset($input['cart'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            return;
        }

        // 🧍 Authentification : session ou token
        $userId = null;

        if (!empty($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        } else {
            $user = $this->getUserFromToken();
            if ($user) {
                $userId = $user['user_id'];
                $_SESSION['user_id'] = $userId;
            }
        }

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
            return;
        }

        $user = $input['user'];
        $cart = $input['cart'];

        // ✅ Vérifie que l'adresse est complète
        if (
            empty($user['street']) || empty($user['number']) ||
            empty($user['postal_code']) || empty($user['city'])
        ) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Adresse de livraison incomplète']);
            return;
        }

        // 🏠 Enregistre la nouvelle adresse (non par défaut)
        $stmt = $this->pdo->prepare("
            INSERT INTO shipping_addresses (
                user_id, recipient_name, street, number, postal_code, city, region, country, is_default
            )
            VALUES (:user_id, :recipient_name, :street, :number, :postal_code, :city, :region, :country, 0)
        ");

        $recipientName = trim($user['first_name'] . ' ' . $user['last_name']);
        $stmt->execute([
            'user_id' => $userId,
            'recipient_name' => $recipientName,
            'street' => $user['street'],
            'number' => (int)$user['number'],
            'postal_code' => $user['postal_code'],
            'city' => $user['city'],
            'region' => $user['region'] ?? '',
            'country' => $user['country'] ?? 'Belgium'
        ]);

        $addressId = $this->pdo->lastInsertId();
        if (!$addressId) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur enregistrement adresse']);
            return;
        }

        // 💶 Calcul du total depuis la BDD (sécurisé)
        $total = 0;
        $productsInfo = [];

        foreach ($cart as $item) {
            $stmt = $this->pdo->prepare("SELECT price FROM products WHERE product_id = ?");
            $stmt->execute([$item['product_id']]);
            $product = $stmt->fetch();

            if (!$product) continue;

            $unitPrice = (float)$product['price'];
            $quantity = (int)$item['quantity'];
            $subtotal = $unitPrice * $quantity;
            $total += $subtotal;

            $productsInfo[] = [
                'product_id' => $item['product_id'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice
            ];
        }

        // 🧾 Enregistre la commande
        $stmt = $this->pdo->prepare("
            INSERT INTO orders (user_id, address_id, total_amount, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $addressId, $total]);
        $orderId = $this->pdo->lastInsertId();

        if (!$orderId) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur création commande']);
            return;
        }

        // 🧺 Enregistre les items de la commande
        $stmtItem = $this->pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, unit_price)
            VALUES (?, ?, ?, ?)
        ");
        foreach ($productsInfo as $product) {
            $stmtItem->execute([
                $orderId,
                $product['product_id'],
                $product['quantity'],
                $product['unit_price']
            ]);
        }

        echo json_encode(['success' => true, 'order_id' => $orderId]);
    }
}
