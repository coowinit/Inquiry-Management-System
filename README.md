# Inquiry Management System

Version: v0.8.0

This release adds API log detail pages, site-level notification overrides, export templates, follow-up reminders, and quick copy tools.

## Upgrade from v0.6.x
Run `database/upgrade-v0.7.0.sql` and then replace project files.

## v0.8.0 highlights
- API request logs now support a detail view with copied JSON blocks.
- Each site can inherit, disable or override email notification delivery.
- Inquiry export templates can be saved and reused.
- Follow-up reminders now have a dedicated queue page.
- Inquiry detail includes quick-copy buttons for contact summary, reply draft and JSON snapshot.

## Upgrade from v0.7.x
Run:

```sql
source database/upgrade-v0.8.0.sql;
```
