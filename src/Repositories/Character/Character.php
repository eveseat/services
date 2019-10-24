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

use Illuminate\Support\Collection;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Corporation\CorporationInfo;

/**
 * Class Character.
 * @package Seat\Services\Repositories
 */
trait Character
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function getAllCharacters(): Collection
    {

        return CharacterInfo::all();
    }

    /**
     * Query the database for characters, keeping filters,
     * permissions and affiliations in mind.
     *
     * @param bool $get
     *
     * @return mixed
     */
    public function getAllCharactersWithAffiliations(bool $get = true)
    {

        // TODO : rewrite the method according to the new ACL mechanic

        // Get the User for permissions and affiliation
        // checks
        $user = auth()->user();

        // Start the character information query
        $characters = new CharacterInfo;

        // If the user is not a super user, return only the characters the
        // user has access to.
        if (! $user->hasSuperUser()) {

            $affiliations_map = $user->getAffiliationMap();

            $granted_characters = array_keys($affiliations_map['char']);

            $characters = $characters->whereIn('character_id', $granted_characters);
        }

        if ($get)
            return $characters
                ->orderBy('name')
                ->get();

        return $characters->getQuery();
    }

    /**
     * Get a list of alliances the current
     * authenticated user has access to.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCharacterAlliances(): Collection
    {

        $user = auth()->user();

        $alliances = CharacterInfo::join(
            'alliance_members',
            'alliance_members.corporation_id',
            'character_infos.corporation_id')
            ->join(
                'alliances',
                'alliances.alliance_id',
                'alliance_members.alliance_id')
            ->distinct();

        // If the user us a super user, return all
        if (! $user->hasSuperUser()) {

            $alliances = $alliances->orWhere(function ($query) use ($user) {

                // If the user has any affiliations and can
                // list those characters, add them
                if ($user->has('character.list', false))
                    $query = $query->whereIn('character_id',
                        array_keys($user->getAffiliationMap()['char']));

                // Add any characters from owner API keys
                $query->orWhere('character_id', $user->id);
            });
        }

        return $alliances->orderBy('alliances.name')
            ->pluck('alliances.name')
            ->filter(function ($item) {

                // Filter out the null alliance name
                return ! is_null($item);
            });

    }

    /**
     * Get a list of corporations the current
     * authenticated user has access to.
     *
     * @deprecated replace by new ACL system. Must be move to ACL trait
     *
     * @return mixed
     */
    public function getCharacterCorporations()
    {

        // TODO : rewrite the method according to the new ACL mechanic

        $user = auth()->user();

        $corporations = new CorporationInfo;

        // If the user us a super user, return all
        if (! $user->hasSuperUser()) {

            $affiliation_map = $user->getAffiliationMap();

            $granted_corporations = array_keys($affiliation_map['corp']);

            $corporations = $corporations->whereIn('corporation_id', $granted_corporations);
        }

        return $corporations->orderBy('name')
            ->pluck('name');
    }
}
