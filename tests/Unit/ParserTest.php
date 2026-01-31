<?php

use Nguemoue\LaravelDbObject\Migration\SqlFileParser;
use Illuminate\Support\Facades\File;

it('can parse a SQL file with up/down structure and config', function () {
    $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_group';
    if (!is_dir($tempDir)) {
        mkdir($tempDir);
    }
    
    $baseName = 'test_view';
    $upPath = $tempDir . DIRECTORY_SEPARATOR . $baseName . '.up.sql';
    $downPath = $tempDir . DIRECTORY_SEPARATOR . $baseName . '.down.sql';
    $configPath = $tempDir . DIRECTORY_SEPARATOR . $baseName . '.sql.json';
    
    $upSql = "CREATE VIEW test_view AS SELECT 1;";
    $downSql = "DROP VIEW IF EXISTS test_view;";
    $config = json_encode(['enabled' => true, 'on_exists' => 'replace']);
    
    file_put_contents($upPath, $upSql);
    file_put_contents($downPath, $downSql);
    file_put_contents($configPath, $config);

    $parsed = SqlFileParser::parse($upPath);

    expect($parsed['name'])->toBe('test_view');
    expect($parsed['group'])->toBe('test_group');
    expect($parsed['type'])->toBe('VIEW');
    expect($parsed['up_sql'])->toBe($upSql);
    expect($parsed['down_sql'])->toBe($downSql);
    expect($parsed['config_overrides'])->toHaveKey('on_exists', 'replace');

    // Clean up
    @unlink($upPath);
    @unlink($downPath);
    @unlink($configPath);
    @rmdir($tempDir);
});