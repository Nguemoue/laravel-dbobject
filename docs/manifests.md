---
title: Manifests & Config
description: Configure object-specific behavior using JSON manifests.
---

## Overview

Each stored object is defined by a set of files. While the `.up.sql` file is mandatory, you can also provide a `.sql.json` file to override default driver behaviors for that specific object.

## File Naming

For an object named `calculate_tax` in the `finance` group:

| File | Description |
| :--- | :--- |
| `calculate_tax.up.sql` | **Required**. Contains the `CREATE` statement(s). |
| `calculate_tax.down.sql` | **Optional**. Contains the `DROP` statement(s). |
| `calculate_tax.sql.json` | **Optional**. Configuration overrides. |

## Configuration Options

You can override driver defaults using the `.sql.json` file.

```json
{
  "enabled": true,
  "transactional": false,
  "splitter": "mysql_delimiter",
  "on_exists": "recreate",
  "on_missing_drop": "auto"
}
```

### Reference

| Key | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| **enabled** | `bool` | `true` | Set to `false` to skip migration of this object. |
| **transactional** | `bool` | *Driver Default* | Whether to wrap the execution in a database transaction. |
| **splitter** | `string` | *Driver Default* | Strategy to split SQL statements (`none`, `mysql_delimiter`, `go_batch`). |
| **delimiter** | `string` | `$$` | Custom delimiter used when splitter is `mysql_delimiter`. |
| **batch_separator**| `string` | `GO` | Separator used when splitter is `go_batch`. |
| **on_exists** | `string` | *Driver Default* | Behavior if object exists: `recreate` (Drop/Create) or `replace` (Create Or Replace). |
| **on_missing_drop**| `string` | `auto` | Behavior during rollback if `.down.sql` is missing. <br>• `auto`: Generate a default DROP statement.<br>• `skip`: Do nothing (remove from log only).<br>• `fail`: Throw an exception. |
| **schema** | `string` | *Driver Default* | Target schema name (informational for some drivers). |