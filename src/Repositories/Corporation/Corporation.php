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

namespace Seat\Services\Repositories\Corporation;

use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Services\Repositories\Configuration\UserRespository;

/**
 * Class Corporation.
 * @package Seat\Services\Repositories\Corporation
 */
trait Corporation
{
    use UserRespository;

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllCorporations()
    {

        return CorporationInfo::all();
    }

    /**
     * Return the corporations for which a user has access.
     *
     * @return mixed
     */
    public function getAllCorporationsWithAffiliationsAndFilters()
    {

        // Get the User for permissions and affiliation
        // checks
        $user = auth()->user();

        // Start a fresh query
        $corporations = new CorporationInfo();

        // Check if this user us a superuser. If not,
        // limit to stuff only they can see.
        if (! $user->hasSuperUser()) {

            // Add affiliated corporations based on the
            // corporation.list_all permission
            if ($user->has('corporation.list_all', false))
                $corporations = $corporations->orWhereIn(
                    'corporation_id', array_keys($user->getAffiliationMap()['corp']));

            // TODO : ensure user is granted - we're not checking if the user has enough permission to get access to
            //        attached corporation

            // Add any character the user may own. This is a slightly
            // complex case as we need to sub select a few things
            $corporations = $corporations->orWhereIn('corporation_id',

                $this->getUserGroupCharacters($user->groups)->map(function($item) {
                    return $item->character->corporation_id;
                })

            );

        }

        return $corporations->orderBy('name', 'desc')
            ->get();

    }

    /**
     * Return the Corporation Sheet for a Corporation.
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationSheet($corporation_id)
    {

        return CorporationInfo::where('corporation_id', $corporation_id)
            ->first();
    }
}
