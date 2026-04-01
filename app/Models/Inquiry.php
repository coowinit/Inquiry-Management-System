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
        $spam = (int) $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'spam'")->fetchColumn();
        $today = (int) $pdo->query('SELECT COUNT(*) FROM inquiries WHERE DATE(created_at) = CURRENT_DATE()')->fetchColumn();
        $assigned = (int) $pdo->query('SELECT COUNT(*) FROM inquiries WHERE assigned_admin_id IS NOT NULL')->fetchColumn();

        return compact('total', 'unread', 'read', 'trash', 'spam', 'today', 'assigned');
    }

    public function latest(int $limit = 8): array
    {
        $sql = 'SELECT i.*, s.site_name,
                       aa.username AS assigned_username,
                       aa.nickname AS assigned_nickname
                FROM inquiries i
                LEFT JOIN inquiry_sites s ON s.id = i.site_id
                LEFT JOIN admins aa ON aa.id = i.assigned_admin_id
                ORDER BY i.created_at DESC LIMIT :limit';
        $stmt = Database::connection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function dailyTrend(int $days = 7): array
    {
        $days = max(2, $days);
        $start = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));

        $stmt = Database::connection()->prepare(
            'SELECT DATE(created_at) AS day,
                    COUNT(*) AS total_count,
                    SUM(CASE WHEN status = "unread" THEN 1 ELSE 0 END) AS unread_count,
                    SUM(CASE WHEN status = "spam" THEN 1 ELSE 0 END) AS spam_count
             FROM inquiries
             WHERE DATE(created_at) >= :start
             GROUP BY DATE(created_at)
             ORDER BY day ASC'
        );
        $stmt->execute(['start' => $start]);
        $rows = $stmt->fetchAll();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['day']] = $row;
        }

        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime('-' . $i . ' days'));
            $row = $indexed[$day] ?? null;
            $result[] = [
                'day' => $day,
                'label' => date('m-d', strtotime($day)),
                'total_count' => (int) ($row['total_count'] ?? 0),
                'unread_count' => (int) ($row['unread_count'] ?? 0),
                'spam_count' => (int) ($row['spam_count'] ?? 0),
            ];
        }

        return $result;
    }

    public function topForms(int $limit = 8, int $days = 0): array
    {
        $where = '';
        $params = [];
        if ($days > 0) {
            $where = 'WHERE DATE(i.created_at) >= :start';
            $params['start'] = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        }

        $sql = 'SELECT
                    COALESCE(i.form_key, "general_form") AS form_key,
                    COALESCE(s.site_name, "Unknown site") AS site_name,
                    COUNT(*) AS total_count,
                    SUM(CASE WHEN i.status = "unread" THEN 1 ELSE 0 END) AS unread_count,
                    MAX(i.created_at) AS last_inquiry_at
                FROM inquiries i
                LEFT JOIN inquiry_sites s ON s.id = i.site_id
                ' . $where . '
                GROUP BY i.site_id, COALESCE(i.form_key, "general_form"), COALESCE(s.site_name, "Unknown site")
                ORDER BY total_count DESC, last_inquiry_at DESC
                LIMIT :limit';
        $stmt = Database::connection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countrySummary(int $limit = 8, int $days = 0): array
    {
        $where = '';
        $params = [];
        if ($days > 0) {
            $where = 'WHERE DATE(created_at) >= :start';
            $params['start'] = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        }

        $sql = 'SELECT COALESCE(NULLIF(country, ""), "Unknown") AS country_name, COUNT(*) AS total_count
                FROM inquiries
                ' . $where . '
                GROUP BY COALESCE(NULLIF(country, ""), "Unknown")
                ORDER BY total_count DESC, country_name ASC
                LIMIT :limit';
        $stmt = Database::connection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function siteBreakdown(int $days = 30, int $limit = 12): array
    {
        $start = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        $sql = 'SELECT COALESCE(s.site_name, "Unknown site") AS site_name,
                       COALESCE(s.site_domain, "-") AS site_domain,
                       COUNT(*) AS total_count,
                       SUM(CASE WHEN i.status = "unread" THEN 1 ELSE 0 END) AS unread_count,
                       SUM(CASE WHEN i.status = "spam" THEN 1 ELSE 0 END) AS spam_count,
                       MAX(i.created_at) AS last_inquiry_at
                FROM inquiries i
                LEFT JOIN inquiry_sites s ON s.id = i.site_id
                WHERE DATE(i.created_at) >= :start
                GROUP BY COALESCE(s.site_name, "Unknown site"), COALESCE(s.site_domain, "-")
                ORDER BY total_count DESC, last_inquiry_at DESC
                LIMIT :limit';
        $stmt = Database::connection()->prepare($sql);
        $stmt->bindValue(':start', $start);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function statusBreakdown(int $days = 30): array
    {
        $start = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        $stmt = Database::connection()->prepare('SELECT status, COUNT(*) AS total_count
                FROM inquiries
                WHERE DATE(created_at) >= :start
                GROUP BY status
                ORDER BY total_count DESC');
        $stmt->execute(['start' => $start]);
        $rows = $stmt->fetchAll();
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['status']] = (int) $row['total_count'];
        }
        $result = [];
        foreach (['unread', 'read', 'spam', 'trash'] as $status) {
            $result[] = ['status' => $status, 'total_count' => $indexed[$status] ?? 0];
        }
        return $result;
    }

    public function assigneeSummary(int $days = 30, int $limit = 10): array
    {
        $start = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        $sql = 'SELECT COALESCE(a.nickname, a.username, "Unassigned") AS assignee_name,
                       COUNT(*) AS total_count,
                       SUM(CASE WHEN i.status = "unread" THEN 1 ELSE 0 END) AS unread_count,
                       SUM(CASE WHEN i.status = "spam" THEN 1 ELSE 0 END) AS spam_count
                FROM inquiries i
                LEFT JOIN admins a ON a.id = i.assigned_admin_id
                WHERE DATE(i.created_at) >= :start
                GROUP BY COALESCE(a.nickname, a.username, "Unassigned")
                ORDER BY total_count DESC
                LIMIT :limit';
        $stmt = Database::connection()->prepare($sql);
        $stmt->bindValue(':start', $start);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function overviewForDays(int $days = 30): array
    {
        $start = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        $stmt = Database::connection()->prepare('SELECT
                COUNT(*) AS total_count,
                SUM(CASE WHEN status = "unread" THEN 1 ELSE 0 END) AS unread_count,
                SUM(CASE WHEN status = "read" THEN 1 ELSE 0 END) AS read_count,
                SUM(CASE WHEN status = "spam" THEN 1 ELSE 0 END) AS spam_count,
                SUM(CASE WHEN status = "trash" THEN 1 ELSE 0 END) AS trash_count,
                SUM(CASE WHEN assigned_admin_id IS NOT NULL THEN 1 ELSE 0 END) AS assigned_count,
                COUNT(DISTINCT COALESCE(site_id, 0)) AS site_count,
                COUNT(DISTINCT COALESCE(form_key, "general_form")) AS form_count
            FROM inquiries
            WHERE DATE(created_at) >= :start');
        $stmt->execute(['start' => $start]);
        return $stmt->fetch() ?: [];
    }

    public function paginate(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        [$whereSql, $bindings] = $this->buildWhere($filters);

        $countSql = 'SELECT COUNT(*) FROM inquiries i ' . $whereSql;
        $countStmt = Database::connection()->prepare($countSql);
        $countStmt->execute($bindings);
        $total = (int) $countStmt->fetchColumn();

        $sql = 'SELECT i.*, s.site_name,
                       aa.username AS assigned_username,
                       aa.nickname AS assigned_nickname,
                       COALESCE(fc.followup_count, 0) AS followup_count,
                       fc.last_followup_at
                FROM inquiries i
                LEFT JOIN inquiry_sites s ON s.id = i.site_id
                LEFT JOIN admins aa ON aa.id = i.assigned_admin_id
                LEFT JOIN (
                    SELECT inquiry_id, COUNT(*) AS followup_count, MAX(created_at) AS last_followup_at
                    FROM inquiry_followups
                    GROUP BY inquiry_id
                ) fc ON fc.inquiry_id = i.id '
                . $whereSql .
                ' ORDER BY i.created_at DESC
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

    public function exportRows(array $filters = [], array $columns = [], int $limit = 5000): array
    {
        $allowedColumns = $this->allowedExportColumns();
        $columns = array_values(array_intersect($columns ?: array_keys($allowedColumns), array_keys($allowedColumns)));
        if ($columns === []) {
            $columns = array_keys($allowedColumns);
        }

        [$whereSql, $bindings] = $this->buildWhere($filters);

        $selectParts = [];
        foreach ($columns as $column) {
            $selectParts[] = $allowedColumns[$column] . ' AS ' . $column;
        }

        $sql = 'SELECT ' . implode(', ', $selectParts) . '
                FROM inquiries i
                LEFT JOIN inquiry_sites s ON s.id = i.site_id
                LEFT JOIN admins aa ON aa.id = i.assigned_admin_id '
                . $whereSql .
                ' ORDER BY i.created_at DESC
                LIMIT :limit';

        $stmt = Database::connection()->prepare($sql);
        foreach ($bindings as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function allowedExportColumns(): array
    {
        return [
            'id' => 'i.id',
            'site_name' => 'COALESCE(s.site_name, "")',
            'form_key' => 'COALESCE(i.form_key, "")',
            'status' => 'i.status',
            'assigned_to' => 'COALESCE(aa.nickname, aa.username, "")',
            'name' => 'i.name',
            'email' => 'i.email',
            'title' => 'COALESCE(i.title, "")',
            'content' => 'i.content',
            'country' => 'COALESCE(i.country, "")',
            'phone' => 'COALESCE(i.phone, "")',
            'address' => 'COALESCE(i.address, "")',
            'from_company' => 'COALESCE(i.from_company, "")',
            'source_url' => 'COALESCE(i.source_url, "")',
            'referer_url' => 'COALESCE(i.referer_url, "")',
            'ip' => 'COALESCE(i.ip, "")',
            'browser' => 'COALESCE(i.browser, "")',
            'device_type' => 'COALESCE(i.device_type, "")',
            'language' => 'COALESCE(i.language, "")',
            'admin_note' => 'COALESCE(i.admin_note, "")',
            'submitted_at' => 'COALESCE(i.submitted_at, "")',
            'created_at' => 'i.created_at',
            'updated_at' => 'i.updated_at',
            'extra_data' => 'COALESCE(i.extra_data, "")',
        ];
    }

    public function find(int $id): array|false
    {
        $sql = 'SELECT i.*, s.site_name, s.site_domain,
                       aa.username AS assigned_username,
                       aa.nickname AS assigned_nickname,
                       aa.email AS assigned_email
                FROM inquiries i
                LEFT JOIN inquiry_sites s ON s.id = i.site_id
                LEFT JOIN admins aa ON aa.id = i.assigned_admin_id
                WHERE i.id = :id
                LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO inquiries (
                    site_id, form_key, name, email, title, content, country, phone, address, from_company,
                    source_url, referer_url, ip, user_agent, browser, device_type, language,
                    status, is_read, is_spam, admin_note, assigned_admin_id, extra_data, raw_payload, submitted_at
                ) VALUES (
                    :site_id, :form_key, :name, :email, :title, :content, :country, :phone, :address, :from_company,
                    :source_url, :referer_url, :ip, :user_agent, :browser, :device_type, :language,
                    :status, :is_read, :is_spam, :admin_note, :assigned_admin_id, :extra_data, :raw_payload, :submitted_at
                )';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'site_id' => $data['site_id'],
            'form_key' => $data['form_key'],
            'name' => $data['name'],
            'email' => $data['email'],
            'title' => $data['title'],
            'content' => $data['content'],
            'country' => $data['country'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'from_company' => $data['from_company'],
            'source_url' => $data['source_url'],
            'referer_url' => $data['referer_url'],
            'ip' => $data['ip'],
            'user_agent' => $data['user_agent'],
            'browser' => $data['browser'],
            'device_type' => $data['device_type'],
            'language' => $data['language'],
            'status' => $data['status'],
            'is_read' => $data['is_read'],
            'is_spam' => $data['is_spam'],
            'admin_note' => $data['admin_note'],
            'assigned_admin_id' => $data['assigned_admin_id'] ?? null,
            'extra_data' => $data['extra_data'],
            'raw_payload' => $data['raw_payload'],
            'submitted_at' => $data['submitted_at'],
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public function existsRecentDuplicate(string $email, string $content, int $minutes = 10): bool
    {
        $cutoff = date('Y-m-d H:i:s', time() - ($minutes * 60));

        $sql = 'SELECT id FROM inquiries
                WHERE email = :email AND content = :content AND created_at >= :cutoff
                LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'email' => $email,
            'content' => $content,
            'cutoff' => $cutoff,
        ]);
        return (bool) $stmt->fetchColumn();
    }

    public function recentCountByIp(string $ip, int $minutes = 10): int
    {
        $cutoff = date('Y-m-d H:i:s', time() - ($minutes * 60));

        $sql = 'SELECT COUNT(*) FROM inquiries WHERE ip = :ip AND created_at >= :cutoff';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'ip' => $ip,
            'cutoff' => $cutoff,
        ]);
        return (int) $stmt->fetchColumn();
    }

    public function recentCountByEmail(string $email, int $minutes = 10): int
    {
        $cutoff = date('Y-m-d H:i:s', time() - ($minutes * 60));

        $sql = 'SELECT COUNT(*) FROM inquiries WHERE email = :email AND created_at >= :cutoff';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'email' => $email,
            'cutoff' => $cutoff,
        ]);
        return (int) $stmt->fetchColumn();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $isRead = $status === 'unread' ? 0 : 1;
        $isSpam = $status === 'spam' ? 1 : 0;

        $sql = 'UPDATE inquiries SET status = :status, is_read = :is_read, is_spam = :is_spam, updated_at = NOW() WHERE id = :id';
        $stmt = Database::connection()->prepare($sql);
        return $stmt->execute([
            'status' => $status,
            'is_read' => $isRead,
            'is_spam' => $isSpam,
            'id' => $id,
        ]);
    }

    public function updateNote(int $id, ?string $note): bool
    {
        $stmt = Database::connection()->prepare('UPDATE inquiries SET admin_note = :note, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([
            'note' => $note,
            'id' => $id,
        ]);
    }

    public function updateAssignedAdmin(int $id, ?int $adminId): bool
    {
        $stmt = Database::connection()->prepare('UPDATE inquiries SET assigned_admin_id = :admin_id, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([
            'admin_id' => $adminId,
            'id' => $id,
        ]);
    }

    public function bulkUpdateStatus(array $ids, string $status): int
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return 0;
        }

        $isRead = $status === 'unread' ? 0 : 1;
        $isSpam = $status === 'spam' ? 1 : 0;
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = 'UPDATE inquiries SET status = ?, is_read = ?, is_spam = ?, updated_at = NOW() WHERE id IN (' . $placeholders . ')';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(array_merge([$status, $isRead, $isSpam], $ids));
        return $stmt->rowCount();
    }

    public function bulkAssign(array $ids, ?int $adminId): int
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = 'UPDATE inquiries SET assigned_admin_id = ?, updated_at = NOW() WHERE id IN (' . $placeholders . ')';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(array_merge([$adminId], $ids));
        return $stmt->rowCount();
    }

    private function buildWhere(array $filters): array
    {
        $clauses = [];
        $bindings = [];

        if (!empty($filters['status'])) {
            $clauses[] = 'i.status = :status';
            $bindings['status'] = $filters['status'];
        }

        if (!empty($filters['site_id'])) {
            $clauses[] = 'i.site_id = :site_id';
            $bindings['site_id'] = (int) $filters['site_id'];
        }

        if (!empty($filters['date_from'])) {
            $clauses[] = 'DATE(i.created_at) >= :date_from';
            $bindings['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $clauses[] = 'DATE(i.created_at) <= :date_to';
            $bindings['date_to'] = $filters['date_to'];
        }

        if (!empty($filters['keyword'])) {
            $clauses[] = '(i.title LIKE :keyword OR i.content LIKE :keyword OR i.name LIKE :keyword OR i.email LIKE :keyword OR i.from_company LIKE :keyword OR i.admin_note LIKE :keyword)';
            $bindings['keyword'] = '%' . $filters['keyword'] . '%';
        }

        if (($filters['has_note'] ?? '') === 'yes') {
            $clauses[] = 'i.admin_note IS NOT NULL AND i.admin_note <> ""';
        }

        if (($filters['has_note'] ?? '') === 'no') {
            $clauses[] = '(i.admin_note IS NULL OR i.admin_note = "")';
        }

        if (($filters['assigned_admin_id'] ?? '') === 'unassigned') {
            $clauses[] = 'i.assigned_admin_id IS NULL';
        } elseif (($filters['assigned_admin_id'] ?? '') !== '' && $filters['assigned_admin_id'] !== null) {
            $clauses[] = 'i.assigned_admin_id = :assigned_admin_id';
            $bindings['assigned_admin_id'] = (int) $filters['assigned_admin_id'];
        }

        $whereSql = '';
        if (!empty($clauses)) {
            $whereSql = ' WHERE ' . implode(' AND ', $clauses);
        }

        return [$whereSql, $bindings];
    }
}
