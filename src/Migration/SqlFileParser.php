<?php

namespace Nguemoue\LaravelDbObject\Migration;

class SqlFileParser
{
    /**
     * Parse an object definition starting from its UP SQL file.
     *
     * @param string $filePath Path to the .sql file
     * @return array
     */
    public static function parse(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \RuntimeException("Cannot read file: $filePath");
        }

        $directory = dirname($filePath);
        $filename = basename($filePath);
        $baseName = preg_replace('/\.sql$/', '', $filename);
        $group = basename($directory);

        // 1. Extract Front-matter
        $configOverrides = [];
        $frontMatterPattern = '/^---\s*\n(.*?)\n---\s*\n/s';
        $body = $content;

        if (preg_match($frontMatterPattern, $content, $matches)) {
            $frontMatter = $matches[1];
            $body = substr($content, strlen($matches[0]));
            
            // Simple YAML-like parsing (key: value)
            foreach (explode("\n", $frontMatter) as $line) {
                if (str_contains($line, ':')) {
                    [$key, $value] = explode(':', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Handle simple arrays like []
                    if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
                        $value = trim($value, '[]');
                        $configOverrides[$key] = $value ? array_map('trim', explode(',', $value)) : [];
                    } else {
                        // Remove quotes if present
                        $value = trim($value, "\"'");
                        $configOverrides[$key] = $value;
                    }
                }
            }
        }

        // 2. Split UP and DOWN sections
        $upSql = '';
        $downSql = '';

        if (preg_match('/--\s*up:(.*?)(?:--\s*down:|$)/is', $body, $upMatches)) {
            $upSql = trim($upMatches[1]);
        }
        
        if (preg_match('/--\s*down:(.*)$/is', $body, $downMatches)) {
            $downSql = trim($downMatches[1]);
        }

        // If no markers found, treat the whole body as UP (for backward compatibility or simple files)
        if (empty($upSql) && !str_contains($body, '-- up:')) {
            $upSql = trim($body);
        }

        // Infer type from front-matter or SQL
        $type = $configOverrides['object_type'] ?? self::inferType($upSql);
        $group = $configOverrides['group'] ?? $group;

        return [
            'name' => $baseName,
            'group' => $group,
            'type' => strtoupper($type),
            'up_sql' => $upSql,
            'down_sql' => $downSql,
            'config_overrides' => $configOverrides,
            'depends' => $configOverrides['depends_on'] ?? [],
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