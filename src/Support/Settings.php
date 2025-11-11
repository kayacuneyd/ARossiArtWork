<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\SettingRepository;
use PDOException;

class Settings
{
    private static ?array $cache = null;

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = self::all();
        if (array_key_exists($key, $all)) {
            return $all[$key];
        }

        $envKey = strtoupper($key);
        return env($envKey, $default);
    }

    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        try {
            $repo = SettingRepository::make();
            self::$cache = $repo->all();
        } catch (PDOException $exception) {
            self::$cache = [];
        }

        return self::$cache;
    }

    public static function refresh(): void
    {
        self::$cache = null;
        self::all();
    }
}
