<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4" data-modal="contact">
    <div class="w-full max-w-lg rounded-lg bg-white p-8 shadow-xl">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-2xl font-display">Request via WhatsApp</h2>
                <p class="mt-2 text-sm text-zinc-500">Complete form to prepare your WhatsApp message.</p>
            </div>
            <button class="text-zinc-500 hover:text-zinc-800" data-close-modal>&times;</button>
        </div>
        <form class="mt-6 space-y-4" method="post" action="/inquiry" data-contact-form>
            <?= csrf_field(); ?>
            <input type="hidden" name="artwork_title" value="" data-contact-artwork-title>
            <input type="hidden" name="artwork_slug" value="" data-contact-artwork-slug>
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
                <textarea class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2 focus:border-ink focus:outline-none focus:ring-1 focus:ring-ink" id="message" name="message" rows="4" required></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="rounded-md border border-zinc-300 px-4 py-2 text-sm" data-close-modal>Cancel</button>
                <button type="submit" class="btn-primary text-sm">
                    Continue to WhatsApp
                </button>
            </div>
        </form>
    </div>
</div>
