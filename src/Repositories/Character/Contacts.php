<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017  Leon Jacobs
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
use Seat\Eveapi\Models\Character\ContactList;
use Seat\Eveapi\Models\Character\ContactListLabel;

trait Contacts
{
    /**
     * Get a characters contact list.
     *
     * @param int $character_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCharacterContacts(int $character_id): Collection
    {

        return ContactList::where('characterID', $character_id)
            ->join('invTypes', function ($join) {

                $join->on('invTypes.typeID', '=', 'character_contact_lists.contactTypeID');
            })
            ->orderBy('standing', 'desc')
            ->get();
    }

    /**
     * Get a characters contact list labels.
     *
     * @param int $character_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCharacterContactLabels(int $character_id): Collection
    {

        return ContactListLabel::where('characterID', $character_id)
            ->get();
    }
}
