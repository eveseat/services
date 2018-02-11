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

namespace Seat\Services\Repositories\Character;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Assets\CharacterAsset;
use Seat\Eveapi\Models\Character\AssetListContents;

/**
 * Class Assets.
 * @package Seat\Services\Repositories\Character
 */
trait Assets
{
    /**
     * Return the assets that belong to a Character.
     *
     * @param int $character_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCharacterAssets(int $character_id): Collection
    {

        //return DB::table('character_assets as a')
        return CharacterAsset::with('content', 'type')
            ->select(DB::raw('
                *, CASE
                when character_assets.location_id BETWEEN 66015148 AND 66015151 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID=character_assets.location_id-6000000)
                when character_assets.location_id BETWEEN 66000000 AND 66014933 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID=character_assets.location_id-6000001)
                when character_assets.location_id BETWEEN 66014934 AND 67999999 then
                    (SELECT d.name FROM `sovereignty_structures` AS c
                      JOIN universe_stations d ON c.structure_id = d.station_id
                      WHERE c.structure_id=character_assets.location_id-6000000)
                when character_assets.location_id BETWEEN 60014861 AND 60014928 then
                    (SELECT d.name FROM `sovereignty_structures` AS c
                      JOIN universe_stations d ON c.structure_id = d.station_id
                      WHERE c.structure_id=character_assets.location_id)
                when character_assets.location_id BETWEEN 60000000 AND 61000000 then
                    (SELECT s.stationName FROM staStations AS s
                      WHERE s.stationID=character_assets.location_id)
                when character_assets.location_id BETWEEN 61000000 AND 61001146 then
                    (SELECT d.name FROM `sovereignty_structures` AS c
                      JOIN universe_stations d ON c.structure_id = d.station_id
                      WHERE c.structure_id=character_assets.location_id)
                when character_assets.location_id > 61001146 then
                    (SELECT name FROM `universe_structures` AS c
                     WHERE c.structure_id = character_assets.location_id)
                else (SELECT m.itemName FROM mapDenormalize AS m
                    WHERE m.itemID=character_assets.location_id) end
                    AS locationName,character_assets.location_id AS locID'))
            ->where('character_assets.character_id', $character_id)
            ->get();
    }

    /**
     * Return an assets contents. If no parent asset / item ids
     * are specified, then all assets for the corporation is
     * returned.
     *
     * @param int $character_id
     * @param int $parent_asset_id
     * @param int $parent_item_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCharacterAssetContents(int $character_id,
                                                int $parent_asset_id = null,
                                                int $parent_item_id = null): Collection
    {

        $contents = AssetListContents::join('invTypes',
            'character_assets.type_id', '=',
            'invTypes.typeID')
            ->where('character_id', $character_id);

        if (! is_null($parent_asset_id))
            $contents = $contents->where('parentAssetItemID', $parent_asset_id);

        if (! is_null($parent_item_id))
            $contents = $contents->where('parentItemID', $parent_item_id);

        // TODO: Allow the nested lookups to occur.
        $contents = $contents->where('parentItemID', null);

        return $contents->get();
    }
}
