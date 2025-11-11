<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ArtworkRepository;
use App\Models\InquiryRepository;
use App\Support\Flash;
use App\Support\Settings;
use App\Support\View;
use Respect\Validation\Validator as v;

class SiteController
{
    public function __construct(
        private readonly ArtworkRepository $artworks,
        private readonly InquiryRepository $inquiries
    ) {
    }

    public static function make(): self
    {
        return new self(ArtworkRepository::make(), InquiryRepository::make());
    }

    public function home(): void
    {
        $artworks = $this->artworks->allPublished();
        $settings = Settings::all();

        View::render('pages/home.php', [
            'title' => 'Alexandre Mike Rossi Artworks — Bournemouth Painter Portfolio',
            'metaDescription' => 'Browse original artworks by Alexandre Mike Rossi, a Bournemouth-based painter available for commissions.',
            'artworks' => $artworks,
            'settings' => $settings,
            'flash' => Flash::all(),
        ]);
    }

    public function artwork(string $slug): void
    {
        $artwork = $this->artworks->findBySlug($slug);
        if (!$artwork || !(int) $artwork['is_published']) {
            http_response_code(404);
            View::render('pages/not-found.php', [
                'title' => 'Artwork not found',
                'metaDescription' => 'The requested artwork could not be located.',
            ]);
            return;
        }

        View::render('pages/artwork.php', [
            'title' => $artwork['title'] . ' — Alexandre Mike Rossi Artworks',
            'metaDescription' => substr(strip_tags((string) $artwork['description']), 0, 150),
            'artwork' => $artwork,
        ]);
    }

    public function contact(): void
    {
        $settings = Settings::all();
        View::render('pages/contact.php', [
            'title' => 'Contact & WhatsApp Requests — Alexandre Mike Rossi Artworks',
            'metaDescription' => 'Start a commission or inquire about available artworks via WhatsApp or email.',
            'settings' => $settings,
            'flash' => Flash::all(),
        ]);
    }

    public function submitInquiry(): void
    {
        if (!verify_csrf($_POST['_token'] ?? null)) {
            Flash::set('error', 'Security token mismatch. Please submit the form again.');
            redirect('/contact');
        }

        $input = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'message' => trim((string) ($_POST['message'] ?? '')),
            'preferred_size' => trim((string) ($_POST['preferred_size'] ?? '')),
            'artwork_title' => trim((string) ($_POST['artwork_title'] ?? '')),
            'artwork_slug' => trim((string) ($_POST['artwork_slug'] ?? '')),
        ];

        $validator = v::key('name', v::stringType()->length(2, 180))
            ->key('email', v::email())
            ->key('phone', v::optional(v::regex('/^\+?[0-9\s()-]{7,20}$/')))
            ->key('message', v::stringType()->length(10, null))
            ->key('preferred_size', v::optional(v::stringType()->length(0, 180)))
            ->key('artwork_title', v::optional(v::stringType()->length(0, 180)))
            ->key('artwork_slug', v::optional(v::regex('/^[a-z0-9-]{1,200}$/')));

        try {
            $validator->assert($input);
        } catch (\InvalidArgumentException $exception) {
            Flash::set('error', 'Please check the form and try again.');
            redirect('/contact');
        }

        $whatsappNumber = Settings::get('whatsapp_number', env('WHATSAPP_NUMBER', ''));
        if (!$whatsappNumber) {
            Flash::set('error', 'WhatsApp number is not configured.');
            redirect('/contact');
        }

        $linkedArtwork = null;
        if ($input['artwork_slug']) {
            $linkedArtwork = $this->artworks->findBySlug($input['artwork_slug']);
        }
        if ($linkedArtwork) {
            $input['artwork_title'] = $linkedArtwork['title'];
        }

        $messageTemplate = $this->buildWhatsAppMessage($input, $linkedArtwork);

        $this->inquiries->create([
            'artwork_id' => $linkedArtwork['id'] ?? null,
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'] ?: null,
            'preferred_size' => $input['preferred_size'] ?: null,
            'message' => $input['message'],
            'whatsapp_message' => $messageTemplate,
        ]);

        $this->sendEmailNotification($input, $messageTemplate);

        $whatsappUrl = sprintf(
            'https://api.whatsapp.com/send?phone=%s&text=%s',
            rawurlencode($whatsappNumber),
            rawurlencode($messageTemplate)
        );

        redirect($whatsappUrl);
    }

    private function buildWhatsAppMessage(array $input, ?array $artwork): string
    {
        $lines = [
            'Hello Alexandre Mike Rossi!',
            'My name is ' . $input['name'] . '.',
            'Email: ' . $input['email'],
        ];

        if ($artwork || !empty($input['artwork_title'])) {
            $title = $artwork['title'] ?? $input['artwork_title'];
            $lines[] = 'Artwork: ' . $title;
        }

        if (!empty($input['phone'])) {
            $lines[] = 'Phone: ' . $input['phone'];
        }

        if (!empty($input['preferred_size'])) {
            $lines[] = 'Preferred size/colour: ' . $input['preferred_size'];
        }

        $lines[] = 'Message:';
        $lines[] = $input['message'];

        return implode("\n", $lines);
    }

    private function sendEmailNotification(array $input, string $messageTemplate): void
    {
        $artistEmail = Settings::get('artist_email', env('ARTIST_EMAIL', ''));
        if (!$artistEmail) {
            return;
        }

        $subject = 'New inquiry via Alexandre Mike Rossi portfolio';
        $body = "<p>You have received a new inquiry from the portfolio website.</p>" .
            '<p><strong>Name:</strong> ' . htmlspecialchars($input['name'], ENT_QUOTES, 'UTF-8') . '</p>' .
            '<p><strong>Email:</strong> ' . htmlspecialchars($input['email'], ENT_QUOTES, 'UTF-8') . '</p>' .
            (!empty($input['phone']) ? '<p><strong>Phone:</strong> ' . htmlspecialchars($input['phone'], ENT_QUOTES, 'UTF-8') . '</p>' : '') .
            (!empty($input['artwork_title']) ? '<p><strong>Artwork:</strong> ' . htmlspecialchars($input['artwork_title'], ENT_QUOTES, 'UTF-8') . '</p>' : '') .
            (!empty($input['preferred_size']) ? '<p><strong>Preferred size/colour:</strong> ' . htmlspecialchars($input['preferred_size'], ENT_QUOTES, 'UTF-8') . '</p>' : '') .
            '<p><strong>Message:</strong><br>' . nl2br(htmlspecialchars($input['message'], ENT_QUOTES, 'UTF-8')) . '</p>' .
            '<p><strong>WhatsApp preview:</strong><br>' . nl2br(htmlspecialchars($messageTemplate, ENT_QUOTES, 'UTF-8')) . '</p>';

        $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            if ($host = env('MAIL_HOST')) {
                $mailer->isSMTP();
                $mailer->Host = $host;
                $mailer->Port = (int) env('MAIL_PORT', 587);
                $mailer->SMTPAuth = !empty(env('MAIL_USERNAME'));
                if ($mailer->SMTPAuth) {
                    $mailer->Username = env('MAIL_USERNAME');
                    $mailer->Password = env('MAIL_PASSWORD');
                }
                $mailer->SMTPSecure = env('MAIL_ENCRYPTION') ?: \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }

            $fromAddress = env('MAIL_FROM_ADDRESS', $artistEmail);
            $fromName = env('MAIL_FROM_NAME', 'Alexandre Mike Rossi Artworks');

            $mailer->setFrom($fromAddress, $fromName);
            $mailer->addAddress($artistEmail);
            $mailer->addReplyTo($input['email'], $input['name']);
            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body = $body;
            $mailer->AltBody = strip_tags(str_replace('<br>', "\n", $body));
            $mailer->send();
        } catch (\Throwable $exception) {
            // Silently fail to avoid blocking the flow. Consider logging in production.
        }
    }
}
