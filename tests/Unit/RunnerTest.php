<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Nguemoue\LaravelDbObject\Migration\DboMigrator;
use Nguemoue\LaravelDbObject\Migration\SqlFileParser;

// Préparer un fichier SQL de vue pour le test
beforeEach(function() {
    $this->basePath = base_path('database/dbo/test');
    File::ensureDirectoryExists($this->basePath);
    $this->filePath = $this->basePath . '/my_view.sql';
    $sqlContent = <<<SQL
---
object_type: view
group: test
depends_on: []
tags: []
description: "Test view"
---
-- up:
CREATE VIEW {{ident "my_view"}} AS SELECT 1 as one;
-- down:
DROP VIEW IF EXISTS {{ident "my_view"}};
SQL;
    File::put($this->filePath, $sqlContent);
});

afterEach(function() {
    // Nettoyer: supprimer le fichier et dossier test
    File::delete($this->filePath);
    File::deleteDirectory(dirname($this->filePath));
});

it('migrates and rollbacks a view successfully', function () {
    $migrator = new DboMigrator();
    // Au départ, la vue n'existe pas
    // Exécuter la migration
    $count = $migrator->migrateAll();
    expect($count)->toBe(1);

    // Vérifier que la vue existe en base (SQLite) en interrogeant sqlite_master
    $exists = DB::selectOne("SELECT name FROM sqlite_master WHERE type='view' AND name='my_view'");
    expect($exists)->not->toBeNull();

    // Status doit indiquer la vue migrée
    $status = $migrator->getStatus();
    $myViewStatus = collect($status)->firstWhere('name', 'my_view');
    expect($myViewStatus)->not->toBeNull();
    expect($myViewStatus['status'])->toBe('Migrated');

    // Rollback la vue
    $rolled = $migrator->rollbackLastBatch();
    expect($rolled)->toBe(1);

    // Vérifier que la vue a été supprimée
    $existsAfter = DB::selectOne("SELECT name FROM sqlite_master WHERE type='view' AND name='my_view'");
    expect($existsAfter)->toBeNull();

    // La table de suivi ne doit plus contenir d'entrée pour my_view
    $statusAfter = $migrator->getStatus();
    $myViewStatusAfter = collect($statusAfter)->firstWhere('name', 'my_view');
    expect($myViewStatusAfter['status'])->toBe('Pending');
});
