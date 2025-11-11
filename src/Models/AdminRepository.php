<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Database;
use PDO;

class AdminRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public static function make(): self
    {
        return new self(Database::connection());
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM admins WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch();

        return $admin ?: null;
    }

    public function create(string $username, string $passwordHash, ?string $email = null): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO admins (username, password_hash, email) VALUES (:username, :password_hash, :email)');
        $stmt->execute([
            'username' => $username,
            'password_hash' => $passwordHash,
            'email' => $email,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updatePassword(int $id, string $passwordHash): bool
    {
        $stmt = $this->pdo->prepare('UPDATE admins SET password_hash = :password_hash WHERE id = :id');
        return $stmt->execute([
            'password_hash' => $passwordHash,
            'id' => $id,
        ]);
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT id, username, email, is_active, created_at FROM admins ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }
}
