ALTER TABLE inquiry_sites
    ADD COLUMN notification_settings_json JSON DEFAULT NULL AFTER field_mapping_json;

CREATE TABLE IF NOT EXISTS export_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(150) NOT NULL,
    template_scope ENUM('personal','shared') NOT NULL DEFAULT 'personal',
    admin_id INT UNSIGNED DEFAULT NULL,
    filters_json JSON DEFAULT NULL,
    columns_json JSON DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_export_templates_admin_id (admin_id),
    INDEX idx_export_templates_scope (template_scope)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
