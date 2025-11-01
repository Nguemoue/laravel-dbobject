<?php

namespace Nguemoue\LaravelDbObject\Adapters;

class MySqlAdapter implements AdapterInterface
{
    /**
     * Remplace les macros dans le SQL par les équivalents MySQL.
     */
    public function processSql(string $sql): string
    {
        // [[now]] -> NOW()
        $sql = str_replace('[[now]]', 'NOW()', $sql);

        // {{ident "name"}} et {{id "name"}} -> `name` (quote MySQL)
        // Utilisation d'une regex pour trouver {{ident "<texte>"}}
        $pattern = '/\{\{\s*(ident|id)\s*"([^"]+)"\s*\}\}/';
        $sql = preg_replace_callback($pattern, function($matches) {
            $name = $matches[2];
            // On quote le nom avec des backticks
            return $this->quoteIdentifier($name);
        }, $sql);

        return $sql;
    }

    /**
     * Quote un identifiant avec des backticks (MySQL).
     */
    public function quoteIdentifier(string $name): string
    {
        // Échapper les backticks déjà présents
        $escaped = str_replace('`', '``', $name);
        return "`{$escaped}`";
    }
}
