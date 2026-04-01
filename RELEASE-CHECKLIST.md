# Release Checklist

## Goal
Use this checklist before tagging and releasing a new stable package.

## 1. Code integrity
- Run `php scripts/check-release.php`
- Confirm all PHP files pass syntax lint
- Confirm route targets resolve to existing controller methods
- Confirm controller views and layouts exist
- Confirm App class imports resolve to real files

## 2. Database integrity
- Verify `database/schema.sql` reflects the latest code
- Verify `database/seed.sql` still creates a working admin account
- Verify the newest `database/upgrade-v*.sql` file exists
- Confirm upgrade notes mention whether the release changes schema

## 3. Regression review
- Review `MANUAL-TEST-CHECKLIST.md`
- Test the critical pages after importing current schema + seed
- Check the inquiry list, inquiry detail, sites, tools, logs, reports, follow-up reminders

## 4. API review
- Review `API-TEST-EXAMPLES.md`
- Test `/api/v1/health`
- Test one valid submission
- Test one invalid token request
- Test one spam-rule-triggered request
- Confirm data and API request logs are written as expected

## 5. Packaging
- Update `VERSION.txt`
- Update `CHANGELOG.md`
- Update `README.md` if release or upgrade steps changed
- Create the release ZIP
- Tag the release in GitHub

## 6. Known issues note
- Record any remaining known issues in the release notes
- Do not claim untested areas are verified
