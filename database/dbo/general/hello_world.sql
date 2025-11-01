---
object_type: function
group: general
depends_on: []
tags: []
description: "Example function that returns 1"
---
-- up:
CREATE FUNCTION {{ident "hello_world"}}()
RETURNS INT DETERMINISTIC
BEGIN
RETURN 1;
END;
-- down:
DROP FUNCTION IF EXISTS {{ident "hello_world"}};
