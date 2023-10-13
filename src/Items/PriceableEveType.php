<?php

namespace Seat\Services\Items;

use Seat\Services\Contracts\HasTypeID;
use Seat\Services\Contracts\IPriceable;

/**
 * A basic implementation od IPriceable
 */
class PriceableEveType extends EveType implements IPriceable
{
    protected float $price;
    protected float $amount;

    /**
     * @param int|HasTypeID $type_id The eve type to be appraised
     * @param float $amount The amount of this type to be appraised
     */
    public function __construct(int|HasTypeID $type_id, float $amount)
    {
        parent::__construct($type_id);
        $this->price = 0;
        $this->amount = $amount;
    }

    /**
     * @return int The amount of this item to be appraised
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @return float The price of this item stack
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price The new price of this item stack
     * @return void
     */
    public function setPrice(float $price): void
    {
       $this->price = $price;
    }
}