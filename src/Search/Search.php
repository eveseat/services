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

namespace Seat\Services\Search;

use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Character\CharacterSheetSkills;
use Seat\Eveapi\Models\Character\MailMessage;
use Seat\Eveapi\Models\Eve\ApiKey;
use Seat\Services\Repositories\Character\Character;
use Seat\Services\Repositories\Corporation\Corporation;

/**
 * Class Search
 * @package Seat\Services\Search
 */
trait Search
{

    use Character, Corporation;

    /**
     * @param string $query
     *
     * @return \Illuminate\Database\Eloquent\Collection|static|static[]
     */
    public function doSearchCharacters(string $query)
    {

        // Get the data
        $characters = $this->getAllCharactersWithAffiliations();

        // Filter the data
        $characters = $characters->filter(function ($item) use ($query) {

            return str_contains(
                strtoupper($item->characterName), strtoupper($query));
        });

        return $characters;
    }

    /**
     * @param string $query
     *
     * @return mixed
     */
    public function doSearchCorporations(string $query)
    {

        $corporations = $this->getAllCorporationsWithAffiliationsAndFilters();

        $corporations = $corporations->filter(function ($item) use ($query) {

            return str_contains(
                strtoupper($item->corporationName), strtoupper($query));
        });

        return $corporations;

    }

    /**
     * @param string $filter
     *
     * @return mixed
     */
    public function doSearchCharacterMail(string $filter)
    {

        // Get the User for permissions and affiliation
        // checks
        $user = auth()->user();

        $messages = MailMessage::join('character_mail_message_bodies',
            'character_mail_messages.messageID', '=',
            'character_mail_message_bodies.messageID')
            ->join(
                'account_api_key_info_characters',
                'character_mail_messages.characterID', '=',
                'account_api_key_info_characters.characterID')
            ->join(
                'eve_api_keys',
                'eve_api_keys.key_id', '=',
                'account_api_key_info_characters.keyID');

        // If the user is a super user, return all
        if (!$user->hasSuperUser()) {

            $messages = $messages->where(function ($query) use ($user) {

                // If the user has any affiliations and can
                // list those characters, add them
                if ($user->has('character.mail', false))
                    $query = $query->whereIn('account_api_key_info_characters.characterID',
                        array_keys($user->getAffiliationMap()['char']));

                // Add any characters from owner API keys
                $query->orWhere('eve_api_keys.user_id', $user->id);
            });
        }

        // Filter by the query string
        $messages = $messages->where(function ($query) use ($filter) {

            $query->where('character_mail_messages.title', 'like', '%' . $filter . '%')
                ->orWhere('character_mail_message_bodies.body', 'like', '%' . $filter . '%');
        });

        return $messages->orderBy('character_mail_messages.sentDate', 'desc')
            ->take(150)// Have to limit a little.
            ->get();

    }

    /**
     * @return mixed
     */
    public function doSearchCharacterAssets()
    {

        // Get the user.
        $user = auth()->user();

        // Start the query with all the joins needed.
        $assets = DB::table('character_asset_lists as a')
            ->select(DB::raw("
                *,
                CASE
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
            ->join(
                'account_api_key_info_characters',
                'a.characterID', '=',
                'account_api_key_info_characters.characterID')
            ->join(
                'eve_api_keys',
                'eve_api_keys.key_id', '=',
                'account_api_key_info_characters.keyID');

        // If the user is not a superuser, filter the results.
        if (!$user->hasSuperUser()) {

            $assets = $assets->where(function ($query) use ($user) {

                // If the user has any affiliations and can
                // list those characters, add them
                if ($user->has('character.assets', false))
                    $query = $query->whereIn('account_api_key_info_characters.characterID',
                        array_keys($user->getAffiliationMap()['char']));

                // Add any characters from owner API keys
                $query->orWhere('eve_api_keys.user_id', $user->id);
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
        $skills = CharacterSheetSkills::join(
            'invTypes',
            'character_character_sheet_skills.typeID', '=',
            'invTypes.typeID')
            ->join(
                'invGroups',
                'invTypes.groupID', '=',
                'invGroups.groupID')
            ->join(
                'account_api_key_info_characters',
                'character_character_sheet_skills.characterID', '=',
                'account_api_key_info_characters.characterID')
            ->join(
                'eve_api_keys',
                'eve_api_keys.key_id', '=',
                'account_api_key_info_characters.keyID');

        // If the user is not a superuser, filter the results.
        if (!$user->hasSuperUser()) {

            $skills = $skills->where(function ($query) use ($user) {

                // If the user has any affiliations and can
                // list those characters, add them
                if ($user->has('character.skills', false))
                    $query = $query->whereIn('account_api_key_info_characters.characterID',
                        array_keys($user->getAffiliationMap()['char']));

                // Add any characters from owner API keys
                $query->orWhere('eve_api_keys.user_id', $user->id);
            });
        }

        return $skills;
    }

    /**
     * @param string $filter
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function doSearchApiKey(string $filter)
    {

        $keys = ApiKey::with('info');

        $keys->where(function ($query) use ($filter) {

            $query->where('key_id', 'like', '%' . $filter . '%')
                ->orWhere('enabled', 'like', '%' . $filter . '%')
                ->orWhereHas('info', function ($sub_filter) use ($filter) {

                    $sub_filter->where('type', 'like', '%' . $filter . '%')
                        ->orWhere('expires', 'like', '%' . $filter . '%');

                });
        });

        if (!auth()->user()->has('apikey.list', false))
            $keys = $keys
                ->where('user_id', auth()->user()->id);

        return $keys->get();
    }

}
