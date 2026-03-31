<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Admin;


final class SettingsController extends Controller
{
    public function profile(): void
    {
        $adminModel = new Admin();
        $user = $adminModel->findById((int) Auth::id());

        $this->view('dashboard/profile', [
            'pageTitle' => 'My Profile',
            'user' => $user,
            'csrfToken' => Csrf::token(),
            'error' => get_flash('error'),
            'success' => get_flash('success'),
        ]);
    }

    public function updateProfile(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request token.');
            redirect('profile');
        }

        $id = (int) Auth::id();
        $adminModel = new Admin();

        $nickname = trim((string) ($_POST['nickname'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $website = trim((string) ($_POST['website'] ?? ''));
        $pageSize = max(10, min(100, (int) ($_POST['page_size'] ?? 20)));
        $bio = trim((string) ($_POST['bio'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

        if ($nickname === '' || $email === '') {
            flash('error', 'Nickname and email are required.');
            redirect('profile');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please enter a valid email address.');
            redirect('profile');
        }

        $adminModel->updateProfile($id, [
            'nickname' => $nickname,
            'email' => $email,
            'website' => $website,
            'page_size' => $pageSize,
            'bio' => $bio,
        ]);

        if ($password !== '' || $passwordConfirm !== '') {
            if ($password !== $passwordConfirm) {
                flash('error', 'The two passwords do not match.');
                redirect('profile');
            }

            if (strlen($password) < 8) {
                flash('error', 'Password must be at least 8 characters long.');
                redirect('profile');
            }

            $adminModel->updatePassword($id, password_hash($password, PASSWORD_DEFAULT));
        }

        $updated = $adminModel->findById($id);

        $_SESSION['auth_user'] = [
            'id' => (int) $updated['id'],
            'username' => $updated['username'],
            'nickname' => $updated['nickname'] ?: $updated['username'],
            'email' => $updated['email'],
        ];

        flash('success', 'Profile updated successfully.');
        redirect('profile');
    }
}
