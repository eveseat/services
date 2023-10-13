<?php

namespace Seat\Services\Contracts;

/**
 * An interface to describe objects having a type id.
 */
interface HasTypeID
{
    /**
     * @return int The eve type id of this object
     */
    public function getTypeID(): int;
}