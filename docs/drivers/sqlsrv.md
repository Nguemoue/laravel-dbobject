# SQL Server (sqlsrv) Driver

## Defaults

- **transactional**: `true`
- **splitter**: `go_batch`
- **batch_separator**: `GO`
- **on_exists**: `recreate`
- **schema**: `dbo`

## Behavior

SQL Server scripts often use `GO` to separate batches. The driver splits the file on lines containing only `GO` (case-insensitive).

## Hello World Example

`hello.up.sql`:

```sql
CREATE PROCEDURE hello
AS
BEGIN
    SELECT 'Hello SQL Server';
END
GO
```
