<?php

namespace Seat\Services\Contracts\Prices;

interface PriceProviderBackendDescription
{
    public function getName(): string;

    // TODO a way for configuration
    public function getConfigPrototype(): void;

    public function getBackendClass(): string;
}