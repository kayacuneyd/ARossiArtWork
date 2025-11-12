<?php
/**
 * Admin Settings Page
 * Built in Kornwestheim
 * Developed by Cüneyt Kaya — https://kayacuneyt.com
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/includes/config.php';
require_once APP_ROOT . '/includes/db.php';

require_login();

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token($_POST['csrf_token'] ?? '')) {
    try {
        // Update settings
        $settings = [
            'whatsapp_phone' => trim($_POST['whatsapp_phone'] ?? ''),
            'artist_email' => trim($_POST['artist_email'] ?? ''),
            'max_upload_size' => intval($_POST['max_upload_size'] ?? 8),
            'site_title' => trim($_POST['site_title'] ?? 'Artist Portfolio'),
            'site_description' => trim($_POST['site_description'] ?? ''),
            'enable_prices' => isset($_POST['enable_prices']) ? '1' : '0',
            'enable_inquiries' => isset($_POST['enable_inquiries']) ? '1' : '0'
        ];
        
        // Validate WhatsApp phone (E.164 format)
        if (!empty($settings['whatsapp_phone'])) {
            $phone = preg_replace('/[^+0-9]/', '', $settings['whatsapp_phone']);
            if (!preg_match('/^\+[1-9]\d{1,14}$/', $phone)) {
                throw new Exception('Invalid WhatsApp phone format. Use E.164 format: +447123456789');
            }
            $settings['whatsapp_phone'] = $phone;
        }
        
        // Validate email
        if (!empty($settings['artist_email']) && !filter_var($settings['artist_email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address');
        }
        
        // Validate upload size
        if ($settings['max_upload_size'] < 1 || $settings['max_upload_size'] > 50) {
            throw new Exception('Upload size must be between 1-50 MB');
        }
        
        // Update each setting
        foreach ($settings as $key => $value) {
            set_setting($key, $value);
        }
        
        log_action('Update Settings', 'Settings updated');
        $success = 'Settings saved successfully!';
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Load current settings
$currentSettings = [
    'whatsapp_phone' => get_setting('whatsapp_phone', '+447123456789'),
    'artist_email' => get_setting('artist_email', ''),
    'max_upload_size' => get_setting('max_upload_size', '8'),
    'site_title' => get_setting('site_title', 'Artist Portfolio'),
    'site_description' => get_setting('site_description', ''),
    'enable_prices' => get_setting('enable_prices', '1'),
    'enable_inquiries' => get_setting('enable_inquiries', '1')
];

$csrfToken = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
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
                        <a href="inquiries.php" class="text-gray-600 hover:text-gray-900">Inquiries</a>
                        <a href="settings.php" class="text-gray-900 font-medium">Settings</a>
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
        <h2 class="text-3xl font-bold text-gray-900 mb-6">Settings</h2>

        <?php if ($success): ?>
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                <?php echo h($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                <?php echo h($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

            <!-- Contact Settings -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold mb-4">Contact Settings</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            WhatsApp Phone Number *
                        </label>
                        <input 
                            type="text" 
                            name="whatsapp_phone" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="+447123456789"
                            value="<?php echo h($currentSettings['whatsapp_phone']); ?>"
                        >
                        <p class="mt-1 text-sm text-gray-500">Use E.164 format (e.g., +447123456789 for UK)</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Artist Email
                        </label>
                        <input 
                            type="email" 
                            name="artist_email"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="artist@example.com"
                            value="<?php echo h($currentSettings['artist_email']); ?>"
                        >
                        <p class="mt-1 text-sm text-gray-500">Optional: Receive email notifications for new inquiries</p>
                    </div>
                </div>
            </div>

            <!-- Site Settings -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold mb-4">Site Settings</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Site Title
                        </label>
                        <input 
                            type="text" 
                            name="site_title"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Artist Portfolio"
                            value="<?php echo h($currentSettings['site_title']); ?>"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Site Description
                        </label>
                        <textarea 
                            name="site_description" 
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Contemporary art portfolio"
                        ><?php echo h($currentSettings['site_description']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Upload Settings -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold mb-4">Upload Settings</h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Maximum Upload Size (MB)
                    </label>
                    <input 
                        type="number" 
                        name="max_upload_size" 
                        min="1" 
                        max="50"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        value="<?php echo h($currentSettings['max_upload_size']); ?>"
                    >
                    <p class="mt-1 text-sm text-gray-500">Server limit: <?php echo ini_get('upload_max_filesize'); ?></p>
                </div>
            </div>

            <!-- Feature Toggles -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold mb-4">Features</h3>
                
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="enable_prices" 
                            value="1"
                            <?php echo $currentSettings['enable_prices'] === '1' ? 'checked' : ''; ?>
                            class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                        >
                        <span class="ml-2 text-sm text-gray-700">Show prices on gallery</span>
                    </label>
                    
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="enable_inquiries" 
                            value="1"
                            <?php echo $currentSettings['enable_inquiries'] === '1' ? 'checked' : ''; ?>
                            class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                        >
                        <span class="ml-2 text-sm text-gray-700">Enable WhatsApp inquiry form</span>
                    </label>
                </div>
            </div>

            <!-- System Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-3">System Information</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-blue-700 font-medium">PHP Version:</span>
                        <span class="text-blue-900"><?php echo PHP_VERSION; ?></span>
                    </div>
                    <div>
                        <span class="text-blue-700 font-medium">Image Processing:</span>
                        <span class="text-blue-900"><?php echo extension_loaded('imagick') ? 'Imagick' : 'GD'; ?></span>
                    </div>
                    <div>
                        <span class="text-blue-700 font-medium">Max Upload:</span>
                        <span class="text-blue-900"><?php echo ini_get('upload_max_filesize'); ?></span>
                    </div>
                    <div>
                        <span class="text-blue-700 font-medium">Memory Limit:</span>
                        <span class="text-blue-900"><?php echo ini_get('memory_limit'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button 
                    type="submit"
                    class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
                >
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</body>
</html>
