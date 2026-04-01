CREATE TABLE IF NOT EXISTS blacklist_emails (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rule_type ENUM('email','domain') NOT NULL,
    rule_value VARCHAR(190) NOT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_rule (rule_type, rule_value),
    INDEX idx_rule_type (rule_type),
    INDEX idx_rule_value (rule_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO system_settings (setting_key, setting_value)
VALUES (
    'email_notifications',
    JSON_OBJECT(
        'enabled', false,
        'transport', 'log_only',
        'from_email', 'no-reply@example.com',
        'from_name', 'Inquiry Management System',
        'subject_prefix', '[IMS]',
        'recipients', JSON_ARRAY('sales@example.com'),
        'notify_statuses', JSON_ARRAY('unread'),
        'include_spam', false,
        'include_admin_link', true
    )
)
ON DUPLICATE KEY UPDATE setting_value = setting_value, updated_at = NOW();
