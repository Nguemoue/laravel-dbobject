<?php

namespace Nguemoue\LaravelDbObject\Adapters;

interface AdapterInterface
{
    /**
     * Quote an identifier (object name, column name, etc.) appropriate for the driver.
     */
    public function quoteIdentifier(string $name): string;
}