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
use Seat\Eveapi\Models\Corporation\AssetListContents;
use Seat\Eveapi\Models\Corporation\Locations;

/**
 * Class Assets.
 * @package Seat\Services\Repositories\Corporation
 */
trait Assets
{
    /**
     * Return the assets list for a Corporation.
     *
     * @param int $corporation_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationAssets(int $corporation_id): Collection
    {

        return DB::table('corporation_asset_lists as a')
            ->select(DB::raw('
                --
                -- Select All Fields
                --
                *,

                --
                -- Start the Lookation Lookup
                --
                CASE
                when a.locationID BETWEEN 66015148 AND 66015151 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.locationID-6000000)
                when a.locationID BETWEEN 66000000 AND 66014933 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.locationID-6000001)
                when a.locationID BETWEEN 66014934 AND 67999999 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.locationID-6000000)
                when a.locationID BETWEEN 60014861 AND 60014928 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.locationID)
                when a.locationID BETWEEN 60000000 AND 61000000 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID = a.locationID)
                when a.locationID >= 61000000 then
                    (SELECT c.stationName FROM `eve_conquerable_station_lists` AS c
                      WHERE c.stationID = a.locationID)
                else (SELECT m.itemName FROM mapDenormalize AS m
                    WHERE m.itemID = a.locationID) end
                    AS location'))
            ->selectSub(function($query) {

                return $query->from('corporation_asset_list_contents')
                    ->selectRaw('count(*)')
                    ->where('parentAssetItemID',
                        $query->raw('a.itemID'));

            }, 'childContentCount')
            ->join('invTypes',
                'a.typeID', '=',
                'invTypes.typeID')
            ->join('invGroups',
                'invTypes.groupID', '=',
                'invGroups.groupID')
            ->where('a.corporationID', $corporation_id)
            ->get();

    }

    /**
     * Returns a corporation assets grouped by location.
     * Only assets in space will appear here as assets
     * that are in stations dont have 'locations' entries.
     *
     * @param int $corporation_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationAssetByLocation(int $corporation_id): Collection
    {

        return Locations::leftJoin('corporation_asset_lists',
            'corporation_locations.itemID', '=',
            'corporation_asset_lists.itemID')
            ->leftJoin(
                'invTypes',
                'corporation_asset_lists.typeID', '=',
                'invTypes.typeID')
            ->where('corporation_locations.corporationID', $corporation_id)
            ->get()
            ->groupBy('mapID'); // <--- :O That is so sexy <3
    }

    /**
     * Return an assets contents. If no parent asset / item ids
     * are specified, then all assets for the corporation is
     * returned.
     *
     * @param int $corporation_id
     * @param int $parent_asset_id
     * @param int $parent_item_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationAssetContents(int $corporation_id,
                                                int $parent_asset_id = null,
                                                int $parent_item_id = null): Collection
    {

        $contents = AssetListContents::join('invTypes',
            'corporation_asset_list_contents.typeID', '=',
            'invTypes.typeID')
            ->where('corporationID', $corporation_id);

        if (! is_null($parent_asset_id))
            $contents = $contents->where('parentAssetItemID', $parent_asset_id);

        if (! is_null($parent_item_id))
            $contents = $contents->where('parentItemID', $parent_item_id);

        // TODO: Allow the nested lookups to occur.
        $contents = $contents->where('parentItemID', null);

        return $contents->get();
    }
}
