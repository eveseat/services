<?php

namespace Seat\Tests\Services\Prices;

use Seat\Services\Contracts\Prices\PriceProviderBackendDescription;

class TestingPriceProviderDescription implements PriceProviderBackendDescription
{

    public function getName(): string
    {
        return trans('services::testing.testing_prices_provider');
    }

    public function getConfigPrototype(): void
    {
        // TODO: Implement getConfigPrototype() method.
    }

    public function getBackendClass(): string
    {
        return TestingPriceProviderBackend::class;
    }
}