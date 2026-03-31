<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Site
{
    public function all(): array
    {
        $sql = 'SELECT * FROM inquiry_sites ORDER BY id DESC';
        return Database::connection()->query($sql)->fetchAll();
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
