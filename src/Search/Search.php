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

namespace Seat\Services\Search;

use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Assets\CharacterAsset;
use Seat\Eveapi\Models\Character\CharacterSkill;
use Seat\Eveapi\Models\Mail\MailHeader;
use Seat\Services\Repositories\Character\Character;
use Seat\Services\Repositories\Corporation\Corporation;

/**
 * Class Search.
 * @package Seat\Services\Search
 */
trait Search
{
    use Character, Corporation;

    /**
     * @return mixed
     */
    public function doSearchCharacters()
    {

        return $this->getAllCharactersWithAffiliations(false);
    }

    /**
     * @return mixed
     */
    public function doSearchCorporations()
    {

        return $this->getAllCorporationsWithAffiliationsAndFilters(false);
    }

    /**
     * @return mixed
     */
    public function doSearchCharacterMail()
    {

        // Get the User for permissions and affiliation
        // checks
        $user = auth()->user();

        $messages = MailHeader::with('body', 'recipients', 'sender');

        // If the user is a super user, return all
        if (! $user->hasSuperUser()) {

            $messages = $messages->where(function ($query) use ($user) {

                // If the user has any affiliations and can
                // list those characters, add them
                // also include all attached characters

                $map = $user->getAffiliationMap();
                $character_maps = [];

                foreach ($map['char'] as $character_id => $permissions) {
                    if (in_array('character.*', $permissions))
                        $character_maps[] = $character_id;
                    if (in_array('character.mail', $permissions))
                        $character_maps[] = $character_id;
                }

                $query = $query->orWhereIn('character_id', $character_maps)
                    ->orWhereIn('from', $character_maps);

                $query = $query->orWhereHas('recipients', function ($sub_query) use ($character_maps) {
                    $sub_query->whereIn('recipient_id', $character_maps);
                });
            });
        }

        return $messages->orderBy('timestamp', 'desc')
            ->take(150);

    }

    /**
     * @return mixed
     */
    public function doSearchCharacterAssets()
    {

        // Get the user.
        $user = auth()->user();

        // Start the query with all the joins needed.
        $assets = CharacterAsset::select(DB::raw('
                character_infos.name AS character_name,
                invTypes.typeName,
                invGroups.groupName,
                character_assets.*, CASE
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
                AS location,
                character_assets.location_id AS locID'))
            ->join('character_infos',
                'character_assets.character_id', '=',
                'character_infos.character_id')
            ->join('invTypes',
                'character_assets.type_id', '=',
                'invTypes.typeID')
            ->join('invGroups',
                'invTypes.groupID', '=',
                'invGroups.groupID');

        // If the user is not a superuser, filter the results.
        if (! $user->hasSuperUser()) {

            $assets = $assets->where(function ($query) use ($user) {

                // If the user has any affiliations and can
                // list those characters, add them
                if ($user->has('character.assets', false))
                    $query = $query->whereIn('character_assets.character_id',
                        array_keys($user->getAffiliationMap()['char']));

                // Add any characters from owner API keys
                $user_character_ids = auth()->user()->group->users->pluck('id')->toArray();

                $query->orWhere('character_assets.character_id', $user_character_ids);
            });
        }

        return $assets;

    }

    /**
     * @return mixed
     */
    public function doSearchCharacterSkills()
    {

        // Get the user
        $user = auth()->user();

        // Start the skills query
        $skills = CharacterSkill::join(
            'invTypes',
            'character_skills.skill_id', '=',
            'invTypes.typeID')
            ->join(
                'invGroups',
                'invTypes.groupID', '=',
                'invGroups.groupID')
            ->join(
                'character_infos',
                'character_infos.character_id', '=',
                'character_skills.character_id'
            );

        // If the user is not a superuser, filter the results.
        if (! $user->hasSuperUser()) {

            $skills = $skills->where(function ($query) use ($user) {

                // If the user has any affiliations and can
                // list those characters, add them
                if ($user->has('character.skills', false))
                    $query = $query->whereIn('character_skills.character_id',
                        array_keys($user->getAffiliationMap()['char']));

                // Add any characters from owner API keys
                $user_character_ids = auth()->user()->group->users->pluck('id')->toArray();

                $query->orWhereIn('character_skills.character_id', $user_character_ids);
            });
        }

        return $skills;
    }
}
