---
title: CLI Reference
description: Control your database objects from the command line.
---

The package provides a suite of Artisan commands to manage the lifecycle of your stored objects.

## Core Commands

### `dbo:migrate`
Executes all pending migrations. It scans your `database/dbo` folder for `.up.sql` files that haven't been recorded in the `dbo_migrations` table yet.

```bash
php artisan dbo:migrate
```

### `dbo:rollback`
Rolls back migrations.
- **Last Batch**: Without arguments, it rolls back the entire last batch of migrations.
- **Specific Object**: Provide the object name to rollback only that specific object.

```bash
php artisan dbo:rollback
php artisan dbo:rollback my_function
```

### `dbo:status`
Displays a table showing the current status of all discovered objects.

```bash
php artisan dbo:status
```

| Status | Meaning |
| :--- | :--- |
| **Pending** | File exists but not yet migrated to the database. |
| **Migrated** | File exists and is currently active in the database. |
| **Orphaned** | Entry exists in the database logs but the file is missing. |

## Development Commands

### `dbo:make`
Generates a new set of migration files.

```bash
php artisan dbo:make my_object --type=view --group=reports
```

### `dbo:refresh`
Rolls back all active migrations and then re-runs them. Useful for a clean slate.

```bash
php artisan dbo:refresh
```

### `dbo:redo`
Rolls back the last batch and immediately re-runs it. Perfect for iterative development on a set of objects.

```bash
php artisan dbo:redo
```
