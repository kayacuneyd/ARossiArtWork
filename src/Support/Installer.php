<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\AdminRepository;
use PDOException;

class Installer
{
    public static function ensureDefaultAdmin(): void
    {
        try {
            $repo = AdminRepository::make();
            $admins = $repo->all();
            if (count($admins) === 0) {
                $username = env('ADMIN_DEFAULT_USER', 'admin');
                $password = env('ADMIN_DEFAULT_PASS', 'ChangeMe123!');
                $email = env('ARTIST_EMAIL', null);
                $repo->create($username, password_hash($password, PASSWORD_DEFAULT), $email);
            }
        } catch (PDOException $exception) {
            // Likely that migrations not yet run; swallow for installer step
        }
    }
}
