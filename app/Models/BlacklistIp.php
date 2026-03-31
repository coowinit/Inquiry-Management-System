<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDOException;

final class BlacklistIp
{
    public function all(): array
    {
        $sql = 'SELECT * FROM blacklist_ips ORDER BY id DESC';
        return Database::connection()->query($sql)->fetchAll();
    }

    public function exists(string $ipAddress): bool
    {
        $sql = 'SELECT id FROM blacklist_ips WHERE ip_address = :ip_address LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['ip_address' => $ipAddress]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(string $ipAddress, ?string $reason = null): bool
    {
        try {
            $sql = 'INSERT INTO blacklist_ips (ip_address, reason) VALUES (:ip_address, :reason)';
            $stmt = Database::connection()->prepare($sql);
            return $stmt->execute([
                'ip_address' => $ipAddress,
                'reason' => $reason,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM blacklist_ips WHERE id = :id';
        $stmt = Database::connection()->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
