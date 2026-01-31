---
title: SQL Splitters
description: How the package handles complex SQL scripts.
---

When executing SQL files, standard database drivers often struggle with files containing multiple statements or complex blocks (like `BEGIN...END`). Laravel DbObject uses **Splitters** to solve this.

## Splitter Strategies

### `none`
The entire file is sent to the database as a single string.
- **Best for**: PostgreSQL, single-statement views.

### `mysql_delimiter`
Automatically handles blocks. It identifies the end of a statement using a custom delimiter (default `$$`).
- **Best for**: MySQL/MariaDB procedures and triggers.
- **Feature**: You don't need to write `DELIMITER //` in your SQL file. The package handles it.

### `go_batch`
Splits the file based on the `GO` keyword on its own line.
- **Best for**: SQL Server scripts.

## Customizing the Delimiter

If your SQL contains the default `$$` as data, you can change it in your manifest.

**`my_procedure.sql.json`**
```json
{
  "splitter": "mysql_delimiter",
  "delimiter": "//"
}
```

Then in your SQL:
```sql
CREATE PROCEDURE my_proc()
BEGIN
  -- logic here
END//
```
