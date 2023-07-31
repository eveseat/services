<?php

namespace Seat\Tests\Services\Prices;

use Illuminate\Support\Collection;
use Seat\Services\Contracts\Prices\PriceProviderBackend;
use Seat\Services\Contracts\Prices\PriceProviderBackendDescription;

class TestingPriceProviderBackend extends PriceProviderBackend
{

    public function getPrices(Collection $items): Collection
    {
        foreach ($items as $item){
            $item->setPrice(1.0);
        }
    }

    public static function getDescription(): PriceProviderBackendDescription
    {
        return new TestingPriceProviderDescription();
    }
}