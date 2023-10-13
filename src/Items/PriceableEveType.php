<?php

namespace Seat\Services\Items;

use Seat\Services\Contracts\HasTypeID;
use Seat\Services\Contracts\IPriceable;

class PriceableEveType extends EveType implements IPriceable
{
    protected float $price;
    protected float $amount;

    /**
     * @param int|HasTypeID $type_id
     * @param float $amount
     */
    public function __construct(int|HasTypeID $type_id, float $amount)
    {
        parent::__construct($type_id);
        $this->price = 0;
        $this->amount = $amount;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
       $this->price = $price;
    }
}