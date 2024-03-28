<?php

namespace Seat\Services\Items;

use Seat\Services\Contracts\HasTypeID;
use Seat\Services\Contracts\HasTypeIDWithAmount;

/**
 * A simple implementation on HasTypeIDWithAmount
 */
class EveTypeWithAmount extends EveType implements HasTypeIDWithAmount
{
    private int $amount;

    /**
     * @param int|HasTypeID $type_id
     * @param int $amount
     */
    public function __construct(int|HasTypeID $type_id, int $amount)
    {
        parent::__construct($type_id);
        $this->amount = $amount;
    }

    /**
     * @return int The amount of items
     */
    public function getAmount(): int
    {
        return $this->amount;
    }
}