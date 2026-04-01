<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class InquiryFollowup
{
    public function latestForInquiry(int $inquiryId, int $limit = 20): array
    {
        $sql = 'SELECT f.*, a.username AS admin_username, a.nickname AS admin_nickname
                FROM inquiry_followups f
                LEFT JOIN admins a ON a.id = f.admin_id
                WHERE f.inquiry_id = :inquiry_id
                ORDER BY f.created_at DESC, f.id DESC
                LIMIT :limit';
        $stmt = Database::connection()->prepare($sql);
        $stmt->bindValue(':inquiry_id', $inquiryId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = Database::connection()->prepare('SELECT * FROM inquiry_followups WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO inquiry_followups (inquiry_id, admin_id, followup_type, content, next_contact_at, is_completed, completed_at, updated_at)
                VALUES (:inquiry_id, :admin_id, :followup_type, :content, :next_contact_at, :is_completed, :completed_at, NOW())';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'inquiry_id' => $data['inquiry_id'],
            'admin_id' => $data['admin_id'],
            'followup_type' => $data['followup_type'],
            'content' => $data['content'],
            'next_contact_at' => $data['next_contact_at'],
            'is_completed' => $data['is_completed'],
            'completed_at' => !empty($data['is_completed']) ? date('Y-m-d H:i:s') : null,
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE inquiry_followups
                SET followup_type = :followup_type,
                    content = :content,
                    next_contact_at = :next_contact_at,
                    is_completed = :is_completed,
                    completed_at = :completed_at,
                    updated_at = NOW()
                WHERE id = :id';
        $stmt = Database::connection()->prepare($sql);
        return $stmt->execute([
            'followup_type' => $data['followup_type'],
            'content' => $data['content'],
            'next_contact_at' => $data['next_contact_at'],
            'is_completed' => $data['is_completed'],
            'completed_at' => !empty($data['is_completed']) ? date('Y-m-d H:i:s') : null,
            'id' => $id,
        ]);
    }

    public function markCompleted(int $id, bool $completed): bool
    {
        $stmt = Database::connection()->prepare('UPDATE inquiry_followups SET is_completed = :is_completed, completed_at = :completed_at, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([
            'is_completed' => $completed ? 1 : 0,
            'completed_at' => $completed ? date('Y-m-d H:i:s') : null,
            'id' => $id,
        ]);
    }

    public function countOpenByAssignee(int $adminId): int
    {
        if ($adminId <= 0) {
            return 0;
        }
        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM inquiry_followups WHERE admin_id = :admin_id AND is_completed = 0');
        $stmt->execute(['admin_id' => $adminId]);
        return (int) $stmt->fetchColumn();
    }
}
