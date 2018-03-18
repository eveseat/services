<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017  Leon Jacobs
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

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Contracts\ContractItem;

/**
 * Class Contracts.
 * @package Seat\Services\Repositories\Corporation
 */
trait Contracts
{
    /**
     * Return the contracts for a Corporation.
     *
     * @param int  $corporation_id
     * @param bool $get
     * @param int  $chunk
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getCorporationContracts(
        int $corporation_id, bool $get = true, int $chunk = 50)
    {

        $contracts = DB::table(DB::raw('contract_details as a'))
            ->select(DB::raw(
                '
                --
                -- All Columns
                --
                *,

                --
                -- Start Location Lookup
                --
                CASE
                when a.start_location_id BETWEEN 66015148 AND 66015151 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.start_location_id-6000000)
                when a.start_location_id BETWEEN 66000000 AND 66014933 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.start_location_id-6000001)
                when a.start_location_id BETWEEN 66014934 AND 67999999 then
                    (SELECT d.name FROM `sovereignty_structures` AS c
                      JOIN universe_stations d ON c.structure_id = d.station_id
                      WHERE c.structure_id = a.start_location_id-6000000)
                when a.start_location_id BETWEEN 60014861 AND 60014928 then
                    (SELECT d.name FROM `sovereignty_structures` AS c
                      JOIN universe_stations d ON c.structure_id = d.station_id
                      WHERE c.structure_id = a.start_location_id)
                when a.start_location_id BETWEEN 60000000 AND 61000000 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.start_location_id)
                when a.start_location_id >= 61000000 then
                    (SELECT d.name FROM `sovereignty_structures` AS c
                      JOIN universe_stations d ON c.structure_id = d.station_id
                      WHERE c.structure_id = a.start_location_id)
                else (SELECT m.itemName FROM mapDenormalize AS m
                    WHERE m.itemID = a.start_location_id) end
                AS startlocation,

                --
                -- End Location Lookup
                --
                CASE
                when a.end_location_id BETWEEN 66015148 AND 66015151 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.end_location_id-6000000)
                when a.end_location_id BETWEEN 66000000 AND 66014933 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.end_location_id-6000001)
                when a.end_location_id BETWEEN 66014934 AND 67999999 then
                    (SELECT d.name FROM `sovereignty_structures` AS c
                      JOIN universe_stations d ON c.structure_id = d.station_id
                      WHERE c.structure_id = a.end_location_id-6000000)
                when a.end_location_id BETWEEN 60014861 AND 60014928 then
                    (SELECT d.name FROM `sovereignty_structures` AS c
                      JOIN universe_stations d ON c.structure_id = d.station_id
                      WHERE c.structure_id = a.end_location_id)
                when a.end_location_id BETWEEN 60000000 AND 61000000 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.end_location_id)
                when a.end_location_id >= 61000000 then
                    (SELECT d.name FROM `sovereignty_structures` AS c
                      JOIN universe_stations d ON c.structure_id = d.station_id
                      WHERE c.structure_id = a.end_location_id)
                else (SELECT m.itemName FROM mapDenormalize AS m
                    WHERE m.itemID = a.end_location_id) end
                AS endlocation '))
            ->join('corporation_contracts', 'corporation_contracts.contract_id', '=', 'a.contract_id')
            ->where('corporation_contracts.corporation_id', $corporation_id);

        if ($get)
            return $contracts
                ->orderBy('date_issued', 'desc')
                ->paginate($chunk);

        return $contracts;
    }

    /**
     * @param int $corporation_id
     * @param int $contract_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationContractsItems(int $corporation_id, int $contract_id): Collection
    {

        return ContractItem::leftJoin('invTypes',
            'contract_items.type_id', '=',
            'invTypes.typeID')
            ->join('invGroups',
                'invTypes.groupID', '=',
                'invGroups.groupID')
            ->join('corporation_contracts',  'corporation_contracts.contract_id', '=', 'contract_items.contract_id')
            ->where('corporation_id', $corporation_id)
            ->where('corporation_contracts.contract_id', $contract_id)
            ->take(150)// Limit to 150 for now. Some of these contracts are insanely big.
            ->get();

    }
}
