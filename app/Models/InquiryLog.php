<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class InquiryLog
{
    public function create(?int $inquiryId, ?int $adminId, string $action, ?string $note = null): bool
    {
        $sql = 'INSERT INTO inquiry_logs (inquiry_id, admin_id, action, action_note) VALUES (:inquiry_id, :admin_id, :action, :action_note)';
        $stmt = Database::connection()->prepare($sql);

        return $stmt->execute([
            'inquiry_id' => $inquiryId,
            'admin_id' => $adminId,
            'action' => $action,
            'action_note' => $note,
        ]);
    }

    public function paginate(int $page = 1, int $perPage = 30): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $pdo = Database::connection();

        $total = (int) $pdo->query('SELECT COUNT(*) FROM inquiry_logs')->fetchColumn();

        $sql = 'SELECT 
                    l.*,
                    a.username AS admin_username,
                    a.nickname AS admin_nickname,
                    i.title AS inquiry_title,
                    i.email AS inquiry_email
                FROM inquiry_logs l
                LEFT JOIN admins a ON a.id = l.admin_id
                LEFT JOIN inquiries i ON i.id = l.inquiry_id
                ORDER BY l.created_at DESC, l.id DESC
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

    public function latestForInquiry(int $inquiryId, int $limit = 8): array
    {
        $sql = 'SELECT 
                    l.*,
                    a.username AS admin_username,
                    a.nickname AS admin_nickname
                FROM inquiry_logs l
                LEFT JOIN admins a ON a.id = l.admin_id
                WHERE l.inquiry_id = :inquiry_id
                ORDER BY l.created_at DESC, l.id DESC
                LIMIT :limit';

        $stmt = Database::connection()->prepare($sql);
        $stmt->bindValue(':inquiry_id', $inquiryId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
