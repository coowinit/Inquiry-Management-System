<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\BlacklistEmail;
use App\Models\BlacklistIp;
use App\Models\InquiryLog;
use App\Services\EmailNotificationService;
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

    public function blacklistEmails(): void
    {
        $this->view('dashboard/blacklist-emails', [
            'pageTitle' => 'Blocked Emails & Domains',
            'items' => (new BlacklistEmail())->all(),
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function addBlacklistEmail(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request token.');
            redirect('tools/blacklist-emails');
        }

        $ruleType = trim((string) ($_POST['rule_type'] ?? 'email'));
        $ruleValue = strtolower(trim((string) ($_POST['rule_value'] ?? '')));
        $reason = trim((string) ($_POST['reason'] ?? ''));

        if (!in_array($ruleType, ['email', 'domain'], true)) {
            flash('error', 'Please choose a valid rule type.');
            redirect('tools/blacklist-emails');
        }

        $isValid = $ruleType === 'email'
            ? (bool) filter_var($ruleValue, FILTER_VALIDATE_EMAIL)
            : (bool) preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/i', $ruleValue);

        if (!$isValid) {
            flash('error', $ruleType === 'email' ? 'Please enter a valid email address.' : 'Please enter a valid email domain.');
            redirect('tools/blacklist-emails');
        }

        $created = (new BlacklistEmail())->create($ruleType, $ruleValue, $reason !== '' ? $reason : null);

        if ($created) {
            (new InquiryLog())->create(null, Auth::id(), 'blacklist_email_added', 'Blocked ' . $ruleType . ' ' . $ruleValue);
            flash('success', 'Blocked rule added successfully.');
        } else {
            flash('error', 'Unable to add the blocked rule. It may already exist.');
        }

        redirect('tools/blacklist-emails');
    }

    public function deleteBlacklistEmail(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request token.');
            redirect('tools/blacklist-emails');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('error', 'Invalid blocked rule id.');
            redirect('tools/blacklist-emails');
        }

        $deleted = (new BlacklistEmail())->delete($id);

        if ($deleted) {
            (new InquiryLog())->create(null, Auth::id(), 'blacklist_email_deleted', 'Deleted blocked email/domain #' . $id);
            flash('success', 'Blocked rule removed successfully.');
        } else {
            flash('error', 'Unable to remove the blocked rule.');
        }

        redirect('tools/blacklist-emails');
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

    public function emailNotifications(): void
    {
        $this->view('dashboard/email-notifications', [
            'pageTitle' => 'Email Notifications',
            'settings' => (new EmailNotificationService())->getSettings(),
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function updateEmailNotifications(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request token.');
            redirect('tools/email-notifications');
        }

        $saved = (new EmailNotificationService())->saveFromPost($_POST);

        if ($saved) {
            (new InquiryLog())->create(null, Auth::id(), 'email_notifications_updated', 'Updated email notification settings');
            flash('success', 'Email notification settings saved successfully.');
        } else {
            flash('error', 'Unable to save email notification settings.');
        }

        redirect('tools/email-notifications');
    }

    public function testEmailNotifications(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request token.');
            redirect('tools/email-notifications');
        }

        $sent = (new EmailNotificationService())->sendTest(Auth::user() ?: []);

        if ($sent) {
            (new InquiryLog())->create(null, Auth::id(), 'notification_test_sent', 'Ran email notification test');
            flash('success', 'Test notification executed. Check your inbox or system logs.');
        } else {
            flash('error', 'Test notification did not run. Check whether notifications are enabled and recipients are configured.');
        }

        redirect('tools/email-notifications');
    }
}
