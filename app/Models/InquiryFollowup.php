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
                ORDER BY COALESCE(f.next_contact_at, f.created_at) ASC, f.id DESC
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

    public function reminderStats(?int $adminId = null): array
    {
        $sql = 'SELECT
                    SUM(CASE WHEN is_completed = 0 AND next_contact_at IS NOT NULL AND next_contact_at < NOW() THEN 1 ELSE 0 END) AS overdue_count,
                    SUM(CASE WHEN is_completed = 0 AND next_contact_at IS NOT NULL AND DATE(next_contact_at) = CURDATE() THEN 1 ELSE 0 END) AS today_count,
                    SUM(CASE WHEN is_completed = 0 AND next_contact_at IS NOT NULL AND next_contact_at > NOW() AND next_contact_at <= DATE_ADD(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS next7_count
                FROM inquiry_followups WHERE 1=1';
        $bindings = [];
        if ($adminId !== null && $adminId > 0) {
            $sql .= ' AND admin_id = :admin_id';
            $bindings['admin_id'] = $adminId;
        }
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetch() ?: ['overdue_count' => 0, 'today_count' => 0, 'next7_count' => 0];
    }

    public function paginateReminders(array $filters, int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        [$whereSql, $bindings] = $this->buildReminderWhere($filters);

        $countStmt = Database::connection()->prepare('SELECT COUNT(*) FROM inquiry_followups f ' . $whereSql);
        $countStmt->execute($bindings);
        $total = (int) $countStmt->fetchColumn();

        $sql = 'SELECT f.*, i.name, i.email, i.title, i.status AS inquiry_status, i.site_id, i.form_key,
                       s.site_name,
                       a.username AS admin_username, a.nickname AS admin_nickname
                FROM inquiry_followups f
                INNER JOIN inquiries i ON i.id = f.inquiry_id
                LEFT JOIN inquiry_sites s ON s.id = i.site_id
                LEFT JOIN admins a ON a.id = f.admin_id '
                . $whereSql .
                ' ORDER BY
                    CASE
                        WHEN f.next_contact_at IS NULL THEN 3
                        WHEN f.next_contact_at < NOW() THEN 0
                        WHEN DATE(f.next_contact_at) = CURDATE() THEN 1
                        ELSE 2
                    END ASC,
                    f.next_contact_at ASC,
                    f.id DESC
                LIMIT :limit OFFSET :offset';
        $stmt = Database::connection()->prepare($sql);
        foreach ($bindings as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
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

    public function upcomingForDashboard(?int $adminId = null, int $limit = 6): array
    {
        $filters = ['scope' => $adminId ? 'mine' : 'all', 'admin_id' => $adminId ?? '', 'timing' => 'open_only'];
        return $this->paginateReminders($filters, 1, $limit)['data'];
    }

    private function buildReminderWhere(array $filters): array
    {
        $clauses = [];
        $bindings = [];

        $scope = (string) ($filters['scope'] ?? 'all');
        $adminId = (int) ($filters['admin_id'] ?? 0);
        if ($scope === 'mine' && $adminId > 0) {
            $clauses[] = 'f.admin_id = :admin_id';
            $bindings['admin_id'] = $adminId;
        }

        $siteId = (int) ($filters['site_id'] ?? 0);
        if ($siteId > 0) {
            $clauses[] = 'i.site_id = :site_id';
            $bindings['site_id'] = $siteId;
        }

        $includeCompleted = !empty($filters['include_completed']);
        if (!$includeCompleted) {
            $clauses[] = 'f.is_completed = 0';
        }

        $timing = (string) ($filters['timing'] ?? 'open_only');
        if ($timing === 'overdue') {
            $clauses[] = 'f.next_contact_at IS NOT NULL AND f.next_contact_at < NOW()';
        } elseif ($timing === 'today') {
            $clauses[] = 'f.next_contact_at IS NOT NULL AND DATE(f.next_contact_at) = CURDATE()';
        } elseif ($timing === 'next7') {
            $clauses[] = 'f.next_contact_at IS NOT NULL AND f.next_contact_at > NOW() AND f.next_contact_at <= DATE_ADD(NOW(), INTERVAL 7 DAY)';
        } elseif ($timing === 'unscheduled') {
            $clauses[] = 'f.next_contact_at IS NULL';
        }

        $keyword = trim((string) ($filters['keyword'] ?? ''));
        if ($keyword !== '') {
            $clauses[] = '(i.name LIKE :keyword OR i.email LIKE :keyword OR i.title LIKE :keyword OR f.content LIKE :keyword)';
            $bindings['keyword'] = '%' . $keyword . '%';
        }

        $whereSql = $clauses === [] ? '' : ' WHERE ' . implode(' AND ', $clauses);
        return [$whereSql, $bindings];
    }
}
