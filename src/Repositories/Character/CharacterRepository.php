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
use Seat\Eveapi\Models\Account\ApiKeyInfoCharacters;
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
                    $query = $query->whereIn('characterID',
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

        $info = ApiKeyInfoCharacters::join('eve_character_infos',
            'eve_character_infos.characterID', '=',
            'account_api_key_info_characters.characterID')
            ->where('eve_character_infos.characterID', $character_id)
            ->first();

        return $info;

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
