<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
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

namespace Seat\Services\Repositories\Character;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Seat\Eveapi\Models\Industry\CharacterMining;

/**
 * Trait MiningLedger.
 *
 * @package Seat\Services\Repositories\Character
 */
trait MiningLedger
{
    /**
     * @param int  $character_id
     * @param bool $get
     *
     * @return mixed
     */
    public function getCharacterLedger(Collection $character_ids) : Builder
    {

        return CharacterMining::with('type', 'system')
            ->select(
                'character_minings.date',
                'character_minings.character_id',
                'solar_system_id',
                'character_minings.type_id',
                'historical_prices.adjusted_price')
            ->leftJoin('historical_prices', function ($join) {
                $join->on('historical_prices.type_id', '=', 'character_minings.type_id')
                     ->on('historical_prices.date', '=', 'character_minings.date');
            })
            ->whereIn('character_id', $character_ids->toArray());
    }
}
