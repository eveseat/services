<?php

namespace Seat\Services\Services;

use Exception;
use Illuminate\Support\Collection;
use Seat\Services\Contracts\Prices\PriceProvider as PriceProviderContract;
use Seat\Services\Contracts\Prices\PriceProviderBackend;
use Seat\Services\Contracts\Prices\PriceProviderBackendDescription;
use Seat\Services\Models\PriceProviderInstance;

class PriceProviderImpl implements PriceProviderContract
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
    }
}