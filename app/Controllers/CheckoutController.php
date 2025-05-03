<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\ProductModel;

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

        // Authentification
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
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

        if (
            empty($user['street']) || empty($user['number']) ||
            empty($user['postal_code']) || empty($user['city'])
        ) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Adresse de livraison incomplète']);
            return;
        }

        try {
            $this->pdo->beginTransaction();

            // Enregistre l’adresse
            $stmt = $this->pdo->prepare("
                INSERT INTO shipping_addresses (
                    user_id, recipient_name, street, number, postal_code, city, region, country, is_default
                ) VALUES (
                    :user_id, :recipient_name, :street, :number, :postal_code, :city, :region, :country, 0
                )
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
            if (!$addressId) throw new \Exception("Échec insertion adresse");

            $productModel = new ProductModel($this->pdo);
            $total = 0;
            $productsInfo = [];

            foreach ($cart as $item) {
                $product = $productModel->getValidProduct($item['product_id']);

                if (!$product || $product['stock'] < $item['quantity']) {
                    throw new \Exception("Produit invalide ou stock insuffisant");
                }

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

            $total = round($total, 2);

            // Crée la commande
            $stmt = $this->pdo->prepare("
                INSERT INTO orders (user_id, address_id, total_amount, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$userId, $addressId, $total]);
            $orderId = $this->pdo->lastInsertId();
            if (!$orderId) throw new \Exception("Échec insertion commande");

            // Ajoute les items
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

                // Décrémente le stock
                if (!$productModel->decrementStock($product['product_id'], $product['quantity'])) {
                    throw new \Exception("Stock insuffisant ou erreur update stock");
                }
            }

            $this->pdo->commit();

            echo json_encode([
                'success' => true,
                'order_id' => $orderId,
                'total_amount' => $total,
                'address' => "{$user['street']} {$user['number']}, {$user['postal_code']} {$user['city']}"
            ]);

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la commande',
                'debug' => $e->getMessage()
            ]);
        }
    }
}