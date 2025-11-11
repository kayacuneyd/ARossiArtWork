<?php
/** @var string $body */
use App\Support\View;
use App\Support\Flash;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Alexandre Mike Rossi Artworks', ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription ?? 'Contemporary art portfolio', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/tailwind.css'); ?>">
</head>
<body class="min-h-screen flex flex-col bg-canvas text-ink">
    <header class="border-b border-zinc-200 bg-white/80 backdrop-blur">
        <div class="mx-auto max-w-6xl px-4 py-5 flex items-center justify-between">
            <a href="/" class="text-2xl font-display tracking-tight">Alexandre Mike Rossi</a>
            <nav class="hidden md:flex gap-6 text-sm uppercase tracking-[0.2em]">
                <a href="/" class="hover:text-zinc-900 transition">Gallery</a>
                <a href="/contact" class="hover:text-zinc-900 transition">Contact</a>
                <a href="/admin" class="hover:text-zinc-900 transition">Admin</a>
            </nav>
            <button type="button" class="btn-primary md:hidden" data-open-contact>
                Contact
            </button>
        </div>
    </header>
    <main class="flex-1">
        <?php $flashes = $flash ?? Flash::all(); ?>
        <?php if (!empty($flashes)): ?>
            <div class="mx-auto max-w-4xl px-4 pt-6">
                <?php foreach ($flashes as $type => $messages): ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="mb-3 rounded-md border px-4 py-3 text-sm <?= $type === 'error' ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700'; ?>">
                            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?= $body ?? ''; ?>
    </main>
    <footer class="border-t border-zinc-200 bg-white py-6 text-center text-sm">
        <p class="text-zinc-600">Developed by <a href="https://kayacuneyt.com" class="underline hover:text-zinc-900" target="_blank" rel="noopener">Cüneyt Kaya</a> — <span class="font-medium">Built in kornwestheim</span></p>
    </footer>
    <?= View::fragment('components/contact-modal.php'); ?>
    <script src="<?= asset('assets/js/app.js'); ?>" type="module"></script>
</body>
</html>
