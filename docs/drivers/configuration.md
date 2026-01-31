---
title: Driver Defaults
description: See the built-in configuration for each database driver.
---

Laravel DbObject automatically detects your database connection and applies these defaults. You only need to provide a `.sql.json` file if you want to change these values.

## MySQL / MariaDB
Standard configuration optimized for procedures and triggers.

```json
{
  "transactional": false,
  "splitter": "mysql_delimiter",
  "delimiter": "$$",
  "on_exists": "recreate",
  "schema": null
}
```

## PostgreSQL
Optimized for transactional DDL and function replacement.

```json
{
  "transactional": true,
  "splitter": "none",
  "on_exists": "replace",
  "schema": "public"
}
```

## SQL Server (sqlsrv)
Configured to handle `GO` batches commonly found in T-SQL scripts.

```json
{
  "transactional": true,
  "splitter": "go_batch",
  "batch_separator": "GO",
  "on_exists": "recreate",
  "schema": "dbo"
}
```

## SQLite
Focused on Views and Triggers (Stored procedures/functions are not supported in standard SQLite).

```json
{
  "transactional": true,
  "splitter": "none",
  "on_exists": "recreate",
  "schema": null
}
```
