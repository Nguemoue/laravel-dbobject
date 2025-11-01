<?php

namespace Nguemoue\LaravelDbObject\Commands;

use Illuminate\Console\Command;
use Nguemoue\LaravelDbObject\Migration\DboMigrator;

class DboRefreshCommand extends Command
{
    protected $signature = 'dbo:refresh';
    protected $description = 'Annule toutes les migrations d\'objets puis les exécute à nouveau depuis le début';

    public function handle(): int
    {
        $migrator = new DboMigrator();
        $repo = $migrator->getRepository();
        $lastBatch = $repo->getLastBatchNumber();
        if ($lastBatch === null) {
            $this->info("Aucun objet migré à rollback. Exécution d'un dbo:migrate complet...");
            $count = $migrator->migrateAll();
            $this->info("{$count} objet(s) migré(s).");
            return 0;
        }
        $this->info("Rollback de tous les objets migrés...");
        // Rollback batch par batch du dernier au premier
        while(($batch = $repo->getLastBatchNumber()) !== null) {
            $migrator->rollbackLastBatch();
            $this->line("Batch #{$batch} annulé.");
        }
        $this->info("Toutes les migrations d'objets ont été annulées.");
        $this->info("Ré-exécution de toutes les migrations d'objets...");
        $count = $migrator->migrateAll();
        $this->info("Migration terminée: {$count} objet(s) migré(s).");
        return 0;
    }
}
