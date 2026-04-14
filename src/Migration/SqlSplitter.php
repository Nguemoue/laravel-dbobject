<?php

namespace Nguemoue\LaravelDbObject\Migration;

class SqlSplitter
{
    /**
     * Splits SQL content based on the configured strategy.
     *
     * @param string $sql The SQL content.
     * @param string $splitter The splitter strategy ("none", "mysql_delimiter", "go_batch").
     * @param string $delimiter The custom delimiter (used for mysql_delimiter).
     * @param string $batchSeparator The batch separator (used for go_batch, default "GO").
     * @return array The list of SQL statements.
     */
    public static function split(string $sql, string $splitter, string $delimiter = '$$', string $batchSeparator = 'GO'): array
    {
        return match ($splitter) {
            'mysql_delimiter' => self::splitMysqlDelimiter($sql, $delimiter),
            'go_batch' => self::splitGoBatch($sql, $batchSeparator),
            default => [$sql],
        };
    }

    protected static function splitMysqlDelimiter(string $sql, string $delimiter): array
    {
        if (empty($delimiter)) {
            return [$sql];
        }

        // Split by the delimiter
        $parts = explode($delimiter, $sql);
        $stmts = [];
        
        foreach ($parts as $part) {
            $trimmed = trim($part);
            if ($trimmed !== '') {
                $stmts[] = $trimmed;
            }
        }
        
        return $stmts;
    }

    protected static function splitGoBatch(string $sql, string $separator): array
    {
        // Split on lines containing only "GO" (case-insensitive)
        $lines = explode("\n", $sql);
        $stmts = [];
        $buffer = '';
        
        foreach ($lines as $line) {
            if (trim(strtoupper($line)) === strtoupper($separator)) {
                if (trim($buffer) !== '') {
                    $stmts[] = trim($buffer);
                }
                $buffer = '';
            } else {
                $buffer .= $line . "\n";
            }
        }
        
        if (trim($buffer) !== '') {
            $stmts[] = trim($buffer);
        }
        
        return $stmts;
    }
}
