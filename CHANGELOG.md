# Changelog

## v0.8.3
- rebuilt Inquiry Management into a tighter admin-style list page with a clearer stats strip, compact filters, lighter export area, and cleaner row actions
- rebuilt Inquiry Detail into a true two-column working page with a stronger summary header, clearer card hierarchy, follow-up timeline styling, and weaker system-data sections
- removed the generic top-page subtitle so the shared layout no longer adds unrelated helper text to every screen
- retained the same business logic and routes while focusing this release on UI structure, readability, and day-to-day workflow comfort
- added `database/upgrade-v0.8.3.sql` as a no-op upgrade marker for the inquiry page UI refactor

## v0.8.2
- refreshed Inquiry Management with a denser Bootstrap-based filter, template, and table layout
- refreshed Inquiry Detail with a clearer summary header, stronger content hierarchy, and a two-column working view
- included Bootstrap v5 base assets in the shared layout while retaining the existing PHP backend structure
- folded the earlier inquiry list and follow-up page fixes into the packaged codebase
- added `database/upgrade-v0.8.2.sql` as a no-op upgrade marker for the UI refresh release

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
