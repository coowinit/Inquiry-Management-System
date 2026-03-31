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

    public function findById(int $id): array|false
    {
        $sql = 'SELECT * FROM inquiry_sites WHERE id = :id LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function findByCredentials(string $siteKey, string $apiToken): array|false
    {
        $sql = 'SELECT * FROM inquiry_sites WHERE site_key = :site_key AND api_token = :api_token AND status = :status LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'site_key' => $siteKey,
            'api_token' => $apiToken,
            'status' => 'active',
        ]);

        return $stmt->fetch();
    }

    public function create(array $data): bool
    {
        try {
            $sql = 'INSERT INTO inquiry_sites (
                        site_name, site_domain, site_key, api_token, signature_secret, require_signature, status, notes
                    ) VALUES (
                        :site_name, :site_domain, :site_key, :api_token, :signature_secret, :require_signature, :status, :notes
                    )';
            $stmt = Database::connection()->prepare($sql);

            return $stmt->execute([
                'site_name' => $data['site_name'],
                'site_domain' => $data['site_domain'],
                'site_key' => $data['site_key'],
                'api_token' => $data['api_token'],
                'signature_secret' => $data['signature_secret'],
                'require_signature' => $data['require_signature'],
                'status' => $data['status'],
                'notes' => $data['notes'],
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $sql = 'UPDATE inquiry_sites
                    SET site_name = :site_name,
                        site_domain = :site_domain,
                        site_key = :site_key,
                        require_signature = :require_signature,
                        status = :status,
                        notes = :notes,
                        updated_at = NOW()
                    WHERE id = :id';
            $stmt = Database::connection()->prepare($sql);

            return $stmt->execute([
                'site_name' => $data['site_name'],
                'site_domain' => $data['site_domain'],
                'site_key' => $data['site_key'],
                'require_signature' => $data['require_signature'],
                'status' => $data['status'],
                'notes' => $data['notes'],
                'id' => $id,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function rotateApiToken(int $id, string $apiToken): bool
    {
        $sql = 'UPDATE inquiry_sites SET api_token = :api_token, updated_at = NOW() WHERE id = :id';
        $stmt = Database::connection()->prepare($sql);

        return $stmt->execute([
            'api_token' => $apiToken,
            'id' => $id,
        ]);
    }

    public function rotateSignatureSecret(int $id, string $secret): bool
    {
        $sql = 'UPDATE inquiry_sites SET signature_secret = :signature_secret, updated_at = NOW() WHERE id = :id';
        $stmt = Database::connection()->prepare($sql);

        return $stmt->execute([
            'signature_secret' => $secret,
            'id' => $id,
        ]);
    }

    public function isAllowedHost(array $site, ?string $host): bool
    {
        if ($host === null || $host === '') {
            return true;
        }

        $configuredHost = strtolower(trim((string) ($site['site_domain'] ?? '')));
        $host = strtolower(trim($host));

        if ($configuredHost === '') {
            return true;
        }

        if ($host === $configuredHost) {
            return true;
        }

        return str_ends_with($host, '.' . $configuredHost);
    }
}
