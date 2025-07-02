<?php

namespace Alareqi\FilamentAppVersionManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Alareqi\FilamentAppVersionManager\FilamentAppVersionManager
 */
class FilamentAppVersionManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Alareqi\FilamentAppVersionManager\FilamentAppVersionManager::class;
    }
}
