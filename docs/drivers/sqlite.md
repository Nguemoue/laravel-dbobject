# SQLite Driver

## Defaults

- **transactional**: `true`
- **splitter**: `none`
- **on_exists**: `recreate`
- **schema**: `null`

## Behavior

SQLite has limited support for stored objects.
- **Stored Functions/Procedures**: NOT supported. The migration will skip them safely.
- **Triggers/Views**: Supported.

## Hello World Example (View)

`hello_view.up.sql`:

```sql
CREATE VIEW hello_view AS SELECT 'Hello SQLite' as message;
```
