<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Database;
use PDO;

class InquiryRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public static function make(): self
    {
        return new self(Database::connection());
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO inquiries (artwork_id, name, email, phone, preferred_size, message, whatsapp_message) VALUES (:artwork_id, :name, :email, :phone, :preferred_size, :message, :whatsapp_message)'
        );

        $stmt->execute([
            'artwork_id' => $data['artwork_id'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'preferred_size' => $data['preferred_size'] ?? null,
            'message' => $data['message'],
            'whatsapp_message' => $data['whatsapp_message'] ?? null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function recent(int $limit = 20): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM inquiries ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
