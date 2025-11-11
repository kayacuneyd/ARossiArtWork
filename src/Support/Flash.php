<?php

declare(strict_types=1);

namespace App\Support;

class Flash
{
    private const SESSION_KEY = '_flash';

    public static function set(string $type, string $message): void
    {
        $_SESSION[self::SESSION_KEY][$type][] = $message;
    }

    public static function get(string $type): array
    {
        $messages = $_SESSION[self::SESSION_KEY][$type] ?? [];
        unset($_SESSION[self::SESSION_KEY][$type]);
        return $messages;
    }

    public static function all(): array
    {
        $all = $_SESSION[self::SESSION_KEY] ?? [];
        unset($_SESSION[self::SESSION_KEY]);
        return $all;
    }
}
