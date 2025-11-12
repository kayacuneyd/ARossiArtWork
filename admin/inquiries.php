<?php
/**
 * Admin Inquiries Page
 * Built in Kornwestheim
 * Developed by Cüneyt Kaya — https://kayacuneyt.com
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/includes/config.php';
require_once APP_ROOT . '/includes/db.php';

require_login();

// Get all inquiries
$inquiries = $db->fetchAll("SELECT * FROM inquiries ORDER BY created_at DESC");

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiries - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center space-x-8">
                    <h1 class="text-xl font-bold text-gray-900">Admin Panel</h1>
                    <div class="hidden md:flex space-x-4">
                        <a href="dashboard.php" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                        <a href="artworks.php" class="text-gray-600 hover:text-gray-900">Artworks</a>
                        <a href="inquiries.php" class="text-gray-900 font-medium">Inquiries</a>
                        <a href="settings.php" class="text-gray-600 hover:text-gray-900">Settings</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a 
                        href="<?php echo SITE_URL; ?>" 
                        target="_blank" 
                        rel="noopener" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                    >
                        View Site
                    </a>
                    <span class="text-sm text-gray-600"><?php echo h($_SESSION['admin_username']); ?></span>
                    <a href="logout.php" class="text-sm text-red-600 hover:text-red-700">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h2 class="text-3xl font-bold text-gray-900">Inquiries</h2>
            <p class="text-gray-600 mt-1">All artwork inquiries from the WhatsApp contact form</p>
        </div>

        <?php if ($flash): ?>
            <div class="mb-6 px-4 py-3 rounded-lg <?php echo $flash['type'] === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'; ?>">
                <?php echo h($flash['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($inquiries)): ?>
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No Inquiries Yet</h3>
                <p class="text-gray-500">Inquiries from your WhatsApp contact form will appear here</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($inquiries as $inquiry): ?>
                    <div class="bg-white rounded-lg shadow hover:shadow-md transition p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900"><?php echo h($inquiry['name']); ?></h3>
                                <div class="text-sm text-gray-600 mt-1">
                                    <a href="mailto:<?php echo h($inquiry['email']); ?>" class="hover:text-blue-600">
                                        <?php echo h($inquiry['email']); ?>
                                    </a>
                                    <?php if ($inquiry['phone']): ?>
                                        <span class="mx-2">•</span>
                                        <a href="tel:<?php echo h($inquiry['phone']); ?>" class="hover:text-blue-600">
                                            <?php echo h($inquiry['phone']); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-right text-sm text-gray-500">
                                <?php echo date('M j, Y', strtotime($inquiry['created_at'])); ?>
                                <br>
                                <?php echo date('g:i A', strtotime($inquiry['created_at'])); ?>
                            </div>
                        </div>

                        <?php if ($inquiry['artwork_title']): ?>
                            <div class="mb-4 text-sm text-blue-700 bg-blue-50 border border-blue-100 rounded-lg px-4 py-2">
                                Interested in: <span class="font-semibold"><?php echo h($inquiry['artwork_title']); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($inquiry['preferred_size'] || $inquiry['preferred_color']): ?>
                            <div class="mb-3 flex gap-4 text-sm">
                                <?php if ($inquiry['preferred_size']): ?>
                                    <div>
                                        <span class="font-medium text-gray-700">Preferred Size:</span>
                                        <span class="text-gray-600"><?php echo h($inquiry['preferred_size']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($inquiry['preferred_color']): ?>
                                    <div>
                                        <span class="font-medium text-gray-700">Preferred Color:</span>
                                        <span class="text-gray-600"><?php echo h($inquiry['preferred_color']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-sm font-medium text-gray-700 mb-2">Message:</p>
                            <p class="text-gray-800 whitespace-pre-wrap"><?php echo h($inquiry['message']); ?></p>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <a 
                                href="mailto:<?php echo h($inquiry['email']); ?>?subject=Re: Artwork Inquiry" 
                                class="text-sm bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                            >
                                Reply via Email
                            </a>
                            <?php if ($inquiry['phone']): ?>
                                <a 
                                    href="tel:<?php echo h($inquiry['phone']); ?>" 
                                    class="text-sm bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
                                >
                                    Call
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
