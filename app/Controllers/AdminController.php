<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Admin;
use App\Models\InquiryLog;

final class AdminController extends Controller
{
    public function index(): void
    {
        if (!Auth::can('tools.view')) {
            flash('error', 'You do not have permission to access user management.');
            redirect('dashboard');
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = max(10, min(100, (int) (Auth::user()['page_size'] ?? 20)));

        $this->view('dashboard/admin-users', [
            'pageTitle' => 'Admin Users',
            'pagination' => (new Admin())->paginate($page, $perPage),
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function create(): void
    {
        if (!Auth::can('tools.view') || !Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request.');
            redirect('admins');
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $nickname = trim((string) ($_POST['nickname'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $role = trim((string) ($_POST['role'] ?? 'agent'));
        $status = trim((string) ($_POST['status'] ?? 'active'));
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $email === '' || $password === '') {
            flash('error', 'Username, email and password are required.');
            redirect('admins');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please enter a valid email address.');
            redirect('admins');
        }
        if (!in_array($role, ['admin', 'manager', 'agent', 'viewer'], true)) {
            $role = 'agent';
        }
        if (!in_array($status, ['active', 'disabled'], true)) {
            $status = 'active';
        }

        $created = (new Admin())->create([
            'username' => $username,
            'nickname' => $nickname !== '' ? $nickname : $username,
            'email' => $email,
            'website' => null,
            'bio' => null,
            'page_size' => 20,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'status' => $status,
        ]);

        if ($created) {
            (new InquiryLog())->create(null, Auth::id(), 'admin_user_created', 'Created admin user ' . $username);
            flash('success', 'Admin user created successfully.');
        } else {
            flash('error', 'Unable to create admin user. Username may already exist.');
        }

        redirect('admins');
    }

    public function updateMeta(): void
    {
        if (!Auth::can('tools.view') || !Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request.');
            redirect('admins');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $role = trim((string) ($_POST['role'] ?? 'agent'));
        $status = trim((string) ($_POST['status'] ?? 'active'));

        if ($id <= 0) {
            flash('error', 'Invalid admin id.');
            redirect('admins');
        }
        if (!in_array($role, ['admin', 'manager', 'agent', 'viewer'], true)) {
            $role = 'agent';
        }
        if (!in_array($status, ['active', 'disabled'], true)) {
            $status = 'active';
        }

        $updated = (new Admin())->updateRoleAndStatus($id, $role, $status);
        if ($updated) {
            (new InquiryLog())->create(null, Auth::id(), 'admin_user_updated', 'Updated admin #' . $id . ' to ' . $role . '/' . $status);
            flash('success', 'Admin user updated successfully.');
        } else {
            flash('error', 'Unable to update admin user.');
        }
        redirect('admins');
    }
}
