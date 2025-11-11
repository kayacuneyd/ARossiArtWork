<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Database;
use PDO;

class ArtworkRepository
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
        $uuid = $data['uuid'] ?? uuid_str();
        $displayOrder = $data['display_order'] ?? $this->nextDisplayOrder();

        $stmt = $this->pdo->prepare(
            'INSERT INTO artworks (uuid, title, slug, description, year, technique, dimensions, price, currency, image_path, thumbnail_path, webp_path, is_featured, is_published, display_order, metadata)
             VALUES (:uuid, :title, :slug, :description, :year, :technique, :dimensions, :price, :currency, :image_path, :thumbnail_path, :webp_path, :is_featured, :is_published, :display_order, :metadata)'
        );

        $stmt->execute([
            'uuid' => $uuid,
            'title' => $data['title'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'year' => $data['year'] ?? null,
            'technique' => $data['technique'] ?? null,
            'dimensions' => $data['dimensions'] ?? null,
            'price' => $data['price'] ?? null,
            'currency' => $data['currency'] ?? 'GBP',
            'image_path' => $data['image_path'],
            'thumbnail_path' => $data['thumbnail_path'],
            'webp_path' => $data['webp_path'] ?? null,
            'is_featured' => $data['is_featured'] ?? 0,
            'is_published' => $data['is_published'] ?? 0,
            'display_order' => $displayOrder,
            'metadata' => $data['metadata'] ?? null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        foreach ($data as $column => $value) {
            $fields[] = sprintf('%s = :%s', $column, $column);
            $params[$column] = $value;
        }

        if (empty($fields)) {
            return false;
        }

        $sql = 'UPDATE artworks SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM artworks WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM artworks WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM artworks WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findByUuid(string $uuid): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM artworks WHERE uuid = :uuid LIMIT 1');
        $stmt->execute(['uuid' => $uuid]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function allPublished(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM artworks WHERE is_published = 1 ORDER BY display_order DESC, created_at DESC');
        return $stmt->fetchAll();
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM artworks ORDER BY display_order DESC, created_at DESC');
        return $stmt->fetchAll();
    }

    public function reorder(array $orders): void
    {
        $stmt = $this->pdo->prepare('UPDATE artworks SET display_order = :display_order WHERE id = :id');
        foreach ($orders as $id => $order) {
            $stmt->execute([
                'id' => (int) $id,
                'display_order' => (int) $order,
            ]);
        }
    }

    public function markFeatured(int $id, bool $featured): bool
    {
        $stmt = $this->pdo->prepare('UPDATE artworks SET is_featured = :featured WHERE id = :id');
        return $stmt->execute([
            'id' => $id,
            'featured' => $featured ? 1 : 0,
        ]);
    }

    public function publish(int $id, bool $publish): bool
    {
        $stmt = $this->pdo->prepare('UPDATE artworks SET is_published = :publish WHERE id = :id');
        return $stmt->execute([
            'id' => $id,
            'publish' => $publish ? 1 : 0,
        ]);
    }

    private function nextDisplayOrder(): int
    {
        $stmt = $this->pdo->query('SELECT MAX(display_order) as max_order FROM artworks');
        $max = $stmt->fetchColumn();
        return ((int) $max) + 1;
    }
}
