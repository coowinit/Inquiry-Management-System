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
}
