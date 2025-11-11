<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Database;
use PDO;

class SettingRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public static function make(): self
    {
        return new self(Database::connection());
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $stmt = $this->pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $value = $stmt->fetchColumn();

        return $value === false ? $default : (string) $value;
    }

    public function set(string $key, string $value): void
    {
        $update = $this->pdo->prepare('UPDATE settings SET setting_value = :value WHERE setting_key = :key');
        $update->execute([
            'key' => $key,
            'value' => $value,
        ]);

        if ($update->rowCount() === 0) {
            $exists = $this->pdo->prepare('SELECT 1 FROM settings WHERE setting_key = :key LIMIT 1');
            $exists->execute(['key' => $key]);

            if ($exists->fetchColumn()) {
                return;
            }

            $insert = $this->pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)');
            $insert->execute([
                'key' => $key,
                'value' => $value,
            ]);
        }
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT setting_key, setting_value FROM settings');
        $pairs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        return $pairs ?: [];
    }
}
