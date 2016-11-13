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

namespace Seat\Services\Repositories\Character;

use Illuminate\Support\Facades\DB;

/**
 * Class Contracts
 * @package Seat\Services\Repositories\Character
 */
trait Contracts
{

    /**
     * Return Contract Information for a character
     *
     * @param int  $character_id
     * @param bool $get
     * @param int  $chunk
     *
     * @return
     */
    public function getCharacterContracts(
        int $character_id, bool $get = true, int $chunk = 50)
    {

        $contracts = DB::table(DB::raw('character_contracts as a'))
            ->select(DB::raw(
                "
                --
                -- All Columns
                --
                *,

                --
                -- Start Location Lookup
                --
                CASE
                when a.startStationID BETWEEN 66015148 AND 66015151 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.startStationID-6000000)
                when a.startStationID BETWEEN 66000000 AND 66014933 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.startStationID-6000001)
                when a.startStationID BETWEEN 66014934 AND 67999999 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.startStationID-6000000)
                when a.startStationID BETWEEN 60014861 AND 60014928 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.startStationID)
                when a.startStationID BETWEEN 60000000 AND 61000000 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.startStationID)
                when a.startStationID >= 61000000 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.startStationID)
                else (SELECT m.itemName FROM mapDenormalize AS m
                    WHERE m.itemID = a.startStationID) end
                AS startlocation,

                --
                -- End Location Lookup
                --
                CASE
                when a.endstationID BETWEEN 66015148 AND 66015151 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.endStationID-6000000)
                when a.endStationID BETWEEN 66000000 AND 66014933 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.endStationID-6000001)
                when a.endStationID BETWEEN 66014934 AND 67999999 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.endStationID-6000000)
                when a.endStationID BETWEEN 60014861 AND 60014928 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.endStationID)
                when a.endStationID BETWEEN 60000000 AND 61000000 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.endStationID)
                when a.endStationID >= 61000000 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.endStationID)
                else (SELECT m.itemName FROM mapDenormalize AS m
                    WHERE m.itemID = a.endStationID) end
                AS endlocation "))
            ->where('a.characterID', $character_id);

        if ($get)
            return $contracts->orderBy('dateIssued', 'desc')
                ->paginate($chunk);

        return $contracts;

    }

}