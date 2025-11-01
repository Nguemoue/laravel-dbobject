<?php

namespace Nguemoue\LaravelDbObject\Commands;

use Illuminate\Console\Command;
use Nguemoue\LaravelDbObject\Migration\DboMigrator;

class DboRollbackCommand extends Command
{
    protected $signature = 'dbo:rollback {name? : Nom de l\'objet à rollback (optionnel)}';
    protected $description = 'Annule (down) le dernier batch de migrations d\'objets, ou un objet spécifique si un nom est fourni';

    public function handle(): int
    {
        $objectName = $this->argument('name');
        $migrator = new DboMigrator();

        if ($objectName) {
            // Rollback d'un objet particulier
            if ($migrator->rollbackObject($objectName)) {
                $this->info("Objet '{$objectName}' rollbacké avec succès.");
                return 0;
            }

            $this->warn("Aucune migration trouvée pour l'objet '{$objectName}'.");
            return 0;
        }

        // Rollback du dernier batch
        $batch = $migrator->getRepository()->getLastBatchNumber();
        if ($batch === null) {
            $this->info("Aucun batch de migrations d'objets à annuler.");
            return 0;
        }
        $this->info("Rollback du batch #{$batch}...");
        $rolledBack = $migrator->rollbackLastBatch();
        if ($rolledBack > 0) {
            $this->info("Batch #{$batch} annulé ({$rolledBack} objet(s) rollbacké(s)).");
        } else {
            $this->info("Batch #{$batch} annulé.");
        }
        return 0;
    }
}
