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

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait Assets
{

    /**
     * Return the assets that belong to a Character
     *
     * @param int $character_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCharacterAssets(int $character_id): Collection
    {

        return DB::table('character_asset_lists as a')
            ->select(DB::raw("
                *, CASE
                when a.locationID BETWEEN 66015148 AND 66015151 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID=a.locationID-6000000)
                when a.locationID BETWEEN 66000000 AND 66014933 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID=a.locationID-6000001)
                when a.locationID BETWEEN 66014934 AND 67999999 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID=a.locationID-6000000)
                when a.locationID BETWEEN 60014861 AND 60014928 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID=a.locationID)
                when a.locationID BETWEEN 60000000 AND 61000000 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID=a.locationID)
                when a.locationID>=61000000 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID=a.locationID)
                else (SELECT m.itemName FROM mapDenormalize AS m
                    WHERE m.itemID=a.locationID) end
                    AS location,a.locationId AS locID"))
            ->join('invTypes',
                'a.typeID', '=',
                'invTypes.typeID')
            ->join('invGroups',
                'invTypes.groupID', '=',
                'invGroups.groupID')
            ->where('a.characterID', $character_id)
            ->get();
    }

    /**
     * Return the nested assets that belong to a Character
     *
     * @param int $character_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCharacterAssetContents(int $character_id): Collection
    {

        return DB::table(DB::raw('character_asset_list_contents as a'))
            ->select(DB::raw('*'), DB::raw('SUM(a.quantity) as sumquantity'))
            ->leftJoin('invTypes', 'a.typeID', '=', 'invTypes.typeID')
            ->leftJoin('invGroups', 'invTypes.groupID', '=', 'invGroups.groupID')
            ->where('a.characterID', $character_id)
            ->groupBy(DB::raw('a.itemID, a.typeID'))
            ->get();
    }

}
