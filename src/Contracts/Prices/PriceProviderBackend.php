<?php

namespace Seat\Services\Contracts\Prices;

use Illuminate\Support\Collection;
use Seat\Services\Services\Prices\PriceProviderBackendDescription;

abstract class PriceProviderBackend
{
    protected array $configuration;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param Collection<Priceable> $items
     * @return Collection<Priceable>
     */
    abstract public function getPrices(Collection $items): Collection;

    abstract public static function getDescription(): PriceProviderBackendDescription;
}