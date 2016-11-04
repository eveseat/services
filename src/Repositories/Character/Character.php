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

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Seat\Eveapi\Models\Account\AccountStatus;
use Seat\Eveapi\Models\Account\ApiKeyInfoCharacters;
use Seat\Services\Helpers\Filterable;

/**
 * Class Character
 * @package Seat\Services\Repositories
 */
trait Character
{

    use Filterable;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getAllCharacters() : Collection
    {

        return ApiKeyInfoCharacters::all();
    }

    /**
     * Query the database for characters, keeping filters,
     * permissions and affiliations in mind
     *
     * @param \Illuminate\Http\Request|null $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllCharactersWithAffiliationsAndFilters(
        Request $request = null) : Collection
    {

        // Get the User for permissions and affiliation
        // checks
        $user = auth()->user();

        $characters = ApiKeyInfoCharacters::with('key', 'key.owner', 'key_info')
            ->join(
                'account_api_key_infos',
                'account_api_key_infos.keyID', '=',
                'account_api_key_info_characters.keyID')
            ->join(
                'eve_api_keys',
                'eve_api_keys.key_id', '=',
                'account_api_key_info_characters.keyID')
            ->join(
                'eve_character_infos',
                'eve_character_infos.characterID', '=',
                'account_api_key_info_characters.characterID')
            ->where('account_api_key_infos.type', '!=', 'Corporation');

        // Apply any received filters
        if ($request && $request->filter)
            $characters = $this->where_filter(
                $characters, $request->filter, config('web.filter.rules.characters'));

        // If the user is a super user, return all
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

        return $characters
            ->groupBy('account_api_key_info_characters.characterID')
            ->orderBy('account_api_key_info_characters.characterName')
            ->get();
    }

    /**
     * Get a list of alliances the current
     * authenticated user has access to
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCharacterAlliances() : Collection
    {

        $user = auth()->user();

        $corporations = ApiKeyInfoCharacters::join(
            'eve_api_keys',
            'eve_api_keys.key_id', '=',
            'account_api_key_info_characters.keyID')
            ->join(
                'eve_character_infos',
                'eve_character_infos.characterID', '=',
                'account_api_key_info_characters.characterID')
            ->distinct();

        // If the user us a super user, return all
        if (!$user->hasSuperUser()) {

            $corporations = $corporations->orWhere(function ($query) use ($user) {

                // If the user has any affiliations and can
                // list those characters, add them
                if ($user->has('character.list', false))
                    $query = $query->whereIn('account_api_key_info_characters.characterID',
                        array_keys($user->getAffiliationMap()['char']));

                // Add any characters from owner API keys
                $query->orWhere('eve_api_keys.user_id', $user->id);
            });
        }

        return $corporations->orderBy('corporationName')
            ->pluck('eve_character_infos.alliance')
            ->filter(function ($item) {

                // Filter out the null alliance name
                return !is_null($item);
            });

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
            ->pluck('corporationName');
    }

    /**
     * Return the Account Status information for a specific
     * character
     *
     * @param $character_id
     */
    public function getCharacterAccountInfo($character_id)
    {

        $key_info = ApiKeyInfoCharacters::where('characterID', $character_id)
            ->leftJoin(
                'account_api_key_infos',
                'account_api_key_infos.keyID', '=',
                'account_api_key_info_characters.keyID')
            ->where('account_api_key_infos.type', '!=', 'Corporation')
            ->first();

        if ($key_info)
            return AccountStatus::find($key_info->keyID);

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
