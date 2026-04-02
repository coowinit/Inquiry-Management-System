# Inquiry Management System

A lightweight **multi-site inquiry / lead management system** built with **pure PHP + MySQL**.
It centralizes form submissions from multiple websites into one admin panel, where your team can review, assign, filter, tag, follow up, export, and audit every inquiry in one place.

> Suitable for companies operating multiple marketing sites, contact forms, quote forms, sample request forms, distributor forms, and other lead-capture pages.

---

## Table of Contents

- [Project Overview](#project-overview)
- [Main Use Cases](#main-use-cases)
- [Core Features](#core-features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [How the System Works](#how-the-system-works)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Deployment Notes](#deployment-notes)
- [Admin Routes](#admin-routes)
- [Public API](#public-api)
- [Standard Inquiry Fields](#standard-inquiry-fields)
- [Site Onboarding Checklist](#site-onboarding-checklist)
- [Multi-site Form Submission Examples](#multi-site-form-submission-examples)
- [Field Mapping Examples](#field-mapping-examples)
- [Email Notification Notes](#email-notification-notes)
- [Security Notes](#security-notes)
- [Testing and Release Checks](#testing-and-release-checks)
- [Upgrade Notes](#upgrade-notes)
- [Troubleshooting](#troubleshooting)
- [Suggested GitHub Repository Details](#suggested-github-repository-details)
- [License / Internal Use](#license--internal-use)

---

## Project Overview

**Inquiry Management System** is designed to solve a common business problem:

- You have **multiple websites**
- Each site has one or more forms
- Form fields are not always identical
- Leads are scattered across emails, site backends, or spreadsheets
- Sales and operations teams need one place to review and follow up

This project provides a central system that receives inquiries from multiple websites through a unified API and stores them in a shared database. The admin backend then provides:

- inquiry list and detail pages
- assignment and follow-up workflow
- spam / blacklist control
- logs and API request tracing
- CSV export and reusable export templates
- site-specific API credentials and notification rules

---

## Main Use Cases

This system is a good fit if you need to collect inquiries from websites such as:

- Main corporate website contact forms
- Product inquiry / RFQ forms
- Free sample request forms
- Distributor application forms
- Dealer application forms
- Landing pages for campaigns
- Regional sites for different countries or brands

Example multi-site setup:

- `a.com` → Main company site
- `b.com` → Sample request site
- `c.com` → Distributor recruitment site

All submissions go into one admin system.

---

## Core Features

### Multi-site collection
- Unified inquiry submission API
- One site = one `site_key`
- Site-specific `api_token`
- Optional HMAC request signature
- Site-level field mapping
- Site-level notification override

### Inquiry workflow
- Inquiry list
- Inquiry detail view
- Status update (`unread`, `read`, `spam`, `trash`)
- Assign owner
- Admin note
- Follow-up history and reminders
- Bulk actions

### Anti-spam and control
- Blacklisted IPs
- Blacklisted emails and domains
- Honeypot support
- Link count threshold
- Duplicate submission window
- IP / email rate limits
- Keyword rules
- Country blocks
- Content length rules

### Export and reporting
- CSV export
- Select export columns
- Save reusable export templates
- Dashboard summary
- Reports and analytics
- Follow-up reminders

### Auditing and maintenance
- System logs
- API request logs
- Release check scripts
- Manual test checklist
- API example scripts

### Admin user management
- Create admin user
- Edit admin profile
- Reset password
- Enable / disable admin user
- Role and status management

---

## Tech Stack

- **Backend:** PHP 8+
- **Database:** MySQL / MariaDB
- **Frontend:** server-rendered PHP views + Bootstrap-based admin UI
- **Architecture:** lightweight MVC-style structure, no heavy framework

---

## Project Structure

```text
.
├── app/
│   ├── Controllers/
│   ├── Core/
│   ├── Helpers/
│   ├── Models/
│   └── Services/
├── bootstrap/
├── config/
├── database/
│   ├── schema.sql
│   ├── seed.sql
│   └── upgrade-v*.sql
├── examples/
│   ├── api-tests/
│   ├── javascript-fetch-example.js
│   ├── php-forwarder.php
│   └── php-signed-forwarder.php
├── public/
│   ├── index.php
│   └── assets/
├── resources/
│   └── views/
├── scripts/
│   ├── check-release.php
│   ├── check-release.sh
│   └── check-release.bat
└── storage/
```

---

## How the System Works

### 1. A website form is submitted
A user fills in a form on one of your external websites.

### 2. The website forwards the form to the central API
Depending on your setup, the site may:
- send the request directly with JavaScript (token-only sites)
- or send it through the site backend (recommended)
- or send it with HMAC signature (recommended for stronger security)

### 3. The API validates the request
The system checks:
- site key
- API token
- optional signature
- required fields
- anti-spam rules

### 4. Inquiry is stored
The system stores:
- standard fields in dedicated columns
- extra fields in `extra_data`
- original request snapshot in `raw_payload`

### 5. Admin reviews and follows up
Your team can then:
- review the inquiry
- assign an owner
- update the status
- add follow-up records
- export filtered lists
- inspect API logs if something fails

---

## Quick Start

### Requirements

- PHP 8.0 or above
- MySQL 5.7+ or MariaDB with JSON support recommended
- Apache or Nginx
- PDO MySQL extension enabled
- `curl` extension recommended for example forwarders

### Quick installation flow

1. Upload project files to your server
2. Point your web root to the `public/` directory
3. Create a database
4. Import:
   - `database/schema.sql`
   - `database/seed.sql`
5. Review `config/database.php`
6. Review `config/app.php`
7. Visit:
   - `/public/login`
   - or your configured base URL

---

## Configuration

### `config/app.php`

Important keys:

- `name` → system name
- `base_url` → currently set to `/public/`
- `timezone`
- `debug`
- `session_name`
- `api` defaults:
  - signature tolerance
  - honeypot field
  - default rate limits
  - duplicate window

### `config/database.php`

This file stores the current database connection.

> Keep the existing connection values already used in your deployed/test environment unless you intentionally need to change them.

---

## Database Setup

### Fresh install
Import in this order:

```sql
source database/schema.sql;
source database/seed.sql;
```

### Existing install / upgrades
Use the corresponding upgrade files in:

```text
database/upgrade-v0.3.0.sql
database/upgrade-v0.4.0.sql
...
database/upgrade-v0.8.5.sql
```

For small UI-only or maintenance releases, the upgrade file may be a no-op placeholder indicating that no schema change is required.

---

## Deployment Notes

### Recommended web root
Point your domain or virtual host to:

```text
/public
```

### Example local development URL

```text
http://localhost/your-project/public/
```

### Base URL note
The current `config/app.php` uses:

```php
'base_url' => '/public/'
```

If you later deploy to a subdomain or different document root, update it accordingly.

---

## Admin Routes

Main admin pages in the current project:

- `/dashboard`
- `/reports/stats`
- `/inquiries`
- `/inquiry?id=1`
- `/followup-reminders`
- `/sites`
- `/sites/edit?id=1`
- `/admins`
- `/admins/edit?id=1`
- `/logs`
- `/api-logs`
- `/api-log?id=1`
- `/tools/blacklist-ips`
- `/tools/blacklist-emails`
- `/tools/spam-rules`
- `/tools/email-notifications`
- `/profile`

---

## Public API

### Health check

**GET** `/api/v1/health`

Example:

```bash
curl -X GET "https://your-central-domain.com/api/v1/health"
```

### Submit inquiry

**POST** `/api/v1/inquiries/submit`

Supports:
- `application/json`
- standard form-style POST submissions handled by your site backend

---

## Standard Inquiry Fields

The system is designed around a mix of **standard fields** and **flexible extra fields**.

### Standard fields
These are the most common fields used across websites:

- `site_key`
- `api_token`
- `form_key`
- `name`
- `email`
- `title`
- `content`
- `country`
- `phone`
- `address`
- `from_company`
- `source_url`
- `referer_url`
- `client_ip`
- `browser`
- `language`
- `submitted_at`
- `extra_data` (JSON object)

### Required fields
At minimum, most sites should send:

- `site_key`
- `api_token`
- `name`
- `email`
- `content`

### Flexible fields
Site-specific fields should go into:

- `extra_data`

Examples:

```json
{
  "product_interest": "WPC Decking",
  "sample_pack": "Yes",
  "project_stage": "Planning",
  "quantity": "200 sqm"
}
```

---

## Site Onboarding Checklist

When connecting a new website, follow this checklist:

1. Create the site in **Sites & API**
2. Record these values:
   - `site_key`
   - `api_token`
   - optional `signature_secret`
3. Decide whether the site requires signed requests
4. Set `form_key` names for each form
5. Configure field mapping if the source form uses different field names
6. Configure site-specific notification override if needed
7. Submit a test inquiry
8. Check:
   - inquiry list
   - inquiry detail
   - API request logs
   - system logs

---

## Multi-site Form Submission Examples

Below are practical submission patterns for different kinds of websites.

### Scenario A — Pure PHP website backend forwards the form (recommended)

This is the safest and most common setup.

**Flow:**
1. User submits form on `a.com`
2. `a.com` validates form locally
3. `a.com` backend forwards payload to central IMS API
4. IMS stores the inquiry

Use file:

```text
examples/php-forwarder.php
```

Example:

```php
<?php

$apiEndpoint = 'https://your-central-domain.com/api/v1/inquiries/submit';

$payload = [
    'site_key'      => 'a_main',
    'api_token'     => 'token_a_main_2026',
    'form_key'      => 'contact_form',
    'name'          => $_POST['name'] ?? '',
    'email'         => $_POST['email'] ?? '',
    'title'         => $_POST['subject'] ?? '',
    'content'       => $_POST['message'] ?? '',
    'country'       => $_POST['country'] ?? '',
    'phone'         => $_POST['phone'] ?? '',
    'from_company'  => $_POST['company'] ?? '',
    'source_url'    => 'https://a.com/contact-us/',
    'client_ip'     => $_SERVER['REMOTE_ADDR'] ?? '',
    'browser'       => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'language'      => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
    'submitted_at'  => date('c'),
    'extra_data'    => [
        'product_interest' => $_POST['product_interest'] ?? '',
        'quantity'         => $_POST['quantity'] ?? '',
    ],
];

$ch = curl_init($apiEndpoint);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error    = curl_error($ch);
curl_close($ch);
```

**Recommended for:**
- PHP sites
- custom sites
- Laravel / CodeIgniter / old PHP sites
- websites where token secrecy matters

---

### Scenario B — Signed backend forwarding (recommended for higher security)

For more sensitive sites, enable `require_signature` and send:

- `X-Timestamp`
- `X-Signature`

Use file:

```text
examples/php-signed-forwarder.php
```

Signature rule:

```text
signature = HMAC_SHA256(timestamp + "\n" + raw_body, signature_secret)
```

Example:

```php
<?php

$endpoint         = 'https://your-central-domain.com/api/v1/inquiries/submit';
$siteKey          = 'b_sample';
$apiToken         = 'token_b_sample_2026';
$signatureSecret  = 'sig_b_sample_2026_secret_1234567890';

$payload = [
    'site_key'  => $siteKey,
    'api_token' => $apiToken,
    'form_key'  => 'sample_form',
    'name'      => 'John Smith',
    'email'     => 'john@example.com',
    'title'     => 'Request for free samples',
    'content'   => 'Please send us your decking sample options.',
    'country'   => 'United States',
    'source_url'=> 'https://b.com/free-samples/',
    'extra_data'=> [
        'product_interest' => 'Decking',
        'project_stage'    => 'Planning',
    ],
];

$rawBody   = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$timestamp = (string) time();
$signature = hash_hmac('sha256', $timestamp . "\n" . $rawBody, $signatureSecret);

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'X-Timestamp: ' . $timestamp,
        'X-Signature: ' . $signature,
    ],
    CURLOPT_POSTFIELDS     => $rawBody,
]);
```

**Recommended for:**
- business-critical lead sites
- signed server-to-server integrations
- cases where direct frontend submission is not acceptable

---

### Scenario C — Static site or direct JavaScript submission (token-only)

This works for simple sites, but is **less secure** because the token is exposed in frontend JavaScript.

Use file:

```text
examples/javascript-fetch-example.js
```

Example:

```javascript
fetch('https://your-central-domain.com/api/v1/inquiries/submit', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    site_key: 'a_main',
    api_token: 'token_a_main_2026',
    form_key: 'contact_form',
    name: 'John Smith',
    email: 'john@example.com',
    content: 'I want more information about your products.',
    extra_data: {
      product_interest: 'Decking'
    }
  })
})
  .then(r => r.json())
  .then(console.log)
  .catch(console.error);
```

**Use only when:**
- security requirements are low
- the site is static
- you accept that the token is visible in source code

---

### Scenario D — HTML form on each website, local handler forwards to IMS

This is often the cleanest pattern for marketing sites.

#### Step 1: website form (`contact.html`)

```html
<form method="post" action="/submit-contact.php">
  <input type="text" name="name" placeholder="Name" required>
  <input type="email" name="email" placeholder="Email" required>
  <input type="text" name="phone" placeholder="Phone">
  <input type="text" name="company" placeholder="Company">
  <textarea name="message" placeholder="Message" required></textarea>
  <button type="submit">Send</button>
</form>
```

#### Step 2: local handler (`submit-contact.php`)

```php
<?php

$apiEndpoint = 'https://your-central-domain.com/api/v1/inquiries/submit';

$payload = [
    'site_key'      => 'a_main',
    'api_token'     => 'token_a_main_2026',
    'form_key'      => 'contact_form',
    'name'          => trim($_POST['name'] ?? ''),
    'email'         => trim($_POST['email'] ?? ''),
    'phone'         => trim($_POST['phone'] ?? ''),
    'from_company'  => trim($_POST['company'] ?? ''),
    'content'       => trim($_POST['message'] ?? ''),
    'source_url'    => 'https://a.com/contact/',
    'submitted_at'  => date('c'),
];

$ch = curl_init($apiEndpoint);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode($payload),
]);

$response = curl_exec($ch);
curl_close($ch);

header('Location: /thank-you.html');
exit;
```

**Recommended for:**
- classic marketing sites
- brochure sites
- sites where you want full control over validation and thank-you flow

---

### Scenario E — Sample request website with custom fields

For a sample request form, send standard fields plus `extra_data`:

```json
{
  "site_key": "b_sample",
  "api_token": "token_b_sample_2026",
  "form_key": "sample_form",
  "name": "Jane Doe",
  "email": "jane@example.com",
  "title": "Free sample request",
  "content": "Please send color samples for our upcoming project.",
  "country": "Australia",
  "phone": "+61 400 000 000",
  "from_company": "Acme Projects",
  "source_url": "https://b.com/free-samples/",
  "extra_data": {
    "product_interest": "Decking",
    "sample_pack": "Yes",
    "preferred_colors": ["Teak", "Smoke Grey"],
    "project_stage": "Planning",
    "quantity": "150 sqm"
  }
}
```

---

### Scenario F — Distributor / dealer application website

A dealer form often has more fields than the core schema. Send them in `extra_data`.

```json
{
  "site_key": "c_distributor",
  "api_token": "token_c_distributor_2026",
  "form_key": "quote_form",
  "name": "Michael Brown",
  "email": "dealer@example.com",
  "title": "Distributor application",
  "content": "We would like to discuss dealership opportunities.",
  "country": "United Kingdom",
  "phone": "+44 20 0000 0000",
  "from_company": "Brown Building Supply",
  "source_url": "https://c.com/distributor/",
  "extra_data": {
    "annual_volume": "5000 sqm",
    "sales_region": "London & South East",
    "warehouse_available": "Yes",
    "team_size": "12",
    "current_products": "Decking, cladding, fencing"
  }
}
```

---

### Scenario G — `curl` test from terminal

Useful for quick API tests.

```bash
curl -X POST "https://your-central-domain.com/api/v1/inquiries/submit" \
  -H "Content-Type: application/json" \
  -d '{
    "site_key": "a_main",
    "api_token": "token_a_main_2026",
    "form_key": "contact_form",
    "name": "John Smith",
    "email": "john@example.com",
    "content": "Please contact us about your products.",
    "extra_data": {"product_interest": "Decking"}
  }'
```

Also see:

```text
examples/api-tests/health-check.sh
examples/api-tests/submit-valid.sh
examples/api-tests/submit-invalid-token.sh
```

---

## Field Mapping Examples

Not every website uses the same field names. That is exactly why **field mapping** exists.

### Example source form fields
A site may submit:

- `fullname`
- `user_email`
- `message`
- `company_name`

But the system standard fields are:

- `name`
- `email`
- `content`
- `from_company`

### Example mapping JSON
Configure this in the site record:

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

### What this means
If the API receives:

```json
{
  "site_key": "b_sample",
  "api_token": "token_b_sample_2026",
  "fullname": "Leo Liu",
  "user_email": "leo@example.com",
  "message": "Need product samples.",
  "company_name": "Example Co."
}
```

The system can still map it into:

- `name = Leo Liu`
- `email = leo@example.com`
- `content = Need product samples.`
- `from_company = Example Co.`

---

## Email Notification Notes

The system supports:

- global notification settings
- site-level notification override
- `log_only` mode for testing
- `mail` mode using PHP `mail()`

Recommendation:
- use `log_only` first in testing
- only switch to `mail` after your server mail setup is confirmed

---

## Security Notes

### Recommended security model
The safest production model is:

- each website submits to its own backend
- the backend forwards to IMS
- signed requests enabled for important sites

### Avoid exposing secrets in frontend code
If possible, do **not** expose:

- `api_token`
- `signature_secret`

in browser JavaScript.

### Enable signed requests for important sites
Use:

- `require_signature = 1`
- `X-Timestamp`
- `X-Signature`

### Use anti-spam features
At minimum, enable:

- honeypot
- IP rate limit
- email rate limit
- duplicate window
- email/domain blacklist

---

## Testing and Release Checks

Useful files already included in the repository:

- `RELEASE-CHECKLIST.md`
- `MANUAL-TEST-CHECKLIST.md`
- `API-TEST-EXAMPLES.md`
- `scripts/check-release.php`
- `scripts/check-release.sh`
- `scripts/check-release.bat`

### Release check

```bash
php scripts/check-release.php
```

This helps detect:

- missing classes
- route / controller issues
- view file problems
- release consistency issues

---

## Upgrade Notes

Current release history includes:

- `v0.3.x` → site management / signatures / logs
- `v0.4.x` → field mapping / spam rule center / admin note
- `v0.5.x` → email notifications / email blacklist / export field control
- `v0.6.x` → follow-ups / assignees / bulk actions / analytics
- `v0.7.x` → admin roles / API request logs / advanced spam rules
- `v0.8.x` → UI rebuild and stabilization
- `v0.8.5` → admin user edit and safe disable flow

For production upgrades, always:

1. back up the database
2. back up current project files
3. apply code update
4. run the matching upgrade SQL
5. test key pages
6. test one valid API submission

---

## Troubleshooting

### 1. Inquiry not showing in list
Check:
- API response body
- `/api-logs`
- `/logs`
- site `api_token`
- site `status`
- spam rules

### 2. Inquiry submitted but marked as spam
Check:
- honeypot field
- link threshold
- blacklisted email / domain
- keyword rules
- duplicate / rate limit windows

### 3. Signed requests fail
Check:
- `require_signature` enabled?
- timestamp tolerance
- exact raw JSON body used in signature
- newline format: `timestamp + "\n" + raw_body`
- secret mismatch

### 4. Notifications not sent
Check:
- global notifications enabled?
- transport set to `mail`?
- server supports PHP `mail()`?
- site notification override set to `disable`?
- logs for `notification_failed`

### 5. Admin user cannot log in
Check:
- `status = active`
- password reset if needed
- self-disable protection / admin count protection rules

---

## Suggested GitHub Repository Details

These values are ready to copy into the GitHub **About** panel.

### Description

```text
Multi-site inquiry management system built with PHP + MySQL. Collect, review, assign, follow up, export, and audit form submissions from multiple websites in one admin panel.
```

### Topics

```text
php, mysql, inquiry-management, lead-management, contact-form, form-api, multi-site, admin-dashboard, crm-lite, bootstrap-5
```

### Website

Use your deployed central system URL, for example:

```text
https://your-domain.com/public/
```

If you want a cleaner public-facing URL later, move the app to a dedicated subdomain such as:

```text
https://ims.yourdomain.com/
```

and update `config/app.php` accordingly.

---

## License / Internal Use

This repository is currently best treated as:

- company internal project
- custom business system
- private operational tool

If you later plan to open source it, add a proper license file and review:

- credentials
- seed data
- test tokens
- private domains
- example email addresses

