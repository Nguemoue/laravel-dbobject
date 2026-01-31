---
title: FAQ
description: Frequently asked questions.
---

### Is this a replacement for Laravel Migrations?
**No.** Use Laravel migrations for your Tables and Columns. Use DbObject for your Views, Procedures, Functions, and Triggers.

### Can I use PHP variables inside my SQL files?
**No.** We prioritize **Pure SQL**. If you need dynamic SQL, we recommend using standard Laravel Migrations or calling your procedures with parameters from PHP.

### Does it support my database?
We currently support:
- MySQL 5.7+ / MariaDB
- PostgreSQL
- SQL Server (sqlsrv)
- SQLite (Views and Triggers only)

### How do I change the default directory?
Publish the config file and change the `path` key:
```bash
php artisan vendor:publish --tag="db-objects-config"
```
Then edit `config/db-objects.php`.
