<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\BlacklistIp;

final class ToolsController extends Controller
{
    public function blacklistIps(): void
    {
        $model = new BlacklistIp();

        $this->view('dashboard/blacklist-ips', [
            'pageTitle' => 'Blocked IPs',
            'items' => $model->all(),
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function addBlacklistIp(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request token.');
            redirect('tools/blacklist-ips');
        }

        $ip = trim((string) ($_POST['ip_address'] ?? ''));
        $reason = trim((string) ($_POST['reason'] ?? ''));

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            flash('error', 'Please enter a valid IP address.');
            redirect('tools/blacklist-ips');
        }

        $created = (new BlacklistIp())->create($ip, $reason !== '' ? $reason : null);

        if ($created) {
            flash('success', 'Blocked IP added successfully.');
        } else {
            flash('error', 'Unable to add the blocked IP. It may already exist.');
        }

        redirect('tools/blacklist-ips');
    }
}
