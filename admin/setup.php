<?php
/**
 * One-Time Admin Setup
 * Run this once to create the first admin account, then DELETE THIS FILE!
 * Built in Kornwestheim
 * Developed by Cüneyt Kaya — https://kayacuneyt.com
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/includes/config.php';
require_once APP_ROOT . '/includes/db.php';

// Check if admin already exists
$stmt = $pdo->query("SELECT COUNT(*) FROM admins");
$adminCount = $stmt->fetchColumn();

if ($adminCount > 0) {
    die('
        <html>
        <head><title>Setup Complete</title></head>
        <body style="font-family: sans-serif; text-align: center; padding: 50px;">
            <h1>⚠️ Admin account already exists</h1>
            <p>For security reasons, please DELETE this file immediately:</p>
            <code style="background: #f3f4f6; padding: 10px; display: inline-block; margin: 20px;">/admin/setup.php</code>
            <p><a href="login.php" style="color: #2563eb;">Go to Login</a></p>
        </body>
        </html>
    ');
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username) || empty($password) || empty($email)) {
        $error = 'All fields are required';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    } else {
        try {
            // Create admin account
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admins (username, password, email) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hashedPassword, $email]);
            $success = true;
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2 { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-900 via-blue-800 to-blue-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <?php if ($success): ?>
                <!-- Success Message -->
                <div class="bg-green-600 p-8 text-center">
                    <h1 class="text-3xl font-bold text-white mb-2">✓ Setup Complete!</h1>
                    <p class="text-green-100">Admin account created successfully</p>
                </div>
                <div class="p-8 text-center">
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg mb-6">
                        <p class="font-semibold mb-2">⚠️ IMPORTANT SECURITY STEP</p>
                        <p class="text-sm">You MUST delete this file now:</p>
                        <code class="block bg-white px-3 py-2 rounded mt-2">/admin/setup.php</code>
                    </div>
                    <a href="login.php" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg font-medium hover:bg-blue-700 transition">
                        Go to Login
                    </a>
                </div>
            <?php else: ?>
                <!-- Setup Form -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-500 p-8 text-center">
                    <h1 class="text-3xl font-bold text-white mb-2">Admin Setup</h1>
                    <p class="text-blue-100 text-sm">Create your first admin account</p>
                </div>
                
                <div class="p-8">
                    <?php if ($error): ?>
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                            <?php echo h($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                            <input 
                                type="text" 
                                name="username" 
                                required 
                                minlength="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Choose a username"
                                value="<?php echo h($_POST['username'] ?? ''); ?>"
                            >
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input 
                                type="email" 
                                name="email" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="your@email.com"
                                value="<?php echo h($_POST['email'] ?? ''); ?>"
                            >
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <input 
                                type="password" 
                                name="password" 
                                required 
                                minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Min <?php echo PASSWORD_MIN_LENGTH; ?> characters"
                            >
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                            <input 
                                type="password" 
                                name="confirm_password" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Re-enter password"
                            >
                        </div>
                        
                        <button 
                            type="submit"
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-500 text-white py-3 rounded-lg font-medium hover:from-blue-700 hover:to-blue-600 transition"
                        >
                            Create Admin Account
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-8 text-blue-200 text-sm">
            <p>Built in Kornwestheim</p>
            <p class="mt-1">Developed by <a href="https://kayacuneyt.com" target="_blank" class="text-white hover:underline">Cüneyt Kaya</a></p>
        </div>
    </div>
</body>
</html>
