CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    nickname VARCHAR(100) DEFAULT NULL,
    email VARCHAR(150) NOT NULL,
    website VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    page_size INT UNSIGNED NOT NULL DEFAULT 20,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inquiry_sites (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_name VARCHAR(150) NOT NULL,
    site_domain VARCHAR(255) NOT NULL,
    site_key VARCHAR(80) NOT NULL UNIQUE,
    api_token VARCHAR(100) NOT NULL,
    signature_secret VARCHAR(150) DEFAULT NULL,
    require_signature TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    notes TEXT DEFAULT NULL,
    field_mapping_json JSON DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_site_domain (site_domain),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inquiries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id INT UNSIGNED DEFAULT NULL,
    form_key VARCHAR(100) DEFAULT NULL,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    title VARCHAR(255) DEFAULT NULL,
    content TEXT NOT NULL,
    country VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(100) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    from_company VARCHAR(150) DEFAULT NULL,
    source_url VARCHAR(500) DEFAULT NULL,
    referer_url VARCHAR(500) DEFAULT NULL,
    ip VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    browser VARCHAR(255) DEFAULT NULL,
    device_type VARCHAR(50) DEFAULT NULL,
    language VARCHAR(50) DEFAULT NULL,
    status ENUM('unread','read','trash','spam') NOT NULL DEFAULT 'unread',
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    is_spam TINYINT(1) NOT NULL DEFAULT 0,
    admin_note TEXT DEFAULT NULL,
    assigned_admin_id INT UNSIGNED DEFAULT NULL,
    extra_data JSON DEFAULT NULL,
    raw_payload JSON DEFAULT NULL,
    submitted_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_inquiries_site_id FOREIGN KEY (site_id) REFERENCES inquiry_sites(id) ON DELETE SET NULL,
    INDEX idx_site_id (site_id),
    INDEX idx_status (status),
    INDEX idx_is_read (is_read),
    INDEX idx_email (email),
    INDEX idx_name (name),
    INDEX idx_ip (ip),
    INDEX idx_created_at (created_at),
    INDEX idx_assigned_admin_id (assigned_admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blacklist_ips (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL UNIQUE,
    reason VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_address (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE IF NOT EXISTS system_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inquiry_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    inquiry_id BIGINT UNSIGNED DEFAULT NULL,
    admin_id INT UNSIGNED DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    action_note TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_inquiry_id (inquiry_id),
    INDEX idx_admin_id (admin_id),
    INDEX idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS inquiry_followups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    inquiry_id BIGINT UNSIGNED NOT NULL,
    admin_id INT UNSIGNED DEFAULT NULL,
    followup_type ENUM('note','email','call','meeting','todo','status') NOT NULL DEFAULT 'note',
    content TEXT NOT NULL,
    next_contact_at DATETIME DEFAULT NULL,
    is_completed TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_followup_inquiry_id (inquiry_id),
    INDEX idx_followup_admin_id (admin_id),
    INDEX idx_followup_next_contact_at (next_contact_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
