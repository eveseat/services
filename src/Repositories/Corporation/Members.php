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
use Seat\Eveapi\Models\Corporation\MemberTracking;

/**
 * Class Members
 * @package Seat\Services\Repositories\Corporation
 */
trait Members
{

    /**
     * Return the Member Tracking for a Corporation
     *
     * @param int $corporation_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCorporationMemberTracking(int $corporation_id) : Collection
    {

        return MemberTracking::select(
            'corporation_member_trackings.*',
            'account_api_key_info_characters.*',
            'eve_api_keys.enabled')
            ->join('account_api_key_info_characters', function ($join) {

                $join->on('corporation_member_trackings.characterID', '=',
                    'account_api_key_info_characters.characterID');
            })
            ->join('eve_api_keys', function ($join) {

                $join->on('account_api_key_info_characters.keyID', '=',
                    'eve_api_keys.key_id');
            })
            ->where('corporation_member_trackings.corporationID',
                $corporation_id)
            ->groupBy('corporation_member_trackings.characterID')
            ->get();
    }

}
