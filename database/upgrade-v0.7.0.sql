ALTER TABLE admins
    ADD COLUMN role ENUM('admin','manager','agent','viewer') NOT NULL DEFAULT 'admin' AFTER password_hash,
    ADD COLUMN status ENUM('active','disabled') NOT NULL DEFAULT 'active' AFTER role,
    ADD COLUMN last_login_at DATETIME DEFAULT NULL AFTER status;

ALTER TABLE inquiry_followups
    ADD COLUMN completed_at DATETIME DEFAULT NULL AFTER is_completed,
    ADD COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

CREATE TABLE IF NOT EXISTS api_request_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_key VARCHAR(80) DEFAULT NULL,
    site_id INT UNSIGNED DEFAULT NULL,
    endpoint VARCHAR(255) NOT NULL,
    request_method VARCHAR(10) NOT NULL DEFAULT 'POST',
    request_ip VARCHAR(45) DEFAULT NULL,
    origin_host VARCHAR(255) DEFAULT NULL,
    referer_host VARCHAR(255) DEFAULT NULL,
    response_status INT NOT NULL,
    result_code VARCHAR(120) DEFAULT NULL,
    result_message VARCHAR(500) DEFAULT NULL,
    request_headers_json JSON DEFAULT NULL,
    payload_json JSON DEFAULT NULL,
    response_json JSON DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_api_site_id (site_id),
    INDEX idx_api_site_key (site_key),
    INDEX idx_api_response_status (response_status),
    INDEX idx_api_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
