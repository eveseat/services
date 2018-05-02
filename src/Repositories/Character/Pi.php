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
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanetExtractor;

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

    public function getCharacterPlanetaryExtractors(int $character_id): Collection
    {

        $extractors = CharacterPlanetExtractor::where('character_planet_extractors.character_id', $character_id)
            ->join('character_planet_pins as pin', 'pin.pin_id', '=', 'character_planet_extractors.pin_id')
            ->join('character_planets as planets', 'planets.planet_id', '=', 'character_planet_extractors.planet_id')
            ->join('mapDenormalize as system', 'system.itemID', '=', 'planets.solar_system_id')
            ->join('mapDenormalize as planet', 'planet.itemID', '=', 'character_planet_extractors.planet_id')
            ->join('invTypes as inventory', 'inventory.typeID', '=', 'character_planet_extractors.product_type_id')
            ->select(
                'character_planet_extractors.product_type_id', // Extractor Product Name f.e. Aqueous Liquids
                'planet.celestialIndex', // arabic planet index
                'system.itemName', // System Name J123456
                'planet.typeID', // Planet Type ID
                'pin.install_time', //UTC Time of start
                'pin.expiry_time', // UTC Time of expiry
                'planets.planet_type', // barren, temperate, gas
                'inventory.typeName' // Extractor Product Name
            )
            ->get();

        $extractors = $extractors->map(function ($item) {

            $item->celestialIndex = numberToRomanRepresentation($item->celestialIndex);

            return $item;
        });

        $clean_extractors = collect([]);

        foreach ($extractors as $extractor) {

            $clean_extractors->push([
                'product_type_id' => $extractor->product_type_id,
                'celestialIndex'  => $extractor->celestialIndex,
                'itemName'        => $extractor->itemName,
                'typeID'          => $extractor->typeID,
                'install_time'    => $extractor->install_time,
                'expiry_time'     => $extractor->expiry_time,
                'planet_type'     => $extractor->planet_type,
                'typeName'        => $extractor->typeName,
            ]);
        }

        return $clean_extractors;
    }
}
