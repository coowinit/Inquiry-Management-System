<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;


final class Admin
{
    public function findByUsername(string $username): array|false
    {
        $sql = 'SELECT * FROM admins WHERE username = :username LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['username' => $username]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false
    {
        $sql = 'SELECT * FROM admins WHERE id = :id LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function updateProfile(int $id, array $data): bool
    {
        $sql = 'UPDATE admins SET nickname = :nickname, email = :email, website = :website, page_size = :page_size, bio = :bio, updated_at = NOW() WHERE id = :id';
        $stmt = Database::connection()->prepare($sql);
        return $stmt->execute([
            'nickname' => $data['nickname'],
            'email' => $data['email'],
            'website' => $data['website'],
            'page_size' => $data['page_size'],
            'bio' => $data['bio'],
            'id' => $id,
        ]);
    }

    public function updatePassword(int $id, string $passwordHash): bool
    {
        $sql = 'UPDATE admins SET password_hash = :password_hash, updated_at = NOW() WHERE id = :id';
        $stmt = Database::connection()->prepare($sql);
        return $stmt->execute([
            'password_hash' => $passwordHash,
            'id' => $id,
        ]);
    }
}
