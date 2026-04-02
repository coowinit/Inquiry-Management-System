<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
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

    public function edit(): void
    {
        if (!Auth::can('tools.view')) {
            flash('error', 'You do not have permission to access user management.');
            redirect('dashboard');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $adminModel = new Admin();
        $admin = $adminModel->findById($id);

        if (!$admin) {
            flash('error', 'Admin user not found.');
            redirect('admins');
        }

        $this->view('dashboard/admin-user-edit', [
            'pageTitle' => 'Edit Admin User',
            'adminUser' => $admin,
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
        $role = $this->sanitizeRole((string) ($_POST['role'] ?? 'agent'));
        $status = $this->sanitizeStatus((string) ($_POST['status'] ?? 'active'));
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $email === '' || $password === '') {
            flash('error', 'Username, email and password are required.');
            redirect('admins');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please enter a valid email address.');
            redirect('admins');
        }
        if (strlen($password) < 8) {
            flash('error', 'Password must be at least 8 characters long.');
            redirect('admins');
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
        $role = $this->sanitizeRole((string) ($_POST['role'] ?? 'agent'));
        $status = $this->sanitizeStatus((string) ($_POST['status'] ?? 'active'));

        if ($id <= 0) {
            flash('error', 'Invalid admin id.');
            redirect('admins');
        }

        $adminModel = new Admin();
        $target = $adminModel->findById($id);
        if (!$target) {
            flash('error', 'Admin user not found.');
            redirect('admins');
        }

        $guardMessage = $this->guardAdminChange($target, $role, $status);
        if ($guardMessage !== null) {
            flash('error', $guardMessage);
            redirect('admins');
        }

        $updated = $adminModel->updateRoleAndStatus($id, $role, $status);
        if ($updated) {
            $this->refreshAuthUserIfCurrent($id);
            (new InquiryLog())->create(null, Auth::id(), 'admin_user_updated', 'Updated admin #' . $id . ' to ' . $role . '/' . $status);
            flash('success', 'Admin user updated successfully.');
        } else {
            flash('error', 'Unable to update admin user.');
        }
        redirect('admins');
    }

    public function update(): void
    {
        if (!Auth::can('tools.view') || !Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request.');
            redirect('admins');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $nickname = trim((string) ($_POST['nickname'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $role = $this->sanitizeRole((string) ($_POST['role'] ?? 'agent'));
        $status = $this->sanitizeStatus((string) ($_POST['status'] ?? 'active'));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

        if ($id <= 0) {
            flash('error', 'Invalid admin id.');
            redirect('admins');
        }
        if ($nickname === '' || $email === '') {
            flash('error', 'Nickname and email are required.');
            redirect('admins/edit?id=' . $id);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please enter a valid email address.');
            redirect('admins/edit?id=' . $id);
        }

        $adminModel = new Admin();
        $target = $adminModel->findById($id);
        if (!$target) {
            flash('error', 'Admin user not found.');
            redirect('admins');
        }

        $guardMessage = $this->guardAdminChange($target, $role, $status);
        if ($guardMessage !== null) {
            flash('error', $guardMessage);
            redirect('admins/edit?id=' . $id);
        }

        $adminModel->updateManagedUser($id, [
            'nickname' => $nickname,
            'email' => $email,
            'role' => $role,
            'status' => $status,
        ]);

        if ($password !== '' || $passwordConfirm !== '') {
            if ($password !== $passwordConfirm) {
                flash('error', 'The two passwords do not match.');
                redirect('admins/edit?id=' . $id);
            }
            if (strlen($password) < 8) {
                flash('error', 'Password must be at least 8 characters long.');
                redirect('admins/edit?id=' . $id);
            }
            $adminModel->updatePassword($id, password_hash($password, PASSWORD_DEFAULT));
        }

        $this->refreshAuthUserIfCurrent($id);
        (new InquiryLog())->create(null, Auth::id(), 'admin_user_profile_updated', 'Updated admin profile #' . $id);
        flash('success', 'Admin user saved successfully.');
        redirect('admins/edit?id=' . $id);
    }

    public function toggleStatus(): void
    {
        if (!Auth::can('tools.view') || !Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request.');
            redirect('admins');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $targetStatus = $this->sanitizeStatus((string) ($_POST['target_status'] ?? 'disabled'));

        $adminModel = new Admin();
        $target = $adminModel->findById($id);
        if (!$target) {
            flash('error', 'Admin user not found.');
            redirect('admins');
        }

        $guardMessage = $this->guardAdminChange($target, (string) ($target['role'] ?? 'agent'), $targetStatus);
        if ($guardMessage !== null) {
            flash('error', $guardMessage);
            redirect('admins');
        }

        $updated = $adminModel->updateStatus($id, $targetStatus);
        if ($updated) {
            $this->refreshAuthUserIfCurrent($id);
            $action = $targetStatus === 'disabled' ? 'admin_user_disabled' : 'admin_user_enabled';
            (new InquiryLog())->create(null, Auth::id(), $action, ucfirst($targetStatus) . ' admin #' . $id);
            flash('success', 'Admin user status updated successfully.');
        } else {
            flash('error', 'Unable to update admin status.');
        }
        redirect('admins');
    }

    private function sanitizeRole(string $role): string
    {
        return in_array($role, ['admin', 'manager', 'agent', 'viewer'], true) ? $role : 'agent';
    }

    private function sanitizeStatus(string $status): string
    {
        return in_array($status, ['active', 'disabled'], true) ? $status : 'active';
    }

    private function guardAdminChange(array $target, string $newRole, string $newStatus): ?string
    {
        $id = (int) ($target['id'] ?? 0);
        $currentRole = (string) ($target['role'] ?? 'agent');
        $currentStatus = (string) ($target['status'] ?? 'active');
        $adminModel = new Admin();

        if ($id === (int) Auth::id() && $newStatus === 'disabled') {
            return 'You cannot disable your own account.';
        }

        if ($currentStatus === 'active' && $newStatus === 'disabled' && $adminModel->countActiveAdmins(null, $id) < 1) {
            return 'At least one active administrator account must remain.';
        }

        $isRemovingLastActiveAdmin = $currentRole === 'admin' && $currentStatus === 'active'
            && ($newRole !== 'admin' || $newStatus !== 'active')
            && $adminModel->countActiveAdmins('admin', $id) < 1;

        if ($isRemovingLastActiveAdmin) {
            return 'At least one active admin role account must remain.';
        }

        return null;
    }

    private function refreshAuthUserIfCurrent(int $id): void
    {
        if ($id !== (int) Auth::id()) {
            return;
        }

        $updated = (new Admin())->findById($id);
        if (!$updated) {
            return;
        }

        Session::set('auth_user', [
            'id' => (int) $updated['id'],
            'username' => $updated['username'],
            'nickname' => $updated['nickname'] ?: $updated['username'],
            'email' => $updated['email'],
            'role' => $updated['role'] ?? 'admin',
            'status' => $updated['status'] ?? 'active',
            'page_size' => (int) ($updated['page_size'] ?? 20),
        ]);
    }
}
