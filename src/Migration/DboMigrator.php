<?php

namespace Nguemoue\LaravelDbObject\Migration;

use Illuminate\Support\Facades\DB;
use Nguemoue\LaravelDbObject\Adapters\AdapterInterface;
use Nguemoue\LaravelDbObject\Adapters\MySqlAdapter;
use Nguemoue\LaravelDbObject\Adapters\PgSqlAdapter;
use Nguemoue\LaravelDbObject\Adapters\SqlSrvAdapter;
use Nguemoue\LaravelDbObject\Adapters\SqliteAdapter;
use Nguemoue\LaravelDbObject\Configuration\ObjectConfiguration;

class DboMigrator
{
    protected DboMigrationRepository $repository;
    
    public function __construct(?DboMigrationRepository $repository = null)
    {
        $this->repository = $repository ?: new DboMigrationRepository();
    }

    public function getRepository(): DboMigrationRepository
    {
        return $this->repository;
    }

    protected function getAdapter(string $driver): AdapterInterface
    {
        return match ($driver) {
            'pgsql' => new PgSqlAdapter(),
            'sqlsrv' => new SqlSrvAdapter(),
            'sqlite' => new SqliteAdapter(),
            default => new MySqlAdapter(), // mysql, mariadb
        };
    }

    public function migrateAll(?callable $onMigrated = null): int
    {
        $this->repository->ensureTableExists();

        $basePath = config('db-objects.path', base_path('database/dbo'));
        $files = $this->getAllUpFiles($basePath);

        if (empty($files)) {
            return 0;
        }

        $connectionName = DB::getDefaultConnection();
        $driver = DB::connection($connectionName)->getDriverName();
        $adapter = $this->getAdapter($driver);
        
        // Get applied migrations
        $appliedRecords = $this->repository->getAllApplied();
        $appliedSet = [];
        foreach ($appliedRecords as $rec) {
            $appliedSet[strtolower($rec['object_type'] . ':' . $rec['object_name'])] = true;
        }

        $migratedCount = 0;
        $batch = ($this->repository->getLastBatchNumber() ?? 0) + 1;

        foreach ($files as $filePath) {
            $object = SqlFileParser::parse($filePath);
            $name = $object['name'];
            $type = $object['type'];
            $group = $object['group'];
            $config = new ObjectConfiguration($driver, $object['config_overrides']);

            // 1. Check enabled
            if (!$config->enabled) {
                continue;
            }

            // 2. Check unsupported types for SQLite
            if ($driver === 'sqlite' && in_array(strtoupper($type), ['FUNCTION', 'PROCEDURE', 'TRIGGER'])) {
                if (in_array(strtoupper($type), ['FUNCTION', 'PROCEDURE'])) {
                    continue;
                }
            }

            // 3. Check if already migrated
            $key = strtolower($type . ':' . $name);
            if (isset($appliedSet[$key])) {
                continue;
            }

            // 4. Execution Logic
            $run = function () use ($object, $config, $adapter, $driver) {
                // Handle on_exists = recreate
                if ($config->onExists === 'recreate') {
                    $dropSql = $this->defaultDropStatement($object['type'], $object['name'], $adapter);
                    DB::statement($dropSql);
                }

                // Split and Execute
                $stmts = SqlSplitter::split($object['up_sql'], $config->splitter, $config->delimiter, $config->batchSeparator);
                
                foreach ($stmts as $stmt) {
                    if (trim($stmt) === '') continue;
                    DB::statement($stmt);
                }
            };

            // Transactional wrapper
            if ($config->transactional) {
                DB::transaction($run);
            } else {
                $run();
            }

            // Log migration
            $this->repository->log($name, $type, $group, $batch);
            $migratedCount++;

            if ($onMigrated) {
                $onMigrated($name, $type);
            }
        }

        return $migratedCount;
    }

    public function rollbackLastBatch(): int
    {
        $lastBatch = $this->repository->getLastBatchNumber();
        if ($lastBatch === null) {
            return 0;
        }
        $batchRecords = $this->repository->getBatch($lastBatch)->all();
        if (empty($batchRecords)) {
            return 0;
        }

        $connectionName = DB::getDefaultConnection();
        $driver = DB::connection($connectionName)->getDriverName();
        $adapter = $this->getAdapter($driver);
        $basePath = config('db-objects.path', base_path('database/dbo'));

        // Sort by ID descending (reverse order of creation)
        usort($batchRecords, function($a, $b) {
            return $b->id - $a->id;
        });

        $count = 0;
        foreach ($batchRecords as $rec) {
            $name = $rec->object_name;
            $type = $rec->object_type;
            $group = $rec->group;
            
            // Find file
            $upPath = $basePath . DIRECTORY_SEPARATOR . $group . DIRECTORY_SEPARATOR . $name . '.up.sql';
            
            $downSql = null;
            $config = null;
            
            if (file_exists($upPath)) {
                $object = SqlFileParser::parse($upPath);
                $downSql = $object['down_sql'];
                $config = new ObjectConfiguration($driver, $object['config_overrides']);
            } else {
                // Config defaults if file missing
                $config = new ObjectConfiguration($driver, []);
            }

            // Handle Missing Down SQL
            if (empty($downSql)) {
                if ($config->onMissingDrop === 'fail') {
                    throw new \RuntimeException("Missing down SQL for object: {$type} {$name}");
                } elseif ($config->onMissingDrop === 'skip') {
                    // Do not execute any drop SQL, just remove from logs
                    $this->repository->remove($name, $type);
                    $count++;
                    continue;
                } else {
                    // Default / Auto -> Generate Drop
                    $downSql = $this->defaultDropStatement($type, $name, $adapter);
                }
            }

            // Execute Down
            $run = function () use ($downSql, $config) {
                $stmts = SqlSplitter::split($downSql, $config->splitter, $config->delimiter, $config->batchSeparator);
                foreach ($stmts as $stmt) {
                    if (trim($stmt) === '') continue;
                    DB::statement($stmt);
                }
            };
            
            if ($config->transactional) {
                DB::transaction($run);
            } else {
                $run();
            }

            $this->repository->remove($name, $type);
            $count++;
        }

        return $count;
    }

    public function rollbackObject(string $name): bool
    {
        $record = $this->repository->findByName($name);
        if (!$record) {
            return false;
        }
        
        $connectionName = DB::getDefaultConnection();
        $driver = DB::connection($connectionName)->getDriverName();
        $adapter = $this->getAdapter($driver);
        $basePath = config('db-objects.path', base_path('database/dbo'));

        $type = $record->object_type;
        $group = $record->group;
        $upPath = $basePath . DIRECTORY_SEPARATOR . $group . DIRECTORY_SEPARATOR . $name . '.up.sql';

        $downSql = null;
        $config = null;

        if (file_exists($upPath)) {
            $object = SqlFileParser::parse($upPath);
            $downSql = $object['down_sql'];
            $config = new ObjectConfiguration($driver, $object['config_overrides']);
        } else {
            $config = new ObjectConfiguration($driver, []);
        }

        if (empty($downSql)) {
            if ($config->onMissingDrop === 'fail') {
                throw new \RuntimeException("Missing down SQL for object: {$type} {$name}");
            } elseif ($config->onMissingDrop === 'skip') {
                $this->repository->remove($name, $type);
                return true;
            } else {
                $downSql = $this->defaultDropStatement($type, $name, $adapter);
            }
        }

        $run = function () use ($downSql, $config) {
            $stmts = SqlSplitter::split($downSql, $config->splitter, $config->delimiter, $config->batchSeparator);
            foreach ($stmts as $stmt) {
                if (trim($stmt) === '') continue;
                DB::statement($stmt);
            }
        };

        if ($config->transactional) {
            DB::transaction($run);
        } else {
            $run();
        }

        $this->repository->remove($name, $type);
        return true;
    }

    public function getStatus(): array
    {
        $basePath = config('db-objects.path', base_path('database/dbo'));
        $files = $this->getAllUpFiles($basePath);
        $statusList = [];

        $applied = $this->repository->getAllApplied();
        $appliedIndex = [];
        foreach ($applied as $rec) {
            $key = strtolower($rec['object_type'] . ':' . $rec['object_name']);
            $appliedIndex[$key] = $rec;
        }

        foreach ($files as $filePath) {
            $obj = SqlFileParser::parse($filePath);
            $name = $obj['name'];
            $type = $obj['type'];
            $group = $obj['group'];
            $key = strtolower($type . ':' . $name);

            if (isset($appliedIndex[$key])) {
                $batch = $appliedIndex[$key]['batch'];
                $statusList[] = [
                    'name' => $name,
                    'type' => $type,
                    'group' => $group,
                    'status' => 'Migrated',
                    'batch' => $batch
                ];
                unset($appliedIndex[$key]);
            } else {
                $statusList[] = [
                    'name' => $name,
                    'type' => $type,
                    'group' => $group,
                    'status' => 'Pending'
                ];
            }
        }

        foreach ($appliedIndex as $rec) {
            $statusList[] = [
                'name' => $rec['object_name'],
                'type' => $rec['object_type'],
                'group' => $rec['group'],
                'status' => 'Orphaned',
                'batch' => $rec['batch']
            ];
        }

        usort($statusList, static function($a, $b) {
            if ($a['group'] === $b['group']) {
                return strcmp($a['name'], $b['name']);
            }
            return strcmp($a['group'], $b['group']);
        });

        return $statusList;
    }

    protected function getAllUpFiles(string $basePath): array
    {
        $files = [];
        if (!is_dir($basePath)) {
            return $files;
        }
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($basePath));
        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.up.sql')) {
                $files[] = $file->getPathname();
            }
        }
        return $files;
    }

    protected function defaultDropStatement(string $type, string $name, AdapterInterface $adapter): string
    {
        $type = strtoupper($type);
        $quotedName = $adapter->quoteIdentifier($name);
        return match ($type) {
            'FUNCTION' => "DROP FUNCTION IF EXISTS {$quotedName}",
            'PROCEDURE' => "DROP PROCEDURE IF EXISTS {$quotedName}",
            'TRIGGER' => "DROP TRIGGER IF EXISTS {$quotedName}",
            'VIEW' => "DROP VIEW IF EXISTS {$quotedName}",
            default => "DROP OBJECT IF EXISTS {$quotedName}",
        };
    }
}
