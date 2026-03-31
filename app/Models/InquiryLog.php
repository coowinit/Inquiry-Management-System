<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class InquiryLog
{
    public function create(?int $inquiryId, ?int $adminId, string $action, ?string $note = null): bool
    {
        $sql = 'INSERT INTO inquiry_logs (inquiry_id, admin_id, action, action_note) VALUES (:inquiry_id, :admin_id, :action, :action_note)';
        $stmt = Database::connection()->prepare($sql);

        return $stmt->execute([
            'inquiry_id' => $inquiryId,
            'admin_id' => $adminId,
            'action' => $action,
            'action_note' => $note,
        ]);
    }
}
