---
title: Transactions
description: Ensuring data integrity during migrations.
---

Database migrations should be atomic. If one object fails, the others in that batch shouldn't be left in an inconsistent state.

## Transactional DDL

The package automatically wraps migrations in transactions based on the driver's capabilities.

| Driver | Default | Notes |
| :--- | :--- | :--- |
| **PostgreSQL** | `true` | Full support for transactional DDL. |
| **SQL Server** | `true` | Full support. |
| **SQLite** | `true` | Full support. |
| **MySQL** | `false` | MySQL implicitly commits after most DDL statements, making transactions ineffective for these operations. |

## Overriding Transactions

You can force a transaction on or off for a specific object via `.sql.json`.

```json
{
  "transactional": true
}
```

<Warning>
  Forcing `transactional: true` on MySQL will still result in implicit commits for most CREATE/DROP statements. Use with caution.
</Warning>
