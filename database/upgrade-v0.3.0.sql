ALTER TABLE inquiry_sites
    ADD COLUMN signature_secret VARCHAR(150) DEFAULT NULL AFTER api_token,
    ADD COLUMN require_signature TINYINT(1) NOT NULL DEFAULT 0 AFTER signature_secret;
