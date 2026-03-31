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

        return compact('total', 'unread', 'read', 'trash', 'spam', 'today');
    }

    public function latest(int $limit = 8): array
    {
        $sql = 'SELECT i.*, s.site_name FROM inquiries i LEFT JOIN inquiry_sites s ON s.id = i.site_id ORDER BY i.created_at DESC LIMIT :limit';
        $stmt = Database::connection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
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

        $sql = 'SELECT i.*, s.site_name
                FROM inquiries i
                LEFT JOIN inquiry_sites s ON s.id = i.site_id '
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

    public function create(array $data): int
    {
        $sql = 'INSERT INTO inquiries (
                    site_id, form_key, name, email, title, content, country, phone, address, from_company,
                    source_url, referer_url, ip, user_agent, browser, device_type, language,
                    status, is_read, is_spam, admin_note, extra_data, raw_payload, submitted_at
                ) VALUES (
                    :site_id, :form_key, :name, :email, :title, :content, :country, :phone, :address, :from_company,
                    :source_url, :referer_url, :ip, :user_agent, :browser, :device_type, :language,
                    :status, :is_read, :is_spam, :admin_note, :extra_data, :raw_payload, :submitted_at
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
            $clauses[] = '(i.title LIKE :keyword OR i.content LIKE :keyword OR i.name LIKE :keyword OR i.email LIKE :keyword OR i.from_company LIKE :keyword)';
            $bindings['keyword'] = '%' . $filters['keyword'] . '%';
        }

        $whereSql = '';
        if (!empty($clauses)) {
            $whereSql = ' WHERE ' . implode(' AND ', $clauses);
        }

        return [$whereSql, $bindings];
    }
}
