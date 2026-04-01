<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use PDOException;

final class ExportTemplate
{
    public function allVisibleTo(?int $adminId = null): array
    {
        $sql = "SELECT t.*, a.username AS admin_username, a.nickname AS admin_nickname
                FROM export_templates t
                LEFT JOIN admins a ON a.id = t.admin_id
                WHERE t.template_scope = 'shared'";

        if ($adminId !== null && $adminId > 0) {
            $sql .= ' OR t.admin_id = :admin_id';
        }

        $sql .= ' ORDER BY t.template_scope DESC, t.template_name ASC, t.id DESC';
        $stmt = Database::connection()->prepare($sql);
        if ($adminId !== null && $adminId > 0) {
            $stmt->bindValue(':admin_id', $adminId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findVisibleById(int $id, ?int $adminId = null): array|false
    {
        $sql = "SELECT * FROM export_templates WHERE id = :id AND (template_scope = 'shared'";
        if ($adminId !== null && $adminId > 0) {
            $sql .= ' OR admin_id = :admin_id';
        }
        $sql .= ') LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        if ($adminId !== null && $adminId > 0) {
            $stmt->bindValue(':admin_id', $adminId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch();
    }

    public function create(array $data): bool
    {
        $sql = 'INSERT INTO export_templates (template_name, template_scope, admin_id, filters_json, columns_json)
                VALUES (:template_name, :template_scope, :admin_id, :filters_json, :columns_json)';
        try {
            $stmt = Database::connection()->prepare($sql);
            return $stmt->execute([
                'template_name' => $data['template_name'],
                'template_scope' => $data['template_scope'],
                'admin_id' => $data['admin_id'],
                'filters_json' => $data['filters_json'],
                'columns_json' => $data['columns_json'],
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function delete(int $id, ?int $adminId = null): bool
    {
        $sql = "DELETE FROM export_templates WHERE id = :id AND (template_scope = 'shared'";
        if ($adminId !== null && $adminId > 0) {
            $sql .= ' OR admin_id = :admin_id';
        }
        $sql .= ')';
        $stmt = Database::connection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        if ($adminId !== null && $adminId > 0) {
            $stmt->bindValue(':admin_id', $adminId, PDO::PARAM_INT);
        }
        return $stmt->execute();
    }

    public function decodeFilters(array $template): array
    {
        $decoded = json_decode((string) ($template['filters_json'] ?? ''), true);
        return is_array($decoded) ? $decoded : [];
    }

    public function decodeColumns(array $template): array
    {
        $decoded = json_decode((string) ($template['columns_json'] ?? ''), true);
        return is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
    }
}
