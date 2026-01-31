---
title: Triggers
description: Automate actions on data changes.
---

Triggers allow you to execute code automatically when a row is inserted, updated, or deleted.

## Audit Log Example (MySQL)

**`log_user_update.up.sql`**
```sql
CREATE TRIGGER log_user_update
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    IF OLD.email <> NEW.email THEN
        INSERT INTO audit_logs (user_id, old_value, new_value)
        VALUES (NEW.id, OLD.email, NEW.email);
    END IF;
END
```

## Considerations

Triggers can significantly impact performance if they contain complex logic. 

- **MySQL**: Triggers cannot be wrapped in standard transactions for DDL in some versions.
- **PostgreSQL**: Requires a separate trigger function to be defined first.

## Dependency Management

If your trigger depends on a specific table or function:
1. Ensure the table exists via standard Laravel migrations.
2. If it depends on another `DbObject` (like a function), ensure they are in the same folder or alphabetized so they run in order (or use groups).
