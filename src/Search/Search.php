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

        $messages = MailHeader::with('body', 'recipients', 'sender', 'character')
            ->select('timestamp', 'from', 'subject', 'mail_headers.mail_id', 'character_id');

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

        return $messages;

    }

    /**
     * @return mixed
     */
    public function doSearchCharacterAssets()
    {
        return CharacterAsset::authorized('character.asset')
            ->with('character', 'character.corporation', 'character.alliance', 'type', 'type.group')
            ->select()
            ->addSelect('character_assets.name as asset_name');
    }

    /**
     * @return mixed
     */
    public function doSearchCharacterSkills()
    {
        return CharacterSkill::authorized('character.skill')
            ->with('character', 'character.corporation', 'character.alliance', 'type', 'type.group');
    }
}
