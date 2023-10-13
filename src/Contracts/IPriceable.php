<?php

namespace Seat\Services\Contracts;

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