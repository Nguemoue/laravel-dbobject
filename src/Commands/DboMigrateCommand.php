<?php

namespace Nguemoue\LaravelDbObject\Commands;

use Illuminate\Console\Command;
use Nguemoue\LaravelDbObject\Migration\DboMigrator;

class DboMigrateCommand extends Command
{
    protected $signature = 'dbo:migrate';
    protected $description = 'Exécute les migrations (up) pour tous les objets non migrés';

    public function handle()
    {
        $this->info("Migration des objets de base de données en cours...");
        $migrator = new DboMigrator();

        try {
            $count = $migrator->migrateAll(function($objectName, $objectType) {
                $this->line("Migrated: <info>{$objectType} {$objectName}</info>");
            });
        } catch (\Exception $e) {
            $this->error("Erreur lors de la migration: " . $e->getMessage());
            return 1;
        }

        if ($count === 0) {
            $this->info("Aucun nouvel objet à migrer.");
        } else {
            $this->info("$count objet(s) migré(s).");
        }
        return 0;
    }
}
