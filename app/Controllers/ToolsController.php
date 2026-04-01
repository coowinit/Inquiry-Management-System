<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\BlacklistIp;
use App\Models\InquiryLog;
use App\Services\SpamRuleService;

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
            (new InquiryLog())->create(null, Auth::id(), 'blacklist_ip_added', 'Blocked IP ' . $ip);
            flash('success', 'Blocked IP added successfully.');
        } else {
            flash('error', 'Unable to add the blocked IP. It may already exist.');
        }

        redirect('tools/blacklist-ips');
    }

    public function deleteBlacklistIp(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request token.');
            redirect('tools/blacklist-ips');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('error', 'Invalid blocked IP id.');
            redirect('tools/blacklist-ips');
        }

        $deleted = (new BlacklistIp())->delete($id);

        if ($deleted) {
            (new InquiryLog())->create(null, Auth::id(), 'blacklist_ip_deleted', 'Deleted blocked IP #' . $id);
            flash('success', 'Blocked IP removed successfully.');
        } else {
            flash('error', 'Unable to remove the blocked IP.');
        }

        redirect('tools/blacklist-ips');
    }

    public function spamRules(): void
    {
        $this->view('dashboard/spam-rules', [
            'pageTitle' => 'Spam Rule Center',
            'rules' => (new SpamRuleService())->getRules(),
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function updateSpamRules(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request token.');
            redirect('tools/spam-rules');
        }

        $saved = (new SpamRuleService())->saveFromPost($_POST);

        if ($saved) {
            (new InquiryLog())->create(null, Auth::id(), 'spam_rules_updated', 'Updated spam rule center settings');
            flash('success', 'Spam rules updated successfully.');
        } else {
            flash('error', 'Unable to save spam rules.');
        }

        redirect('tools/spam-rules');
    }
}
