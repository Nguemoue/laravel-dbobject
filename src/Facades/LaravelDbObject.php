<?php

namespace Nguemoue\LaravelDbObject\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Nguemoue\LaravelDbObject\LaravelDbObject
 */
class LaravelDbObject extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Nguemoue\LaravelDbObject\LaravelDbObject::class;
    }
}
