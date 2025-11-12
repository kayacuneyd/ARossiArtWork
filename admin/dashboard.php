<?php
/**
 * Admin Dashboard
 * Built in Kornwestheim
 * Developed by Cüneyt Kaya — https://kayacuneyt.com
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/includes/config.php';
require_once APP_ROOT . '/includes/db.php';
require_once APP_ROOT . '/includes/imageprocessor.php';

require_login();

// Get statistics
$stats = [
    'artworks' => $db->fetchColumn("SELECT COUNT(*) FROM artworks"),
    'published' => $db->fetchColumn("SELECT COUNT(*) FROM artworks WHERE is_published = 1"),
    'featured' => $db->fetchColumn("SELECT COUNT(*) FROM artworks WHERE is_featured = 1"),
    'inquiries' => $db->fetchColumn("SELECT COUNT(*) FROM inquiries")
];

// Get recent artworks
$artworks = $db->fetchAll("
    SELECT * FROM artworks 
    ORDER BY created_at DESC 
    LIMIT 20
");

// Get recent inquiries
$inquiries = $db->fetchAll("
    SELECT * FROM inquiries 
    ORDER BY created_at DESC 
    LIMIT 10
");

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
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
                        <a href="dashboard.php" class="text-gray-900 font-medium">Dashboard</a>
                        <a href="artworks.php" class="text-gray-600 hover:text-gray-900">Artworks</a>
                        <a href="inquiries.php" class="text-gray-600 hover:text-gray-900">Inquiries</a>
                        <a href="settings.php" class="text-gray-600 hover:text-gray-900">Settings</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?php echo SITE_URL; ?>" target="_blank" class="text-sm text-gray-600 hover:text-gray-900">
                        View Site
                    </a>
                    <span class="text-sm text-gray-600">
                        <?php echo h($_SESSION['admin_username']); ?>
                    </span>
                    <a href="logout.php" class="text-sm text-red-600 hover:text-red-700">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Flash Message -->
        <?php if ($flash): ?>
            <div class="mb-6 px-4 py-3 rounded-lg <?php echo $flash['type'] === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'; ?>">
                <?php echo h($flash['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-500 mb-1">Total Artworks</div>
                <div class="text-3xl font-bold text-gray-900"><?php echo $stats['artworks']; ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-500 mb-1">Published</div>
                <div class="text-3xl font-bold text-green-600"><?php echo $stats['published']; ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-500 mb-1">Featured</div>
                <div class="text-3xl font-bold text-blue-600"><?php echo $stats['featured']; ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-500 mb-1">Inquiries</div>
                <div class="text-3xl font-bold text-purple-600"><?php echo $stats['inquiries']; ?></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Artworks -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <h2 class="text-lg font-semibold">Recent Artworks</h2>
                    <a href="upload.php" class="text-sm bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        + Upload New
                    </a>
                </div>
                <div class="divide-y">
                    <?php if (empty($artworks)): ?>
                        <div class="p-6 text-center text-gray-500">
                            No artworks yet. <a href="upload.php" class="text-blue-600 hover:underline">Upload your first artwork</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($artworks as $artwork): ?>
                            <div class="p-4 hover:bg-gray-50 flex items-center space-x-4">
                                <img 
                                    src="<?php echo SITE_URL . '/uploads/thumbnails/' . h($artwork['thumbnail']); ?>" 
                                    alt="<?php echo h($artwork['title']); ?>"
                                    class="w-16 h-16 object-cover rounded"
                                >
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-900 truncate">
                                        <?php echo h($artwork['title']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo h($artwork['year']); ?> • <?php echo h($artwork['technique']); ?>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <?php if ($artwork['is_featured']): ?>
                                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Featured</span>
                                    <?php endif; ?>
                                    <?php if (!$artwork['is_published']): ?>
                                        <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded">Draft</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if (count($artworks) > 0): ?>
                    <div class="px-6 py-3 border-t bg-gray-50 text-center">
                        <a href="artworks.php" class="text-sm text-blue-600 hover:text-blue-700">View all artworks →</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Inquiries -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold">Recent Inquiries</h2>
                </div>
                <div class="divide-y">
                    <?php if (empty($inquiries)): ?>
                        <div class="p-6 text-center text-gray-500">
                            No inquiries yet
                        </div>
                    <?php else: ?>
                        <?php foreach ($inquiries as $inquiry): ?>
                            <div class="p-4 hover:bg-gray-50">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="font-medium text-gray-900"><?php echo h($inquiry['name']); ?></div>
                                    <span class="text-xs text-gray-500">
                                        <?php echo date('M j, g:i A', strtotime($inquiry['created_at'])); ?>
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600 mb-1"><?php echo h($inquiry['email']); ?></div>
                                <div class="text-sm text-gray-700 line-clamp-2"><?php echo h($inquiry['message']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if (count($inquiries) > 0): ?>
                    <div class="px-6 py-3 border-t bg-gray-50 text-center">
                        <a href="inquiries.php" class="text-sm text-blue-600 hover:text-blue-700">View all inquiries →</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
