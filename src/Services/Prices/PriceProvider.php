<?php

namespace Seat\Services\Services\Prices;

use Illuminate\Support\Collection;
use Seat\Services\Contracts\Prices\PriceProviderBackend;
use Seat\Services\Models\PriceProviderInstance;

class PriceProvider
{
    public function instance(int $identifier): PriceProviderBackend
    {
        $instance = PriceProviderInstance::find($identifier);

        return new $instance->backend($instance->configuration);
    }


    /**
     * @return Collection<PriceProviderBackendDescription>
     */
    public function availableBackends(): Collection
    {
        return collect(config('priceproviders.backends'))
            ->map(function (string $PriceProviderBackend){
                return $PriceProviderBackend::getDescription();
            });
    }

    public function createInstance(PriceProviderBackendDescription $description, array $configuration): int
    {
        $instance = new PriceProviderInstance();
        $instance->name = "TODO: get name here";
        $instance->backend = $description->getBackendClass();
        $instance->configuration = $configuration;
        $instance->save();

        return $instance->id;
    }
}