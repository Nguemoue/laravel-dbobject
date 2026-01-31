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
        $group = $this->option('group') ?: config('dbobjects.default_group', 'general');

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
        $basePath = config('dbobjects.path', base_path('database/dbo'));
        $groupPath = $basePath . DIRECTORY_SEPARATOR . $group;
        
        $upPath = $groupPath . DIRECTORY_SEPARATOR . $name . '.up.sql';
        $downPath = $groupPath . DIRECTORY_SEPARATOR . $name . '.down.sql';

        // Vérifier l'existence
        if (File::exists($upPath)) {
            $this->error("Le fichier $upPath existe déjà.");
            return 1;
        }

        // Créer le dossier du group s'il n'existe pas
        if (!File::isDirectory($groupPath)) {
            File::makeDirectory($groupPath, 0755, true);
        }

        // Load stubs
        $stubUpPath = __DIR__ . '/../../stubs/dbo.up.stub';
        $stubDownPath = __DIR__ . '/../../stubs/dbo.down.stub';

        $upContent = File::exists($stubUpPath) ? File::get($stubUpPath) : "CREATE __TYPE__ __NAME__\n";
        $downContent = File::exists($stubDownPath) ? File::get($stubDownPath) : "DROP __TYPE__ IF EXISTS __NAME__;\n";

        // Replacements
        $replacements = [
            '__NAME__'        => $name,
            '__TYPE__'        => strtoupper($type),
        ];
        
        $upContent = str_replace(array_keys($replacements), array_values($replacements), $upContent);
        $downContent = str_replace(array_keys($replacements), array_values($replacements), $downContent);

        // Write files
        File::put($upPath, $upContent);
        File::put($downPath, $downContent);

        $this->info("Fichiers de migration créés:");
        $this->info("- $upPath");
        $this->info("- $downPath");
        
        return 0;
    }
}