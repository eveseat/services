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

use Illuminate\Support\Collection;
use Seat\Eveapi\Models\Account\ApiKeyInfoCharacters;
use Seat\Eveapi\Models\Character\CharacterSheet;
use Seat\Eveapi\Models\Character\CharacterSheetCorporationTitles;
use Seat\Eveapi\Models\Eve\CharacterInfoEmploymentHistory;

trait Info
{

    /**
     * Retreive a character name by character id
     *
     * @param int $character_id
     *
     * @return string
     */
    public function getCharacterNameById(int $character_id) : string
    {

        return ApiKeyInfoCharacters::where('characterID', $character_id)
            ->value('characterName');
    }

    /**
     * Get Information about a specific Character
     *
     * @param int $character_id
     *
     * @return \Seat\Eveapi\Models\Account\ApiKeyInfoCharacters
     */
    public function getCharacterInformation(int $character_id) : ApiKeyInfoCharacters
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
     * @param int $character_id
     *
     * @return \Seat\Eveapi\Models\Character\CharacterSheet
     */
    public function getCharacterSheet(int $character_id) : CharacterSheet
    {

        return CharacterSheet::find($character_id);
    }

    /**
     * Return the employment history for a character
     *
     * @param int $character_id
     *
     * @return \Illuminate\Support\Collection|mixed
     */
    public function getCharacterEmploymentHistory(int $character_id) : Collection
    {

        return CharacterInfoEmploymentHistory::where('characterID', $character_id)
            ->orderBy('startDate', 'desc')
            ->get();

    }

    /**
     * Get Corporation titles related to a specific character
     *
     * @param int $character_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCharacterCorporationTitles(int $character_id) : Collection
    {

        return CharacterSheetCorporationTitles::where('characterID', $character_id)
            ->get();
    }

}
