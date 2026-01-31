# PostgreSQL Driver

## Defaults

- **transactional**: `true`
- **splitter**: `none`
- **on_exists**: `replace`
- **schema**: `public`

## Behavior

PostgreSQL supports transactional DDL. The driver executes the SQL file as a single block by default (`splitter: none`).
It defaults to `on_exists: replace`, assuming you use `CREATE OR REPLACE ...`.

## Hello World Example

`hello.up.sql`:

```sql
CREATE OR REPLACE FUNCTION hello() RETURNS text AS $$
BEGIN
    RETURN 'Hello Postgres';
END;
$$ LANGUAGE plpgsql;
```
