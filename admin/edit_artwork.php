<?php
/**
 * Edit Artwork Page
 * Built in Kornwestheim
 * Developed by Cüneyt Kaya — https://kayacuneyt.com
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/includes/config.php';
require_once APP_ROOT . '/includes/db.php';
require_once APP_ROOT . '/includes/imageprocessor.php';

require_login();

$artworkId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($artworkId <= 0) {
    set_flash('error', 'Artwork ID missing.');
    redirect(SITE_URL . '/admin/artworks.php');
}

$artwork = $db->fetchOne("SELECT * FROM artworks WHERE id = ?", [$artworkId]);
if (!$artwork) {
    set_flash('error', 'Artwork not found.');
    redirect(SITE_URL . '/admin/artworks.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $year = isset($_POST['year']) && $_POST['year'] !== '' ? intval($_POST['year']) : null;
        $technique = trim($_POST['technique'] ?? '');
        $dimensions = trim($_POST['dimensions'] ?? '');
        $price = isset($_POST['price']) && $_POST['price'] !== '' ? floatval($_POST['price']) : null;
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $isPublished = isset($_POST['is_published']) ? 1 : 0;

        if ($title === '') {
            $error = 'Title is required';
        } else {
            $newImageData = null;
            $processor = new ImageProcessor();
            $db->beginTransaction();

            try {
                if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $newImageData = $processor->processUpload($_FILES['image']);
                }

                $params = [
                    $title,
                    $description,
                    $year,
                    $technique,
                    $dimensions,
                    $price,
                    $isFeatured,
                    $isPublished
                ];

                $sql = "UPDATE artworks 
                        SET title = ?, description = ?, year = ?, technique = ?, dimensions = ?, 
                            price = ?, is_featured = ?, is_published = ?";

                if ($newImageData) {
                    $sql .= ", filename = ?, thumbnail = ?, webp_filename = ?";
                    $params[] = $newImageData['filename'];
                    $params[] = $newImageData['thumbnail'];
                    $params[] = $newImageData['webp'];
                }

                $sql .= " WHERE id = ?";
                $params[] = $artworkId;

                $db->execute($sql, $params);
                $db->commit();

                if ($newImageData) {
                    $processor->deleteArtwork($artwork['filename'], $artwork['webp_filename']);
                }

                log_action('Edit Artwork', "Updated: $title");
                set_flash('success', 'Artwork updated successfully.');
                redirect(SITE_URL . '/admin/artworks.php');

            } catch (Exception $e) {
                $db->rollback();
                if ($newImageData) {
                    $processor->deleteArtwork($newImageData['filename'], $newImageData['webp']);
                }
                $error = 'Update failed: ' . $e->getMessage();
            }
        }
    }
}

$csrfToken = generate_csrf_token();
$publishChecked = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? isset($_POST['is_published'])
    : (bool) $artwork['is_published'];
$featuredChecked = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? isset($_POST['is_featured'])
    : (bool) $artwork['is_featured'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Artwork - Admin Panel</title>
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

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="artworks.php" class="text-sm text-gray-600 hover:text-gray-900">← Back to Artworks</a>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <h2 class="text-2xl font-bold">Edit Artwork</h2>
                <p class="text-sm text-gray-500 mt-1"><?php echo h($artwork['title']); ?></p>
            </div>

            <div class="p-6">
                <?php if ($error): ?>
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                        <?php echo h($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                    <!-- Existing Image -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Current Artwork
                            </label>
                            <div class="border rounded-xl overflow-hidden bg-gray-50">
                                <img 
                                    src="<?php echo SITE_URL . '/uploads/thumbnails/' . h($artwork['thumbnail']); ?>" 
                                    alt="<?php echo h($artwork['title']); ?>"
                                    class="w-full h-64 object-cover"
                                >
                            </div>
                        </div>

                        <!-- Image Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Replace Image (optional)
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 transition">
                                <input 
                                    type="file" 
                                    name="image" 
                                    id="image"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="hidden"
                                    onchange="previewImage(this)"
                                >
                                <label for="image" class="cursor-pointer">
                                    <div id="preview" class="mb-4 text-sm text-gray-500">
                                        Uploading a new file will overwrite the existing artwork.
                                    </div>
                                    <div class="text-gray-600">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <p class="font-medium">Click to upload image</p>
                                        <p class="text-sm text-gray-500 mt-1">JPG, PNG, WebP up to <?php echo format_bytes(MAX_UPLOAD_SIZE); ?></p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Title *
                        </label>
                        <input 
                            type="text" 
                            name="title" 
                            required
                            maxlength="255"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            value="<?php echo h($_POST['title'] ?? $artwork['title']); ?>"
                        >
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea 
                            name="description" 
                            rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        ><?php echo h($_POST['description'] ?? $artwork['description']); ?></textarea>
                    </div>

                    <!-- Year, Technique, Dimensions -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                            <input 
                                type="number" 
                                name="year" 
                                min="1900" 
                                max="<?php echo date('Y'); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                value="<?php echo h($_POST['year'] ?? $artwork['year']); ?>"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Technique</label>
                            <input 
                                type="text" 
                                name="technique"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                value="<?php echo h($_POST['technique'] ?? $artwork['technique']); ?>"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dimensions</label>
                            <input 
                                type="text" 
                                name="dimensions"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                value="<?php echo h($_POST['dimensions'] ?? $artwork['dimensions']); ?>"
                            >
                        </div>
                    </div>

                    <!-- Price -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Price (£)
                        </label>
                        <input 
                            type="number" 
                            name="price" 
                            step="0.01" 
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            value="<?php echo h($_POST['price'] ?? $artwork['price']); ?>"
                        >
                    </div>

                    <!-- Checkboxes -->
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input 
                                type="checkbox" 
                                name="is_published" 
                                value="1"
                                class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                                <?php echo $publishChecked ? 'checked' : ''; ?>
                            >
                            <span class="ml-2 text-sm text-gray-700">Published</span>
                        </label>
                        <label class="flex items-center">
                            <input 
                                type="checkbox" 
                                name="is_featured" 
                                value="1"
                                class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                                <?php echo $featuredChecked ? 'checked' : ''; ?>
                            >
                            <span class="ml-2 text-sm text-gray-700">Featured artwork</span>
                        </label>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t">
                        <a 
                            href="artworks.php" 
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                        >
                            Cancel
                        </a>
                        <button 
                            type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
                        >
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" class="max-h-64 mx-auto rounded-lg shadow-md">';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.innerHTML = 'Uploading a new file will overwrite the existing artwork.';
            }
        }
    </script>
</body>
</html>
