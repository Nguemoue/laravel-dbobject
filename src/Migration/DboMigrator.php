<?php

namespace Nguemoue\LaravelDbObject\Migration;

use Illuminate\Support\Facades\DB;
use Nguemoue\LaravelDbObject\Adapters\AdapterInterface;
use Nguemoue\LaravelDbObject\Adapters\MySqlAdapter;

class DboMigrator
{
    protected DboMigrationRepository $repository;
    protected AdapterInterface|MySqlAdapter $adapter;

    public function __construct(?DboMigrationRepository $repository = null, ?AdapterInterface $adapter = null)
    {
        $this->repository = $repository ?: new DboMigrationRepository();
        $this->adapter = $adapter ?: new MySqlAdapter();
    }

    /**
     * Retourne l'instance du repository (table dbo_migrations).
     */
    public function getRepository(): DboMigrationRepository
    {
        return $this->repository;
    }

    /**
     * Applique toutes les migrations d'objets non encore migrés.
     * @param callable|null $onMigrated Callback(optionnel) appelé pour chaque objet migré (params: nom, type).
     * @return int Nombre d'objets migrés.
     * @throws \Exception si une migration échoue.
     */
    public function migrateAll(?callable $onMigrated = null): int
    {
        // S'assurer que la table de suivi existe
        $this->repository->ensureTableExists();

        // Récupérer tous les fichiers SQL d'objets
        $basePath = config('db-objects.path', base_path('database/dbo'));
        $files = $this->getAllSqlFiles($basePath);


        if (empty($files)) {
            return 0;
        }

        // Filtrer ceux déjà migrés
        $appliedRecords = $this->repository->getAllApplied();
        $appliedSet = [];
        foreach ($appliedRecords as $rec) {
            $appliedSet[strtolower($rec['object_type'] . ':' . $rec['object_name'])] = true;
        }

        // Construire la liste des objets pendings (non migrés)
        $pendingObjects = [];
        foreach ($files as $filePath) {
            $object = SqlFileParser::parseFile($filePath);
            $name = $object['name'];
            $type = $object['type'];
            $group = $object['group'];
            $key = strtolower($type . ':' . $name);
            if (!isset($appliedSet[$key])) {
                // Objet pas encore migré, on l'ajoute
                $pendingObjects[] = [
                    'name' => $name,
                    'type' => $type,
                    'group' => $group,
                    'up_sql' => $object['up_sql'],
                    'down_sql' => $object['down_sql'],
                    'depends' => $object['depends'] ?? []
                ];
            }
        }

        if (empty($pendingObjects)) {
            // Rien à migrer
            return 0;
        }

        // Ordonner en respectant les dépendances (algorithme de tri topologique)
        $ordered = $this->orderByDependencies($pendingObjects);

        // Récupérer le prochain numéro de batch
        $batch = ($this->repository->getLastBatchNumber() ?? 0) + 1;
        $migratedCount = 0;

        foreach ($ordered as $obj) {
            $name = $obj['name'];
            $type = $obj['type'];
            $group = $obj['group'];
            $upSql = $obj['up_sql'];

            // Remplacer macros dans le SQL up
            $sql = $this->adapter->processSql($upSql);

            // Exécuter le SQL de création
            DB::unprepared($sql);

            // Logguer la migration dans la table
            $this->repository->log($name, $type, $group, $batch);
            $migratedCount++;

            if ($onMigrated) {
                $onMigrated($name, $type);
            }
        }

        return $migratedCount;
    }

    /**
     * Rollback le dernier batch de migrations d'objets.
     * @return int Nombre d'objets rollbackés.
     */
    public function rollbackLastBatch(): int
    {
        $lastBatch = $this->repository->getLastBatchNumber();
        if ($lastBatch === null) {
            return 0;
        }
        // Récupérer les objets du dernier batch
        $batchRecords = $this->repository->getBatch($lastBatch)->all();
        if ($batchRecords === null) {
            return 0;
        }

        // Ordonner ces objets en inversant l'ordre de dépendance
        // (on peut simplement inverser l'ordre d'insertion si elles ont été migrées en ordre de dépendance)
        // Pour plus de sûreté, on peut reprojeter leurs dépendances et trier.
        $objects = [];
        foreach ($batchRecords as $rec) {
            $objects[] = [
                'name' => $rec->object_name,
                'type' => $rec->object_type,
                'group' => $rec->group
            ];
        }
        // Récupérer la définition de chaque objet (fichier) si dispo, sinon on génère un drop par type.
        $toRollback = [];
        foreach ($objects as $obj) {
            $name = $obj['name'];
            $type = $obj['type'];
            $group = $obj['group'];
            $filePath = config('db-objects.path') . "/{$group}/{$name}.sql";
            if (file_exists($filePath)) {
                $parsed = SqlFileParser::parseFile($filePath);
                $downSql = $parsed['down_sql'];
            } else {
                // si fichier absent, construire un SQL de drop par défaut
                $downSql = $this->defaultDropStatement($type, $name);
            }
            $toRollback[] = [
                'name' => $name,
                'type' => $type,
                'down_sql' => $downSql
            ];
        }
        // Si des dépendances existent entre objets du batch, s'assurer de l'ordre (inverse du up).
        // On peut supposer que l'ordre d'application initial correspond à l'ordre d'ID croissant.
        // Donc trions par id descendant:
        usort($batchRecords, function($a, $b) {
            return $b->id - $a->id;
        });
        // Ordonner la liste toRollback selon cet ordre d'id descendant:
        $orderedRollback = [];
        foreach ($batchRecords as $rec) {
            foreach ($toRollback as $entry) {
                if ($entry['name'] === $rec->object_name && $entry['type'] === $rec->object_type) {
                    $orderedRollback[] = $entry;
                    break;
                }
            }
        }

        // Exécuter chaque down et supprimer du repository
        $count = 0;
        foreach ($orderedRollback as $obj) {
            $downSql = $this->adapter->processSql($obj['down_sql']);
            DB::unprepared($downSql);
            $this->repository->remove($obj['name'], $obj['type']);
            $count++;
        }
        return $count;
    }

    /**
     * Rollback un objet spécifique (quel que soit son batch).
     * @param string $name Nom de l'objet à rollback.
     * @return bool True si un rollback a eu lieu, false si aucun objet trouvé.
     */
    public function rollbackObject(string $name): bool
    {
        // Trouver l'enregistrement correspondant (quel que soit le type)
        $record = $this->repository->findByName($name);
        if (!$record) {
            return false;
        }
        $type = $record->object_type;
        $group = $record->group;
        // Récupérer le SQL de down soit via le fichier soit via default
        $filePath = config('db-objects.path') . "/{$group}/{$name}.sql";
        if (file_exists($filePath)) {
            $parsed = SqlFileParser::parseFile($filePath);
            $downSql = $parsed['down_sql'];
        } else {
            $downSql = $this->defaultDropStatement($type, $name);
        }
        // Exécuter le down
        $sql = $this->adapter->processSql($downSql);
        DB::unprepared($sql);
        // Retirer du suivi
        $this->repository->remove($name, $type);
        return true;
    }

    /**
     * Renvoie la liste des statuts de tous les objets connus (fichiers et DB).
     * Chaque entrée du tableau a: name, type, group, status, batch (si migré).
     */
    public function getStatus(): array
    {
        $basePath = config('db-objects.path', base_path('database/dbo'));
        $files = $this->getAllSqlFiles($basePath);
        $statusList = [];

        // Récupérer tous les enregistrements de dbo_migrations
        $applied = $this->repository->getAllApplied();
        // Indexer par clé "type:name"
        $appliedIndex = [];
        foreach ($applied as $rec) {
            $key = strtolower($rec['object_type'] . ':' . $rec['object_name']);
            $appliedIndex[$key] = $rec;
        }

        // Lister les fichiers connus
        foreach ($files as $filePath) {
            $obj = SqlFileParser::parseFile($filePath);
            $name = $obj['name'];
            $type = $obj['type'];
            $group = $obj['group'];
            $key = strtolower($type . ':' . $name);
            if (isset($appliedIndex[$key])) {
                // Objet migré
                $batch = $appliedIndex[$key]['batch'];
                $statusList[] = [
                    'name' => $name,
                    'type' => $type,
                    'group' => $group,
                    'status' => 'Migrated',
                    'batch' => $batch
                ];
                unset($appliedIndex[$key]); // retirer de la liste des appliqués
            } else {
                // Objet pas migré
                $statusList[] = [
                    'name' => $name,
                    'type' => $type,
                    'group' => $group,
                    'status' => 'Pending'
                ];
            }
        }

        // Tout reste dans $appliedIndex à ce stade correspond à des objets en base sans fichier
        foreach ($appliedIndex as $key => $rec) {
            $statusList[] = [
                'name' => $rec['object_name'],
                'type' => $rec['object_type'],
                'group' => $rec['group'],
                'status' => 'Orphaned',   // or "Missing File"
                'batch' => $rec['batch']
            ];
        }

        // Trier la liste par group puis nom pour cohérence
        usort($statusList, static function($a, $b) {
            if ($a['group'] === $b['group']) {
                return strcmp($a['name'], $b['name']);
            }
            return strcmp($a['group'], $b['group']);
        });

        return $statusList;
    }

    /**
     * Récupère la liste de tous les fichiers .sql présents dans le dossier de base (récursif).
     */
    protected function getAllSqlFiles(string $basePath): array
    {
        $files = [];
        if (!is_dir($basePath)) {
            return $files;
        }
        // Récupérer tous les fichiers .sql (group = dossiers de premier niveau)
        $globPattern = $basePath . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*.sql';
        $files = glob($globPattern) ?: [];
        return $files;
    }

    /**
     * Trie une liste d'objets selon leurs dépendances (Kahn's algorithm).
     * @param array $objects Tableau d'objets pendings avec 'name', 'type', 'depends' etc.
     * @return array Liste ordonnée d'objets.
     * @throws \Exception si une dépendance circulaire est détectée.
     */
    protected function orderByDependencies(array $objects): array
    {
        // Indexer les objets par nom pour accès facile
        $objMap = [];
        foreach ($objects as $obj) {
            $objMap[$obj['name']] = $obj;
        }
        // Préparer le graphe des dépendances (nom -> liste de dépendants)
        $deps = [];
        foreach ($objects as $obj) {
            $name = $obj['name'];
            $deps[$name] = []; // initialise
        }
        foreach ($objects as $obj) {
            foreach ($obj['depends'] as $dep) {
                // Ne compter que les deps qui sont dans la liste pending (sinon ignore)
                if (isset($objMap[$dep])) {
                    $deps[$obj['name']][] = $dep;
                }
            }
        }

        // Kahn: liste des noeuds sans dépendances
        $ordered = [];
        $noDeps = [];
        foreach ($deps as $name => $depList) {
            if (empty($depList)) {
                $noDeps[] = $name;
            }
        }

        while (!empty($noDeps)) {
            $current = array_shift($noDeps);
            // Ajouter à la liste ordonnée
            if (isset($objMap[$current])) {
                $ordered[] = $objMap[$current];
            }
            // Retirer ce noeud des dépendances des autres
            foreach ($deps as $name => $depList) {
                $index = array_search($current, $depList, true);
                if ($index !== false) {
                    unset($deps[$name][$index]);
                    if (empty($deps[$name])) {
                        $noDeps[] = $name;
                    }
                }
            }
            unset($deps[$current]);
        }

        if (!empty($deps)) {
            // S'il reste des dépendances, il y a un cycle ou des deps manquantes
            throw new \Exception("Dépendances circulaires ou non résolues détectées: " . implode(',', array_keys($deps)));
        }

        return $ordered;
    }

    /**
     * Génère une requête SQL de DROP pour un objet donné, selon son type (si le fichier source n'existe pas).
     */
    protected function defaultDropStatement(string $type, string $name): string
    {
        $type = strtolower($type);
        return match ($type) {
            'function' => "DROP FUNCTION IF EXISTS {$this->adapter->quoteIdentifier($name)};",
            'procedure' => "DROP PROCEDURE IF EXISTS {$this->adapter->quoteIdentifier($name)};",
            'trigger' => "DROP TRIGGER IF EXISTS {$this->adapter->quoteIdentifier($name)};",
            'view' => "DROP VIEW IF EXISTS {$this->adapter->quoteIdentifier($name)};",
            default => "DROP OBJECT IF EXISTS {$this->adapter->quoteIdentifier($name)};",
        };
    }
}
