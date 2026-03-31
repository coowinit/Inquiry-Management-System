<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

use PDO;


final class Inquiry
{
    public function stats(): array
    {
        $pdo = Database::connection();

        $total = (int) $pdo->query('SELECT COUNT(*) FROM inquiries')->fetchColumn();
        $unread = (int) $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'unread'")->fetchColumn();
        $read = (int) $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'read'")->fetchColumn();
        $trash = (int) $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'trash'")->fetchColumn();

        return compact('total', 'unread', 'read', 'trash');
    }

    public function latest(int $limit = 8): array
    {
        $sql = 'SELECT i.*, s.site_name FROM inquiries i LEFT JOIN inquiry_sites s ON s.id = i.site_id ORDER BY i.created_at DESC LIMIT :limit';
        $stmt = Database::connection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function paginate(int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $total = (int) Database::connection()->query('SELECT COUNT(*) FROM inquiries')->fetchColumn();

        $sql = 'SELECT i.*, s.site_name
                FROM inquiries i
                LEFT JOIN inquiry_sites s ON s.id = i.site_id
                ORDER BY i.created_at DESC
                LIMIT :limit OFFSET :offset';
        $stmt = Database::connection()->prepare($sql);
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

    public function find(int $id): array|false
    {
        $sql = 'SELECT i.*, s.site_name, s.site_domain
                FROM inquiries i
                LEFT JOIN inquiry_sites s ON s.id = i.site_id
                WHERE i.id = :id
                LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}
