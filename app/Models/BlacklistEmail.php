<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDOException;

final class BlacklistEmail
{
    public function all(): array
    {
        return Database::connection()->query('SELECT * FROM blacklist_emails ORDER BY id DESC')->fetchAll();
    }

    public function create(string $ruleType, string $ruleValue, ?string $reason = null): bool
    {
        $sql = 'INSERT INTO blacklist_emails (rule_type, rule_value, reason) VALUES (:rule_type, :rule_value, :reason)';
        $stmt = Database::connection()->prepare($sql);

        try {
            return $stmt->execute([
                'rule_type' => $ruleType,
                'rule_value' => strtolower(trim($ruleValue)),
                'reason' => $reason,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        $stmt = Database::connection()->prepare('DELETE FROM blacklist_emails WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function exists(string $email): bool
    {
        $email = strtolower(trim($email));
        if ($email === '') {
            return false;
        }

        $domain = strtolower((string) substr(strrchr($email, '@') ?: '', 1));

        $sql = 'SELECT id
                FROM blacklist_emails
                WHERE (rule_type = "email" AND rule_value = :email)
                   OR (rule_type = "domain" AND rule_value = :domain)
                LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'email' => $email,
            'domain' => $domain,
        ]);

        return (bool) $stmt->fetchColumn();
    }
}
