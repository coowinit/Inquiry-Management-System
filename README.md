# Inquiry Management System

Version: **v0.2.0**

A pure PHP + MySQL inquiry management system for collecting inquiry forms from multiple websites into one centralized backend.

## v0.2.0 Highlights

- Unified receive API is ready: `/api/v1/inquiries/submit`
- `site_key + api_token` validation
- Required field validation for `name`, `email`, `content`
- Stores both `extra_data` and `raw_payload`
- Basic anti-spam checks
- Blocked IP validation
- Inquiry filters and quick status actions in the backend
- GitHub Actions ZIP packaging workflow included

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

## Default Admin Account

- Username: `admin`
- Password: `Admin@123456`

## Main Backend Routes

- `/login`
- `/dashboard`
- `/inquiries`
- `/sites`
- `/tools/blacklist-ips`
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

## Example Files

- `examples/php-forwarder.php`
- `examples/javascript-fetch-example.js`

## GitHub Actions

Workflow file:

- `.github/workflows/build-release.yml`

It creates a ZIP package automatically when you push a tag like:

```bash
git tag v0.2.0
git push origin v0.2.0
```

## Notes

Recommended production flow:

1. Website form submits to the current website backend
2. The current website backend forwards the payload to this central system
3. This system validates, filters, stores, and manages the inquiry

This is safer than exposing tokens directly in front-end JavaScript.
