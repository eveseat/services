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

use Seat\Eveapi\Models\Corporation\Starbase;

/**
 * Class Starbases.
 * @package Seat\Services\Repositories\Corporation
 */
trait Starbases
{
    /**
     * Return a list of starbases for a Corporation. If
     * a starbaseID is provided, then only data for that
     * starbase is returned.
     *
     * @param int $corporation_id
     * @param int $starbase_id
     *
     * @return
     */
    public function getCorporationStarbases(int $corporation_id, int $starbase_id = null)
    {

        $starbase = Starbase::select(
            'corporation_starbases.itemID',
            'corporation_starbases.moonID',
            'corporation_starbases.state',
            'corporation_starbases.stateTimeStamp',
            'corporation_starbases.onlineTimeStamp',
            'corporation_starbases.onlineTimeStamp',
            'corporation_starbase_details.useStandingsFrom',
            'corporation_starbase_details.onAggression',
            'corporation_starbase_details.onCorporationWar',
            'corporation_starbase_details.allowCorporationMembers',
            'corporation_starbase_details.allowAllianceMembers',
            'corporation_starbase_details.fuelBlocks',
            'corporation_starbase_details.strontium',
            'corporation_starbase_details.starbaseCharter',
            'invTypes.typeID as starbaseTypeID',
            'invTypes.typeName as starbaseTypeName',
            'mapDenormalize.itemName as mapName',
            'mapDenormalize.security as mapSecurity',
            'invNames.itemName as moonName',
            'map_sovereignties.solarSystemName',
            'corporation_starbase_details.updated_at')
            ->selectSub(function ($query) {

                return $query->from('invControlTowerResources')
                    ->select('quantity')
                    // from invControlTowerResources, we can see
                    // that fuelBlock resourceTypeID's are around
                    // 4051 -> 4312. For that reason, we can just
                    // approximate the range that fuel blocks will
                    // fall in.
                    ->whereBetween('resourceTypeID', [4000, 5000])
                    ->where('purpose', 1)
                    ->where('controlTowerTypeID',
                        $query->raw('corporation_starbases.typeID'));

            }, 'baseFuelUsage')
            ->selectSub(function ($query) {

                return $query->from('invControlTowerResources')
                    ->select('quantity')
                    ->where('resourceTypeID', '=', 16275)
                    ->where('purpose', 4)
                    ->where('controlTowerTypeID',
                        $query->raw('corporation_starbases.typeID'));

            }, 'baseStrontUsage')
            ->selectSub(function ($query) {

                return $query->from('invTypes')
                    ->select('capacity')
                    ->where('groupID', 365)
                    ->where('typeID',
                        $query->raw('corporation_starbases.typeID'));

            }, 'fuelBaySize')
            ->selectSub(function ($query) {

                return $query->from('dgmTypeAttributes')
                    ->select('valueFloat')
                    ->where('dgmTypeAttributes.attributeID', 1233)
                    ->where('typeID',
                        $query->raw('corporation_starbases.typeID'));

            }, 'strontBaySize')
            ->selectSub(function ($query) {

                return $query->from('corporation_locations')
                    ->select('itemName')
                    ->where('itemID',
                        $query->raw('corporation_starbases.itemID'));

            }, 'starbaseName')
            ->selectSub(function ($query) use ($corporation_id) {

                return $query->from('map_sovereignties')
                    ->selectRaw(
                        'IF(solarSystemID, TRUE, FALSE) inSovSystem')
                    ->where('factionID', 0)
                    ->whereIn('allianceID', function ($subquery) use ($corporation_id) {

                        $subquery->from('corporation_sheets')
                            ->select('allianceID')
                            ->where('corporationID', $corporation_id);
                    })
                    ->where('solarSystemID',
                        $query->raw('corporation_starbases.locationID'));

            }, 'inSovSystem')
            ->selectSub(function ($query) {

                return $query->from('dgmTypeAttributes')
                    ->select('valueFloat')
                    // From dgmAttributeTypes,
                    // 757 = controlTowerSiloCapacityBonus
                    ->where('attributeID', 757)
                    ->where('typeID',
                        $query->raw('corporation_starbases.typeID'));

            }, 'siloCapacityBonus')
            ->join(
                'corporation_starbase_details',
                'corporation_starbases.itemID', '=',
                'corporation_starbase_details.itemID')
            ->join(
                'mapDenormalize',
                'corporation_starbases.locationID', '=',
                'mapDenormalize.itemID')
            ->join(
                'invNames',
                'corporation_starbases.moonID', '=',
                'invNames.itemID')
            ->join(
                'invTypes',
                'corporation_starbases.typeID', '=',
                'invTypes.typeID')
            ->leftJoin(
                'map_sovereignties',
                'corporation_starbases.locationID', '=',
                'map_sovereignties.solarSystemID')
            ->where('corporation_starbases.corporationID', $corporation_id)
            ->orderBy('invNames.itemName', 'asc');

        // If we did get a specific starbase_id to query then
        // just return what we have now for all of the starbases.
        if (is_null($starbase_id))
            return $starbase->get();

        // ... otherwise, filter down to the specific requested starbase
        // and grab some extra information about the silos etc at this tower.
        $starbase = $starbase->where('corporation_starbases.itemID', $starbase_id)
            ->first();

        // When calculating *actual* silo capacity, we need
        // to keep in mind that certain towers have bonusses
        // to silo cargo capacity, like amarr & gallente
        // towers do now. To calculate this, we will get the
        // siloCapacityBonus value from the starbase and add the
        // % capacity to actual modules that benefit from
        // the bonusses.
        $cargo_types_with_bonus = [14343, 17982]; // Silo, Coupling Array
        $assetlist_locations = $this->getCorporationAssetByLocation($corporation_id);
        $module_contents = $this->getCorporationAssetContents($corporation_id);

        // Check if we know of *any* assets at the moon where this tower is.
        if ($assetlist_locations->has($starbase->moonID)) {

            // Set the 'modules' key for the starbase
            $starbase->modules = $assetlist_locations->get($starbase->moonID)
                ->map(function ($asset) use (
                    $starbase,
                    $cargo_types_with_bonus,
                    $module_contents
                ) {

                    // Return a collection with module related info.
                    return [
                        'detail'           => $asset,
                        'used_volume'      => $module_contents->where(
                            'parentAssetItemID', $asset->itemID)->sum(function ($_) {

                            return $_->quantity * $_->volume;
                        }),
                        'available_volume' => in_array($asset->typeID, $cargo_types_with_bonus) ?
                            $asset->capacity * (1 + $starbase->siloCapacityBonus / 100) :
                            $asset->capacity,
                        'total_items'      => $module_contents->where(
                            'parentAssetItemID', $asset->itemID)->sum('quantity'),
                    ];
                });
        }

        return $starbase;
    }
}
