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

namespace Seat\Services\Repositories\Corporation;

use Illuminate\Support\Collection;
use Seat\Eveapi\Models\Corporation\ContactList;
use Seat\Eveapi\Models\Corporation\ContactListLabel;

/**
 * Class Contacts
 * @package Seat\Services\Repositories\Corporation
 */
trait Contacts
{

    /**
     * Return the contacts list for a corporation
     *
     * @param int $corporation_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationContacts(int $corporation_id) : Collection
    {

        return ContactList::where('corporationID', $corporation_id)
            ->join('invTypes', function ($join) {

                $join->on('invTypes.typeID', '=', 'corporation_contact_lists.contactTypeID');
            })
            ->orderBy('standing', 'desc')
            ->get();

    }

    /**
     * Return the contact labels for a Corporation
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationContactsLabels(int $corporation_id) : Collection
    {

        return ContactListLabel::where('corporationID', $corporation_id)
            ->get();
    }

}
