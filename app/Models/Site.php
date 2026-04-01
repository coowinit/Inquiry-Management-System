<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDOException;

final class Site
{
    public function all(): array
    {
        $sql = 'SELECT * FROM inquiry_sites ORDER BY id DESC';
        return Database::connection()->query($sql)->fetchAll();
    }

    public function allWithStats(): array
    {
        $sql = 'SELECT 
                    s.*,
                    COUNT(i.id) AS inquiry_total,
                    SUM(CASE WHEN i.status = "unread" THEN 1 ELSE 0 END) AS unread_total,
                    MAX(i.created_at) AS last_inquiry_at
                FROM inquiry_sites s
                LEFT JOIN inquiries i ON i.site_id = s.id
                GROUP BY s.id
                ORDER BY s.id DESC';
        return Database::connection()->query($sql)->fetchAll();
    }

    public function findByCredentials(string $siteKey, string $apiToken): array|false
    {
        $stmt = Database::connection()->prepare('SELECT * FROM inquiry_sites WHERE site_key = :site_key AND api_token = :api_token AND status = "active" LIMIT 1');
        $stmt->execute([
            'site_key' => $siteKey,
            'api_token' => $apiToken,
        ]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false
    {
        $stmt = Database::connection()->prepare('SELECT * FROM inquiry_sites WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create(array $data): bool
    {
        $sql = 'INSERT INTO inquiry_sites (
                    site_name, site_domain, site_key, api_token, signature_secret, require_signature, status, notes, field_mapping_json
                ) VALUES (
                    :site_name, :site_domain, :site_key, :api_token, :signature_secret, :require_signature, :status, :notes, :field_mapping_json
                )';

        try {
            $stmt = Database::connection()->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException) {
            return false;
        }
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE inquiry_sites SET
                    site_name = :site_name,
                    site_domain = :site_domain,
                    site_key = :site_key,
                    require_signature = :require_signature,
                    status = :status,
                    notes = :notes,
                    field_mapping_json = :field_mapping_json,
                    updated_at = NOW()
                WHERE id = :id';

        try {
            $stmt = Database::connection()->prepare($sql);
            return $stmt->execute([
                'site_name' => $data['site_name'],
                'site_domain' => $data['site_domain'],
                'site_key' => $data['site_key'],
                'require_signature' => $data['require_signature'],
                'status' => $data['status'],
                'notes' => $data['notes'],
                'field_mapping_json' => $data['field_mapping_json'],
                'id' => $id,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function rotateToken(int $id, string $token): bool
    {
        $stmt = Database::connection()->prepare('UPDATE inquiry_sites SET api_token = :token, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([
            'token' => $token,
            'id' => $id,
        ]);
    }

    public function rotateSignatureSecret(int $id, string $secret): bool
    {
        $stmt = Database::connection()->prepare('UPDATE inquiry_sites SET signature_secret = :secret, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([
            'secret' => $secret,
            'id' => $id,
        ]);
    }

    public function isAllowedHost(array $site, ?string $host): bool
    {
        if ($host === null || $host === '') {
            return true;
        }

        $siteDomain = strtolower(trim((string) ($site['site_domain'] ?? '')));
        $host = strtolower(trim($host));

        if ($siteDomain === '') {
            return true;
        }

        return $host === $siteDomain || str_ends_with($host, '.' . $siteDomain);
    }

    public function fieldMapping(array $site): array
    {
        $mapping = json_decode((string) ($site['field_mapping_json'] ?? ''), true);
        return is_array($mapping) ? $mapping : [];
    }
}
