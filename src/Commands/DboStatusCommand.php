<?php

namespace Nguemoue\LaravelDbObject\Commands;

use Illuminate\Console\Command;
use Nguemoue\LaravelDbObject\Migration\DboMigrator;

class DboStatusCommand extends Command
{
    protected $signature = 'dbo:status';
    protected $description = 'Affiche le statut (migré/non migré) de tous les objets gérés par le package';

    public function handle(): int
    {
        $migrator = new DboMigrator();
        $statusList = $migrator->getStatus();

        if (empty($statusList)) {
            $this->info("Aucun fichier d'objet trouvé dans " . config('dbobjects.path'));
            return 0;
        }

        // Préparer les lignes pour le tableau
        $rows = [];
        foreach ($statusList as $entry) {
            $rows[] = [
                'Name'   => $entry['name'],
                'Type'   => ucfirst($entry['type']),
                'Group'  => $entry['group'],
                'Status' => $entry['status'],
                'Batch'  => $entry['batch'] ?? '',
            ];
        }

        $this->table(['Name', 'Type', 'Group', 'Status', 'Batch'], $rows);
        return 0;
    }
}
