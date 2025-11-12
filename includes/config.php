<?php
/**
 * Artist Portfolio - Configuration File
 * Built in Kornwestheim
 * Developed by Cüneyt Kaya — https://kayacuneyt.com
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306')
define('DB_NAME', 'u629681856_aTAla');
define('DB_USER', 'u629681856_3hOmA');
define('DB_PASS', 'Kayacuneyd1453!');
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_URL', 'https://arossiartwork.com/');
define('SITE_NAME', 'Artist Portfolio');

// Upload configuration
define('UPLOAD_DIR', APP_ROOT . '/uploads/artworks/');
define('THUMB_DIR', APP_ROOT . '/uploads/thumbnails/');
define('WEBP_DIR', APP_ROOT . '/uploads/webp/');
define('MAX_UPLOAD_SIZE', 8 * 1024 * 1024); // 8MB in bytes
define('MAX_IMAGE_WIDTH', 2048);
define('THUMB_WIDTH', 600);

// Allowed image types
define('ALLOWED_MIME_TYPES', [
    'image/jpeg',
    'image/png',
    'image/webp'
]);

define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

// Session configuration
define('SESSION_LIFETIME', 3600 * 24); // 24 hours
define('SESSION_NAME', 'artist_portfolio_session');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);

// Pagination
define('ITEMS_PER_PAGE', 12);

// Email configuration (PHPMailer - optional)
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'artist@arossiartwork.com');
define('SMTP_PASSWORD', 'Artist1234!');
define('SMTP_FROM_EMAIL', 'noreply@arossiartwork.com');
define('SMTP_FROM_NAME', 'ARossi Artwork');

// Timezone
date_default_timezone_set('Europe/London');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1); // Set to 1 if using HTTPS
    ini_set('session.use_strict_mode', 1);
    session_name(SESSION_NAME);
    session_start();
}

// Auto-load classes
spl_autoload_register(function ($class) {
    $file = APP_ROOT . '/includes/' . strtolower($class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Helper functions

/**
 * Sanitize output for HTML
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

/**
 * Require login for admin pages
 */
function require_login() {
    if (!is_logged_in()) {
        redirect(SITE_URL . '/admin/login.php');
    }
}

/**
 * Format file size
 */
function format_bytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Get setting value from database
 */
function get_setting($key, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? $value : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

/**
 * Set setting value in database
 */
function set_setting($key, $value) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = ?
        ");
        return $stmt->execute([$key, $value, $value]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Flash message system
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Generate unique filename
 */
function generate_unique_filename($original_name) {
    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    return uniqid('artwork_', true) . '_' . time() . '.' . $ext;
}

/**
 * Validate image file
 */
function validate_image_file($file) {
    $errors = [];
    
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $errors[] = 'No file uploaded';
        return $errors;
    }
    
    // Check file size
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        $errors[] = 'File size exceeds ' . format_bytes(MAX_UPLOAD_SIZE);
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, ALLOWED_MIME_TYPES)) {
        $errors[] = 'Invalid file type. Allowed: JPG, PNG, WebP';
    }
    
    // Check extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        $errors[] = 'Invalid file extension';
    }
    
    // Verify it's actually an image
    if (!@getimagesize($file['tmp_name'])) {
        $errors[] = 'File is not a valid image';
    }
    
    return $errors;
}

/**
 * Log admin action (optional)
 */
function log_action($action, $details = '') {
    // Implement if needed
    error_log(sprintf(
        "[%s] Admin %s: %s - %s",
        date('Y-m-d H:i:s'),
        $_SESSION['admin_username'] ?? 'unknown',
        $action,
        $details
    ));
}
