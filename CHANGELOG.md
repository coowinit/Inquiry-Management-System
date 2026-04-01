# Changelog

## v0.8.1
- consolidated prior hotfixes into the main stable package
- retained `InquiryFollowup.php` in the packaged release
- retained `Admin::allBrief()` and corrected SQL quoting in `app/Models/Admin.php`
- added `scripts/check-release.php` for syntax, class, route, view, and SQL integrity checks
- added release checklist, manual smoke checklist, API test examples, known issues, and a generated release report
- added `database/upgrade-v0.8.1.sql` as the stable baseline upgrade marker
- no new business features added; release focus is stabilization

## v0.8.0
- added API request log detail page
- added site-level notification override settings
- added export templates
- added follow-up reminders page
- added quick-copy actions in inquiry detail
