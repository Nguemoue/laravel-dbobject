<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Nguemoue\LaravelDbObject\Migration\DboMigrator;

beforeEach(function () {
    $this->basePath = sys_get_temp_dir().'/dbo_test';
    // Ensure clean state
    if (File::exists($this->basePath)) {
        File::deleteDirectory($this->basePath);
    }
    File::makeDirectory($this->basePath, 0755, true);

    // Create a group folder
    $this->groupPath = $this->basePath.'/test_group';
    File::makeDirectory($this->groupPath);

    // Point config to this path
    config(['db-objects.path' => $this->basePath]);

    // Create unified SQL file
    $this->sqlPath = $this->groupPath.'/my_view.sql';

    $sqlContent = <<<'SQL'
---
object_type: view
group: test_group
---
-- up:
CREATE VIEW my_view AS SELECT 1 as col;
-- down:
DROP VIEW IF EXISTS my_view;
SQL;

    File::put($this->sqlPath, $sqlContent);
});

afterEach(function () {
    if (File::exists($this->basePath)) {
        File::deleteDirectory($this->basePath);
    }
});

it('migrates and rollbacks a view successfully from a single file', function () {
    $migrator = new DboMigrator;

    // Migrate
    $count = $migrator->migrateAll();
    expect($count)->toBe(1);

    // Verify existence in SQLite
    $exists = DB::selectOne("SELECT name FROM sqlite_master WHERE type='view' AND name='my_view'");
    expect($exists)->not->toBeNull();

    // Verify Status
    $status = $migrator->getStatus();
    $myViewStatus = collect($status)->firstWhere('name', 'my_view');
    expect($myViewStatus['status'])->toBe('Migrated');

    // Rollback
    $rolled = $migrator->rollbackLastBatch();
    expect($rolled)->toBe(1);

    // Verify removal
    $existsAfter = DB::selectOne("SELECT name FROM sqlite_master WHERE type='view' AND name='my_view'");
    expect($existsAfter)->toBeNull();
});
