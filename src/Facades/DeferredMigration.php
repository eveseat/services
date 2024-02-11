<?php

namespace Seat\Services\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;
use Seat\Services\Services\DeferredMigrationRegistry;

/**
 * @method static void schedule(Closure $migration)
 */
class DeferredMigration extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DeferredMigrationRegistry::class;
    }
}