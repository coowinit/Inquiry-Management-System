<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class SystemSetting
{
    public function get(string $key, ?string $default = null): ?string
    {
        $stmt = Database::connection()->prepare('SELECT setting_value FROM system_settings WHERE setting_key = :key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $value = $stmt->fetchColumn();

        if ($value === false || $value === null) {
            return $default;
        }

        return (string) $value;
    }

    public function getJson(string $key, array $default = []): array
    {
        $value = $this->get($key);
        if ($value === null || $value === '') {
            return $default;
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : $default;
    }

    public function set(string $key, string $value): bool
    {
        $sql = 'INSERT INTO system_settings (setting_key, setting_value)
                VALUES (:key, :value)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()';
        $stmt = Database::connection()->prepare($sql);
        return $stmt->execute([
            'key' => $key,
            'value' => $value,
        ]);
    }
}
