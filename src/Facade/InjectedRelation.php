<?php

namespace Seat\Services\Facade;

use Illuminate\Support\Facades\Facade;
use Seat\Services\Services\InjectedRelationRegistry;

/**
 * @method static void registerRelation(string $model, string $extension, string $extension_name): void
 */
class InjectedRelation extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor(): string
    {
        return InjectedRelationRegistry::class;
    }
}