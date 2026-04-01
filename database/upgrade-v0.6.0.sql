ALTER TABLE inquiries
    ADD COLUMN assigned_admin_id INT UNSIGNED DEFAULT NULL AFTER admin_note,
    ADD INDEX idx_assigned_admin_id (assigned_admin_id);

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
