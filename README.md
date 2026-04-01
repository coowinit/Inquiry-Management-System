# Inquiry Management System

Version: **v0.4.0**

A pure PHP + MySQL inquiry management system for collecting inquiry forms from multiple websites into one centralized backend.

## v0.4.0 Highlights

- Added per-site **Field Mapping JSON** so different form field names can map into the system's standard fields
- Added **Admin Note** editing on the inquiry detail page
- Added **Spam Rule Center** in the backend for honeypot, links, duplicates, rate limits, keywords, and disposable email domains
- Added filtering by **Has Note** in inquiry list
- API now stores mapped payload data into `extra_data` when field mapping is applied

## Environment

- PHP 8.1+
- MySQL 5.7+ or MySQL 8+
- Apache or Nginx

## Installation

1. Create a database, for example: `inquiry_system`
2. Import:
   - `database/schema.sql`
   - `database/seed.sql`
3. Update database settings in `config/database.php`
4. Point your web root to `public/`
5. Open the project in your browser

## Upgrading from v0.3.0

If you already have a v0.3.0 database, run:

- `database/upgrade-v0.4.0.sql`

This adds:

- `field_mapping_json` on `inquiry_sites`
- default `spam_rules` in `system_settings`

## Default Admin Account

- Username: `admin`
- Password: `Admin@123456`

## Main Backend Routes

- `/login`
- `/dashboard`
- `/inquiries`
- `/inquiries/export`
- `/sites`
- `/sites/edit?id=1`
- `/logs`
- `/tools/blacklist-ips`
- `/tools/spam-rules`
- `/profile`

## API Routes

### Health Check

`GET /api/v1/health`

### Submit Inquiry

`POST /api/v1/inquiries/submit`

Supported payload types:

- `application/json`
- standard form POST

### Minimum payload

```json
{
  "site_key": "a_main",
  "api_token": "token_a_main_2026",
  "name": "John Smith",
  "email": "john@example.com",
  "content": "I want more information about your products."
}
```

### Optional fields

- `form_key`
- `title`
- `country`
- `phone`
- `address`
- `from_company`
- `source_url`
- `referer_url`
- `language`
- `browser`
- `device_type`
- `submitted_at`
- `client_ip`
- `extra_data` (array)

Unknown fields will also be merged into `extra_data` automatically.

## Field Mapping JSON

You can configure a site-level JSON mapping in the backend to transform external field names into the system's standard fields before validation and storage.

Example:

```json
{
  "name": ["fullname", "your_name"],
  "email": ["user_email", "contact_email"],
  "title": ["subject"],
  "content": ["message", "comments"],
  "from_company": ["company", "company_name"],
  "phone": ["mobile", "tel"]
}
```

## Signed Request Mode

For sites with **Require HMAC signature** enabled:

- Header: `X-Timestamp` = unix timestamp in seconds
- Header: `X-Signature` = `hash_hmac('sha256', X-Timestamp + "\n" + raw_body, signature_secret)`

Recommended usage:

1. Your website backend builds the final request body
2. Your website backend signs the raw body with the site's signature secret
3. Your website backend sends the request to the central inquiry system

## Spam Rule Center

The backend now includes a configurable spam rule center for:

- honeypot field check
- link threshold check
- duplicate submission check
- IP rate limit
- email rate limit
- keyword-based spam detection
- disposable email domain detection

## Example Files

- `examples/php-forwarder.php`
- `examples/php-signed-forwarder.php`
- `examples/javascript-fetch-example.js`

## GitHub Actions

Workflow file:

- `.github/workflows/build-release.yml`

It creates a ZIP package automatically when you push a tag like:

```bash
git tag v0.4.0
git push origin v0.4.0
```

## Notes

Recommended production flow:

1. Website form submits to the current website backend
2. The current website backend maps and forwards the payload to this central system
3. This system validates, filters, stores, and manages the inquiry

This is safer than exposing tokens directly in front-end JavaScript.
