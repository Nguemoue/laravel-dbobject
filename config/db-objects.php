<?php

return [
    /*
   |--------------------------------------------------------------------------
   | Database Objects Migration Table
   |--------------------------------------------------------------------------
   |
   | Nom de la table utilisée pour enregistrer l'état des migrations d'objets.
   | Vous pouvez la modifier si besoin (par exemple en cas de conflit ou par préférence).
   |
   */
    'table' => env('DBO_MIGRATIONS_TABLE', 'dbo_migrations'),

    /*
    |--------------------------------------------------------------------------
    | Path to Database Objects SQL Files
    |--------------------------------------------------------------------------
    |
    | Chemin du répertoire où se trouvent les fichiers SQL de définition des objets.
    | Par défaut, le package utilise le dossier 'database/dbo' de votre application.
    |
    */
    'path' => base_path('database/dbo'),

    /*
    |--------------------------------------------------------------------------
    | Default Group Name
    |--------------------------------------------------------------------------
    |
    | Nom de group par défaut à utiliser lorsque vous créez un nouveau fichier
    | via la commande dbo:make sans préciser l'option --group.
    |
    */
    'default_group' => 'general',

    /*
    |--------------------------------------------------------------------------
    | Stub File for New Objects
    |--------------------------------------------------------------------------
    |
    | Chemin vers le fichier "stub" utilisé par dbo:make pour générer le contenu initial
    | des fichiers de migration d'objet. Par défaut, on pointe sur le stub pouvant être
    | publié dans le dossier /stubs de votre application. Si ce fichier n'existe pas,
    | le stub interne du package sera utilisé.
    |
    */
    'stub' => base_path('stubs/dbo.stub'),
    'paths' => [ base_path('database/dbo') ],
    'connection' => env('DB_OBJECTS_CONNECTION'), // null => default
    'default_filters' => [
        'groups' => [],
        'tags'   => [],
        'names'  => [],
        'exclude'=> [],
    ],
    'rollback_steps' => 1,
    'dry_run' => false,
];
