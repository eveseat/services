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

namespace Seat\Services\Repositories\Character;

use Illuminate\Database\Eloquent\Builder;
use Seat\Eveapi\Models\Contacts\CharacterContact;
use Seat\Eveapi\Models\Contacts\CharacterContactLabel;

trait Contacts
{
    /**
     * Get a characters contact list.
     *
     * @param int $character_id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getCharacterContacts(int $character_id): Builder
    {

        return CharacterContact::where('character_contacts.character_id', $character_id)
            ->orderBy('standing', 'desc')
            ->leftJoin('character_infos', 'character_contacts.contact_id', '=', 'character_infos.character_id')
            ->leftJoin('corporation_infos', 'character_contacts.contact_id', '=', 'corporation_infos.corporation_id')
            ->select('character_infos.name', 'character_contacts.*', 'corporation_infos.name AS corporationName');
    }

    /**
     * Get a characters contact list labels.
     *
     * @param int $character_id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getCharacterContactLabels(int $character_id): Builder
    {

        return CharacterContactLabel::where('character_id', $character_id);
    }
}
