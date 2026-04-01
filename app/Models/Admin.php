<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use PDOException;

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

    public function allBrief(): array
    {
        $sql = "SELECT id, username, nickname, email, role, status
                FROM admins
                WHERE status = 'active'
                ORDER BY COALESCE(NULLIF(nickname, ''), username) ASC, id ASC";
        return Database::connection()->query($sql)->fetchAll();
    }

    public function paginate(int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $pdo = Database::connection();
        $total = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();

        $sql = 'SELECT a.*, COUNT(i.id) AS assigned_inquiry_count
                FROM admins a
                LEFT JOIN inquiries i ON i.assigned_admin_id = a.id
                GROUP BY a.id
                ORDER BY a.id ASC
                LIMIT :limit OFFSET :offset';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / max(1, $perPage)),
        ];
    }

    public function create(array $data): bool
    {
        $sql = 'INSERT INTO admins (username, nickname, email, website, bio, page_size, password_hash, role, status)
                VALUES (:username, :nickname, :email, :website, :bio, :page_size, :password_hash, :role, :status)';
        try {
            $stmt = Database::connection()->prepare($sql);
            return $stmt->execute([
                'username' => $data['username'],
                'nickname' => $data['nickname'],
                'email' => $data['email'],
                'website' => $data['website'],
                'bio' => $data['bio'],
                'page_size' => $data['page_size'],
                'password_hash' => $data['password_hash'],
                'role' => $data['role'],
                'status' => $data['status'],
            ]);
        } catch (PDOException) {
            return false;
        }
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

    public function updateRoleAndStatus(int $id, string $role, string $status): bool
    {
        $sql = 'UPDATE admins SET role = :role, status = :status, updated_at = NOW() WHERE id = :id';
        $stmt = Database::connection()->prepare($sql);
        return $stmt->execute([
            'role' => $role,
            'status' => $status,
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

    public function touchLastLogin(int $id): bool
    {
        $stmt = Database::connection()->prepare('UPDATE admins SET last_login_at = NOW(), updated_at = NOW() WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
