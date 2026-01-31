<?php

use Nguemoue\LaravelDbObject\Adapters\MySqlAdapter;
use Nguemoue\LaravelDbObject\Adapters\PgSqlAdapter;
use Nguemoue\LaravelDbObject\Adapters\SqlSrvAdapter;
use Nguemoue\LaravelDbObject\Adapters\SqliteAdapter;

it('quotes identifiers correctly for MySQL', function () {
    $adapter = new MySqlAdapter();
    expect($adapter->quoteIdentifier('table'))->toBe('`table`');
    expect($adapter->quoteIdentifier('my`table'))->toBe('`my``table`');
});

it('quotes identifiers correctly for PostgreSQL', function () {
    $adapter = new PgSqlAdapter();
    expect($adapter->quoteIdentifier('table'))->toBe('"table"');
    expect($adapter->quoteIdentifier('my"table'))->toBe('"my""table"');
});

it('quotes identifiers correctly for SQL Server', function () {
    $adapter = new SqlSrvAdapter();
    expect($adapter->quoteIdentifier('table'))->toBe('[table]');
    expect($adapter->quoteIdentifier('my]table'))->toBe('[my]]table]');
});

it('quotes identifiers correctly for SQLite', function () {
    $adapter = new SqliteAdapter();
    expect($adapter->quoteIdentifier('table'))->toBe('"table"');
});