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

namespace Seat\Services\Repositories\Corporation;

use Seat\Eveapi\Models\Corporation\CorporationSheet;
use Seat\Eveapi\Models\Corporation\CorporationSheetDivision;
use Seat\Eveapi\Models\Corporation\CorporationSheetWalletDivision;
use Seat\Services\Helpers\Filterable;

/**
 * Class CorporationRepository
 * @package Seat\Services\Repositories\Corporation
 */
trait CorporationRepository
{

    use Filterable;

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllCorporations()
    {

        return CorporationSheet::all();
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
        $corporations = new CorporationSheet;

        // Check if this user us a superuser. If not,
        // limit to stuff only they can see.
        if (!$user->hasSuperUser()) {

            // Add affiliated corporations based on the
            // corporation.list_all permission
            if ($user->has('corporation.list_all', false))
                $corporations = $corporations->orWhereIn(
                    'corporationID', array_keys($user->getAffiliationMap()['corp']));

            // Add any keys the user may own. This is a slightly
            // complex case as we need to sub select a few things
            $corporations = $corporations->orWhereIn('corporationID',

                // The return array of all of the below is a
                // nested mess. We can just flatten it.
                array_flatten($user->keys()
                    // Include info.characters so that we can
                    // filter it down in the map() function
                    // below.
                    ->with('info.characters')
                    // Info itself has a constraint applied, checking
                    // if the api key type is that of a corp.
                    ->whereHas('info', function ($query) {

                        $query->where('type', 'Corporation');

                    })->get()->map(function ($item) {

                        // We finally map the resultant Collection
                        // object and list the corporationID out of
                        // the $key->info->characters relation.
                        return $item->info->characters
                            ->lists('corporationID')->toArray();
                    })));
        }

        return $corporations->orderBy('corporationName', 'desc')
            ->get();

    }

    /**
     * Return the Corporation Divisions for a Corporation
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationDivisions($corporation_id)
    {

        return CorporationSheetDivision::where('corporationID', $corporation_id)
            ->get();
    }

    /**
     * Return the Corporation Sheet for a Corporation
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationSheet($corporation_id)
    {

        return CorporationSheet::where('corporationID', $corporation_id)
            ->first();
    }

    /**
     * Return the Corporation Wallet Divisions for a Corporation
     *
     * @param $corporation_id
     *
     * @return mixed
     */
    public function getCorporationWalletDivisions($corporation_id)
    {

        return CorporationSheetWalletDivision::where('corporationID', $corporation_id)
            ->get();
    }

}
