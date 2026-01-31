<?php

namespace Nguemoue\LaravelDbObject\Configuration;

class ObjectConfiguration
{
    public bool $enabled = true;
    public bool $transactional;
    public string $splitter;
    public string $delimiter;
    public string $batchSeparator;
    public string $onExists;
    public string $onMissingDrop = 'auto'; // Default to auto (generate drop)
    public ?string $schema = null;

    public function __construct(string $driver, array $overrides = [])
    {
        $defaults = self::getDriverDefaults($driver);

        $this->transactional = $defaults['transactional'];
        $this->splitter = $defaults['splitter'];
        $this->delimiter = $defaults['delimiter'] ?? ';';
        $this->batchSeparator = $defaults['batch_separator'] ?? 'GO';
        $this->onExists = $defaults['on_exists'];
        $this->schema = $defaults['schema'];
        
        // Apply overrides
        if (isset($overrides['enabled']) && is_bool($overrides['enabled'])) {
            $this->enabled = $overrides['enabled'];
        }
        if (isset($overrides['transactional']) && is_bool($overrides['transactional'])) {
            $this->transactional = $overrides['transactional'];
        }
        if (isset($overrides['splitter']) && in_array($overrides['splitter'], ['none', 'mysql_delimiter', 'go_batch'])) {
            $this->splitter = $overrides['splitter'];
        }
        if (isset($overrides['delimiter']) && is_string($overrides['delimiter'])) {
            $this->delimiter = $overrides['delimiter'];
        }
        if (isset($overrides['batch_separator']) && is_string($overrides['batch_separator'])) {
            $this->batchSeparator = $overrides['batch_separator'];
        }
        if (isset($overrides['on_exists']) && in_array($overrides['on_exists'], ['skip', 'recreate', 'replace'])) {
            $this->onExists = $overrides['on_exists'];
        }
        if (isset($overrides['on_missing_drop']) && in_array($overrides['on_missing_drop'], ['skip', 'fail'])) {
            $this->onMissingDrop = $overrides['on_missing_drop'];
        }
        if (array_key_exists('schema', $overrides)) { // Allow null
             $this->schema = $overrides['schema'];
        }
    }

    public static function getDriverDefaults(string $driver): array
    {
        return match ($driver) {
            'pgsql' => [
                'transactional' => true,
                'splitter' => 'none',
                'on_exists' => 'replace',
                'schema' => 'public',
            ],
            'sqlsrv' => [
                'transactional' => true,
                'splitter' => 'go_batch',
                'batch_separator' => 'GO',
                'on_exists' => 'recreate',
                'schema' => 'dbo',
            ],
            'sqlite' => [
                'transactional' => true,
                'splitter' => 'none',
                'on_exists' => 'recreate',
                'schema' => null,
            ],
            default => [ // mysql, mariadb
                'transactional' => false,
                'splitter' => 'mysql_delimiter',
                'delimiter' => '$$',
                'on_exists' => 'recreate',
                'schema' => null,
            ],
        };
    }
}