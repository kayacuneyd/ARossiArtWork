<?php
/** @var array $settings */
?>
<section class="bg-white">
    <div class="mx-auto max-w-4xl px-4 py-16">
        <a href="/" class="text-sm text-zinc-500 hover:text-ink">← Back to gallery</a>
        <div class="mt-8 grid gap-10 md:grid-cols-2">
            <div>
                <h1 class="font-display text-4xl text-ink">Contact Alexandre Mike Rossi</h1>
                <p class="mt-4 text-zinc-600">Fill in the form and you will be redirected to WhatsApp with a message ready to send. Every inquiry is stored securely in the admin dashboard.</p>
                <dl class="mt-6 space-y-3 text-sm text-zinc-600">
                    <div>
                        <dt class="font-medium">Email</dt>
                        <dd><?= htmlspecialchars($settings['artist_email'] ?? env('ARTIST_EMAIL', ''), ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium">WhatsApp</dt>
                        <dd><?= htmlspecialchars($settings['whatsapp_number'] ?? env('WHATSAPP_NUMBER', ''), ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                </dl>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-8 shadow">
                <form class="space-y-5" method="post" action="/inquiry">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="artwork_title" value="<?= htmlspecialchars($_GET['artwork_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="artwork_slug" value="<?= htmlspecialchars($_GET['artwork_slug'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700" for="name">Name</label>
                        <input class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2 focus:border-ink focus:outline-none focus:ring-1 focus:ring-ink" type="text" id="name" name="name" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700" for="email">Email</label>
                        <input class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2 focus:border-ink focus:outline-none focus:ring-1 focus:ring-ink" type="email" id="email" name="email" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700" for="phone">Phone (optional)</label>
                        <input class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2 focus:border-ink focus:outline-none focus:ring-1 focus:ring-ink" type="tel" id="phone" name="phone" placeholder="+447483284919">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700" for="preferred_size">Preferred size / colour</label>
                        <input class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2 focus:border-ink focus:outline-none focus:ring-1 focus:ring-ink" type="text" id="preferred_size" name="preferred_size">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700" for="message">Message</label>
                        <textarea class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2 focus:border-ink focus:outline-none focus:ring-1 focus:ring-ink" id="message" name="message" rows="4" placeholder="Tell me about the artwork or commission you have in mind." required></textarea>
                    </div>
                    <button type="submit" class="btn-primary w-full justify-center">Continue to WhatsApp</button>
                </form>
            </div>
        </div>
    </div>
</section>
