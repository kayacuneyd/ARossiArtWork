<?php
/**
 * Admin Logout
 * Built in Kornwestheim
 * Developed by Cüneyt Kaya — https://kayacuneyt.com
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/includes/config.php';

// Destroy session
$_SESSION = [];
session_destroy();

// Clear session cookie
if (isset($_COOKIE[SESSION_NAME])) {
    setcookie(SESSION_NAME, '', time() - 3600, '/');
}

// Redirect to login
header('Location: ' . SITE_URL . '/admin/login.php');
exit;
