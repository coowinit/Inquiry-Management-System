<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SystemSetting;

final class SpamRuleService
{
    public const SETTING_KEY = 'spam_rules';

    public static function defaults(): array
    {
        return [
            'enable_honeypot' => true,
            'honeypot_field' => (string) config('app.api.honeypot_field', 'website'),
            'enable_link_check' => true,
            'spam_link_threshold' => (int) config('app.api.spam_link_threshold', 2),
            'enable_duplicate_check' => true,
            'duplicate_window_minutes' => (int) config('app.api.duplicate_window_minutes', 10),
            'enable_ip_rate_limit' => true,
            'ip_rate_limit_window_minutes' => (int) config('app.api.ip_rate_limit_window_minutes', 10),
            'ip_rate_limit_max' => (int) config('app.api.ip_rate_limit_max', 8),
            'enable_email_rate_limit' => true,
            'email_rate_limit_window_minutes' => (int) config('app.api.email_rate_limit_window_minutes', 10),
            'email_rate_limit_max' => (int) config('app.api.email_rate_limit_max', 5),
            'enable_keyword_check' => true,
            'spam_keywords' => [
                'seo service',
                'buy backlinks',
                'casino',
                'viagra',
                'crypto recovery',
            ],
            'enable_disposable_email_domains' => true,
            'disposable_email_domains' => [
                'mailinator.com',
                'tempmail.com',
                '10minutemail.com',
                'guerrillamail.com',
            ],
        ];
    }

    public function getRules(): array
    {
        $saved = (new SystemSetting())->getJson(self::SETTING_KEY, []);
        $rules = array_replace_recursive(self::defaults(), $saved);

        $rules['spam_keywords'] = $this->normalizeStringList($rules['spam_keywords'] ?? []);
        $rules['disposable_email_domains'] = $this->normalizeStringList($rules['disposable_email_domains'] ?? []);

        foreach ([
            'enable_honeypot',
            'enable_link_check',
            'enable_duplicate_check',
            'enable_ip_rate_limit',
            'enable_email_rate_limit',
            'enable_keyword_check',
            'enable_disposable_email_domains',
        ] as $boolKey) {
            $rules[$boolKey] = !empty($rules[$boolKey]);
        }

        foreach ([
            'spam_link_threshold',
            'duplicate_window_minutes',
            'ip_rate_limit_window_minutes',
            'ip_rate_limit_max',
            'email_rate_limit_window_minutes',
            'email_rate_limit_max',
        ] as $intKey) {
            $rules[$intKey] = max(1, (int) ($rules[$intKey] ?? 1));
        }

        $rules['honeypot_field'] = trim((string) ($rules['honeypot_field'] ?? 'website')) ?: 'website';

        return $rules;
    }

    public function saveFromPost(array $post): bool
    {
        $rules = [
            'enable_honeypot' => isset($post['enable_honeypot']),
            'honeypot_field' => trim((string) ($post['honeypot_field'] ?? 'website')) ?: 'website',
            'enable_link_check' => isset($post['enable_link_check']),
            'spam_link_threshold' => max(1, (int) ($post['spam_link_threshold'] ?? 2)),
            'enable_duplicate_check' => isset($post['enable_duplicate_check']),
            'duplicate_window_minutes' => max(1, (int) ($post['duplicate_window_minutes'] ?? 10)),
            'enable_ip_rate_limit' => isset($post['enable_ip_rate_limit']),
            'ip_rate_limit_window_minutes' => max(1, (int) ($post['ip_rate_limit_window_minutes'] ?? 10)),
            'ip_rate_limit_max' => max(1, (int) ($post['ip_rate_limit_max'] ?? 8)),
            'enable_email_rate_limit' => isset($post['enable_email_rate_limit']),
            'email_rate_limit_window_minutes' => max(1, (int) ($post['email_rate_limit_window_minutes'] ?? 10)),
            'email_rate_limit_max' => max(1, (int) ($post['email_rate_limit_max'] ?? 5)),
            'enable_keyword_check' => isset($post['enable_keyword_check']),
            'spam_keywords' => $this->normalizeStringList($post['spam_keywords'] ?? ''),
            'enable_disposable_email_domains' => isset($post['enable_disposable_email_domains']),
            'disposable_email_domains' => $this->normalizeStringList($post['disposable_email_domains'] ?? ''),
        ];

        return (new SystemSetting())->set(self::SETTING_KEY, json_encode($rules, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function normalizeStringList(array|string $value): array
    {
        $items = is_array($value) ? $value : preg_split('/\r\n|\r|\n/', (string) $value);
        $items = array_map(static fn ($item) => strtolower(trim((string) $item)), $items ?: []);
        $items = array_filter($items, static fn ($item) => $item !== '');
        return array_values(array_unique($items));
    }
}
