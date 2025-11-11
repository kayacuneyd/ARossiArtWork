<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Support/helpers.php';

const BASE_PATH = __DIR__ . '/..';

$envFile = file_exists(BASE_PATH . '/.env') ? '.env' : '.env.example';
$dotenv = Dotenv::createImmutable(BASE_PATH, $envFile);
$dotenv->safeLoad();

if (env('APP_DEBUG', false)) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

$timezone = env('APP_TIMEZONE', 'Europe/London');
date_default_timezone_set($timezone);

session_name(env('SESSION_NAME', 'arossi_session'));
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function app_path(string $path = ''): string
{
    $base = BASE_PATH;
    return $path ? $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $base;
}
