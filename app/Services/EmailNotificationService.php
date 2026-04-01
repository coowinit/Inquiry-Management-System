<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\InquiryLog;
use App\Models\SystemSetting;

final class EmailNotificationService
{
    public const SETTING_KEY = 'email_notifications';

    public static function defaults(): array
    {
        return [
            'enabled' => false,
            'transport' => 'log_only',
            'from_email' => 'no-reply@example.com',
            'from_name' => 'Inquiry Management System',
            'subject_prefix' => '[IMS]',
            'recipients' => ['sales@example.com'],
            'notify_statuses' => ['unread'],
            'include_spam' => false,
            'include_admin_link' => true,
        ];
    }

    public function getSettings(): array
    {
        $saved = (new SystemSetting())->getJson(self::SETTING_KEY, []);
        $settings = array_replace_recursive(self::defaults(), $saved);

        $settings['enabled'] = !empty($settings['enabled']);
        $settings['include_spam'] = !empty($settings['include_spam']);
        $settings['include_admin_link'] = !empty($settings['include_admin_link']);
        $settings['transport'] = in_array(($settings['transport'] ?? 'log_only'), ['log_only', 'mail'], true)
            ? $settings['transport']
            : 'log_only';
        $settings['from_email'] = trim((string) ($settings['from_email'] ?? 'no-reply@example.com')) ?: 'no-reply@example.com';
        $settings['from_name'] = trim((string) ($settings['from_name'] ?? 'Inquiry Management System')) ?: 'Inquiry Management System';
        $settings['subject_prefix'] = trim((string) ($settings['subject_prefix'] ?? '[IMS]')) ?: '[IMS]';
        $settings['recipients'] = $this->normalizeEmailList($settings['recipients'] ?? []);
        $settings['notify_statuses'] = $this->normalizeStatusList($settings['notify_statuses'] ?? ['unread']);

        return $settings;
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
        $settings = $this->getSettings();

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

        if ($settings['transport'] === 'log_only') {
            $log->create((int) ($inquiry['id'] ?? 0), null, 'notification_logged', 'Notification prepared for: ' . implode(', ', $recipients));
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
            ($sent ? 'Notification sent to: ' : 'Notification failed for: ') . implode(', ', $recipients)
        );

        return $sent;
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

        if (!empty($settings['include_admin_link'])) {
            $lines[] = '';
            $lines[] = 'Admin Link: ' . base_url('inquiry?id=' . (int) ($inquiry['id'] ?? 0));
        }

        return implode("\n", $lines);
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
