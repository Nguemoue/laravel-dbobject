<?php

namespace Nguemoue\LaravelDbObject\Adapters;

class PgSqlAdapter implements AdapterInterface
{
    public function quoteIdentifier(string $name): string
    {
        $name = str_replace('"', '""', $name);
        return "\"{$name}\"";
    }
}

