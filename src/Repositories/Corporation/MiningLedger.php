<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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

namespace Seat\Services\Repositories\Corporation;

use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Industry\CharacterMining;

/**
 * Trait MiningLedger.
 *
 * @package Seat\Services\Repositories\Corporation
 */
trait MiningLedger
{
    /**
     * @param int  $corporation_id
     * @param bool $get
     *
     * @return mixed
     */
    public function getCorporationLedgers(int $corporation_id, bool $get = true)
    {

        $ledger = CharacterMining::select('year', 'month')
            ->join('corporation_member_trackings', 'corporation_member_trackings.character_id', 'character_minings.character_id')
            ->distinct()
            ->where('corporation_id', $corporation_id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc');

        if (! $get)
            return $ledger;

        return $ledger->get();
    }

    /**
     * @param int  $corporation_id
     * @param int  $year
     * @param int  $month
     * @param bool $get
     *
     * @return mixed
     */
    public function getCorporationLedger(int $corporation_id, int $year, int $month, bool $get = true)
    {

        $ledger = CharacterMining::select('character_minings.character_id', 'year', 'month', DB::raw('SUM(quantity) as quantity'), DB::raw('SUM(quantity * volume) as volumes'), DB::raw('SUM(quantity * adjusted_price) as amounts'))
            ->join('corporation_member_trackings', 'corporation_member_trackings.character_id', 'character_minings.character_id')
            ->join('invTypes', 'invTypes.typeID', 'character_minings.type_id')
            ->leftJoin('historical_prices', function ($join) {
                $join->on('historical_prices.type_id', '=', 'character_minings.type_id')
                     ->on('historical_prices.date', '=', 'character_minings.date');
            })
            ->where('corporation_id', $corporation_id)
            ->where('year', $year)
            ->where('month', $month)
            ->groupBy('character_id', 'year', 'month');

        if (! $get)
            return $ledger;

        return $ledger->get();
    }
}
