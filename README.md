# Inquiry Management System

Version: v0.8.1

This release is the **stable baseline** after the earlier hotfixes and the feature work completed through v0.8.0.

## What is new in v0.8.1
- consolidates earlier hotfixes into the main package
- keeps `InquiryFollowup.php` in the package
- keeps `Admin::allBrief()` in the admin model
- keeps the corrected SQL quoting in `app/Models/Admin.php`
- adds a release integrity checker: `php scripts/check-release.php`
- adds release, manual test, API test, and known-issues documents
- adds a no-op upgrade file for the stable baseline release

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

### Upgrade from v0.8.0
- Run `database/upgrade-v0.8.1.sql`
- No schema changes are required in this release

## Release quality files
- `RELEASE-CHECKLIST.md`
- `MANUAL-TEST-CHECKLIST.md`
- `API-TEST-EXAMPLES.md`
- `KNOWN-ISSUES.md`
- `RELEASE-REPORT-v0.8.1.txt`
- `scripts/check-release.php`
- `scripts/check-release.sh`
- `scripts/check-release.bat`

## Default admin
- Username: `admin`
- Password: `Admin@123456`
