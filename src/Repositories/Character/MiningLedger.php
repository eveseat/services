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
    public function getCharacterLedger(int $character_id, bool $get = true)
    {

        $ledger = CharacterMining::select('character_minings.date', 'solar_system_id', 'character_minings.type_id')
            ->join('invTypes', 'invTypes.typeID', 'character_minings.type_id')
            ->leftJoin('historical_prices', function ($join) {
                $join->on('historical_prices.type_id', '=', 'character_minings.type_id')
                     ->on('historical_prices.date', '=', 'character_minings.date');
            })
            ->where('character_id', $character_id);

        if (! $get)
            return $ledger;

        return $ledger->get();
    }
}
