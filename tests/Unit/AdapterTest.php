<?php

use Nguemoue\LaravelDbObject\Adapters\MySqlAdapter;

it('replaces custom macros with MySQL syntax', function () {
    $adapter = new MySqlAdapter();
    $sql = 'INSERT INTO {{ident "logs"}} (created_at) VALUES ([[now]]);';
    $processed = $adapter->processSql($sql);
    // [[now]] -> NOW()
    expect($processed)->toContain('NOW()');
    // {{ident "logs"}} -> `logs`
    expect($processed)->toContain('INSERT INTO `logs`');
    // QuoteIdentifier escapement test
    $ident = 'te`st';
    $quoted = $adapter->quoteIdentifier($ident);
    expect($quoted)->toBe('`te``st`');
});
