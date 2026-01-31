---
title: Manifests (.sql.json)
description: Fine-tune how individual objects are migrated and managed.
---

While the package provides smart defaults, you sometimes need specific behavior for a single object. This is where the `.sql.json` file comes in.

## How it works

The manifest must share the same base name as your SQL files and be located in the same directory.

```text
database/dbo/
└── general/
    ├── my_proc.up.sql
    ├── my_proc.down.sql
    └── my_proc.sql.json   <-- This manifest controls 'my_proc'
```

## Available Keys

| Key | Value | Description |
| :--- | :--- | :--- |
| **enabled** | `true` or `false` | If `false`, the object is completely ignored during migrations. Useful for WIP objects. |
| **transactional** | `true` or `false` | Wraps the execution in a transaction. Note: MySQL implicitly commits DDL, so this is `false` by default there. |
| **splitter** | `"none"`, `"mysql_delimiter"`, `"go_batch"` | Defines how to split multiple statements. Use `none` if your script is a single block. |
| **on_exists** | `"recreate"`, `"replace"`, `"skip"` | `recreate` runs a DROP before CREATE. `replace` assumes your SQL uses `CREATE OR REPLACE`. |
| **on_missing_drop**| `"auto"`, `"skip"`, `"fail"` | Behavior when rolling back if `.down.sql` is missing. |
| **delimiter** | `string` | The marker for statement ends when using `mysql_delimiter` (default `$$`). |
| **batch_separator**| `string` | The marker for `go_batch` (default `GO`). |

## Common Examples

### Forcing a Recreate for a View
If you are changing the columns of a view, simple "Replace" might fail. Use "Recreate" to ensure a clean state.

```json
{
  "on_exists": "recreate"
}
```

### Disabling an object
```json
{
  "enabled": false
}
```

### Custom MySQL Delimiter
If your procedure uses `$$` inside the logic (e.g. for strings), change the delimiter to something else like `//`.

```json
{
  "splitter": "mysql_delimiter",
  "delimiter": "//"
}
```
