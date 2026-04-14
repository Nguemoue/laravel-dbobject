<?php

use Nguemoue\LaravelDbObject\Migration\SqlSplitter;

it('returns the same SQL when splitter is none', function () {
    $sql = 'CREATE VIEW test AS SELECT 1;';
    $stmts = SqlSplitter::split($sql, 'none');

    expect($stmts)->toBe([$sql]);
});

it('splits by mysql_delimiter correctly', function () {
    $sql = <<<'SQL'
CREATE PROCEDURE p1()
BEGIN
    SELECT 1;
END$$

CREATE PROCEDURE p2()
BEGIN
    SELECT 2;
END$$
SQL;

    $stmts = SqlSplitter::split($sql, 'mysql_delimiter', '$$');

    expect(count($stmts))->toBe(2);
    expect($stmts[0])->toContain('CREATE PROCEDURE p1()');
    expect($stmts[0])->not->toContain('$$');
    expect($stmts[1])->toContain('CREATE PROCEDURE p2()');
});

it('splits by go_batch correctly', function () {
    $sql = <<<'SQL'
CREATE VIEW v1 AS SELECT 1;
GO
CREATE VIEW v2 AS SELECT 2;
GO
SQL;

    $stmts = SqlSplitter::split($sql, 'go_batch', '$$', 'GO');

    expect(count($stmts))->toBe(2);
    expect($stmts[0])->toBe('CREATE VIEW v1 AS SELECT 1;');
    expect($stmts[1])->toBe('CREATE VIEW v2 AS SELECT 2;');
});

it('handles trailing content without delimiters', function () {
    $sql = 'SELECT 1;$$ SELECT 2;';
    // Note: splitMysqlDelimiter in current implementation is line-based for delimiters at the END of trimmed lines

    $stmts = SqlSplitter::split($sql, 'mysql_delimiter', '$$');
    expect(count($stmts))->toBe(2);
});
