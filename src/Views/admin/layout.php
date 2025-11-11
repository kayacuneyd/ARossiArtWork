<?php
/** @var string $body */
use App\Support\Flash;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Admin — Alexandre Mike Rossi Artworks', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/tailwind.css'); ?>">
</head>
<body class="min-h-screen bg-zinc-50 text-ink">
    <div class="mx-auto flex min-h-screen max-w-6xl flex-col px-4 py-10">
        <header class="flex items-center justify-between pb-8">
            <h1 class="text-2xl font-display">Alexandre Mike Rossi Admin</h1>
            <?php if (!empty($_SESSION['admin_username'])): ?>
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-zinc-500">Signed in as <?= htmlspecialchars($_SESSION['admin_username'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <form method="post" action="/admin/logout" class="inline">
                        <?= csrf_field(); ?>
                        <button class="text-sm text-rose-600 hover:text-rose-700" type="submit">Sign out</button>
                    </form>
                </div>
            <?php endif; ?>
        </header>
        <?php $flashes = $flash ?? Flash::all(); ?>
        <?php if (!empty($flashes)): ?>
            <div class="space-y-2 pb-6">
                <?php foreach ($flashes as $type => $messages): ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="rounded-md border px-4 py-3 text-sm <?= $type === 'error' ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700'; ?>">
                            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <main class="flex-1">
            <?= $body ?? ''; ?>
        </main>
        <footer class="mt-12 border-t border-zinc-200 pt-6 text-center text-xs text-zinc-500">
            &copy; <?= date('Y'); ?> Alexandre Mike Rossi Artworks
        </footer>
    </div>
</body>
</html>
