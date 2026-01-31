---
title: Troubleshooting
description: Technical solutions for common database object issues.
---

## Migration Failures

### 1. "Object already exists" (SQL Error)
This happens when you run `dbo:migrate` but the object is already in the database without being tracked.
- **Why**: You probably created it manually or deleted the `dbo_migrations` table.
- **Fix**: Add `"on_exists": "recreate"` to your `.sql.json` to force the package to drop the existing object before creating the new one.

### 2. Syntax Error: "Check the manual that corresponds to your MySQL server"
- **Why**: Usually caused by the splitter not finding where your procedure ends.
- **Fix**: Ensure that if your script has a `BEGIN...END` block, it ends with the delimiter (default `$$`) on the same line as `END`.
  - Correct: `END$$`
  - Incorrect: `END;` (unless it's a single statement)

### 3. "Command dbo:migrate not found"
- **Why**: The service provider is not registered or the cache is stale.
- **Fix**: 
  1. Check `composer.json` for `"extra": { "laravel": { "providers": [...] } }`.
  2. Run `php artisan config:clear`.
  3. Ensure you are running the command from the root of your Laravel project.

## Rollback Issues

### 4. "Nothing to rollback"
- **Why**: You might be trying to rollback an object that was never successfully migrated (it's not in the `dbo_migrations` table).
- **Fix**: Run `php artisan dbo:status` to see if the object is marked as **Migrated**.

### 5. Rollback fails because of dependencies
- **Why**: You are trying to drop a view/function that is still being used by another object.
- **Fix**: You must rollback objects in the reverse order of their dependencies. Currently, you should manage this by rolling back the entire batch or by manually rolling back the dependent objects first.

## SQLite Specifics

### 6. "Unsupported object type"
- **Why**: You tried to migrate a `FUNCTION` or `PROCEDURE` to SQLite.
- **Fix**: SQLite does not support these via SQL scripts. The package skips them to prevent errors. Use **Views** or **Triggers** instead.