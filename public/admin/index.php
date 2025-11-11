<?php

declare(strict_types=1);

require __DIR__ . '/../../src/bootstrap.php';

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\DashboardController;

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH) ?: '/admin';
$path = rtrim($path, '/');
if ($path === '') {
    $path = '/';
}
if ($path === '/admin') {
    // ensure canonical path without trailing slash variations
    $path = '/admin';
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$authController = AuthController::make();
$dashboardController = DashboardController::make();

if ($path === '/admin/login' && $method === 'GET') {
    $authController->showLogin();
    return;
}

if ($path === '/admin/login' && $method === 'POST') {
    $authController->login();
    return;
}

if ($path === '/admin/logout' && $method === 'POST') {
    $authController->logout();
    return;
}

if ($path === '/admin' && $method === 'GET') {
    $dashboardController->index();
    return;
}

if ($path === '/admin/artworks' && $method === 'POST') {
    $dashboardController->storeArtwork();
    return;
}

if (preg_match('#^/admin/artworks/(\d+)/update$#', $path, $matches) && $method === 'POST') {
    $dashboardController->updateArtwork((int) $matches[1]);
    return;
}

if (preg_match('#^/admin/artworks/(\d+)/delete$#', $path, $matches) && $method === 'POST') {
    $dashboardController->deleteArtwork((int) $matches[1]);
    return;
}

if (preg_match('#^/admin/artworks/(\d+)/publish$#', $path, $matches) && $method === 'POST') {
    $dashboardController->togglePublish((int) $matches[1]);
    return;
}

if (preg_match('#^/admin/artworks/(\d+)/featured$#', $path, $matches) && $method === 'POST') {
    $dashboardController->toggleFeatured((int) $matches[1]);
    return;
}

if ($path === '/admin/artworks/reorder' && $method === 'POST') {
    $dashboardController->reorder();
    return;
}

if ($path === '/admin/settings' && $method === 'POST') {
    $dashboardController->updateSettings();
    return;
}

http_response_code(404);
echo 'Not Found';
