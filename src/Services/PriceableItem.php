<?php

namespace Seat\Services\Services;

use Seat\Services\Contracts\HasTypeID;
use Seat\Services\Contracts\Prices\Priceable;

class PriceableItem implements Priceable
{
    private int $amount;
    private int $type_id;

    private float $price;

    /**
     * @param int $amount
     * @param int $type_id
     */
    public function __construct(int | HasTypeID $type_id, int $amount, )
    {
        if($type_id instanceof HasTypeID){
            $type_id = $type_id->getTypeID();
        }

        $this->amount = $amount;
        $this->type_id = $type_id;
    }

    public function getTypeID(): int
    {
        return $this->type_id;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }
}