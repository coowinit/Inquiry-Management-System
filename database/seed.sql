INSERT INTO admins (username, nickname, email, website, bio, page_size, password_hash, role, status)
VALUES (
    'admin',
    'Administrator',
    'admin@example.com',
    'https://example.com',
    'Default administrator account for initial setup.',
    20,
    '$2y$12$niTRRVVdQ9bOOcK3m/amu.ihmlNYxVaWsEPgbuZskV7lKUo.NA4N2',
    'admin',
    'active'
);

INSERT INTO inquiry_sites (site_name, site_domain, site_key, api_token, signature_secret, require_signature, status, notes, field_mapping_json, notification_settings_json)
VALUES
('a.com Main Website', 'a.com', 'a_main', 'token_a_main_2026', 'sig_a_main_2026_secret_1234567890', 0, 'active', 'Primary official website', NULL, NULL),
('b.com Sample Website', 'b.com', 'b_sample', 'token_b_sample_2026', 'sig_b_sample_2026_secret_1234567890', 1, 'active', 'Sample request website with signed requests', JSON_OBJECT('name', JSON_ARRAY('fullname'), 'email', JSON_ARRAY('user_email'), 'content', JSON_ARRAY('message'), 'from_company', JSON_ARRAY('company_name')), JSON_OBJECT('mode', 'custom', 'transport', 'log_only', 'subject_prefix', '[B-SAMPLE]', 'recipients', JSON_ARRAY('samples@example.com'), 'notify_statuses', JSON_ARRAY('unread'), 'include_spam', false, 'include_admin_link', true)),
('c.com Distributor Website', 'c.com', 'c_distributor', 'token_c_distributor_2026', 'sig_c_distributor_2026_secret_1234567890', 0, 'active', 'Distributor recruitment website', NULL, JSON_OBJECT('mode', 'disable', 'transport', 'log_only', 'subject_prefix', '', 'recipients', JSON_ARRAY(), 'notify_statuses', JSON_ARRAY('unread'), 'include_spam', false, 'include_admin_link', true));

INSERT INTO system_settings (setting_key, setting_value)
VALUES
(
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
        'disposable_email_domains', JSON_ARRAY('mailinator.com', 'tempmail.com', '10minutemail.com', 'guerrillamail.com'),
        'enable_country_block', false,
        'blocked_countries', JSON_ARRAY(),
        'enable_name_keyword_check', false,
        'blocked_name_keywords', JSON_ARRAY(),
        'enable_company_keyword_check', false,
        'blocked_company_keywords', JSON_ARRAY(),
        'enable_content_length_check', false,
        'content_min_length', 5,
        'content_max_length', 5000
    )
),
(
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
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW();

INSERT INTO blacklist_ips (ip_address, reason)
VALUES
('85.209.11.20', 'Spam source'),
('5.252.30.198', 'Repeated suspicious submissions');

INSERT INTO blacklist_emails (rule_type, rule_value, reason)
VALUES
('email', 'blocked@example.com', 'Known spam sender'),
('domain', 'mailinator.com', 'Disposable mailbox domain');

INSERT INTO inquiries (
    site_id, form_key, name, email, title, content, country, phone, address, from_company,
    source_url, referer_url, ip, user_agent, browser, device_type, language, status, is_read, is_spam,
    admin_note, extra_data, raw_payload, submitted_at
)
VALUES
(
    1,
    'contact_form',
    'Matthew Pickering',
    'mattp@hycom.com.au',
    'Free Samples',
    'We are interested in your WPC products and would like to request free samples.',
    'Australia',
    '+61 400 123 456',
    'Sydney',
    'HYCOM',
    'https://a.com/free-samples/',
    'https://a.com/free-samples/',
    '174.7.22.43',
    'Mozilla/5.0',
    'Apple Safari 26.3 mac',
    'desktop',
    'en-AU',
    'unread',
    0,
    0,
    'Hot lead from sample page',
    JSON_OBJECT('product_interest', 'WPC Decking', 'sample_pack', 'Yes'),
    JSON_OBJECT('name', 'Matthew Pickering', 'email', 'mattp@hycom.com.au', 'sample_pack', 'Yes'),
    NOW()
),
(
    2,
    'sample_form',
    'Leo Liu',
    'liuzeyuleo@gmail.com',
    'Deck and fence sample',
    'Want to get samples for decking and fence project.',
    'Austria',
    '6043636822',
    'Vienna',
    'EVODEKCO',
    'https://b.com/free-samples/',
    'https://b.com/free-samples/',
    '174.7.22.44',
    'Mozilla/5.0',
    'Apple Safari 26.3 mac',
    'mobile',
    'zh-CN',
    'read',
    1,
    0,
    NULL,
    JSON_OBJECT('product_type', 'fence', 'quantity', 'small batch'),
    JSON_OBJECT('subject', 'Deck and fence sample', 'message', 'Want to get samples'),
    NOW()
),
(
    3,
    'quote_form',
    'Harish',
    'hareesh183@gmail.com',
    '300 nos 25x40',
    'Need quotation for 300 nos 25x40 products.',
    'India',
    '+91 99999 88888',
    'Bangalore',
    'COOWINWPC',
    'https://c.com/quote/',
    'https://c.com/quote/',
    '174.7.22.45',
    'Mozilla/5.0',
    'Google Chrome 147.0.0.0 Linux',
    'desktop',
    'en-IN',
    'trash',
    1,
    0,
    'Archived after duplicate follow-up',
    JSON_OBJECT('quantity', '300', 'size', '25x40'),
    JSON_OBJECT('quantity', '300', 'size', '25x40', 'name', 'Harish'),
    NOW()
);

INSERT INTO inquiry_logs (inquiry_id, admin_id, action, action_note)
VALUES
(1, 1, 'seed_created', 'Initial demo inquiry record'),
(2, 1, 'status_changed', 'Marked as read for demo data'),
(NULL, 1, 'site_created', 'Seeded demo sites for local development'),
(NULL, 1, 'email_notifications_updated', 'Seeded default notification settings for local development');


INSERT INTO inquiry_followups (inquiry_id, admin_id, followup_type, content, next_contact_at, is_completed, completed_at) VALUES
(1, 1, 'email', 'Sent sample catalog and promised DHL sample arrangement.', DATE_ADD(NOW(), INTERVAL 2 DAY), 0, NULL),
(2, 1, 'call', 'Called customer and confirmed sample size request.', NULL, 1, NOW());

INSERT INTO api_request_logs (site_key, site_id, endpoint, request_method, request_ip, origin_host, referer_host, response_status, result_code, result_message, request_headers_json, payload_json, response_json) VALUES
('a_main', 1, '/api/v1/inquiries/submit', 'POST', '174.7.22.43', 'a.com', 'a.com', 201, 'unread', 'Inquiry received successfully.', JSON_OBJECT('Content-Type', 'application/json'), JSON_OBJECT('site_key', 'a_main', 'name', 'Matthew Pickering'), JSON_OBJECT('success', true)),
('b_sample', 2, '/api/v1/inquiries/submit', 'POST', '174.7.22.44', 'b.com', 'b.com', 401, 'AUTH_INVALID', 'Invalid site credentials or inactive site.', JSON_OBJECT('Content-Type', 'application/json'), JSON_OBJECT('site_key', 'b_sample'), JSON_OBJECT('success', false));


INSERT INTO export_templates (template_name, template_scope, admin_id, filters_json, columns_json) VALUES
('Unread AU Samples', 'shared', 1, JSON_OBJECT('status', 'unread', 'site_id', '1', 'date_from', '', 'date_to', '', 'keyword', 'sample', 'has_note', '', 'assigned_admin_id', ''), JSON_ARRAY('id', 'site_name', 'status', 'name', 'email', 'country', 'title', 'content', 'created_at'));
