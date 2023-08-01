<?php

namespace Seat\Services\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Collection availableBackends
 */
class PriceProvider extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Seat\Services\Services\Prices\PriceProvider::class;
    }
}