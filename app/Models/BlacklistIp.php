<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;


final class BlacklistIp
{
    public function all(): array
    {
        $sql = 'SELECT * FROM blacklist_ips ORDER BY id DESC';
        return Database::connection()->query($sql)->fetchAll();
    }
}
