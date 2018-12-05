<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanet;

/**
 * Class Pi.
 * @package Seat\Services\Repositories\Character
 */
trait Pi
{
    /**
     * Return the Planetary Colonies for a character.
     *
     * @param int $character_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCharacterPlanetaryColonies(int $character_id): Collection
    {

        return CharacterPlanet::where('character_id', $character_id)
            ->join('mapDenormalize as system', 'system.itemID', '=', 'solar_system_id')
            ->join('mapDenormalize as planet', 'planet.itemID', '=', 'planet_id')
            ->select('character_planets.*', 'system.itemName', 'planet.typeID')
            ->get();
    }

    /**
     * @param int $character_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCharacterPlanetaryExtractors(int $character_id): Collection
    {

        $extractors = CharacterPlanet::where('character_planets.character_id', $character_id)
            ->join('character_planet_extractors', function ($join) {
                $join->on('character_planet_extractors.planet_id', '=', 'character_planets.planet_id')
                    ->on('character_planet_extractors.character_id', '=', 'character_planets.character_id');
            })
            ->join('character_planet_pins', function ($join) {
                $join->on('character_planet_pins.pin_id', '=', 'character_planet_extractors.pin_id')
                    ->on('character_planet_pins.planet_id', '=', 'character_planet_extractors.planet_id')
                    ->on('character_planet_pins.character_id', '=', 'character_planet_extractors.character_id');
            })
            ->join('mapDenormalize as planet',
                'planet.itemID', '=', 'character_planets.planet_id')
            ->join('mapDenormalize as system',
                'system.itemID', '=', 'character_planets.solar_system_id')
            ->join('invTypes',
                'invTypes.typeID', '=', 'character_planet_extractors.product_type_id')
            ->select(
                'character_planet_extractors.product_type_id', // Extractor Product Name f.e. Aqueous Liquids
                'planet.celestialIndex', // arabic planet index
                'planet.itemName', // Planet Name
                'planet.typeID', // Planet Type ID
                'system.itemName', // System Name
                'character_planet_pins.install_time', //UTC Time of start
                'character_planet_pins.expiry_time', // UTC Time of expiry
                'character_planets.planet_type', // barren, temperate, gas
                'invTypes.typeName' // Extractor Product Name
            )
            ->get();

        return $extractors->map(function ($item) {

            $item->celestialIndex = number_roman($item->celestialIndex);

            return $item;
        });
    }
}
