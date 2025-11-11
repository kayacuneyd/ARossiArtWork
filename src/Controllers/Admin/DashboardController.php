<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ArtworkRepository;
use App\Models\InquiryRepository;
use App\Models\SettingRepository;
use App\Services\ImageService;
use App\Support\AdminAuth;
use App\Support\Flash;
use App\Support\View;
use App\Support\Settings;
use Respect\Validation\Validator as v;

class DashboardController
{
    public function __construct(
        private readonly AdminAuth $auth,
        private readonly ArtworkRepository $artworks,
        private readonly InquiryRepository $inquiries,
        private readonly SettingRepository $settings,
        private readonly ImageService $images
    ) {
    }

    public static function make(): self
    {
        return new self(
            AdminAuth::make(),
            ArtworkRepository::make(),
            InquiryRepository::make(),
            SettingRepository::make(),
            new ImageService()
        );
    }

    public function index(): void
    {
        $this->ensureAuthenticated();

        $artworks = $this->artworks->all();
        $recentInquiries = $this->inquiries->recent();
        $settings = $this->settings->all();

        View::render('admin/dashboard.php', [
            'title' => 'Dashboard — Alexandre Mike Rossi Artworks',
            'artworks' => $artworks,
            'inquiries' => $recentInquiries,
            'settings' => $settings,
            'flash' => Flash::all(),
        ], 'admin/layout.php');
    }

    public function storeArtwork(): void
    {
        $this->ensureAuthenticated();
        $this->assertCsrf();

        $validator = v::key('title', v::stringType()->length(1, 180))
            ->key('description', v::optional(v::stringType()->length(0, null)))
            ->key('year', v::optional(v::oneOf(v::intType()->between(1900, (int) date('Y')), v::nullType())))
            ->key('technique', v::optional(v::stringType()->length(0, 180)))
            ->key('dimensions', v::optional(v::stringType()->length(0, 120)))
            ->key('price', v::optional(v::floatVal()->min(0)))
            ->key('currency', v::optional(v::stringType()->length(3, 3)));

        $year = $_POST['year'] ?? null;
        $year = $year === '' ? null : (int) $year;
        $price = $_POST['price'] ?? null;
        $price = $price === '' ? null : (float) $price;

        $input = [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'year' => $year,
            'technique' => trim((string) ($_POST['technique'] ?? '')),
            'dimensions' => trim((string) ($_POST['dimensions'] ?? '')),
            'price' => $price,
            'currency' => strtoupper((string) ($_POST['currency'] ?? 'GBP')),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
        ];

        try {
            $validator->assert($input);
        } catch (\InvalidArgumentException $exception) {
            Flash::set('error', 'Please correct the highlighted fields and try again.');
            redirect('/admin');
        }

        if (empty($_FILES['image']['tmp_name'] ?? null)) {
            Flash::set('error', 'Please select an image to upload.');
            redirect('/admin');
        }

        try {
            $processed = $this->images->processUpload($_FILES['image']);
        } catch (\Throwable $exception) {
            Flash::set('error', $exception->getMessage());
            redirect('/admin');
        }

        $slug = $this->generateUniqueSlug($input['title']);

        $metadata = json_encode([
            'year' => $input['year'],
            'technique' => $input['technique'] ?: null,
            'dimensions' => $input['dimensions'] ?: null,
            'price' => $input['price'] !== null ? $input['price'] : null,
            'currency' => $input['currency'] ?: 'GBP',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $this->artworks->create([
            'uuid' => $processed['uuid'],
            'title' => $input['title'],
            'slug' => $slug,
            'description' => $input['description'],
            'year' => $input['year'],
            'technique' => $input['technique'] ?: null,
            'dimensions' => $input['dimensions'] ?: null,
            'price' => $input['price'],
            'currency' => $input['currency'] ?: 'GBP',
            'image_path' => $processed['image_path'],
            'thumbnail_path' => $processed['thumbnail_path'],
            'webp_path' => $processed['webp_path'],
            'is_featured' => $input['is_featured'],
            'is_published' => $input['is_published'],
            'metadata' => $metadata,
        ]);

        Flash::set('success', 'Artwork uploaded successfully.');
        redirect('/admin');
    }

    public function updateArtwork(int $id): void
    {
        $this->ensureAuthenticated();
        $this->assertCsrf();

        $existing = $this->artworks->findById($id);
        if (!$existing) {
            Flash::set('error', 'Artwork not found.');
            redirect('/admin');
        }

        $yearRaw = $_POST['year'] ?? '';
        $priceRaw = $_POST['price'] ?? '';
        $input = [
            'title' => trim((string) ($_POST['title'] ?? $existing['title'])),
            'description' => trim((string) ($_POST['description'] ?? $existing['description'] ?? '')),
            'year' => $yearRaw === '' ? null : (int) $yearRaw,
            'technique' => trim((string) ($_POST['technique'] ?? '')),
            'dimensions' => trim((string) ($_POST['dimensions'] ?? '')),
            'price' => $priceRaw === '' ? null : (float) $priceRaw,
            'currency' => strtoupper((string) ($_POST['currency'] ?? ($existing['currency'] ?? 'GBP'))),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
        ];

        $validator = v::key('title', v::stringType()->length(1, 180))
            ->key('description', v::optional(v::stringType()))
            ->key('year', v::optional(v::oneOf(v::intType()->between(1900, (int) date('Y')), v::nullType())))
            ->key('technique', v::optional(v::stringType()->length(0, 180)))
            ->key('dimensions', v::optional(v::stringType()->length(0, 120)))
            ->key('price', v::optional(v::floatVal()->min(0)))
            ->key('currency', v::optional(v::stringType()->length(3, 3)));

        try {
            $validator->assert($input);
        } catch (\InvalidArgumentException $exception) {
            Flash::set('error', 'Please correct the form fields.');
            redirect('/admin');
        }

        $data = $input;
        if (!empty($_FILES['image']['tmp_name'] ?? null)) {
            try {
                $processed = $this->images->processUpload($_FILES['image']);
                $data['image_path'] = $processed['image_path'];
                $data['thumbnail_path'] = $processed['thumbnail_path'];
                $data['webp_path'] = $processed['webp_path'];
            } catch (\Throwable $exception) {
                Flash::set('error', $exception->getMessage());
                redirect('/admin');
            }
        }

        if ($input['title'] !== $existing['title']) {
            $data['slug'] = $this->generateUniqueSlug($input['title'], (int) $existing['id']);
        }

        $metadata = json_encode([
            'year' => $input['year'],
            'technique' => $input['technique'] ?: null,
            'dimensions' => $input['dimensions'] ?: null,
            'price' => $input['price'] !== null ? $input['price'] : null,
            'currency' => $input['currency'] ?: 'GBP',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $data['metadata'] = $metadata;

        $this->artworks->update($id, $data);

        Flash::set('success', 'Artwork updated.');
        redirect('/admin');
    }

    public function deleteArtwork(int $id): void
    {
        $this->ensureAuthenticated();
        $this->assertCsrf();

        $this->artworks->delete($id);
        Flash::set('success', 'Artwork removed.');
        redirect('/admin');
    }

    public function reorder(): void
    {
        $this->ensureAuthenticated();
        $this->assertCsrf();

        $orders = $_POST['order'] ?? [];
        $this->artworks->reorder($orders);
        Flash::set('success', 'Display order updated.');
        redirect('/admin');
    }

    public function togglePublish(int $id): void
    {
        $this->ensureAuthenticated();
        $this->assertCsrf();
        $publish = isset($_POST['publish']) && (int) $_POST['publish'] === 1;
        $this->artworks->publish($id, $publish);
        Flash::set('success', 'Publish state updated.');
        redirect('/admin');
    }

    public function toggleFeatured(int $id): void
    {
        $this->ensureAuthenticated();
        $this->assertCsrf();
        $featured = isset($_POST['featured']) && (int) $_POST['featured'] === 1;
        $this->artworks->markFeatured($id, $featured);
        Flash::set('success', 'Featured flag updated.');
        redirect('/admin');
    }

    public function updateSettings(): void
    {
        $this->ensureAuthenticated();
        $this->assertCsrf();

        $input = [
            'artist_email' => trim((string) ($_POST['artist_email'] ?? '')),
            'whatsapp_number' => trim((string) ($_POST['whatsapp_number'] ?? '')),
            'max_upload_mb' => trim((string) ($_POST['max_upload_mb'] ?? '8')),
        ];

        $validator = v::key('artist_email', v::email())
            ->key('whatsapp_number', v::regex('/^\+?[1-9]\d{6,15}$/'))
            ->key('max_upload_mb', v::intVal()->between(1, 32));

        try {
            $validator->assert($input);
        } catch (\InvalidArgumentException $exception) {
            Flash::set('error', 'Settings validation failed.');
            redirect('/admin');
        }

        $this->settings->set('artist_email', $input['artist_email']);
        $this->settings->set('whatsapp_number', $input['whatsapp_number']);
        $this->settings->set('max_upload_mb', $input['max_upload_mb']);
        $_ENV['MAX_UPLOAD_MB'] = (string) $input['max_upload_mb'];
        $_ENV['ARTIST_EMAIL'] = $input['artist_email'];
        $_ENV['WHATSAPP_NUMBER'] = $input['whatsapp_number'];
        Settings::refresh();

        Flash::set('success', 'Settings saved.');
        redirect('/admin');
    }

    private function ensureAuthenticated(): void
    {
        if (!$this->auth->check()) {
            redirect('/admin/login');
        }
    }

    private function assertCsrf(): void
    {
        if (!verify_csrf($_POST['_token'] ?? null)) {
            Flash::set('error', 'Invalid session token.');
            redirect('/admin');
        }
    }

    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = str_slug($title);
        $slug = $base;
        $counter = 1;

        while ($existing = $this->artworks->findBySlug($slug)) {
            if ($ignoreId !== null && (int) $existing['id'] === $ignoreId) {
                break;
            }
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
