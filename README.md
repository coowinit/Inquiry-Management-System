# Inquiry Management System

Version: v0.8.2

This release is the **UI polish baseline** after the stable packaging work in v0.8.1, focused on making the two core working screens easier to scan and use daily.

## What is new in v0.8.2
- refreshes `Inquiry Management` with a denser Bootstrap-based layout
- refreshes `Inquiry Detail` with a stronger summary header and clearer two-column hierarchy
- keeps the v0.8.1 release integrity checker and stability documents
- folds the inquiry list status-form fix and the follow-up reminder SQL fix into the packaged release
- adds a no-op upgrade file for the UI refresh release

## Quick start
1. Import `database/schema.sql`
2. Import `database/seed.sql`
3. Update `config/database.php`
4. Point the web root to `public/`
5. Run `php scripts/check-release.php` before packaging or deploying

## Upgrade
### Fresh install
- `database/schema.sql`
- `database/seed.sql`

### Upgrade from v0.8.1
- Run `database/upgrade-v0.8.2.sql`
- No schema changes are required in this release

## Release quality files
- `RELEASE-CHECKLIST.md`
- `MANUAL-TEST-CHECKLIST.md`
- `API-TEST-EXAMPLES.md`
- `KNOWN-ISSUES.md`
- `RELEASE-REPORT-v0.8.2.txt`
- `scripts/check-release.php`
- `scripts/check-release.sh`
- `scripts/check-release.bat`

## Default admin
- Username: `admin`
- Password: `Admin@123456`
