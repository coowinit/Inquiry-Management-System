# Inquiry Management System

Version: v0.1.0

A lightweight inquiry management system built with pure PHP + MySQL.

## Current scope in v0.1.0

- Project scaffold and directory structure
- PDO database connection layer
- Login / logout
- Session + CSRF helper
- Basic dashboard
- Inquiry list page
- Inquiry detail page
- Site list page
- Blacklist IP page
- Personal settings page
- SQL schema and seed files
- Changelog and versioned delivery structure for GitHub Desktop workflow

## Recommended environment

- PHP 8.1+
- MySQL 5.7+ or MariaDB 10.4+
- Apache / Nginx

## Quick start

1. Create a new MySQL database, for example: `inquiry_system`
2. Import the SQL files in order:
   - `database/schema.sql`
   - `database/seed.sql`
3. Update database config in:
   - `config/database.php`
4. Put the project in your web root and point the document root to:
   - `public/`
5. Open the system in your browser.

## Default admin account

- Username: `admin`
- Password: `Admin@123456`

Please change the password immediately after first login.

## Suggested GitHub Desktop workflow

For each delivery version:

1. Replace the local project files with the new full package
2. Review `CHANGELOG.md`
3. Commit with a versioned message, such as:
   - `v0.1.0 initial scaffold`
   - `v0.2.0 add inquiry receiving API`
4. Push to GitHub
5. Optionally create a Git tag

## Planned next step

v0.2.0 will focus on:

- Inquiry receiving API
- Site token validation
- Basic anti-spam checks
- Insert inquiry records from external websites
