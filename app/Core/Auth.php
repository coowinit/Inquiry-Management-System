<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Admin;

final class Auth
{
    public static function attempt(string $username, string $password): bool
    {
        $adminModel = new Admin();
        $user = $adminModel->findByUsername($username);

        if (!$user) {
            return false;
        }

        if (($user['status'] ?? 'active') !== 'active') {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        $adminModel->touchLastLogin((int) $user['id']);

        Session::set('auth_user', [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'nickname' => $user['nickname'] ?: $user['username'],
            'email' => $user['email'],
            'role' => $user['role'] ?? 'admin',
            'status' => $user['status'] ?? 'active',
        ]);

        return true;
    }

    public static function user(): ?array
    {
        $user = Session::get('auth_user');
        return is_array($user) ? $user : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function role(): string
    {
        return (string) (self::user()['role'] ?? 'guest');
    }

    public static function can(string $permission): bool
    {
        $role = self::role();
        $map = [
            'viewer' => ['dashboard.view', 'inquiries.view', 'reports.view', 'profile.manage'],
            'agent' => ['dashboard.view', 'inquiries.view', 'inquiries.update', 'followups.manage', 'reports.view', 'profile.manage'],
            'manager' => ['dashboard.view', 'inquiries.view', 'inquiries.update', 'followups.manage', 'reports.view', 'sites.view', 'tools.view', 'logs.view', 'api_logs.view', 'profile.manage'],
            'admin' => ['*'],
        ];
        $grants = $map[$role] ?? [];
        return in_array('*', $grants, true) || in_array($permission, $grants, true);
    }

    public static function logout(): void
    {
        Session::forget('auth_user');
    }
}
