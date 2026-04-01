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
            'spam_keywords' => ['seo service', 'buy backlinks', 'casino', 'viagra', 'crypto recovery'],
            'enable_disposable_email_domains' => true,
            'disposable_email_domains' => ['mailinator.com', 'tempmail.com', '10minutemail.com', 'guerrillamail.com'],
            'enable_country_block' => false,
            'blocked_countries' => [],
            'enable_name_keyword_check' => false,
            'blocked_name_keywords' => [],
            'enable_company_keyword_check' => false,
            'blocked_company_keywords' => [],
            'enable_content_length_check' => false,
            'content_min_length' => 5,
            'content_max_length' => 5000,
        ];
    }

    public function getRules(): array
    {
        $saved = (new SystemSetting())->getJson(self::SETTING_KEY, []);
        $rules = array_replace_recursive(self::defaults(), $saved);

        foreach (['spam_keywords', 'disposable_email_domains', 'blocked_countries', 'blocked_name_keywords', 'blocked_company_keywords'] as $listKey) {
            $rules[$listKey] = $this->normalizeStringList($rules[$listKey] ?? []);
        }

        foreach ([
            'enable_honeypot', 'enable_link_check', 'enable_duplicate_check', 'enable_ip_rate_limit', 'enable_email_rate_limit',
            'enable_keyword_check', 'enable_disposable_email_domains', 'enable_country_block', 'enable_name_keyword_check',
            'enable_company_keyword_check', 'enable_content_length_check',
        ] as $boolKey) {
            $rules[$boolKey] = !empty($rules[$boolKey]);
        }

        foreach ([
            'spam_link_threshold', 'duplicate_window_minutes', 'ip_rate_limit_window_minutes', 'ip_rate_limit_max',
            'email_rate_limit_window_minutes', 'email_rate_limit_max', 'content_min_length', 'content_max_length',
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
            'enable_country_block' => isset($post['enable_country_block']),
            'blocked_countries' => $this->normalizeStringList($post['blocked_countries'] ?? ''),
            'enable_name_keyword_check' => isset($post['enable_name_keyword_check']),
            'blocked_name_keywords' => $this->normalizeStringList($post['blocked_name_keywords'] ?? ''),
            'enable_company_keyword_check' => isset($post['enable_company_keyword_check']),
            'blocked_company_keywords' => $this->normalizeStringList($post['blocked_company_keywords'] ?? ''),
            'enable_content_length_check' => isset($post['enable_content_length_check']),
            'content_min_length' => max(1, (int) ($post['content_min_length'] ?? 5)),
            'content_max_length' => max(1, (int) ($post['content_max_length'] ?? 5000)),
        ];

        return (new SystemSetting())->set(self::SETTING_KEY, json_encode($rules, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function normalizeStringList(array|string $value): array
    {
        $items = is_array($value) ? $value : preg_split('/
||
/', (string) $value);
        $items = array_map(static fn ($item) => strtolower(trim((string) $item)), $items ?: []);
        $items = array_filter($items, static fn ($item) => $item !== '');
        return array_values(array_unique($items));
    }
}
