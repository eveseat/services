<?php

namespace Seat\Services\Services\Prices;

class PriceProviderBackendDescription
{
    private string $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getBackendClass(): string
    {
        return $this->backend_class;
    }

    /**
     * @param string $name
     * @return PriceProviderBackendDescription
     */
    public function name(string $name): PriceProviderBackendDescription
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $backend_class
     * @return PriceProviderBackendDescription
     */
    public function for(string $backend_class): PriceProviderBackendDescription
    {
        $this->backend_class = $backend_class;
        return $this;
    }
    private string $backend_class;
}