<?php

namespace Seat\Services\Facades;

use Illuminate\Support\Facades\Facade;

class PriceProvider extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Seat\Services\Services\Prices\PriceProvider::class;
    }
}