---
title: Troubleshooting
description: Common issues and how to fix them.
---

### "Object already exists" error
This happens if you try to migrate an object that is already in the database but not tracked by the package.
**Solution**: 
- Use `on_exists: recreate` in your `.sql.json`.
- Or manually drop the object once before running the migration.

### MySQL: Syntax error near 'BEGIN'
MySQL requires a different way of handling blocks.
**Solution**: Ensure your file ends with the configured delimiter (default `$$`) if you are using multiple statements. 
*Note: Our automatic splitter handles most cases without manual intervention.*

### Commands not found
If `php artisan dbo:...` commands are missing:
**Solution**: Ensure the service provider is registered (it should be automatic via Laravel Package Discovery). Try running `php artisan clear-compiled` and `composer dump-autoload`.
