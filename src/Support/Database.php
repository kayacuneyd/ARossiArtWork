<?php

declare(strict_types=1);

namespace App\Support;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $driver = strtolower((string) env('DB_CONNECTION', 'mysql'));

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$pdo = $driver === 'sqlite'
                    ? self::connectSqlite($options)
                    : self::connectMysql($options);
            } catch (PDOException $exception) {
                throw new PDOException('Database connection failed: ' . $exception->getMessage(), (int) $exception->getCode(), $exception);
            }
        }

        return self::$pdo;
    }

    private static function connectMysql(array $options): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            env('DB_HOST', '127.0.0.1'),
            env('DB_PORT', '3306'),
            env('DB_NAME', 'arossi_portfolio')
        );

        return new PDO($dsn, env('DB_USER', 'root'), env('DB_PASS', ''), $options);
    }

    private static function connectSqlite(array $options): PDO
    {
        $databasePath = env('DB_DATABASE', base_path('storage/database.sqlite'));
        $databasePath = self::resolvePath($databasePath);

        self::prepareSqliteDatabase($databasePath);

        return new PDO('sqlite:' . $databasePath, null, null, $options);
    }

    private static function resolvePath(string $path): string
    {
        if ($path === '') {
            throw new PDOException('SQLite database path is not configured.');
        }

        $isAbsoluteUnix = str_starts_with($path, DIRECTORY_SEPARATOR);
        $isAbsoluteWindows = (bool) preg_match('/^[A-Za-z]:\\\\/', $path);

        return ($isAbsoluteUnix || $isAbsoluteWindows) ? $path : base_path($path);
    }

    private static function prepareSqliteDatabase(string $databasePath): void
    {
        $directory = dirname($databasePath);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new PDOException('Unable to create SQLite directory: ' . $directory);
        }

        $isNewDatabase = !file_exists($databasePath);
        if ($isNewDatabase && !touch($databasePath)) {
            throw new PDOException('Unable to create SQLite database file: ' . $databasePath);
        }

        $pdo = new PDO('sqlite:' . $databasePath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $schemaFile = base_path('database/sqlite_schema.sql');
        if (!is_file($schemaFile)) {
            throw new PDOException('SQLite schema file not found at ' . $schemaFile);
        }

        $sql = file_get_contents($schemaFile);
        if ($sql === false) {
            throw new PDOException('Unable to read SQLite schema file.');
        }

        $pdo->exec($sql);

        if ($isNewDatabase) {
            self::seedSqliteDefaults($pdo);
        }
    }

    private static function seedSqliteDefaults(PDO $pdo): void
    {
        $settingsCount = (int) $pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn();
        if ($settingsCount === 0) {
            $stmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)');
            $defaults = [
                'artist_email' => 'artist@example.com',
                'whatsapp_number' => '+447483284919',
                'max_upload_mb' => '8',
            ];

            foreach ($defaults as $key => $value) {
                $stmt->execute([
                    'key' => $key,
                    'value' => $value,
                ]);
            }
        }

        $artworkCount = (int) $pdo->query('SELECT COUNT(*) FROM artworks')->fetchColumn();
        if ($artworkCount === 0) {
            $samples = [
                [
                    'uuid' => '11111111-1111-1111-1111-111111111111',
                    'title' => 'Placeholder Sunrise',
                    'slug' => 'placeholder-sunrise',
                    'description' => 'Warm gradients representing a sunrise over the Bournemouth coast.',
                    'year' => 2023,
                    'technique' => 'Acrylic on canvas',
                    'dimensions' => '60 x 80 cm',
                    'price' => null,
                    'currency' => 'GBP',
                    'image_path' => 'uploads/sample-01.png',
                    'thumbnail_path' => 'uploads/thumbs/sample-01.png',
                    'webp_path' => null,
                    'is_featured' => 1,
                    'is_published' => 1,
                    'display_order' => 3,
                    'metadata' => [
                        'technique' => 'Acrylic on canvas',
                        'dimensions' => '60 x 80 cm',
                    ],
                ],
                [
                    'uuid' => '22222222-2222-2222-2222-222222222222',
                    'title' => 'Azure Coastline',
                    'slug' => 'azure-coastline',
                    'description' => 'Cool-toned study with layered blues and subtle texture.',
                    'year' => 2022,
                    'technique' => 'Oil on canvas',
                    'dimensions' => '50 x 70 cm',
                    'price' => null,
                    'currency' => 'GBP',
                    'image_path' => 'uploads/sample-02.png',
                    'thumbnail_path' => 'uploads/thumbs/sample-02.png',
                    'webp_path' => null,
                    'is_featured' => 0,
                    'is_published' => 1,
                    'display_order' => 2,
                    'metadata' => [
                        'technique' => 'Oil on canvas',
                        'dimensions' => '50 x 70 cm',
                    ],
                ],
                [
                    'uuid' => '33333333-3333-3333-3333-333333333333',
                    'title' => 'Monochrome Sketch',
                    'slug' => 'monochrome-sketch',
                    'description' => 'Quick monochrome study hinting at future commissions.',
                    'year' => 2024,
                    'technique' => 'Charcoal and gesso',
                    'dimensions' => '42 x 59 cm',
                    'price' => null,
                    'currency' => 'GBP',
                    'image_path' => 'uploads/sample-03.png',
                    'thumbnail_path' => 'uploads/thumbs/sample-03.png',
                    'webp_path' => null,
                    'is_featured' => 0,
                    'is_published' => 1,
                    'display_order' => 1,
                    'metadata' => [
                        'technique' => 'Charcoal and gesso',
                        'dimensions' => '42 x 59 cm',
                    ],
                ],
            ];

            $stmt = $pdo->prepare(
                'INSERT INTO artworks (uuid, title, slug, description, year, technique, dimensions, price, currency, image_path, thumbnail_path, webp_path, is_featured, is_published, display_order, metadata)
                 VALUES (:uuid, :title, :slug, :description, :year, :technique, :dimensions, :price, :currency, :image_path, :thumbnail_path, :webp_path, :is_featured, :is_published, :display_order, :metadata)'
            );

            foreach ($samples as $sample) {
                $stmt->execute([
                    'uuid' => $sample['uuid'],
                    'title' => $sample['title'],
                    'slug' => $sample['slug'],
                    'description' => $sample['description'],
                    'year' => $sample['year'],
                    'technique' => $sample['technique'],
                    'dimensions' => $sample['dimensions'],
                    'price' => $sample['price'],
                    'currency' => $sample['currency'],
                    'image_path' => $sample['image_path'],
                    'thumbnail_path' => $sample['thumbnail_path'],
                    'webp_path' => $sample['webp_path'],
                    'is_featured' => $sample['is_featured'],
                    'is_published' => $sample['is_published'],
                    'display_order' => $sample['display_order'],
                    'metadata' => json_encode($sample['metadata'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            }
        }
    }
}
