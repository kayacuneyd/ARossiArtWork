<section class="space-y-12">
    <div class="rounded-xl bg-white p-8 shadow-sm">
        <h2 class="text-xl font-display">Upload new artwork</h2>
        <p class="mt-2 text-sm text-zinc-500">Images are resized to 2048px, thumbnails at 600px, and converted to WebP automatically.</p>
        <form class="mt-6 grid gap-6 md:grid-cols-2" method="post" action="/admin/artworks" enctype="multipart/form-data">
            <?= csrf_field(); ?>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-zinc-700" for="image">Artwork image</label>
                <input class="mt-2 block w-full rounded-md border border-dashed border-zinc-300 bg-zinc-50 px-4 py-6 text-sm" type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700" for="title">Title</label>
                <input class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2 focus:border-ink focus:outline-none focus:ring-1 focus:ring-ink" type="text" name="title" id="title" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700" for="year">Year</label>
                <input class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2" type="number" min="1900" max="<?= date('Y'); ?>" name="year" id="year">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700" for="technique">Technique</label>
                <input class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2" type="text" name="technique" id="technique">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700" for="dimensions">Dimensions</label>
                <input class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2" type="text" name="dimensions" id="dimensions" placeholder="e.g. 40 x 50 cm">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700" for="price">Price (optional)</label>
                <div class="flex gap-2">
                    <input class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2" type="number" min="0" step="0.01" name="price" id="price">
                    <input class="mt-1 w-24 rounded-md border border-zinc-200 px-3 py-2" type="text" name="currency" id="currency" value="GBP">
                </div>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-zinc-700" for="description">Description</label>
                <textarea class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2" name="description" id="description" rows="3"></textarea>
            </div>
            <div class="flex items-center gap-4">
                <label class="inline-flex items-center gap-2 text-sm text-zinc-700">
                    <input type="checkbox" name="is_featured" class="rounded border-zinc-300"> Featured
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-zinc-700">
                    <input type="checkbox" name="is_published" class="rounded border-zinc-300" checked> Published
                </label>
            </div>
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="btn-primary">Upload artwork</button>
            </div>
        </form>
    </div>

    <div class="rounded-xl bg-white p-8 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-display">Existing artworks</h2>
            <form id="reorder-form" method="post" action="/admin/artworks/reorder" class="flex items-center gap-3">
                <?= csrf_field(); ?>
                <button type="submit" class="rounded-md border border-zinc-300 px-3 py-2 text-sm">Save order</button>
            </form>
        </div>
        <div class="mt-6 space-y-6">
            <?php foreach ($artworks as $artwork): ?>
                <?php $meta = json_decode($artwork['metadata'] ?? '{}', true) ?: []; ?>
                <div class="rounded-lg border border-zinc-200 p-6">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="h-24 w-24 overflow-hidden rounded-md bg-zinc-100">
                                <?php if (!empty($artwork['thumbnail_path'])): ?>
                                    <img src="/<?= htmlspecialchars($artwork['thumbnail_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($artwork['title'], ENT_QUOTES, 'UTF-8'); ?>" class="h-full w-full object-cover">
                                <?php endif; ?>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold"><?= htmlspecialchars($artwork['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p class="mt-1 text-sm text-zinc-500">Slug: <?= htmlspecialchars($artwork['slug'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="mt-2 text-sm text-zinc-500">Order
                                    <input class="ml-2 w-16 rounded border border-zinc-200 px-2 py-1" type="number" name="order[<?= (int) $artwork['id']; ?>]" value="<?= (int) $artwork['display_order']; ?>" form="reorder-form">
                                </p>
                                <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                    <span class="rounded-full bg-zinc-100 px-3 py-1">Featured: <?= $artwork['is_featured'] ? 'Yes' : 'No'; ?></span>
                                    <span class="rounded-full bg-zinc-100 px-3 py-1">Published: <?= $artwork['is_published'] ? 'Yes' : 'No'; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <form method="post" action="/admin/artworks/<?= (int) $artwork['id']; ?>/featured">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="featured" value="<?= $artwork['is_featured'] ? '0' : '1'; ?>">
                                <button type="submit" class="rounded-md border border-zinc-300 px-3 py-2 text-xs">
                                    <?= $artwork['is_featured'] ? 'Unfeature' : 'Mark featured'; ?>
                                </button>
                            </form>
                            <form method="post" action="/admin/artworks/<?= (int) $artwork['id']; ?>/publish">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="publish" value="<?= $artwork['is_published'] ? '0' : '1'; ?>">
                                <button type="submit" class="rounded-md border border-zinc-300 px-3 py-2 text-xs">
                                    <?= $artwork['is_published'] ? 'Unpublish' : 'Publish'; ?>
                                </button>
                            </form>
                            <form method="post" action="/admin/artworks/<?= (int) $artwork['id']; ?>/delete" onsubmit="return confirm('Delete this artwork?');">
                                <?= csrf_field(); ?>
                                <button type="submit" class="rounded-md border border-rose-300 px-3 py-2 text-xs text-rose-600">Delete</button>
                            </form>
                        </div>
                    </div>
                    <details class="mt-4">
                        <summary class="cursor-pointer text-sm text-zinc-600">Edit details</summary>
                        <form method="post" action="/admin/artworks/<?= (int) $artwork['id']; ?>/update" enctype="multipart/form-data" class="mt-4 grid gap-4 md:grid-cols-2">
                            <?= csrf_field(); ?>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-zinc-700" for="image-<?= (int) $artwork['id']; ?>">Replace image (optional)</label>
                                <input class="mt-1 w-full rounded-md border border-dashed border-zinc-300 px-3 py-2 text-sm" type="file" name="image" id="image-<?= (int) $artwork['id']; ?>" accept="image/jpeg,image/png,image/webp">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Title</label>
                                <input class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2" type="text" name="title" value="<?= htmlspecialchars($artwork['title'], ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Year</label>
                                <input class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2" type="number" min="1900" max="<?= date('Y'); ?>" name="year" value="<?= htmlspecialchars($artwork['year'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Technique</label>
                                <input class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2" type="text" name="technique" value="<?= htmlspecialchars($meta['technique'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Dimensions</label>
                                <input class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2" type="text" name="dimensions" value="<?= htmlspecialchars($meta['dimensions'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Price</label>
                                <div class="flex gap-2">
                                    <input class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2" type="number" min="0" step="0.01" name="price" value="<?= htmlspecialchars($artwork['price'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <input class="mt-1 w-24 rounded-md border border-zinc-200 px-3 py-2" type="text" name="currency" value="<?= htmlspecialchars($artwork['currency'] ?? 'GBP', ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-zinc-700">Description</label>
                                <textarea class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2" name="description" rows="3"><?= htmlspecialchars($artwork['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                            <div class="flex items-center gap-4">
                                <label class="inline-flex items-center gap-2 text-sm text-zinc-700">
                                    <input type="checkbox" name="is_featured" class="rounded border-zinc-300" <?= $artwork['is_featured'] ? 'checked' : ''; ?>> Featured
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm text-zinc-700">
                                    <input type="checkbox" name="is_published" class="rounded border-zinc-300" <?= $artwork['is_published'] ? 'checked' : ''; ?>> Published
                                </label>
                            </div>
                            <div class="md:col-span-2 flex justify-end">
                                <button type="submit" class="btn-primary">Save changes</button>
                            </div>
                        </form>
                    </details>
                </div>
            <?php endforeach; ?>
            <?php if (empty($artworks)): ?>
                <p class="text-sm text-zinc-500">No artworks uploaded yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid gap-8 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-8 shadow-sm">
            <h2 class="text-xl font-display">Settings</h2>
            <form class="mt-6 space-y-4" method="post" action="/admin/settings">
                <?= csrf_field(); ?>
                <div>
                    <label class="block text-sm font-medium text-zinc-700" for="artist_email">Artist email</label>
                    <input class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2" type="email" name="artist_email" id="artist_email" value="<?= htmlspecialchars($settings['artist_email'] ?? env('ARTIST_EMAIL', ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700" for="whatsapp_number">WhatsApp number</label>
                    <input class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2" type="text" name="whatsapp_number" id="whatsapp_number" value="<?= htmlspecialchars($settings['whatsapp_number'] ?? env('WHATSAPP_NUMBER', ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700" for="max_upload_mb">Max upload size (MB)</label>
                    <input class="mt-1 w-24 rounded-md border border-zinc-200 px-3 py-2" type="number" min="1" max="32" name="max_upload_mb" id="max_upload_mb" value="<?= htmlspecialchars($settings['max_upload_mb'] ?? env('MAX_UPLOAD_MB', '8'), ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn-primary">Save settings</button>
                </div>
            </form>
        </div>
        <div class="rounded-xl bg-white p-8 shadow-sm">
            <h2 class="text-xl font-display">Recent inquiries</h2>
            <div class="mt-4 space-y-4 text-sm">
                <?php foreach ($inquiries as $inquiry): ?>
                    <div class="rounded-lg border border-zinc-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium"><?= htmlspecialchars($inquiry['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="text-zinc-500"><?= htmlspecialchars($inquiry['email'], ENT_QUOTES, 'UTF-8'); ?><?= $inquiry['phone'] ? ' · ' . htmlspecialchars($inquiry['phone'], ENT_QUOTES, 'UTF-8') : ''; ?></p>
                            </div>
                            <span class="text-xs text-zinc-400"><?= date('d M Y H:i', strtotime($inquiry['created_at'])); ?></span>
                        </div>
                        <p class="mt-3 text-zinc-600"><?= nl2br(htmlspecialchars($inquiry['message'], ENT_QUOTES, 'UTF-8')); ?></p>
                        <?php if (!empty($inquiry['preferred_size'])): ?>
                            <p class="mt-2 text-xs text-zinc-500">Preferred size/colour: <?= htmlspecialchars($inquiry['preferred_size'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($inquiries)): ?>
                    <p class="text-zinc-500">No inquiries yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
