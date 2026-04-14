<?php

use Nguemoue\LaravelDbObject\Migration\SqlFileParser;

it('can parse a single SQL file with front-matter and up/down markers', function () {
    $tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'test_group';
    if (! is_dir($tempDir)) {
        mkdir($tempDir);
    }

    $baseName = 'test_view';
    $filePath = $tempDir.DIRECTORY_SEPARATOR.$baseName.'.sql';

    $sqlContent = <<<'SQL'
---
object_type: view
group: test_group
depends_on: []
tags: [unit-test]
description: "Test description"
on_exists: replace
---
-- up:
CREATE VIEW test_view AS SELECT 1;
-- down:
DROP VIEW IF EXISTS test_view;
SQL;

    file_put_contents($filePath, $sqlContent);

    $parsed = SqlFileParser::parse($filePath);

    expect($parsed['name'])->toBe('test_view');
    expect($parsed['group'])->toBe('test_group');
    expect($parsed['type'])->toBe('VIEW');
    expect($parsed['up_sql'])->toBe('CREATE VIEW test_view AS SELECT 1;');
    expect($parsed['down_sql'])->toBe('DROP VIEW IF EXISTS test_view;');
    expect($parsed['config_overrides'])->toHaveKey('on_exists', 'replace');
    expect($parsed['config_overrides']['tags'])->toBe(['unit-test']);

    // Clean up
    @unlink($filePath);
    @rmdir($tempDir);
});

it('can parse a simple SQL file without markers as UP only', function () {
    $tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'simple_group';
    if (! is_dir($tempDir)) {
        mkdir($tempDir);
    }

    $filePath = $tempDir.DIRECTORY_SEPARATOR.'simple_view.sql';
    $sqlContent = 'CREATE VIEW simple_view AS SELECT 1;';

    file_put_contents($filePath, $sqlContent);

    $parsed = SqlFileParser::parse($filePath);

    expect($parsed['name'])->toBe('simple_view');
    expect($parsed['type'])->toBe('VIEW'); // Inferred
    expect($parsed['up_sql'])->toBe($sqlContent);
    expect($parsed['down_sql'])->toBeEmpty();

    @unlink($filePath);
    @rmdir($tempDir);
});
