<?php

namespace App\Models;

use App\Core\BaseModel;
use PDO;

class ShippingAddressModel extends BaseModel
{
    protected string $table = 'shipping_addresses';
    protected string $primaryKey = 'address_id';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    public function getAddressesByUserId(int $userId): array
    {
        $sql = "
            SELECT 
                address_id,
                recipient_name,
                street,
                number,
                postal_code,
                city,
                region,
                country,
                is_default
            FROM shipping_addresses
            WHERE user_id = :user_id
            ORDER BY is_default DESC, address_id ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createAddress(array $data): bool
    {
        $sql = "
            INSERT INTO shipping_addresses (
                user_id, recipient_name, street, number, postal_code, city, region, country, is_default
            ) VALUES (
                :user_id, :recipient_name, :street, :number, :postal_code, :city, :region, :country, :is_default
            )
        ";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function updateAddress(int $addressId, array $data): bool
    {
        $sql = "
            UPDATE shipping_addresses SET
                recipient_name = :recipient_name,
                street = :street,
                number = :number,
                postal_code = :postal_code,
                city = :city,
                region = :region,
                country = :country,
                is_default = :is_default
            WHERE address_id = :address_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $data['address_id'] = $addressId;
        return $stmt->execute($data);
    }

    public function deleteAddress(int $addressId): bool
    {
        $sql = "DELETE FROM shipping_addresses WHERE address_id = :address_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':address_id', $addressId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
