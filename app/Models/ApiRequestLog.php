<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class ApiRequestLog
{
    public function create(array $data): bool
    {
        $sql = 'INSERT INTO api_request_logs (
                    site_key, site_id, endpoint, request_method, request_ip, origin_host, referer_host,
                    response_status, result_code, result_message, request_headers_json, payload_json, response_json
                ) VALUES (
                    :site_key, :site_id, :endpoint, :request_method, :request_ip, :origin_host, :referer_host,
                    :response_status, :result_code, :result_message, :request_headers_json, :payload_json, :response_json
                )';
        $stmt = Database::connection()->prepare($sql);
        return $stmt->execute([
            'site_key' => $data['site_key'],
            'site_id' => $data['site_id'],
            'endpoint' => $data['endpoint'],
            'request_method' => $data['request_method'],
            'request_ip' => $data['request_ip'],
            'origin_host' => $data['origin_host'],
            'referer_host' => $data['referer_host'],
            'response_status' => $data['response_status'],
            'result_code' => $data['result_code'],
            'result_message' => $data['result_message'],
            'request_headers_json' => $data['request_headers_json'],
            'payload_json' => $data['payload_json'],
            'response_json' => $data['response_json'],
        ]);
    }

    public function paginate(int $page = 1, int $perPage = 30, array $filters = []): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $pdo = Database::connection();

        [$whereSql, $bindings] = $this->buildWhere($filters);
        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM api_request_logs l ' . $whereSql);
        $countStmt->execute($bindings);
        $total = (int) $countStmt->fetchColumn();

        $sql = 'SELECT l.*, s.site_name
                FROM api_request_logs l
                LEFT JOIN inquiry_sites s ON s.id = l.site_id '
                . $whereSql .
                ' ORDER BY l.created_at DESC, l.id DESC
                LIMIT :limit OFFSET :offset';
        $stmt = $pdo->prepare($sql);
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

    public function findById(int $id): array|false
    {
        $sql = 'SELECT l.*, s.site_name, s.site_domain
                FROM api_request_logs l
                LEFT JOIN inquiry_sites s ON s.id = l.site_id
                WHERE l.id = :id
                LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function recent(int $limit = 8): array
    {
        $sql = 'SELECT l.*, s.site_name
                FROM api_request_logs l
                LEFT JOIN inquiry_sites s ON s.id = l.site_id
                ORDER BY l.created_at DESC, l.id DESC
                LIMIT :limit';
        $stmt = Database::connection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function statusCounts(int $days = 30): array
    {
        $start = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        $stmt = Database::connection()->prepare('SELECT response_status, COUNT(*) AS total_count
            FROM api_request_logs
            WHERE DATE(created_at) >= :start
            GROUP BY response_status
            ORDER BY response_status ASC');
        $stmt->execute(['start' => $start]);
        return $stmt->fetchAll();
    }

    private function buildWhere(array $filters): array
    {
        $clauses = [];
        $bindings = [];

        $siteId = (int) ($filters['site_id'] ?? 0);
        if ($siteId > 0) {
            $clauses[] = 'l.site_id = :site_id';
            $bindings['site_id'] = $siteId;
        }

        $statusClass = trim((string) ($filters['status_class'] ?? ''));
        if ($statusClass === 'success') {
            $clauses[] = 'l.response_status < 400';
        } elseif ($statusClass === 'error') {
            $clauses[] = 'l.response_status >= 400';
        }

        $keyword = trim((string) ($filters['keyword'] ?? ''));
        if ($keyword !== '') {
            $clauses[] = '(l.site_key LIKE :keyword OR l.result_code LIKE :keyword OR l.result_message LIKE :keyword OR l.request_ip LIKE :keyword OR l.endpoint LIKE :keyword)';
            $bindings['keyword'] = '%' . $keyword . '%';
        }

        $whereSql = $clauses === [] ? '' : ' WHERE ' . implode(' AND ', $clauses);
        return [$whereSql, $bindings];
    }
}
