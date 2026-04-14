<?php

namespace Nguemoue\LaravelDbObject\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DboMakeCommand extends Command
{
    protected $signature = 'dbo:make {name : Nom de l\'objet (fichier)} {--type= : Type de l\'objet (function, procedure, trigger, view)} {--group= : Group/dossier de classement (optionnel)}';
    protected $description = 'Crée les fichiers de migration (.up.sql, .down.sql) pour un objet de base de données.';

    public function handle()
    {
        $name = $this->argument('name');
        $type = strtolower($this->option('type') ?? '');
        $group = $this->option('group') ?: config('db-objects.default_group', 'general');

        // Valider le type
        $validTypes = ['function', 'procedure', 'trigger', 'view'];
        if (!in_array($type, $validTypes)) {
            $this->error("Invalid --type specified. Allowed types: function, procedure, trigger, view.");
            return 1;
        }

        // Clean name
        $name = preg_replace('/\.up\.sql$/', '', $name);
        $name = preg_replace('/\.sql$/', '', $name);

        // Construire le chemin du fichier à créer
        $basePath = config('db-objects.path', base_path('database/dbo'));
        $groupPath = $basePath . DIRECTORY_SEPARATOR . $group;
        
        $sqlPath = $groupPath . DIRECTORY_SEPARATOR . $name . '.sql';

        // Vérifier l'existence
        if (File::exists($sqlPath)) {
            $this->error("Le fichier $sqlPath existe déjà.");
            return 1;
        }

        // Créer le dossier du group s'il n'existe pas
        if (!File::isDirectory($groupPath)) {
            File::makeDirectory($groupPath, 0755, true);
        }

        // Load stub
        $stubPath = __DIR__ . '/../../stubs/dbo.stub';

        if (!File::exists($stubPath)) {
            $this->error("Le stub $stubPath n'existe pas.");
            return 1;
        }

        $content = File::get($stubPath);

        // Replacements
        $replacements = [
            '__NAME__'        => $name,
            '__TYPE__'        => $type,
            '__GROUP__'       => $group,
        ];
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        // Write file
        File::put($sqlPath, $content);

        $this->info("Fichier de migration créé:");
        $this->info("- $sqlPath");
        
        return 0;
    }
}