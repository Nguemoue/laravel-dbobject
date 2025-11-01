<?php

namespace Nguemoue\LaravelDbObject\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DboMakeCommand extends Command
{
    protected $signature = 'dbo:make {name : Nom de l\'objet (fichier)} {--type= : Type de l\'objet (function, procedure, trigger, view)} {--group= : Group/dossier de classement (optionnel)}';
    protected $description = 'Crée un fichier de migration pour un objet de base de données (fonction, procédure, vue, trigger).';

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

        // S'assurer que le nom n'a pas d'extension .sql (on l'ajoutera)
        $fileName = Str::endsWith($name, '.sql') ? $name : $name . '.sql';

        // Construire le chemin du fichier à créer
        $basePath = config('dbobjects.path', base_path('database/dbo'));
        $groupPath = $basePath . DIRECTORY_SEPARATOR . $group;
        $fullPath = $groupPath . DIRECTORY_SEPARATOR . $fileName;

        // Vérifier l'existence
        if (File::exists($fullPath)) {
            $this->error("Le fichier $fullPath existe déjà.");
            return 1;
        }

        // Créer le dossier du group s'il n'existe pas
        if (!File::isDirectory($groupPath)) {
            File::makeDirectory($groupPath, 0755, true);
        }

        // Charger le stub depuis la config (sinon stub interne)
        $stubPath = config('dbobjects.stub');
        if (!File::exists($stubPath)) {
            $stubPath = __DIR__ . '/../../stubs/dbo.stub';
        }

        $stubContent = File::get($stubPath);

        // Remplacer les placeholders dans le stub
        $replacements = [
            '__OBJECT_TYPE__' => $type,
            '__GROUP__'       => $group,
            '__NAME__'        => $name,
            '__TYPE__'        => strtoupper($type),
        ];
        $content = str_replace(array_keys($replacements), array_values($replacements), $stubContent);

        // Écrire le nouveau fichier
        File::put($fullPath, $content);

        $this->info("Fichier de migration créé: $fullPath");
        return 0;
    }
}
