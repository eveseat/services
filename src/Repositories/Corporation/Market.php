<?php
/*
This file is part of SeAT

Copyright (C) 2015, 2016  Leon Jacobs

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace Seat\Services\Repositories\Corporation;

use Illuminate\Support\Facades\DB;

/**
 * Class Market
 * @package Seat\Services\Repositories\Corporation
 */
trait Market
{

    /**
     * Return the Market Orders for a Corporation
     *
     * @param int  $corporation_id
     * @param bool $get
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationMarketOrders(int $corporation_id, bool $get = true)
    {

        $orders = DB::table(DB::raw('corporation_market_orders as a'))
            ->select(DB::raw(
                "
                --
                -- Select All
                --
                *,

                --
                -- Start stationName Lookup
                --
                CASE
                when a.stationID BETWEEN 66015148 AND 66015151 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.stationID-6000000)
                when a.stationID BETWEEN 66000000 AND 66014933 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.stationID-6000001)
                when a.stationID BETWEEN 66014934 AND 67999999 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.stationID-6000000)
                when a.stationID BETWEEN 60014861 AND 60014928 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.stationID)
                when a.stationID BETWEEN 60000000 AND 61000000 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.stationID)
                when a.stationID >= 61000000 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.stationID)
                else (SELECT m.itemName FROM mapDenormalize AS m
                    WHERE m.itemID = a.stationID) end
                    AS stationName"))
            ->join(
                'invTypes',
                'a.typeID', '=',
                'invTypes.typeID')
            ->join(
                'invGroups',
                'invTypes.groupID', '=',
                'invGroups.groupID')
            ->where('a.corporationID', $corporation_id);

        if ($get)
            return $orders->orderBy('a.issued', 'desc')
                ->get();

        return $orders;

    }

}
