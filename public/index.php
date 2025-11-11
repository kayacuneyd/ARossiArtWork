<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

use App\Controllers\SiteController;
use App\Support\View;

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rtrim($path, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$controller = SiteController::make();

if ($method === 'GET' && $path === '/') {
    $controller->home();
    return;
}

if ($method === 'GET' && $path === '/contact') {
    $controller->contact();
    return;
}

if ($method === 'GET' && preg_match('#^/artwork/([a-z0-9\-]+)$#', $path, $matches)) {
    $controller->artwork($matches[1]);
    return;
}

if ($method === 'POST' && $path === '/inquiry') {
    $controller->submitInquiry();
    return;
}

http_response_code(404);
View::render('pages/not-found.php', [
    'title' => 'Page not found — Alexandre Mike Rossi Artworks',
    'metaDescription' => 'Page not found',
]);
