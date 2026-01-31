-- @id: core/hello_world
-- @type: function
-- @name: sample
-- @schema:
-- @dialects: mysql,mariadb,pgsql,sqlsrv
-- @version: 1
-- @tags: demo,core
-- @depends_on:
--   - core/languages_table
-- @description: Example function that returns 1
-- @options.transactional: false
-- @options.idempotent: true

-- up:
CREATE FUNCTION {{ident "hello_world"}}()
RETURNS INT DETERMINISTIC
BEGIN
RETURN 1;
END;
-- down:
DROP FUNCTION IF EXISTS {{ident "hello_world"}};
