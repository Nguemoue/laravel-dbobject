<?php

namespace Nguemoue\LaravelDbObject\Migration;

class SqlFileParser
{
    /**
     * Parse an object definition starting from its UP SQL file.
     *
     * @param string $upFilePath Path to the .up.sql file
     * @return array
     */
    public static function parse(string $upFilePath): array
    {
        $directory = dirname($upFilePath);
        $filename = basename($upFilePath);
        
        // Expected format: name.up.sql
        // If the file does not end in .up.sql, we might skip it or handle it legacy?
        // The user says "Drop a .up.sql file".
        
        // Let's support legacy .sql too?
        // The prompt implies a new system. Let's strictly look for .up.sql or handle .sql as legacy if needed.
        // But for "UPDATE the project", let's assume we look for .up.sql primarily.
        // However, DboMigrator currently globs `*.sql`. I will change that later.
        
        $baseName = preg_replace('/\.up\.sql$/', '', $filename);
        if ($baseName === $filename) {
             // Fallback for simple .sql files if we decide to support them as "up only"
             $baseName = preg_replace('/\.sql$/', '', $filename);
        }
        
        $group = basename($directory);
        
        $upSql = file_get_contents($upFilePath);
        if ($upSql === false) {
             throw new \RuntimeException("Cannot read file: $upFilePath");
        }
        
        // Look for down file
        $downFilePath = $directory . DIRECTORY_SEPARATOR . $baseName . '.down.sql';
        $downSql = '';
        if (file_exists($downFilePath)) {
            $downSql = file_get_contents($downFilePath) ?: '';
        }
        
        // Look for config file
        $configFilePath = $directory . DIRECTORY_SEPARATOR . $baseName . '.sql.json';
        $configOverrides = [];
        if (file_exists($configFilePath)) {
            $json = file_get_contents($configFilePath);
            if ($json) {
                $decoded = json_decode($json, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $configOverrides = $decoded;
                }
            }
        }
        
        // Infer type from SQL
        $type = self::inferType($upSql);
        
        return [
            'name' => $baseName,
            'group' => $group,
            'type' => $type, // might be 'unknown' or null
            'up_sql' => $upSql,
            'down_sql' => $downSql,
            'config_overrides' => $configOverrides,
            'depends' => [], // Dependencies are not easily defined in pure SQL without parsing. 
                             // The user didn't mention dependencies in the new spec. 
                             // We will leave it empty for now.
        ];
    }
    
    protected static function inferType(string $sql): string
    {
        // Simple regex to find CREATE ...
        // Remove comments for better matching?
        $sql = preg_replace('!/\*.*?\*/!s', '', $sql); // block comments
        $sql = preg_replace('/^--.*$/m', '', $sql); // line comments
        
        if (preg_match('/CREATE\s+(?:OR\s+REPLACE\s+)?(PROCEDURE|FUNCTION|VIEW|TRIGGER)\s+/i', $sql, $matches)) {
            return strtoupper($matches[1]);
        }
        
        return 'OBJECT';
    }
}