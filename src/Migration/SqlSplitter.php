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
        // Simple parser for MySQL BEGIN/END blocks or custom delimiters.
        // However, the prompt says: "parse BEGIN/END blocks using the configured delimiter"
        // and "DO NOT require users to write DELIMITER statements in SQL files".
        
        // If the user does not write DELIMITER, then the SQL is likely just standard statements 
        // OR it contains CREATE PROCEDURE ... BEGIN ... END;
        // If we use "mysql_delimiter", we usually assume the user might want to run multiple statements.
        // But the typical issue with MySQL stored procedures is the internal semicolons.
        
        // If "delimiter" is passed (e.g. "$$"), it implies we should treat the whole block as one statement
        // if it matches the pattern, OR we just execute it.
        
        // Wait, if the user doesn't write "DELIMITER $$", how do we know where the statement ends?
        // The prompt says "parse BEGIN/END blocks using the configured delimiter".
        // This likely means we need to detect BEGIN...END blocks and treat them as a single unit, 
        // OR the user puts the delimiter at the end of the procedure like `END$$`.
        
        // Let's assume the standard behavior requested:
        // "parse BEGIN/END blocks using the configured delimiter"
        
        // Actually, if the file contains pure SQL, it might look like:
        // CREATE PROCEDURE foo() BEGIN SELECT 1; END;
        // The standard driver (PDO) might fail if we don't wrap it or split correctly.
        
        // If the strategy is "mysql_delimiter", and default delimiter is "$$",
        // maybe the user WRITES the delimiter in the file? 
        // "DO NOT require users to write DELIMITER statements in SQL files" -> This implies `DELIMITER //` command is not needed.
        // But maybe the `//` or `$$` IS present at the end of the block.
        
        // Let's implement a splitter that looks for the delimiter. 
        
        $stmts = [];
        $buffer = '';
        $lines = explode("\n", $sql);
        $delimiterLen = strlen($delimiter);
        
        foreach ($lines as $line) {
            $trimmed = trim($line);
            $buffer .= $line . "\n";
            
            if (substr($trimmed, -$delimiterLen) === $delimiter) {
                // Statement ended
                // Remove the delimiter from the end
                $stmt = substr($buffer, 0, strrpos($buffer, $delimiter)); 
                // We might need to keep the delimiter or not depending on the driver? 
                // Usually for PDO/Laravel execution, we DON'T send the delimiter if it's not standard SQL ';'.
                // But for procedures, we just send the create statement.
                
                if (trim($stmt) !== '') {
                    $stmts[] = trim($stmt);
                }
                $buffer = '';
            }
        }
        
        if (trim($buffer) !== '') {
            $stmts[] = trim($buffer);
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
