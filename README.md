# Inquiry Management System v0.5.0

A lightweight **pure PHP + MySQL** inquiry hub for collecting form submissions from multiple websites and managing them in one backend.

## What is included in v0.5.0

- Multi-site inquiry receive API
- Site management with token and signature secret rotation
- Field mapping JSON per site
- Inquiry list, detail page, note management and status flow
- CSV export with selectable export fields
- Spam rule center
- Blocked IP management
- Blocked email / domain management
- Email notification center
- Dashboard with 7-day trend, top forms and country summary
- System logs
- GitHub Actions ZIP build workflow

## Default admin account

- Username: `admin`
- Password: `Admin@123456`

## New in this version

### 1. Email notifications

You can now configure notification delivery from:

- `Tools > Email Notifications`

Supported modes:

- `log_only`: safe testing mode, writes notification attempts to system logs
- `mail`: uses native PHP `mail()`

### 2. Email and domain blacklist

You can now block:

- a specific sender email
- an entire email domain

Manage them from:

- `Tools > Blocked Emails`

### 3. Better export control

The inquiry list page now lets you choose which CSV columns should be exported.

### 4. Dashboard enhancements

The dashboard now shows:

- 7-day inquiry trend
- top forms
- top countries
- current notification configuration summary

## Installation

### Fresh install

1. Create a MySQL database
2. Import:
   - `database/schema.sql`
   - `database/seed.sql`
3. Update `config/database.php`
4. Point your web root to `public/`

### Upgrade from v0.4.0

Run:

- `database/upgrade-v0.5.0.sql`

## API endpoint

- `POST /api/v1/inquiries/submit`
- `GET /api/v1/health`

## Notes about outbound mail

When using `transport = mail`, the hosting environment must already support outbound email for PHP `mail()`.
If your server does not support it yet, use `log_only` first to verify the notification workflow safely.
