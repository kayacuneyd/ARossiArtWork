<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Support\AdminAuth;
use App\Support\Flash;
use App\Support\Installer;
use App\Support\View;
use Respect\Validation\Validator as v;

class AuthController
{
    public function __construct(private readonly AdminAuth $auth)
    {
        Installer::ensureDefaultAdmin();
    }

    public static function make(): self
    {
        return new self(AdminAuth::make());
    }

    public function showLogin(): void
    {
        if ($this->auth->check()) {
            redirect('/admin');
        }

        View::render('admin/login.php', [
            'title' => 'Admin Login — Alexandre Mike Rossi Artworks',
            'flash' => Flash::all(),
        ], 'admin/layout.php');
    }

    public function login(): void
    {
        if (!verify_csrf($_POST['_token'] ?? null)) {
            Flash::set('error', 'Invalid session token. Please try again.');
            redirect('/admin/login');
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $validator = v::key('username', v::stringType()->notEmpty())
            ->key('password', v::stringType()->length(6, null));

        try {
            $validator->assert([
                'username' => $username,
                'password' => $password,
            ]);
        } catch (\InvalidArgumentException $exception) {
            Flash::set('error', 'Please provide a valid username and password.');
            redirect('/admin/login');
        }

        if (!$this->auth->attempt($username, $password)) {
            Flash::set('error', 'Incorrect credentials or inactive account.');
            redirect('/admin/login');
        }

        Flash::set('success', 'Welcome back, ' . $this->auth->username() . '!');
        redirect('/admin');
    }

    public function logout(): void
    {
        $this->auth->logout();
        Flash::set('success', 'You have been signed out.');
        redirect('/admin/login');
    }
}
