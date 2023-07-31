<?php

namespace Seat\Services\Contracts\Prices;

use Illuminate\Support\Collection;

interface PriceProvider
{
    /**
     * Creates a backend with its configuration
     * @param string $identifier
     * @return PriceProviderBackend
     */
    public function instance(int $identifier): PriceProviderBackend;

    /**
     * @return Collection<PriceProviderBackendDescription>
     */
    public function availableBackends(): Collection;

    public function createInstance(PriceProviderBackendDescription $description, array $configuration): int;
}