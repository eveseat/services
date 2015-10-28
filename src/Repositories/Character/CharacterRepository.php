<?php
/*
This file is part of SeAT

Copyright (C) 2015  Leon Jacobs

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

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Account\AccountStatus;
use Seat\Eveapi\Models\Account\ApiKeyInfoCharacters;
use Seat\Eveapi\Models\Character\CharacterSheet;
use Seat\Eveapi\Models\Character\CharacterSheetImplants;
use Seat\Eveapi\Models\Character\CharacterSheetSkills;
use Seat\Eveapi\Models\Character\SkillInTraining;
use Seat\Eveapi\Models\Character\SkillQueue;
use Seat\Eveapi\Models\Eve\CharacterInfoEmploymentHistory;
use Seat\Services\Helpers\Filterable;

/**
 * Class CharacterRepository
 * @package Seat\Services\Repositories
 */
trait CharacterRepository
{

    use Filterable;

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllCharacters()
    {

        return ApiKeyInfoCharacters::all();
    }

    /**
     * Query the databse for characters, keeping filters,
     * permissions and affiliations in mind
     *
     * @param \Illuminate\Http\Request|null $request
     *
     * @return \Illuminate\Database\Eloquent\Builder|mixed|static
     */
    public function getAllCharactersWithAffiliationsAndFilters(Request $request = null)
    {

        // Get the User for permissions and affiliation
        // checks
        $user = auth()->user();

        $characters = ApiKeyInfoCharacters::with('key', 'key.owner', 'key_info')
            ->join(
                'eve_api_keys',
                'eve_api_keys.key_id', '=',
                'account_api_key_info_characters.keyID')
            ->join(
                'eve_character_infos',
                'eve_character_infos.characterID', '=',
                'account_api_key_info_characters.characterID');

        // Apply any received filters
        if ($request && $request->filter)
            $characters = $this->where_filter($characters, $request->filter);

        // If the user us a super user, return all
        if (!$user->hasSuperUser()) {

            $characters = $characters->where(function ($query) use ($user, $request) {

                // If the user has any affiliations and can
                // list those characters, add them
                if ($user->has('character.list', false))
                    $query = $query->whereIn('account_api_key_info_characters.characterID',
                        array_keys($user->getAffiliationMap()['char']));

                // Add any characters from owner API keys
                $query->orWhere('eve_api_keys.user_id', $user->id);
            });

        }

        return $characters->orderBy('account_api_key_info_characters.characterName')
            ->get();
    }

    /**
     * Get a list of corporations the current
     * authenticated user has access to
     *
     * @return mixed
     */
    public function getCharacterCorporations()
    {

        $user = auth()->user();

        $corporations = ApiKeyInfoCharacters::join(
            'eve_api_keys',
            'eve_api_keys.key_id', '=',
            'account_api_key_info_characters.keyID')
            ->distinct();

        // If the user us a super user, return all
        if (!$user->hasSuperUser()) {

            $corporations = $corporations->orWhere(function ($query) use ($user) {

                // If the user has any affiliations and can
                // list those characters, add them
                if ($user->has('character.list', false))
                    $query = $query->whereIn('characterID',
                        array_keys($user->getAffiliationMap()['char']));

                // Add any characters from owner API keys
                $query->orWhere('eve_api_keys.user_id', $user->id);
            });
        }

        return $corporations->orderBy('corporationName')
            ->lists('corporationName');
    }

    /**
     * Get Information about a specific Character
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterInformation($character_id)
    {

        return ApiKeyInfoCharacters::join('eve_character_infos',
            'eve_character_infos.characterID', '=',
            'account_api_key_info_characters.characterID')
            ->where('eve_character_infos.characterID', $character_id)
            ->first();

    }

    /**
     * Return the character sheet for a character
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterSheet($character_id)
    {

        return CharacterSheet::find($character_id);
    }

    /**
     * Return the skills detail for a specific Character
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterSkillsInformation($character_id)
    {

        return CharacterSheetSkills::join('invTypes',
            'character_character_sheet_skills.typeID', '=',
            'invTypes.typeID')
            ->join('invGroups', 'invTypes.groupID', '=', 'invGroups.groupID')
            ->where('character_character_sheet_skills.characterID', $character_id)
            ->orderBy('invTypes.typeName')
            ->get();

    }

    /**
     * Return information about the current skill in training
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterSkillInTraining($character_id)
    {

        return SkillInTraining::join('invTypes',
            'character_skill_in_trainings.trainingTypeID', '=',
            'invTypes.typeID')
            ->where('characterID', $character_id)
            ->first();
    }

    /**
     * Return a characters current Skill Queue
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterSkilQueue($character_id)
    {

        return SkillQueue::join('invTypes',
            'character_skill_queues.typeID', '=',
            'invTypes.typeID')
            ->where('characterID', $character_id)
            ->orderBy('queuePosition')
            ->get();

    }

    /**
     * Return the employment history for a character
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterEmploymentHistory($character_id)
    {

        return CharacterInfoEmploymentHistory::where('characterID', $character_id)
            ->orderBy('startDate', 'desc')
            ->get();

    }

    /**
     * Return the implants a certain character currently has
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterImplants($character_id)
    {

        return CharacterSheetImplants::where('characterID', $character_id)
            ->get();
    }

    /**
     * Get jump clones and jump clone locations for a
     * character
     *
     * @param $character_id
     *
     * @return mixed
     */
    public function getCharacterJumpClones($character_id)
    {

        return DB::table(DB::raw(
            'character_character_sheet_jump_clones as a'))
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
            ->join('invTypes', 'a.typeID', '=', 'invTypes.typeID')
            ->where('a.characterID', $character_id)
            ->get();
    }

    /**
     * Return the Account Status information for a specific
     * character
     *
     * @param $character_id
     */
    public function getCharacterAccountInfo($character_id)
    {

        $key_id = ApiKeyInfoCharacters::where('characterID', $character_id)
            ->value('keyID');

        if ($key_id)
            return AccountStatus::find($key_id);

        return;

    }

    /**
     * Returns the characters on a API Key
     *
     * @param $key_id
     *
     * @return mixed
     */
    public function getCharactersOnApiKey($key_id)
    {

        return ApiKeyInfoCharacters::where('keyID', $key_id)
            ->get();

    }

}
