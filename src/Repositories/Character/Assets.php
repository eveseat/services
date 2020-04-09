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

namespace Seat\Services\Repositories\Character;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Assets\CharacterAsset;

/**
 * Class Assets.
 * @package Seat\Services\Repositories\Character
 */
trait Assets
{
    /**
     * Return an assets contents. If no parent asset / item ids
     * are specified, then all assets for the corporation is
     * returned.
     *
     * @param \Illuminate\Support\Collection $character_ids
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getCharacterAssetsBuilder(Collection $character_ids): Builder
    {

        $base_assets = CharacterAsset::with('content', 'type')
            ->leftJoin('invTypes', 'character_assets.type_id', '=', 'invTypes.typeID')
            ->select('item_id', 'character_id', 'type_id', 'quantity', 'location_id', 'location_type', 'location_flag', 'is_singleton', 'name', 'typeName', 'volume', 'groupID', DB::raw('CASE
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
                when character_assets.location_id = 2004 THEN "Asset Safety"
                else (SELECT m.itemName FROM mapDenormalize AS m
                    WHERE m.itemID=character_assets.location_id) end
                AS locationName,
                character_assets.location_id AS locID', 'invTypes.typeName AS typeName'))
            ->whereIn('character_assets.character_id', $character_ids->toArray())
            ->whereIn('location_flag', ['Hangar', 'AssetSafety', 'Deliveries'])
            ->whereNotIn('location_id', function ($query) {

                //Do not show assets inside an asset wrapper.
                $query->select('item_id')->where('type_id', '=', 60)->from('character_assets');
            })
            ->orderBy('locationName');

        $in_space_assets = DB::table('character_ships AS cs')
            ->join('character_locations AS cl', 'cs.character_id', '=', 'cl.character_id')
            ->join('invTypes AS it', 'cs.ship_type_id', '=', 'it.typeID')
            ->join('mapDenormalize AS md', 'cl.solar_system_id', '=', 'md.itemID')
            ->whereNull('station_id')
            ->whereNull('structure_id')
            ->whereIn('cs.character_id', $character_ids->toArray())
            ->select('ship_item_id', 'cs.character_id', 'ship_type_id', DB::raw('1'), 'solar_system_id', DB::raw('"solar_system"'), DB::raw('"Hangar"'), DB::raw(0), 'ship_name', 'typeName', 'volume', 'it.groupID', 'itemName', 'solar_system_id')
            ->orderBy('itemName');

        $assets = $base_assets->union($in_space_assets);

        return $assets;
    }
}
