---
title: Stored Procedures
description: Learn how to manage complex logic inside procedures.
---

Stored procedures are ideal for batch operations or complex logic that needs to reside close to the data.

## Basic Example (MySQL)

**`register_user.up.sql`**
```sql
CREATE PROCEDURE register_user(IN p_email VARCHAR(255))
BEGIN
    INSERT INTO users (email, created_at) VALUES (p_email, NOW());
END
```

## Advanced Example (SQL Server)

SQL Server often requires multiple batches using the `GO` separator.

**`process_monthly_billing.up.sql`**
```sql
CREATE PROCEDURE process_monthly_billing
AS
BEGIN
    -- Batch 1: Mark pending
    UPDATE invoices SET status = 'processing' WHERE due_date <= GETDATE();
END
GO

CREATE PROCEDURE get_processing_stats
AS
BEGIN
    SELECT count(*) FROM invoices WHERE status = 'processing';
END
GO
```

## Rollback Strategy

Always provide a `.down.sql` file to ensure clean rollbacks.

**`register_user.down.sql`**
```sql
DROP PROCEDURE IF EXISTS register_user;
```

## Driver Specifics

| Driver | Delimiter Handling |
| :--- | :--- |
| **MySQL** | Handled automatically. No `DELIMITER //` needed in SQL. |
| **PostgreSQL** | Use `CREATE OR REPLACE` for smoother updates. |
| **SQL Server** | Supports `GO` batching. |
