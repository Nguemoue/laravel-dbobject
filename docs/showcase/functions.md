---
title: Stored Functions
description: Scalable calculations inside your database.
---

Functions are used to return values and can be used directly inside `SELECT` statements.

## PostgreSQL Showcase

PostgreSQL functions are powerful and support multiple languages.

**`get_discounted_price.up.sql`**
```sql
CREATE OR REPLACE FUNCTION get_discounted_price(price NUMERIC, discount NUMERIC)
RETURNS NUMERIC AS $$
BEGIN
    RETURN price - (price * discount);
END;
$$ LANGUAGE plpgsql;
```

**`get_discounted_price.sql.json`**
```json
{
  "on_exists": "replace",
  "transactional": true
}
```

## MySQL Showcase

**`slugify.up.sql`**
```sql
CREATE FUNCTION slugify(s VARCHAR(255)) RETURNS VARCHAR(255)
DETERMINISTIC
BEGIN
    RETURN LOWER(REPLACE(s, ' ', '-'));
END
```

## Limitations

<Warning>
  **SQLite Note**: Standard SQLite does not support creating stored functions via SQL. These files will be skipped safely by the migrator.
</Warning>
