<?php

namespace Seat\Services\Contracts;

/**
 * Describes items that are appraisable using recursivetree/seat-prices-core.
 * This interface is in the services package to encourage making classes that describe items compatible across both the
 * seat core and plugin, even if they don't depend on recursivetree/seat-prices-core.
 */
interface IPriceable extends HasTypeID
{
    /**
     * @return int The amount of items to be appraised by a price provider
     */
    public function getAmount(): int;

    /**
     * Set the price of this object
     * @param float $price
     * @return void
     */
    public function setPrice(float $price): void;
}