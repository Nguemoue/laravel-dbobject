# MySQL / MariaDB Driver

## Defaults

- **transactional**: `false` (MySQL does not support transactional DDL well).
- **splitter**: `mysql_delimiter`
- **delimiter**: `$$`
- **on_exists**: `recreate`
- **schema**: `null`

## Behavior

The driver parses `BEGIN ... END` blocks if they use the configured delimiter (default `$$`).
You do **not** need to add `DELIMITER $$` commands in your file. Just use the delimiter if needed or rely on the parser to handle single statements.

## Hello World Example

`hello.up.sql`:

```sql
CREATE PROCEDURE hello()
BEGIN
  SELECT 'Hello MySQL';
END
```

## Limitations

- DDL statements cause implicit commits, so `transactional` is disabled by default.
