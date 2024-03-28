<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Services\Items;

use Seat\Services\Contracts\HasTypeID;
use Seat\Services\Contracts\IPriceable;

/**
 * A basic implementation od IPriceable.
 */
class PriceableEveType extends EveTypeWithAmount implements IPriceable
{
    protected float $price;

    /**
     * @param  int|HasTypeID  $type_id  The eve type to be appraised
     * @param  float|int  $amount  The amount of this type to be appraised
     */
    public function __construct(int|HasTypeID $type_id, float|int $amount)
    {
        parent::__construct($type_id, (int) $amount);
        $this->price = 0;
    }

    /**
     * @return float The price of this item stack
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param  float  $price  The new price of this item stack
     * @return void
     */
    public function setPrice(float $price): void
    {
       $this->price = $price;
    }
}
