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

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        Session::set('auth_user', [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'nickname' => $user['nickname'] ?: $user['username'],
            'email' => $user['email'],
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

    public static function logout(): void
    {
        Session::forget('auth_user');
    }
}
