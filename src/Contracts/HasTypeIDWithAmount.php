<?php

namespace Seat\Services\Contracts;

/**
 * This interface is something between HasTypeID and IPriceable for things that have an amount and type but no price, like an asset list.
 * The goal is to improve cross-plugin item handling compatibility.
 */
interface HasTypeIDWithAmount extends HasTypeID
{
    /**
     * @return int The amount of items
     */
    public function getAmount(): int;
}