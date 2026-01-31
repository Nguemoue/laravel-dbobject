---
title: Database Views
description: Simplify complex queries with views.
---

Views allow you to encapsulate complex joins and logic into a virtual table.

## Common Use Case

Creating a view for active users with their latest subscription.

**`active_subscribers.up.sql`**
```sql
CREATE VIEW active_subscribers AS
SELECT 
    u.id, 
    u.email, 
    s.plan_name
FROM users u
JOIN subscriptions s ON s.user_id = u.id
WHERE s.ends_at > NOW();
```

## SQLite Showcase

Views are the primary "stored object" used in SQLite.

**`user_summary.up.sql`**
```sql
CREATE VIEW user_summary AS
SELECT email, count(*) as login_count FROM users GROUP BY email;
```

## Recreate vs Replace

For views, we recommend using the `recreate` strategy in your `.sql.json` if your database doesn't support `CREATE OR REPLACE VIEW` with your specific query changes.

```json
{
  "on_exists": "recreate"
}
```
This will run the `DROP VIEW` (if exists) before running your `CREATE VIEW`.
