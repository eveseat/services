<?php

namespace Seat\Services\Contracts\Prices;

use Seat\Services\Contracts\HasTypeID;

interface Priceable extends HasTypeID
{
    public function getAmount(): int;

    public function setPrice(float $price): void;
}