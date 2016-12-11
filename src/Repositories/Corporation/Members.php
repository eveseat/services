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

use Illuminate\Support\Facades\DB;

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
    public function getCorporationMemberTracking(int $corporation_id)#: Collection
    {

        $sub_table = DB::table('account_api_key_info_characters')
            ->select('account_api_key_info_characters.characterID')
            ->selectRaw(
                'case
                    when
                        (
                            -- The key must be enabled
                            `eve_api_keys`.`enabled` = 1

                            -- And if expiry is set, but be in the future.
                            and
                            (
                                `account_api_key_infos`.`expires` > NOW()
                                or
                                `account_api_key_infos`.`expires` IS NULL
                            )
                        )
                    then 1
                    else 0
                end as `key_ok`')
            ->leftJoin('eve_api_keys', function ($join) {

                $join->on('eve_api_keys.key_id', '=',
                    'account_api_key_info_characters.keyID');
            })
            ->leftJoin('account_api_key_infos', function ($join) {

                $join->on('account_api_key_infos.keyID', '=',
                    'eve_api_keys.key_id');
            })
            ->where(function ($query) {

                $query->whereRaw('`account_api_key_infos`.`expires` > NOW()')
                    ->orWhereNull('account_api_key_infos.expires');
            })
            // Sad whereRaw here. No idea but the query builder is
            // paramaterizing this field and adding the corpID!? :rage:
            ->whereRaw('eve_api_keys.enabled = 1')
            ->groupBy('account_api_key_info_characters.characterID')
            ->toSql();

        // Return the tracking data with the subtable joined
        return DB::table(DB::raw('(' . $sub_table . ') as key_status'))
            ->select(
                'corporation_member_trackings.*',
                'key_status.key_ok'
            )
            ->rightJoin('corporation_member_trackings', function ($join) {

                $join->on('key_status.characterID', '=',
                    'corporation_member_trackings.characterID');
            })
            ->where('corporation_member_trackings.corporationID', $corporation_id)
            ->get();

    }

}
