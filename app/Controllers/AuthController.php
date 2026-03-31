<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;


final class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect('dashboard');
        }

        $this->view('auth/login', [
            'pageTitle' => 'Login',
            'csrfToken' => Csrf::token(),
            'error' => get_flash('error'),
            'success' => get_flash('success'),
        ], 'layouts/guest');
    }

    public function login(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request token. Please try again.');
            redirect('login');
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        flash_old_input();

        if ($username === '' || $password === '') {
            flash('error', 'Username and password are required.');
            redirect('login');
        }

        if (!Auth::attempt($username, $password)) {
            flash('error', 'Invalid username or password.');
            redirect('login');
        }

        clear_old_input();
        flash('success', 'Login successful.');
        redirect('dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        flash('success', 'You have logged out.');
        redirect('login');
    }
}
