<?php

namespace Nguemoue\LaravelDbObject\Adapters;

class SqlSrvAdapter implements AdapterInterface
{
    public function quoteIdentifier(string $name): string
    {
        $name = str_replace(']', ']]', $name);
        return "[{$name}]";
    }
}
