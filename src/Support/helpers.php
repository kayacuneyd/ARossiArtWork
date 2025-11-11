<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid;

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 1);
        return $path ? $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $base;
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $path = ltrim($path, '/');
        $base = rtrim(env('APP_URL', ''), '/');
        return $base ? $base . '/' . $path : '/' . $path;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_token" value="' . $token . '">';
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf(?string $token): bool
    {
        return is_string($token) && hash_equals(csrf_token(), $token);
    }
}

if (!function_exists('uuid_str')) {
    function uuid_str(): string
    {
        return Uuid::uuid4()->toString();
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}

if (!function_exists('str_slug')) {
    function str_slug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
        $value = trim((string) $value, '-');
        return $value ?: bin2hex(random_bytes(4));
    }
}
