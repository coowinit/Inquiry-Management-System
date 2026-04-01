<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\InquiryLog;
use App\Models\Site;
use App\Models\SystemSetting;

final class EmailNotificationService
{
    private const SETTING_KEY = 'email_notifications';

    public function getSettings(): array
    {
        $settings = (new SystemSetting())->getJson(self::SETTING_KEY, [
            'enabled' => false,
            'transport' => 'log_only',
            'from_email' => 'no-reply@example.com',
            'from_name' => 'Inquiry Management System',
            'subject_prefix' => '[IMS]',
            'recipients' => [],
            'notify_statuses' => ['unread'],
            'include_spam' => false,
            'include_admin_link' => true,
        ]);

        return $this->normalizeGlobalSettings($settings);
    }

    public function getEffectiveSettingsForSite(array $site): array
    {
        $global = $this->getSettings();
        $siteSettings = (new Site())->notificationSettings($site);
        $mode = $siteSettings['mode'] ?? 'inherit';

        if ($mode === 'disable') {
            $global['enabled'] = false;
            $global['_site_mode'] = 'disable';
            return $global;
        }

        if ($mode === 'custom') {
            $effective = $global;
            $effective['enabled'] = true;
            $effective['transport'] = in_array(($siteSettings['transport'] ?? 'log_only'), ['log_only', 'mail'], true) ? $siteSettings['transport'] : $global['transport'];
            $effective['subject_prefix'] = trim((string) ($siteSettings['subject_prefix'] ?? '')) ?: $global['subject_prefix'];
            $effective['recipients'] = $this->normalizeEmailList($siteSettings['recipients'] ?? []);
            $effective['notify_statuses'] = $this->normalizeStatusList($siteSettings['notify_statuses'] ?? $global['notify_statuses']);
            $effective['include_spam'] = !empty($siteSettings['include_spam']);
            $effective['include_admin_link'] = array_key_exists('include_admin_link', $siteSettings) ? !empty($siteSettings['include_admin_link']) : $global['include_admin_link'];
            $effective['_site_mode'] = 'custom';
            return $effective;
        }

        $global['_site_mode'] = 'inherit';
        return $global;
    }

    public function saveFromPost(array $post): bool
    {
        $settings = [
            'enabled' => isset($post['enabled']),
            'transport' => in_array(($post['transport'] ?? 'log_only'), ['log_only', 'mail'], true) ? $post['transport'] : 'log_only',
            'from_email' => trim((string) ($post['from_email'] ?? 'no-reply@example.com')) ?: 'no-reply@example.com',
            'from_name' => trim((string) ($post['from_name'] ?? 'Inquiry Management System')) ?: 'Inquiry Management System',
            'subject_prefix' => trim((string) ($post['subject_prefix'] ?? '[IMS]')) ?: '[IMS]',
            'recipients' => $this->normalizeEmailList($post['recipients'] ?? ''),
            'notify_statuses' => $this->normalizeStatusList($post['notify_statuses'] ?? []),
            'include_spam' => isset($post['include_spam']),
            'include_admin_link' => isset($post['include_admin_link']),
        ];

        return (new SystemSetting())->set(self::SETTING_KEY, json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function notify(array $inquiry, array $site): bool
    {
        $settings = $this->getEffectiveSettingsForSite($site);

        if (empty($settings['enabled'])) {
            return false;
        }

        $status = (string) ($inquiry['status'] ?? 'unread');
        if (!in_array($status, $settings['notify_statuses'], true)) {
            return false;
        }

        if ($status === 'spam' && empty($settings['include_spam'])) {
            return false;
        }

        $recipients = $settings['recipients'];
        if (empty($recipients)) {
            return false;
        }

        $subject = trim($settings['subject_prefix'] . ' ' . ucfirst($status) . ' inquiry from ' . ($site['site_name'] ?? 'Unknown site'));
        $body = $this->buildBody($inquiry, $site, $settings);

        $log = new InquiryLog();
        $mode = $settings['_site_mode'] ?? 'inherit';

        if ($settings['transport'] === 'log_only') {
            $log->create((int) ($inquiry['id'] ?? 0), null, 'notification_logged', 'Notification prepared for: ' . implode(', ', $recipients) . ' [site mode: ' . $mode . ']');
            return true;
        }

        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headers[] = 'From: ' . $settings['from_name'] . ' <' . $settings['from_email'] . '>';

        $sent = @mail(implode(',', $recipients), $subject, $body, implode("\r\n", $headers));

        $log->create(
            (int) ($inquiry['id'] ?? 0),
            null,
            $sent ? 'notification_sent' : 'notification_failed',
            ($sent ? 'Notification sent to: ' : 'Notification failed for: ') . implode(', ', $recipients) . ' [site mode: ' . $mode . ']'
        );

        return $sent;
    }

    public function sendTest(array $admin = [], ?array $site = null): bool
    {
        $settings = $site ? $this->getEffectiveSettingsForSite($site) : $this->getSettings();
        if (empty($settings['enabled']) || empty($settings['recipients'])) {
            return false;
        }

        $site = $site ?: ['site_name' => 'Manual Test', 'site_domain' => base_url()];
        $inquiry = [
            'id' => 0,
            'status' => in_array('unread', $settings['notify_statuses'], true) ? 'unread' : ($settings['notify_statuses'][0] ?? 'unread'),
            'form_key' => 'manual_test',
            'name' => $admin['nickname'] ?? $admin['username'] ?? 'Admin User',
            'email' => $admin['email'] ?? 'admin@example.com',
            'phone' => '-',
            'from_company' => 'Internal Test',
            'country' => '-',
            'title' => 'Email notification test',
            'content' => 'This is a manually triggered notification test from the Email Notifications tool page.',
            'source_url' => base_url('tools/email-notifications'),
            'ip' => request_ip(),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        return $this->notify($inquiry, $site);
    }

    private function buildBody(array $inquiry, array $site, array $settings): string
    {
        $lines = [
            'A new inquiry has been received.',
            '',
            'Site: ' . ($site['site_name'] ?? '-'),
            'Domain: ' . ($site['site_domain'] ?? '-'),
            'Status: ' . ($inquiry['status'] ?? '-'),
            'Form Key: ' . ($inquiry['form_key'] ?? '-'),
            'Name: ' . ($inquiry['name'] ?? '-'),
            'Email: ' . ($inquiry['email'] ?? '-'),
            'Phone: ' . (($inquiry['phone'] ?? '') !== '' ? $inquiry['phone'] : '-'),
            'Company: ' . (($inquiry['from_company'] ?? '') !== '' ? $inquiry['from_company'] : '-'),
            'Country: ' . (($inquiry['country'] ?? '') !== '' ? $inquiry['country'] : '-'),
            'Title: ' . (($inquiry['title'] ?? '') !== '' ? $inquiry['title'] : '-'),
            'Content:',
            (string) ($inquiry['content'] ?? ''),
            '',
            'Source URL: ' . (($inquiry['source_url'] ?? '') !== '' ? $inquiry['source_url'] : '-'),
            'IP: ' . (($inquiry['ip'] ?? '') !== '' ? $inquiry['ip'] : '-'),
            'Created At: ' . (($inquiry['created_at'] ?? '') !== '' ? $inquiry['created_at'] : date('Y-m-d H:i:s')),
        ];

        if (!empty($settings['include_admin_link']) && !empty($inquiry['id'])) {
            $lines[] = '';
            $lines[] = 'Admin Link: ' . base_url('inquiry?id=' . (int) ($inquiry['id'] ?? 0));
        }

        return implode("\n", $lines);
    }

    private function normalizeGlobalSettings(array $settings): array
    {
        $settings['enabled'] = !empty($settings['enabled']);
        $settings['transport'] = in_array(($settings['transport'] ?? 'log_only'), ['log_only', 'mail'], true) ? $settings['transport'] : 'log_only';
        $settings['from_email'] = trim((string) ($settings['from_email'] ?? 'no-reply@example.com')) ?: 'no-reply@example.com';
        $settings['from_name'] = trim((string) ($settings['from_name'] ?? 'Inquiry Management System')) ?: 'Inquiry Management System';
        $settings['subject_prefix'] = trim((string) ($settings['subject_prefix'] ?? '[IMS]')) ?: '[IMS]';
        $settings['recipients'] = $this->normalizeEmailList($settings['recipients'] ?? []);
        $settings['notify_statuses'] = $this->normalizeStatusList($settings['notify_statuses'] ?? ['unread']);
        $settings['include_spam'] = !empty($settings['include_spam']);
        $settings['include_admin_link'] = array_key_exists('include_admin_link', $settings) ? !empty($settings['include_admin_link']) : true;
        return $settings;
    }

    private function normalizeEmailList(array|string $value): array
    {
        $items = is_array($value) ? $value : preg_split('/\r\n|\r|\n|,/', (string) $value);
        $items = array_map(static fn ($item) => strtolower(trim((string) $item)), $items ?: []);
        $items = array_filter($items, static fn ($item) => $item !== '' && filter_var($item, FILTER_VALIDATE_EMAIL));
        return array_values(array_unique($items));
    }

    private function normalizeStatusList(array|string $value): array
    {
        $items = is_array($value) ? $value : preg_split('/\r\n|\r|\n|,/', (string) $value);
        $items = array_map(static fn ($item) => strtolower(trim((string) $item)), $items ?: []);
        $items = array_values(array_intersect($items, ['unread', 'read', 'spam', 'trash']));
        return $items !== [] ? array_values(array_unique($items)) : ['unread'];
    }
}
