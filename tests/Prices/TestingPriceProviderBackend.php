<?php

namespace Seat\Tests\Services\Prices;

use Illuminate\Support\Collection;
use Seat\Services\Contracts\Prices\PriceProviderBackend;
use Seat\Services\Services\Prices\PriceProviderBackendDescription;

class TestingPriceProviderBackend extends PriceProviderBackend
{

    public function getPrices(Collection $items): Collection
    {
        foreach ($items as $item){
            $item->setPrice(1.0);
        }

        return $items;
    }

    public static function getDescription(): PriceProviderBackendDescription
    {
        return (new PriceProviderBackendDescription())
            ->for(static::class)
            ->name('services::testing.testing_prices_provider');

    }
}