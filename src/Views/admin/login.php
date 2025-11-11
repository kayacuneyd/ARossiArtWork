<section class="mx-auto max-w-md rounded-xl bg-white p-8 shadow-lg">
    <h2 class="text-2xl font-display">Admin Login</h2>
    <p class="mt-2 text-sm text-zinc-500">Use the credentials provided in the README to access the dashboard.</p>
    <form method="post" action="/admin/login" class="mt-6 space-y-5">
        <?= csrf_field(); ?>
        <div>
            <label class="block text-sm font-medium text-zinc-700" for="username">Username</label>
            <input class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2 focus:border-ink focus:outline-none focus:ring-1 focus:ring-ink" type="text" id="username" name="username" autocomplete="username" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-zinc-700" for="password">Password</label>
            <input class="mt-1 w-full rounded-md border border-zinc-200 px-3 py-2 focus:border-ink focus:outline-none focus:ring-1 focus:ring-ink" type="password" id="password" name="password" autocomplete="current-password" required>
        </div>
        <button type="submit" class="btn-primary w-full justify-center">Sign in</button>
    </form>
</section>
