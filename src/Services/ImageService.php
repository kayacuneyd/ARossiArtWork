<?php

declare(strict_types=1);

namespace App\Services;

use Intervention\Image\ImageManager;
use RuntimeException;

class ImageService
{
    private ImageManager $manager;

    public function __construct(?ImageManager $manager = null)
    {
        if ($manager) {
            $this->manager = $manager;
            return;
        }

        $driver = extension_loaded('imagick') ? 'imagick' : 'gd';
        $this->manager = new ImageManager(['driver' => $driver]);
    }

    public function processUpload(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Image upload failed.');
        }

        $maxSizeMb = (int) env('MAX_UPLOAD_MB', 8);
        $maxBytes = $maxSizeMb * 1024 * 1024;
        if (($file['size'] ?? 0) > $maxBytes) {
            throw new RuntimeException('File exceeds maximum size of ' . $maxSizeMb . 'MB.');
        }

        $mime = $this->detectMime($file['tmp_name']);
        $extension = $this->extensionFromMime($mime);

        $baseName = $this->generateFilename();
        $uuid = uuid_str();

        $paths = $this->buildPaths($baseName, $extension);
        $this->ensureDirectories($paths);

        $image = $this->manager->make($file['tmp_name'])->orientate();
        if ($image->width() > 2048) {
            $image->resize(2048, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        $encodedOriginal = (string) $image->encode($extension, 90);
        file_put_contents($paths['storage_original'], $encodedOriginal);
        file_put_contents($paths['public_original'], $encodedOriginal);

        $thumbImage = clone $image;
        $thumbImage->resize(600, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $encodedThumb = (string) $thumbImage->encode('jpg', 85);
        file_put_contents($paths['storage_thumb'], $encodedThumb);
        file_put_contents($paths['public_thumb'], $encodedThumb);

        $webpPath = null;
        if ($this->canEncodeWebp()) {
            $webpImage = clone $image;
            $encodedWebp = (string) $webpImage->encode('webp', 80);
            file_put_contents($paths['storage_webp'], $encodedWebp);
            file_put_contents($paths['public_webp'], $encodedWebp);
            $webpPath = $paths['relative_webp'];
        }

        return [
            'uuid' => $uuid,
            'image_path' => $paths['relative_original'],
            'thumbnail_path' => $paths['relative_thumb'],
            'webp_path' => $webpPath,
            'mime_type' => $mime,
            'width' => $image->width(),
            'height' => $image->height(),
        ];
    }

    private function detectMime(string $filePath): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filePath) ?: '';
        finfo_close($finfo);

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowed, true)) {
            throw new RuntimeException('Unsupported image format. Allowed: JPG, PNG, WebP.');
        }

        return $mime;
    }

    private function extensionFromMime(string $mime): string
    {
        return match ($mime) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
    }

    private function generateFilename(): string
    {
        return date('YmdHis') . '_' . bin2hex(random_bytes(6));
    }

    private function buildPaths(string $baseName, string $extension): array
    {
        $storageDir = base_path('storage/uploads');
        $storageThumbDir = base_path('storage/thumbs');
        $storageWebpDir = base_path('storage/webp');

        $publicDir = base_path('public/uploads');
        $publicThumbDir = base_path('public/uploads/thumbs');
        $publicWebpDir = base_path('public/uploads/webp');

        $originalFilename = $baseName . '.' . $extension;
        $thumbFilename = $baseName . '_thumb.jpg';
        $webpFilename = $baseName . '.webp';

        return [
            'storage_original' => $storageDir . '/' . $originalFilename,
            'storage_thumb' => $storageThumbDir . '/' . $thumbFilename,
            'storage_webp' => $storageWebpDir . '/' . $webpFilename,
            'public_original' => $publicDir . '/' . $originalFilename,
            'public_thumb' => $publicThumbDir . '/' . $thumbFilename,
            'public_webp' => $publicWebpDir . '/' . $webpFilename,
            'relative_original' => 'uploads/' . $originalFilename,
            'relative_thumb' => 'uploads/thumbs/' . $thumbFilename,
            'relative_webp' => 'uploads/webp/' . $webpFilename,
        ];
    }

    private function ensureDirectories(array $paths): void
    {
        $directories = [
            dirname($paths['storage_original']),
            dirname($paths['storage_thumb']),
            dirname($paths['storage_webp']),
            dirname($paths['public_original']),
            dirname($paths['public_thumb']),
            dirname($paths['public_webp']),
        ];

        foreach ($directories as $directory) {
            if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
                throw new RuntimeException('Unable to create directory: ' . $directory);
            }
        }
    }

    private function canEncodeWebp(): bool
    {
        if (extension_loaded('imagick')) {
            return true;
        }

        return function_exists('imagewebp');
    }
}
