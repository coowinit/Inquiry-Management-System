<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class InquiryFollowup
{
    public function create(array $data): int
    {
        $sql = 'INSERT INTO inquiry_followups (
                    inquiry_id,
                    admin_id,
                    followup_type,
                    content,
                    next_contact_at,
                    is_completed
                ) VALUES (
                    :inquiry_id,
                    :admin_id,
                    :followup_type,
                    :content,
                    :next_contact_at,
                    :is_completed
                )';

        $stmt = Database::connection()->prepare($sql);
        $ok = $stmt->execute([
            'inquiry_id' => (int) ($data['inquiry_id'] ?? 0),
            'admin_id' => isset($data['admin_id']) && $data['admin_id'] !== '' ? (int) $data['admin_id'] : null,
            'followup_type' => (string) ($data['followup_type'] ?? 'note'),
            'content' => (string) ($data['content'] ?? ''),
            'next_contact_at' => $data['next_contact_at'] ?? null,
            'is_completed' => !empty($data['is_completed']) ? 1 : 0,
        ]);

        return $ok ? (int) Database::connection()->lastInsertId() : 0;
    }

    public function latestForInquiry(int $inquiryId, int $limit = 20): array
    {
        $sql = 'SELECT
                    f.*,
                    a.username AS admin_username,
                    a.nickname AS admin_nickname
                FROM inquiry_followups f
                LEFT JOIN admins a ON a.id = f.admin_id
                WHERE f.inquiry_id = :inquiry_id
                ORDER BY f.created_at DESC, f.id DESC
                LIMIT :limit';

        $stmt = Database::connection()->prepare($sql);
        $stmt->bindValue(':inquiry_id', $inquiryId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countOpenByAssignee(int $adminId): int
    {
        if ($adminId <= 0) {
            return 0;
        }

        $sql = 'SELECT COUNT(*)
                FROM inquiry_followups f
                INNER JOIN inquiries i ON i.id = f.inquiry_id
                WHERE i.assigned_admin_id = :admin_id
                  AND f.is_completed = 0';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['admin_id' => $adminId]);

        return (int) $stmt->fetchColumn();
    }
}
