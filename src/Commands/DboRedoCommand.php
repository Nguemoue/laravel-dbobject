<?php

namespace Nguemoue\LaravelDbObject\Commands;

use Illuminate\Console\Command;
use Nguemoue\LaravelDbObject\Migration\DboMigrator;

class DboRedoCommand extends Command
{
    protected $signature = 'dbo:redo';
    protected $description = 'Rollback le dernier batch de migrations d\'objets, puis le ré-exécute (redo)';

    public function handle(): int
    {
        $migrator = new DboMigrator();
        $lastBatch = $migrator->getRepository()->getLastBatchNumber();
        if ($lastBatch === null) {
            $this->info("Aucun batch précédent à re-migrer (dbo_migrations vide).");
            return 0;
        }
        $this->info("Annulation du batch #{$lastBatch}...");
        $migrator->rollbackLastBatch();
        $this->info("Ré-exécution du batch #{$lastBatch}...");
        $count = $migrator->migrateAll();
        $this->info("Batch #{$lastBatch} migré à nouveau ({$count} objet(s) concernés).");
        return 0;
    }
}
