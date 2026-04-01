# Manual Test Checklist

## Environment setup
- Import `database/schema.sql`
- Import `database/seed.sql`
- Update `config/database.php`
- Point the web root to `public/`

## Authentication
- `/login`
- Login with the seeded admin account
- `/logout`
- Login again and confirm session persists

## Dashboard
- `/dashboard`
- Confirm summary cards render
- Confirm charts/summary sections load without PHP warnings

## Inquiry management
- `/inquiries`
- Filter by status
- Filter by site
- Filter by date range
- Filter by assignee
- Filter by note flag
- Select rows and run bulk actions: read, unread, spam, trash
- Save an export template
- Export CSV using selected columns

## Inquiry detail
- Open `/inquiry?id=1` or an existing inquiry ID
- Change status
- Save admin note
- Assign an owner
- Add a follow-up record
- Edit the follow-up record
- Toggle follow-up complete and reopen it
- Use all quick-copy buttons

## Site management
- `/sites`
- Create a site
- Edit the site
- Update field mapping JSON
- Update site notification override settings
- Rotate API token
- Rotate signature secret

## Tools
- `/tools/blacklist-ips`
- Add and delete a blocked IP
- `/tools/blacklist-emails`
- Add and delete a blocked email rule
- `/tools/spam-rules`
- Save spam rules
- `/tools/email-notifications`
- Save notification settings
- Run a test notification

## Logs and reports
- `/logs`
- `/api-logs`
- Open one API log detail page
- `/reports/stats`
- `/followup-reminders`

## Admin and profile
- `/admins`
- Create a second admin user
- Change role/status of an existing admin
- `/profile`
- Update profile fields
- Change password
