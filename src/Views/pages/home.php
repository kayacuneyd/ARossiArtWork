<?php
use App\Support\Settings;
/** @var array $artworks */
/** @var array $settings */
$featured = array_filter($artworks, static fn ($art) => (int) ($art['is_featured'] ?? 0) === 1);
$featuredArtwork = $featured ? reset($featured) : ($artworks[0] ?? null);
?>
<section class="relative overflow-hidden bg-white">
    <div class="mx-auto flex max-w-6xl flex-col-reverse gap-12 px-4 py-16 lg:flex-row lg:items-center">
        <div class="flex-1">
            <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">Contemporary Art</p>
            <h1 class="mt-4 text-4xl font-display md:text-5xl">Textures from Bournemouth by Alexandre Mike Rossi</h1>
            <p class="mt-6 max-w-xl text-lg text-zinc-600">
                <?= !empty($featuredArtwork['description'])
                    ? htmlspecialchars(mb_strimwidth($featuredArtwork['description'], 0, 220, '…'), ENT_QUOTES, 'UTF-8')
                    : 'Explore abstract textures, vibrant palettes, and commission-ready canvases by Bournemouth-based painter Alexandre Mike Rossi.'; ?>
            </p>
            <div class="mt-8 flex flex-wrap gap-4">
                <a href="#gallery" class="btn-primary">View gallery</a>
                <button type="button" class="inline-flex items-center gap-2 rounded-md border border-ink px-4 py-2 text-sm uppercase tracking-wide" data-open-contact>
                    Request via WhatsApp
                </button>
            </div>
        </div>
        <div class="flex-1">
            <?php if ($featuredArtwork): ?>
                <div class="relative">
                    <img src="/<?= htmlspecialchars($featuredArtwork['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($featuredArtwork['title'], ENT_QUOTES, 'UTF-8'); ?>" class="h-auto w-full rounded-2xl border border-zinc-200 object-cover shadow-xl" loading="lazy">
                    <div class="absolute bottom-4 left-4 rounded-md bg-white/80 px-4 py-3 shadow">
                        <p class="text-xs uppercase tracking-[0.2em] text-zinc-500">Featured</p>
                        <p class="font-display text-lg"><?= htmlspecialchars($featuredArtwork['title'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid h-72 place-items-center rounded-2xl border border-dashed border-zinc-200 bg-zinc-50 text-sm text-zinc-400">
                    Artwork showcase coming soon.
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section id="gallery" class="bg-canvas">
    <div class="mx-auto max-w-6xl px-4 py-16">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="text-3xl font-display">Gallery</h2>
                <p class="text-zinc-500">Scroll through the latest paintings, mixed media, and commissions.</p>
            </div>
            <a href="/contact" class="text-sm uppercase tracking-[0.2em] text-ink hover:underline">Book a commission</a>
        </div>
        <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($artworks as $artwork): ?>
                <?php $meta = json_decode($artwork['metadata'] ?? '{}', true) ?: []; ?>
                <article class="group cursor-pointer" data-artwork='<?= htmlspecialchars(json_encode([
                    'title' => $artwork['title'],
                    'description' => $artwork['description'] ?? '',
                    'image' => '/' . ltrim($artwork['image_path'], '/'),
                    'thumb' => '/' . ltrim($artwork['thumbnail_path'], '/'),
                    'webp' => $artwork['webp_path'] ? '/' . ltrim($artwork['webp_path'], '/') : null,
                    'meta' => $meta,
                    'slug' => $artwork['slug'],
                ], JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'); ?>'>
                    <div class="relative overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
                        <picture>
                            <?php if (!empty($artwork['webp_path'])): ?>
                                <source srcset="/<?= htmlspecialchars($artwork['webp_path'], ENT_QUOTES, 'UTF-8'); ?>" type="image/webp">
                            <?php endif; ?>
                            <img src="/<?= htmlspecialchars($artwork['thumbnail_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($artwork['title'], ENT_QUOTES, 'UTF-8'); ?>" class="h-64 w-full object-cover transition-transform duration-700 group-hover:scale-105" loading="lazy">
                        </picture>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
                    </div>
                    <div class="mt-4 flex items-start justify-between">
                        <div>
                            <h3 class="font-display text-xl text-ink">
                                <a href="/artwork/<?= htmlspecialchars($artwork['slug'], ENT_QUOTES, 'UTF-8'); ?>" class="hover:underline">
                                    <?= htmlspecialchars($artwork['title'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </h3>
                            <p class="text-sm text-zinc-500">
                                <?= htmlspecialchars(($meta['technique'] ?? 'Mixed media') . ($meta['dimensions'] ? ' · ' . $meta['dimensions'] : ''), ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        </div>
                        <?php if (!empty($artwork['price'])): ?>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-ink shadow">£<?= number_format((float) $artwork['price'], 2); ?></span>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if (empty($artworks)): ?>
            <div class="mt-10 rounded-xl border border-dashed border-zinc-300 bg-white p-6 text-center text-sm text-zinc-500">
                Upload artworks in the admin dashboard to populate the gallery.
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="bg-white">
    <div class="mx-auto max-w-6xl px-4 py-16">
        <div class="grid gap-10 md:grid-cols-2">
            <div>
                <h2 class="text-3xl font-display">Commissions & inquiries</h2>
                <p class="mt-3 text-zinc-600">
                    Ready to discuss a commission or buy a listed piece? Send a WhatsApp with your preferred size, colour palette, and any inspiration. You will receive a quick reply (usually within 24 hours).
                </p>
                <dl class="mt-6 space-y-3 text-sm text-zinc-600">
                    <div>
                        <dt class="font-medium">Email</dt>
                        <dd><?= htmlspecialchars($settings['artist_email'] ?? env('ARTIST_EMAIL', ''), ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium">WhatsApp</dt>
                        <dd><?= htmlspecialchars(Settings::get('whatsapp_number', env('WHATSAPP_NUMBER', '')), ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                </dl>
                <div class="mt-8 flex gap-3">
                    <button type="button" class="btn-primary" data-open-contact>Open WhatsApp form</button>
                    <a href="/contact" class="rounded-md border border-zinc-300 px-4 py-2 text-sm">Contact page</a>
                </div>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-8 shadow-inner">
                <h3 class="font-display text-xl">How it works</h3>
                <ol class="mt-4 space-y-3 text-sm text-zinc-600">
                    <li><span class="font-medium text-ink">1.</span> Fill the WhatsApp form with your idea.</li>
                    <li><span class="font-medium text-ink">2.</span> You are redirected to WhatsApp with the message ready to send.</li>
                    <li><span class="font-medium text-ink">3.</span> Alexandre Mike Rossi replies with availability, pricing, and next steps.</li>
                </ol>
                <p class="mt-6 text-xs text-zinc-500">Prefer email only? Use the contact page — every inquiry is stored securely in the dashboard.</p>
            </div>
        </div>
    </div>
</section>

<div id="artwork-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 px-4" data-modal="artwork">
    <div class="relative max-h-[90vh] w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl">
        <button type="button" class="absolute right-4 top-4 z-10 rounded-full bg-black/70 px-3 py-1 text-sm text-white" data-close-modal>&times;</button>
        <div class="grid gap-0 md:grid-cols-2">
            <div class="relative">
                <picture>
                    <source data-artwork-webp type="image/webp">
                    <img data-artwork-image src="" alt="" class="h-full w-full object-cover" loading="lazy">
                </picture>
            </div>
            <div class="flex flex-col gap-4 p-6">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-zinc-500">Artwork</p>
                    <h3 data-artwork-title class="font-display text-2xl text-ink"></h3>
                </div>
                <p data-artwork-description class="text-sm leading-relaxed text-zinc-600"></p>
                <div data-artwork-meta class="space-y-2 text-sm text-zinc-500"></div>
                <button type="button" class="btn-primary mt-auto w-full" data-open-contact>Request via WhatsApp</button>
            </div>
        </div>
    </div>
</div>
