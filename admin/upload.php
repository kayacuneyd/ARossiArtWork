<?php
/**
 * Admin Upload Artwork Page
 * Built in Kornwestheim
 * Developed by Cüneyt Kaya — https://kayacuneyt.com
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/includes/config.php';
require_once APP_ROOT . '/includes/db.php';
require_once APP_ROOT . '/includes/imageprocessor.php';

require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'Please select an image to upload';
    } else {
        // Get form data
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $year = !empty($_POST['year']) ? intval($_POST['year']) : null;
        $technique = trim($_POST['technique'] ?? '');
        $dimensions = trim($_POST['dimensions'] ?? '');
        $price = !empty($_POST['price']) ? floatval($_POST['price']) : null;
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $isPublished = isset($_POST['is_published']) ? 1 : 0;
        
        // Validate required fields
        if (empty($title)) {
            $error = 'Title is required';
        } else {
            try {
                // Process image
                $processor = new ImageProcessor();
                $result = $processor->processUpload($_FILES['image']);
                
                // Insert into database
                $sql = "INSERT INTO artworks 
                        (title, description, year, technique, dimensions, price, 
                         filename, thumbnail, webp_filename, is_featured, is_published) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $db->insert($sql, [
                    $title,
                    $description,
                    $year,
                    $technique,
                    $dimensions,
                    $price,
                    $result['filename'],
                    $result['thumbnail'],
                    $result['webp'],
                    $isFeatured,
                    $isPublished
                ]);
                
                log_action('Upload Artwork', "Uploaded: $title");
                set_flash('success', 'Artwork uploaded successfully!');
                redirect(SITE_URL . '/admin/artworks.php');
                
            } catch (Exception $e) {
                $error = 'Upload failed: ' . $e->getMessage();
            }
        }
    }
}

$csrfToken = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Artwork - Admin Panel</title>
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

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="artworks.php" class="text-sm text-gray-600 hover:text-gray-900">← Back to Artworks</a>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <h2 class="text-2xl font-bold">Upload New Artwork</h2>
            </div>

            <div class="p-6">
                <?php if ($error): ?>
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                        <?php echo h($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                    <!-- Image Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Artwork Image *
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 transition">
                            <input 
                                type="file" 
                                name="image" 
                                id="image"
                                accept="image/jpeg,image/png,image/webp"
                                required
                                class="hidden"
                                onchange="previewImage(this)"
                            >
                            <label for="image" class="cursor-pointer">
                                <div id="preview" class="mb-4"></div>
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
                            placeholder="e.g., Sunset Dreams"
                            value="<?php echo h($_POST['title'] ?? ''); ?>"
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
                            placeholder="Tell the story behind this artwork..."
                        ><?php echo h($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <!-- Year, Technique, Dimensions (Row) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                            <input 
                                type="number" 
                                name="year" 
                                min="1900" 
                                max="<?php echo date('Y'); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="<?php echo date('Y'); ?>"
                                value="<?php echo h($_POST['year'] ?? ''); ?>"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Technique</label>
                            <input 
                                type="text" 
                                name="technique"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., Oil on Canvas"
                                value="<?php echo h($_POST['technique'] ?? ''); ?>"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dimensions</label>
                            <input 
                                type="text" 
                                name="dimensions"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., 60x80 cm"
                                value="<?php echo h($_POST['dimensions'] ?? ''); ?>"
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
                            placeholder="Optional"
                            value="<?php echo h($_POST['price'] ?? ''); ?>"
                        >
                    </div>

                    <!-- Checkboxes -->
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input 
                                type="checkbox" 
                                name="is_published" 
                                value="1"
                                checked
                                class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                            >
                            <span class="ml-2 text-sm text-gray-700">Publish immediately</span>
                        </label>
                        <label class="flex items-center">
                            <input 
                                type="checkbox" 
                                name="is_featured" 
                                value="1"
                                class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                            >
                            <span class="ml-2 text-sm text-gray-700">Mark as featured</span>
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
                            Upload Artwork
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
            }
        }
    </script>
</body>
</html>
