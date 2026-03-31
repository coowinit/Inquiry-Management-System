<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\BlacklistIp;


final class ToolsController extends Controller
{
    public function blacklistIps(): void
    {
        $model = new BlacklistIp();

        $this->view('dashboard/blacklist-ips', [
            'pageTitle' => 'Blocked IPs',
            'items' => $model->all(),
        ]);
    }
}
