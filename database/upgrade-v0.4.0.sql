ALTER TABLE inquiry_sites
    ADD COLUMN field_mapping_json JSON DEFAULT NULL AFTER notes;

INSERT INTO system_settings (setting_key, setting_value)
VALUES (
    'spam_rules',
    JSON_OBJECT(
        'enable_honeypot', true,
        'honeypot_field', 'website',
        'enable_link_check', true,
        'spam_link_threshold', 2,
        'enable_duplicate_check', true,
        'duplicate_window_minutes', 10,
        'enable_ip_rate_limit', true,
        'ip_rate_limit_window_minutes', 10,
        'ip_rate_limit_max', 8,
        'enable_email_rate_limit', true,
        'email_rate_limit_window_minutes', 10,
        'email_rate_limit_max', 5,
        'enable_keyword_check', true,
        'spam_keywords', JSON_ARRAY('seo service', 'buy backlinks', 'casino', 'viagra', 'crypto recovery'),
        'enable_disposable_email_domains', true,
        'disposable_email_domains', JSON_ARRAY('mailinator.com', 'tempmail.com', '10minutemail.com', 'guerrillamail.com')
    )
)
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW();
