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

use Seat\Eveapi\Models\Character\MailMessage;
use Seat\Services\Repositories\Character\CharacterRepository;
use Seat\Services\Repositories\Corporation\CorporationRepository;

/**
 * Class Search
 * @package Seat\Services\Search
 */
trait Search
{

    use CharacterRepository, CorporationRepository {

        // Both Char & Corp Repos use the Seat\Services\Helpers\Filterable
        // Trait, so just specify one to resolve the colision
        CharacterRepository::where_filter insteadof CorporationRepository;
    }

    /**
     * @param $query
     *
     * @return \Illuminate\Database\Eloquent\Collection|static|static[]
     */
    public function doSearchCharacters($query)
    {

        // Get the data
        $characters = $this->getAllCharactersWithAffiliationsAndFilters();

        // Filter the data
        $characters = $characters->filter(function ($item) use ($query) {

            return str_contains(
                strtoupper($item->characterName), strtoupper($query));
        });

        return $characters;
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function doSearchCorporations($query)
    {

        $corporations = $this->getAllCorporationsWithAffiliationsAndFilters();

        $corporations = $corporations->filter(function ($item) use ($query) {

            return str_contains(
                strtoupper($item->corporationName), strtoupper($query));
        });

        return $corporations;

    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function doSearchCharacterMail($query)
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

        return $messages->where('character_mail_messages.title', 'like', '%' . $query . '%')
            ->orWhere('character_mail_message_bodies.body', 'like', '%' . $query . '%')
            ->orderBy('character_mail_messages.sentDate', 'desc')
            ->take(15)
            ->get();

    }

}
