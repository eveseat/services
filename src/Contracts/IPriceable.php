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

namespace Seat\Services\Contracts;

/**
 * Describes items that are appraisable using recursivetree/seat-prices-core.
 * This interface is in the services package to encourage making classes that describe items compatible across both the
 * seat core and plugin, even if they don't depend on recursivetree/seat-prices-core.
 */
interface IPriceable extends HasTypeID, HasTypeIDWithAmount
{
    /**
     * Set the price of this object.
     *
     * @param  float  $price
     * @return void
     */
    public function setPrice(float $price): void;
}
