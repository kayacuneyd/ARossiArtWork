<?php
/**
 * Admin Artworks Management
 * Built in Kornwestheim
 * Developed by Cüneyt Kaya — https://kayacuneyt.com
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/includes/config.php';
require_once APP_ROOT . '/includes/db.php';
require_once APP_ROOT . '/includes/imageprocessor.php';

require_login();

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Get artwork details
    $artwork = $db->fetchOne("SELECT * FROM artworks WHERE id = ?", [$id]);
    
    if ($artwork) {
        // Delete files
        $processor = new ImageProcessor();
        $processor->deleteArtwork($artwork['filename'], $artwork['webp_filename']);
        
        // Delete from database
        $db->execute("DELETE FROM artworks WHERE id = ?", [$id]);
        
        log_action('Delete Artwork', "Deleted: {$artwork['title']}");
        set_flash('success', 'Artwork deleted successfully');
    }
    
    redirect(SITE_URL . '/admin/artworks.php');
}

// Handle toggle published
if (isset($_GET['action']) && $_GET['action'] === 'toggle_publish' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $db->execute("UPDATE artworks SET is_published = NOT is_published WHERE id = ?", [$id]);
    redirect(SITE_URL . '/admin/artworks.php');
}

// Handle toggle featured
if (isset($_GET['action']) && $_GET['action'] === 'toggle_featured' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $db->execute("UPDATE artworks SET is_featured = NOT is_featured WHERE id = ?", [$id]);
    redirect(SITE_URL . '/admin/artworks.php');
}

// Get all artworks
$artworks = $db->fetchAll("SELECT * FROM artworks ORDER BY sort_order ASC, created_at DESC");

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Artworks - Admin Panel</title>
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
                        <a href="artworks.php" class="text-gray-900 font-medium">Artworks</a>
                        <a href="inquiries.php" class="text-gray-600 hover:text-gray-900">Inquiries</a>
                        <a href="settings.php" class="text-gray-600 hover:text-gray-900">Settings</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600"><?php echo h($_SESSION['admin_username']); ?></span>
                    <a href="logout.php" class="text-sm text-red-600 hover:text-red-700">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-900">Manage Artworks</h2>
            <a href="upload.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition">
                + Upload New Artwork
            </a>
        </div>

        <?php if ($flash): ?>
            <div class="mb-6 px-4 py-3 rounded-lg <?php echo $flash['type'] === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'; ?>">
                <?php echo h($flash['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($artworks)): ?>
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No Artworks Yet</h3>
                <p class="text-gray-500 mb-4">Start building your portfolio by uploading your first artwork</p>
                <a href="upload.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                    Upload First Artwork
                </a>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Image</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($artworks as $artwork): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <img 
                                        src="<?php echo SITE_URL . '/uploads/thumbnails/' . h($artwork['thumbnail']); ?>" 
                                        alt="<?php echo h($artwork['title']); ?>"
                                        class="w-20 h-20 object-cover rounded"
                                    >
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900"><?php echo h($artwork['title']); ?></div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo date('M j, Y', strtotime($artwork['created_at'])); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php if ($artwork['year']): ?>
                                        <div><?php echo h($artwork['year']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($artwork['technique']): ?>
                                        <div><?php echo h($artwork['technique']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($artwork['price']): ?>
                                        <div class="font-semibold">£<?php echo number_format($artwork['price'], 2); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <?php if ($artwork['is_published']): ?>
                                            <span class="inline-block px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded">Published</span>
                                        <?php else: ?>
                                            <span class="inline-block px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-800 rounded">Draft</span>
                                        <?php endif; ?>
                                        <?php if ($artwork['is_featured']): ?>
                                            <span class="inline-block px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded">Featured</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm space-y-2">
                                    <a 
                                        href="?action=toggle_publish&id=<?php echo $artwork['id']; ?>" 
                                        class="block text-blue-600 hover:text-blue-800"
                                    >
                                        <?php echo $artwork['is_published'] ? 'Unpublish' : 'Publish'; ?>
                                    </a>
                                    <a 
                                        href="?action=toggle_featured&id=<?php echo $artwork['id']; ?>" 
                                        class="block text-purple-600 hover:text-purple-800"
                                    >
                                        <?php echo $artwork['is_featured'] ? 'Unfeature' : 'Feature'; ?>
                                    </a>
                                    <a 
                                        href="?action=delete&id=<?php echo $artwork['id']; ?>" 
                                        class="block text-red-600 hover:text-red-800"
                                        onclick="return confirm('Are you sure you want to delete this artwork? This cannot be undone.');"
                                    >
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
