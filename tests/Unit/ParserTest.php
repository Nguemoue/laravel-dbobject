<?php

use Nguemoue\LaravelDbObject\Migration\SqlFileParser;
use Illuminate\Support\Facades\File;

it('can parse a SQL file with YAML front-matter and up/down sections', function () {
    $sqlContent = <<<SQL
---
object_type: view
group: test
depends_on: ["users_view"]
tags: ["analytics"]
description: "Test view description"
---
-- up:
CREATE VIEW {{ident "active_users"}} AS SELECT * FROM users WHERE active = 1;
-- down:
DROP VIEW IF EXISTS {{ident "active_users"}};
SQL;

    // Ecrire le contenu dans un fichier temporaire
    $tempPath = sys_get_temp_dir() . '/test_active_users.sql';
    File::put($tempPath, $sqlContent);

    $parsed = SqlFileParser::parseFile($tempPath);

    expect($parsed['type'])->toBe('view');
    expect($parsed['group'])->toBe('test');
    expect($parsed['depends'])->toBe(['users_view']);
    expect($parsed['tags'])->toBe(['analytics']);
    expect($parsed['description'])->toBe('Test view description');
    expect(trim($parsed['up_sql']))->toStartWith('CREATE VIEW');
    expect(trim($parsed['down_sql']))->toBe('DROP VIEW IF EXISTS {{ident "active_users"}};');

    // Nettoyer le fichier temp
    File::delete($tempPath);
});
