<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Nguemoue\LaravelDbObject\Migration\DboMigrationRepository;

it('creates migration table and logs entries', function () {
    $repo = new DboMigrationRepository();

    // Ensure creation
    $repo->ensureTableExists();
    expect(Schema::hasTable('dbo_migrations'))->toBeTrue();

    // Log an entry
    $repo->log('obj1', 'view', 'general', 1);
    $repo->log('obj2', 'function', 'general', 1);

    // Check getAllApplied
    $all = $repo->getAllApplied();
    expect(count($all))->toBe(2);
    $names = array_column($all, 'object_name');
    expect($names)->toContain('obj1');
    expect($names)->toContain('obj2');

    // Last batch should be 1
    expect($repo->getLastBatchNumber())->toBe(1);

    // Remove one entry
    $repo->remove('obj1', 'view');
    $remaining = DB::table('dbo_migrations')->pluck('object_name')->all();

    expect($remaining)->not->toContain('obj1');
    expect($remaining)->toContain('obj2');
});
