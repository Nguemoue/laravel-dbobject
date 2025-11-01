<?php

namespace Nguemoue\LaravelDbObject\Migration;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DboMigrationRepository
{
    protected ?string $table;

    public function __construct()
    {
        $this->table = config('db-objects.table', 'dbo_migrations');
    }

    /**
     * Vérifie l'existence de la table dbo_migrations et la crée si nécessaire.
     */
    public function ensureTableExists(): void
    {
        if (! Schema::hasTable($this->table)) {
            Schema::create($this->table, static function($table) {
                $table->id();
                $table->string('object_name');
                $table->string('object_type');
                $table->string('group')->nullable();
                $table->integer('batch');
                $table->timestamp('migrated_at')->useCurrent();
                $table->unique(['object_name', 'object_type']);
            });
        }
    }

    /**
     * Renvoie toutes les migrations appliquées (toutes entrées de la table).
     * @return array Tableau associatif des enregistrements (colonnes -> valeurs).
     */
    public function getAllApplied(): array
    {
        $this->ensureTableExists();
        $records = DB::table($this->table)->orderBy('id')->get();
        $result = [];
        foreach ($records as $rec) {
            $result[] = [
                'id' => $rec->id,
                'object_name' => $rec->object_name,
                'object_type' => $rec->object_type,
                'group' => $rec->group,
                'batch' => $rec->batch,
                'migrated_at' => $rec->migrated_at,
            ];
        }
        return $result;
    }

    /**
     * Retourne le numéro du dernier batch migré, ou null s'il n'y en a pas.
     */
    public function getLastBatchNumber(): ?int
    {
        $batch = DB::table($this->table)->max('batch');
        return $batch ?: null;
    }

    /**
     * Renvoie les enregistrements du batch spécifié (sous forme de collection d'objets).
     */
    public function getBatch(int $batch): \Illuminate\Support\Collection
    {
        return DB::table($this->table)->where('batch', $batch)->orderBy('id')->get();
    }

    /**
     * Insère un enregistrement de migration d'objet (log de migration réussie).
     */
    public function log(string $objectName, string $objectType, string $group, int $batch): void
    {
        DB::table($this->table)->insert([
            'object_name' => $objectName,
            'object_type' => $objectType,
            'group'       => $group,
            'batch'       => $batch,
            'migrated_at' => now(),
        ]);
    }

    /**
     * Supprime l'enregistrement d'un objet migré (lors d'un rollback).
     */
    public function remove(string $objectName, string $objectType): void
    {
        DB::table($this->table)
            ->where('object_name', $objectName)
            ->where('object_type', $objectType)
            ->delete();
    }

    /**
     * Trouve un enregistrement par nom d'objet (peu importe le type).
     * Renvoie le premier trouvé, ou null si aucun.
     */
    public function findByName(string $name)
    {
        return DB::table($this->table)->where('object_name', $name)->first();
    }
}
