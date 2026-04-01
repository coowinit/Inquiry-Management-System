# Changelog

## v0.6.0
- Added inquiry follow-up records and follow-up history on the detail page
- Added owner assignment for individual inquiries and bulk assignment from the list page
- Added bulk actions for status updates and assignee changes
- Added Reports & Analytics page with site, form, country, status and assignee summaries
- Added manual test trigger for email notifications
- Added assigned owner and follow-up count columns to the inquiry list
- Added database upgrade script for assignee support and follow-up table

## v0.5.0

- added blocked email and blocked domain management
- added email notification center with `log_only` and `mail` modes
- added dashboard 7-day trend, top forms and country summary widgets
- added export field selection for inquiry CSV export
- enhanced API receive flow with blocked email/domain checks
- added `blacklist_emails` table and `email_notifications` system setting
- logged notification delivery attempts into system logs

## v0.4.0

- added site field mapping JSON support
- added admin note editing in inquiry detail page
- added spam rule center page and settings persistence

## v0.3.0

- added site create and edit management
- added HMAC signature verification support
- added CSV export and system logs page

## v0.2.0

- added unified inquiry receive API
- added base spam detection and payload storage

## v0.1.0

- initial scaffold with login, dashboard and basic inquiry pages
