<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\AdminRepository;

class AdminAuth
{
    private const SESSION_KEY = 'admin_id';

    public function __construct(private readonly AdminRepository $admins)
    {
    }

    public static function make(): self
    {
        return new self(AdminRepository::make());
    }

    public function attempt(string $username, string $password): bool
    {
        $admin = $this->admins->findByUsername($username);
        if (!$admin || (int) ($admin['is_active'] ?? 1) !== 1) {
            return false;
        }

        if (!password_verify($password, $admin['password_hash'])) {
            return false;
        }

        if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $this->admins->updatePassword((int) $admin['id'], $newHash);
        }

        $_SESSION[self::SESSION_KEY] = (int) $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        return true;
    }

    public function check(): bool
    {
        return isset($_SESSION[self::SESSION_KEY]);
    }

    public function id(): ?int
    {
        return $this->check() ? (int) $_SESSION[self::SESSION_KEY] : null;
    }

    public function username(): ?string
    {
        return $_SESSION['admin_username'] ?? null;
    }

    public function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY], $_SESSION['admin_username']);
    }
}
