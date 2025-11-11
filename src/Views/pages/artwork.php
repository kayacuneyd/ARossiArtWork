<?php
/** @var array $artwork */
$meta = json_decode($artwork['metadata'] ?? '{}', true) ?: [];
?>
<section class="bg-white">
    <div class="mx-auto max-w-5xl px-4 py-16">
        <a href="/" class="text-sm text-zinc-500 hover:text-ink">← Back to gallery</a>
        <div class="mt-8 grid gap-8 md:grid-cols-2">
            <div class="overflow-hidden rounded-2xl border border-zinc-200 shadow">
                <picture>
                    <?php if (!empty($artwork['webp_path'])): ?>
                        <source srcset="/<?= htmlspecialchars($artwork['webp_path'], ENT_QUOTES, 'UTF-8'); ?>" type="image/webp">
                    <?php endif; ?>
                    <img src="/<?= htmlspecialchars($artwork['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($artwork['title'], ENT_QUOTES, 'UTF-8'); ?>" class="h-full w-full object-cover" loading="lazy">
                </picture>
            </div>
            <div class="flex flex-col gap-6">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Artwork</p>
                    <h1 class="mt-2 font-display text-4xl text-ink"><?= htmlspecialchars($artwork['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
                    <?php if (!empty($meta['year'])): ?>
                        <p class="text-sm text-zinc-500">Created in <?= htmlspecialchars((string) $meta['year'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                </div>
                <?php if (!empty($artwork['description'])): ?>
                    <p class="text-base leading-relaxed text-zinc-600"><?= nl2br(htmlspecialchars($artwork['description'], ENT_QUOTES, 'UTF-8')); ?></p>
                <?php endif; ?>
                <dl class="grid gap-3 text-sm text-zinc-600">
                    <?php if (!empty($meta['technique'])): ?>
                        <div><dt class="font-medium text-ink">Technique</dt><dd><?= htmlspecialchars($meta['technique'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
                    <?php endif; ?>
                    <?php if (!empty($meta['dimensions'])): ?>
                        <div><dt class="font-medium text-ink">Dimensions</dt><dd><?= htmlspecialchars($meta['dimensions'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
                    <?php endif; ?>
                    <?php if (!empty($meta['price'])): ?>
                        <div><dt class="font-medium text-ink">Price</dt><dd>£<?= number_format((float) $meta['price'], 2); ?></dd></div>
                    <?php endif; ?>
                </dl>
                <div class="mt-auto flex gap-3">
                    <button type="button" class="btn-primary" data-open-contact>Request via WhatsApp</button>
                    <a href="/contact" class="rounded-md border border-zinc-300 px-4 py-2 text-sm">Contact page</a>
                </div>
            </div>
        </div>
    </div>
</section>
