INSERT INTO admins (username, nickname, email, website, bio, page_size, password_hash)
VALUES (
    'admin',
    'Administrator',
    'admin@example.com',
    'https://example.com',
    'Default administrator account for initial setup.',
    20,
    '$2y$12$niTRRVVdQ9bOOcK3m/amu.ihmlNYxVaWsEPgbuZskV7lKUo.NA4N2'
);

INSERT INTO inquiry_sites (site_name, site_domain, site_key, api_token, status, notes)
VALUES
('a.com Main Website', 'a.com', 'a_main', 'token_a_main_2026', 'active', 'Primary official website'),
('b.com Sample Website', 'b.com', 'b_sample', 'token_b_sample_2026', 'active', 'Sample request website'),
('c.com Distributor Website', 'c.com', 'c_distributor', 'token_c_distributor_2026', 'active', 'Distributor recruitment website');

INSERT INTO blacklist_ips (ip_address, reason)
VALUES
('85.209.11.20', 'Spam source'),
('5.252.30.198', 'Repeated suspicious submissions');

INSERT INTO inquiries (
    site_id, form_key, name, email, title, content, country, phone, address, from_company,
    source_url, referer_url, ip, user_agent, browser, device_type, language, status, is_read, is_spam,
    extra_data, raw_payload, submitted_at
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
    JSON_OBJECT('quantity', '300', 'size', '25x40'),
    JSON_OBJECT('quantity', '300', 'size', '25x40', 'name', 'Harish'),
    NOW()
);
